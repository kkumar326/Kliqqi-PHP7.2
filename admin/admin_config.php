<?php

include_once('../internal/Smarty.class.php');
$main_smarty = new Smarty;

include('../config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'tags.php');
include(mnminclude.'smartyvariables.php');
include(mnminclude.'admin_config.php');

check_referrer();

// require user to log in
force_authentication();

// restrict access to admins
$canIhaveAccess = 0;
$canIhaveAccess = $canIhaveAccess + checklevel('admin');

if($canIhaveAccess == 0){	
//	$main_smarty->assign('tpl_center', '/admin/access_denied');
//	$main_smarty->display('/admin/admin.tpl');		
	header("Location: " . getmyurl('admin_login', $_SERVER['REQUEST_URI']));
	die();
}

// breadcrumbs and page titles
$navwhere['text1'] = $main_smarty->get_config_vars('KLIQQI_Visual_Header_AdminPanel');
$navwhere['link1'] = getmyurl('admin', '');
$navwhere['text2'] = $main_smarty->get_config_vars('KLIQQI_Visual_Header_AdminPanel_5') . $main_smarty->get_config_vars('KLIQQI_Visual_Name');
$navwhere['link2'] = my_kliqqi_base . "/admin/admin_config.php";
$main_smarty->assign('navbar_where', $navwhere);
$main_smarty->assign('posttitle', " / " . $main_smarty->get_config_vars('KLIQQI_Visual_Header_AdminPanel'));

// pagename	
define('pagename', 'admin_config'); 
$main_smarty->assign('pagename', pagename);

// read the mysql database to get the kliqqi version
/* Redwine: kliqqi version query removed and added to /libs/smartyvriables.php */

// show the template
$main_smarty->assign('tpl_center', '/admin/configure');
if(isset($_REQUEST['action'])){
	dowork();
} else {
	// sidebar	
	//$main_smarty = do_sidebar($main_smarty);

	$main_smarty->display('/admin/admin.tpl');	
}

function dowork(){	
	global $db, $main_smarty;

	$canIhaveAccess = 0;
	$canIhaveAccess = $canIhaveAccess + checklevel('admin');
	
	if($canIhaveAccess == 1)
	{
		if(is_writable('../settings.php') == 0){
			die("<div class='alert'>Error: settings.php is not writeable.</div>");
		}
		
		$action = isset($_REQUEST['action']) && sanitize($_REQUEST['action'], 3) != '' ? sanitize($_REQUEST['action'], 3) : "view";
		
		if($action == "view"){
			$config = new kliqqiconfig;
			if(isset($_REQUEST['page'])){
				$config->var_page = sanitize($_REQUEST['page'], 3);
				$config->showpage();
			}
		}
		
		if($action == "save"){
			$config = new kliqqiconfig;
//			$config->var_id = substr(sanitize($_REQUEST['var_id'], 3), 6, 10);
			$config->var_id = sanitize($_REQUEST['var_id'], 3);
			$config->read();

			// Check if template exists
			if ($config->var_name=='$thetemp' && $config->var_value!=js_urldecode($_REQUEST['var_value']))
			{
				if (!file_exists('../templates/'.js_urldecode($_REQUEST['var_value'])))
				{
				    print "alert('".$main_smarty->get_config_vars('KLIQQI_Visual_AdminPanel_NoTemplate')."')";
				    exit;
				}
				else
				{
				    if (file_exists('../templates/'.js_urldecode($_REQUEST['var_value']).'/template_details.php'))
					include ('../templates/'.js_urldecode($_REQUEST['var_value']).'/template_details.php');
/* Redwine: replaced the kliqqi version() callwith the kliqqi version variable from /libs/smartyvriables.php */
				    if ($template_info['designed_for_kliqqi_version'] < $kliqqi_version && !$_REQUEST['force'])
				    {
					if (!$template_info['designed_for_kliqqi_version']) $template_info['designed_for_kliqqi_version'] = 'unknown';
					print sprintf("if (confirm('".$main_smarty->get_config_vars('KLIQQI_Visual_AdminPanel_Template_Version')."')) {XMLHttpRequestObject.open('GET', '?action=save&var_id={$config->var_id}&var_value=".urlencode($_REQUEST['var_value'])."&force=1', true); XMLHttpRequestObject.send(null);}",
								$template_info['designed_for_kliqqi_version'],kliqqi_version());
					exit;
				    }
				}
			}
			$config->var_value = $db->escape(js_urldecode($_REQUEST['var_value']));
			$config->store(false);
		}
	}
}

?>

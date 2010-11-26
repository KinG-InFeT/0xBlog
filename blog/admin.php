<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file admin.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */
ob_start();
session_start();
 
include("config.php");
include("lib/mysql.class.php");
include("lib/core.class.php");
include("lib/admin.class.php");
include("lib/login.class.php");

$template = new Core();
$admin    = new Admin();
$login    = new Login();

@$action = $_GET['action'];

$template->show_header($title = 'Administration');

print "\n<div id=\"wrapper\">"
    . "\n<div id=\"content\">";

$login->form_login(@$_COOKIE['username'], @$_COOKIE['password']);
    
switch($action) {

	/* Manager articles */
	case 'add_post':
		$admin->add_post();
	break;

	case 'edit_post';
		$admin->edit_post(@$_REQUEST['id']);
	break;
	
	case 'del_post':
		$admin->del_post(@$_REQUEST['id']);
	break;
	
	case 'manage_comments':
		$admin->manage_comments(@$_GET['id']);
	break;
	
	case 'del_comment':
		$admin->del_comment(@$_POST['id']);
	break;
	
	/* admin struments */
	case 'add_admin':
		$admin->add_admin();
	break;
	
	case 'del_admin':
		$admin->del_admin(@$_POST['a_id']);
	break;
	
	case 'change_pass_admin':
		$admin->change_pass_admin(@$_POST['a_id']);
	break;
	
	/*  manager Settings for blog */	
	case 'themes':
		$admin->themes();
	break;

	case 'settings':
		$admin->settings();
	break;
	
	case 'updates':
		print $admin->updates(Core::VERSION);
	break;
	
	case 'clear_blog':
		$admin->clear_blog();
	break;
	
	//logout
	case 'logout':
		$login->logout(@$_COOKIE['username'], @$_COOKIE['password']);
	break;
	
	//print all articles for manage
	default:
		$admin->show_administration();
	break;
}

print "</div>\n";

$template->show_menu($class = 'admin');
$template->show_footer();
?>

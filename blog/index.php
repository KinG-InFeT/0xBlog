<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file index.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */
 
include("config.php");
include("lib/mysql.class.php");
include("lib/core.class.php");

$template = new Core();

if (isset($_GET['page']) && is_numeric($_GET['page']) && ((int) $_GET['page']) > 0 ) 
	$page = (int) $_GET['page']; 
else
	$page = 1;

$template->show_header($title = NULL);
$template->show_blog($page);
$template->show_menu($class = NULL);
$template->show_footer();
?>

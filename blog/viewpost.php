<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file viewpost.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */

session_start();

include("lib/mysql.class.php");
include("lib/core.class.php");

$template = new Core();

$template->show_header($template->get_title(@$_GET['id']));

$template->show_post_and_comments(@$_GET['id']);

$template->show_menu($class = NULL);

$template->show_footer();

?>

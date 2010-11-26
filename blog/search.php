<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file search.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */

session_start();

include("lib/mysql.class.php");
include("lib/core.class.php");

$template = new Core();

$template->show_header($title = 'Search');

$template->show_menu($class = NULL);

print "\n<div id=\"wrapper\">"
    . "\n<div id=\"content\">";

print "<big><big>";

if(empty($_GET['text'])) {

	print "\n<br /><br /><br /><br /><b>Search within the blog:</b><br />";
	print "\n<form method=\"GET\" />"
		. "\n<input type=\"text\" name=\"text\" />"
		. "\n<input type=\"submit\" value=\"Search\" />"
		. "\n</form>";
}else{
	$template->search(@$_GET['text']);
}

print "</big></big>";

print "\n</div>"
    . "\n</div>";
    
$template->show_footer();

?>

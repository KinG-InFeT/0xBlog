<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file search.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */

session_start();

include("lib/mysql.class.php");
include("lib/core.class.php");

$template = new Core();

$template->show_header($title = 'Search');

$template->show_menu($class = NULL);

print "\n\t<div id=\"content\">";

if(empty($_GET['text'])) {

	print "\n<strong>Search within the blog:</strong><br />"
	    . "\n<form method=\"GET\" action=\"\"/>"
		. "\n<input type=\"text\" name=\"text\" />"
		. "\n<input type=\"submit\" value=\"Search\" />"
		. "\n</form>";
}else{
	$template->search(@$_GET['text']);
}

print "\n\t</div>";
    
$template->show_footer();

?>

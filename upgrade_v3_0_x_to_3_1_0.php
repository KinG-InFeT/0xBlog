<?php
ob_start();
if(!file_exists("config.php"))
	die("File config.php does not exist!");
else
	include("config.php");

?>
<html>
<head><title>0xBlog Upgrade System</title></head>
<body>		  
		<h1 align="center">Upgrade System of 0xBlog</h1><br />
		<br />
		Upgrade 3.0.x to 3.1.0
		<br />
		  
<form method="POST" action="?send=1" />
<input type="submit" value="Upgrade" />
</form>
<?php

if(@$_GET['send'] == 1) {
	
	  mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
	mysql_select_db($db_name) or die(mysql_error());
	
	mysql_query("ALTER TABLE ".__PREFIX__."articles ADD cat_id INT") or die(mysql_error());
 	
 	mysql_query("UPDATE `".__PREFIX__."articles` SET cat_id = 1") or die(mysql_error());
 	
	mysql_query("CREATE TABLE `".__PREFIX__."categories` (
					`cat_id` int( 11 ) NOT NULL AUTO_INCREMENT,
					`cat_name` text NOT NULL ,
				KEY `cat_id` ( `cat_id` )
				) TYPE = MYISAM AUTO_INCREMENT = 1;") or die(mysql_error());
	
	mysql_query("INSERT INTO ".__PREFIX__."categories (`cat_id`, `cat_name`) VALUES ('1', 'General');") or die(mysql_error());
	
	print "<script>alert(\"Upgrade System with success\");</script>";
	header('Location: index.php');
}
	
?>
</body>
</html>

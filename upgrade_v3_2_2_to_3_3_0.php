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
		Upgrade 3.2.2 to 3.3.0
		<br />
		  
<form method="POST" action="?send=1" />
<input type="submit" value="Upgrade" />
</form>
<?php

if(@$_GET['send'] == 1) {
	
	  mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
	mysql_select_db($db_name) or die(mysql_error());

	mysql_query("ALTER TABLE ".__PREFIX__."config ADD ip_log_active INT") or die(mysql_error());
	
	print "<script>alert(\"Upgrade System with success\");</script>";
	header('Location: index.php');
}
	
?>
</body>
</html>
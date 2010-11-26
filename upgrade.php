<?php
if(!file_exists("config.php"))
	die("File config.php does not exist!");
else
	include("config.php");

?>
<html>
<head><title>Upgrade File</title></head>
<body>		  
		<h1 align="center">Upgrade System of 0xBlog</h1><br />
		<br />
		Upgrade 2.5 - Final to 3.0 - Beta
		<br />
		  
<form method="POST" action="upgrade.php?send=1" />
Email: <input type="text" name="mail" /><br /><br />
<input type="submit" value="Upgrade" />
</form>
<?php

if((@$_GET['send'] == 1) && (!empty($_POST['mail']))) {

	$email = mysql_real_escape_string( htmlspecialchars( stripslashes( $_POST['mail'] )));
	
	  mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
	mysql_select_db($db_name) or die(mysql_error());
	
	//aggiunta della colonna num_read nella tabella articles
	mysql_query("ALTER TABLE ".PREFIX."articles ADD num_read INT");
 	
	//tabella config
	mysql_query("CREATE TABLE `".PREFIX."config` (
	  `title` text NOT NULL,
	  `description` text NOT NULL,
	  `themes` text NOT NULL,
	  `lang` text NOT NULL,
	  `limit` INT NOT NULL,
	  `footer` text NOT NULL 
	) TYPE=MyISAM AUTO_INCREMENT=1 ;") or die(mysql_error());
	
	//popolazione tabella config
	mysql_query("INSERT INTO `".PREFIX."config` (`title`, `description`, `themes`, `lang`, `limit`, `footer`) VALUES ('Upgraded Version to 3.0 - Beta Version', 'Go Admin Panel -> Settings', 'default.css', 'eng.php', '".$limit."', 'FOOTER DA CAMBIARE');") or die(mysql_error());

	//tabella users	
	mysql_query("CREATE TABLE `".PREFIX."users` (
	  `id` int(11) NOT NULL auto_increment,
	  `username` text NOT NULL,
	  `password` text NOT NULL,
	  `email` text NOT NULL,
	  KEY `id` (`id`)
	) TYPE=MyISAM AUTO_INCREMENT=1 ;") or die(mysql_error());
	
	//popolazione tabella utenti 
	mysql_query("INSERT INTO ".PREFIX."users (username, password, email) VALUES ('".$username."', '".$password."', '".$email."');") or die(mysql_error());
		
	//creo il file config.php ;)
	$config = '<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file config.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */

@define("__INSTALLED__", 1);

@define("__PREFIX__","'.PREFIX.'");

$db_host = "'.$db_host.'";
$db_user = "'.$db_user.'";
$db_pass = "'.$db_pass.'";
$db_name = "'.$db_name.'";
?>';
	
		// Scriviamo sul config.php i dati che ci occorrono
		if(!($open = fopen( "config.php", "w" )))
			die("Errore durante l'apertura sul file config.php<br /> Prego di controllare i permessi sul file!");
			
		fwrite ($open, $config);//Scrivo sul file config.php
		
		fclose ($open); // chiudo il file
		print "<script>alert(\"Upgrade with success\");</script>";
		header('Location: index.php');
}
	
?>
</body>
</html>

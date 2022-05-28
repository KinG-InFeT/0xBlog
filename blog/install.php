<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file install.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */

ob_start();

if(!(file_exists('./config.php')))
	die("<b>File 'config.php' inesistente! <br />
		Creare un file vuoto chiamato config.php </b>\n");

if(!(is_writable('./install.php')))
	die ("<b>File 'install.php' non &egrave; scrivibile!<br />
		 Settare i permessi a 777 (chmod)</b>\n");		 	
		 
if(!(is_writable('./config.php')))
	die ("<b>File 'config.php' non &egrave; scrivibile!<br />
		 Settare i permessi a 777 (chmod)</b>\n");
		 
if(!(phpversion() >= '5.2.0')) {
	die('PHP version is: '.phpversion().' ERROR! Upgrade to last version > 5.2.0');
}
		 
include("config.php");

if( isSet($_GET['delete_install']) && $_GET['delete_install'] == 1 ){
	if( unlink("./install.php") == FALSE ){
		chmod("./install.php", 0777);
		die(header('Location: install.php?delete_install=1'));
	}else{
		header("location: index.php");
	}
}

if(defined("__INSTALLED__"))
	die("0xBlog already installed! <br /> Go to <a href=\"index.php\">Home Blog</a>");

function VarProtect ($content) {
	if (is_array ($content)) {
		foreach ($content as $key => $val)
			$content[$key] = addslashes (htmlentities (stripslashes ($content[$key])));
	}else{
		$content = addslashes (htmlentities ($content));
	}
	
	return stripslashes ($content);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
	<title>Welcome to 0xBlog</title>
	<style type="text/css">
	<!--
	a:link {
		color:white; 
		text-decoration: none;
	}
	
	a:hover {
		color:#66FFCC;
		text-decoration:underline;
	}
	
	a:visited {
		 color:#FF6600;
		 text-decoration: none;
	}
	
	textarea {
		 background-color: #CCFFCC;
		 color: #000033;
	}
	
	input,select ,button {
		background-color: #000000;
		color: #CC9900;
		border-style: solid;
		border-color: #4B4B4B;
	}
	
	input:hover	{
		background: #222;
	}
	
	table {
		border-collapse:collapse;
	}
	
	table,th, td {
		border: 1px solid grey;
	}
	-->
	</style>
</head>

<body bgcolor="black" text="white">
<h2><div align="center"><font color="white">Welcome to<b> 0xBlog</b> Installation</font></div></h2>
<br />
<?php

if (   !empty( $_POST['username'] )
	&& !empty( $_POST['password'] )
	&& !empty( $_POST['title']    )
	&& !empty( $_POST['desc']     )
	&& !empty( $_POST['email']    )
	&& !empty( $_POST['host']     )	
	&& !empty( $_POST['name']     )
	&& !empty( $_POST['user']     )
	&& !empty( $_POST['prefix']   )
	&& !empty( $_POST['limit']    )
	&& !empty( $_POST['footer']   )
	&& !empty( $_POST['lang']     )
	) {
		
	//Dati per connessione al MySQL
	$host = htmlspecialchars( $_POST['host'] );
	$user = htmlspecialchars( $_POST['user'] );
	$pass = htmlspecialchars( $_POST['pass'] );
	$name = htmlspecialchars( $_POST['name'] );
	
	//Dati Connessione MySQL e Connessione
	$db_connect = @mysqli_connect  ( $host, $user, $pass, $name);

	if(!$db_connect)
		die("<b>Errore durante la connessione al database MySQL</b><br />".mysql_errno()." : ".mysql_error());
	
	//dati amministrazione
	$user_admin    = VarProtect ( $_POST['username'] );
	$pass_admin    =        md5 ( $_POST['password'] );
	
	//dati di configurazione
	$title   = VarProtect( $_POST['title']   );
	$desc    = VarProtect( $_POST['desc']    );
	$email   = VarProtect( $_POST['email']   );
	$limit   =     intval( $_POST['limit']   );
	$footer  = VarProtect( $_POST['footer']  );
	$prefix  = VarProtect( $_POST['prefix']  );
	$lang    = VarProtect( $_POST['lang']    );	
		
	//creo la tabella users
	mysqli_query($db_connect, "CREATE TABLE `".$prefix."users` (
	  `id` int(11) NOT NULL auto_increment,
	  `username` text NOT NULL,
	  `password` text NOT NULL,
	  `email` text NOT NULL,
	  KEY `id` (`id`)
	);") or die(mysql_error());
	
	echo "Table <b>'".$prefix."users'</b> created with success<br />\n";
	
	mysqli_query($db_connect, "INSERT INTO ".$prefix."users (username, password, email) VALUES ('".$user_admin."', '".$pass_admin."', '".$email."');") or die(mysql_error());
		
	echo "User <b>".$user_admin."</b> added with success<br />\n";
	
	
	//tabella config
	mysqli_query($db_connect, "CREATE TABLE `".$prefix."config` (
	  `title` text NOT NULL,
	  `description` text NOT NULL,
	  `themes` text NOT NULL,
	  `lang` text NOT NULL,
	  `limit` INT NOT NULL,
	  `footer` text NOT NULL, 
	  `ip_log_active` INT NOT NULL 
	);") or die(mysql_error());
	
	echo "Table <b>'".$prefix."config'</b> created with success<br />\n";
	
	mysqli_query($db_connect, "INSERT INTO ".$prefix."config (`title`, `description`, `themes`, `lang`, `limit`, `footer`, `ip_log_active`
				) VALUES (
				'".$title."', '".$desc."', 'default.css', '".$lang."', '".$limit."', '".$footer."', 1);") 
			or die(mysql_error());
		
	echo "<b>Configuration</b> added with success<br />\n";
			
	//tabella articles
	mysqli_query($db_connect, "CREATE TABLE `".$prefix."articles` (
	  `id` int(11) NOT NULL auto_increment,
	  `author` text NOT NULL,
	  `title` text NOT NULL,
	  `post` text NOT NULL,
	  `post_date` text NOT NULL,
	  `num_read` INT NOT NULL,
  	  `cat_id` INT NOT NULL,
	  KEY `id` (`id`)
	);") or die(mysql_error());
	
		echo "Table <b>'".$prefix."articles'</b> created with success<br />\n";
		
	//tabella comments
	mysqli_query($db_connect, "CREATE TABLE `".$prefix."comments` (
	  `id` int(11) NOT NULL auto_increment,
	  `blog_id` int(11) NOT NULL default '0',
	  `name` text NOT NULL,
	  `comment` text NOT NULL,
	  `ip` text NOT NULL,
	  KEY `id` (`id`)
	);") or die(mysql_error());
	
	echo "Table <b>'".$prefix."comments'</b> created with success<br />\n";
	
	mysqli_query($db_connect, "CREATE TABLE `".$prefix."categories` (
					`cat_id` int( 11 ) NOT NULL AUTO_INCREMENT,
					`cat_name` text NOT NULL ,
				KEY `cat_id` ( `cat_id` )
				);") or die(mysql_error());
	
	echo "Table <b>'".$prefix."categories'</b> created with success<br />\n";
	
	mysqli_query($db_connect, "INSERT INTO ".$prefix."categories (`cat_id`, `cat_name`) VALUES ('1', 'General');") or die(mysql_error());
		
	echo "<b>Default Categories</b> added with success<br />\n";
	
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
 * @link http://0xproject.netsons.org#0xBlog
 *
 */
@define("__INSTALLED__", 1);

@define("__PREFIX__","'.$prefix.'");

$db_host = "'.$host.'";
$db_user = "'.$user.'";
$db_pass = "'.$pass.'";
$db_name = "'.$name.'";
?>';
	
		// Scriviamo sul config.php i dati che ci occorrono
		if(!($open = fopen( "config.php", "w" )))
			die("Errore durante l'apertura sul file config.php<br /> Prego di controllare i permessi sul file!");
			
		fwrite ($open, $config);//Scrivo sul file config.php
		
		fclose ($open); // chiudo il file

		echo "<b>config.php</b> created with success<br />\n";
		
		echo "<font color=green>Installation Complete with Success!</font>"; //stampo l'avvenuto successo di installazione
		echo '<br><br><a href="?delete_install=1">Delete file install.php</a>';//Consiglio di delettare il file install.php
}else{
?>
<div align="center">
<font color="white" face="Arial" size="5">This is your first installation!</font><br />
<br />
<form method="POST">
<table style="text-align: left;" border="0" cellpadding="2" width="100%" cellspacing="2">
<tbody>
	<tr>
		<td><font color="white">Insert your username:</font></td>
		<td><input type="text" name="username" size="40"></td>
		<td bgcolor="black"><a onclick="window.alert('Example: admin')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Insert your password:</font></td>
		<td><input type="password" name="password" size="40"></td>
		<td bgcolor="black"><a onclick="window.alert('Example: my_passw0rd')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Insert the title for your blog:</font></td>
		<td><input type="text" name="title"></td>
		<td bgcolor="black"><a onclick="window.alert('Example: Welcome to my world')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Insert the description for your blog:</font></td>
		<td><input type="text" name="desc"></td>
		<td bgcolor="black"><a onclick="window.alert('Example: Welcome to my world')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>	
	<tr>
		<td><font color="white">Insert your e-mail address:</font></td>
		<td><font color="white"><input type="text" name="email" size="40"></font></td>
		<td bgcolor="black"><a onclick="window.alert('Example: my_email@hotmail.it')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Footer Part:</font></td>
		<td><font color="white"><input type="text" name="footer" size="40" value="Copyright <?php print date("Y"); ?> By ..."></font></td>
		<td bgcolor="black"><a onclick="window.alert('Example: Copyright 20../20.. By my name')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>	
	<tr>
		<td><font color="white">How many items per page? ( Recommended 6):</font></td>
		<td><input type="text" name="limit" value="6" size="3"></td>
		<td bgcolor="black"><a onclick="window.alert('Example: 5 or 10 or 13 or ...')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Language:</font></td>
		<td>
		<select name='lang'>
			<option value = 'eng.php' >English
			<option value = 'ita.php' >Italiano
		</select>
		</td>
		<td bgcolor="black"><a onclick="window.alert('You like talking about?')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	</tbody>
</table>

<p><b><font color="white"><br /><br />MySQL Info</font></b></p>

<table style="text-align: left;" border="0" cellpadding="2" width="100%" cellspacing="2">
<tbody>
	<tr>
		<td><font color="white">Host Database:</font></td>
		<td><input type="text" name="host" size="30"></td>
		<td bgcolor="black"><a onclick="window.alert('The Host which is part of the Database.')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Username Database:</font></td>
		<td><input type="text" name="user" size="30"></td>
		<td bgcolor="black"><a onclick="window.alert('Username for Connection MySQL')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Password Database:</font></td>
		<td><input type="text" name="pass" size="30"></td>
		<td bgcolor="black"><a onclick="window.alert('Password for connection the database')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>	
	<tr>
		<td><font color="white">Name Database:</font></td>
		<td><input type="text" name="name" size="30"></td>
		<td bgcolor="black"><a onclick="window.alert('Name the your Database')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>
	<tr>
		<td><font color="white">Table Prefix:</font></td>
		<td><input type="text" name="prefix" value="0xBlog_" size="15"></td>
		<td bgcolor="black"><a onclick="window.alert('Exemple: blog_')"><img alt="img/info.png" border="0" src="img/info.png"></a></td>
	</tr>	
	</tbody>
</table>
<br />
<div align="center"><input type="submit" value="Confirm"> <input type="reset"  value="Reset"></div>
</form>
</div>
<?php
}
?>
<br />
<br />
<div align="center"><font color="grey"><i>Powered By <a href="http://0xproject.netsons.org/#0xBlog">0xBlog</a></i></font></div>
</body>
</html>

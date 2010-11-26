<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file login.class.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */
 
include_once("language.class.php");

$lang = new Language();

include("languages/".$lang->load_language());
 
class Login {

	public function __construct () {
	
			include ("config.php");
			include_once ("mysql.class.php");
			
			$this->sql = new MySQL ($db_host, $db_user, $db_pass, $db_name);
	}
	
	public function VarProtect ($content) {
		if (is_array ($content)) {
			foreach ($content as $key => $val)
				$content[$key] = mysql_real_escape_string (htmlentities (stripslashes ($content[$key])));
		}else{
			$content = mysql_real_escape_string (htmlentities ($content));
		}
	
		return (get_magic_quotes_gpc () ? stripslashes ($content) : $content);
	}
	
	public function is_admin($user, $pass) {
		
		$this->username = $this->VarProtect ($user);
		$this->password = $this->VarProtect ($pass);
		
		$query = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."users WHERE username='".$this->username."'");
		
		while ($ris = mysql_fetch_array ($query)) {
		
			if ($this->username == $ris['username'] && $this->password == $ris['password'])
				return TRUE;
			else
				return FALSE;
		}
	}
	
	public function send_login ($username, $password) {
	
		$this->username = $this->VarProtect($username);
		$this->password = md5($password);
		
		$this->login = $this->sql->sendQuery ("SELECT * FROM ".__PREFIX__."users WHERE username = '".$this->username."' LIMIT 1");
		
		while ($this->user = mysql_fetch_array ($this->login)) {
		
			if ($this->username == $this->user['username'] && $this->password == $this->user['password']) {			
			
				setcookie ("username", $this->username, time () + (3600 * 24), "/");
				setcookie ("password", $this->password, time () + (3600 * 24), "/");	
	
				return TRUE;
			}else{
				return FALSE;
			}
		}
	}
	
	public function logout($user, $pass) {
		
		$this->username = $this->VarProtect($user);
		$this->password = $this->VarProtect($pass);		
		
		if($this->is_admin($this->username, $this->password) == TRUE) {
			setcookie ("username", $this->username, time () - (3600 * 24), "/");
			setcookie ("password", $this->password, time () - (3600 * 24), "/");
			
			print "\n<script>window.location=\"index.php\";</script>";
		}else{
			die("<script>window.location=\"index.php\";</script>");
		}
	}
	
	public function form_login($user, $pass) {
	global $lang;
	
		if($this->is_admin($user, $pass) == FALSE) 
		{
			if(!empty($_POST['user']) && !empty($_POST['pass'])) 
			{	
				if($this->send_login($_POST['user'], $_POST['pass']) == FALSE)
					die("<div id=\"error\"><b>".$lang['login_1'].".</b><br /><br />\n<a href=\"admin.php\">".$lang['back']."</a></div>");
			}else{
			
				die(  "\n <fieldset>"
		   			. "\n<legend>Login</legend>"
					. "\n     <br /><p align=\"center\">"
					. "\n     <FORM action=\"admin.php\" method=\"POST\">"
					. "\n     Username :"
					. "\n     <INPUT type=\"text\" name=\"user\" Style=\"Color: #0044FF\"><br />"
					. "\n     Password :"
					. "\n     <INPUT type=\"password\" name=\"pass\" Style=\"Color: #0044FF; Font-Size: 11\"><br /><br />"
					. "\n     <INPUT type=\"submit\" value=\"Login\"> </p>"
					. "\n     </FORM><br />"
					. "\n </fieldset>"
					. "\n</div>");
			}
		}
	}
}
?>

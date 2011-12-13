<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file login.class.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */
 
include_once("language.class.php");

$lang = new Language();

include("languages/".$lang->load_language());

class Login extends Security {

	public function __construct () {
	
			include ("config.php");
			include_once ("mysql.class.php");
			
			$this->sql = new MySQL ($db_host, $db_user, $db_pass, $db_name);
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
			
				setcookie ("0xBlog_Username", $this->username, time () + (3600 * 24), "/");
				setcookie ("0xBlog_Password", $this->password, time () + (3600 * 24), "/");
				
				$this->token = $this->generate_token();
				
				$_SESSION['token'] = $this->token;	
	
				return TRUE;
			}else{
				return FALSE;
			}
		}
	}
	
	public function logout($user, $pass, $token) {
		
		$this->security_token($token, $_SESSION['token']);
		
		$this->username = $this->VarProtect($user);
		$this->password = $this->VarProtect($pass);		
		
		if($this->is_admin($this->username, $this->password) == TRUE) {
			setcookie ("0xBlog_Username", $this->username, time () - (3600 * 24), "/");
			setcookie ("0xBlog_Password", $this->password, time () - (3600 * 24), "/");
			
			header('Location: index.php');
			exit;
		}else{
			header('Location: index.php');
			exit;
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
					. "\n     <FORM action=\"admin.php\" method=\"POST\" name=\"0xBlog_Login\">"
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

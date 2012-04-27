<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file security.class.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */
 
class Security {

	public function VarProtect ($content) {
	
		$this->content = stripslashes ($content);
		
		if (is_array ($this->content)) {
			foreach ($this->content as $key => $val)
				$this->content[$key] = mysql_real_escape_string (htmlspecialchars ($this->content[$key]));
		}else{
			$this->content = mysql_real_escape_string (htmlspecialchars ($this->content));
		}
	
		//return (get_magic_quotes_gpc () ? stripslashes ($this->content) : $this->content);
		return $this->content;
	}
	
	public function generate_token () {
	
		$this->token = md5(rand(1,999999));
		
		return $this->token;
	}
	
	public function security_token($security, $token) {
		
		$this->security = $security;
		$this->token    = $token;
	
		if($this->security != $this->token)
			die("<div id=\"error\"><h2 align=\"center\">CSRF Attack Attemp!</h2></div>");	
	}
	
	public function my_is_numeric($text) {
		if(preg_match("/^[0-9]+$/",$text) == FALSE)
			die("<div id=\"error\"><h2 align=\"center\">Hacking Attemp!</h2></div>");
	}
	
	public function check_extension($file_name) {
		$ext = explode( ".", $file_name);
		return strtolower( $ext[ count( $ext ) - 1 ] );
	}
}
?>

<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file language.class.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */
 
class Language {

	public function __construct () {
	
			include ("config.php");
			include_once ("mysql.class.php");
			
			$this->sql = new MySQL ($db_host, $db_user, $db_pass, $db_name);
	}
	
	public function check_exist_language($lang) {
	
		$languages = scandir("languages/");
		
		if (in_array ($lang, $languages))
			return TRUE;
		else
			return FALSE;
	}
	
	public function load_language() {
	
		$this->config =  mysql_fetch_array($this->sql->sendQuery("SELECT lang FROM ".__PREFIX__."config"));
		
		if($this->check_exist_language($this->config['lang']) == TRUE)
			return $this->config['lang'];
		else
			return FALSE;
	}
	
	public function list_language() {
	
		$languages = scandir("languages/");
		
		foreach ($languages as $this->lang)
			if ($this->lang != "." && $this->lang != "..")
				print "\n<option value=\"".$this->lang."\">".$this->lang;
	}
}
?>

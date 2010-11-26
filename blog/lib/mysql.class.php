<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file mysql.class.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */

class MySQL {
	private $result = NULL;
	private $conn   = NULL;
	
	public function __construct ($db_host, $db_user, $db_pass, $db_name) {
	
		if (!$this -> conn = @mysql_connect ($db_host, $db_user, $db_pass)) {
			die ("Errore nella connessione al database!<br />".mysql_error());
		}
		
		if (!@mysql_select_db ($db_name, $this -> conn)) {
			die ("Errore nella selezione del Database: ".$db_name."!<br />".mysql_error());
		}
	}
	
	public function sendQuery ($query) {
		if (!$this -> result = @mysql_query ($query, $this -> conn)) {
			die ("SQL Error: ".mysql_error ());
		}else {
			return $this -> result;
		}
	}
	
	public function __destruct () {
		@mysql_close ($this -> _conn);
	}
}

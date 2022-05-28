<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file mysql.class.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */

class MySQL {
	private $result = NULL;
	private $conn   = NULL;
	
	public function __construct ($db_host, $db_user, $db_pass, $db_name) {
	
		if (!$this -> conn = @mysqli_connect ($db_host, $db_user, $db_pass, $db_name)) {
			die (mysqli_error());
		}
	}
	
	public function sendQuery ($query) {
		if (!$this -> result = @mysqli_query ($this -> conn, $query)) {
			die ("SQL Error: ");
		}else {
			return $this -> result;
		}
	}
	
	public function __destruct () {
		@mysqli_close ($this -> conn);
	}
}

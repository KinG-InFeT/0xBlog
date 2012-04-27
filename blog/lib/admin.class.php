<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file admin.class.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */
 
include_once("language.class.php");

$lang = new Language();

include("languages/".$lang->load_language());

class Admin extends Security  {

	public function __construct () {
	
			include ("config.php");
			include_once ("mysql.class.php");
			
			$this->sql = new MySQL ($db_host, $db_user, $db_pass, $db_name);
	}

	public function valid_mail($mail) {
		$this->mail = trim($mail);
		
		if(!$this->mail)
			return FALSE;
			
		$this->num_at = count(explode( '@', $this->mail )) - 1;
		
		if($this->num_at != 1)
			return FALSE;
		
		if(strpos($this->mail,';') || strpos($this->mail,',') || strpos($this->mail,' '))
			return FALSE;
		
		if(!preg_match( '/^[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}$/', $this->mail))
			return FALSE;
		
		return TRUE;
	}	
	
	public function show_administration() {
	global $lang;
		
		print "<h2 align=\"center\">".$lang['title_zone_admin']."</h2>\n";	
		
		print "\n<table style=\"border-collapse: collapse;\" border=\"2\" align=\"center\" cellpadding=\"10\" cellspacing=\"1\">"
			. "\n<tbody>"
			. "\n	<tr align=\"center\">"
			. "\n	  <td><b>".$lang['title']."</b></td>"
			. "\n	  <td><b>".$lang['author']."</b></td>"
			. "\n	  <td><b>".$lang['view_post']."</b></td>"
			. "\n	  <td><b>".$lang['comment']."</b></td>"
			. "\n	  <td><b>".$lang['date']."</b></td>"
			. "\n	  <td><b>".$lang['view']."</b></td>"
			. "\n	  <td><b>[".$lang['manage']."]</b></td>"
			. "\n	</tr>";
		
		$this->post = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."articles ORDER by id DESC");
		
		while($this->article = mysql_fetch_array($this->post)) {
		
			$this->comment  = $this->sql->sendQuery("SELECT blog_id FROM ".__PREFIX__."comments WHERE blog_id = '".$this->article['id']."'");
			$this->comments = mysql_fetch_row($this->comment);
			
			$this->manage = ($this->comments < 1) ? "" : "<a href=\"admin.php?action=manage_comments&id=".$this->comments[0]."\">[".$lang['manage']."]</a>";
			
			print "\n\t<tr>"
				. "\n	  <td>".$this->article['title']."</td>"
				. "\n	  <td>".$this->article['author']."</td>"
				. "\n	  <td><a href=\"viewpost.php?id=".$this->article['id']."\">[".$lang['view_post']."]</a></td>"
				. "\n	  <td>".mysql_num_rows($this->comment)."".$this->manage."</td>"
				. "\n	  <td>".$this->article['post_date']."</td>"
				. "\n	  <td>".number_format((int)$this->article['num_read'])."</td>"
				. "\n	  <td><a href=\"admin.php?action=del_post&id=".$this->article['id']."&security=".$_SESSION['token']."\">[X]</a> ~ <a href=\"admin.php?action=edit_post&id=".$this->article['id']."\">[".$lang['mod']."]</a></td>"
				. "\n	</tr>";
		}
		print " </tbody>\n"
			. "</table>\n"
			. "</div>\n";
	}
	
	/*
	 * BBCODES:
	 * [code] various_code[/code]
	 * [url=<url_path>]<url_name>[/url]
	 * [url]<url_path>[/url]
	 * [img]<link image>[/img]
	 * [youtube]<id_code_video>[/youtube]
	 * [b]<text>[/b]
	 * [i]<text>[/i]
	 * [u]<text>[/u]
	 * [center]<text>[/center]
	 */
	
	public function BBcode($text) {
	
		//$text = nl2br($text);
		$text = str_replace("\n","<br />",$text);
		
		
		//escape
		$text = str_replace("&egrave;","è",$text);
		$text = str_replace("&agrave;","à",$text);
		$text = str_replace("&quot;","\"",$text);
		$text = str_replace("&ugrave;","ù",$text);
		$text = str_replace("&Igrave;","ì",$text);
		$text = str_replace("&nbsp;"," ",$text);
		$text = str_replace("&euro;","€",$text);				
	
		/* Smile */
		$text = str_replace(":)", "<img alt=\":)\" src=\"img/01.jpg\">", $text);
		$text = str_replace(":D", "<img alt=\":D\" src=\"img/02.jpg\">", $text);
		$text = str_replace(";)", "<img alt=\";)\" src=\"img/03.jpg\" >", $text);
		$text = str_replace("^_^", "<img alt=\"^_^\" src=\"img/04.gif\">", $text);
		$text = str_replace(":(", "<img alt=\":(\" src=\"img/06.gif\">", $text);
		
		/* BBcode */
		$text = str_replace("[img]", "<img src=\"", $text);
		$text = str_replace("[/img]", "\"><!-- immagine -->", $text);
		$text = str_replace("[b]", "<b>", $text);
		$text = str_replace("[/b]", "</b>", $text);
		$text = str_replace("[i]", "<i>", $text);
		$text = str_replace("[/i]", "</i>", $text);
		$text = str_replace("[u]", "<u>", $text);
		$text = str_replace("[/u]", "</u>", $text);
		$text = str_replace("[center]", "<center>", $text);
		$text = str_replace("[/center]", "</center>", $text);
		$text = str_replace("[code]", "<div class=\"code\">Code:<hr /><pre>", $text);
		$text = str_replace("[/code]", "</pre></div><!-- code -->", $text);
		
		//Link BBcode
 		$search  = array(
 					"/\\[url\\](.*?)\\[\\/url\\]/is", 
 					"/\\[url\\=(.*?)\\](.*?)\\[\\/url\\]/is", 
 					"/\\[youtube\\](.*?)\\[\\/youtube\\]/is"
 				);
 				
    	$replace = array(
    				"<a target=\"_blank\" href=\"$1\">$1</a>", 
    				"<a target=\"_blank\" href=\"$1\">$2</a>", 
    				"<br /><iframe title=\"YouTube video player\" width=\"480\" height=\"390\" src=\"http://www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>"
				);
				
    	//if(preg_match("#(?:http://)?(?:www.)?youtube.(?:com|it)/(?:watch?v=|v/)(.{11})#i", $text, $parte)) {
    	//	$id_code_yt = $parte[1];
    	//}
    	
 		$text = preg_replace ($search, $replace, $text);

		return $text;
	}
	
	public function add_post() {
	global $lang;
	
		print "<h2 align=\"center\">".$lang['title_new_article']."</h2><br />\n";
	
		if (!empty($_POST['author']) && !empty($_POST['title']) && !empty($_POST['article'])) {
			
			$this->security_token($_POST['security'], $_SESSION['token']);
			
		    $this->date    = @date('d/m/y');
		    $this->article = $this->VarProtect( $_POST['article']  );
		    $this->title   = $this->VarProtect( $_POST['title']    );
		    $this->author  = $this->VarProtect( $_POST['author']   );
   		    $this->cat_id  = $this->VarProtect( $_POST['category'] );
		
		    $this->sql->sendQuery("INSERT INTO ".__PREFIX__."articles (post, title, author, post_date, num_read, cat_id
		        						) VALUES (
		        					'".$this->article."', '".$this->title."', '".$this->author."',  '".$this->date."', 0, '".$this->cat_id."')");
		
		    print "<script>alert(\"".$lang['add_article_success']."\");</script>";
		    header('Location: admin.php');
		}else{
			$this->cat = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."categories");
			
		    //Visualizzo la form
			print '<form action="admin.php?action=add_post" method="POST">
			    '.$lang['author'].':<br />
    	      	    <input type="text" name="author" value="'.htmlspecialchars($_COOKIE['0xBlog_Username']).'"/><br /><br />
    	      	    '.$lang['title'].':<br />
    	      	    <input type="text" name="title" /><br /><br />
    	      	    '.$lang['associate_category'].'<br />
    	      	    <select name="category">';
    	      	    
    	      	    while($this->category = mysql_fetch_array($this->cat))
    	      	    	print "\n<option value=\"".$this->category['cat_id']."\">".$this->category['cat_name']."</option>";
    	      	    
    	    print ' </select>
    	    		<br /><br />							
					BBcode:<br />
					* [code] various_code [/code]<br />
					* [url= url_path ] url_name [/url]<br />
					* [url] url_path [/url]<br />
					* [img] url_img [/img]<br />
					* [youtube] id_code_video [/youtube] ( http://www.youtube.com/watch?v=<b>8UFIYGkROII</b> ) <br />
					* [b] text [/b]<br />
					* [i] text [/i]<br />
					* [u] text [/u]<br />
					* [center] text [/center]<br />
					<br />
					'.$lang['article'].':<br />
					<img src="img/01.jpg" alt="sorriso" onclick="document.getElementById(\'article\').value+=\' :) \'">
					<img src="img/02.jpg" alt="felicemente" onclick="document.getElementById(\'article\').value+=\' :D \'">
					<img src="img/03.jpg" alt="ok" onclick="document.getElementById(\'article\').value+=\' ;) \'">
					<img src="img/04.gif" alt="felice" onclick="document.getElementById(\'article\').value+=\' ^_^ \'">
					<img src="img/06.gif" alt="triste" onclick="document.getElementById(\'article\').value+=\' :( \'">
					<br />
    	      	    <textarea id="article" name="article" cols="90" rows="25"></textarea><br /><br />
    	      	    <input type="submit" value="'.$lang['send_new_article'].'" />
    	      	    <input type="hidden" name="security" value="'.$_SESSION['token'].'" />
    	      	    <br /><br /></form>';
		}
	}
	
	public function manage_comments($id) {
	global $lang;
		
		$this->id = (int) $id;
		
		$this->my_is_numeric($this->id);
				
		if(empty($this->id))
			die("<div id=\"error\"><h2 align=\"center\">".$lang['id_not_exist']."</h2></div>");
		
		print "<h2 align=\"center\">".$lang['title_comments']."</h2><br />\n";
		
		$this->comments = $this->sql->sendQuery("SELECT id, blog_id, name, comment, ip FROM ".__PREFIX__."comments WHERE blog_id = '".$this->id."'");
		
		if(mysql_num_rows($this->comments) < 1) {
			print "<p><b>".$lang['no_comment']."</b></p>";
		}else{
			print '<table style="border-collapse: collapse;" border="2" align="center" cellpadding="10" cellspacing="1">
			<tbody>
				<tr>
				  <td><center>'.$lang['name'].'</center></td>
				  <td><center>'.$lang['commit'].'</center></td>
				  <td><center>IP</center></td>
				  <td><center>[#]</center></td>
				</tr>';	
				
			while($this->comment = mysql_fetch_array($this->comments)) {
				print "\n<form action='admin.php?action=del_comment' method='POST'>";	
				print "\n<tr>"
					  . "\n<td>".htmlspecialchars($this->comment['name'])."</td>"
					  . "\n<td>".htmlspecialchars($this->comment['comment'])."</td>"
					  . "\n<td>".$this->comment['ip']."</td>"
					  . "\n<td><input type='submit' value='".$lang['delete']."'/></td>"
					. "\n</tr>";
				print "\n<input type='hidden' name='id' value='".(int) $this->comment['id']."'>";
				print "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />";
				print "\n</form>";
			}
			echo "\n</tbody>\n</table>\n";
		}
	}
	
	public function del_comment($id) {
	global $lang;
			
		$this->id = (int) $id;
		
		if(empty($this->id))
			die("<div id=\"error\"><h2 align=\"center\">".$lang['id_not_exist']."</h2></div>");
			
		$this->security_token($_POST['security'], $_SESSION['token']);
		
		$this->sql->sendQuery("DELETE FROM ".__PREFIX__."comments WHERE id = '".$this->id."'");
		
		print '<script>window.location="admin.php?action=manage_comment";</script>';
	}
	
	public function del_post($id) {
	global $lang;
	
		$this->id = (int) $id;
		
		print "<h2 align=\"center\">".$lang['title_del_article']."</h2><br />\n";
		
		if(empty($this->id)) {
			print "\n<br /><br /><form method=\"POST\" action=\"admin.php?action=del_post\" />\n"
				. $lang['inserit_article_id'].": <input type=\"text\" name=\"id\" />\n"
				. "<br /><input type=\"submit\" value=\"".$lang['delete_art']."\" />\n"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "</form>";
		}else{
			$this->security_token($_REQUEST['security'], $_SESSION['token']);
			
			$this->sql->sendQuery("DELETE FROM ".__PREFIX__."articles WHERE id = '".$this->id."'");
			$this->sql->sendQuery("DELETE FROM ".__PREFIX__."comments WHERE blog_id = '".$this->id."'");

			die(header('Location: admin.php'));
		}
	}
	
	public function clear_blog() {
	global $lang;
	
		print "<h2 align=\"center\">".$lang['title_reset_blog']."</h2><br />\n";
		
   		print "\n<br />";
      	print "\n<form method=\"POST\" action=\"admin.php?action=clear_blog\" />"
	       	. "\n".$lang['reset_init']."<br />"
			. "\n# ".$lang['reset_init_2'].".<br />"
			. "\n# ".$lang['reset_init_3'].".<br />"
			. "\n# ".$lang['reset_init_4']."<br /><br /><br />"
        	. "\n<select name=\"scelta\">"
       		. "\n\t<option value=\"no\">NO</option>"
			. "\n\t<option value=\"si\">".$lang['si']."</option>"
			. "\n</select>"
        	. "\n<input type=\"submit\" name=\"invia\" value=\"".$lang['send']."\" />"
        	. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
        	. "\n</form>";
        		
       	$scelta = @$_POST['scelta'];
       	
       	if(isset($scelta))  {
       		if($scelta == 'si') {
       		
       			$this->security_token($_POST['security'], $_SESSION['token']);
       			
       			$this->sql->sendQuery("TRUNCATE TABLE ".__PREFIX__."articles");
       			$this->sql->sendQuery("TRUNCATE TABLE ".__PREFIX__."comments");
				$this->sql->sendQuery("TRUNCATE TABLE ".__PREFIX__."categories");
       			$this->sql->sendQuery("UPDATE ".__PREFIX__."config SET themes = 'default.css'");
       			
       			print "\n<p>TRUNCATE TABLE ".__PREFIX__."articles: <font color='green'>Success</font><br />\n"
       			    . "TRUNCATE TABLE ".__PREFIX__."comments: <font color='green'>Success</font>\n"
       			    . "TRUNCATE TABLE ".__PREFIX__."categories: <font color='green'>Success</font>\n"
       			    . "<br />UPDATE ".__PREFIX__."config SET themes to default.css: <font color='green'>Success</font>\n"       			    
       			    . "<br /><br /><u><a href='admin.php'>".$lang['title_zone_admin']."</a></u></p>\n";
       		}else{
       			print '<script>window.location="admin.php";</script>';
       		}
       	}
	}
	
	public function settings() {
	global $lang;
	
		print "<h2 align=\"center\">".$lang['title_settings']."</h2><br />\n";
		
		include_once("lib/language.class.php");
		$lang_tool = new Language();
	
		if(!empty($_POST['title'])  && 
		   !empty($_POST['desc'])   && 
		   !empty($_POST['lang'])   && 
		   !empty($_POST['limit'])  && 
		   !empty($_POST['footer'])
		  ) {
			
			$this->security_token($_POST['security'], $_SESSION['token']);
			
			$this->title  = $this->VarProtect( $_POST['title']  );
			$this->desc   = $this->VarProtect( $_POST['desc']   );
			$this->lang   = $this->VarProtect( $_POST['lang']   );
			$this->limit  =           intval ( $_POST['limit']  );
			$this->footer = $this->VarProtect( $_POST['footer'] );						
			$this->log_ip = 		  intval ( $_POST['log_ip'] );	
			 
			if($lang_tool->check_exist_language($this->lang) == FALSE)
				die('<script>alert("'.$lang['lang_not_exist'].'"); window.location="admin.php?action=settings";</script>');
			
			$this->sql->sendQuery("UPDATE `".__PREFIX__."config` SET 
									`title` = '".$this->title."', 
									`description` = '".$this->desc."', 
									`lang` = '".$this->lang."',
									`limit` = '".$this->limit."',
									`footer` = '".$this->footer."',
									`ip_log_active` = '".$this->log_ip."' LIMIT 1 ;");
			
			print "<script>alert(\"".$lang['setting_success'].".\"); window.location=\"admin.php?action=settings\";</script>";
		
		}else{
			$this->config = mysql_fetch_array($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."config"));
			
			print "\n<h2 align=\"center\">".$lang['setting']."</h2>"
				. "\n<br /><br />"
				. "\n<form method=\"POST\" action=\"admin.php?action=settings\" />"
				. "\n<table style=\"text-align: left;\" border=\"0\" cellpadding=\"2\" width=\"100%\" cellspacing=\"2\">"
				. "\n<tbody>"
				. "\n<tr>"
				. "\n	<td>".$lang['setting_title'].":</td>"
				. "\n	<td><input type=\"text\" name=\"title\" value=\"".$this->config['title']."\" /></td>"
				. "\n</tr>"
				. "\n<tr>"
				. "\n	<td>".$lang['setting_desc'].":</td>"
				. "\n	<td><input type=\"text\" name=\"desc\" value=\"".$this->config['description']."\" /></td>"
				. "\n</tr>"
				. "\n<tr>"
				. "\n	<td>".$lang['change_lang'].":</td>"
				. "\n	<td>"
				. "\n<select name='lang'>";
				
				$lang_tool->list_language();
					
			print "\n</select>"
				. "\n</td>"
				. "\n</tr>"
				. "\n<tr>"
				. "\n	<td>".$lang['setting_limit'].":</td>"
				. "\n	<td><input type=\"text\" name=\"limit\" value=\"".$this->config['limit']."\" /></td>"
				. "\n</tr>"	
				. "\n<tr>"
				. "\n	<td>".$lang['setting_footer'].":</td>"
				. "\n	<td><input type=\"text\" name=\"footer\" value=\"".$this->config['footer']."\" /></td>"
				. "\n</tr>"		
				. "\n<tr>"
				. "\n	<td>Log IP:</td>"
				. "\n	<td><input type=\"radio\" name=\"log_ip\" value=\"1\" checked=\"checked\"> Anable <br />"
				. "\n	<input type=\"radio\" name=\"log_ip\" value=\"0\"> Disable</td>"
				. "\n</tr>"	
				. "\n</tbody>"
				. "\n</table>"
				. "\n<br /><input type=\"submit\" value=\"".$lang['send']."\" />"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>"
				."";
		}	
	}
	
	public function check_user_exist($user) {
		
		$this->user = $this->VarProtect( $user );
		
		$this->check = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."users WHERE username = '".$this->user."' limit 1;");
		
		if(mysql_num_rows($this->check) == 1)
			return TRUE;
		else
			return FALSE;
	}
	
	public function check_email_exist($email) {
		
		$this->email = $this->VarProtect( $email );
		
		$this->check = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."users WHERE email = '".$this->email."' limit 1;");
		
		if(mysql_num_rows($this->check) == 1)
			return TRUE;
		else
			return FALSE;
	}
	
	public function add_admin() {
	global $lang;
	
		print "<h2 align=\"center\">".$lang['title_add_admin']."</h2><br />\n";
	
		if(!empty($_POST['nick']) && !empty($_POST['pass']) && !empty($_POST['pass_check']) && !empty($_POST['email'])) {
		
			$this->security_token($_POST['security'], $_SESSION['token']);
			
			$this->nick       = $this->VarProtect( $_POST['nick']       );
			$this->pass       = $this->VarProtect( $_POST['pass']       );
			$this->pass_check = $this->VarProtect( $_POST['pass_check'] );						
			$this->email      = $this->VarProtect( $_POST['email']      );

			if($this->check_user_exist($this->nick) == TRUE)
				die("<script>alert(\"".$lang['nick_exist'].".\");location.href = 'admin.php?action=add_admin';</script>");

			if($this->pass != $this->pass_check)
				die("<script>alert(\"".$lang['pass_not_equal'].".\");location.href = 'admin.php?action=add_admin';</script>");

			if($this->check_email_exist($this->email) == TRUE) 
				die("<script>alert(\"".$lang['email_exist'].".\");location.href = 'admin.php?action=add_admin';</script>");

			if($this->valid_mail($this->email) == FALSE)
				die("<script>alert(\"".$lang['email_not_valide'].".\");location.href = 'admin.php?action=add_admin';</script>");
			
			$this->sql->sendQuery("INSERT INTO ".__PREFIX__."users (username, password, email) VALUES ('".$this->nick."', '".md5($this->pass)."', '".$this->email."')");
			
			print "<script>alert('Account ".$this->nick." ".$lang['account_create']."!'); location.href = 'admin.php';</script>";
			
			exit;
			
		}else{
				print "\n<br /><br />"
				. "\n<form method=\"POST\" action=\"admin.php?action=add_admin\" />"
				. "\n<table style=\"text-align: left;\" border=\"0\" cellpadding=\"2\" width=\"100%\" cellspacing=\"2\">"
				. "\n<tbody>"
				. "\n<tr>"
				. "\n	<td>".$lang['nick']."</td>"
				. "\n	<td><input type=\"text\" name=\"nick\" /></td>"
				. "\n</tr>"
				. "\n<tr>"
				. "\n	<td>Password</td>"
				. "\n	<td><input type=\"password\" name=\"pass\" /></td>"
				. "\n</tr>"
				. "\n<tr>"
				. "\n	<td>Password (".$lang['again'].")</td>"
				. "\n	<td><input type=\"password\" name=\"pass_check\" /></td>"
				. "\n</tr>"				
				. "\n<tr>"
				. "\n	<td>Email:</td>"
				. "\n	<td><input type=\"text\" name=\"email\" /></td>"
				. "\n</tr>"			
				. "\n</tbody>"
				. "\n</table>"
				. "\n<input type=\"submit\" value=\"".$lang['send']."\" />"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>"
				."";
		}
	}
	
	public function del_admin($id) {
	global $lang;
		
		$this->id = intval($id);
		
		print "<h2 align=\"center\">".$lang['title_del_admin']."</h2><br />\n";
		
		if(empty($this->id)) {
		
			print "\n<form method = \"POST\" action=\"admin.php?action=del_admin\" />\n"
				. "\n<b>".$lang['list_admins'].": </b><br />"
				. "\n<select name = \"a_id\">\n";
					
			$this->query = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."users");
			
			while ($this->users = mysql_fetch_array ($this->query , MYSQL_ASSOC)) {
			
				$this->a_id   = $this->users['id'];
				$this->a_user = $this->users['username'];
				
				if(strtolower($_COOKIE['0xBlog_Username']) != strtolower($this->a_user))
					print "\n<option value = \"".$this->a_id."\">".$this->a_user."</option>";
			}
			print "\n</select>"
				. "\n<input type = \"submit\" value = \"".$lang['delete']."\">"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>";
		}else{
			$this->security_token($_POST['security'], $_SESSION['token']);
			
			$this->sql->sendQuery("DELETE FROM ".__PREFIX__."users WHERE id = '".$this->id."'");		
			print "<script>alert('Account ".$this->a_user." ".$lang['delete']."!'); location.href = 'admin.php?action=del_admin';</script>";
		}
	}
	
	public function updates($version) {
	global $lang;

		print "<h2 align=\"center\">".$lang['title_update']."</h2><br />\n";	
		
		$update = NULL;
		
		if ($fsock = @fsockopen('www.0xproject.netsons.org', 80, $errno, $errstr, 10)) {
			@fputs($fsock, "GET /versions/0xBlog.txt HTTP/1.1\r\n");
			@fputs($fsock, "HOST: www.0xproject.netsons.org\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");
	
			$get_info = FALSE;
			
			while (!@feof($fsock)) {
				if ($get_info)
					$update .= @fread($fsock, 1024);
				else
					if (@fgets($fsock, 1024) == "\r\n")
						$get_info = TRUE;
			}
			
			@fclose($fsock);
			
			$update = htmlspecialchars($update);
			
			$update1  = str_replace(".", "", $update);
			$version1 = str_replace(".", "", $version);
	
			if ($version1 >= $update1)
				$version_info = "<p style=\"color:green\">".$lang['update_1'].".</p><br />";
			else
				$version_info = "\n<p style=\"color:red\">".$lang['update_2'].".<br />\n".$lang['update_3'].": ". $update."\n"
							  . "<br /><br />Link Download: <a href=\"http://0xproject.netsons.org/#0xBlog\">".$lang['update_4']."</a><br />\n";
		}else{
			if ($errstr)
				$version_info = '<p style="color:red">' . sprintf("".$lang['update_5'].":<br />%s", $errstr) . '</p>';
			else
				$version_info = '<p>'.$lang['update_6'].'.</p>';
		}
		
		return ("<br /><br /><big><big>".$version_info."</big></big>");
	}
	
	public function themes() {
	global $lang;
		
		print "<h2 align=\"center\">".$lang['title_theme']."</h2><br />\n";	
		
		print '<font color="green">'.$lang['theme_1'].':<br /></font><br />';
		
		$themes = scandir("themes/");
		
		if (!empty($_GET['select'])) {				
			if (in_array ($_GET['select'], $themes)) {
			
				$this->security_token($_GET['security'], $_SESSION['token']);
				
				$this->sql->sendQuery("UPDATE ".__PREFIX__."config SET themes = '".$this->VarProtect($_GET['select'])."';");
				
				print "<script>alert(\"".$lang['theme_changed']."\"); window.location.href = 'admin.php?action=themes';</script>";
			}else {
				die ("<script>alert(\"".$lang['theme_not_found']."\"); window.location.href = 'admin.php?action=themes';</script>");
			}
		}else{
			foreach ($themes as $theme)
				if ($theme != "." && $theme != "..")
					print "\n". $theme ." <a href = 'admin.php?action=themes&select=".$theme."&security=".$_SESSION['token']."'>".$lang['select']."</a> ~ <a href=\"admin.php?action=edit_theme&theme_name=".$theme."\">".$lang['send_edit']."</a><br />";
		}
	}
	
	
	public function edit_theme($theme_name) {
	global $lang;
	
		$this->theme_name = "themes/".htmlspecialchars(stripslashes($theme_name));
		
		//Fix #14 BUG
		$ext = $this->check_extension($this->theme_name);
		if($ext != 'css')
			die("<div id=\"error\"><h2 align=\"center\">".$lang['ext_not_validate']."</h2></div>");
		
		if(!file_exists($this->theme_name))
			die("<div id=\"error\"><h2 align=\"center\">".$lang['theme_not_found']."</h2></div>");
			
		print "<h2 align=\"center\">".$lang['title_edit_theme']."</h2><br />\n";	
		
		if ( !empty($_POST['send']) && 
		    ($_POST['send'] == 1)   && 
		    !empty($_POST['theme_file']) &&
			file_exists("themes/" . $_POST['theme_file'])
		   ) {
		
			//Fix #14 BUG
			$ext = $this->check_extension($_POST['theme_file']);
			if($ext != 'css')
				die("<div id=\"error\"><h2 align=\"center\">".$lang['ext_not_validate']."</h2></div>");
		

			$this->security_token($_POST['security'], $_SESSION['token']);

			$scrivi_file = fopen($this->theme_name,"w");
			fwrite($scrivi_file,htmlspecialchars(stripslashes($_POST['theme_file']))) or die("Error writing file:".$this->theme_name);
			fclose($scrivi_file);
				
			print "<script>alert(\"".$lang['theme_edited']."\"); window.location.href = 'admin.php?action=themes';</script>";

		}else{
			
			//Fix #14 BUG
			$ext = $this->check_extension($this->theme_name);
			if($ext != 'css')
				die("<div id=\"error\"><h2 align=\"center\">".$lang['ext_not_validate']."</h2></div>");
				
			$leggi_file  = fopen($this->theme_name,"r");
			$dim_file    = filesize($this->theme_name);
			
			$this->theme_file = fread($leggi_file,$dim_file);
			
			fclose($leggi_file);
			
			print "\n<form method=\"POST\" action=\"admin.php?action=edit_theme\" />"
				. "\n<p align=\"center\">Theme File:<br />"
				. "\n<textarea name=\"theme_file\" rows=\"25\" cols=\"90\">".$this->theme_file."</textarea><br />"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n<input type=\"hidden\" name=\"theme_name\" value=\"".htmlspecialchars($theme_name)."\" />"
				. "\n<input type=\"hidden\" name=\"send\" value=\"1\" />"
				. "\n<input type=\"submit\" value=\"".$lang['send_edit']."\" /></p>"
				. "\n</form>"
				. "";
		}
	}
	
	public function change_pass_admin($id) {
	global $lang;
			
		$this->id = (int) $id;
		
		print "<h2 align=\"center\">".$lang['title_change_pass']."</h2><br />\n";
		
		if(empty($this->id)) {
		
			print "\n<form method = \"POST\" action=\"admin.php?action=change_pass_admin\" />\n"
				. "\n<b>".$lang['list_admins'].": </b><br />"
				. "\n<select name = \"a_id\">\n";
					
			$this->query = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."users");
			
			while ($this->users = mysql_fetch_array ($this->query , MYSQL_ASSOC)) {
			
				$this->a_id   = $this->users['id'];
				$this->a_user = $this->users['username'];
				
				print "\n<option value = \"".$this->a_id."\">".$this->a_user."</option>";
			}
			print "\n</select>"
				. "\n<input type = \"submit\" value = \"".$lang['select']."\">"
				. "\n</form>";
		}else{
			$this->admin = mysql_fetch_array($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."users WHERE id = '".$this->id."'"));
			
			print "\n<form method = \"POST\" action=\"admin.php?action=change_pass_admin\" />\n"
				. "\nAdmin: <b>".$this->admin['username']."</b><br />"
				. "\nPassword: <input type=\"password\" name=\"new_pass\" /><br />"
				. "\n<input type=\"hidden\" name=\"a_id\" value=\"".$this->admin['id']."\" />"
				. "\n<input type=\"hidden\" name=\"a_user\" value=\"".$this->admin['username']."\" />"
				. "\n<input type=\"submit\" value=\"".$lang['change_pass_admin']."\" />"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>";
			
			if(!empty($_POST['new_pass'])) {
				$this->security_token($_POST['security'], $_SESSION['token']);
				
				$this->sql->sendQuery("UPDATE ".__PREFIX__."users SET password = '".md5($_POST['new_pass'])."' WHERE id = '".$this->id."'");		
				print "<script>alert('Account ".$this->VarProtect($_POST['a_user'])." ".$lang['pass_changed']."'); location.href = 'admin.php?action=change_pass_admin';</script>";
			}
		}
	}
	
	public function edit_post($id) {
	global $lang;
		
		$this->id = intval($id);
	
		print "<h2 align=\"center\">".$lang['title_edit_article']."</h2><br />\n";
		
		if(empty($this->id)) {
			print "\n<form method = \"POST\" action=\"admin.php?action=edit_post\" />\n"
				. "\n".$lang['edit_article_ins'].": <input type=\"text\" name=\"id\" /><br />"
				. "\n<input type=\"submit\" value=\"".$lang['send']."\" /></form>";
		}else{
		
	    	if(mysql_num_rows($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."articles WHERE id = '".$this->id."'")) < 1)
				die("<script>alert(\"".$lang['article_not_exist'].".\");location.href = 'admin.php?action=edit_post';</script>");
	    		
			if (!empty($_POST['author']) && !empty($_POST['title']) && !empty($_POST['article'])) {
					
					$this->security_token($_POST['security'], $_SESSION['token']);
					
			        $this->date    = @date('d/m/y');
			        $this->article = $this->VarProtect( $_POST['article']);
			        $this->title   = $this->VarProtect( $_POST['title']  );
			        $this->author  = $this->VarProtect( $_POST['author'] );
			        $this->cat_id  = (int) $_POST['category'];
			
			        $this->sql->sendQuery("UPDATE ".__PREFIX__."articles SET post = '".$this->article."', title = '".$this->title."', author = '".$this->author."', cat_id = '".$this->cat_id."' WHERE id = '".$this->id."'");
			
			        print "<script>alert(\"".$lang['edit_success']."!\");</script>";
			        print '<script>window.location="admin.php";</script>';
			    }else{
			    	$this->data_article = mysql_fetch_array($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."articles WHERE id = '".$this->id."'"));
			    	
    				$this->cat = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."categories");
    				
					print '<form action="admin.php?action=edit_post&id='.$this->id.'" method="POST">
						    '.$lang['author'].':<br />
    			            <input type="text" name="author" value="'.$this->data_article['author'].'"/><br /><br />
    			            '.$lang['title'].':<br />
    			            <input type="text" name="title" value="'.$this->data_article['title'].'" /><br />
    			            <br />
    			      	    '.$lang['associate_category'].'<br />
    			      	    <select name="category">';
    			      	    
    			      	    $this->cat_name = mysql_fetch_array($this->sql->sendQuery("SELECT cat_name FROM ".__PREFIX__."categories WHERE cat_id = ".$this->data_article['cat_id'].""));
    			      	    
		      	   print "\n<option value=\"".$this->data_article['cat_id']."\">".$this->cat_name['cat_name']."</option>";
    		      	    
    			      	    while($this->category = mysql_fetch_array($this->cat))
    			      	    	print "\n<option value=\"".$this->category['cat_id']."\">".$this->category['cat_name']."</option>";
    	      	    
		    	    print ' </select><br />							
							BBcode:<br />
							* [code] various_code [/code]<br />
							* [url= url_path ] url_name [/url]<br />
							* [url] url_path [/url]<br />
							* [img] url_img [/img]<br />
							* [youtube] id_code_video [/youtube] ( http://www.youtube.com/watch?v=<b>8UFIYGkROII</b> ) <br />
							* [b] text [/b]<br />
							* [i] text [/i]<br />
							* [u] text [/u]<br />
							* [center] text [/center]<br />
							<br />
    			            '.$lang['article'].':<br />
							<img src="img/01.jpg" alt="sorriso" onclick="document.getElementById(\'article\').value+=\' :) \'">
							<img src="img/02.jpg" alt="felicemente" onclick="document.getElementById(\'article\').value+=\' :D \'">
							<img src="img/03.jpg" alt="ok" onclick="document.getElementById(\'article\').value+=\' ;) \'">
							<img src="img/04.gif" alt="felice" onclick="document.getElementById(\'article\').value+=\' ^_^ \'">
							<img src="img/06.gif" alt="triste" onclick="document.getElementById(\'article\').value+=\' :( \'">
							<br />
							<textarea id="article" name="article" cols="90" rows="25">'.$this->data_article['post'].'</textarea><br /><br />
    			            <input type="submit" value="'.$lang['send_edit'].'" />
    			            <br /><br />
							<input type="hidden" name="security" value="'.$_SESSION['token'].'" />
    			            </form>';
			}
		}
	}

	public function add_category() {
	global $lang;
	
		print "<h2 align=\"center\">".$lang['title_add_category']."</h2><br />\n";
	
		if(!empty($_POST['cat_name'])) {
	
			$this->security_token($_POST['security'], $_SESSION['token']);
		
			$this->cat_name = $this->VarProtect( $_POST['cat_name'] );
			
			$this->sql->sendQuery("INSERT INTO ".__PREFIX__."categories (`cat_name`) VALUES ('".$this->cat_name."')");
			
			print "<script>alert(\"".$lang['cat_add_success']."!\");</script>";
			
			header('Location: admin.php');
		
		}else{
	
			print "\n<br /><br />"
				. "\n<form method=\"POST\" action=\"admin.php?action=add_category\" />"
				. "\n<table style=\"text-align: left;\" border=\"0\" cellpadding=\"2\" width=\"100%\" cellspacing=\"2\">"
				. "\n<tbody>"
				. "\n<tr>"
				. "\n	<td>".$lang['cat_name'].":</td>"
				. "\n	<td><input type=\"text\" name=\"cat_name\" /></td>"
				. "\n</tr>"
				. "\n</tbody>"
				. "\n</table>"
				. "\n<input type=\"submit\" value=\"".$lang['send']."\" />"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>"
				."";
	
		}
	}
	
	public function edit_category($id) {
	global $lang;
			
		$this->id = (int) $id;
		
		print "<h2 align=\"center\">".$lang['title_edit_category']."</h2><br />\n";
		
		if(empty($this->id)) {
		
			print "\n<form method = \"POST\" action=\"admin.php?action=edit_category\" />\n"
				. "\n<b>".$lang['list_categories'].": </b><br />"
				. "\n<select name = \"cat_id\">\n";
					
			$this->query = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."categories");
			
			while ($this->cat = mysql_fetch_array ($this->query , MYSQL_ASSOC)) {
			
				$this->cat_id   = $this->cat['cat_id'];
				$this->cat_name = $this->cat['cat_name'];

				print "\n<option value = \"".$this->cat_id."\">".$this->cat_name."</option>";
			}
			print "\n</select>"
				. "\n<input type = \"submit\" value = \"".$lang['select']."\">"
				. "\n</form>";
		}else{
			$this->cat = mysql_fetch_array($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."categories WHERE cat_id = '".$this->id."'"));
			
			print "\n<form method = \"POST\" action=\"admin.php?action=edit_category\" />\n"
				. "\nCategory: <input type=\"text\" name=\"new_cat\" value=\"".$this->cat['cat_name']."\" /><br />"
				. "\n<input type=\"hidden\" name=\"cat_id\" value=\"".$this->id."\" />"
				. "\n<input type=\"submit\" value=\"".$lang['change_name_cat']."\" />"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>";
			
			if(!empty($_POST['new_cat'])) {
				$this->security_token($_POST['security'], $_SESSION['token']);
				
				$this->sql->sendQuery("UPDATE ".__PREFIX__."categories SET cat_name = '".$this->VarProtect($_POST['new_cat'])."' WHERE cat_id = '".$this->id."'");		
				print "<script>alert('".$lang['cat_edit_success']."'); location.href = 'admin.php?action=edit_category';</script>";
			}
		}
	}
	
	public function del_category($id) {
	global $lang;
	
		$this->id = (int) $id;
		
		print "<h2 align=\"center\">".$lang['title_del_category']."</h2><br />\n";
	
		if(empty($this->id)) {
		
			print "\n<form method = \"POST\" action=\"admin.php?action=del_category\" />"
				. "\n<b>".$lang['list_categories'].": </b><br />"
				. "\n<select name = \"cat_id\">";
					
			$this->query = $this->sql->sendQuery("SELECT * FROM ".__PREFIX__."categories");
			
			while ($this->cat = mysql_fetch_array ($this->query , MYSQL_ASSOC)) {
			
				$this->cat_id   = $this->cat['cat_id'];
				$this->cat_name = $this->cat['cat_name'];
				
				$this->num_art_for_cat = mysql_num_rows($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."articles WHERE cat_id = '".$this->cat_id."'"));
				
				print "\n<option value = \"".$this->cat_id."\">".$this->cat_name." (".$this->num_art_for_cat.")</option>";
			}
			
			print "\n</select>"
				. "\n<input type = \"submit\" value = \"".$lang['delete']."\">"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "\n</form>";
		}else{
			$this->security_token($_POST['security'], $_SESSION['token']);
			
			$this->sql->sendQuery("DELETE FROM ".__PREFIX__."categories WHERE cat_id = '".$this->id."'");		
			print "<script>alert('".$lang['cat_del_success']."!'); location.href = 'admin.php?action=del_category';</script>";
		}
	}
}	
?>		

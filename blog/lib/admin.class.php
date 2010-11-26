<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file admin.class.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
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
			. "\n	  <td><b>[".$lang['manage']."]</b></td>"
			. "\n	</tr>";
		
		$this->post = $this->sql->sendQuery("SELECT id, author, title, post, post_date FROM ".__PREFIX__."articles ORDER by id DESC");
		
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
				. "\n	  <td><a href=\"admin.php?action=del_post&id=".$this->article['id']."\">[X]</a> ~ <a href=\"admin.php?action=edit_post&id=".$this->article['id']."\">[".$lang['mod']."]</a></td>"
				. "\n	</tr>";
		}
		print " </tbody>\n"
			. "</table>\n"
			. "</div>\n";
	}
	
	/*
	 * BBCODES:
	 * [img]<image_path>[/img]
	 * [url=<url_path>]<url_name>[/url]
	 * [url]<url_path>[/url]
	 * [img]<link image>[/img]
	 * [b]<text>[/b]
	 * [i]<text>[/i]
	 * [u]<text>[/u]
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
		
 		$search  = array("/\\[url\\](.*?)\\[\\/url\\]/is", "/\\[url\\=(.*?)\\](.*?)\\[\\/url\\]/is");
    	$replace = array("<a target=\"_blank\" href=\"$1\">$1</a>", "<a target=\"_blank\" href=\"$1\">$2</a>");
 	
    	$text = preg_replace ($search, $replace, $text);

		return $text;
	}
	
	public function add_post() {
	global $lang;
	
		print "<h2 align=\"center\">".$lang['title_new_article']."</h2><br />\n";
	
		if (!empty($_POST['author']) && !empty($_POST['title']) && !empty($_POST['article'])) {
			
			$this->security_token($_POST['security'], $_SESSION['token']);
			
		    $this->date    = @date('d/m/y');
		    $this->article = $this->VarProtect( $_POST['article']);
		    $this->title   = $this->VarProtect( $_POST['title']  );
		    $this->author  = $this->VarProtect( $_POST['author'] );
		
		    $this->sql->sendQuery("INSERT INTO ".__PREFIX__."articles (post, title, author,post_date
		        						) VALUES (
		        					'".$this->article."', '".$this->title."', '".$this->author."',  '".$this->date."')");
		
		    print "<script>alert(\"".$lang['add_article_success']."\");</script>";
		    print '<script>window.location="admin.php";</script>';
		}else{
		    	//Visualizzo la form
			print '<form action="admin.php?action=add_post" method="POST">
			    '.$lang['author'].':<br />
    	      	    <input type="text" name="author" value="'.htmlspecialchars($_COOKIE['username']).'"/><br /><br />
    	      	    '.$lang['title'].':<br />
    	      	    <input type="text" name="title" /><br /><br />
    	      	    Smile: :) , :( , :D , ;) , ^_^ .<br /><br />
    	      	    BBcode:<br />
    		        * [img] image_path [/img]<br />
					* [url= url_path ] url_name [/url]<br />
					* [url] url_path [/url]<br />
					* [img] url_img [/img]<br />
					* [b] text [/b]<br />
					* [i] text [/i]<br />
					* [u] text [/u]<br />
				<br />
    	      	    '.$lang['article'].':<br />
    	      	    <textarea name="article" cols="100" rows="25"></textarea><br /><br />
    	      	    <input type="submit" value="'.$lang['send_new_article'].'" />
    	      	    <input type="hidden" name="security" value="'.$_SESSION['token'].'" />
    	      	    <br /><br /></form>';
		}
	}
	
	public function manage_comments($id) {
	global $lang;
		
		$this->id = intval($id);
				
		if(empty($this->id))
			die("<div id=\"error\"><h2 align=\"center\">".$lang['id_not_exist']."</p></div>");
		
		print "<h2 align=\"center\">".$lang['title_comments']."</h2><br />\n";
		
		$this->comments = $this->sql->sendQuery("SELECT id, blog_id, name, comment FROM ".__PREFIX__."comments WHERE blog_id = '{$id}'");
		
		if(mysql_num_rows($this->comments) < 1) {
			print "<p><b>".$lang['no_comment']."</b></p>";
		}else{
			print '<table style="border-collapse: collapse;" border="2" align="center" cellpadding="10" cellspacing="1">
			<tbody>
				<tr>
				  <td><center>'.$lang['name'].'</center></td>
				  <td><center>'.$lang['commit'].'</center></td>
				  <td><center>[#]</center></td>
				</tr>';	
				
			while($this->comment = mysql_fetch_array($this->comments)) {
				print "\n<form action='admin.php?action=del_comment' method='POST'>";	
				print "\n<tr>"
					  . "\n<td>".htmlspecialchars($this->comment['name'])."</td>"
					  . "\n<td>".htmlspecialchars($this->comment['comment'])."</td>"
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
			die("<div id=\"error\"><h2 align=\"center\">".$lang['id_not_exist']."</p></div>");
			
		$this->security_token($_POST['security'], $_SESSION['token']);
		
		$this->sql->sendQuery("DELETE FROM ".__PREFIX__."comments WHERE id = '".$this->id."'");
		
		print '<script>window.location="admin.php?action=manage_comment";</script>';
	}
	
	public function del_post($id) {
	global $lang;
	
		$this->id = intval($id);
		
		print "<h2 align=\"center\">".$lang['title_del_article']."</h2><br />\n";
		
		if(empty($this->id)) {
			print "\n<br /><br /><form method=\"POST\" action=\"admin.php?action=del_post\" />\n"
				. $lang['inserit_article_id'].": <input type=\"text\" name=\"id\" />\n"
				. "<br /><input type=\"submit\" value=\"".$lang['delete_art']."\" />\n"
				. "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />"
				. "</form>";
		}else{
			$this->security_token($_POST['security'], $_SESSION['token']);
			
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
       			$this->sql->sendQuery("UPDATE ".__PREFIX__."config SET themes = 'default.css'");
       			
       			print "\n<p>TRUNCATE TABLE ".__PREFIX__."articles: <font color='green'>Success</font><br />\n"
       			    . "TRUNCATE TABLE ".__PREFIX__."comments: <font color='green'>Success</font>\n"
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
		$lol = new Language();
	
		if(!empty($_POST['title']) && !empty($_POST['desc']) && !empty($_POST['lang']) && !empty($_POST['limit']) && !empty($_POST['footer'])) {
			
			$this->security_token($_POST['security'], $_SESSION['token']);
			
			$this->title  = $this->VarProtect( $_POST['title']  );
			$this->desc   = $this->VarProtect( $_POST['desc']   );
			$this->lang   = $this->VarProtect( $_POST['lang']   );
			$this->limit  =           intval ( $_POST['limit']  );
			$this->footer = $this->VarProtect( $_POST['footer'] );						
			 
			if($lol->check_exist_language($this->lang) == FALSE)
				die('<script>alert("'.$lang['lang_not_exist'].'"); window.location="admin.php?action=settings";</script>');
			
			$this->sql->sendQuery("UPDATE `".__PREFIX__."config` SET 
									`title` = '".$this->title."', 
									`description` = '".$this->desc."', 
									`lang` = '".$this->lang."',
									`limit` = '".$this->limit."',
									`footer` = '".$this->footer."' LIMIT 1 ;");
			
			print "<script>alert(\"".$lang['setting_success'].".\"); window.location=\"admin.php\";</script>";
		
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
				
				$lol->list_language();
					
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
				
				if($_COOKIE['username'] != $this->a_user)
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
		
		if ($fsock = @fsockopen('www.0xproject.hellospace.net', 80, $errno, $errstr, 10)) {
			@fputs($fsock, "GET /versions/0xBlog.txt HTTP/1.1\r\n");
			@fputs($fsock, "HOST: www.0xproject.hellospace.net\r\n");
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
	
			if ($version == $update)
				$version_info = "<p style=\"color:green\">".$lang['update_1'].".</p><br />";
			else
				$version_info = "\n<p style=\"color:red\">".$lang['update_2'].".<br />\n".$lang['update_3'].":". $update."\n"
							  . "<br /><br />Link Download: <a href=\"http://0xproject.hellospace.net/#0xBlog\">".$lang['update_4']."</a><br />\n";
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
			
				$this->security_token($_POST['security'], $_SESSION['token']);
				
				$this->sql->sendQuery("UPDATE ".__PREFIX__."config SET themes = '".$this->VarProtect($_GET['select'])."';");
				
				print "<script>alert(\"".$lang['theme_changed']."\"); window.location.href = 'admin.php?action=themes';</script>";
			}else {
				die ("<script>alert(\"".$lang['theme_not_found']."\"); window.location.href = 'admin.php?action=themes';</script>");
			}
		}else{			
			print "<from method=\"POST\" />";
			foreach ($themes as $theme)
				if ($theme != "." && $theme != "..")
					print "\n". $theme ." <a href = 'admin.php?action=themes&select={$theme}'>".$lang['select']."</a><br />";
			print "\n<input type=\"hidden\" name=\"security\" value=\"".$_SESSION['token']."\" />\n</form>";
		}
	}
	
	public function change_pass_admin($id) {
	global $lang;
			
		$this->id = intval($id);
		
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
			
			        $this->sql->sendQuery("UPDATE ".__PREFIX__."articles SET post = '".$this->article."', title = '".$this->title."', author = '".$this->author."' WHERE id = '".$this->id."'");
			
			        print "<script>alert(\"".$lang['edit_success']."!\");</script>";
			        print '<script>window.location="admin.php";</script>';
			    }else{
			    	$this->data_article = mysql_fetch_array($this->sql->sendQuery("SELECT * FROM ".__PREFIX__."articles WHERE id = '".$this->id."'"));
			    	
					print '<form action="admin.php?action=edit_post&id='.$this->id.'" method="POST">
						    '.$lang['author'].':<br />
    			            <input type="text" name="author" value="'.$this->data_article['author'].'"/><br /><br />
    			            '.$lang['title'].':<br />
    			            <input type="text" name="title" value="'.$this->data_article['title'].'" /><br /><br />
    			            Smile: :) , :( , :D , ;) , ^_^ .<br /><br />
    			            BBcode:<br />
    			            * [img] image_path [/img]<br />
							* [url= url_path ] url_name [/url]<br />
							* [url] url_path [/url]<br />
							* [b] text [/b]<br />
							* [i] text [/i]<br />
							* [u] text [/u]<br />
							<br />
    			            '.$lang['article'].':<br />
    			            <textarea name="article" cols="100" rows="25">'.$this->data_article['post'].'</textarea><br /><br />
    			            <input type="submit" value="'.$lang['send_edit'].'" />
    			            <br /><br />
							<input type="hidden" name="security" value="'.$_SESSION['token'].'" />
    			            </form>';
			}
		}
	}
				
}	
?>		

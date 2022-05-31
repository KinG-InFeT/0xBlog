<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file captcha.php
 *
 * @link http://0xproject.netsons.org#0xBlog
 *
 */

session_start();

@$hash = $_GET['hash'];

//stringa inutile per il captcha (Anti-Cache-Loading)
@$rnd = $_GET['rnd'];

if($hash != $_SESSION['hash'])
	die("FAIL");

@$code = $_SESSION['captcha'];

if (empty ($code))
	die("FAIL");
	
header ('Content-type: image/png');
$x = 120;
$y = 30;

$im = imagecreate ($x, $y);

$bgcolor   = array (rand (0,255),rand (0,255),rand (0,255));
$textcolor   = array (rand (0,255),rand (0,255),rand (0,255));
//$textcolor = array (~$bgcolor[0],~$bgcolor[1],~$bgcolor[2]);

$bg = imagecolorallocate ($im, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
$tc = imagecolorallocate ($im, $textcolor[0], $textcolor[1], $textcolor[2]);

for ($i = 0; $i < strlen($code); $i++) {
	imagestring ($im,5, rand($i*15,$i*15+5), rand(0,15), $code[$i], $tc);
}

for ($i = 0; $i < 25; $i++) {
	$lncol = array (rand(0,255),rand(0,255),rand(0,255));
	$ln = imagecolorallocate ($im, $lncol[0], $lncol[1], $lncol[2]);
	$coord = array (rand(0,90),rand(0,30));
	imageline($im, $coord[0], $coord[1], $coord[0], $coord[1], $ln);
}

for($i = 0; $i < 80; $i++) {
	$x1 = rand(3,$x-3);
	$y1 = rand(3,$y-3);
	$x2 = $x1-2-rand(0,8);
	$y2 = $y1-2-rand(0,8);
	$ln = imagecolorallocate ($im, $lncol[0], $lncol[1], $lncol[2]);
	imageline($im,$x1,$y1,$x2,$y2,$ln);
}

imagepng ($im);

imagedestroy ($im);
?>

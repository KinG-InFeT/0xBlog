<?php
/*
 *
 * @project 0xBlog
 * @author KinG-InFeT
 * @licence GNU/GPL
 *
 * @file captcha.php
 *
 * @link http://0xproject.hellospace.net#0xBlog
 *
 */
session_start();

$charmap = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
$display = $charmap{rand(0,59)};

for($i = 1; $i < 6; $i++)
	$display .= $charmap{rand(0,59)};

$_SESSION['captcha'] = $display;

header('Content-type: image/png');

$string = $display;
$height = 20;
$width  = 60;

$image = imagecreate($width, $height);
$line  = imagecolorallocate($image, rand(90,160), rand(90,160), rand(90,160));

for($i=1; $i<30; $i++)
	$line = imageline($image, rand(0,60), rand(0,20), rand(0,60), rand(0,20), imagecolorallocate($image, rand(90,160), rand(90,160), rand(90,160)));;

$bgcolor = imagecolorallocate($image, rand(150,255), rand(150,255), rand(150,255));
$fcolor  = imagecolorallocate($image, rand(0,100), rand(0,100), rand(0,100));

imagefill($image, 0, 0, $bgcolor);
imagestring($image, 5, rand(1,6), rand(1,4), $display, $fcolor);
imagepng($image);

imagedestroy($image);
?>

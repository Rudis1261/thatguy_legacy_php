<?php

//Bootstrap SPF
require 'includes/master.inc.php';

// Set the headers so the image gets created
header("Content-type:image/jpeg");

// Lets check if we should be creating a image and we will only be able to do specific types
if ((isset($_REQUEST['action']))
&& ($_REQUEST['action'] == 'captcha')) // Needs to be empty
{
    header("Content-Disposition:inline ;filename=captcha.jpg");
    Captcha::createImage(250, 60, 5);
}

// Wallpaper request, I will decide later if we will be allowing only registered users to download
if ((isset($_REQUEST['action']))
&& ($_REQUEST['action'] == 'wallpaper')
&& (isset($_REQUEST['w']))
&& (!empty($_REQUEST['w']))
&& (isset($_REQUEST['h']))
&& (!empty($_REQUEST['h']))
&& (isset($_REQUEST['img']))
&& (!empty($_REQUEST['img'])))
{
    $img = new GD($_REQUEST['img'], 'jpg');
    set_time_limit(3600);
    $largeImage = 'assets/portfolio/large/';
    $original = 'assets/portfolio/';
    $imgSaveName = str_replace('.jpg', "_wallpaper_" . $_REQUEST['w'] . "_" . $_REQUEST['h']. ".jpg",$_REQUEST['img']);
    $imgSaveName = str_replace($largeImage, '', $imgSaveName);
    $imgSaveName = str_replace($original, '', $imgSaveName);
    header("Content-Disposition:inline ;filename=" . $imgSaveName);
    $img->resizeToResolution($_REQUEST['w'], $_REQUEST['h']);
}

?>
<?php
//Bootstrap SPF
require 'includes/master.inc.php';
require 'includes/user.inc.php';
$title = "404 - Page not found";
$body = "<div style='margin: auto; text-align: center; width: 600px; padding: 75px 100px; background: white; border: 1px solid #0b2836; -webkit-border-radius: 20px; -moz-border-radius: 20px; border-radius: 20px;'>
            <h1 style='font-size: 200px; color: red; font-weight: bold; border-bottom: 1px solid #ccc;'>404</h1>
            <h2>Weird, it should be there. But it's not, now thats a shame</h2>
            <p><b>" . full_url() . "</b></p>
        </div>";

Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header-min', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'home', 'fb'=>$fb)),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer-min',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>
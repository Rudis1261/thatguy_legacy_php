<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

$title='Welcome';
$body = "";
$JS->output('index.js');
$JS->export();

Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'home', 'fb'=>$fb, 'Auth'=>$Auth)),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>

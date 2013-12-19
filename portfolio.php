<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

# Export the JS
$JS->output('portfolio.js');
$JS->export();

# Some turnaries to make things easier
$id             = (isset($_REQUEST['id']))          ? $_REQUEST['id']           : false;
$action         = (isset($_REQUEST['action']))      ? $_REQUEST['action']       : false;

# Hook into the Portfolio Class
$Portfolio      = new Portfolio($id);

# Start the output
$title          = 'Portfolio <small>What have I been up to?</small>';
$body           = "";

# Switch through the various actions available
switch($action)
{
    case "write":
        break;

    default:
        break;
}

Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'portfolio', 'fb'=>$fb, 'Auth'=>$Auth)),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>

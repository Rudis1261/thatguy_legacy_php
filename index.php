<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

# Export the JS
$JS->output('index.js');
$JS->export();

# Some turnaries to make things easier
$id             = (isset($_REQUEST['id']))          ? $_REQUEST['id']           : false;
$action         = (isset($_REQUEST['action']))      ? $_REQUEST['action']       : false;
$type           = (isset($_REQUEST['type']))        ? $_REQUEST['type']         : false;

# Start the output
$title  = '';
$body   = "";

# Switch through the various actions available
switch($action)
{
    case 'meta':

        # Authenticate to ensure that the user has access to this section
        if ($Auth->loggedIn() AND $Auth->isAdmin() AND $type!== false)
        {
            # Connect to the meta object and display the manager
            $title  = "Manage <small>some meta aspects</small>";
            $meta   = new Meta($type);
            $body   .= $meta->manage("index.php?action=meta&type=" . $type, $id);
        }
        break;

    default:
        redirect("blog.php");
        break;
}


Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'home', 'fb'=>$fb, 'Auth'=>$Auth)),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>

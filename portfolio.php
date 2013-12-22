<?php

//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

# Export the JS
$JS->output('portfolio.js');
$JS->export();

# And the CSS
$CSS->add('portfolio.css');
$CSS->output('portfolio.css');
$CSS->export();

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

    # Save info
    case "write":
        $Portfolio->upsert($id);
        exit();
        break;


    # Cleanup cron, to remove unpublished and stale images
    case "clean":
        $Portfolio->clean();
        exit();
        break;


    # Dropping images like they are hot
    case "drop":

        # Attempt to remove the image
        $drop = $Portfolio->drop($id);

        # Success
        if ($drop == true)
        {
            $Error->add("info", "Successfully deleted!");
        }

        # Denied
        elseif ($drop === "denied")
        {
            $Error->add("error", "Access denied!");
        }

        # Redirect my good sir
        redirect($Portfolio->script);
        break;


    # Display the default
    default:
        $body .= $Portfolio->defaultView();
        break;
}

if ((isset($_SESSION['error'])) AND (count($_SESSION['error']) > 0)) $msg = $Error->alert();
Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array("CSS"=>$CSS->output(), 'title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'portfolio', 'fb'=>$fb, 'Auth'=>$Auth)),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>

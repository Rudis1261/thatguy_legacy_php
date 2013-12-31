<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

# Fire up the JS and CSS portions
$JS->output('blog.js');
$JS->add('prettify.js');
$JS->add('run_prettify.js');
$JS->export();

$CSS->add('prettify.css');
$CSS->add('blog.css');
$CSS->output('blog.css');
$CSS->export();


# Some turnaries to make things easier
$id             = (isset($_REQUEST['id']))          ? $_REQUEST['id']           : false;
$action         = (isset($_REQUEST['action']))      ? $_REQUEST['action']       : false;
$type           = (isset($_REQUEST['type']))        ? $_REQUEST['type']         : false;

# We need a quick way to extract the controlling information
$blog           = (isset($_REQUEST['blog']))        ? $_REQUEST['blog']         : false;
$article        = (isset($_REQUEST['article']))     ? $_REQUEST['article']      : $id;
$search         = (isset($_REQUEST['search']))      ? $_REQUEST['search']       : false;
$comment        = (isset($_REQUEST['comment']))     ? $_REQUEST['comment']      : false;

# Start the output
$title  = '';
$body   = "";

# Fire up the blog class
$Blog           = new Blog();

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

    case "remove":
        if ($Auth->loggedIn())
        {
            $delete = $Blog->check_remove($blog);
            if ($delete)
            {
                $Error->add('info', "Successfully deleted blog");
            }
            else
            {
                $Error->add('error', "Could not delete blog");
            }
        }
        redirect(full_url_to_script('blog.php'));
        break;

    case "deleteComment":
        if ($Auth->loggedIn())
        {
            $delete = $Blog->deleteComment($id);
            if ($delete)
            {
                $Error->add('info', "Successfully deleted comment");
            }
            else
            {
                $Error->add('error', "Could not delete comment");
            }
        }
        redirect(full_url_to_script('blog.php'));
        break;


    case "editComment":
        if ($Auth->loggedIn())
        {
            $edit = $Blog->editComment($id, $comment);
            if ($edit)
            {
                $Error->add('info', "Successfully edited comment");
            }
            else
            {
                $Error->add('error', "Could not edit comment");
            }
        }
        redirect(full_url_to_script('blog.php'));
        break;


    case "addComment":
        if ($Auth->loggedIn())
        {
            $add = $Blog->addComment($id, $comment);
            if ($add)
            {
                $Error->add('info', "Successfully add comment");
            }
            else
            {
                $Error->add('error', "Could not add comment");
            }
        }
        redirect(full_url_to_script('blog.php'));
        break;


    default:
        $body .= $Blog->defaultView($article);
        break;
}


Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'home', 'fb'=>$fb, "CSS"=>$CSS->output(), 'Auth'=>$Auth)),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>

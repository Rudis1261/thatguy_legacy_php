<?php
//Bootstrap SPF
require 'includes/master.inc.php';
require 'includes/user.inc.php';

$inputValue     = array('desc'=>'', 'text'=>'', '', '', '', '', '', '', '');
$errorClass     = array('desc'=>'', 'text'=>'', '', '', '', '', '', '', '');
$title          = "Blog <small>sharing my thoughts</small>";
$body           = "";

# Fire up the blog class
$Blog           = new Blog();


# We need a quick way to extract the controlling information
$action         = (isset($_REQUEST['action']))  ? $_REQUEST['action']       : false;
$blog           = (isset($_REQUEST['blog']))    ? $_REQUEST['blog']         : false;
$id             = (isset($_REQUEST['id']))      ? $_REQUEST['id']           : false;
$article        = (isset($_REQUEST['article'])) ? $_REQUEST['article']      : $id;
$search         = (isset($_REQUEST['search']))  ? $_REQUEST['search']       : false;
$comment        = (isset($_REQUEST['comment'])) ? $_REQUEST['comment']      : false;

# Fire up the JS and CSS portions
$JS->add('prettify.js');
$JS->output('blog.js');
$JS->export();

$CSS->add('blog.css');
$CSS->output('blog.css');
$CSS->export();


# Switch through the actions as needed
switch ($action)
{
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
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'blog', 'Auth'=>$Auth, "CSS"=>$CSS->output())),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>
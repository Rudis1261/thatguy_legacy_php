<?php
//Bootstrap SPF
require 'includes/master.inc.php';

# Require authentication
$mustauth=true;

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

# Start the output
$title="Feature Request <small>what, where, how</small>";
$body = '';
$formzy = new Form();
$formzy->addText(array("name"=>"desc", "label"=>"Short Description", "validation"=>array("long"=>3)));
$formzy->addTextarea(array("name"=>"longdesc", "label"=>"Let us more", "validation"=>array("long"=>10)));
$formzy->addSubmit(array("name"=>"submit", "value"=>"Request a new feature"));
$form = $formzy->render();

# Get the Options we need
$website    = Options::get('prettyName');
$adminEmail = Options::get('emailAdmin');

# Should the form be valid, let's send an email
if ($formzy->valid())
{
    $username       = $Auth->username;
    $from           = $Auth->email;
    $subject        = $website . " Feature Request";
    $message        = "Hey,

User (" . $username . ") would like to know if you can add the following feature\s:

<b>Short Description</b>
" . htmlspecialchars($_REQUEST['desc']) . "

<b>Long Description</b>
" . htmlspecialchars($_REQUEST['longdesc']) . "

Kind Regards,
Sitebot";

    # Send the email
    $sendmail = send_html_mail($adminEmail, $subject, nl2br($message), $from, $plaintext = $message);

    # Make sure it went fine
    if ($sendmail)
    {
        $Error->add('info', "Thank you for taking the time to request a feature. We will let you know should it be considered!");
        redirect('index.php');
    }
}

$body .= $form;
$msg=$Error->alert();
Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'feature')),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>
<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

$title="So <small>you want to get hold of us</small>";
$mustauth=false;

$body = '';
$formzy = new Form();
$formzy->addText(array("name"=>"name", "label"=>"Name", "validation"=>array("long"=>2)));
$formzy->addText(array("name"=>"email", "label"=>"Email Address", "validation"=>array("email"=>true)));
$formzy->addTextarea(array("name"=>"message", "label"=>"Message", "validation"=>array("long"=>10)));
$formzy->addSubmit(array("name"=>"submit", "value"=>"Contact Us"));
$form = $formzy->render();


# Should the form be valid, let's send an email
if ($formzy->valid())
{
    $from           = $_REQUEST['email'];
    $subject        = "Thatguy.co.za Contact Form";
    $message        = "Hey,

" . htmlspecialchars($_REQUEST['name']) . " completed our contact form:

<b>They Said</b>
" . htmlspecialchars($_REQUEST['message']) . "

Kind Regards,
Sitebot";

    # Send the email
    $sendmail = send_html_mail('iam@thatguy.co.za', $subject, nl2br($message), $from, $plaintext = $message);

    # Make sure it went fine
    if ($sendmail)
    {
        $Error->add('info', "Thank you for taking the time to contact us.");
        redirect('index.php');
    }
}

$body .= $form;
$msg=$Error->alert();
Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'contact')),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>
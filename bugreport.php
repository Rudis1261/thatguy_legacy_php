<?php
//Bootstrap SPF
require 'includes/master.inc.php';

# Require authentication
$mustauth=true;

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

# Start the output
$title="Report a Bug <small>what, where, how</small>";
$body = '';
$formzy = new Form(array("enctype"=>"multipart/form-data"));
$formzy->addText(array("name"=>"desc", "label"=>"Short Description", "validation"=>array("long"=>3)));
$formzy->addTextarea(array("name"=>"longdesc", "label"=>"Let us more", "validation"=>array("long"=>10)));
$formzy->addCustom('<div class="form-group">
                        <label class="control-label">Attach Images (Optional)</label>
                        <input type="file" name="upload[]" multiple />
                    </div>');
$formzy->addSubmit(array("name"=>"submit", "value"=>"Report bug"));
$form = $formzy->render();

# OPTIONS
$website    = Options::get('prettyName');
$adminEmail = Options::get('emailAdmin');

# Should the form be valid, let's send an email
if ($formzy->valid())
{
    $username       = $Auth->username;
    $from           = $Auth->email;
    $subject        = $website . " Bug Report";
    $message        = "Hey,

User (" . $username . ") found a bug and would like you to have a look at it:

<b>Short Description</b>
" . htmlspecialchars($_REQUEST['desc']) . "

<b>Long Description</b>
" . htmlspecialchars($_REQUEST['longdesc']) . "

Kind Regards,
Sitebot";

    $attachment = array();

    # Check if an image
    if (!empty($_FILES['upload']))
    {
        foreach($_FILES['upload']['type'] as $uid=>$type)
        {
            $filetype = current(explode('/', $type));
            if ($filetype == "image")
            {

                $attachment[$_FILES['upload']['name'][$uid]] = $_FILES['upload']['tmp_name'][$uid];
            }
        }
    }

    # Send the email
    $sendmail = send_html_mail($adminEmail, $subject, nl2br($message), $from, $message, $attachment);

    # Make sure it went fine
    if ($sendmail)
    {
        # Clear the uploads
        if (!empty($attachment))
        {
            # Loop through them
            foreach ($attachment as $original_name=>$tmp_name)
            {
                # unlink the file
                unlink($tmp_name);
            }
        }

        # Add the message
        $Error->add('info', "Thank you for taking the time to report a bug. We will be looking into it and providing you with feedback");
        redirect('index.php');
    }
}

$body .= $form;
$msg=$Error->alert();
Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'bugreport')),
	'content'=>$body,
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>
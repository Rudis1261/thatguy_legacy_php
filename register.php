<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

// Page specific info
$title='Register <small>a new account</small>';
$meta=''; // Possibly add page unique meta data

// Check if a callback URL is specified?
if ((isset($_POST['callback']))
&& (!empty($_POST['callback'])))
{
    $callback = $_POST['callback'];
} else {
    $callback = '';
}


// Validate the form fields
// Username verification
if (isset($_POST['register-username']))
{
    $inputValue[0] = $_POST['register-username'];
    if (($Error->length($_POST['register-username'], 4, 20, 'error', 'Username'))
    && ($Error->username($_POST['register-username'])))

    {
        $errorClass[0] = 'has-success';
    } else {
        $errorClass[0] = 'has-error';
    }
} else if (isset($_POST['action'])) {
    $Error->add('error', 'Username cannot be left empty, (Min 4, Max 20)');
    $errorClass[0] = 'has-error';
}

// Password verification
if (isset($_POST['register-password']))
{
    $inputValue[1] = $_POST['register-password'];
    if ($Error->length($_POST['register-password'], 4, 20, 'error', 'Password'))
    {
        $errorClass[1] = 'has-success';
    } else {
        $errorClass[1] = 'has-error';
    }
} else if (isset($_POST['action'])) {
    $Error->add('error', 'Password cannot be left empty, (Min 4, Max 20)');
    $errorClass[1] = 'has-error';
}

// Confirm the password
if (isset($_POST['register-confirm']))
{
    $inputValue[2] = $_POST['register-confirm'];
    if ($Error->passwords($_POST['register-password'], $_POST['register-confirm'], 'error'))
    {
        $errorClass[2] = 'has-success';
    } else {
        $errorClass[2] = 'has-error';
    }
} else if (isset($_POST['action'])) {
    $Error->add('error', 'Password confirmation cannot be left empty');
    $errorClass[2] = 'has-error';
}

// Email verification
if (isset($_POST['register-email']))
{
    $inputValue[3] = $_POST['register-email'];
    if ($Error->email($_POST['register-email']))
    {
        $errorClass[3] = 'has-success';
    } else {
        $errorClass[3] = 'has-error';
    }
} else if (isset($_POST['action'])) {
    $Error->add('error', 'Email cannot be left empty');
    $errorClass[3] = 'has-error';
}

// Captcha verification
if (isset($_POST['register-captcha']))
{
    $inputValue[4] = ""; /* we need to always clear the captcha field, because it will regenerate after a reresh*/
    if ($Error->captcha($_POST['register-captcha']))
    {
        $errorClass[4] = 'has-success';
    } else {
        $errorClass[4] = 'has-error';
    }
} else if (isset($_POST['action'])) {
    $Error->add('error', 'Captcha cannot be left empty');
    $errorClass[4] = 'has-error';
}

$msg = $Error->alert();

// Check if the form was submitted without any errors.
if ((isset($_POST['register-username']))
&& (isset($_POST['register-password']))
&& (isset($_POST['register-confirm']))
&& (isset($_POST['register-email']))
&& (isset($_POST['register-captcha']))
&& (!$Error->ok()))
{
    $complete = true;
    // Create the actual user
    Auth::createNewUser($_POST['register-username'], $_POST['register-password'], $_POST['register-email']);
    $userId = Auth::userId($_POST['register-username']);
    $link = full_url_to_script('activate.php') . "?action=activate&code=" . Activation::get($userId) . "&id=" . $userId;
    //echo $link;

    Emailtemplate::setBaseDir('./assets/email_templates');
    $html = Emailtemplate::loadTemplate('activation', array('title'=>'Activation Email',
                                                            'prettyName'=>Options::get('prettyName'),
                                                            'name'=>$_POST['register-username'],
                                                            'siteName'=>Options::get('emailName'),
                                                            'activationLink'=>$link,
                                                            'footerLink'=>Options::get('siteName'),
                                                            'footerEmail'=>Options::get('emailInfo')));

    send_html_mail(array($_POST['register-username']=>$_POST['register-email']),
                        'Activation Email',
                        $html,
                        array(Options::get('siteName')=>Options::get('emailAdmin')));
}

Template::setBaseDir('./assets/tmpl');

$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'meta'=>$meta, 'selected'=>'register')),
	'content'=>Template::loadTemplate('register', array('errorClass'=>$errorClass, 'inputValue'=>$inputValue, 'complete'=>$complete, 'callback'=>$callback)),
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>
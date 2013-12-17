<?php
//Bootstrap SPF
require 'includes/master.inc.php';

//This loads up $user - $isadmin - $js
require 'includes/user.inc.php';

$reset          = false;
$detail         = false;
$newPassword    = '';
$title          = 'Forgotten password, request new one';
$meta           = '';
$body           = '';
$message        = '';
$submit         = (isset($_POST['action'])) ? $_POST['action'] : false;

// Username verification
if (isset($_POST['action']))
{
    if ((isset($_POST['forgot-username'])) AND ($_POST['forgot-username'] !== ''))
    {
        $detail = $_POST['forgot-username'];
    }


    elseif ((isset($_POST['forgot-email'])) AND ($_POST['forgot-email'] !== ''))
    {
        $detail = $_POST['forgot-email'];
    }

    else
    {
        $Error->add('error', 'You will need to provide us with either a username or email address to reset your password');
    }

    // Check if the details provided exists and whether they would be allowed to reset their password.
    if ((isset($detail)) AND ($detail !== false) AND (Auth::resetPasswordCheck($detail) == false))
    {
        $Error->add('error', 'The details provided do not appear to be valid');
    }
}

// Check if the form was submitted without any errors.
if ((isset($detail)) AND (Auth::resetPasswordCheck($detail) !== false))
{
    $userId = Auth::resetPasswordCheck($detail);
    $activationCode = Activation::get($userId);
    $complete = true;
    $u = new User($userId);

    $link = full_url_to_script('forgot.php') . "?action=resetpassword&code=" . Activation::get($userId) . "&uid=" . $userId;

    // Select the Email tempalte and replace the relevant values
    Emailtemplate::setBaseDir('./assets/email_templates');
    $html = Emailtemplate::loadTemplate('forgot', array('title'=>'Reset Password Email',
                                                            'prettyName'=>Options::get('prettyName'),
                                                            'name'=>$u->username,
                                                            'siteName'=>Options::get('emailName'),
                                                            'link'=>$link,
                                                            'footerLink'=>Options::get('siteName'),
                                                            'footerEmail'=>Options::get('emailInfo')));

    // Replace the relevant values and send the HTML email
    send_html_mail(array($u->username=>$u->email),
                        'Reset Password Email',
                        $html,
                        array(Options::get('siteName')=>Options::get('emailAdmin')));

    $message="We have sent your reset password email.
                <br /><br />
                <b style='color: black;'>Check your inbox!</b>";
}

// Otherwise if the email link is followed lets reset the password and email it to the user.
if ((isset($_GET['action'])) AND ($_GET['action'] == 'resetpassword') AND (isset($_GET['uid'])) AND (isset($_GET['code'])) AND (Activation::get($_GET['uid']) == $_GET['code']))
{
    $u = new User($_GET['uid']);
    $userId = $u->id;
    $newPassword = Auth::generateStrongPassword(6, false, 'ld');
    Auth::changePassword($userId, $newPassword);
    $reset = true;

    // Select the Email tempalte and replace the relevant values
    Emailtemplate::setBaseDir('./assets/email_templates');
    $html = Emailtemplate::loadTemplate('reset', array('title'=>'Password Successfully Reset',
                                                            'prettyName'=>Options::get('prettyName'),
                                                            'name'=>$u->username,
                                                            'siteName'=>Options::get('emailName'),
                                                            'password'=>$newPassword,
                                                            'footerLink'=>Options::get('siteName'),
                                                            'footerEmail'=>Options::get('emailInfo')));

    // Replace the relevant values and send the HTML email
    send_html_mail(array($u->username=>$u->email),
                        'New Password',
                        $html,
                        array(Options::get('siteName')=>Options::get('emailAdmin')));

    $message="Your password has been reset successfully.
                <br /><br />
                Your new password is
                    <br />
                    <center>
                        <b style='color: black;'>" . $newPassword . "</b>
                    </center>
                </p>";
}

# If there is a message we need to display only that message.
if ($message !== "")
{
    $body = '<div class="form form-vertical alert alert-info" id="loginForm">
                <a href="index.php" style="color: white;">
                    <div class="pull-right">
                        <div class="pull-left">
                          <h1><b>ThatGuy&nbsp;&nbsp;</b></h1>
                        </div>
                        <div class="pull-right">
                            <img src="assets/img/logo_white.png" alt="logo" width="70" height="79" />
                            &nbsp;&nbsp;
                        </div>
                    </div>
                </a>
                <h3>' . $message . '</h3>
            </div>';
}


# Otherwise display the form
else
{
    # Add the form
    $msg=$Error->alert();
    $body='<form method="post" class="form form-vertical alert alert-info" id="loginForm">
                <a href="index.php" style="color: white;">
                    <div class="pull-right">
                        <div class="pull-left">
                          <h1><b>ThatGuy&nbsp;&nbsp;</b></h1>
                        </div>
                        <div class="pull-right">
                            <img src="assets/img/logo_white.png" alt="logo" width="70" height="79" />
                            &nbsp;&nbsp;
                        </div>
                    </div>
                </a>

                <div class="clearfix"></div>
                <h3>Forgotten Password?</h3>
                <p>Provide either your <b style="font-size: 140%;">username</b> or <b style="font-size: 140%;">email address</b> and we will email you a password reset link</p>
                <br />
                ' . $msg . '

                <div class="form-group">
                    <input type="text" id="input01" placeholder="Username" name="forgot-username" value="' . @$_POST['forgot-username'] . '" class="input-lg form-control" type="text" autocomplete="off">
                </div>

                <div class="form-group">
                    <input type="text"  placeholder="Email@address.com" id="input04" name="forgot-email" value="' . @$_POST['forgot-email'] . '" class="input-lg form-control" type="text" autocomplete="off">
                     </div>

                <button type="submit" name="action" value="register" class="btn btn-default btn-block btn-lg btn-success">Request new password</button>
             </form>';
}


Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
    'header'=>Template::loadTemplate('header-min', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'meta'=>$meta, 'selected'=>'forgot')),
    'content'=>$body,
    'footer'=>Template::loadTemplate('footer-min',array('time_start'=>$time_start))
));
echo $html;
?>
<?php
//Bootstrap SPF
require 'includes/master.inc.php';
require 'includes/user.inc.php';

// Page specific info
$body = '';

# Let's make dictionary attacks a bit harder
if (!isset($_SESSION['attempt']))
{
    # Set the value so we can increment it later on
    $_SESSION['attempt'] = 0;
}

# When was the user's last login?
if (!isset($_SESSION['lastLogin']))
{
    # Set the value so we can increment it later on
    $_SESSION['lastLogin'] = time();
}

# Get the thresholds from the options
$maxLoginAttempts   = Options::get('maxLoginAttempts');
$loginRetryTime     = Options::get('loginRetyTime');
$phase1             = false;


# Hey if the user is logged in, what are they doing here?
if ($Auth->loggedIn())
{
    # redirect them to the login page
    $Error->add('info', "Welcome back ". $Auth->username);
    redirect('index.php');
}

# Check whether there was a login attempt
if (isset($_POST['submit']))
{
    # Set the sessions
    //$_SESSION['attempt'] = $_SESSION['attempt'] + 1;
    //$_SESSION['attempt'] = 0;
    //printr($_SESSION['attempt']);

    # Check whether the max login attempts were hit or not
    if ($_SESSION['attempt'] >= $maxLoginAttempts)
    {
        //echo $_SESSION['lastLogin'] . "-" . strtotime("-1 minutes") . ": ";
        //echo ($_SESSION['lastLogin'] > strtotime("-1 minutes")) . "<br />";

        # Time has not elapsed yet
        if ($_SESSION['lastLogin'] <= strtotime("-" . $loginRetryTime . " minutes"))
        //if ($_SESSION['lastLogin'] > strtotime("-1 minutes"))
        {
            $Error->add("error", 'Sorry you have exceeded your maximum attempts allowed, please wait ' . $loginRetryTime . 'minutes before trying again.');
        }

        # Time has elapsed
        else
        {
            # Reset the attempts, and mark phase one of the authentication complete
            $_SESSION['attempt'] = 0;
            $phase1 = true;
        }
    }

    # User is still withing the threshold
    else
    {
        # Mark phase 1 complete
        $phase1 = true;
    }

    # Cool the user is still within their login threshold, let's attempt to auth them
    if ($phase1 == true)
    {
        # Sanitize the login details somewhat
        $username = strip_tags(trim($_POST['username']));
        $password = strip_tags(trim($_POST['password']));

        //var_dump($username);
        //var_dump($password);

        # Check whether the login details received are correct
        if ((!empty($username)) AND (!empty($password)))
        {
            # So we have some information to authenticate with
            $authenticate = $Auth->login($username, $password);

            # Should the authentication be successful, redirect
            if ($authenticate === true)
            {
                unset($_SESSION['attempt']);
                $Error->add('info', "Welcome back ". $username);
                redirect('index.php');
            }

            # Otherwise, should the account not be activated yet, let the user know.
            else if ($authenticate === 'inactive')
            {
                unset($_SESSION['attempt']);
                $Error->add('error', "Your account is not active yet, please check your email for the activation email and follow the link contained within.");
            }

            # Otherwise authentication did fail
            else
            {
                $Error->add("error", 'Authentication failed.');
            }
        }

        # The user's didn't event provide a Username and Password, well shit
        else
        {
            $Error->add("error", 'Authentication failed.');
        }
    }

    # Set the last attempt
    $_SESSION['lastLogin']  = time();
}

$msg=$Error->alert();

# Add the style array which we will use to style our login form
$body = '<form method="post" class="form alert alert-info" id="loginForm">
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
            ' . $msg . '
            <div class="form-group">
               <input name="username" placeholder="Username" class="form-control input-lg" type="text" autocomplete="off">
            </div>

            <div class="form-group">
                <input name="password" placeholder="Password" class="form-control input-lg" type="password" autocomplete="off">
            </div>

            <div class="form-group">
                <a class="btn btn-default" href="forgot.php">I forgot my password ' . icon("lock") . '</a>
            </div>

            <div class="form-group">
                <input name="submit" value="Login" class="btn btn-success btn-lg btn-block" type="submit">
            </div>

            <input name="formzy_submit_run" value="true" type="hidden">
        </form>';


Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
    'header'=>Template::loadTemplate('header-min', array('title'=>'Login and enjoy!','user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'login')),
    'content'=>$body,
    'footer'=>Template::loadTemplate('footer-min',array('time_start'=>$time_start))
));

echo $html;
?>
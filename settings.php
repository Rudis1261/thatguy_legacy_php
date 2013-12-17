<?php
//Bootstrap SPF
require 'includes/master.inc.php';
$mustauth=true;
require 'includes/user.inc.php';

// Static vars
$dateOptions = array();
$dmy = array('_day', '_month', '_year');
$title="Settings, <small>control this online world</small>";
$changes = false;
$u = new User($Auth->userId($user));
$userId = $u->id;
$inputValue[0] = $u->email; // UserEmail
$inputValue[1] = 'password'; // Default password

// Loop through the posted vars
if (isset($_POST))
{    
    foreach($_POST as $key=>$value)
    {
        /*echo $key . " : " . $value . "<br />";*/
        // Loop through the posts and check for a vallue with appended _day, _month or _year then add the variable for further processing
        foreach($dmy as $type)
        {
            // Does it exist in the variable?
            if (strstr($key, $type))            
            {
                // Good, check if the value is not alread in the array and then add it
                $option = str_replace($type, '', $key);                
                if(!in_array($option, $dateOptions))
                {                    
                    $dateOptions[].= $option;   
                }
            }
        }
       
        // Check if the option exists
        if (Options::exists($key))
        {
            // Compare the posted value to the value in the DB and update it if neccesarry
            if (htmlspecialchars(Options::userGet($userId, $key)) !== htmlspecialchars($value))
            {                            
                Options::userSet($userId, $key, $value);
                $changes = true;
            }
        } 
    }
    
    // We previously detected all the date fields, so lets build a timestamp and complare it to what is currently set
    foreach($dateOptions as $dateOption)
    {
        // Check if date details are being passed through correctly or not
        if (isset($_POST[$dateOption . $dmy[0]])) { $dd = $_POST[$dateOption . $dmy[0]]; } else { $dd = 1; }
        if (isset($_POST[$dateOption . $dmy[1]])) { $mm = $_POST[$dateOption . $dmy[1]]; } else { $mm = 1; }
        if (isset($_POST[$dateOption . $dmy[2]])) { $yyyy = $_POST[$dateOption . $dmy[2]]; } else { $yyyy = date('Y', time()); }
        //echo $dd . "/" . $mm . "/" . $yyyy;
        $timestamp = mktime(0, 0, 0, $mm, $dd, $yyyy);
        //echo date('r', $timestamp);
        
        // Check if the options exists, then if the value compares otherwise update it. 
        if (Options::exists($dateOption))
        {
            // Compare the posted value to the value in the DB and update it if neccesarry
            if (htmlspecialchars(Options::userGet($userId, $dateOption)) !== htmlspecialchars($timestamp))
            {
                // Set the option to the new value
                Options::userSet($userId, $dateOption, $timestamp);
                $changes = true;
            }
        }        
    }
    
    
    if ((isset($_POST['action']))
    && ($_POST['action'] == 'Save Changes'))
    {
        // These are the 2 static things in your account a password and a email address
        if ((isset($_POST['user-email']))
        && ($Error->email($_POST['user-email'], false))
        && ($_POST['user-email'] !== $u->email))
        {
            $u->email = $_POST['user-email'];
            $u->update();
            Activation::remove($userId);            
            $link = full_url_to_script('activate.php') . "?action=activate&code=" . Activation::generate($userId) . "&id=" . $userId;            
            Emailtemplate::setBaseDir('./assets/email_templates');
            $html = Emailtemplate::loadTemplate('reactivate', array('title'=>'Reactivation Email',
                                                                    'prettyName'=>Options::get('prettyName'),
                                                                    'name'=>$u->username,
                                                                    'siteName'=>Options::get('emailName'),
                                                                    'activationLink'=>$link,
                                                                    'footerLink'=>Options::get('siteName'),
                                                                    'footerEmail'=>Options::get('emailInfo')));
        
            send_html_mail(array($u->username=>$u->email),
                                'Reactivation Email',
                                $html,
                                array(Options::get('siteName')=>Options::get('emailAdmin')));
            $Error->add('info', '<strong>Logged Out</strong><br />We have sent you a reactivation email to the new email address in order to verify it. Please check your email and follow the link within.');
            $Auth->logout();
        }
        
        // These are the 2 static things in your account a password and a email address
        if ((isset($_POST['user-password']))       
        && ($_POST['user-password'] !== $inputValue[1])
        && ($_POST['user-password'] !== ''))
        {
            Auth::changePassword($u->id, $_POST['user-password']);
            $Error->add('info', '<strong>Logged Out</strong><br />Password updated, you may login with your new password');
            $Auth->logout();
        }
    }
    
    // Changes were made, let's redirect the page
    if ($changes == true){
        $Error->add('info','Setting updated');
        redirect(full_url_to_script('settings.php'));
    }
}


Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'Auth'=>$Auth)),
	'content'=>Template::loadTemplate('settings', array('inputValue'=>$inputValue, 'userId'=>$userId, 'changes'=>$changes)),
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>
<?php
//Bootstrap SPF
require 'includes/master.inc.php';
$mustauth=true;
require 'includes/user.inc.php';

$Auth->requireAdmin();
$changes = false;
$dateOptions = array();
$dmy = array('_day', '_month', '_year');

// Check if the settings have changed, the input name needs to be the same as the option name   
if (isset($_POST)) 
{    
    foreach($_POST as $key=>$value)
    {       
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
            if (htmlspecialchars(Options::get($key)) !== htmlspecialchars($value))
            {                
                Options::set($key, $value);
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
            if (htmlspecialchars(Options::get($dateOption)) !== htmlspecialchars($timestamp))
            {
                // Set the option to the new value
                Options::set($dateOption, $timestamp);
                $changes = true;
            }
        }        
    }
    
    
    if ((isset($_POST['action']))
    && ($_POST['action'] == 'update'))
    {
        Options::set($_POST['option'], Options::get($_POST['option']), $_POST['type'], $_POST['group']);
        $changes = true;
    }
    
    if ((isset($_POST['action']))
    && ($_POST['action'] == 'add'))
    {
        Options::set($_POST['option_name'], $_POST['option_value'], $_POST['type'], $_POST['group']);
        $changes = true;
    }
    
    if ((isset($_POST['action']))
    && ($_POST['action'] == 'group_add'))
    {
        Options::groupAdd($_POST['group_name'], $_POST['group_desc']);
        $changes = true;
    }
    
    if ((isset($_POST['action']))
    && ($_POST['action'] == 'group_remove'))
    {
        Options::groupRemove($_POST['group']);
        $changes = true;
    }
    
    if ((isset($_POST['action']))
    && ($_POST['action'] == 'remove'))
    {
        Options::remove($_POST['option']);
        $changes = true;
    }
    
    // Changes were made, let's redirect the page
    if ($changes == true){
        $Error->add('info','Setting updated');
        redirect(full_url_to_script('admin.php'));
    }
}


$title="Admin <small>take control</small>";

Template::setBaseDir('./assets/tmpl');

$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'admin', 'Auth'=>$Auth)),
	'content'=>Template::loadTemplate('admin', array('changes'=>$changes)),
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>
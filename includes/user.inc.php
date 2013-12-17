<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}

$user=false;
$isadmin=false;

if($Auth->loggedIn())
{
	$user=$Auth->username;
	$isadmin=$Auth->isAdmin()?true:false;	
}
else
{
  if(!empty($mustauth))
  {
    $Error->add('error', "We're sorry, you must be logged in to view this page.");
    redirect(WEB_ROOT . '/index.php');
  }
}

$msg=$Error->alert();

?>
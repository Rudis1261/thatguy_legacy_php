
<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}

//Main layout page
echo($header."\n");
echo($content."\n");
echo($footer);

?>
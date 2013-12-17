<?PHP
    require 'includes/master.inc.php';
    $Error->add('info', "You have been logged out");
    $Auth->logout();
?>
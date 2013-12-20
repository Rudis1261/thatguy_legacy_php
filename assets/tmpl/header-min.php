<?php
//Check if this is called from the application
if(!defined('SPF'))
{
    header('Location:/');
    exit();
}
// Function to check if the selected is passed for the particular value
function selected($name, $selected)
{
  if ((isset($selected))
  && ($selected == $name))
  {
    echo 'class="active"';
  }
}

if (!isset($selected)) { $selected = ''; }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo Options::get('metaDesc'); ?>">
    <meta name="author" content="<?php echo Options::get('metaAuthor'); ?>">
    <meta name="keywords" content="<?php echo Options::get('metaKeyWords'); ?>">

    <?php if (isset($meta)) { echo $meta; } ?>

     <!-- Le styles -->
    <link rel="stylesheet" href="<?php
  if (isset($CSS))
  {
    echo $CSS . "?t=" .  Cache::modified($CSS);
  } else {
    require DOC_ROOT . '/includes/css.inc.php';
    $CSS->output('style.css');
    $CSS->export();
    echo $CSS->output() . "?t=" .  Cache::modified($CSS->output());
  }
    ?>">
    <style>
      body {
        padding-top: 60px;         /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="assets/js/html5.js"></script>
    <![endif]-->
    <link rel="shortcut icon" href="assets/img/iconv2.png">
  </head>

  <body>
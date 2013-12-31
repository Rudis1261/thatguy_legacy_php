<?php
//Check if this is called from the application
if(!defined('SPF'))
{
  header('Location:/');
  exit();
}

# Manually hook into the Auth
$Auth = Auth::getAuth();

# Let's define the menus
# Normal Menu
$menu           = array(
  "index.php"       => "Home" . icon('home'),
  "portfolio.php"   => "Portfolio" . icon('camera'),
  "blog.php"        => "Blog" . icon('bookmark'),
);

# Logged Out Menu
$menuLoggedOut  = array(
  "contact.php"     => "Contact Us" . icon('send'),
  "register.php"    => "Register" . icon('pencil'),
  "login.php"       => "Login" . icon('user'),
);

# Logged In Menu
$menuLoggedin   = array(
  "bugreport.php"   => "Report Bug" . icon('bullhorn'),
  "devider"         => "",
  "settings.php"    => "Settings" . icon('cog'),
  "logout.php"      => "Logoff" . icon('off')
);

# Administrator menu
$menuAdmin      = array(
  "users.php"       => "Users" . icon('user'),
  "admin.php"       => "Admin" . icon('asterisk'),
  "index.php?action=meta&type=portfolio_types"  => "Portfolio Types" . icon('camera'),
  "index.php?action=meta&type=file_types"       => "File Types" . icon('folder-open')
);


# I want to make this more generic
function displayMenu($menuList)
{
  # Check if the selected was implied?
  $selected = script_name() ? script_name() : "";

  # Loop through the menus and create the links
  foreach((array)$menuList as $script=>$name)
  {
    # I like using turnareys for this type of stuff
    $class = ($script == "index.php") ? "sidebar-brand" : "sidebar";
    $class = (($script == $selected) AND ($script !== "index.php")) ? $class . " active" : $class;

    # is the menu item an actual entry?
    if ($script !== "devider")
    {
      echo '<li class="' . $class . '"><a href="' . $script . '">' . $name . '</a></li>';
    }

    # or is it a devider?
    else
    {
      echo '<li class="devider">&nbsp;</li>';
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php if($title) { echo strip_tags($title); } else { echo "ThatGuy.co.za"; } ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo Options::get('metaDesc'); ?>">
    <meta name="author" content="<?php echo Options::get('metaAuthor'); ?>">
    <meta name="keywords" content="<?php echo Options::get('metaKeyWords'); ?>">
    <!-- FaceBook Meta -->
    <meta property="og:title" content="<?php      if (isset($fb['title']))      { echo $fb['title'];      } else {  echo Options::get('facebookTitle'); } ?>" />
    <meta property="og:type" content="<?php       if (isset($fb['type']))       { echo $fb['type'];       } else {  echo Options::get('facebookType'); } ?>" />
    <meta property="og:url" content="<?php        if (isset($fb['url']))        { echo $fb['url'];        } else {  echo Options::get('facebookUrl'); } ?>" />
    <meta property="og:image" content="<?php      if (isset($fb['image']))      { echo $fb['image'];      } else {  echo Options::get('facebookImage'); } ?>" />
    <meta property="og:site_name" content="<?php  if (isset($fb['site_name']))  { echo $fb['site_name'];  } else {  echo Options::get('facebookSiteName'); } ?>" />
    <meta property="fb:admins" content="<?php     if (isset($fb['admins']))     { echo $fb['admins'];     } else {  echo Options::get('facebookAdminUsers'); } ?>" />

    <!-- possible additional metadata -->
    <?php if (isset($meta)) { echo $meta; } ?>
    <!-- Le styles -->
    <link rel="stylesheet" href="<?php
    	if (isset($CSS))
    	{
    		echo $CSS . "?t=" .  Cache::modified($CSS);
    	}

      else
      {
    		require DOC_ROOT . '/includes/css.inc.php';
    		$CSS->output('style.css');
    		$CSS->export();
    		echo $CSS->output() . "?t=" .  Cache::modified($CSS->output());
    	}
    ?>">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="assets/img/iconv2.png">
  </head>
  <body>

    <div id="wrapper">
      <!-- Sidebar -->
      <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
    <?php
          # Let's determine when which menu should be displayed
          ##############################
          # LOGGED IN USER
          ##############################
          if ($Auth->loggedIn())
          {
            # Default menu
            echo "<h3>Welcome $Auth->username</h3>";
            displayMenu($menu);

            # Logged In Menu
            displayMenu($menuLoggedin);

            ##############################
            # ADMIN USER
            ##############################
            if ($Auth->isAdmin())
            {
              echo '<li class="devider">&nbsp;</li>';
              echo "<div id='sidebar-admin'>
                      <h3>Administration</h3>";

              # Admin menu
              displayMenu($menuAdmin);
              echo "</div>";
            }
          }

          ##############################
          # GUEST USER
          ##############################
          else
          {
            # Default user
            echo "<h3>Welcome Guest</h3>";
            displayMenu($menu);

            # NON-User menu
            displayMenu($menuLoggedOut);
          }
    ?>
        </ul>
      </div>

      <!-- Page content -->
      <div id="page-content-wrapper">
        <div class="content-header">
            <div class="pull-left" style="margin-top: 0px;">
              <a id="menu-toggle" href="#" class="btn btn-default"><?php echo icon('align-justify');?> Menu</a>
            </div>
            <div class="pull-right">
              <span class="pull-left">
                <h2>
                  <b>ThatGuy&nbsp;&nbsp;&nbsp;</b>
                </h2>
              </span>
              <span class="pull-left">
                <img src='assets/img/logo_white.png' alt='logo' width='90' height='97' />
              </span>
            </div>
        </div>
        <div class="clearfix"></div>

        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">

    <?php

        if (!empty($title))
        {
          echo "<h1>$title</h1>";
        }
    ?>

    <?php

      // Display the error messages
      if ($msg)
      {
        echo "<div class='row'>
                  <div class='col-sm-6'>
                    " . $msg . "
                  </div>
              </div>";
      }
<?php
//Check if this is called from the application
if(!defined('SPF'))
{
  header('Location:/');
  exit();
}

# Check if we received a selection from the template page
function selected($name, $selected, $addClass='')
{
  # Return active and the class
  if ((isset($selected)) AND ($selected == $name))
  {
    return 'class="active ' . $addClass . '" ';
  }

  # or just the class
  else
  {
    return 'class="' . $addClass . '" ';
  }
}

if (!isset($selected)) { $selected = ''; }


# LOGIN MENU
# Set the defaults when a user is not logged in
$login = '<ul class="nav navbar-nav navbar-right">

            <li ' . selected('register', $selected) . '>
              <a href="register.php">
                <span>' . icon("pencil", true) . '</span> Register
              </a>
            </li>

            <li class="divider-vertical"></li>

            <li ' . selected('login', $selected) . '>
              <a href="login.php">
                <span>' . icon("user", true) . '</span> Login
              </a>
            </li>

          </ul>';

# Add links
$links = '<li ' . selected('contact', $selected) . '>
            <a href="contact.php" title="Contact Us">
              '. icon('envelope', true) . ' Contact Us
            </a>
          </li>';

# By Default no one has admin links
$adminLinks = "";

$Auth = Auth::getAuth();

# When a user is logged it they will have access to other items which normal users may not have
if ($Auth->loggedIn())
{
  # Create the login menu-item
  $login = '<ul class="nav navbar-nav navbar-right">

                  <li>
                    <a href="settings.php">' . icon("cog") . ' Settings</a>
                  </li>

                  <li class="divider"></li>

                  <li>
                    <a href="logout.php">' . icon("off") . ' Sign Out</a>
                  </li>
            </ul>';


  # Override the links with logged in links
  $links = '<li ' . selected('feature', $selected) . '>
              <a href="feature.php" title="Request a feature to be added">
                '. icon('question-sign', true) . ' Request Feature
              </a>
            </li>

            <li ' . selected('bugreport', $selected) . '>
              <a href="bugreport.php" title="Report a application bug">
                '. icon('fire', true) . ' Report Bug
              </a>
            </li>';

  # Check if the user is an admin or not
  if ($admin)
  {
    $adminLinks = '<li ' . selected('users', $selected) . '>
                    <a href="users.php" title="What\'s the crew up to?">
                      ' . icon('user', true) . ' Users
                    </a>
                  </li>

                  <li ' . selected('admin', $selected) . '>
                    <a href="admin.php" title="Administration Page">
                      ' . icon('cog', true) . ' Admin
                    </a>
                  </li>';
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php if($title) { echo strip_tags($title); } else { echo "Tracker.co.za"; } ?></title>
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
    	} else {
    		require DOC_ROOT . '/includes/css.inc.php';
    		$CSS->output('style.css');
    		$CSS->export();
    		echo $CSS->output() . "?t=" .  Cache::modified($CSS->output());
    	}
    ?>">
    <link rel="stylesheet" href="assets/css/bootstrap-responsive.css">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="assets/img/iconv2.png">
  </head>
  <body onload="prettyPrint()">


  <div id="main">
    <div class="container">
      <div class="pull-left">

        <?php

          // display the title of the page
          if (($title) AND ($title !== ''))
          {
            echo '<div class="pull-left" style="color: white;">
                    <h2>
                      ' . $title . '
                    </h2>
                  </div>';
          }

        ?>

      </div>
      <div class="pull-right">
        <div class="pull-left">
          <h1><b>ThatGuy&nbsp;&nbsp;</b></h1>
        </div>
        <img class="pull-left" src="assets/img/logo_white.png" alt="logo" width="70" height="79" />
      </div>
    </div>
  </div>
  <div class="row">
      <div class="navbar navbar-inverse" role="navigation" style="margin-right: -10px; margin-left: -10px;">
        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse" style="margin-right: 25px; margin-left: 25px;">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>
          <div class="collapse navbar-collapse" style="margin-right: 0px; margin-left: 0px; height: auto;">
            <ul class="nav navbar-nav">

                <li <?php echo selected('home', $selected); ?>>
                  <a href="index.php" title="Home Page">
                    <?php echo icon('home', true) ?> Home
                  </a>
                </li>

                <li <?php echo selected('blog', $selected); ?>>
                  <a href="blog.php" title="Blog">
                    <?php echo icon('bookmark', true) ?> Blog
                  </a>
                </li>

                <!-- Add the links as needed -->
                <?php echo $links; ?>

                <!-- Also add admin links -->
                <?php echo $adminLinks; ?>

              </ul>
              <!-- Add the right hand side menu, register and login -->
              <?php echo $login; ?>
          </div>
        </div>
    </div>
  </div>
  <div class="container">

    <?php

      // Display the error messages
      if ($msg)
      {
        echo "<div class='container'>
                <div class='row'>
                  <div class='col-sm-6'>
                  " . $msg . "
                  </div>
                </div>
              </div>";
      }
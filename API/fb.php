<?php
/**
* Copyright 2011 Facebook, Inc.
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may
* not use this file except in compliance with the License. You may obtain
* a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
* WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
* License for the specific language governing permissions and limitations
* under the License.
*/

require 'fb/src/facebook.php';
$fbPage = "147906525337534";

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId' => Options::get('fbKey'),
  'secret' => Options::get('fbSecret'),
  'cookie' => true,
));

// Get User ID
$fbApiUsername = $facebook->getUser();

if ($fbApiUsername) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $fbApiUsername_profile = $facebook->api('/'.$fbPage);
  } catch (FacebookApiException $e) {
    error_log($e);
    $fbApiUsername = null;
  }
}

  $logoutUrl = $facebook->getLogoutUrl();
  $params = array(
    'scope' => 'publish_stream, manage_pages, user_photos, photo_upload'
  );
  $loginUrl = $facebook->getLoginUrl();

if (isset($_REQUEST['debug']))
{
  if (($Auth->loggedIn()) && ($Auth->isAdmin())){ ?>
    <div class="container">
      <a class="btn btn-default" href="<?php echo $loginUrl; ?>">Force FB Login</a> <a class="btn btn-default" href="<?php echo $logoutUrl; ?>">FB Logout</a>
    </div>
  <?php } else { ?>
    <div class="container">
      <a class="btn btn-default" href="<?php echo $loginUrl; ?>">Force FB Login</a>
    </div>
<?php }
}

// USER TOKEN
// Get the token from the PHP headers and check if it's reflecting locally or not
if (isset($_SESSION['fb_' . Options::get('fbKey') . '_access_token']))
{
  // Compare the access token to that stored in the DB
  $access_token = $_SESSION['fb_' . Options::get('fbKey') . '_access_token'];
  $last_obtained = Options::get('fbUserToken');
  if ($access_token !== $last_obtained)
  {
    Options::set('fbUserToken', $access_token);
  }
}

// NOW LETS GET THE PAGE TOKEN
$getPages = file_get_contents("https://graph.facebook.com/me/accounts?access_token=" . Options::get('fbUserToken'));
$getPages = json_decode($getPages);
foreach($getPages->data as $page)
{
  if ($page->id == $fbPage)
  {
    $access_token = $page->access_token;
    $last_obtained = Options::get('fbPageToken');
    if ($access_token !== $last_obtained)
    {
      Options::set('fbPageToken', $access_token);
    }
    break;
  }
}

// Enable the site
$facebook->setFileUploadSupport("http://" . $_SERVER['SERVER_NAME']);
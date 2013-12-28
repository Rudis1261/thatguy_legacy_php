<?php

# Start off by getting the Flickr Token
$getToken       = Options::get('flickrToken');
$flickrKey      = Options::get('flickrKey');
$flickrSecret   = Options::get('flickrSecret');

# No token, get it!
if (empty($getToken))
{
    # Fire up the flickr class
    $f = new phpFlickr($flickrKey, $flickrSecret);

    # Non-redirected version do auth which will redirect back
    if (empty($_REQUEST['frob']))
    {
        $f->auth('delete', false);
    }

    # We have a frob, get the token
    else
    {
        $getToken = $f->auth_getToken($_REQUEST['frob']);
        Options::set('flickrToken', $getToken['token']);
    }
}

# Otherwise we have the token and can set it
else
{
    $f = new phpFlickr($flickrKey, $flickrSecret);
    $f->setToken($getToken);
    $f->auth("delete");
}


# Debugging tiem
if (isset($_GET['dump']))
{
    $msg = "<div class='alert alert-info span5'>";
    $msg .= "<h1>Flicker Details</h1>";
    $msg .= "Flicker Key: " . $flickrKey . "<br />";
    $msg .= "Flicker Secret: " . $flickrSecret . "<br />";
    $msg .= "Flicker Token: " . $flickrToken . "<br />";
    $msg .= "</div>";
}

?>
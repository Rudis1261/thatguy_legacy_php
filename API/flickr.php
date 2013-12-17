<?php

// Check if we have a token
if (Options::get('flickrToken') == '')
{
    // Instantiate the Flickr PHP
    $f = new phpFlickr($flickrKey, $flickrSecret);
    if (empty($_GET['frob'])) {
        $f->auth('delete', false);
    } else {
        $getToken = $f->auth_getToken($_GET['frob']);
        Options::set('flickrToken', $getToken['token']);
    }

// We have a token, lets start and get authed up
} else if ($Auth->isAdmin()) {
    $f = new phpFlickr($flickrKey, $flickrSecret);
    $f->setToken($flickrToken);
    $f->auth("delete");
}


if ((isset($_GET['dump']))
&& ($Auth->isAdmin()))
{
    $msg = "<div class='alert alert-info col-lg-5'>";
    $msg .= "<h1>Flicker Details</h1>";
    $msg .= "Flicker Key: " . $flickrKey . "<br />";
    $msg .= "Flicker Secret: " . $flickrSecret . "<br />";
    $msg .= "Flicker Token: " . $flickrToken . "<br />";
    $msg .= "</div>";
}

?>
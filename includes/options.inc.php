<?php
// I would like to keep this page separate to keep things organised. This will be included in the master file and can be referenced from there
// The variables will be $optNameCammelBack and will always start with $opt to keep tracking them to this file simple

clearstatcache(); // Clear the stats
$lastEdit = stat('includes/options.inc.php');
$lastEdit = $lastEdit['mtime'];
$fileSize = filesize('includes/options.inc.php');

// Get the options from the db, will by default have a false value
$dbTimeStamp = Options::get('modified');
$dbFileSize = Options::get('filesize');

// Force the filesize and modification date to strings to be able to compare it with the DB
settype($lastEdit, 'string');
settype($fileSize, 'string');

$optionsUpdate = false;

// Update version based on the date and / or filesize. This will also work if the db is empty
if (($dbTimeStamp !== $lastEdit)
or ($dbFileSize !== $fileSize))
{

$optionsUpdate = true;

Options::groupAdd('Website Settings', 'basic website information');
Options::groupAdd('META Data', 'control the Meta Data for the website');
Options::groupAdd('Email Templates', 'variables used in Email Templates');
Options::groupAdd('Miscellaneous', 'general settings page, not editible');
Options::groupAdd('User Settings', "options visible in the user's settings page");
Options::groupAdd('API Settings', "varaibles only used by API's. This would be for the entire site");
Options::groupAdd('Portfolio Settings', "Some settings we want to be able to adjust dynamically");

// Set the timestamp and filesize
Options::add('modified', $lastEdit, 'hidden', 'Miscellaneous'); // Do not modify these, they need to be static
Options::add('filesize', $fileSize, 'hidden', 'Miscellaneous'); // Do not modify these, they need to be static

// These would be the options you want to set, it will first
// Site
Options::addOnce('siteName', 'http://www.ThatGuy.co.za', 'input', 'Website Settings');
Options::addOnce('siteLogo', 'assets/img/logo.png', 'input', 'Website Settings');
Options::addOnce('siteIcon', 'assets/img/icon.png', 'input', 'Website Settings');
Options::addOnce('homePageFrom', '2 weeks ago', 'input', 'Website Settings');
Options::addOnce('prettyName', '<small>www.</small>ThatGuy<small>.co.za</small>', 'input', 'Website Settings');
Options::remove('homePageTileHeight');//, '220', 'input', 'Website Settings');
Options::remove('maxOverViewChars');//, '80', 'input', 'Website Settings');
Options::addOnce('dateFormatFull', "d F Y H:i", 'input', 'Website Settings');
Options::remove('tvTrackerComingSoon');//, '+6 days', 'input', 'Website Settings');
Options::remove('tvTrackerComingLater');//, "+1 month", 'input', 'Website Settings');
Options::addOnce('maxLoginAttempts', "7", 'input', 'Website Settings');
Options::addOnce('loginRetyTime', "10", 'input', 'Website Settings');

// Email Templates
Options::addOnce('emailName', 'www.ThatGuy.co.za', 'input', 'Email Templates');
Options::addOnce('emailInfo','info@thatguy.co.za', 'input', 'Email Templates');
Options::addOnce('emailAdmin', 'admin@thatguy.co.za', 'input', 'Email Templates');

// META Data
Options::addOnce('metaAuthor', 'Rudi Strydom', 'input', 'META Data');
Options::addOnce('metaDesc', 'Baseline is a PHP framework. Based on Simple PHP Framework and intgrates with Bootstrap from Twitter to create baseline website.', 'textarea', 'META Data');
Options::addOnce('metaKeyWords', 'Baseline, Simple PHP Framework, Bootstrap from Twitter', 'textarea', 'META Data');
// Facebook meta
Options::addOnce('facebookType', 'website', 'input', 'META Data');
Options::addOnce('facebookUrl', Options::get("siteName"), 'input', 'META Data');
Options::addOnce('facebookImage', Options::get("siteLogo"), 'input', 'META Data');
Options::addOnce('facebookSiteName', Options::get("siteName"), 'input', 'META Data');
Options::addOnce('facebookAdminUsers', '0000000', 'input', 'META Data');
Options::addOnce('facebookTitle', 'FB Page title', 'input', 'META Data');

// Portfolio
Options::addOnce('collageImages', '9', 'input', 'Portfolio Settings');
Options::addOnce('collageWidth', '500', 'input', 'Portfolio Settings');
Options::addOnce('collageHeight', '350', 'input', 'Portfolio Settings');

/*Options::addOnce('imageLargeWidth', '2560x1920', 'input', 'Portfolio Settings');
Options::addOnce('imageMediumWidth', '960x600', 'input', 'Portfolio Settings');
Options::addOnce('imageThumbWidth', '250x156', 'input', 'Portfolio Settings');*/
Options::remove('imageLargeWidth');
Options::remove('imageMediumWidth');
Options::remove('imageThumbWidth');


// User Settings, visible in their Settings.php page. This will be in addition to their email and password
// The values here would be the default settings shown in their console
Options::addOnce('firstName', 'John', 'input', 'User Settings');
Options::addOnce('surname', 'Doe', 'input', 'User Settings');
Options::addOnce('dateOfBirth', '631144800', 'date', 'User Settings');
Options::remove('TVTrackerHowManyDaysBack');
Options::remove('TVTrackerHowManyDaysForward');
Options::remove('TVTrackerEmail');
Options::remove('TVTrackerToken');

// This would be used by various Third Parties, Deviant ART, Flickr and Facebook
Options::addOnce('flickrKey', '', 'input', 'API Settings');
Options::addOnce('flickrSecret', '', 'input', 'API Settings');
Options::addOnce('flickrToken', '', 'input', 'API Settings');

Options::addOnce('daEmailAddress', '', 'input', 'API Settings');
Options::addOnce('daEmailFrom', '', 'input', 'API Settings');
Options::addOnce('apiLive', true, 'bool', 'API Settings');

Options::addOnce('fbKey', '', 'input', 'API Settings');
Options::addOnce('fbSecret', '', 'input', 'API Settings');
Options::remove('fbToken');//, '', 'input', 'API Settings');
Options::addOnce('fbUserToken', '', 'input', 'API Settings');
Options::addOnce('fbPageToken', '', 'input', 'API Settings');

Options::remove('tvDbApiKey');//, '', 'input', 'API Settings');
Options::remove('tvDbLastUpdate');//, '', 'hidden', 'API Settings');

Options::remove('tvUrlApi');//, 'http://kat.ph/usearch/{NAME} S{S}E{E}/?field=seeders&sorder=desc&rss=1', 'input', 'API Settings');
Options::remove('tvUrlKat');//, 'http://kat.ph/usearch/{NAME} S{S}E{E}/?field=seeders&sorder=desc', 'input', 'API Settings');
Options::remove('tvUrlPirate');//, 'http://thepiratebay.se/search/{NAME} S{S}E{E}', 'input', 'API Settings');
Options::remove('tvUrlWarez');//, 'http://www.warez-bb.org/search.php?search_forum=57&search_fields=titleonly&search_terms=all&search_keywords={NAME} S{S}E{E}', 'input', 'API Settings');

# We also need to be able to search based on the name for the Season 0 shows
Options::remove('tvUrlKatName');//, 'http://kat.ph/usearch/{NAME} {EPISODE}/?field=seeders&sorder=desc', 'input', 'API Settings');
Options::remove('tvUrlPirateName');//, 'http://thepiratebay.se/search/{NAME} {EPISODE}', 'input', 'API Settings');
Options::remove('tvUrlWarezName');//, 'http://www.warez-bb.org/search.php?search_forum=57&search_fields=titleonly&search_terms=all&search_keywords={NAME} {EPISODE}', 'input', 'API Settings');

}
?>

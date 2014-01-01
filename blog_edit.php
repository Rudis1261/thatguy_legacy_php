<?php
//Bootstrap SPF
require 'includes/master.inc.php';
$mustauth=true;
require 'includes/user.inc.php';

$JS->output('blog.js');
$JS->export();

# Turnaries
$action     = (isset($_REQUEST['action']))  ? $_REQUEST['action']   : false;
$id         = (isset($_REQUEST['blog']))    ? $_REQUEST['blog']     : false;
$dropImage  = (isset($_REQUEST['img']))     ? $_REQUEST['img']      : false;
$inputValue = array('desc'=>"", 'text'=>"", 'id'=>false);
$errorClass = array('desc'=>'', 'text'=>'', 'id'=>'');
$Auth       = Auth::getAuth();
$blog       = new Blog($id);

// Lets get the default blog info
if ($id)
{
    $inputValue = array('desc'=> htmlentities($blog->desc), 'text'=>htmlentities($blog->body), 'id'=>$id);
}

$title      ="Blog <small>let make some changes</small>";
$editBlog   = 0;
$uploads    = array();

# We may want to unlink images
if ($action == "unlink")
{
    $doDropImage = $blog->uploads_delete($dropImage);
    if ($doDropImage)
    {
        echo "true";
    }

    else
    {
        echo "false";
    }
    exit();
}

// Create Blog
if ($action)
{
    if ($_POST['desc'] == '')
    {
    	$Error->add('error', 'The description is required');
    	$errorClass['desc'] = 'error';
    }

    else
    {
    	$inputValue['desc'] = $_POST['desc'];
    	$editBlog += 1;
    }



    if ($_POST['text'] == '')
    {
    	$Error->add('error', 'You need to write something for the blog');
    	$errorClass['text'] = 'error';
    }

    else
    {
    	$inputValue['text'] = $_POST['text'];
    	$editBlog += 1;
    }


    // All checking done, create it
    if (($editBlog == 2) AND ($Error->ok()==false))
    {
        $DESCRIPTION    = $_POST['desc'];
        $BODY           = $_POST['text'];

        if ($id)
        {
        	$save = $blog->edit($id, $DESCRIPTION, $BODY);
        }

        # This is a new entry
        else
        {
            $save = $blog->create($DESCRIPTION, $BODY, $Auth->id);
        }

        if ($save == false)
    	{
    	    $Error->add('error', 'Something went wrong!');
    	}

        else
        {
    	    $Error->add('info', 'Success!');
            $new_uploads = array();

            # Check for uploads, error 4 is empty
            if (current($_FILES['upload']['error']) !== 4)
            {
                foreach($_FILES['upload']['type'] as $uid=>$type)
                {
                    # Check the file type and extension
                    $filetype   = current(explode('/', $type));
                    $ext        = strtolower(pathinfo ($_FILES["upload"]["name"][$uid], PATHINFO_EXTENSION));
                    $allowed    = array("jpg", "jpeg", "png", "gif");

                    # Ensure the mime is an image
                    if ($filetype == "image")
                    {
                        # Image seems valid, let's continue
                        if (in_array($ext, $allowed))
                        {
                            # Move to uploads with a new unique name
                            $newName        = uniqid(16);
                            $dir            = "uploads/blog/";
                            $newPathname    = $dir . $newName . "." . $ext;

                            # Ensure that the directory exists
                            if (!file_exists($dir))
                            {
                                # Otherwise create it
                                mkdir($dir);
                            }

                            # Move the file
                            move_uploaded_file($_FILES["upload"]["tmp_name"][$uid], $newPathname);

                            # Add it to the uploads to be booked into the db
                            $new_uploads[] = $newPathname;
                        }

                        # Invalid image type provided
                        else
                        {
                            $Error->add("error", $_FILES["upload"]["name"][$uid] . " is an invalid image. We accept " . implode(', ', $allowed) . " image types");
                        }
                    }

                    # Invalid image mime type
                    else
                    {
                        # Add the error message
                        $Error->add("error", $_FILES["upload"]["name"][$uid] . " is an invalid image.");
                    }
                }
            }

            # A new blog gets sent to facebook
            if ($id == false)
            {
                $string     = $BODY;
                $string     = BBCode::codify($string);
                $string     = BBCode::mailify($string);
                $string     = BBCode::decode($string);
                $string     = BBCode::linkify($string);
                $string     = htmlspecialchars($string);
                $blogUrl    = full_url_to_script('blog.php') . "?article=" . rawurlencode($DESCRIPTION) . "#" . rawurlencode($DESCRIPTION);


                # Ensure that we are suppose to be publishing to facebook
                if ((isset($_REQUEST['facebook'])) AND ($_REQUEST['facebook'] == "publish"))
                {
                    # Hook into FACEBOOK
                    require("API/facebook.php");

                    # Post to ThatGuy
                    $fbBlogPost = $facebook->api('/147906525337534/feed', 'POST',
                        array(
                            'link'          => $blogUrl,
                            'name'          => $DESCRIPTION . ", " . dater(time(), 'd M Y'),
                            'description'   => "See what I have been up to",
                            'caption'       => "Open the blog",
                            'message'       => $string,
                            'access_token'  => $fbPageToken
                        )
                    );

                    # Post to my Facebook
                    $fbBlogPost = $facebook->api('/me/feed', 'POST',
                        array(
                             'link'          => "https://www.facebook.com/thatguy.co.za",
                            'name'          => $DESCRIPTION . ", " . dater(time(), 'd M Y'),
                            'description'   => "See what I have been up to",
                            'caption'       => "Check out my page",
                            'message'       => $string,
                            'access_token'  => $fbUserToken
                        )
                    );
                }
            }

            # Check that the new images are not empty
            if (!empty($new_uploads))
    	    {
                # Add the images to the desc
                $appendImages   = $BODY;
                $blogId         = ($id == false) ? $save : $id;

                # We need to add the upload to the db as well as the post
        		foreach($new_uploads as $upload)
        		{
                    # Book the upload into the db
        		    $blog->uploads_add($Auth->id, $blogId, $upload);

                    # Append the image to the blog body
                    $appendImages .= PHP_EOL . "[img]" . $upload . "[/img]";
        		}

                # Update the blog by appending the images
                $blog->edit($blogId, $DESCRIPTION, $appendImages);
    	    }
    	    redirect('blog.php'); // Redirect to the display page
    	}
    }
}

// Get the blog uploads should there be anything
$uploads = ($id) ? Blog::uploads_get($id) : false;

// Create Blog
$msg=$Error->alert();
Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'blog', 'Auth'=>$Auth)),
	'content'=>Template::loadTemplate('blog_edit', array('errorClass'=>$errorClass, 'inputValue'=>$inputValue, 'id'=>$id, 'uploads'=>$uploads)),
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>
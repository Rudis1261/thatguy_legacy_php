<?php
//Bootstrap SPF
require 'includes/master.inc.php';
$mustauth=true;
require 'includes/user.inc.php';

$JS->add('prettify.js');
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
    	$inputValue['desc'] = strip_tags($_POST['desc']);
    	$editBlog += 1;
    }



    if ($_POST['text'] == '')
    {
    	$Error->add('error', 'You need to write something for the blog');
    	$errorClass['text'] = 'error';
    }

    else
    {
    	$inputValue['text'] = strip_tags($_POST['text']);
    	$editBlog += 1;
    }


    // All checking done, create it
    if (($editBlog == 2) AND ($Error->ok()==false))
    {
        if ($id)
        {
        	$save = $blog->edit($_POST['id'], strip_tags($_POST['desc']), strip_tags($_POST['text']));
        }

        else
        {
            $save = $blog->create(strip_tags($_POST['desc']), strip_tags($_POST['text']), $Auth->id);
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
                            $newPathname    = "assets/uploads/" . $newName . "." . $ext;

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

            if (!empty($new_uploads))
    	    {
        		foreach($new_uploads as $upload)
        		{
        		    $blog->uploads_add($Auth->id, $blog->id, $upload);
        		}
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
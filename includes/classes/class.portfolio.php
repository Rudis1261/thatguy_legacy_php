<?PHP
    # Author: Rudi Strydom
    # Date: Dec 2013
    # Purpose: The purpose of this class is to maintain my portfolio with a single page
    class Portfolio extends DBObject
    {
        # Internal Objects
        private $db;
        private $Auth;
        private $Error;

        # On construct variables
        public $types;
        public $file_types;
        public $images;
        public $unpublished;
        public $imgErrors;
        public $page;

        # Runtime variables
        public $script          = "portfolio.php";
        public $path            = "uploads/";
        public $pathThumb       = "uploads/thumb/";
        public $pathMedium      = "uploads/medium/";
        public $pathLarge       = "uploads/large/";
        public $services        = array("facebook", "flickr", "da");
        public $cameraFields    = array("make", "model", "iso", "aperture", "exposure");

        public $sizes           = array(
            "pathLarge"     => array("w"=>2560, "h"=>1600, "q"=>85),
            "pathMedium"    => array("w"=>960, "h"=>600, "q"=>85),
            "pathThumb"     => array("w"=>250, "h"=>160, "q"=>90)
        );


        # We will be creating another instance of the portfolio class and construct it
        public function __construct($id = null)
        {
            # Construct the parent
            $columns = array(
                'id',
                'type',
                'image',
                'name',
                'desc',
                'iso',
                'aperture',
                'exposure',
                'make',
                'model',
                'published',
                'timestamp',
                'facebook',
                'flickr',
                'da'
            );

            parent::__construct('portfolio', /*$columns,*/ $id);

            # Hook into the meta and get the types
            $meta               = new Meta("portfolio_types");
            $this->types        = $meta->getSimple();

            # Get the file types from the meta
            $meta->setType("file_types");
            $this->file_types   = $meta->getSimple();

            # Connect to the db
            $this->db           = Database::getDatabase();

            # Hook into the Auth system
            $this->Auth         = Auth::getAuth();

            # Hook into the Error system
            $this->Error        = Error::getError();

            # Uploading stuff you can expect these errors
            $this->imgErrors    = array(
                0=>"There is no error, the file uploaded with success",
                1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini. " . ini_get('upload_max_filesize'),
                2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                3=>"The uploaded file was only partially uploaded",
                4=>"No file was uploaded",
                6=>"Missing a temporary folder"
            );

            # Select the images from the db
            $this->db->query("SELECT `" . implode("`, `", $columns) . "` FROM portfolio ORDER by id DESC");

            # Get the results
            $results = $this->db->getRows();

            # Ensure that the db actually contained something
            if (!empty($results))
            {
                # Loop through all the images
                foreach($results as $res)
                {
                    # Was the image already published?
                    if ($res['published'])
                    {
                        $this->images[$res['id']]       = $res;
                    }

                    # Nope, add it to the pile to publish
                    else
                    {
                        $this->unpublished[$res['id']]  = $res;
                    }
                }
            }

            $this->page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        }


        # What should be displayed when the user gets to the page?
        public function defaultView()
        {
            //require_once("API/facebook.php");

            # Start the output
            $out = "";

            # Only admins can upload images
            if ($this->Auth->isAdmin())
            {
                $out .= $this->upload();
            }

            # Display the publisher, should there be images which require further details to be completed
            if (!empty($this->unpublished))
            {
                $out .= $this->publisher();
            }

            # Ensure that we have images to display
            if ((!empty($this->images)) AND (!empty($this->types)))
            {
                $out .= $this->showcase();
            }

            return $out;
        }


        # Trash a particular image
        public function drop($id)
        {
            # Only admins please
            if ($this->Auth->isAdmin() == false)
            {
                return "denied";
            }

            $image = false;

            # Check for the image in the images
            if (isset($this->images[$id]))
            {
                $image = $this->images[$id];
            }

            # It might also be an unpublished image
            elseif (isset($this->unpublished[$id]))
            {
                $image = $this->unpublished[$id];
            }

            # Ok so we actually have an image to work with. Cool
            if ($image !== false)
            {
                # Unlink the images
                if (file_exists($this->pathThumb . $image['image']))    unlink($this->pathThumb . $image['image']);
                if (file_exists($this->pathMedium . $image['image']))   unlink($this->pathMedium . $image['image']);
                if (file_exists($this->pathLarge . $image['image']))    unlink($this->pathLarge . $image['image']);

                # And drop it from the DB and return the result
                return $this->db->query("DELETE FROM portfolio WHERE id=" . $image['id']);
            }

            # Default to false
            return false;
        }


        # This function will be used to showcase the images
        public function showcase()
        {
            $images     = array();
            $out        = "<div class='btn-group'>";
            $type       = (isset($_REQUEST['type'])) ? $_REQUEST['type'] : false;

            # Loop through the types and create the button group
            foreach($this->types as $t)
            {
                # We will need to know if we have the current type
                $found = false;

                # Loop through the images
                foreach($this->images as $i)
                {
                    # Check whether images for the type exists
                    if (strstr($i['type'], $t))
                    {
                        # Yeah buddy, break out of the loop
                        $found = true;
                        break;
                    }
                }

                # Cool so the type actually has some images to be displayed
                if ($found !== false)
                {
                    # Now let's also check if it's the currently selected type?
                    $active = (($type !== false) AND ($type== $t)) ? " active " : "";
                    $out .= '<a class="btn btn-default ' . $active . '" href="?type=' . $t . '">' . $t . '</a>';
                }
            }

            # Close the btn group off
            $out .= "</div>";

            # No type defined, consider all the images
            if ($type == false)
            {
                $images = $this->images;
            }

            # Cool we are looking at a specific set of images
            else
            {
                # Loop through the images
                foreach($this->images as $i=>$image)
                {
                    # Match out the types
                    if (strstr($image['type'], $type))
                    {
                        $images[] = $image;
                    }
                }
            }

            # Add the pager and start the container output
            $out .= $this->pager($images);
            $out .= "<div class='publisher'>";

            # Chunk the array, get the index we want
            $chunks = array_chunk($images, 15);

            # Ensure it exists
            if (!empty($chunks[($this->page - 1)]))
            {
                $group = ($type !== false) ? $type : "all";

                # Loop through the chunk
                foreach($chunks[($this->page - 1)] as $image)
                {
                    $types  = "";
                    $c      = 3;

                    # Loop through the types
                    foreach((array)$this->types as $type)
                    {
                        # Attempt to get the types from the database
                        $getTypes   = json_decode($image['type'], true);
                        $active     = "";

                        # Ensure that there was something from the db and also ensure that the type is present
                        if (($getTypes !== false) AND (is_array($getTypes)) AND (in_array($type, $getTypes)))
                        {
                            $active = " checked ";
                        }

                        # And add the checkboxes for the details
                        $types .= '<div class="checkbox" tabindex="' . $c . '" align="left" style="line-height: 18px;">
                                        <label>
                                            <input ' . $active . ' value="' . $type . '" name="type[]" type="checkbox"> ' . $type . '
                                        </label>
                                    </div>';

                        $c++;
                    }

                    $cameraInfo = "";

                    # Let's see if and camera info was picked up and booked in?
                    if ((!empty($image['make'])) AND (!empty($image['model'])))
                    {
                        # We have info, let's add it
                        $cameraInfo .= "<div align='left'>";

                        # Let's get the data
                        foreach($this->cameraFields as $field)
                        {
                            # Let's only add the data which is populated
                            if (!empty($image[$field]))
                            {
                                $cameraInfo .= "<p><strong><span class='col-sm-5'>" . ucfirst($field) . "</span></strong> " . $this->exposure($image[$field]) . "</p>";
                            }
                        }

                        # Close the camera info off
                        $cameraInfo .= "</div>";
                    }

                    # Modal time :-D
                    $modal = '<div class="modal fade" id="modal' . $image['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" tabindex="-1" data-dismiss="modal" aria-hidden="true">' .icon("remove-sign", true) . '</button>
                                            <h4 class="modal-title" id="myModalLabel">What\'s this image all about?</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="pull-left col-sm-6" align="center">
                                                <img class="img-thumbnail" tabindex="-1" src="' . $this->pathThumb . $image['image'] . '" alt="' . $image['name'] . '"/>
                                                <br />
                                                <div class="clearfix"></div>
                                                <p>' . $cameraInfo . '</p>
                                            </div>
                                            <div class="pull-left col-sm-6" align="center">
                                                <form role="form" class="form-vertical" name="myForm' . $image['id'] . '">
                                                    <input type="hidden" name="id" value="' . $image['id'] . '" />
                                                    <input type="hidden" name="action" value="write" />
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="' . $image['name'] . '" tabindex="1" name="name" placeholder="Image Name">
                                                    </div>
                                                    <div class="form-group">
                                                        <textarea class="form-control" name="desc" tabindex="2" rows="4" placeholder="Image Description">' . $image['desc'] . '</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        ' . bool_select($image['published'], "published") . '
                                                    </div>
                                                    ' . $types . '
                                                    ' . $this->publishTypes($image) . '
                                                </form>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" data-id="' . $image['id'] . '" class="btn btn-default updateImage">' . icon("hdd") . '</button>
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">' . icon("remove", true) . '</button>
                                        </div>
                                    </div>
                                </div>
                            </div>';

                    $addCamera = '';

                    # Add the camera information, should it be available
                    if ((!empty($image['make'])) AND (!empty($image['model'])))
                    {
                        # Create the dropdown for the images
                        $addCamera = '<button type="button" class="btn btn-default btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                                            ' . icon("camera") . ' <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" style="width: 350px; padding: 15px;">
                                            <li>
                                                <p>
                                                    <strong>Camera Settings</strong>
                                                </p>
                                            </li>';

                        # Which fields are we getting the data from?
                        $fields = array("make", "model", "iso", "aperture", "exposure");

                        # Let's get the data
                        foreach($fields as $field)
                        {
                            $addCamera .= "<li>";

                            # Let's only add the data which is populated
                            if (!empty($image[$field]))
                            {
                                # Add the spans
                                $addCamera .= "<strong><span style='width: 100px; test-align: left;' align='left' class='pull-left'>" . ucfirst($field) . "</span></strong>";
                                $addCamera .= "<span align='left' class='pull-left' style='width: 200px;'>" . $this->exposure($image[$field]) . "</span>";
                                $addCamera .= "<span class='clearfix'></span>";
                            }

                            $addCamera .= '</li>';
                        }

                        $addCamera .= '</ul>';
                    }

                    # Define the buttons we will be using
                    $btnDownload    = href($this->pathLarge . $image['image'], icon("download"), "danger btn-sm", "Download Image", "_BLANK");
                    $btnEdit        = ($this->Auth->isAdmin()) ? '<a class="btn btn-sm btn-success" href="#"  data-toggle="modal" data-target="#modal' . $image['id'] . '">' . icon('pencil') . '</a>' . $modal : "";
                    $btnDelete      = ($this->Auth->isAdmin()) ? '<a class="btn btn-sm btn-default confirmDelete" href="?action=drop&id=' . $image['id'] . '">' . icon('trash') . '</a>' : "";

                    # Add each image tile to be displayed
                    $out .= '<span>
                                <a style="text-decoration: none;" href="' . $this->pathLarge . $image['image'] . '" data-lightbox="' . $group . '" title="' . $image['name'] . " - " . $image['desc'] . '">
                                    <img class="collage" data-id="' . $image['id'] . '" src="' . $this->pathThumb . $image['image'] . '" alt="' . $image['name'] . '"/>
                                </a>
                                <div class="downloadBtn btn-group">
                                    ' . $btnDownload . '
                                    ' . $addCamera . '
                                    ' . $btnDelete . '
                                    ' . $btnEdit . '
                                </div>
                            </span>';
                }

                # Clearfixing just in case.
                $out .= "<div class='clearfix'></div>";
            }

            # Close of the contained and add a pager at the footer.
            $out .= "</div>";
            $out .= $this->pager($images);

            # Return the output
            return $out;
        }


        # I would like a generic function to get the exposure time
        public function exposure($in)
        {
            # Attempt to split with the forward slash
            $split = explode('/', $in);
            if (count($split) == 2)
            {
                # Seconds
                if (end($split) == 1)
                {
                    $exposure = $split[0] . " seconds";
                }

                # nth of a second
                else
                {
                    $exposure = $split[1] . "th of a second";
                }

                # Return the exposure
                return $exposure;
            }

            # Otherwise return the incoming value
            return $in;
        }


        # I want to know if there are any commonly used words in a set of images.
        # The more it's used the greater I would like the text to be
        public function keyWords($imageArray)
        {
            $largest    = 25;
            $smallest   = 16;
            $words      = array();
            $find       = array(".", ",");
            $replace    = array("", "");

            # Check whether we were handed an array
            if (is_array($imageArray))
            {
                # Loop through the images
                foreach($imageArray as $image)
                {
                    # Replace the stuff we are not interested in
                    $image['name']  = str_replace($find, $replace, $image['name']);
                    $image['desc']  = str_replace($find, $replace, $image['desc']);

                    # Explode by space
                    $titleWords     = explode(" ", $image['name']);
                    $allWords       = explode(" ", $image['desc']);

                    # Combine the 2 arrays
                    //$allWords       = array_merge($titleWords, $descWords);
                    //$allWords       = array_unique($allWords);

                    # Loop through the words array
                    foreach($allWords as $word)
                    {
                        # Let's stick to lowercase
                        $word = strtolower($word);

                        # And remove all the words which are to short
                        if (strlen($word) < 4)
                        {
                            continue;
                        }

                        # If the index exists, let's increment it
                        if (isset($words[$word]))
                        {
                            $words[$word] = $words[$word] + 1;
                        }

                        # Otherwise initialize it
                        else
                        {
                            $words[$word] = 1;
                        }
                    }
                }
            }

            if (!empty($words))
            {
                $max = max($words);
                echo "<div class='row'>";
                foreach($words as $word=>$count)
                {
                    $size = ceil($largest / $count);
                    echo "<div style='font-size: " . $size . "px'>&nbsp;&nbsp;" . ucfirst($word) . "&nbsp;&nbsp;</div>";
                }
                echo "</div>";
            }

            //printr($words);
            return false;
        }


        # I would like a generic pager, thank you
        public function pager($inputArray, $searchAppend="", $perPage=15, $padding=3)
        {
            $itemCount  = count($inputArray);
            $numPages   = ceil($itemCount / $perPage);

            # Start building the pager
            $paging = '<div class="row">
                        <ul class="pagination">'. nl();

            # Loop through the number of pages
            for($i=1; $i<=$numPages; $i++)
            {
                $min = $this->page - $padding;
                $max = $this->page + $padding;

                if ($i == 1)
                {
                    $paging .= '<li><a href="?page=' . $i . $searchAppend .'">&laquo;</a></li>'. nl();
                }

                if ($i == $this->page)
                {
                    $paging .= '<li class="active"><a href="#">' . $i . '</a></li>'. nl();
                }

                elseif (($i >= $max) xor ($i > $min))
                {
                    $paging .= '<li><a href="?page=' . $i . $searchAppend . '">' . $i . '</a></li>'. nl();
                }

                if ($i == $numPages)
                {
                    $paging .= '<li><a href="?page=' . $numPages . $searchAppend . '">&raquo;</a></li>'. nl();
                }
            }
            $paging .='</ul></div>'. nl();
            return $paging;
        }


        # Get the publish types available
        public function publishTypes($imageArray)
        {
            $output     = "<div class='form-group' style='line-height: 18px;'><hr />";
            $output    .= '<div>';

            # Loop through the types
            foreach($this->services as $type)
            {
                # Check that the image has not yet been published
                if (empty($imageArray[$type]))
                {
                    $output .= '<div align="left" class="pull-left">
                                    <label>
                                        <input value="' . time() . '" name="' . $type . '" type="checkbox">
                                        <img class="' . $type . '" src="assets/img/blank.gif" alt="' . $type . '"/>
                                        &nbsp;&nbsp;&nbsp;
                                    </label>
                                </div>';
                }

                else
                {
                    $output .= '<div align="left" class="pull-left">
                                    <label>
                                        <img width="35" height="35" src="assets/img/' . $type . '.jpg" alt="' . $type . '"/>
                                        &nbsp;&nbsp;&nbsp;
                                    </label>
                                </div>';
                }
            }

            $output .= '</div></div>';
            return $output;
        }


        # The publisher is the page which will be used to complete the image details
        public function publisher()
        {
            # Start the output
            $out = "<h2>Publisher " . icon('camera') . " <small>Review, name and publish</small></h2>";

            # Let's make it stand out a bit
            $out .= "<div class='publisher'>";

            # Ensure that there are unpublished images to work with
            if (!empty($this->unpublished))
            {
                # loop through them
                foreach($this->unpublished as $image)
                {
                    $types  = "";
                    $c      = 3;

                    # Loop through the types
                    foreach((array)$this->types as $type)
                    {
                        # Attempt to get the types from the database
                        $getTypes   = json_decode($image['type'], true);
                        $active     = "";

                        # Ensure that there was something from the db and also ensure that the type is present
                        if (($getTypes !== false) AND (is_array($getTypes)) AND (in_array($type, $getTypes)))
                        {
                            $active = " checked ";
                        }

                        # And add the checkboxes for the details
                        $types .= '<div class="checkbox" tabindex="' . $c . '" align="left" style="line-height: 18px;">
                                        <label>
                                            <input ' . $active . ' value="' . $type . '" name="type[]" type="checkbox"> ' . $type . '
                                        </label>
                                    </div>';

                        $c++;
                    }

                    $cameraInfo = "";

                    # Let's see if and camera info was picked up and booked in?
                    if ((!empty($image['make'])) AND (!empty($image['model'])))
                    {
                        # We have info, let's add it
                        $cameraInfo .= "<div align='left'>";

                        # Let's get the data
                        foreach($this->cameraFields as $field)
                        {
                            # Let's only add the data which is populated
                            if (!empty($image[$field]))
                            {
                                $cameraInfo .= "<p><strong><span class='col-sm-5'>" . ucfirst($field) . "</span></strong> " . $this->exposure($image[$field]) . "</p>";
                            }
                        }

                        # Close the camera info off
                        $cameraInfo .= "</div>";
                    }

                    # Modal time :-D
                    $modal = '<div class="modal fade" id="modal' . $image['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" tabindex="-1" data-dismiss="modal" aria-hidden="true">' .icon("remove-sign", true) . '</button>
                                            <h4 class="modal-title" id="myModalLabel">What\'s this image all about?</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="pull-left col-sm-6" align="center">
                                                <img class="img-thumbnail" tabindex="-1" src="' . $this->pathThumb . $image['image'] . '" alt="' . $image['name'] . '"/>
                                                <br />
                                                <div class="clearfix"></div>
                                                <p>' . $cameraInfo . '</p>
                                            </div>
                                            <div class="pull-left col-sm-6" align="center">
                                                <form role="form" class="form-vertical" name="myForm' . $image['id'] . '">
                                                    <input type="hidden" name="id" value="' . $image['id'] . '" />
                                                    <input type="hidden" name="action" value="write" />
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="' . $image['name'] . '" tabindex="1" name="name" placeholder="Image Name">
                                                    </div>
                                                    <div class="form-group">
                                                        <textarea class="form-control" name="desc" tabindex="2" rows="4" placeholder="Image Description">' . $image['desc'] . '</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        ' . bool_select($image['published'], "published") . '
                                                    </div>
                                                    ' . $types . '
                                                    ' . $this->publishTypes($image) . '
                                                </form>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" data-id="' . $image['id'] . '" class="btn btn-default updateImage">' . icon("hdd") . '</button>
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">' . icon("remove", true) . '</button>
                                        </div>
                                    </div>
                                </div>
                            </div>';

                    # I would like to see if the image has camera information. Let's add a button to display it
                    $addCamera = ((!empty($image['make'])) AND (!empty($image['model']))) ? btn(icon('camera'), 'primary btn-sm disabled', "Has camera info") : "";

                    # Add the tile
                    $out .= '<span class="portfolioTiles" data-id="' . $image['id'] . '">
                                <a style="text-decoration: none;" href="#"  data-toggle="modal" data-target="#modal' . $image['id'] . '">
                                    <img class="img-thumbnail" data-id="' . $image['id'] . '" src="' . $this->pathThumb . $image['image'] . '" alt=""/>
                                </a>
                                <div class="btn-group actions" align="right">
                                    <a class="btn btn-sm btn-default" target="_BLANK" href="' . $this->pathLarge . $image['image'] . '">' . icon('fullscreen') . '</a>
                                    <a class="btn btn-sm btn-success" href="#"  data-toggle="modal" data-target="#modal' . $image['id'] . '">' . icon('pencil') . '</a>
                                    <a class="btn btn-sm btn-danger confirmDelete" href="?action=drop&id=' . $image['id'] . '">' . icon('trash') . '</a>
                                    ' . $addCamera . '
                                </div>
                                ' . $modal . '
                            </span>';
                }
            }

            $out .= "</div><br />";
            return $out;
        }


        # This function will be responsible for the uploading of images
        public function upload()
        {
            # Start the output
            $out = '<form role="form" method="post" enctype="multipart/form-data">
                      <div class="form-group">
                        <div class="input-group">
                            <input type="file" icon="picture" title="Select images" name="upload[]" class="btn-success btn-lg" multiple>
                            <button type="submit" class="btn btn-default btn-lg addOn">Upload ' . icon("cloud") . '</button>
                        </div>
                      </div>
                    </form>';

            # let's check if we have files to work with?
            if (isset($_FILES['upload']))
            {
                # Ensure that the directories exists
                mkdir_if_not_exist($this->path);
                mkdir_if_not_exist($this->pathThumb);
                mkdir_if_not_exist($this->pathMedium);
                mkdir_if_not_exist($this->pathLarge);

                # Set the error coutner
                $errors     = array();

                # How many images are we dealing with here?
                $imgCount   = count($_FILES['upload']['name']);

                # Get the indexes and loop through them
                foreach(range(0, $imgCount - 1) as $i)
                {
                    # Extract the details
                    $imgName    = $_FILES['upload']['name'][$i];
                    $imgError   = $_FILES['upload']['error'][$i];
                    $imgTmpName = $_FILES['upload']['tmp_name'][$i];
                    $imgType    = $_FILES['upload']['type'][$i];
                    $mime       = current(explode('/', $imgType));
                    $image      = array();

                    # First check if any images were provided?
                    if ($imgError !== 0)
                    {
                        # Error out and break
                        $errors[] = $this->imgErrors[$imgError];
                        continue;
                    }

                    # We have files to let's attempt to get the extension
                    $imgDetails = pathinfo($imgName);
                    $extension  = strtolower($imgDetails["extension"]);

                    # Incorrect mime type, skip iteration
                    if ($mime !== "image")
                    {
                        # Error out, skip iteration
                        $errors[] = $imgName . " is not an image";
                        continue;
                    }

                    # It's an image, cool. Now is it allowed?
                    if (!in_array($extension, $this->file_types))
                    {
                        # Error out, skip iteration
                        $errors[] = $imgName . " is not a supported image type";
                        continue;
                    }

                    # Cool so the image is not erroneous, we can now continue
                    # First attempt to get the exif info
                    $exif       = $this->cameraUsed($imgTmpName);

                    # Create a new unique name for the image
                    $imgNewName = uniqid(16) . "." . $extension;

                    # Move the image and create the different sizes
                    move_uploaded_file($imgTmpName, $this->path . $imgNewName) or die ("Could not move image " . $imgNewName . " to " . $this->path);

                    # Loop through the sizes
                    foreach($this->sizes as $type=>$dimension)
                    {
                        # Load the image into GD and resize it to the 3 sizes
                        $GD = new GD($this->path . $imgNewName);

                        # Resize the image to the resolution
                        $GD->resizeToResolution(
                            $dimension['w'],
                            $dimension['h'],
                            $dimension['q'],
                            true
                        );

                        # Save the actual image
                        $GD->saveAs($this->$type . $imgNewName);
                    }

                    # Build info array
                    $info                   = array();
                    $info['image']          = $imgNewName;
                    $info['name']           = "";
                    $info['desc']           = "";
                    $info['iso']            = $exif['iso'];
                    $info['aperture']       = $exif['aperture'];
                    $info['exposure']       = $exif['exposure'];
                    $info['make']           = $exif['make'];
                    $info['model']          = $exif['model'];
                    $info['published']      = 0;
                    $info['timestamp']      = time();

                    # Write it to the DB
                    $write = $this->write($info);

                    # If successful, delete the image
                    if ($write !== false)
                    {
                        unlink($this->path . $imgNewName);
                    }
                }

                # Should there be multiple errors, display the,
                if (!empty($errors))
                {
                    # By looping through them
                    foreach($errors as $error)
                    {
                        # Add the individual ones
                        $this->Error->add("error", $error);
                    }
                }

                # No errors, redirect
                else
                {
                    $this->Error->add("info", "Successfull upload!");
                    redirect($this->script);
                }
            }

            # Return the result
            return $out;
        }


        # Let's attempt to read the exif information from the image
        public function cameraUsed($imagePath)
        {
            # The default empty return
            $return = array(
                'make'      => "",
                'model'     => "",
                'exposure'  => "",
                'aperture'  => "",
                'iso'       => ""
            );

            // Check if the variable is set and if the file itself exists before continuing
            if ((isset($imagePath)) AND (file_exists($imagePath)))
            {
                // There are 2 arrays which contains the information we are after, so it's easier to state them both
                $exif_ifd0 = @read_exif_data($imagePath ,'IFD0' ,0);
                $exif_exif = @read_exif_data($imagePath ,'EXIF' ,0);

                # Ensure that we actually got some information
                if (($exif_ifd0 !== false) AND ($exif_exif !== false))
                {
                    // Make
                    if (@array_key_exists('Make', $exif_ifd0))
                    {
                        $return['make']     = $exif_ifd0['Make'];
                    }

                    // Model
                    if (@array_key_exists('Model', $exif_ifd0))
                    {
                        $return['model']    = $exif_ifd0['Model'];
                    }

                    // Exposure
                    if (@array_key_exists('ExposureTime', $exif_ifd0))
                    {
                        $return['exposure'] = $exif_ifd0['ExposureTime'];
                    }

                    // Aperture
                    if (@array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED']))
                    {
                        $return['aperture'] = str_replace("f/", "", $exif_ifd0['COMPUTED']['ApertureFNumber']);
                    }

                    // ISO
                    if (@array_key_exists('ISOSpeedRatings',$exif_exif))
                    {
                        $return['iso']     = $exif_exif['ISOSpeedRatings'];
                    }
                }
            }

            # Return either an empty array, or the details which we were able to extrapolate.
            return $return;
        }


        # The clean script will be used to clean any images which are not in the db.
        # This should only happen once a day, early morning hours
        public function clean()
        {
            # Select all the images from the database
            $this->db->query("SELECT `id`, `image`, `published` FROM portfolio");

            # Ensure we got something
            $images = $this->db->getRows();

            # Ensure that we have images to work with
            if (!empty($images))
            {
                # First off we will delete images which are not published yet
                foreach($images as $index=>$image)
                {
                    # If the image is not published, we shall delete it
                    if ($image['published'] == 0)
                    {
                        # First the thumbnail
                        if (file_exists($this->pathThumb . $image['image']))
                        {
                            unlink($this->pathThumb . $image['image']);
                        }

                        # Then the medium image
                        if (file_exists($this->pathMedium . $image['image']))
                        {
                            unlink($this->pathMedium . $image['image']);
                        }

                        # Lastly the large image
                        if (file_exists($this->pathLarge . $image['image']))
                        {
                            unlink($this->pathLarge . $image['image']);
                        }

                        # Now delete the image from the DB
                        $this->db->query("DELETE FROM portfolio WHERE id=" . $this->db->quote($image['id'])) or die ("Could not delete image " . $image['image']);

                        # Let's show what where doing
                        echo "Deleted unpublished image " . $image['image'] . PHP_EOL;

                        # And then unset the index from the images array
                        unset($images[$index]);
                    }
                }

                ######################################################
                # TODO, scan directory and delete image
                ######################################################
            }

            # Otherwise default to a failure
            return false;
        }


        # Write the entry to db, if id exists it will update it
        public function write($info)
        {
            # Let's see if we have an id?
            $id             = (isset($info['id'])) ? $info['id'] : null;

            # Create an instance of the portfolio
            $p              = new Portfolio($id);

            # Let's set the id
            if (isset($info['id']))
            {
               $p->id       = $info['id'];
            }

            # Update the details
            $p->image       = $info['image'];
            $p->name        = $info['name'];
            $p->desc        = $info['desc'];
            $p->iso         = $info['iso'];
            $p->aperture    = $info['aperture'];
            $p->exposure    = $info['exposure'];
            $p->make        = $info['make'];
            $p->model       = $info['model'];
            $p->published   = $info['published'];
            $p->timestamp   = $info['timestamp'];

            $p->type        = (isset($info['type'])) ? json_encode($info['type']) : "";
            $p->facebook    = (isset($info['facebook']))    ? $info['facebook']     : "";
            $p->flickr      = (isset($info['flickr']))      ? $info['flickr']       : "";
            $p->da          = (isset($info['da']))          ? $info['da']           : "";


            # Check whether we should be inserting
            if (is_null($id))
            {
                $p->insert();
            }

            # Or updating
            else
            {
                $p->update();
            }

            # Check if the action was successful?
            if ($p->ok())
            {
                return $p->id;
            }
            return false;
        }


        public function upsert($id)
        {
            $image = false;

            # Attempt to find the image's information
            if (isset($this->images[$id]))
            {
                $image = $this->images[$id];
            }

            # Was it published yet?
            elseif (isset($this->unpublished[$id]))
            {
                $image = $this->unpublished[$id];
            }

            # We found the image, let's override the data
            if ($image !== false)
            {
                # Loop through the form data and get the key value pairs
                foreach($_REQUEST as $key=>$req)
                {
                    # Check if it's part of the data set?
                    if (isset($image[$key]))
                    {
                        # Override the image's information
                        $image[$key] = $req;
                    }
                }

                # Check whether we should publish an image to the various services
                foreach($this->services as $type)
                {
                    if (isset($_REQUEST[$type]))
                    {
                        # Dispatch it to the method to post the image
                        $this->$type($image);
                    }
                }

                # Write the image's new data to DB
                $write = $this->write($image);

                # Check whether the write was a success
                if ($write !== false)
                {
                    echo "success";
                    return true;
                }
            }

            # Default to failed state
            echo "failure";
            return false;
        }


        # Fire up the Service API's
        public function initServices()
        {
            require("API/facebook.php");
            require("API/flickr.php");
        }


        # Hook into the facebook API
        public function facebook($image)
        {
            # Hook into the API's we need
            require("API/facebook.php");

            $fbPageToken = Options::get('fbPageToken');

            // add a status message
            $photo = $facebook->api('/147906525337534/photos', 'POST',
                array(
                    'image' => '@' . realpath($this->pathLarge . $image['image']),
                    'message' => $image['name'],
                    'access_token' => $fbPageToken,
                )
            );
        }


        # Flickr needs it's own set of posting
        public function flickr($image)
        {
            # Hook into the Flickr API
            require("API/flickr.php");

            # Send the image to Flickr
            $f->sync_upload(
                $this->pathLarge . $image['image'],
                $image['name'],
                $image['desc'],
                NULL,
                1,
                0,
                0
            );
        }


        # We will be using the DA Stash method of posting
        public function da($image)
        {
            # Get the details from the options
            $daEmailAddress = Options::get('daEmailAddress');
            $daEmailFrom    = Options::get('daEmailFrom');
            $cameraInfo     = "";

            foreach($this->cameraFields as $field)
            {
                if (!empty($image[$field]))
                {
                    if (empty($cameraInfo)) $cameraInfo = "<hr />";
                    $cameraInfo .= "<b>" . strtoupper($field) . "</b>: " . $image[$field] . "<br />";
                }
            }

            # Fire up the php mailer class
            $daMail         = new PHPMailer(true);
            $daMail->AddAddress($daEmailAddress, 'DA Stash');
            //$daMail->AddAddress('iam@thatguy.co.za', 'DA Stash');
            $daMail->SetFrom($daEmailFrom, 'ThatGuy.co.za');
            $daMail->Subject = $image['name'];
            $daMail->AltBody = $image['desc'] . $cameraInfo;
            $daMail->MsgHTML($image['desc'] . $cameraInfo);
            $daMail->AddAttachment($this->pathLarge . $image['image']); // Attach the image

            # Attempt to send the mail
            try
            {
              $daMail->Send();
            }

            # And catch an exception
            catch (phpmailerException $e)
            {
              $Error->add('error',$e->errorMessage());
            }
        }
    }
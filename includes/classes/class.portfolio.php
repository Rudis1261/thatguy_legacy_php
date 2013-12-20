<?PHP
    # Author: Rudi Strydom
    # Date: Dec 2013
    # Purpose: The purpose of this class is to maintain my portfolio with a single page
    class Portfolio extends DBObject
    {
        # We need some variables to make this work
        private $db;
        private $Auth;
        private $Error;

        public $types;
        public $file_types;
        public $script      = "portfolio.php";
        public $path        = "uploads/";
        public $pathThumb   = "uploads/thumb/";
        public $pathMedium  = "uploads/medium/";
        public $pathLarge   = "uploads/large/";
        public $imgErrors;


        # We will be creating another instance of the portfolio class and construct it
        public function __construct($id = null)
        {
            # Construct the parent
            $columns = array("id", "type", "thumb", "medium", "large", "name", "desc", "iso", "aperture", "shutter", "make", "model", "published");
            parent::__construct('portfolio', $columns, $id);

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

            $this->imgErrors    = array(
                0=>"There is no error, the file uploaded with success",
                1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini. " . ini_get('upload_max_filesize'),
                2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                3=>"The uploaded file was only partially uploaded",
                4=>"No file was uploaded",
                6=>"Missing a temporary folder"
            );
        }


        # What should be displayed when the user gets to the page?
        public function defaultView()
        {
            # Start the output
            $out = "";

            # Only admins can upload images
            if ($this->Auth->isAdmin())
            {
                $out .= $this->upload();
            }

            return $out;
        }


        # This function will be responsible for the uploading of images
        public function upload()
        {
            # Start the output
            $out = '<form role="form" method="post" enctype="multipart/form-data">
                      <div class="form-group">
                        <div class="input-group">
                            <button type="submit" class="btn btn-default addOn">Upload ' . icon("cloud") . '</button>
                            <input type="file" icon="picture" title="Select images" name="upload[]" class="btn-success" multiple>
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
                    $extension  = $imgDetails["extension"];

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

                    # Load the image into GD and resize it to the 3 sizes
                    $GD     = new GD($imgNewName);
                    $sizes  = array(
                        "pathThumb"     => array("w"=>250, "h"=>160),
                        "pathMedium"    => array("w"=>960, "h"=>600),
                        "pathLarge"     => array("w"=>2560, "h"=>1920)
                    );

                    # Loop through the sizes
                    foreach($sizes as $type=>$dimension)
                    {
                        // cropCentered($w, $h)
                        $GD->cropCentered($dimension['w'], $dimension['h']);
                        $GD->saveAs($this->$type . $imgNewName);
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
                        $return['aperture'] = $exif_ifd0['COMPUTED']['ApertureFNumber'];
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
            $this->db->query("SELECT `id`, `thumb`, `medium`, `large`, `published` FROM portfolio");

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
                        if (file_exists($image['thumb']))
                        {
                            unlink($image['thumb']);
                        }

                        # Then the medium image
                        if (file_exists($image['medium']))
                        {
                            unlink($image['medium']);
                        }

                        # Lastly the large image
                        if (file_exists($image['large']))
                        {
                            unlink($image['large']);
                        }

                        # Now delete the image from the DB
                        $this->db->query("DELETE FROM portfolio WHERE id=" . $this->db->quote($image['id'])) or die ("Could not delete image " . $image['thumbnail']);

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

            # Update the details
            $p->type        = json_encode($info['type']);
            $p->thumb       = $this->$pathThumb . $info['image'];
            $p->medium      = $this->$pathMedium . $info['image'];
            $p->large       = $this->$pathLarge . $info['image'];
            $p->name        = $info['name'];
            $p->desc        = $info['desc'];
            $p->iso         = $info['iso'];
            $p->aperture    = $info['aperture'];
            $p->shutter     = $info['shutter'];
            $p->make        = $info['make'];
            $p->model       = $info['model'];
            $p->published   = $info['published'];
            $p->date        = time();

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
    }
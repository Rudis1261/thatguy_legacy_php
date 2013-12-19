<?PHP
    # Author: Rudi Strydom
    # Date: Dec 2013
    # Purpose: The purpose of this class is to maintain my portfolio with a single page
    class Portfolio extends DBObject
    {
        # We need some variables to make this work
        private $db;
        private $Auth;

        public $types;
        public $path        = "upload";
        public $pathThumb   = "uploads/thumb/";
        public $pathMedium  = "uploads/medium/";
        public $pathLarge   = "uploads/large/";


        # We will be creating another instance of the portfolio class and construct it
        public function __construct($id = null)
        {
            # Construct the parent
            $columns = array("id", "type", "thumb", "medium", "large", "name", "desc", "iso", "aperture", "shutter", "make", "model", "published");
            parent::__construct('portfolio', $columns, $id);

            # Hook into the meta and get the types
            $this->types    = new Meta("portfolio_types");

            # Connect to the db
            $this->db       = Database::getDatabase();

            # Hook into the Auth system
            $this->Auth     = Auth::getAuth();
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
                            <input type="file" title="Select images" name="uploads" class="btn-success" multiple>
                        </div>
                      </div>
                    </form>';
            return $out;
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
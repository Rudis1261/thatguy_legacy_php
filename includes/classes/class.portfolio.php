<?PHP
    # Author: Rudi Strydom
    # Date: Dec 2013
    # Purpose: The purpose of this class is to maintain my portfolio with a single page
    class Portfolio extends DBObject
    {
        # We need some variables to make this work
        public $types;
        public $pathThumb = "uploads/thumb/";
        public $pathLarge = "uploads/large/";

        # We will be creating another instance of the portfolio class and construct it
        public function __construct($id = null)
        {
            # Construct the parent
            $columns = array("id", "type", "thumb", "large", "name", "desc", "iso", "aperture", "shutter", "make", "model");
            parent::__construct('portfolio', $columns, $id);

            # Hook into the meta and get the types
            $this->types = new Meta("portfolio_types");
        }

        public function write($info)
        {
            # Let's see if we have an id?
            $id             = (isset($info['id'])) ? $info['id'] : null;

            # Create an instance of the portfolio
            $p              = new Portfolio($id);

            # Update the details
            $p->type        = json_encode($info['type']);
            $p->thumb       = $this->$pathThumb . $info['thumb'];
            $p->large       = $this->$pathLarge . $info['large'];
            $p->name        = $info['name'];
            $p->desc        = $info['desc'];
            $p->iso         = $info['iso'];
            $p->aperture    = $info['aperture'];
            $p->shutter     = $info['shutter'];
            $p->make        = $info['make'];
            $p->model       = $info['model'];
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
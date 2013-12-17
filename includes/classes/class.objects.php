<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('nid', 'username', 'password', 'level', 'email'), $id);
        }
    }

    class Users extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('username', 'level', 'email'));
        }

        public static function types()
        {
            $db = Database::getDatabase();
            $db->query("SHOW COLUMNS FROM users LIKE 'level'");
            $row = $db->getRow();
            $type = $row['Type'];
            preg_match('/enum\((.*)\)$/', $type, $matches);
            $vals = explode(',', $matches[1]);
            if (is_array($vals))
            {
                return str_replace("'", '', $vals);
            } else {
                return false;
            }
        }
    }

    class BlogUploads extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('blog_uploads', array('user_id', 'blog_id', 'image'), $id);
        }
    }

    class BlogComments extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('blog_comments', array('user_id', 'blog_id', 'comment', 'timestamp', 'username'), $id);
        }
    }

    // Series and Episode classes to be used in TV Tracker
    class Series extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('tv', $id);
        }
    }

    // Series and Episode classes to be used in TV Tracker
    class Episodes extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('tv_episode', $id);
        }
    }

    // TV Alert will be used to track which alert email has been sent to which user
    class TVAlert extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('tv_alert', $id);
        }
    }
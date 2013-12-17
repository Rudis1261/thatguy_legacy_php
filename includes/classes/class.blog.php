<?PHP
    class Blog extends DBObject
    {

        public function __construct($id = null)
        {
            parent::__construct('blog', array('user_id', 'desc', 'body', 'timestamp'), $id);
        }

        // Create a blog
        public function create($desc, $message, $user_id, $uploads=false, $comments=false)
        {
            $b = new Blog();
            $b->desc =          $desc;
            $b->body =          $message;
            $b->user_id =       $user_id;
            $b->timestamp =     time();
            $b->insert();
            if ($b->ok())
            {
                return $b->id;
            }
            return false;
        }

        // Edit a blog
        public function edit($id, $desc, $message, $timestamp=false, $uploads=false, $comments=false)
        {
            $b = new Blog($id);
            $b->desc =          $desc;
            $b->body =          $message;
            if ($timestamp !== false)
            {
                $b->timestamp =     time();
            }
            $b->update();
            if ($b->ok())
            {
                return true;
            }
            return false;
        }

        // Check if the blog is set to be removed and also confirm whether the user has adequate rights to do so
        public static function check_remove($user_id, $admin=false)
        {
            // Lets keep it simple to remove a blog
            if ((isset($_GET['action']))
            && ($_GET['action'] == 'remove')
            && (is_numeric($_GET['blog'])))
            {

                // Pull up the blog and check rights
                $b = new Blog($_GET['blog']);
                if (($b->user_id == $user_id)
                or ($admin !== false))
                {
                    // So delete the blog and redirect the page
                    $b->uploads_purge($b->id);
                    $b->comments_purge($b->id);
                    $b->delete();
                    if ($b->ok())
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        // Get the user's id for a specific blog, this is needed when deleting a blog
        public static function user($id)
        {
            $b = new Blog($id);
            if ($b->ok())
            {
                return $b->user_id;
            }
            return false;
        }

        // Get all the data from the blogs
        public static function get($id)
        {
            $d = array();
            $b = new Blog($id);
            if ($b->ok())
            {
                $d['id'] =          $b->id;
                $d['desc'] =        $b->desc;
                $d['body'] =        $b->body;
                $d['timestamp'] =   $b->timestamp;
                $d['user_id'] =     $b->user_id;
                return $d;
            }
            return false;
        }

        // Check if the Blog exists, if it does then return the id
        public function descToId($desc)
        {
            $b = new Blog();
            $b->select($desc, 'desc');
            if ($b->ok())
            {
                return $b->id;
            }
            return false;
        }

        // Get an array from the upload
        public static function uploads_get($blog_id)
        {
            $db = Database::getDatabase();
            $get = $db->getValues("SELECT image FROM blog_uploads WHERE blog_id = " . $db->quote($blog_id));
            if(count($get) > 0)
            {
                return $get;
            } else {
                return array();
            }
        }

        // Insert image into a blog
        public function uploads_add($user_id, $blog_id, $image)
        {
            $u = new BlogUploads();
            $u->user_id = $user_id;
            $u->blog_id = $blog_id;
            $u->image = $image;
            $u->Insert();
            if ($u->ok())
            {
                return true;
            }
            return false;
        }

        // Delete an image from the blog
        public function uploads_delete($image)
        {
            $u = new BlogUploads();
            $u->select($image, 'image');
            $u->delete();
            if ($u->ok())
            {
                if (file_exists($image))
                {
                    unlink($image);
                }
                return true;
            }
            return false;
        }

        // Delete all images for a particular blog
        public function uploads_purge($blog_id)
        {
            $getImages = Blog::uploads_get($blog_id);
            foreach($getImages as $upload)
            {
                $targetFolder = 'assets/uploads'; // Relative to the root
                $thumbFolder = 'assets/uploads/thumbs';
                if (file_exists($upload))
        		{
                    unlink($upload);
                    unlink(str_replace($targetFolder,$thumbFolder, $upload));
        		}
                $this->uploads_delete($upload);
            }
        }

        // Display all comments for a blog
        public static function comments_get($blog_id)
        {
            $comments = array();
            $db = Database::getDatabase();
            $c = Blog::glob('BlogComments',"SELECT `id`, `user_id`, `blog_id`, `comment`, `timestamp`, `username` FROM blog_comments WHERE blog_id = " . $db->quote($blog_id) . " ORDER By id DESC");
            if(count($c) > 0)
            {
                foreach($c as $comment)
                {
                    $comments[$comment->id]['comment']              = $comment->comment;
                    $comments[$comment->id]['user_id']              = $comment->user_id;
                    $comments[$comment->id]['timestamp']            = $comment->timestamp;
                    $comments[$comment->id]['username']            = $comment->username;
                }

            }
            return $comments;
        }

        // Insert image into a blog
        public static function comments_add($user_id)
        {
            // Lets keep it simple to remove a blog
            if ((isset($_POST['action']))
            && ($_POST['action'] == 'Comment')
            && (is_numeric($_POST['blog']))
            && ($_POST['comment'] !== ''))
            {
                $db = Database::getDatabase();
                $db = Database::getDatabase();
                $exists = $db->getValues('SELECT comment FROM blog_comments WHERE user_id="' . $user_id . '" and blog_id="' . $_POST['blog'] . '"');

                if (count($exists) > 0)
                {
                    foreach($exists as $result)
                    {
                        if ($result == $_POST['comment'])
                        {
                            return false;
                            break;
                        }
                    }
                }


                $user = new User($user_id);
                $u = new BlogComments();
                $u->user_id = $user_id;
                $u->blog_id = $_POST['blog'];
                $u->comment = $_POST['comment'];
                $u->timestamp = time();
                $u->username = ucfirst($user->username);
                $u->Insert();
                if ($u->ok())
                {
                    return true;
                }
            }
            return false;
        }


        // Insert image into a blog
        public static function comments_edit($user_id)
        {
            if ((isset($_POST['action']))
            && ($_POST['action'] == 'Edit Comment')
            && (is_numeric($_POST['id']))
            && ($_POST['comment'] !== ''))
            {
                $u = new BlogComments($_POST['id']);
                $u->comment = trim($_POST['comment']);
                $u->update();
                if ($u->ok())
                {
                    return true;
                }
            }
            return false;
        }


        // Delete an image from the blog
        public static function comments_delete()
        {
            if ((isset($_GET['action']))
            && ($_GET['action'] == 'delete_comment')
            && (is_numeric($_GET['id'])))
            {
                $u = new BlogComments($_GET['id']);
                $u->delete();
                if ($u->ok())
                {
                    return true;
                }
            }
            return false;
        }

        // Delete all images for a particular blog
        public static function comments_purge($blog_id)
        {
            $db = Database::getDatabase();
            $comments = $db->query("DELETE FROM blog_comments WHERE blog_id = " . $db->quote($blog_id));
            if(count($comments) > 0)
            {
                return true;
            }
            return false;
        }
    }

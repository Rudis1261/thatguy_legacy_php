<?PHP

    # Author: Rudi Strydom
    # Date: Dec 2013
    # Purpose: I am refactoring the blog somewhat. Making the code neater and more legible
    class Blog extends DBObject
    {
        # We need some things to get the party started
        public $page;
        public $perPage;
        public $blogs;

        private $db;
        private $Auth;

        public function __construct($id = null)
        {
            parent::__construct('blog', array('user_id', 'desc', 'body', 'timestamp'), $id);

            # Connect to the db
            $this->db           = Database::getDatabase();
            $this->Auth         = Auth::getAuth();
            $this->page         = (isset($_REQUEST['page'])) ? (int)$_REQUEST['page'] : 1;
            $this->perPage      = 15;

            # Attempt to get all the blogs from the DB
            $this->db->query("SELECT
                                b.id,
                                u.username,
                                u.email,
                                `user_id`,
                                `desc`,
                                `body`,
                                `timestamp`
                             FROM
                                blog b,
                                users u
                            WHERE
                                u.id=b.user_id
                            ORDER By id DESC");

            # Get the rows
            $getBlogs = $this->db->getRows();

            # Ensure that we have data to work with
            if (!empty($getBlogs))
            {
                # Loop through the blogs
                foreach($getBlogs as $blog)
                {
                    # And index them according to the blog index
                    $this->blogs[$blog['id']] = $blog;
                }
            }
        }


        # This is what the user will be shown when they see the site without any search parameters
        public function defaultView($article=false, $search='')
        {
            # Start the output
            $out = "";

            # Append the pager
            $out .= $this->pager($search);

            # Check whether we are referring to a specific blog
            if ($article !== false)
            {
                # Get the page and ensure that we are on the right page
                $getPage = $this->getPage($article);

                # Should the pages not match
                if (($getPage !== false) AND ($getPage !== $this->page))
                {
                    # Redirect to the correct page
                    redirect("blog.php?article=" . $article . "&page=" . $getPage . "#" . $article);
                }
            }

            $out .= $this->display();

            # Append the pager
            $out .= $this->pager($search);
            return $out;
        }


        public function display()
        {
            $out    = "";
            $ADMIN  = ($this->Auth->loggedIn()) AND ($this->Auth->isAdmin());

            # Admin links
            if ($ADMIN)
            {
                $out .= '<div>
                            <a href="blog_edit.php" class="btn btn-default">
                                Create New Blog ' . icon("plus") . '
                            </a>
                        </div>
                        <hr />';
            }

            # Get the page of blogs
            $chunks = (!empty($this->blogs))    ? array_chunk($this->blogs, $this->perPage, true)   : array();
            $blogs  = (!empty($chunks))         ? $chunks[$this->page-1]                            : array();
            $c      = 0;

            # Loop through the blogs and start creating the output
            foreach($blogs as $id=>$blog)
            {
                # Let's get the rest of the details for the blog
                $comments       = Blog::comments_get($id);
                $count_comments = (count($comments) > 0) ? count($comments) : 'No ';
                $username       = $blog['username'];
                $style          = "border-bottom: 1px solid #ccc; padding-bottom: 10px; padding-top: 10px;";
                $ACCESS         = (($ADMIN) OR ($this->Auth->id == $blog['user_id']));

                # Create he odd / even row styling
                if ($c %2 == 1)
                {
                    $style .= "background: #f7fafb;";
                }

                # We need to link back to google for posts
                $googlePlusAccount  = Options::userGet($blog['user_id'], 'googlePlusAccount');
                $firstName          = Options::userGet($blog['user_id'], 'firstName');
                $surname            = Options::userGet($blog['user_id'], 'surname');
                $blogUrl            = full_url_to_script('blog.php') . "?article=" . rawurlencode($blog['desc']) . "#" . rawurlencode($blog['desc']);

                # Should I have a first name and a surname, rather display this for google
                if ((!empty($firstName)) AND (!empty($surname)))
                {
                    $username = $firstName . " " . $surname;
                }

                # This it purely to get a better ranking
                if (!empty($googlePlusAccount))
                {
                    $Author = "<span>Author: <b><a style='color: #353535;' href='" . $googlePlusAccount . "?rel=author' target='_BLANK'>" . $username . "</a></b></span>";
                }

                # Otherwise just use the user's details
                else
                {
                    $Author = "<span>Author: <b>" . $username . "</b></span>";
                }

                # Start the blog output
                $out .= ' <div class="row" id="' . $blog['desc'] . '" style="margin-left: -15px; margin-right: -15px;' . $style . '">

                            <div class="col-md-2" style="min-width: 180px;">

                                <div align="left" style="padding-top: 20px;">

                                    <div class="pull-left">

                                        <div class="blogBookmark">
                                            ' . icon('bookmark') . '
                                        </div>

                                        <div align="center" class="blogDate">
                                            <b>' . dater($blog['timestamp'], "d") . '</b>
                                        </div>

                                    </div>

                                    <div align="left" class="blogMonth">
                                        &nbsp;' . dater($blog['timestamp'], "M") . '
                                    </div>

                                    <div align="left" class="blogTime">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <b>' . dater($blog['timestamp'], "G:i") . '</b>
                                    </div>

                                    <div class="clearfix"></div>

                                    <div align="left" class="blogAuthor">
                                        ' . $Author . '
                                    </div>

                                </div>

                            </div>

                            <div class="col-sm-7">';

                # Does the user have access to modify the blog?
                if ($ACCESS)
                {
                    # Add the buttons
                    $out .='<div class="btn-group pull-right">

                                <a class="btn btn-default" href="blog_edit.php?blog=' . $id . '">
                                    <i class="glyphicon glyphicon-pencil"></i>
                                </a>

                                <a class="btn btn-danger confirmDelete" href="blog.php?action=remove&blog=' . $id . '">
                                    <i class="glyphicon glyphicon-trash"></i>
                                </a>

                            </div>';
                }

                # Blog heading
                $out .= '<h3>
                            <b>
                                <a style="color: #353535;" href="' . $blogUrl . '">
                                    ' . $blog['desc'] . '
                                </a>
                            </b>

                            <br />

                            <small>
                                <span>' . time2str($blog['timestamp']) . '</span>
                            </small>
                        </h3>';

                # Process the blog body
                $string = $blog['body'];
                $string = htmlentities($string);
                $string = nl2br($string);
                $string = BBCode::imagic($string);
                $string = BBCode::codify($string);
                $string = BBCode::mailify($string);
                $string = BBCode::decode($string);
                $string = BBCode::linkify($string);

                # Add the actual body
                $out .= '<p>' . $string . '</p>
                    </div>
                    <div class="col-sm-3">
                        <strong>' . $count_comments . ' Comments</strong>
                        <br />
                        <div>';

                if (!empty($comments))
                {
                    $i = 0;
                    foreach($comments as $cid=>$comment)
                    {
                        $adminClass = "";
                        $deleteLink = '';
                        $style      = "padding: 10px; background: #edf1f6; border: 1px solid #dadfe5;";

                        if ($ACCESS)
                        {
                            $adminClass = "class='editDiv'";
                            $deleteLink = " <a class='btn btn-default btn-xs btn-danger pull-right'  href='blog.php?action=deleteComment&id=" . $cid . "'>Delete</a>";
                        }

                        $poster = ($this->Auth->id == $comment['user_id']) ? "You" : $comment['username'];
                        $string = htmlentities($comment['comment']);
                        $string = nl2br($string);
                        $string = BBCode::imagic($string);
                        $string = BBCode::codify($string);
                        $string = BBCode::mailify($string);
                        $string = BBCode::decode($string);
                        $string = BBCode::linkify($string);

                        if ($i %2 == 0)
                        {
                            $style = "background: #f7fafb; padding: 5px;";
                        }

                        $out .=  '<div style="' . $style . '" class="editDiv">
                                    ' .  $string . '
                                    <br />
                                    <i style="font-size: 10px;"> (Posted by <b>' . $poster . '</b>, ' . time2str($comment['timestamp']) . ')</i>
                                    ' . $deleteLink . '
                                </div>';


                        if ($ACCESS) // Check if it's the poster, or an admin and allow modification of the comment
                        {
                            $out .= '<form method="post" class="form editForm hideMe">
                                        <textarea name="comment" rows="4" class="form-control input-sm">' . $comment["comment"] . '</textarea>
                                        <input type="hidden" name="id" value="' .  $cid . '" />
                                        <br />
                                        <input type="hidden" name="action" value="editComment" />
                                        <input type="submit" class="btn btn-default" value="Edit" />
                                        <br />
                                    </form>';
                        }
                        $i++;
                    }
                }
                $out .= "</div>";

                # Add the comment form
                if ($this->Auth->loggedIn())
                {
                    $out .= '<form method="post" class="form">
                                <div class="form-group">
                                    <label class="control-label" for="input01">Something to say?</label>
                                    <textarea rows="2" class="form-control" id="input01" name="comment" placeholder="Say something"></textarea>
                                </div>
                                <input type="hidden" name="action" value="addComment" />
                                <input type="submit" class="form-control btn btn-default btn-primary" value="Comment" />
                                <input type="hidden" name="id" value="' . $id . '" />
                            </form>';
                }

                $out .= "</div>";
                $out .= "</div>";

                # increment the output
                $c++;
            }
            return $out;
        }


        # I would like a generic pager, thank you
        public function pager($searchAppend="", $padding=3)
        {
            $itemCount  = count($this->blogs);
            $numPages   = ceil($itemCount / $this->perPage);

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
                    $paging .= '<li><a href="blog.php?page=' . $i . $searchAppend .'">&laquo;</a></li>'. nl();
                }

                if ($i == $this->page)
                {
                    $paging .= '<li class="active"><a href="#">' . $i . '</a></li>'. nl();
                }

                elseif (($i >= $max) xor ($i > $min))
                {
                    $paging .= '<li><a href="blog.php?page=' . $i . $searchAppend . '">' . $i . '</a></li>'. nl();
                }

                if ($i == $numPages)
                {
                    $paging .= '<li><a href="blog.php?page=' . $numPages . $searchAppend . '">&raquo;</a></li>'. nl();
                }
            }
            $paging .='</ul></div>'. nl();
            return $paging;
        }


        public function getPage($id_or_desc)
        {
            # Count the items, chunk it
            $itemCount  = count($this->blogs);
            $chunks     = array_chunk($this->blogs, $this->perPage, true);
            $numPages   = ceil($itemCount / $this->perPage);

            # Loop through the number of pages
            for($i=1; $i<=$numPages; $i++)
            {
                # We need to get the per page chunk
                $pageContent = $chunks[$i-1];

                # Search by ID
                if (is_numeric($id_or_desc))
                {
                    # Check if we find the index in the array
                    if (in_array($id_or_desc, array_keys($pageContent)))
                    {
                        return $i;
                    }
                }

                # Search by string
                else
                {
                    # Loop through the content and check if we can find the description
                    foreach($pageContent as $content)
                    {
                        # Check for the description
                        if (strtolower($id_or_desc) == strtolower($content["desc"]))
                        {
                            return $i;
                        }
                    }
                }
            }

            # Default to a false
            return false;
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
        public  function check_remove($id, $admin=false)
        {
            # Fire up the blog with the ID
            $b = new Blog($id);

            # Ensure that it's either the poster or and admin and then delete
            if (($b->user_id == $this->Auth->id) OR ($this->Auth->isAdmin()))
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
        public function addComment($id, $comment)
        {
            # Check whether the user has comments for the blog yet?
            $exists = $this->db->getValues('SELECT comment FROM blog_comments WHERE user_id="' . $this->Auth->id . '" and blog_id="' . $id . '"');

            if (count($exists) > 0)
            {
                foreach($exists as $result)
                {
                    if ($result == $comment)
                    {
                        return false;
                        break;
                    }
                }
            }

            # otherwise create a new one
            $u = new BlogComments();
            $u->user_id = $this->Auth->id;
            $u->blog_id = $id;
            $u->comment = $comment;
            $u->timestamp = time();
            $u->username = ucfirst($this->Auth->username);

            $u->Insert();
            if ($u->ok())
            {
                return true;
            }
            return false;
        }


        // Insert image into a blog
        public static function editComment($id, $comment)
        {
            $u = new BlogComments($id);
            $u->comment = trim($comment);
            $u->update();
            if ($u->ok())
            {
                return true;
            }
            return false;
        }


        // Delete an image from the blog
        public static function deleteComment($id)
        {
            $u = new BlogComments($id);
            $u->delete();
            if ($u->ok())
            {
                return true;
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

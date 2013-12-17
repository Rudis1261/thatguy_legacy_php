<?PHP
    class Auth
    {
        const SALT = 'Get down to it, you got to get down to it.';

        private static $me;

        public $email;
        public $id;
        public $username;
        public $user;
        public $expiryDate;
        public $login;
        public $active;
        public $loginUrl = './login.php'; // Where to direct users to login

        private $nid;
        private $loggedIn;

        public function __construct()
        {
            $this->email        = null;
            $this->login        = null;
            $this->active       = null;
            $this->id           = null;
            $this->nid          = null;
            $this->username     = null;
            $this->user         = null;
            $this->loggedIn     = false;
            $this->expiryDate   = mktime(0, 0, 0, 6, 2, 2037);
            $this->user         = new User();
        }

        public static function getAuth()
        {
            if(is_null(self::$me))
            {
                self::$me = new Auth();
                self::$me->init();
            }
            return self::$me;
        }

        public function init()
        {
            $this->setACookie();
            $this->loggedIn = $this->attemptCookieLogin();
        }

        public function login($username, $password)
        {
            $this->loggedIn = false;

            $db = Database::getDatabase();
            $hashed_password = self::hashedPassword($password);
            $row = $db->getRow("SELECT `id`, `nid`, `username`, `login`, `active`, `password`, `level`, `email` FROM users WHERE username = " . $db->quote($username) . " AND password = " . $db->quote($hashed_password));

            if($row === false)
                return false;

            # User logged in successfully, so let's update their active and login status
            $db->query("UPDATE users SET `login`=" . time() . " WHERE id=" . $row['id']);

            $this->email    = $row['email'];
            $this->id       = $row['id'];
            $this->nid      = $row['nid'];
            $this->username = $row['username'];
            $this->active   = $row['active'];
            $this->login    = $row['login'];
            $this->user     = new User();
            $this->user->id = $this->id;
            $this->user->load($row);

            // Check if the account has been activated yet
            $active = $db->getRow("SELECT `id`, `user`, `code`, `active` FROM activation WHERE active=1 and  user = " . $db->quote($this->id));

            if ($active === false)
                return 'inactive';

            $this->generateBCCookies();
            $this->loggedIn = true;
            return true;
        }

        public function logout($redirect=false)
        {
            $this->loggedIn = false;
            $this->clearCookies();
            $this->sendToLoginPage();
        }

        public function loggedIn()
        {
            return $this->loggedIn;
        }

        public function requireUser()
        {
            if(!$this->loggedIn())
                $this->sendToLoginPage();
        }

        public function requireAdmin()
        {
            if(!$this->loggedIn() || !$this->isAdmin())
                $this->sendToLoginPage();
        }

        public static function userId($id_or_username)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            // Check if they originally provided a username
            if($u->ok())
            {
                return $u->id;
            } else if (is_numeric($id_or_username)) {
                return $id_or_username;
            }
        }

        // Check if you can get the username of the account and return it
        public static function username($id)
        {
            $u = new User($id);
            if($u->ok())
            {
                return $u->username;
            } else {
                return false;
            }
        }

        // Get the user's email address quickly
        public static function email($id)
        {
            $u = new User($id);
            if($u->ok())
            {
                return $u->email;
            } else {
                return false;
            }
        }

        public function isAdmin()
        {
            return ($this->user->level === 'admin');
        }

        public function changeCurrentUsername($new_username)
        {
            $db = Database::getDatabase();
            srand(time());
            $this->user->nid = Auth::newNid();
            $this->nid = $this->user->nid;
            $this->user->username = $new_username;
            $this->username = $this->user->username;
            $this->user->update();
            $this->generateBCCookies();
        }

        public function changeCurrentPassword($new_password)
        {
            $db = Database::getDatabase();
            srand(time());
            $this->user->nid = self::newNid();
            $this->user->password = self::hashedPassword($new_password);
            $this->user->update();
            $this->nid = $this->user->nid;
            $this->generateBCCookies();
        }

        // Change the current user email address
        public function changeCurrentEmail($new_email)
        {
            $db = Database::getDatabase();
            if (!Auth::emailExists($new_email))
            {
                $this->user->email = $new_email;
                $this->user->update();
            } else {
                return false;
            }
        }

        // Check if the use can reset their password and if so, return the userid
        public static function resetPasswordCheck($username_or_email)
        {
            if(Auth::emailExists($username_or_email))
            {
                $u = new User();
                $u->select($username_or_email, 'email');
            } else {
                $u = new User();
                $u->select($username_or_email, 'username');
            }

            if($u->ok())
            {
                return $u->id;
            } else {
                return false;
            }
        }

        public static function changeGroup($id_or_username, $type)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if($u->ok())
            {
                $u->nid = self::newNid();
                $u->level = $type;
                $u->update();
            }
        }

        public static function changeUsername($id_or_username, $new_username)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if($u->ok())
            {
                $u->username = $new_username;
                $u->update();
            }
        }

        public static function changePassword($id_or_username, $new_password)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if($u->ok())
            {
                $u->nid = Auth::newNid();
                $u->password = Auth::hashedPassword($new_password);
                $u->update();
                Auth::clearCookies();
            }
        }

        // Change the email on the account, that is if the email does not exist
        public static function changeEmail($email_or_id, $new_email)
        {
            if(ctype_digit($email_or_id))
                $u = new User($email_or_id);
            else
            {
                $u = new User();
                $u->select($email_or_id, 'email');
            }

            if ((!Auth::emailExists($new_email))
            && ($u->ok()))
            {
                $u->email = $new_email;
                $u->update();
            } else {
                return false;
            }
        }

        // Check if the user exists.
        public static function userExists($username)
        {
	    $db = Database::getDatabase();
            $user_exists = $db->getValue("SELECT COUNT(id) FROM users WHERE username = " . $db->quote($username));
            if($user_exists > 0)
            {
                return true;
            } else {
                return false;
            }
        }

        // Check if the email address exists
        public static function emailExists($email)
        {
	    $db = Database::getDatabase();
            $email_exists = $db->getValue("SELECT COUNT(id) FROM users WHERE email = " . $db->quote($email));
            if($email_exists > 0)
            {
                return true;
            } else {
                return false;
            }
        }

        // Get an array of all the email addresses in a specific group
        public static function groupMail($group)
        {
	    $db = Database::getDatabase();
            $group_mail = $db->getValues("SELECT email FROM users WHERE level = " . $db->quote($group));
            if(count($group_mail) > 0)
            {
                return $group_mail;
            } else {
                return false;
            }
        }

        // Create a new user
        public static function createNewUser($username, $password = null, $email)
        {
	    $db = Database::getDatabase();

            if(Auth::userExists($username))
                return false;

            if(is_null($password))
                $password = Auth::generateStrongPassword();

            srand(time());
            $u = new User();
            $u->username = $username;
            $u->nid = self::newNid();
            $u->password = self::hashedPassword($password);
            $u->email = $email;
            $u->insert();

            // Create the activation code
            Activation::generate($u->id, 20);
            return $u;
        }

        // Generate a strong password
        public static function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
        {
            $sets = array();
            if(strpos($available_sets, 'l') !== false)
                $sets[] = 'abcdefghjkmnpqrstuvwxyz';
            if(strpos($available_sets, 'u') !== false)
                $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
            if(strpos($available_sets, 'd') !== false)
                $sets[] = '23456789';
            if(strpos($available_sets, 's') !== false)
                $sets[] = '!@#$%*';

            $all = '';
            $password = '';
            foreach($sets as $set)
            {
                $password .= $set[array_rand(str_split($set))];
                $all .= $set;
            }

            $all = str_split($all);
            for($i = 0; $i < $length - count($sets); $i++)
                $password .= $all[array_rand($all)];

            $password = str_shuffle($password);

            if(!$add_dashes)
                return $password;

            $dash_len = floor(sqrt($length));
            $dash_str = '';
            while(strlen($password) > $dash_len)
            {
                $dash_str .= substr($password, 0, $dash_len) . '-';
                $password = substr($password, $dash_len);
            }
            $dash_str .= $password;
            return $dash_str;
        }

        public function impersonateUser($id_or_username)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if(!$u->ok()) return false;

            $this->id       = $u->id;
            $this->nid      = $u->nid;
            $this->username = $u->username;
            $this->user     = $u;
            $this->email    = $u->email;
            $this->generateBCCookies();

            return true;
        }

        private function attemptCookieLogin()
        {
            if(!isset($_COOKIE['A']) || !isset($_COOKIE['B']) || !isset($_COOKIE['C']))
                return false;

            $ccookie = base64_decode(str_rot13($_COOKIE['C']));
            if($ccookie === false)
                return false;

            $c = array();
            parse_str($ccookie, $c);
            if(!isset($c['n']) || !isset($c['l']))
                return false;

            $bcookie = base64_decode(str_rot13($_COOKIE['B']));
            if($bcookie === false)
                return false;

            $b = array();
            parse_str($bcookie, $b);
            if(!isset($b['s']) || !isset($b['x']))
                return false;

            if($b['x'] < time())
                return false;

            $computed_sig = md5(str_rot13(base64_encode($ccookie)) . $b['x'] . self::SALT);
            if($computed_sig != $b['s'])
                return false;

            $nid = base64_decode($c['n']);
            if($nid === false)
                return false;

            $db = Database::getDatabase();

            // We SELECT * so we can load the full user record into the user DBObject later
            $row = $db->getRow('SELECT  `id`, `active`, `login`, `nid`, `username`, `password`, `level`, `email` FROM users WHERE nid = ' . $db->quote($nid));
            if($row === false)
                return false;

            $this->email    = $row['email'];
            $this->id       = $row['id'];
            $this->nid      = $row['nid'];
            $this->username = $row['username'];
            $this->login    = $row['login'];
            $this->active   = $row['active'];
            $this->user     = new User();
            $this->user->id = $this->id;
            $this->user->load($row);

            return true;
        }

        private function setACookie()
        {
            if(!isset($_COOKIE['A']))
            {
                srand(time());
                $a = md5(rand() . microtime());
                setcookie('A', $a, $this->expiryDate, '/');
            }
        }

        private function generateBCCookies()
        {
            $c  = '';
            $c .= 'n=' . base64_encode($this->nid) . '&';
            $c .= 'l=' . str_rot13($this->username) . '&';
            $c = base64_encode($c);
            $c = str_rot13($c);

            $sig = md5($c . $this->expiryDate . self::SALT);
            $b = "x={$this->expiryDate}&s=$sig";
            $b = base64_encode($b);
            $b = str_rot13($b);

            setcookie('B', $b, $this->expiryDate, '/');
            setcookie('C', $c, $this->expiryDate, '/');
        }

        private static function clearCookies()
        {
            setcookie('B', '', time() - 3600, '/');
            setcookie('C', '', time() - 3600, '/');
        }

        private function sendToLoginPage()
        {
            redirect(WEB_ROOT . '/index.php');
        }

        private static function hashedPassword($password)
        {
            return md5($password . self::SALT);
        }

        private static function newNid()
        {
            srand(time());
            return md5(rand() . microtime());
        }
    }

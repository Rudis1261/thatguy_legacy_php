<?PHP
    // The difference between this and settings is that
    // Options is for site variables,
    // Settings is for client settings.
    class Options
    {
        public static $me;

        public $id;
        public $option;
        public $value;
        public $options;

        public function __construct()
        {
            $this->id        = null;
            $this->option    = null;
            $this->value     = null;
            $this->options   = array();

            // Check for the options
            $this->options['options']        = array();
            $this->options['options_groups'] = array();
            $this->options['options_types']  = array();
            $this->options['user_options']   = array();

            // We need some classes before getting started
            if (class_exists("Auth"))
            {
                $Auth = Auth::getAuth();
                $Options = Options::Load($Auth->id);
            } else {
                $Options = Options::Load();
            }

            // Set the session variables pased on the settings retrieved from the server
            $this->options['options']               = $Options['options'];

            // Load the fking user settings
            $this->options['options_groups']    = @$Options['options_groups'];
            $this->options['options_types']     = @$Options['options_types'];
            $this->options['user_options']      = @$Options['user_options'];
        }

        // Initiate the options page
        public static function init()
        {
            if(is_null(self::$me))
                self::$me = new Options();
            return self::$me;
        }

        public static function groupAdd($group, $desc='')
        {
            $db = Database::getDatabase();
            $db->query('REPLACE INTO
                       options_groups
                       (`group`, `desc`)
                       VALUES
                       (:group:, :desc:)',
                       array('group' => $group, 'desc' => $desc));
        }

        public static function groupRemove($group)
        {
            $db = Database::getDatabase();
            $db->query('DELETE FROM options_groups WHERE `group`=:group:', array('group' => $group));
        }

        // Get the groups from the session
        public static function groups()
        {
            if (self::$me->options['options_groups']) return self::$me->options['options_groups'];
            return false;
        }

        public static function types()
        {
            if (self::$me->options['options_types']) return self::$me->options['options_types'];
            return false;
        }

        // This function gets a list of the information, can be refined to a type or a group
        public static function getList($type=false, $group=false, $sql='')
        {
            // Get a list of options
            $array = array();
            if ($type)
            {
                return Options::filter('type', $type);

            } elseif ($group) {

                return Options::filter('group', $group);

            } else {

                return Options::all();
            }
        }

        // This function is used to filter the info in a specific way
        public static function filter($key, $value)
        {
            $array = array();
            // Loop through the options
            foreach(self::$me->options['options'] as $opt)
            {
                if ($opt[$key] == $value) $array[$opt['key']] = $opt;
            }
            return $array;
        }

        // Load the variables from the DB ONCE
        public static function Load($user_id=NULL)
        {
            $array = array();

            // Get the options from the db
            $db = Database::getDatabase();
            $db->query('SELECT `key`, `value`, `type`, `group` FROM options');
            $array['options'] = $db->getRows();

            // Get the groups
            $db->query('SELECT `group`, `desc` FROM options_groups');
            $array['options_groups'] = $db->getRows();

            // Get the types
            $db->query("SHOW COLUMNS FROM options LIKE 'type'");
            $row = $db->getRow();
            $type = $row['Type'];
            preg_match('/enum\((.*)\)$/', $type, $matches);
            $vals = explode(',', $matches[1]);
            if (is_array($vals))
            {
                $array['options_types'] = str_replace("'", '', $vals);
            }

            # Only populate the user iformation as needed
            
            // Get the User Options
            $db->query('SELECT
                       user_id, options.key, options_users.value, options.type, options.group
                       FROM
                       options, options_users
                       WHERE
                       `group`="User Settings" AND
                       options.key=options_users.key
                       ORDER By user_id ASC');

            // Loop through the user options and add them to the SESSION
            $userOptions = $db->getRows();
            foreach($userOptions as $id=>$vals)
            {
                $array['user_options'][$vals['user_id']][] = $vals;
            }   
            return $array;
        }

        // This is a special function, when you once to add an options but once and not if it changes.
        // This will be used in the Options.inc.php and Install.inc.php
        public static function addOnce($option, $value='', $type=false, $group=false)
        {
            if (!Options::exists($option)){
                Options::add($option, $value, $type, $group);
            }
        }

        // Will generate an array with the available
        public static function all($type='options')
        {
            // Switch between the types and return the list of variables in array form
            switch($type)
            {
                case "options":
                    return self::$me->options[$type];
                    break;

                case "user_options":
                    return self::$me->options[$type];
                    break;

                default:
                    return self::$me->options['options'];
                    break;
            }
        }

        // Get the value with either get or value
        public static function value($option)
        {
            // Get the options from the array
            foreach(self::$me->options['options'] as $id=>$opt)
            {
                // Check if the session variable is set and then return it
                if($opt['key'] == $option)
                {
                    return $opt['value'];
                }
            }
            return false;
        }

        // Does the option exist?
        public static function exists($option)
        {
            // Get the options from the array
            foreach(self::$me->options['options'] as $id=>$opt)
            {
                // Check if the session variable is set and then return it
                if($opt['key'] == $option)
                {
                    return true;
                }
            }
            return false;
        }

        // Add the option
        public static function add($option, $value='', $type=false, $group=false)
        {
            $db = Database::getDatabase();
            /*if ($value == '') $value = Options::value($option);*/
            if ($type == false) $type = Options::type($option);
            if ($group == false) $group = Options::group($option);
            if (Options::exists($option))
            {
                $db->query('UPDATE
                           options
                           SET
                           `value`=:value:, `type`=:type:, `group`=:group:
                           WHERE
                           `key`=:key:',
                           array('key' => $option, 'value' => $value, 'type' => $type, 'group' => $group));

                /*echo "Update option";*/
            } else {
                $db->query('INSERT INTO
                        options
                        (`key`, `value`, `type`, `group`)
                        VALUES
                        (:key:, :value:, :type:, :group:)',
                        array('key' => $option, 'value' => $value, 'type' => $type, 'group' => $group));
                /*echo "Inserted new option";*/
            }
            return $db->affectedRows();

        }


        // Set the group of a partucular option
        public static function group($option, $groupName=false)
        {
            if ($groupName == false)
            {
                $db = Database::getDatabase();
                $db->query('SELECT `group` FROM options WHERE `key`=:key:', array('key' => $option));
                $row = $db->getRow();
                return $row['group'];
            } else {
                $db = Database::getDatabase();
                $db->query('UPDATE
                           options
                           SET
                           `group`=:group:
                           WHERE
                           `key`=:key:',
                           array('key' => $option, 'group' => $groupName));
                return $db->affectedRows();
            }
        }

        // Set the type of a particular option
        public static function type($option, $type=false)
        {
            if ($type == false)
            {
                $db = Database::getDatabase();
                $db->query('SELECT `type` FROM options WHERE `key`=:key:', array('key' => $option));
                $row = $db->getRow();
                return $row['type'];
            } else {
                $db = Database::getDatabase();
                $db->query('UPDATE
                           options
                           SET
                           `type`=:type:
                           WHERE
                           `key`=:key:',
                           array('key' => $option, 'type' => $type));
                return $db->affectedRows();
            }
        }

        // Remove a options from the DB
        public static function remove($option)
        {
            $db = Database::getDatabase();
            $db->query('DELETE FROM options WHERE `key`=:key:', array('key' => $option));
        }

        // Get the value with either get or value
        public static function userValue($userId, $option)
        {
            // Check if the user option even exists, shall we?
            if (self::$me->userExists($userId, $option))
            {
                foreach(self::$me->options['user_options'][(int)$userId] as $opt)
                {                    
                    if (strtolower($opt['key']) == strtolower($option))
                    {
                    	return $opt['value'];
                    }
                }
            }
            return false;
        }

        // Check if the user option is already set or not
        public static function userExists($userId, $option)
        {
            // Check if the user even has settings??
            if (isset(self::$me->options['user_options'][(int)$userId]))
            {
                // Get the options from the array
                foreach(self::$me->options['user_options'][$userId] as $opt)
                {    
                    if (strtolower($opt['key']) == strtolower($option))
                    {
                    	 return true;
                    }
                }
            }
            return false;
        }

        // Add the option
        public static function userAdd($userId, $option, $value='')
        {
            $db = Database::getDatabase();
            if (Options::userExists($userId, $option))
            {
                $db->query('UPDATE
                           options_users
                           SET
                           `value`=:value:
                           WHERE
                           `key`=:key: and `user_id`=:user_id:',
                           array('key' => $option, 'value' => $value, 'user_id' => $userId));
                /*echo "Update option";*/
            } else {
                $db->query('INSERT INTO
                        options_users
                        (`user_id`, `key`, `value`)
                        VALUES
                        (:user_id:, :key:, :value:)',
                        array('user_id' => $userId, 'key' => $option, 'value' => $value));
                /*echo "Inserted new option";*/
            }
            return $db->affectedRows();

        }

        /* User specific options */
        // Alias Functions
        // These are merely another way to call the same function
        public static function available($group=false)                              { return Options::all($group); }
        public static function get($option)                                         { return Options::value($option); }
        public static function set($option, $value='', $type=false, $group=false)   { return Options::add($option, $value, $type, $group); }
        public static function userGet($userId, $option)                            { return Options::userValue($userId, $option); }
        public static function userSet($userId, $option, $value='')                 { return Options::userAdd($userId, $option, $value); }
        public static function groupSet($group, $desc='')                           { return Options::groupAdd($group, $desc); }
        public static function groupDelete($group)                                  { return Options::groupRemove($group); }

    }
?>
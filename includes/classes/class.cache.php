<?php

    // Cache specific content
    // This is a simple class to store a files information to confirm when it was last changed. The file itself is then refreshed with the use of a get varaible
    class Cache extends DBObject
    {
        public static $me;
        public $entries;
        public $db;

        public function __construct()
        {
            // We are addapting this so we are able to mimimize SQL Queries
            $this->db = Database::getDatabase();
            $this->db->query('SELECT `id`, `file`, `size`, `timestamp` FROM cache');
            $rows = $this->db->getRows();

            // Get all the cache entries with their id as the key and a dictionary of the values
            foreach($rows as $row)
            {
                $this->entries[$row['id']]['file'] = $row['file'];
                $this->entries[$row['id']]['size'] = $row['size'];
                $this->entries[$row['id']]['timestamp'] = $row['timestamp'];
            }
        }

        // Initiate the options page
        public static function init()
        {
            if(is_null(self::$me))
                self::$me = new Cache();
            return self::$me;
        }

        // Add the cache information
        public static function add($file, $size)
        {
            $exists = Cache::exists($file);
            if ($exists)
            {
                // UPDATE QUERY
                $sql = 'UPDATE
                        cache
                        SET
                        size=' . self::$me->db->quote($size) . ',
                        timestamp=' . self::$me->db->quote(time()) . '
                        WHERE file=' . self::$me->db->quote($file);
            } else {

                // INSERT Query
                $sql = 'INSERT INTO
                        cache
                        (`file`, `size`, `timestamp`)
                        VALUES
                        (' . self::$me->db->quote($file) . ',
                        ' . self::$me->db->quote($size) . ',
                        ' . self::$me->db->quote(time()) . ')';
            }

            // Add / Update the Cache entry
            self::$me->db->query($sql);
        }

        // Exists function
        public static function exists($file)
        {
            if ( (isset(self::$me->entries))
            && (count(self::$me->entries) > 0) )
            {
                foreach(self::$me->entries as $id=>$cache)
                {
                    if ($cache['file'] == $file)
                    {
                        return $id;
                    }
                }
            }
            return false;
        }

        // modified function, to get the last modified timestamp
        public static function modified($file)
        {
            $id = Cache::exists($file);
            if ( ($id) && (isset(self::$me->entries[$id])) )
            {
                return self::$me->entries[$id]['timestamp'];
            }
            return false;
        }

        // Has the size changed?
        public static function size($file)
        {
            $id = Cache::exists($file);
            if ( ($id) && (isset(self::$me->entries[$id])) )
            {
                return self::$me->entries[$id]['size'];
            }
            return false;
        }


        // Function to confirm if there is a difference
        public static function changed($file, $size)
        {
            $changes = 0;

            // has the size changed?
            if ((int)Cache::size($file) !== (int)$size) $changes = $changes + 1;

            // On change shout it out
            if ($changes > 0) return true;
            return false;
        }
    }

?>
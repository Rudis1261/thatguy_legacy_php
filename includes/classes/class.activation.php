<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).    
    class Activation extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('activation', array('user', 'code', 'active'), $id);
        }
      
        // This function will have a dual purpose, it will both check the insert an activation code for the usernumber, and also return the activation code inserted in order to send the info
        public static function generate($userId, $lenght=10)
        {
            if (!Activation::get($userId))            
            {
                $activationCode = substr(md5(rand() . microtime()), 0, $lenght);
                $activation = new Activation();  
                $activation->user = $userId;
                $activation->code = $activationCode;
                $activation->active = 0;
                $activation->insert();
                return $activation->code;
            } else {
                return Activation::get($userId); 
            }
        }
        
        // Get the activation code on their account
        public static function get($userId)
        {
            $get = new Activation();
            $get->select($userId, 'user');
            if ($get->code)
            {
                return $get->code;  
            } else {
                return false;
            }            
        }
        
        // Will check whether the activation has been done?
        public static function status($userId)
        {
            $db = Database::getDatabase();           
            $query='
            SELECT user, code, active 
            FROM
            activation 
            WHERE user='.$db->quote($userId);
            
            $active = $db->getRow($query);
            if($active == false)
            {
                return false;
            } else {
                
                // So there are contents, lets check if the account is active yet?
                if ($active['active'] == 1)
                {
                    return true;   
                } else{
                    return false;
                }
            } 
        }
        
        // This function will activate the account. 
        public static function activate($userId)
        {
            $a = new Activation($userId);
            $a->select($userId, 'user');
            $a->active= 1;
            $a->update();            	
        }
        
        // This function will de-activate the account. 
        public static function deactivate($userId)
        {
            $a = new Activation($userId);
            $a->select($userId, 'user');
            $a->active= 0;
            $a->update();            	
        }
        
        // This function will de-activate the account. 
        public static function remove($userId)
        {
            $a = new Activation($userId);
            $a->select($userId, 'user');
            $a->delete();
        } 
    }
    
?>
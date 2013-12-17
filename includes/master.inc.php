<?PHP
    // Application flag
    define('SPF', true);

    // https://twitter.com/#!/marcoarment/status/59089853433921537
    date_default_timezone_set('Africa/Johannesburg');

    // Determine our absolute document root
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

    // Global include files
    require DOC_ROOT . '/includes/functions.inc.php';  // spl_autoload_register() is contained in this file
    require DOC_ROOT . '/includes/classes/class.dbobject.php'; // DBOBject...
    require DOC_ROOT . '/includes/classes/class.objects.php';  // and its subclasses

    // Lets start the function to get the execution time
    $time_start = microtime_float();

    // Fix magic quotes
    if(get_magic_quotes_gpc())
    {
        $_POST    = fix_slashes($_POST);
        $_GET     = fix_slashes($_GET);
        $_REQUEST = fix_slashes($_REQUEST);
        $_COOKIE  = fix_slashes($_COOKIE);
    }

    // Load our config settings
    $Config = Config::getConfig();

    // Initialize our session
    @session_name('spfs');
    @session_start();

    // Some special classes
    Options::init();
    Cache::init();

    // This dynamically creates the options variables except for the User Settings
    //foreach(Options::getList(false, false, "WHERE `group`!='User Settings'") as $option=>$values)
    foreach(Options::all() as $option)
    {
        ${$option['key']} = $option['value'];
    }

    // Run the options and install functions, this would be if there are no information present in the db
    require DOC_ROOT . '/includes/install.inc.php';  // Check for installation and create the tables needed
    require DOC_ROOT . '/includes/options.inc.php';  // Get the options from the DB
    require DOC_ROOT . '/includes/javascript.inc.php';  // Dynamic JS
    require DOC_ROOT . '/includes/css.inc.php';  // Dynamic CSS

    // Initialize current user
    $Auth = Auth::getAuth();

    if ($Auth->loggedIn())
    {
        # User logged in successfully, so let's update their active and login status
        $db = Database::getDatabase();
        $db->query("UPDATE users SET `active`=" . time() . " WHERE id=" . $Auth->id);
    }

    // Object for tracking and displaying error messages
    $Error = new Error();

    // Adding commonly used varaibles to catch possible errors
    // These varaibles would be overridden in page specific settings
    $errorClass = array('','','','','','','','','',''); // Used for per page error control
    $inputValue = array('','','','','','','','','',''); // Used for per page input values
    $title      = 'Welcome to ' . $siteName; // used when no title is specified.
    $paging     = ''; // Used when no paging is handed in
    $pager      = ''; // Alias of paging
    $js         = ''; // Setting default in case no JS is provided in the file
    $complete   = ''; // Used to confirm whether all the checks have been done, used in the template files
    $fb         = ''; // Default FB value should this be passed into hte template
?>
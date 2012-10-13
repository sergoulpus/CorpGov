<?

/* Directories and paths */
$CFG["BASE_URL"] = "http://" . $_SERVER["HTTP_HOST"] . substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], "/"));
$CFG["BASE_URI"] = substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], "/"));
$CFG["BASE_DIR"] = getcwd();


/* Include functions and stuff */
include_once ($CFG["BASE_DIR"] . "/protected/lib/CorpGov_lib.php");
include_once ($CFG["BASE_DIR"] . "/protected/lib/formElements.php");



/* Sqlite3 database filename */
$CFG["DB_FILE"] = $CFG["BASE_DIR"] . "/protected/data/corpgov.s3db";

/* Connect to database */
$db = DBconnect($CFG["DB_FILE"]);

/* Turn on Foreign Key support */
$db->exec('PRAGMA foreign_keys = ON;');
//var_dump($db->query('PRAGMA foreign_keys;')->fetch(PDO::FETCH_ASSOC));

?>
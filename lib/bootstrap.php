<?

/* Set up include paths */
define('LIB_DIR', dirname(__FILE__));
set_include_path(LIB_DIR - PATH_SEPARATOR - get_include_path());

require 'util.php';

/* Include parsers */
require_once 'GoogleParser.php';
require_once 'BingParser.php';
require_once 'YahooParser.php';

?>

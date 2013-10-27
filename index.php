<? 

require_once 'lib/bootstrap.php';

function failed_search() {
	output_json(array());
	exit;
}

function handle_search() { 
	/* Abort if not engine and term is set */
	if (!isset($_GET['engine']) || !isset($_GET['term'])) {
		failed_search();
	}

	$search_engine_parser = SearchEngineParser::getParser($_GET['engine']);

	if ($search_engine_parser === null) {
		failed_search();
	}

	$res = $search_engine_parser->getResults($_GET['term']);
	output_json($res);
}

if (safeGet($_GET, "action") === 'search') {
	handle_search();
} else {
	/* Display main page */
	header('Content-Type: text/html');
	readfile('include/main.html');
}

?>

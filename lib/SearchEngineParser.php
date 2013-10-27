<?

abstract class SearchEngineParser { 
	/* Keep track off an instance of all subclasses of SearchEngineParser */
	private static $all = array();

	/*
 	 * Return the appriopriate parser for the search engine $engine.
 	 */
	static function getParser($engine) {
		$tmp = strtolower($engine);
		return safeGet(self::$all, $tmp);
	}

	static function addParser($name, $val) {
		self::$all[$name] = $val;
	}

	/* Unescape the results and return normal urls
	 * Utility function used in sub classes */
	protected function unescapeResult($match) {
		/* remove additional url escape done on urls */
		$match = array_map("urldecode", $match);
		$match = array_map("html_entity_decode", $match);

		return $match;
	}

	/* 
 	 * Return an array of the search results 
 	 *
 	 * This method is overriden in the child classes 
 	 */
	function getResults($search_term) {
		return array();
	}

	abstract function getUrl($term);
	abstract function setupHeader($curl_handler);

	protected function loadWebpage($term) {
		$handle = curl_init($this->getUrl($term));

		/* Don't include header in output */	
		curl_setopt($handle, CURLOPT_HEADER, FALSE);
		/* Return result form curl_exec instead of outputing it */
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

		$this->setupHeader($handle);

		return curl_exec($handle);
	}

	/* 
     * Save the search result in the database. 
	 * Expects $match to be an array of hits. 
     */
	protected function saveResult($engine, $term, $match) {
		/* TODO: Don't hard code this here */
		$mysqli = new mysqli('localhost', 'search_app', 'derp', 'search');
		if ($mysqli->connect_errno) {
			error_log('Error connecting to mysql: ' . $mysqli->connect_error); 
			return;
		}

		$stmt = $mysqli->prepare('INSERT INTO results(engine, term, result) VALUES(?, ?, ?)');
		if (!$stmt) {
			error_log('Error preparing statement: ' . $mysqli->error);
			return;
		}

		if (!$stmt->bind_param('sss', $engine, $term, json_encode($match))) {
			error_log('Error binding parameters: ' . $mysqli->error);
			return;
		}

		if ($stmt->execute() === FALSE) {
			error_log('Execution of statement failed: ' . $mysqli->error);
			return;
		}
	}
};

?>

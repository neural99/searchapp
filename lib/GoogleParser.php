<?

require_once 'SearchEngineParser.php';

class GoogleParser extends SearchEngineParser {

	function setupHeader($curl_handler) {
		curl_setopt($curl_handler, CURLOPT_REFERER, "http://www.google.se");
		curl_setopt($curl_handler, CURLOPT_USERAGENT, "Lynx/2.8.8dev.9 libwww-FM/2.14 SSL-MM/1.4.1 GNUTLS/2.12.14");
	}

	function getUrl($term) {
		return "www.google.se/search?ie=ISO-8859-1&hl=sv&source=hp&q=" .
				urlencode($term) . "&btnG=S%F6k+p%E5+Google&gbv=1";
	}

	function getResults($term) {
		$res = $this->loadWebpage($term);
		if ($res === FALSE) {
			return array();
		}

		preg_match_all("/url\?q=([^&]+)&amp;/",
					   $res,
					   $out,
                       PREG_PATTERN_ORDER);
		
		$match = safeGet($out, 1);		

		if ($match !== null) {
			/* Remove duplicates */
			$match = array_values(array_unique($match));
			$match = parent::unescapeResult($match);
			$match = array_slice($match, 0, 10);
			parent::saveResult('google', $term, $match);
			return $match;
		} else {
			return array();
		}
	}
};

SearchEngineParser::addParser('google', new GoogleParser);

?>

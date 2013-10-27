<?

require_once 'SearchEngineParser.php';

class BingParser extends SearchEngineParser {

	function setupHeader($curl_handler) {
		curl_setopt($curl_handler, CURLOPT_REFERER, "http://www.bing.com");
		curl_setopt($curl_handler, CURLOPT_USERAGENT, "Lynx/2.8.8dev.9 libwww-FM/2.14 SSL-MM/1.4.1 GNUTLS/2.12.14");
	}

	function getUrl($term) {
		return "www.bing.com/search?q=". urlencode($term) . "&go=Submit&qs=ds&form=QBLH&filt=all";
	}

	function getResults($term) {
		$res = $this->loadWebpage($term);
		if ($res === FALSE) {
			return array();
		}

		preg_match_all("/<h3><a href=\"(http[^\"]+)\"/",
					   $res,
					   $out,
                       PREG_PATTERN_ORDER);

		$match = safeGet($out, 1);

		if ($match !== null) {
			/* Remove duplicates */
			$match = array_values(array_unique($match));
			$match = parent::unescapeResult($match);
			$match = array_slice($match, 0, 10);
			parent::saveResult('bing', $term, $match);
			return $match;
		} else {
			return array();
		}
	}
};

SearchEngineParser::addParser('bing', new BingParser);

?>

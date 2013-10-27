<?

require_once 'SearchEngineParser.php';

class YahooParser extends SearchEngineParser {

	function setupHeader($curl_handler) {
		curl_setopt($curl_handler, CURLOPT_REFERER, "http://www.yahoo.se");
		curl_setopt($curl_handler, CURLOPT_USERAGENT, "Lynx/2.8.8dev.9 libwww-FM/2.14 SSL-MM/1.4.1 GNUTLS/2.12.14");
	}

	function getUrl($term) {
		return "http://se.search.yahoo.com/search;_ylt=AuziU.UhLLu5FRtU60DLT_pgorl_?p=" .
			   urlencode($term) . "&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-731";
	}

	function getResults($term) {
		$res = $this->loadWebpage($term);
		if ($res === FALSE) {
			return array();
		}

		preg_match_all("#<a id=\"link-[0-9]{1,2}\" class=\"yschttl spt\" href=\"[^\"]+\*\*([^\"]+)\"target=#",
			           $res,
					   $out,
                       PREG_PATTERN_ORDER);

		$match = safeGet($out, 1);

		if ($match !== null) {
			/* Remove duplicates */
			$match = array_unique($match);
			$match = parent::unescapeResult($match);
			$match = array_slice($match, 0, 10);
			parent::saveResult('yahoo', $term, $match);
			return $match;
		} else {
			return array();
		}
	}
};

SearchEngineParser::addParser('yahoo', new YahooParser);

?>

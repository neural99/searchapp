<?

function safeGet(array $arr, $key) {
	if (isset($arr[$key])) 
		return $arr[$key];
	else
		return null;
}

function output_json(array $arr) {
	header('Content-Type: application/json');		
	echo json_encode($arr);
}

?>

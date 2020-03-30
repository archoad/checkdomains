<?php
	$temp = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_NAME']);
	$url = '';
	for ($i=0; $i<=array_search('checkdomains', $temp); $i++) {
		$url .= $temp[$i].DIRECTORY_SEPARATOR;
	}
	session_destroy();
	header('Location: '.$url);
?>

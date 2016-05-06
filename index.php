<?php

// get request params
if(isset($_GET['c']))
	$command = $_GET['c'];
else
	$command = 'index';

// init debugger
require_once __DIR__.'/model/debug.php';
\debug\init(__DIR__.'/error.log');

switch($command){
	case 'index':
		include __DIR__.'/view/tmpl_index.html';
		break;
	case 'image':
		include __DIR__.'/model/image.php';
		break;
	default:
		header("HTTP/1.0 404 Not Found");
		include __DIR__.'/view/tmpl_e404.html';
}

?>
<?php
function capturer_put($request){
	$pid =shell_exec('/usr/bin/python motion.py > /dev/null 2>/dev/null & echo $!');
	//Printing additional info
	file_put_contents("pid.tmp",$pid);
}
	
function capturer_delete($request){

	if (!file_exists("pid.tmp")){
		return;
	}
	$pid = file_get_contents("pid.tmp");
	//unlink("pid.tmp");
	$result = shell_exec('kill -9 ' . $pid);
	echo $result;
}

function capturer_error($request){
	echo "Error 501";
}
	
	
$method = $_SERVER['REQUEST_METHOD'];
$request = explode("/",substr(@$_SERVER['PATH_INFO'],1));
switch ($method){
	case 'PUT':
		capturer_put($request);
		break;
	case 'DELETE':
		capturer_delete($request);
		break;
	default:
		capturer_error($request);
		break;
	}
?>

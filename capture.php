<?php
	function start_capture(){
		$resp =shell_exec('/usr/bin/python motion.py > /dev/null 2>/dev/null & echo $!');
		//Printing additional info
		echo $resp;
	}
	
	function stop_capture(){
		echo "Stopping capturing";
	}
	
	$enable=$_REQUEST["enable"];
	if ($enable == "true"){
		start_capture();
	} elseif ($enable == "false"){
		stop_capture();
	} else {
		echo "Error 501";
	}
?>

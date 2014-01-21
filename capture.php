<?php
	function start_capture(){
		$pid =shell_exec('/usr/bin/python motion.py > /dev/null 2>/dev/null & echo $!');
		//Printing additional info
		file_put_contents("pid.tmp",$pid);

	}
	
	function stop_capture(){
		if (file_exists("pid.tmp")){
			$pid = file_get_contents("pid.tmp");
			unlink("pid.tmp");
			$result = shell_exec('kill -9 ' . $pid);
			echo $result;
		}
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

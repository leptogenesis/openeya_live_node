<?php
//this is the general logging function. I will call this function for logs. This would allow me to changehow i log more easily
//
function flog($string) {
	echo "\n";
	echo basename(__FILE__, '.php').":";
	$btrace=debug_backtrace();
	if (count($btrace)>1) {echo debug_backtrace()[1]['function'];}
	echo ":".$string."\n";
};

//this function will determine whether the new schedule should be downloaded. It will call the function myshedule.php in the server
//to retrieve the md5 of the json_encoded schedule. Compare it with the md5 of the on disk schedule. It will return false if they do not match and tru otherwise
function should_the_schedule_be_updated($classroom) {
	global $server;
	global $serverroot;
	$check=false;
	$jsonmd5 = file_get_contents($server."/".$serverroot."myschedule.php?classroom=".$classroom."&hash=true");
	if (file_exists("schedule.php") ) {
		$saved = file_get_contents("schedule.php");
	} else {
		$saved="";
	};
	$md5saved=md5($saved);
	if ($jsonmd5!=$md5saved) {$check=true;};
	if ($check) {flog("current schedule is not upto date");} else {flog("the current schedule is up to date");};
	return $check;
}

function download_schedule_for($classroom) {
	global $server;
	global $serverroot;
	flog("downloading the new schedule");
	$json = file_get_contents($server."/".$serverroot."myschedule.php?classroom=".$classroom);
	file_put_contents("schedule.php",$json);
};


//this function will determine if I should be streaming or not.
function should_i_be_streaming() {
	global $pretime;
	$check = false;
	//read the schedule.php file. it is json encoded, so i json decode it.
	$schedule=file_get_contents("schedule.php");
	$schedule=json_decode($schedule);
	//this is the current time
	$now = strtotime(date("H_i"));
	//now go over all the defined times and check
	foreach ($schedule as $time => $info) {
		$starttime = strtotime($time);
		$duration = ((array) $info)["Duration"];
		if ($starttime - $now < $pretime && $now - $starttime < $duration) {$check = true;};
	};
	if ($check) {flog("I should be streaming");} else {flog("I should not be streaming");};
	return $check;
}

//this function checks if I should save the stream. saving has two parts: on the nodes, the video should be saved and then transfered to the 
//server for further processing. This transfer might happen e.g. at midnight. i should be carefull about naming the videos not to erase an 
//already recorded video. the second part of saving a course should happen on the server. (mainly to save disc space on the nodes). 
//getphoto.phpp called from capture_photos should save the videos on the server if the lecture is to be recorded.
//

function should_i_save_the_lecture() {
	//this function will be called only when a lecture is about to start streaming. I will check the lecture that starts within 5 minutes
	//of the time that this function 
	$schedule=file_get_contents("schedule.php");
        $schedule = json_decode($schedule);	
	$schedule = (array) $schedule;
	$now = strtotime(date("H:i"));
	foreach ($schedule as $time => $info) {
		$starttime = strtotime($time);
		if (abs($starttime-$now) < 300) {$coursetime=$time;};
	};
	if (!isset($coursetime)) {flog("this should not happen. Although I am starting streaming, I could not determine the start time");};
	//I determined the time, now i can determine the course and whether I should save it
	$course_info = (array) $schedule[$coursetime];
	//set default to false, and if recorded is chosen, change it to true
	$check = false;
	if ($course_info["Recorded"] == "True") {$check=true;};	
	return $check;
};

function file_name_to_save_movie() {
	//this function will be called only when a lecture is about to start streaming. I will check the lecture that starts within 5 minutes
	//of the time that this function 
	$schedule=file_get_contents("schedule.php");
        $schedule = json_decode($schedule);	
	$schedule = (array) $schedule;
	$now = strtotime(date("H:i"));
	foreach ($schedule as $time => $info) {
		$starttime = strtotime($time);
		if (abs($starttime-$now) < 300) {$coursetime=$time;};
	};
	if (!isset($coursetime)) {flog("this should not happen. Although I am starting streaming, I could not determine the start time");};
	//I determined the time, now i can determine the course and whether I should save it
	$course_info = (array) $schedule[$coursetime];
	//set default to false, and if recorded is chosen, change it to true
	$filename="Movie.mkv";
	if ($course_info["Recorded"] == "True") {$filename="Movie_".$course_info["CourseCode"]."_".date("Y_m_d")."_".str_replace(":","_",$coursetime).".mkv";};	
	return $filename;
};

//this function checks if i am streaming
//it will look for if capture photos and gst are running. if I am streaming, there should be one (or two) gst commands running
//and capture_photos should be running.
function am_i_streaming() {
	$check = true;
	$output=array();
	//this line checks if gst is running
	$pids = exec("ps aux | grep gst | grep -v grep",$output);
	$check = $check && (count($output) > 0);
	if (!$check) {flog("gstreamer is not running");} else {flog("there are instances of gstreamer running.");};
	$output=array();
	//this line check if capture_photo is running
	$pids = exec("ps aux | grep capture_photo | grep -v grep",$output);
	$check = $check && (count($output) > 0);
	if ($check) {flog("I am streaming");} else {flog("I am not streaming");};
	return $check;
};

// this function will read the variable from the config file
// the variable should appear at the beginnig of the line (spaces are fine)
// the string will be devided from the equality sign, if the first element of the array matches the $variable, the second element will
// be returnd
//
function bashconfig($file,$variable)
{
	$string=file_get_contents($file);
	//this line replaces double quuotes with nothing
	$string = str_replace('"',"",$string);
	$lines = explode("\n",$string);
	$result = "No Value Set";
	foreach ($lines as $line) {
		$tline = trim($line);
		$parts = explode("=",$tline);
		if ((string)$parts[0] == $variable ) {$result = $parts[1];};
		if (count($parts) > 2) {echo "the should be only one equality sign. This is an error";};
	};
	return $result;
};

?>

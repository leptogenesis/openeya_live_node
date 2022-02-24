<?php
echo "Manager.php is running as ".get_current_user();

chdir(__DIR__);
//config.php contains necessary information. I should not hardcode any other information into these files.
include_once("config.php");

//include the php functions

include_once("functions.php");

chdir($scriptfolder);
// check the schedule.php. is it up to date. note that before after midnight, it will take the nschedule of the new day.

if (should_the_schedule_be_updated($classroom)) {
	download_schedule_for($classroom);
};

//this will check if I should be streaming or not.
$should_i_stream = should_i_be_streaming();
//this will check if i am streaming or not
$am_i_streaming = am_i_streaming();

//if i should be streaming, and i am streaming, then don't do anything
//if i should not be streaming and i am not streaming, then don't do anything
//if i should be streaming and i am not streaming, then start streaming
if ( ($should_i_stream && $am_i_streaming) || (!$should_i_stream && !$am_i_streaming) ) {
	flog("everything seems to be running fine");
} else if ($should_i_stream && !$am_i_streaming) {
	//I should also add ONLYVIDEO=1 if only the video is needed and the photo is not needed.
	flog("trying to start streaming");
	$should_i_save = should_i_save_the_lecture();
	$filename=file_name_to_save_movie();
       $exec_string = "CLASSROOM=".$classroom." FILENAME=".$filename." ".$scriptfolder."/lecture_capture start >> log.txt 2>>log.txt &";	
	if ($should_i_save) {$exec_string = "SAVE=1 ".$exec_string;} else {$exec_string= "SAVE=0 ".$exec_string;};
	echo $exec_string;
	shell_exec($exec_string);
	#add the commands that would start streaming
} else if (!$should_i_stream && $am_i_streaming) {
	flog("trying to stop streaming");
	shell_exec("./lecture_capture stop > /dev/null 2>/dev/null &");
	#add command that would stop the streaming. make sure that it did actually stop
} else {
	flog("I should never reach here. There is a problem");
};
?>

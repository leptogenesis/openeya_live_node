#!/usr/bin/php 
<?php

//This script will take a two arguments
//	i. the name of the movie file
//
//the second one is necessary so that get photo can determine where to save the movie. 
//the script will then call getphoto.php with the given information and hence start a transfer

//read the configuretaion

require_once(__DIR__."/../config.php");
require_once(__DIR__."/../functions.php");

// Make sure that the script is called with an argument
//
if ($argc != 2) {
	echo "you need to provide the file name and the info as the only arguments";
	exit;
}

print_r($argv);
try {
	$filename=$argv[1];
} catch (Exception $ex) {
	echo "can not obtain the filename from the provided arguments";
	exit;
}

if (!file_exists($filename)) {
	echo "the given file ".$filename." does not exits. Can not transfer";
	exit;
}


//now I obtained the file to transfer. 

// determine if I should save the file. if true, only then I will transfer the file
// I should not call this function if the video is not to be saved anyway.
//

//getphoto.php requires the following parameters:
//	save=1 (if we are here, we are saving the lecture)
//	streaminghost=... (this is the host that is streaming. It will check if this is correct)
//	filename=... (the name of the file to be trasnfered. in this case, it is $filename)
//	classroom=... (the classroom. this should not be strictly necessaty, but anyway...)
//	courseinfo=... (this is not necessary for the photos since it transfers while the course is proceeding. This is
//			not the case for the video )
// courseinfo is not necessary as all the information is already contained in the movie filename
$capturinghost=trim(shell_exec("hostname -I | xargs"));
$CLASSROOM=$classroom;
$transferstring=$server."/getphoto.php?filename=".$filename."&streaminghost=".$capturinghost."&classroom=".$CLASSROOM."&save=1&movie=1";
echo $transferstring;

$reply = shell_exec('curl "'.$transferstring.'"');

echo $reply;

if (substr_count($reply,"file received OK") > 0 ) {
       echo "file transfered successfully. Deleting the local file"; 
       unlink($filename);
} else {
       echo "the file is not deleted as it is not transferred properly";
};

//$fh = fopen($pathtocapturefolder.$filename,"w");
//$ch = curl_init();
//echo "was here\n";
//$curlpath="http://".$streaminghost.$nodepathtophotos.$filename;
//echo "\n curl path is:";
//echo $curlpath;
//curl_setopt($ch,CURLOPT_URL,$curlpath);
//curl_setopt($ch,CURLOPT_FILE,$fh);
//curl_exec($ch);
//curl_close($ch);
	

?>

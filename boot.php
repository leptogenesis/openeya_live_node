<?php
//this is a script run at boot time. I will use this to signal to the server that thie node has booted and inform the 
//server of the IP address of the node	
//
$localIP = getHostByName(getHostName());

?> 

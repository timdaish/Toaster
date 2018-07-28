<?php
$inDomain = $_REQUEST['d'];
header('Content-Type: text/plain');
$hostname = gethostname();
echo "this server: " . $hostname .PHP_EOL;
echo ("get host by name".PHP_EOL);
if(substr($inDomain,-1) != '.')
    $inDomain = $inDomain . '.';
		$ipaddress = gethostbyname($inDomain);
        echo ("get host by name ip . '" . $inDomain . "'; address = '" . $ipaddress . "'".PHP_EOL);
        

?>
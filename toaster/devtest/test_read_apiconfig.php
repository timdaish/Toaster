<?php
header("Content-type:text/plain");
	// read user agents from json config file
	$jsonStr = file_get_contents("ua-config.json");
	$config = json_decode($jsonStr,true); // if you put json_decode($jsonStr, true), it will convert the json string to associative array
	//echo var_dump($config);

    echo "processing each ua" . PHP_EOL;
    foreach ($config as $key => $value) {
        echo ($key . ": " .$value['ua'] . " " . $value["res"] . " " . $value["uastr"] . PHP_EOL);
    }
?>
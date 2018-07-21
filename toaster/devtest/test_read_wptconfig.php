<?php

     $wptconfigfile = "wpt-private-config.json";
    // $wptconfigfile = "wpt-public-config.json";

    // read from config file
	$jsonStr = file_get_contents($wptconfigfile);
	$config = json_decode($jsonStr,true); // if you put json_decode($jsonStr, true), it will convert the json string to associative array
	//echo var_dump($config);

//echo "processing each wpt location" . PHP_EOL;
    foreach ($config as $key => $value) {
        if($key == "server")
        {
            echo ($key . ": " .$value['host'] );
            $wptserver = $value['host'];
        }
        else
            if($key == "locations")
            {

                foreach ($value as $lkey => $lvalue) {
                    echo ($lkey . ": " .$lvalue['location'] . " " . $lvalue["speed"] . " " . $lvalue["options"] . PHP_EOL);
                    if($wptbrowser == $lkey)
                    {
                        $loc = $lvalue['location'];
                        $speed = $lvalue["speed"];
                        $browseroptions = $lvalue["options"];
                        break;
                    }
                }
            }
    }

?>
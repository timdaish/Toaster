<?php
// WPT FUNCTIONS
function submitWPTTest($wptbrowser,$url,$ua,$vpw,$vph,$un,$pw)
{
    global $wptserver;
    //read config file
    $wptprivate = false;
    $wptpublic = false;
    global $browserengine;
    if($browserengine == 6)
    {
        $wptprivate = true;
        $wptconfigfile = "wpt-private-config.json";
    }
    else
    {
        $wptpublic = true;
        $wptconfigfile = "wpt-public-config.json";
    }
    // read from config file
	$jsonStr = file_get_contents($wptconfigfile);
	$config = json_decode($jsonStr,true); // if you put json_decode($jsonStr, true), it will convert the json string to associative array
	//echo var_dump($config);
    $browseroptions = "";
//echo "processing server and each wpt location" . PHP_EOL;
    foreach ($config as $key => $value) {
        if($key == "server")
        {
//echo ($key . ": " .$value['host'] );
            $wptserver = $value['host'];
        }
        else
            if($key == "locations")
            {
                $isdefault = false;
                $cfgfound = false;
                $defaultbrowseroptions = '';
                $defaultloc = '';
                $defaultspeed = '';
                foreach ($value as $lkey => $lvalue) {
//echo ($lkey . ": " .$lvalue['location'] . " " . $lvalue["speed"] . " " . $lvalue["options"] . PHP_EOL);

                    // check for default
                    if($lvalue["options"] == true)
                    {
                        // set defaults
                        $defaultloc = $lvalue['location'];
                        $defaultspeed = $lvalue["speed"];
                        if($defaultspeed != '')
                            $defaultloc = $defaultloc . "." . $lvalue["speed"];
                        if($lvalue["options"] == "ua")
                            $defaultbrowseroptions = "&uastring=".$ua;
                        if($lvalue["options"] == "vp")
                            $defaultbrowseroptions = "&width=".$vpw."&height=".$vph;
                    }

                    if($wptbrowser == $lkey)
                    {
                        $loc = $lvalue['location'];
                        $speed = $lvalue["speed"];
                        if($speed != '')
                            $loc = $loc . "." . $lvalue["speed"];
                        if($lvalue["options"] == "ua")
                            $browseroptions = "&uastring=".$ua;
                        if($lvalue["options"] == "vp")
                            $browseroptions = "&width=".$vpw."&height=".$vph;
                        $cfgfound = true;
                        break;
                    }
                } // end for each
                if (!$cfgfound) // set default
                {
                    $loc = $defaultloc;
                    $speed = $defaultspeed;
                    if($speed != '')
                        $loc = $defaultloc . "." . $lvalue["speed"];
                        $browseroptions = $defaultbrowseroptions;
                }
            }
    }
    $options = "&fvonly=1&video=1&noopt=1&priority=2&pngss=1&bodies=1&location=".$loc;
    if($un !="" and $pw != "")
    {
        $options = $options . "&login=".$un."&password=".$pw."&authtype=0";
    }
    $result = file_get_contents('http://'.$wptserver.'/runtest.php?url='.$url."&f=json" . $options . $browseroptions);

//echo ("WPT Test Submission for '". $url . "'<br/>");
//echo ($result);

    // get testresults id from JSON response
    // decode json object
    $jsonresponse = json_decode($result);
//echo ("WPT response as JSON<br/>");

//echo ("WPT Result<br/>");
    $statusCode =  $jsonresponse->statusCode;
    $testId = $jsonresponse->data->testId;
    $jsonUrl = $jsonresponse->data->jsonUrl;
    $xmlUrl = $jsonresponse->data->xmlUrl;
    $summaryCSV = $jsonresponse->data->summaryCSV;
    $detailCSV = $jsonresponse->data->detailCSV;
//echo ("StatusCode: ". $statusCode . "<br/>");
//echo ("TestID " . $testId . "<br/>");

    return array ($testId,$jsonUrl,$summaryCSV,$detailCSV);
}

function checkWPTTestStatus($testId)
{
    global $wptserver;
    $result = file_get_contents('http://'.$wptserver.'/testStatus.php?f=json&test=' . $testId);
    $jsonresponse = json_decode($result);
    $statusCode =  $jsonresponse->statusCode;
//echo ("test running" . "<br/>");
//echo ("StatusCode: ". $statusCode . "<br/>");
//echo ("TestID " . $testId . "<br/>");

    return $statusCode;
}

function getWPTTestResults($resultURL)
{
    $result = file_get_contents($resultURL);
    return $result;
}

function getWPTHAR($testId)
{
    global $wptserver;
    $result = file_get_contents('http://'.$wptserver.'/export.php?test=' . $testId);
    return $result;
}

function getWPTImagePath($testId,$imgname)
{
    global $wptserver;
    $ipath = 'http://'.$wptserver.'/results/'.date("y/m/d").'/'.substr($testId,7,2).'/'.substr($testId,10,2).'/1_screen.png';
//echo("webpagetest image name to get: ".$ipath ."<br/>");
//echo("saving as: ".$imgname ."<br/>");
    file_put_contents($imgname, file_get_contents($ipath));
    return $ipath;
}
?>
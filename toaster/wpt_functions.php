<?php
// WPT FUNCTIONS
function submitWPTTest($wptbrowser,$url,$ua,$vpw,$vph,$un,$pw)
{
    $browseroptions = "";
    switch ($wptbrowser)
    {
        case "Chrome":
            $loc = "local_wptdriver:Chrome.PA";
            break;
        case "Firefox":
            $loc = "local_wptdriver:Firefox.PA";
            break;
        case "IE":
            $loc = "local_wptdriver:IE.PA";
            break;
        case "Edge":
            $loc = "local_wptdriver:IE.PA";
            $browseroptions = "&uastring=".$ua;
            break;
        case "Android5.1N7":
            $loc = "Local_Nexus7";
            break;
        case "Android5.0M":
            $loc = "Local_MotoG";
            break;
        case "iPhone iOS11":
            $loc = "BiPhone6";
            break;
        case "iPad iOS11":
            $loc = "BiPadAir";
            break;
        default:
            $loc = "local_wptdriver:Chrome.PA";
            $browseroptions = "&uastring=".$ua;
            $vpoptions = "&width=".$width."&height=".$height;

    }




    $options = "&fvonly=1&video=1&noopt=1&priority=2&pngss=1&bodies=1&location=".$loc;

    if($un !="" and pw != "")
    {
        $options = $options . "&login=".$un."&password=".$pw."&authtype=0";
    }
    $result = file_get_contents('http://10.90.67.11/runtest.php?url='.$url."&f=json" . $options . $browseroptions);

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
    $result = file_get_contents('http://10.90.67.11/testStatus.php?f=json&test=' . $testId);
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
    $result = file_get_contents('http://10.90.67.11/export.php?test=' . $testId);
    return $result;
}

function getWPTImagePath($testId,$imgname)
{
    $ipath = 'http://10.90.67.11/results/'.date("y/m/d").'/'.substr($testId,7,2).'/'.substr($testId,10,2).'/1_screen.png';
//echo("webpagetest image name to get: ".$ipath ."<br/>");
//echo("saving as: ".$imgname ."<br/>");
    file_put_contents($imgname, file_get_contents($ipath));
    return $ipath;
}
?>
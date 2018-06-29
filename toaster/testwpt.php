<?php
include 'wpt_functions.php';

$url = 'http://www.freshegg.co.uk/';
$urlenc = urlencode($url );
list ($testId,$jsonResult,$summaryCSV,$detailCSV ) = submitWPTTest($urlenc);

echo "Test submitted<br/>";
// check test status
$statusCode = 0;
while (intval($statusCode) != 200) {
    $statusCode = checkWPTTestStatus($testId);
    sleep(1);
 }


echo ("Test ended" . "<br/>");
echo ("StatusCode: ". $statusCode . "<br/>");
echo ("TestID " . $testId . "<br/>");

//$result = getWPTTestResults($jsonResult);
// get testresults id from JSON response
//echo ($result);

$result = getWPTHAR($testId);
// get testresults id from JSON response
echo ($result);

//

?>
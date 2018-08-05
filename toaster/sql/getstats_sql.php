<?php
header('Content-Type: application/json');
require_once "dbconnect.php";
$r = getstats();
echo json_encode($r);

function getstats()
{
  global $conn,$OS;

  $sql = "SELECT COUNT(*) as count FROM logs";   
  if ($result = $conn->query($sql)) {
    /* fetch object array */
    while ($row = $result->fetch_row()) {
    //     printf ("%s (%s)\n", $row[0], $row[1]);
    $countAll = $row[0];
   }
  // /* free result set */
  $result->close();
}
//echo "countall: ". $countAll;

$sql = "SELECT COUNT(*) as count FROM logs WHERE dt_ended IS NOT NULL";   
if ($result = $conn->query($sql)) {
  /* fetch object array */
  while ($row = $result->fetch_row()) {
  //     printf ("%s (%s)\n", $row[0], $row[1]);
  $countCompleteAll = $row[0];
 }
// /* free result set */
 $result->close();
}
//echo "countcompleteall: ". $countCompleteAll;


$sql = "SELECT COUNT(*) as count FROM logs WHERE dt_ended IS NULL and dt_started > CURRENT_TIMESTAMP - INTERVAL 5 MINUTE";   
if ($result = $conn->query($sql)) {
  /* fetch object array */
  while ($row = $result->fetch_row()) {
  //     printf ("%s (%s)\n", $row[0], $row[1]);
  $countRunning = $row[0];
 }
 
// /* free result set */
$result->close();
}
else
 echo "error getting running count";
//echo "Noof Running: " . $countRunning . PHP_EOL;




// // get all results
$stack = array();
$sql = "SELECT * FROM logs ORDER by dt_ended desc";   
if ($result = $conn->query($sql)) {
  /* fetch object array */
  while ($row = $result->fetch_row()) {
// printf ("%s (%s)\n", $row[0], $row[1]);
    $toasterid = $row[0];
    $svrip = $row[1];
    $url = urldecode($row[2]);
    $wbe = $row[3];
    $hchloc = $row[4];
    $sdt = $row[5];
    $edt = $row[6];
    $usrip = $row[7];

 // echo "{$ipa}: {$sdt} - {$edt}: {$toasterid}: URL: {$url}". PHP_EOL;
  $stack[] = ["tid"=>$toasterid,"svrip"=>$svrip,"sdt"=>$sdt,"edt"=>$edt,"url"=>$url,"wbe"=>$wbe,"hchloc"=>$hchloc,"usrip"=>$usrip,];
 }
// /* free result set */
$result->close();
}
// $sql = "SELECT * FROM LOG ORDER by dt_ended desc";
// // select today and yesterday only
// $sql = "SELECT * FROM LOG WHERE dt_ended >= datetime('now', '-1 days')  AND dt_ended <  datetime('now') ORDER by dt_ended desc"; 
// $result = $db->query($sql);


$arr = array ("noofTestsAll"=> $countAll,"noofCompleteAll"=> $countCompleteAll,"noofRunning5mins"=> $countRunning, "tests" => $stack);
return $arr;
}
?>
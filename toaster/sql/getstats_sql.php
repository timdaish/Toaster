<?php
header('Content-Type: application/json');
$db = new SQLite3('toasterlog.db');

// get count of all results
$countAll = $db->querySingle("SELECT COUNT(*) as count FROM LOG");
//echo "Noof log records: " . $count . PHP_EOL;

$countComplete = $db->querySingle("SELECT COUNT(*) as count FROM LOG WHERE dt_ended IS NOT NULL");
//echo "Noof log records: " . $count . PHP_EOL;

$countRunning = $db->querySingle("SELECT COUNT(*) as count FROM LOG WHERE dt_ended IS NULL and dt_started > datetime('now', '-5 minutes')");
//echo "Noof log records: " . $count . PHP_EOL;



// SELECT * FROM MyTable WHERE myDate >= date('now', '-1 days')  AND myDate <  date('now') // select today and yesterday only


// get all results
$result = $db->query("SELECT * FROM LOG ORDER by dt_ended desc");
$stack = array();
$arrtest = array();
while ($row = $result->fetchArray())
{   
    $toasterid = $row['toaster_id'];
    $ipa = $row['ip_address'];
    $url = urldecode($row['toasted_url']);
    $sdt = $row['dt_started'];
    $edt = $row['dt_ended'];
    $wbe = $row['wb_engine'];
    $hchloc = $row['hch_loc'];
 // echo "{$ipa}: {$sdt} - {$edt}: {$toasterid}: URL: {$url}". PHP_EOL;
  $stack[] = ["tid"=>$toasterid,"ip"=>$ipa,"sdt"=>$sdt,"edt"=>$edt,"url"=>$url,"wbe"=>$wbe,"hchloc"=>$hchloc];
}
//print_r($stack);

$arr = array ("noofTestsAll"=> $countAll,"noofCompleteAll"=> $countComplete,"noofRunning5mins"=> $countRunning, "tests" => $stack);
echo json_encode($arr)
?>
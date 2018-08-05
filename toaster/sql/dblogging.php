<?php
require_once "dbconnect.php";

// read parameters
$tid = $_REQUEST["tid"];
$ip = $_REQUEST["ip"];
$url = URLENCODE($_REQUEST["url"]);
if(isset($_REQUEST["eng"]))
    $eng = $_REQUEST["eng"];
else
    $eng = '';
if(isset($_REQUEST["hchloc"]))
    $hch = URLENCODE($_REQUEST["hchloc"]);
else
    $hch = '';
$status = true;
$uip = $_REQUEST["uip"];

logAction($tid,$ip,$url,$eng,$hch,$uip);

//////////////////////////////////////////////////////////////////////////////////
function logAction($tid,$ip,$url,$eng,$hch,$uip)
{
    global $conn,$OS;
    if ($OS == "Windows")
    {
        $sql = "INSERT INTO logs (toaster_id,server_ip,toasted_url,wb_engine,hch_loc,dt_started,user_ip)
        VALUES ('$tid', '$ip', '$url','$eng','$hch', CURRENT_TIMESTAMP,'$uip') ON DUPLICATE KEY UPDATE dt_ended=CURRENT_TIMESTAMP;";
    }
    else
    {
        $sql = "INSERT INTO logs (toaster_id,server_ip,toasted_url,wb_engine,hch_loc,dt_started,user_ip)
        VALUES ('$tid', '$ip', '$url','$eng','$hch', CURRENT_TIMESTAMP,'$uip') ON DUPLICATE KEY UPDATE dt_ended=CURRENT_TIMESTAMP;";
        }    
    if ($conn->query($sql) === TRUE) {
//echo "New log record created successfully";
    } else {
//echo "Error creating log entry: " . $sql . "<br>" . $conn->error;
    }
}
?>
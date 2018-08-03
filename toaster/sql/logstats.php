<?php
header('Content-Type: application/json');
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
    class MyDB extends SQLite3 {
        function __construct() {
        $this->open('toasterlog.db');
        }
    }
    $db = new MyDB();
    if(!$db)
    {
        header('Temporary-Header: True', true, 204);
        header_remove('Temporary-Header');
// echo $db->lastErrorMsg();
        die;
    } else {
//echo "Opened database successfully\n";
    
        // create table if not xists
        $sql = "CREATE TABLE IF NOT EXISTS LOG
        (id INTEGER PRIMARY KEY AUTOINCREMENT,
        toaster_id              TEXT UNIQUE   NOT NULL,
        ip_address              TEXT NOT NULL,
        toasted_url             TEXT NOT NULL,
        wb_engine               TEXT NOT NULL,
        hch_loc                 TEXT,
        dt_started              TIMESTAMP,
        dt_ended                TIMESTAMP);
        ";
  
     $ret = $db->exec($sql);
     if(!$ret){
//echo $db->lastErrorMsg();
     } else {
//echo "LOG table found or created successfully\n";
     }

     $db->enableExceptions(true);
     try {

        $sql = "INSERT INTO LOG (toaster_id,ip_address,toasted_url,wb_engine,hch_loc,dt_started)
        VALUES ('$tid', '$ip', '$url','$eng','$hch', CURRENT_TIMESTAMP)";

       $ret = $db->exec($sql);
    } catch (Exception $e)
    {
    // echo "PHP SQLITE Error: " . $e . " " . $db->lastErrorCode()  . " " . $db->lastErrorMsg(); 
        if($db->lastErrorCode() == 19)
        {
            $sql = "UPDATE LOG SET dt_ended = CURRENT_TIMESTAMP WHERE toaster_id LIKE '$tid';";
            $ret = $db->exec($sql);
    //echo "Record updated successfully\n";
        }
        else
        {
            $status = false;
   // echo "PHP SQLITE Error: " . $db->lastErrorCode()  . " " . $db->lastErrorMsg();
        }
    } // end catch

     $db->close();
    } // end if db successful
    $arr = array('tid' => $tid,  'status' => $status);
    echo json_encode($arr);
?>
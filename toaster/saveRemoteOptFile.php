<?php
session_start();
date_default_timezone_set('UTC');
//header('Content-Type: plain/text');
header('Content-Type:text/html');
$hostname = gethostname();
$toasterid = $_REQUEST["tid"];
$om = $_REQUEST["om"];
$optpath = $_REQUEST["op"];
$imgdata = $_FILES["imgdata"]['name'];
echo "image opt path = " . $optpath .'/n';

if(!empty($_FILES['uploaded_file']))
{
  $optpath = str_replace('\\', '/', $optpath);
  $path = $optpath . "/" .$om . "/"; //"/var/sites/w/webpagetoaster.com/uploads/".$toasterid."/".$om."/";
  // if(!file_exists("/var/sites/w/webpagetoaster.com/uploads/".$toasterid))
  //   mkdir("/var/sites/w/webpagetoaster.com/uploads/".$toasterid);
  if(!file_exists($optpath))
    mkdir($optpath);
  if(!file_exists($path))
    mkdir($path);
  $path = $path . basename( $_FILES['uploaded_file']['name']);
  if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
    echo "The file ".  basename( $_FILES['uploaded_file']['name']). 
    " has been uploaded to " . $path . "/n";
  } else{
      echo "There was an error uploading the file, please try again!";
      echo $_FILES['uploaded_file']['error'];
  }
}
echo '/nHere is some more debugging info:';
print_r($_FILES);
?>
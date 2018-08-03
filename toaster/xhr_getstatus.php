<?php
session_start();
header('Content-Type: application/json');
$serverName = 'http://'.$_SERVER['SERVER_NAME'];
$hostname = gethostname();
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
//echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
//echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
$toasterid = $_REQUEST["tid"];
$lastpos = intval($_REQUEST["p"]);
if($OS == "Windows")
{
	$debuglog = "f:\\toast\\logs\\" . $toasterid . "_debug.txt";
	$statuslog = "f:\\toast\\logs\\" . $toasterid . "_status.json";
}
else
{
    //set path for webpagetoaster server and others
	if( strpos($hostname,"gridhost.co.uk") != false)
    {
		$debuglog = "/var/sites/w/webpagetoaster.com/public_html/logs/" . $toasterid . "_debug.txt";
		$statuslog = "/var/sites/w/webpagetoaster.com/public_html/logs/" . $toasterid . "_status.txt";
	}
	else{
		$debuglog = "/usr/share/toast/logs/" . $toasterid . "_debug.txt";
		$statuslog = "/usr/share/toast/logs/" . $toasterid . "_status.txt";
	}
}
if($lastpos <= 0)
  $lastpos = 0;
if(file_exists($statuslog))
{
  $statuslogtext = "status log exists";
  // read data from status log
  $fileLength = filesize($statuslog);
  $buffer = '';
  $fhandle = fopen($statuslog, 'r');
  if ($fhandle) {
  // get file data from last position lastpos
    fseek($fhandle, $lastpos, SEEK_SET);
    while (!feof($fhandle)) {
        $buffer .= fgets($fhandle);
//echo $buffer;
    }
  }
  fclose ($fhandle);
  if($buffer != '')
  {
    echo json_encode($buffer);
  }
  else
    header('Temporary-Header: True', true, 204);
}
else
{
  if(isset($_SESSION['status'])) {
    $sdata = $_SESSION['status'];
    $mdata = $_SESSION['mimetype'];
    $mdata = str_replace('/','_',$mdata);
    $odata = $_SESSION['object'];
    $idata = $_SESSION['imagepath'];
    $fdata = $_SESSION['toastedfile'];
    $arr = array('status' => $sdata,  'mimetype' => $mdata, 'object' => $odata, 'imagepath' => $idata, 'toastedfile' => $fdata ,'debug' =>$statuslog);
    echo json_encode($arr);
  }
}
?>
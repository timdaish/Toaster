<?php
header('Content-type: application/json; charset=UTF-8');
$serverName = 'http://'.$_SERVER['SERVER_NAME'];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    //echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
    //echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
include 'imagedecoding.php';
include 'fontdecoding.php';
//include 'ttfInfo.class.php';

if(isset($_GET["pathname"]))
    $fontpathname = $_GET["pathname"];
else
{
    $fontname = $_GET["name"];
    $fontpathname = 'c:\\temp\\'.$fontname;
}
    $cmap = '';
    list($fontinfo,$cmap) = readWOFFFont($fontpathname );
    // echo $fontinfo. ': GLYPH Chracter Mapping (Windows CMAP contents)<pre>';
    // print_r($cmap);
    // echo '</pre>';
    $fontinfo = str_replace('\\u0000', "", json_encode($fontinfo));
    $arr = array("fontname"=> $fontinfo, "cmap"=> $cmap);
    echo json_encode($arr);
?>

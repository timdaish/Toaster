<?php
session_start();
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    //echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
    //echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
$data = file_get_contents('php://input');

$d = urldecode($data);
$djson = json_decode(substr($d,4));

$ilen = count($djson);

$arr = array();

foreach ($djson as $value) {

//$value is an object of stdclass

    $url = $value->url;
    $file = str_replace("\\\\", "\\",$value->localfile);
    $path_parts = pathinfo($file);
    $filename = $path_parts['filename'];

    $savepath = $value->savepath;
    //echo("save path b4:". $savepath);
    if($OS == 'Windows')
        $savepath = str_replace("/","\\",$savepath);
    //echo("save path after:". $savepath);

    $folder = '_Thumbnails';
    $baseImgfolder =  $savepath.$folder;
    if (!file_exists($baseImgfolder))
        mkdir($baseImgfolder, 0777, true);

    if($OS == 'Windows')
        $os_cmd = 'c:\ImageMagick\mogrify -format gif -path ' . $baseImgfolder . ' -thumbnail 100x100 ' . escapeshellarg($file);
    else
        $os_cmd = 'mogrify -format gif -path ' . $baseImgfolder . ' -thumbnail 100x100 ' . escapeshellarg($file);
    $res = array();
	exec($os_cmd,$res);
    //print_r($res);

    $imgarr = array('tool' => 'imagemagick', 'file' => $file, 'sfolder' => $baseImgfolder );
    $arr[]=$imgarr;



  }// end for each djson




    header('Content-Type: application/json');
    echo json_encode($arr, JSON_FORCE_OBJECT);
    exit;

?>

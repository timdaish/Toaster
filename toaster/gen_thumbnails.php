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
header('Content-Type: application/json');
$data = $_REQUEST["ids"];
//echo 'Current script owner: ' . get_current_user();
$d = urldecode($data);
$djson = json_decode($d);
$ilen = count($djson);
$arr = array();
if($ilen == 0)
{
    $imgarr = array('tool' => "fail", 'file' => $file, 'sfolder' => '', 'status'=> "fail" );
    $arr[]=$imgarr;
    echo json_encode($arr, JSON_FORCE_OBJECT);
    exit;
}
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
    $saveimage = $baseImgfolder . "/" . $filename . ".gif";

    if(file_exists( $saveimage))
    {
        $imgarr = array('tool' => "", 'file' => $file, 'sfolder' => '', 'status'=> "exists");
        $arr[]=$imgarr;
        //echo json_encode($arr, JSON_FORCE_OBJECT);
        continue;
    }

    if (!file_exists($baseImgfolder))
        mkdir($baseImgfolder, 0777, true);
    $toolname = "ImageMagick";
    if($OS == 'Windows')
        $os_cmd = 'c:\ImageMagick\mogrify -format gif -path ' . $baseImgfolder . ' -thumbnail 100x100 ' . escapeshellarg($file);
    else
    {
        $os_cmd = 'mogrify -format gif -path ' . $baseImgfolder . ' -thumbnail 100x100 ' . escapeshellarg($file);
    }
    $res = array();
	exec($os_cmd,$res);
    //print_r($res);
    // check if mogrify worked
    if(!file_exists($saveimage)  and $OS != 'Windows')
    {
        $toolname = "PHP GD";
        // get image format
        $imageTypeArray = array
        (
            0=>'UNKNOWN',
            1=>'GIF',
            2=>'JPEG',
            3=>'PNG',
            4=>'SWF',
            5=>'PSD',
            6=>'BMP',
            7=>'TIFF_II',
            8=>'TIFF_MM',
            9=>'JPC',
            10=>'JP2',
            11=>'JPX',
            12=>'JB2',
            13=>'SWC',
            14=>'IFF',
            15=>'WBMP',
            16=>'XBM',
            17=>'ICO',
            18=>'COUNT'  
        );
        $hostname = gethostname();
 
        if( strpos($hostname,"gridhost.co.uk") != false)
        { // convert filepath to local system
            $file = str_replace("https://www.webpagetoaster.com/",'/var/sites/w/webpagetoaster.com/public_html/',$file);
            $saveimage = str_replace("https://www.webpagetoaster.com/",'/var/sites/w/webpagetoaster.com/public_html/',$saveimage);
        }
//echo("converting gd image:"  . $file."<br/>");
        $size = getimagesize($file);
        
        $imgmimetype = image_type_to_mime_type($size[2]);
        switch ($imgmimetype)
        {
            case 'image/gif': // GIF
                $img = imagecreatefromgif($file);
                break;
            case 'image/jpeg': //JPEG
                $img = imagecreatefromjpeg($file);
                break;
            case 'image/png': // PNG
                $img = imagecreatefrompng($file);
                break;
            case 'image/bmp': // BMP
                $img = imagecreatefromwbmp($file);
                break;
            case 'image/jp2': // JP2
                $img = imagecreatefromwbmp($file);
                break;
            case 'image/webp': // JP2
                $img = imagecreatefromwebp($file);
                break;
            default:

        }
        if($img)
        {

                    // use GD library

                    // resize it as thumbnail
                    $img = imagescale($img,100);
                    // Save the resize image as a GIF
//echo ("saving GD thumbnail: " . $baseImgfolder.'/'.$filename."<br/>");
                    imagegif($img, $saveimage);

                    // Free from memory
                    imagedestroy($img);
        }
    } // end use GD functions
    if(file_exists($saveimage))
        $status = true;
    else
        $status = false;
    $imgarr = array('tool' => $toolname, 'file' => $file, 'sfolder' => $baseImgfolder, 'status'=> $status,'savepath'=> $saveimage);
    $arr[]=$imgarr;
  }// end for each djson
    echo json_encode($arr, JSON_FORCE_OBJECT);
    exit;
?>
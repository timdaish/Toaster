<?php
session_start();
date_default_timezone_set('UTC');
$hostname = gethostname();
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    //echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
    $debuglog = "c:\\temp\\".$toasterid."_debug_optimg.txt";
}
else {
    //echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
        //set path for webpagetoaster server and others
	if( strpos($hostname,"gridhost.co.uk") != false)
    {
		$debuglog = "/var/sites/w/webpagetoaster.com/subdomains/toast/".$toasterid."_debug_optimg.txt";
	}
	else{
		$debuglog = "/usr/share/toast/".$toasterid."_debug_optimg.txt";
	}
}
ini_set("log_errors", 1);
ini_set("error_log", $debuglog);
file_put_contents($debuglog, "IMAGE OPTIMISATION DEBUG LOG started" . PHP_EOL);

$data = file_get_contents('php://input');
$d = urldecode($data);
$djson = json_decode(substr($d,4));

$ilen = count($djson);

//echo "first item: ".$djson[0]->ObjNo."</br>";
//error_log("opt image first item: ".$djson[0]->ObjNo);
foreach ($djson as $value) {

//$value is an object of stdclass

  $mt =  $value->mimetype;
  $url = $value->url;
  $file = str_replace("\\\\", "\\",$value->localfile);
  echo ("optmising image: " . $file.PHP_EOL);
  // update localfilepath for linux
    if( $OS != "Windows")
    {
                //set path for webpagetoaster server and others
	if( strpos($hostname,"gridhost.co.uk") != false)
    {
		$file = str_replace ("http://toast.webpagetoaster.com", "/var/sites/w/webpagetoaster.com/subdomains/toast",$file);
	}
	else{
        $file = str_replace ("http://toast.webpagetoaster.com", "/usr/share/toast",$file);
	    }
    }


  $savepath = $value->savepath;
  $animflag = $value->animflag;
  //$tinyjpgkey = $value->tinyjpgapikey;
  error_log("opt image: ".$file);

  $path_parts = pathinfo($file);
  $filename = $path_parts['filename'];
  echo ("saving optmised image to the path: " . $savepath .PHP_EOL);
  if($OS == 'Windows')
    $savepath = str_replace("/","\\",$savepath);
  else
    if( strpos($hostname,"gridhost.co.uk") != false)
    {
		$savepath= "/var/sites/w/webpagetoaster.com/subdomains/toast/";
	}
	else{
		$savepath = "/usr/share/toast/";
	}


 error_log("opt image save path: ".$savepath);
  switch($mt)
  {
    case "image/png":
        $pngoptres = array();
        $pngoptres = optimisePNG($savepath, $file);
        $arr = $pngoptres;
        break;

    case "image/gif":
        $gifoptres = array();
        if($animflag == false)
            $gifoptres = optimiseGIF($savepath, $file);
        else
            $gifoptres = optimiseGIFAnimation($savepath, $file);
        $arr = $gifoptres;
        break;

    case "image/jpeg":
    case "image/jpg":
        $jpgoptres = array();
        $jpgoptres = optimiseJPG($savepath, $file);

        $arr = array();
        $arr = $jpgoptres;

        break;
  }


} // end foreach $djson

  header('Content-Type: application/json');
  echo json_encode($arr, JSON_FORCE_OBJECT);
  exit;



// $arr is now array(2, 4, 6, 8)
unset($value);


// end of main





////////////////////////////////////////////////////////////////////
// functions
////////////////////////////////////////////////////////////////////
function is64Bits() {
    return strlen(decbin(~0)) == 64;
}

function optimisePNG($savepath, $lfn)
{
  global $OS;
    $path_parts = pathinfo($lfn);
    $filename = $path_parts['filename'];

    $folder = '_Optimised_Images';
    $baseImgfolder =  $savepath.$folder;
    if (!file_exists($baseImgfolder))
        mkdir($baseImgfolder, 0777, true);

    $folder = DIRECTORY_SEPARATOR;
    $PNGImgfolder = $baseImgfolder.$folder;
    if (!file_exists($PNGImgfolder))
        mkdir($PNGImgfolder, 0777, true);
    //echo "optimising PNG: ".$lfn."</br>";
    echo ("optimising PNGs to savepath folder: ".$PNGImgfolder."</br>");

    // init array to return
    $pngdata = array();
    $pngopt = array();

    //// OPTIMISATION - EXIFTOOOL - REMOVE METADATA
    $folder = 'png_no_metatdata'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    $ImgWithoutMetadata = $PNGImgfile;

    echo "optimising PNG as: ".$PNGImgfile."</br>";
    if($OS == 'Windows')
        $os_cmd = 'win_tools\exiftool -all= -o '.escapeshellarg($ImgWithoutMetadata) . ' ' . escapeshellarg($lfn);
    else
    
        $os_cmd = './lnx_tools/ExifTool/exiftool -all= -o '.escapeshellarg($ImgWithoutMetadata) . ' ' . escapeshellarg($lfn);
    echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'Exiftool', 'id' => 'no_metadata',  'operation' => 'Remove Metadata', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);



    //// OPTIMISATION - ImageMagick Convert PNG without metadata to pixmap
    $SaveImgfolder = $PNGImgfolder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $decoded_pixmap = $SaveImgfolder . $filename . '.ppm';

    if($OS == 'Windows')
        $os_cmd = 'c:\ImageMagick\convert ' . escapeshellarg($ImgWithoutMetadata) . ' '. escapeshellarg($decoded_pixmap) ;
    else
        $os_cmd = 'convert ' . escapeshellarg($ImgWithoutMetadata) . ' '. escapeshellarg($decoded_pixmap) ;
    $res = array();
	exec($os_cmd,$res);
    //print_r($res);

    $PNGImgfileUnoptimised = $ImgWithoutMetadata;


 //// OPTIMISATION - unoptimised PNG to PNGquant
    $folder = 'pngquant'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if($OS == 'Windows')
        $os_cmd = 'win_tools\pngquant ' .escapeshellarg($PNGImgfileUnoptimised) .' -o '.escapeshellarg($PNGImgfile);
    else
        $os_cmd = '.\lnx_tools\pngquant ' .escapeshellarg($PNGImgfileUnoptimised) .' -o '.escapeshellarg($PNGImgfile);
    //echo 'cmd = '.$os_cmd;
        $res = array();
	exec($os_cmd,$res);
    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'pngquant', 'id' => 'PNGQUANT',  'operation' => 'Optimise', 'settings' => 'Optimised PNGQuant', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end png opt

  //// OPTIMISATION - unoptimised PNG to PNGcrush
    $folder = 'pngcrush'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if(is64Bits())
        $pngcrushtool = "pngcrush_w64";
    else
        $pngcrushtool = "pngcrush_w32";

    if($OS == 'Windows')
        $os_cmd = "win_tools".DIRECTORY_SEPARATOR . $pngcrushtool .' ' .escapeshellarg($PNGImgfileUnoptimised) .' ' .escapeshellarg($PNGImgfile);
    else
        $os_cmd = 'pngcrush ' .escapeshellarg($PNGImgfileUnoptimised) .' ' .escapeshellarg($PNGImgfile);
    //echo 'cmd = '.$os_cmd;
    $res = array();
	exec($os_cmd,$res);
    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'pngcrush', 'id' => 'PNGCRUSH',  'operation' => 'Optimise', 'settings' => 'Optimised PNGCrush', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end pngcrush


  //// OPTIMISATION - unoptimised PNG to PNGcrush
    $folder = 'pngcrush_brute'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if(is64Bits())
        $pngcrushtool = "pngcrush_w64";
    else
        $pngcrushtool = "pngcrush_w32";

    if($OS == 'Windows')
        $os_cmd = "win_tools".DIRECTORY_SEPARATOR . $pngcrushtool .' -brute ' .escapeshellarg($PNGImgfileUnoptimised) .' ' .escapeshellarg($PNGImgfile);
    else
        $os_cmd = 'pngcrush -brute ' .escapeshellarg($PNGImgfileUnoptimised) .' ' .escapeshellarg($PNGImgfile);
    //echo 'cmd = '.$os_cmd;
    $res = array();
	exec($os_cmd,$res);
    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'pngcrushBrute', 'id' => 'PNGCRUSHbrute',  'operation' => 'Brute force optimsisation', 'settings' => 'pngcrush -brute', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end pngcrush



  //// OPTIMISATION - unoptimised PNG to OptiPNG
    $folder = 'png_optipng'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if($OS == 'Windows')
        $os_cmd = "win_tools\optipng" .' -o 1 -out ' .escapeshellarg($PNGImgfile) .' ' .escapeshellarg($PNGImgfileUnoptimised);
    else
        $os_cmd = "optipng" .' -o 1 -out ' .escapeshellarg($PNGImgfile) .' ' .escapeshellarg($PNGImgfileUnoptimised);
    //echo 'cmd = '.$os_cmd;
    $res = array();
	exec($os_cmd,$res);
    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'OPTIPNG', 'id' => 'OPTIPNG',  'operation' => 'Optimise', 'settings' => 'Optimised OptiPNG', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end png optipng


  //// OPTIMISATION - unoptimised PNG to PNGnq
    $folder = 'png_pngnq'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    $PNGImgfileSaved = $SaveImgfolder . $filename . '-nq8.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if($OS == 'Windows')
        $os_cmd = "win_tools\pngnq-s9" .' -f -d ' . $SaveImgfolder . ' -e .png ' .escapeshellarg($PNGImgfileUnoptimised);
    else
        $os_cmd = "pngnq-s9" .' -f -d ' . $SaveImgfolder . ' -e .png ' .escapeshellarg($PNGImgfileUnoptimised);
    //echo 'cmd = '.$os_cmd;
    $res = array();
	exec($os_cmd,$res);


    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'PNGNQ-S9', 'id' => 'PNGnq',  'operation' => 'Optimise', 'settings' => 'Optimised PNGnq-s9', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end png pngnq

/*
    //// OPTIMISATION - unoptimised PNG to PNGout
    $folder = 'pngout'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);


    $os_cmd = "win_tools\pngout "  .escapeshellarg($PNGImgfileUnoptimised) .' '. escapeshellarg($PNGImgfile);
    //echo 'cmd = '.$os_cmd;
    $res = array();
	exec($os_cmd,$res);
    //get size of file
    $size = filesize($PNGImgfile);

    $pngopt = array('tool' => 'PNGOUT', 'id' => 'PNGOUT',  'operation' => 'Optimise', 'settings' => 'Optimised PNGout', 'object' => $lfn, 'size' => $size);
    //$pngdata[] = array('optimisation' => $pngopt);
*/
    ////////////end png opt pngout


    //// OPTIMISATION  CJPEG - SAVE AS QUALITY 75%
    $folder = 'jpeg_quality_75'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder . $filename . '.jpg';

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $pngopt = array('tool' => 'IJG9a', 'id' => 'q75', 'operation' => 'Save as JPEG', 'settings' => 'quality 75%', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end jpeg opt



   //// OPTIMISATION - CWEBP = SAVE AS WEBP
     $folder = 'webp'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $WEBPImgfile = $SaveImgfolder .  $filename . '.webp';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cwebp ' .escapeshellarg($ImgWithoutMetadata). ' -o ' . escapeshellarg($WEBPImgfile);
    else
        $os_cmd = 'cwebp ' .escapeshellarg($ImgWithoutMetadata). ' -o ' . escapeshellarg($WEBPImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($WEBPImgfile);

    $pngopt = array('tool' => 'cwebp', 'id' => 'WEBP',  'operation' => 'Convert to WEBP', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end webp opt



   //// OPTIMISATION - BGPenc = SAVE AS BGP
    $folder = 'bpg'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $PNGImgfolder.$folder;
     if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $BPGImgfile = $SaveImgfolder .$filename . '.bpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\bpgenc -o ' . escapeshellarg($BPGImgfile). '  ' .escapeshellarg($ImgWithoutMetadata);
    else
        $os_cmd = 'bpgenc -o ' . escapeshellarg($BPGImgfile). '  ' .escapeshellarg($ImgWithoutMetadata);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($BPGImgfile);

    $pngopt = array('tool' => 'bpgenc', 'id' => 'BPG',  'operation' => 'Convert to BPG', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $pngdata[] = array('optimisation' => $pngopt);

    ////////////end bgp opt




    //echo "json: ";
    // tidy-up
    //unlink($decoded_pixmap);
    return $pngdata;

}

function optimiseJPG($savepath,$lfn)
{
    global $OS;
    $path_parts = pathinfo($lfn);
    $filename = $path_parts['filename'];
    if($OS == 'Windows')
        $savepath = str_replace("/","\\",$savepath);

    $folder = '_Optimised_Images';
    error_log( "making folder: ".$savepath.$folder);
    $baseImgfolder =  $savepath.$folder;
    if (!file_exists($baseImgfolder))
    {
        $r = mkdir($baseImgfolder, 0777, true);
        error_log( "making folder result: ".$r);
    }
    $folder = DIRECTORY_SEPARATOR;
    $JPGImgfolder = $baseImgfolder.$folder;
    if (!file_exists($JPGImgfolder))
        mkdir($JPGImgfolder, 0777, true);
    error_log( "optimising JPG: ".$lfn);
    //echo "optimising JPG savepath: ".$JPGImgfolder."</br>";

    // init array to return
    $jpgdata = array();

    //// OPTIMISATION 1 - EXIFTOOOL - REMOVE METADATA
    $folder = 'jpeg_no_metatdata'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder . $filename . '.jpg';
    $ImgWithoutMetadata = $JPGImgfile;

    //echo "optimising JPG as: ".$JPGImgfile."</br>";
    if($OS == 'Windows')
        $os_cmd = 'win_tools\exiftool -all= -o '.escapeshellarg($JPGImgfile). ' ' . escapeshellarg($lfn);
    else
        $os_cmd = './lnx_tools/ExifTool/exiftool -all= -o '.escapeshellarg($JPGImgfile). ' ' . escapeshellarg($lfn);
    error_log('cmd = '.$os_cmd);
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file

    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'Exiftool', 'id' => 'no_metadata',  'operation' => 'Remove Metadata', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);


    //set deccoded jpeg as ppm file
    $decoded_pixmap = $JPGImgfolder.'decoded_' . $filename.'.pnm';

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 75%
    $folder = 'jpeg_quality_75'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder . $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";
    if($OS == 'Windows')
        $os_cmd = 'win_tools\djpeg '.escapeshellarg($ImgWithoutMetadata). ' '. escapeshellarg($decoded_pixmap);
    else
        $os_cmd = 'djpeg '.escapeshellarg($ImgWithoutMetadata). ' > '. escapeshellarg($decoded_pixmap);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q75', 'operation' => 'Save as JPEG', 'settings' => 'quality 75%', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 75% PROGRESSIVE
     $folder = 'jpeg_quality_75_progressive'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder . $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";


    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -progressive ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -progressive ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q75P',  'operation' => 'Save as JPEG ', 'settings' => 'quality 75% Progressive', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 85%
     $folder = 'jpeg_quality_85'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -quality 85 ' .escapeshellarg($decoded_pixmap). ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -quality 85 ' .escapeshellarg($decoded_pixmap). ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q85',  'operation' => 'Save as JPEG', 'settings' => 'quality 85%', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 85% PROGRESSIVE
     $folder = 'jpeg_quality_85_progressive'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -progressive -quality 85 ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -progressive -quality 85 ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q85P',  'operation' => 'Save as JPEG ', 'settings' => 'quality 85% Progressive', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 65%
     $folder = 'jpeg_quality_65'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -quality 65 ' .escapeshellarg($decoded_pixmap). ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -quality 65 ' .escapeshellarg($decoded_pixmap). ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q65',  'operation' => 'Save as JPEG', 'settings' => 'quality 65%', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 65% PROGRESSIVE
     $folder = 'jpeg_quality_65_progressive'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -progressive -quality 65 ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -progressive -quality 65 ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q65P',  'operation' => 'Save as JPEG ', 'settings' => 'quality 65% Progressive', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

        //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 55%
     $folder = 'jpeg_quality_55'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -quality 55 ' .escapeshellarg($decoded_pixmap). ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -quality 55 ' .escapeshellarg($decoded_pixmap). ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q55',  'operation' => 'Save as JPEG', 'settings' => 'quality 55%', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - DJPEG and CJPEG - SAVE AS QUALITY 55% PROGRESSIVE
     $folder = 'jpeg_quality_55_progressive'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize -progressive -quality 55 ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize -progressive -quality 55 ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'IJG9a', 'id' => 'q55P',  'operation' => 'Save as JPEG ', 'settings' => 'quality 55% Progressive', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt

    //// OPTIMISATION - JPEGTRAN
     $folder = 'jpeg_jpegtran'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\jpegtran -copy none -optimize'  . ' -outfile ' . escapeshellarg($JPGImgfile) . ' ' .escapeshellarg($ImgWithoutMetadata);
    else
        $os_cmd = 'jpegtran -copy none -optimize '  . escapeshellarg($ImgWithoutMetadata) . ' > ' .escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;1011
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'jpegTran', 'id' => 'jpegtran',  'operation' => 'JPEG Optimise ', 'settings' => '-copy none -optimize', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt


    //// OPTIMISATION - JPEGTRAN PROGRESSIVE
     $folder = 'jpeg_jpegtran_progressive'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\jpegtran -copy none -optimize -progressive' . ' -outfile ' . escapeshellarg($JPGImgfile) .' ' .escapeshellarg($ImgWithoutMetadata);
    else
        $os_cmd = 'jpegtran -copy none -optimize -progressive ' . escapeshellarg($ImgWithoutMetadata) .' > ' .escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $jpgopt = array('tool' => 'jpegTran', 'id' => 'jpegtranP',  'operation' => 'JPEG Optimise Progressive ', 'settings' => '-copy none -optimize -progressive', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt




    //// OPTIMISATION - CWEBP = SAVE AS WEBP
     $folder = 'webp'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $WEBPImgfile = $SaveImgfolder .  $filename . '.webp';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cwebp ' .escapeshellarg($ImgWithoutMetadata). ' -o ' . escapeshellarg($WEBPImgfile);
    else
        $os_cmd = 'cwebp ' .escapeshellarg($ImgWithoutMetadata). ' -o ' . escapeshellarg($WEBPImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($WEBPImgfile);

    $jpgopt = array('tool' => 'cwebp', 'id' => 'WEBP',  'operation' => 'Convert to WEBP', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end webp


    //// OPTIMISATION - ImageMagick Convert + PNGQuant SAVE AS PNG
    $folder = 'png'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $PNGImgfileUnoptimised = $SaveImgfolder . $filename . '.png';


    if($OS == 'Windows')
        $os_cmd = 'c:\ImageMagick\convert ' . escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($PNGImgfileUnoptimised);
    else
        $os_cmd = 'convert ' . escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($PNGImgfileUnoptimised);
    $res = array();
	exec($os_cmd,$res);
    //print_r($res);
    $size = filesize($PNGImgfileUnoptimised);

    $jpgopt = array('tool' => 'im-convert', 'id' => 'PNG',  'operation' => 'Convert to PNG', 'settings' => 'Unoptimised', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    $folder = 'pngquant'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if($OS == 'Windows')
        $os_cmd = 'win_tools\pngquant ' .escapeshellarg($PNGImgfileUnoptimised) .' -o '.escapeshellarg($PNGImgfile);
    else
        $os_cmd = 'pngquant ' .escapeshellarg($PNGImgfileUnoptimised) .' -o '.escapeshellarg($PNGImgfile);
    //echo 'cmd = '.$os_cmd;
        $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($PNGImgfile);

    $jpgopt = array('tool' => 'pngquant', 'id' => 'PNGQUANT',  'operation' => 'Convert to PNG', 'settings' => 'Optimised PNGQuant', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end jpeg opt



   //// OPTIMISATION - BGPenc = SAVE AS BGP
    $folder = 'bpg'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
     if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $BPGImgfile = $SaveImgfolder .$filename . '.bpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\bpgenc -o ' . escapeshellarg($BPGImgfile). '  ' .escapeshellarg($ImgWithoutMetadata);
    else
        $os_cmd = 'bpgenc -o ' . escapeshellarg($BPGImgfile). '  ' .escapeshellarg($ImgWithoutMetadata);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($BPGImgfile);

    $jpgopt = array('tool' => 'bpgenc', 'id' => 'BPG',  'operation' => 'Convert to BPG', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $jpgdata[] = array('optimisation' => $jpgopt);

    ////////////end bgp opt



    //// OPTIMISATION - TINYJPG via API and CURL - only if API key provided
    $folder = 'jpeg_tinyjpg'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $JPGImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder .  $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    $res = array();

    //$tinyjpgkey = 'LA50CE4VjddBb_F3R8ZJyQMhWC8cpZfI';
    $tinyjpgkey = '';
    //curl to tinyjpg
    if($tinyjpgkey != '' and $tinyjpgkey != '-1')
    {
        curl_TinyJPG($tinyjpgkey, $ImgWithoutMetadata,$JPGImgfile);

      //get size of file
      $size = filesize($JPGImgfile);

      $jpgopt = array('tool' => 'TinyJPG', 'id' => 'TINYJPG',  'operation' => 'Optimise JPEG', 'settings' => 'optimised TinyJPG', 'object' => $lfn, 'size' => $size);
      $jpgdata[] = array('optimisation' => $jpgopt);

      ////////////end jpeg opt
    }

    // tidy-up
    unlink($decoded_pixmap);
    return $jpgdata;

}




function optimiseGIF($savepath, $lfn)
{
    global $OS;
    $path_parts = pathinfo($lfn);
    $filename = $path_parts['filename'];
    if($OS == 'Windows')
    $savepath = str_replace("/","\\",$savepath);

    $folder = '_Optimised_Images';
    $baseImgfolder =  $savepath.$folder;
    if (!file_exists($baseImgfolder))
        mkdir($baseImgfolder, 0777, true);

    $folder = DIRECTORY_SEPARATOR;
    $GIFImgfolder = $baseImgfolder.$folder;
    if (!file_exists($GIFImgfolder))
        mkdir($GIFImgfolder, 0777, true);
    //echo "optimising JPG: ".$lfn."</br>";
    //echo "optimising JPG savepath: ".$JPGImgfolder."</br>";

    // init array to return
    $gifdata = array();
    $gifopt = array();

    //// OPTIMISATION 1 - EXIFTOOOL - REMOVE METADATA
    $folder = 'gif_no_metatdata'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $ImgWithoutMetadata = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    if($OS == 'Windows')
        $os_cmd = 'win_tools\exiftool -all= -o '.escapeshellarg($ImgWithoutMetadata) . ' ' . escapeshellarg($lfn);
    else
        $os_cmd = './lnx_tools/ExifTool/exiftool -all= -o '.escapeshellarg($ImgWithoutMetadata) . ' ' . escapeshellarg($lfn);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'Exiftool', 'id' => 'no_metadata',  'operation' => 'Remove Metadata', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);



    //// OPTIMISATION 2 - ImageMagick Convert GIF without metadata to pixmap
    $SaveImgfolder = $GIFImgfolder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $decoded_pixmap = $SaveImgfolder . $filename . '.ppm';

    if($OS == 'Windows')
        $os_cmd = 'c:\ImageMagick\convert ' . escapeshellarg($ImgWithoutMetadata) . ' '. escapeshellarg($decoded_pixmap) ;
    else
        $os_cmd = 'convert ' . escapeshellarg($ImgWithoutMetadata) . ' '. escapeshellarg($decoded_pixmap) ;
    $res = array();
	exec($os_cmd,$res);
    //print_r($res);


    $folder = 'png'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $PNGImgfileUnoptimised = $SaveImgfolder . $filename . '.png';

    if($OS == 'Windows')
      $os_cmd = 'c:\ImageMagick\convert ' . escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($PNGImgfileUnoptimised);
    else
      $os_cmd = 'convert ' . escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($PNGImgfileUnoptimised);
    $res = array();
	exec($os_cmd,$res);
    //print_r($res);
    $size = filesize($PNGImgfileUnoptimised);

 //// OPTIMISATION 3 - unoptimised PNG to PNGquant
    $folder = 'pngquant'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    $PNGImgfile = $SaveImgfolder . $filename . '.png';
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);

    if($OS == 'Windows')
        $os_cmd = 'win_tools\pngquant ' .escapeshellarg($PNGImgfileUnoptimised) .' -o '.escapeshellarg($PNGImgfile);
    else
        $os_cmd = 'pngquant ' .escapeshellarg($PNGImgfileUnoptimised) .' -o '.escapeshellarg($PNGImgfile);
    //echo 'cmd = '.$os_cmd;
    $res = array();
	exec($os_cmd,$res);
    //get size of file
    $size = filesize($PNGImgfile);

    $gifopt = array('tool' => 'pngquant', 'id' => 'PNGQUANT',  'operation' => 'Optimise', 'settings' => 'Optimised PNGQuant', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);

    ////////////end png opt 3


   //// OPTIMISATION 2 - DJPEG and CJPEG - SAVE AS QUALITY 75%
    $folder = 'jpeg_quality_75'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $JPGImgfile = $SaveImgfolder . $filename . '.jpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cjpeg -optimize ' .escapeshellarg($decoded_pixmap) . ' ' . escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'cjpeg -optimize ' .escapeshellarg($decoded_pixmap) . ' > ' . escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($JPGImgfile);

    $gifopt = array('tool' => 'IJG9a', 'id' => 'q75', 'operation' => 'Save as JPEG', 'settings' => 'quality 75%', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);


//// OPTIMISATION - BGPenc = SAVE AS BGP
    $folder = 'bpg'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
     if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $BPGImgfile = $SaveImgfolder .$filename . '.bpg';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\bpgenc -o ' . escapeshellarg($BPGImgfile). '  ' .escapeshellarg($JPGImgfile);
    else
        $os_cmd = 'bpgenc -o ' . escapeshellarg($BPGImgfile). '  ' .escapeshellarg($JPGImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($BPGImgfile);

    $gifopt = array('tool' => 'bpgenc', 'id' => 'BPG',  'operation' => 'Convert to BPG', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);

    ////////////end bgp opt


    //// OPTIMISATION 6 - CWEBP = SAVE AS WEBP
     $folder = 'webp'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $WEBPImgfile = $SaveImgfolder .  $filename . '.webp';
    //echo "optimising JPG as: ".$JPGImgfile."</br>";

    if($OS == 'Windows')
        $os_cmd = 'win_tools\cwebp ' .escapeshellarg($JPGImgfile). ' -o ' . escapeshellarg($WEBPImgfile);
    else
        $os_cmd = 'cwebp ' .escapeshellarg($JPGImgfile). ' -o ' . escapeshellarg($WEBPImgfile);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($WEBPImgfile);

    $gifopt = array('tool' => 'cwebp', 'id' => 'WEBP',  'operation' => 'Convert to WEBP', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);

    ////////////end webp

    // tidy-up
    unlink($decoded_pixmap);
    return $gifdata;


}

function optimiseGIFAnimation($savepath, $lfn)
{
    global $OS;
    $path_parts = pathinfo($lfn);
    $filename = $path_parts['filename'];
    if($OS == 'Windows')
        $savepath = str_replace("/","\\",$savepath);

    $folder = '_Optimised_Images';
    $baseImgfolder =  $savepath.$folder;
    if (!file_exists($baseImgfolder))
        mkdir($baseImgfolder, 0777, true);

    $folder = DIRECTORY_SEPARATOR;
    $GIFImgfolder = $baseImgfolder.$folder;
    if (!file_exists($GIFImgfolder))
        mkdir($GIFImgfolder, 0777, true);
    //echo "optimising JPG: ".$lfn."</br>";
    //echo "optimising JPG savepath: ".$JPGImgfolder."</br>";

    // init array to return
    $gifdata = array();
    $gifopt = array();

    //// OPTIMISATION 1 - EXIFTOOOL - REMOVE METADATA
    $folder = 'gif_no_metatdata'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $ImgWithoutMetadata = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    if($OS == 'Windows')
        $os_cmd = 'win_tools\exiftool -all= -o '.escapeshellarg($ImgWithoutMetadata) . ' ' . escapeshellarg($lfn);
    else
        $os_cmd = './lnx_tools/ExifTool/exiftool -all= -o '.escapeshellarg($ImgWithoutMetadata) . ' ' . escapeshellarg($lfn);
    //echo 'cmd = '.$os_cmd;
    //exiftool - remove metadata
    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'Exiftool', 'id' => 'no_metadata',  'operation' => 'Remove Metadata', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);


    //// OPTIMISATION - GIFSICLE o1
    $folder = 'gifsicle01'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifsicleanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    if(is64Bits())
        $gifsicletool = "gifsicle64";
    else
        $gifsicletool = "gifsicle32";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $gifsicletool.' -O1 '. escapeshellarg($ImgWithoutMetadata) . ' -o ' .escapeshellarg($gifsicleanim) ;
    else
        $os_cmd = 'gifsicle -O1 '. escapeshellarg($ImgWithoutMetadata) . ' -o ' .escapeshellarg($gifsicleanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'Gifsicle', 'id' => 'gifsicleO1',  'operation' => 'Optimise', 'settings' => 'Level 1', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);

    //// OPTIMISATION - GIFSICLE O2
    $folder = 'gifsicle02'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifsicleanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    if(is64Bits())
        $gifsicletool = "gifsicle64";
    else
        $gifsicletool = "gifsicle32";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $gifsicletool.' -O2 '. escapeshellarg($ImgWithoutMetadata) . ' -o ' .escapeshellarg($gifsicleanim) ;
    else
        $os_cmd = 'gifsicle -O2 '. escapeshellarg($ImgWithoutMetadata) . ' -o ' .escapeshellarg($gifsicleanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'Gifsicle', 'id' => 'gifsicleO2',  'operation' => 'Optimise', 'settings' => 'Level 2', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);


    //// OPTIMISATION - GIFSICLE O3
    $folder = 'gifsicle03'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifsicleanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    if(is64Bits())
        $gifsicletool = "gifsicle64";
    else
        $gifsicletool = "gifsicle32";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $gifsicletool.' -O3 '. escapeshellarg($ImgWithoutMetadata) . ' -o ' .escapeshellarg($gifsicleanim) ;
    else
        $os_cmd = 'gifsicle -O3 '. escapeshellarg($ImgWithoutMetadata) . ' -o ' .escapeshellarg($gifsicleanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'Gifsicle', 'id' => 'gifsicleO3',  'operation' => 'Optimise', 'settings' => 'Level 3', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);


    //// OPTIMISATION - Convert to APNG
    $folder = 'apng'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    $giftool = "gif2apng";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $giftool.' '. escapeshellarg($ImgWithoutMetadata). ' ' .escapeshellarg($gifsicleanim) ;
    else
        $os_cmd = $giftool.' '. escapeshellarg($ImgWithoutMetadata). ' ' .escapeshellarg($gifsicleanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'gif2apng', 'id' => 'gif2apng',  'operation' => 'Convert', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);

    //// OPTIMISATION - Convert to APNG and optimise
    $folder = 'apng'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    $giftool = "gif2apng";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $giftool.' -q '. escapeshellarg($ImgWithoutMetadata). ' ' .escapeshellarg($gifanim) ;
    else
        $os_cmd = $giftool.' -q '. escapeshellarg($ImgWithoutMetadata). ' ' .escapeshellarg($gifanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);


    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'gif2apng', 'id' => 'gif2apngq',  'operation' => 'Convert and Optimise', 'settings' => 'zopfli compression algorithm', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);


    //// OPTIMISATION - Convert to WEBP
    $folder = 'awebp'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    $giftool = "gif2webp";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $giftool.' '. escapeshellarg($ImgWithoutMetadata). ' -o ' .escapeshellarg($gifanim) ;
    else
        $os_cmd = $giftool.' '. escapeshellarg($ImgWithoutMetadata). ' -o ' .escapeshellarg($gifanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'gif2webp', 'id' => 'gif2webp',  'operation' => 'Convert', 'settings' => '', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);


    //// OPTIMISATION - Convert to WEBP and optimise
    $folder = 'awebp'.DIRECTORY_SEPARATOR;
    $SaveImgfolder = $GIFImgfolder.$folder;
    if (!file_exists($SaveImgfolder))
        mkdir($SaveImgfolder, 0777, true);
    $GIFImgfile = $SaveImgfolder . $filename . '.gif';
    $gifanim = $GIFImgfile;

    //echo "optimising GIF as: ".$GIFImgfile."</br>";
    $giftool = "gif2webp";

    if($OS == 'Windows')
        $os_cmd = 'win_tools'.DIRECTORY_SEPARATOR. $giftool.' -q80 '. escapeshellarg($ImgWithoutMetadata). ' -o ' .escapeshellarg($gifanim) ;
    else
        $os_cmd = $giftool.' -q80 '. escapeshellarg($ImgWithoutMetadata). ' -o ' .escapeshellarg($gifanim) ;
    //echo 'cmd = '.$os_cmd;

    $res = array();
	exec($os_cmd,$res);

    //get size of file
    $size = filesize($GIFImgfile);

    $gifopt = array('tool' => 'gif2webp', 'id' => 'gif2webp80',  'operation' => 'Convert and Optimise', 'settings' => 'Quality = 80', 'object' => $lfn, 'size' => $size);
    $gifdata[] = array('optimisation' => $gifopt);



    // tidy-up
    //unlink($decoded_pixmap);
    return $gifdata;
}



function curl_TinyJPG($key, $input,$output){


$request = curl_init();
curl_setopt_array($request, array(
  CURLOPT_URL => "https://api.tinypng.com/shrink",
  CURLOPT_USERPWD => "api:" . $key,
  CURLOPT_POSTFIELDS => file_get_contents($input),
  CURLOPT_BINARYTRANSFER => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HEADER => true,
  /* Uncomment below if you have trouble validating our SSL certificate.
     Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
  CURLOPT_CAINFO => __DIR__ . DIRECTORY_SEPARATOR."win_tools".DIRECTORY_SEPARATOR."cacert.pem",
  CURLOPT_SSL_VERIFYPEER => false
));

$response = curl_exec($request);
if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
  /* Compression was successful, retrieve output from Location header. */
  $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
  foreach (explode("\r\n", $headers) as $header) {
    if (substr($header, 0, 10) === "Location: ") {
      $request = curl_init();
      curl_setopt_array($request, array(
        CURLOPT_URL => substr($header, 10),
        CURLOPT_RETURNTRANSFER => true,
        /* Uncomment below if you have trouble validating our SSL certificate. */
        CURLOPT_CAINFO => __DIR__ . DIRECTORY_SEPARATOR."win_tools".DIRECTORY_SEPARATOR."cacert.pem",
        CURLOPT_SSL_VERIFYPEER => false
      ));
      file_put_contents($output, curl_exec($request));
    }
  }
} else {
    print(curl_error($request));
  /* Something went wrong! */
  print("Compression failed");
}



}

?>

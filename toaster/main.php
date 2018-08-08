<?php
session_start();
session_destroy();
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
$_SESSION['status'] = 'Putting the Page into the Toaster!';
$_SESSION['object'] = '';
$_SESSION['mimetype'] = '';
$_SESSION['imagepath'] = '';
$_SESSION['toastedfile']  = '';
session_write_close();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set("auto_detect_line_endings", true);
ini_set('max_execution_time', '1200');
ini_set('display_errors', 0); // change to 1 for displaying errors on main screen // 0 to disable
error_reporting(E_ALL | E_STRICT);
ini_set('exif.encode_unicode', 'UTF-8');
require 'xhr_getstatus.php';
include 'ps_functions.php';
include 'downloadObject.php';
include 'extract_urls.php';
include 'domain_url_functions.php';
include 'simple_html_dom.php';
include 'tests.php';
include 'imagedecoding.php';
include 'fontdecoding.php';
include 'minify.php';
include 'class.JavaScriptPacker.php';
include 'class.Minify.php';
include 'class.GifDecoder.php';
include 'ttfInfo.class.php';
include 'wpt_functions.php';
include 'getiploc.php';
// set toaster id from parameter if available
if (isset($_REQUEST["tid"]))
	$toasterid = $_REQUEST["tid"];
else
	$toasterid=generateRandomString(); // generate if calling main.php directly without tid
// echo "toaster id:" . $toasterid .PHP_EOL; 
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
if(file_exists($debuglog))
	unlink($debuglog);
if(file_exists($statuslog))
	unlink($statuslog);
file_put_contents($debuglog, "DEBUG LOG started" . PHP_EOL);
ini_set("log_errors", 1);
ini_set("error_log", $debuglog);
// Start the buffering //
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
<meta http-equiv="Cache-Control" content="no-store" />
<meta name="viewport" content="initial-scale=1">
<title>THE WEBPAGE TOASTER</title>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/datatables_customised.css">
<link rel="stylesheet" type="text/css" href="/toaster/toaster_tools/shCore.css">
<link rel="stylesheet" type="text/css" href="/toaster/toaster_tools/shThemeDefault.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/toastertabs.css">
<link rel="stylesheet" type="text/css" href="/toaster/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/toaster/bootstrap/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/waterfall.css" media="screen" charset="utf-8"/>
<link rel="stylesheet" type="text/css" href="/toaster/css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/toasterpage.css">
<link rel="stylesheet" type="text/css" href="/toaster/toaster_tools/synh.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/vis-min.css" />
    <style type="text/css">
        #TPnetwork {
      border: 1px solid lightgray;
        }
    </style>
</head>
<body>
<div class="container-fluid">
<h2 class="pageheader"><a href="/toaster/toaster.htm"><img class="toaster" src="/toaster/images/toaster_tn.png" width="64" height="38" alt="Webpage Toaster">
</a>The Webpage Toaster's Report for <q><span id="pagetitle">page</span></q></h2>
<div id="activitystatus" class="status"></div>
<?php
logStatus("Status","Firing up the Toaster");
logStatus("Toasting",true);
//print_r($_REQUEST);
addInitialRules();
//echo 'This web server runs on '. $OS .'<br/>';
$loadContentFromHAR = false;
if(!empty($_FILES))
{
	if ($_FILES['fileupload']['size']==0)
				{
				//echo "Problem: uploaded file is zero length";
				//print_r( $_FILES );
				//exit;
				}
	else
	{
		// check that uploaded file is a HAR file
	$uploadedHAR = True;
	//echo 'uploading file - temp name = '.basename($_FILES['fileupload']['name']).'<br/>';
	$uploaddir = '/toast/uploads/';
	$uploadfile = $uploaddir . basename($_FILES['fileupload']['name']);
	$uploadedHARFileName = $uploadfile;
	// print_r( $_FILES );
	//echo  $uploadfile.'<pre>';
	if (move_uploaded_file($_FILES['fileupload']['tmp_name'], $uploadfile)) {
	//echo ("Reading objects from HAR File: " . basename($_FILES['fileupload']['name']));
		} else {
			echo "HAR file upload failed<br/>";
		}
	//echo '</pre>';
	// if HAR file uploaded, take URL from it
		//$_REQUEST["url"] = substr(basename($_FILES['userfile']['name']),0,-4);
	}
}
if (isset($_REQUEST["harex"]))
{
	$loadContentFromHAR = true;
//echo ("HAR file exclusive content only". PHP_EOL);
}
$basescheme = 'http';
if (isset($_REQUEST["url"]))
{
	$url = trim($_REQUEST["url"]);	$url = trim($_REQUEST["url"]);
	// detect if url is secure of not and set basescheme
	if(strpos($url,'https') !== false)
		$basescheme = "https";
	else
		$basescheme = "http";
	if(substr($url,0,4) != 'http')
	{
		//prefix the url with http scheme
		$url = $basescheme . '://' . $url;
		//echo "MAIN: URL is valid<br/>";
	}
//echo (__FILE__ . "/" . __FUNCTION__ . "/" . __LINE__ . ": URL is valid; scheme = " . $basescheme.  " for url: " . $url. "<br/>");
    $originalurl = $url;
	if(!filter_var($url, FILTER_VALIDATE_URL))
	  {
	  //echo "URL is not valid";
	  die("URL is not valid");
	  }
	else
	  {
	  //echo "URL is valid";
	  }
	$i = $_REQUEST["ua"];
	$wptbrowser = $i;
	// read user agents from json config file
	// read user agents from json config file
	$jsonStr = file_get_contents("ua-config.json");
	$config = json_decode($jsonStr,true); // if you put json_decode($jsonStr, true), it will convert the json string to associative array
	//echo var_dump($config);

//echo "processing each ua" . PHP_EOL;
	foreach ($config as $key => $value) {
//echo ($key . ": " .$value['ua'] . " " . $value["res"] . " " . $value["uastr"] . PHP_EOL);
		if(strtolower($key) == strtolower($i))
		{
			$ua = $value['ua'];
			$resolution = $value["res"];
			$uastr = $value["uastr"];
			break;
		}
	}
    if($i == "Googlebot" or $i == "Custom")
		echo "User agent: " . $ua."</br>";
if(!isset($resolution))
{
	// set defaults
	$ua = "Mozilla/5.0 (Windows NT 10.0, WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.2526.73 Safari/537.36";
	$width = 1920;
	$height = 1080;
	$uastr = "Chrome_Desktop";
}
else
{
    $reswh = explode("x",$resolution);
    $width = $reswh[0];
	$height = $reswh[1];
}
    //echo $height."<br/>";
	if (isset($_REQUEST["chklinks"]))
	{
		$getlinks = true;
		//echo "get links status true<br/>";
	}
	else
	{
		$getlinks = false;
		//echo "get links status false<br/>";
	}
	// always use 3rd party database
	$dbusage = true;
	//echo "dbusage status true<br/>";
	if (isset($_REQUEST["chkdebug"]) and $_REQUEST["chkdebug"] == true)
	{
		$debug = true;
		echo "header('Content-Type: text/html')";
		//echo "debug status true<br/>";
	}
	else
	{
		$debug = false;
		//echo "debug status false<br/>";
	}
	
	if (isset($_REQUEST["akdebug"]) and $_REQUEST["akdebug"] == true)
	{
		$boolakamaiDebug = true;
		//echo "debug status true<br/>";
	}
	else
	{
		$boolakamaiDebug = false;
		//echo "debug status false<br/>";
	}
//	if (isset($_REQUEST["3pchain"]) and $_REQUEST["3pchain"] == true)
	{
		$thirdpartychain = true;
		//echo "debug status true<br/>";
	}
	// else
	// {
	// 	$thirdpartychain = false;
	// 	//echo "debug status false<br/>";
	// }
	if (isset($_REQUEST["cssimgs"]))
	{
		$cssimgs = true;
		$jsfiles = true;
		//echo "cssimgs status true<br/>";
	}
	else
	{
		$cssimgs = false;
		$jsfiles = false;
		//echo "cssimgs status false<br/>";
	}
	if (isset($_REQUEST["ip"]))
	{
		$getipgeo = $_REQUEST["ip"];
		//echo "get geo IP status: ".$getipgeo."<br/>";
	}
	$getipgeo = "all";
	if (isset($_REQUEST["ipapi"]))
	{
		switch ($_REQUEST["ipapi"])
		{
			case 'dbip':
				$geoIPLookupMethod = 1;
				break;
			case "telize":
				$geoIPLookupMethod = 2;
				break;
			case "freegeoip":
				$geoIPLookupMethod = 3;
				break;
            case "hackertarget":
				$geoIPLookupMethod = 4;
				break;
			default:
				$geoIPLookupMethod = 0; // none - use local maxmind  db
		}
//echo "geo IP API provider: ".$geoIPLookupMethod."<br/>";
	}
	else
	{
		$geoIPLookupMethod = 0; // none - use local maxmind  db
	}
	if (isset($_REQUEST["wbengine"]))
	{
		switch ($_REQUEST["wbengine"])
		{
			case 'none':
				$browserengine = 0;
				break;
			case 'pjs1.9':
				$browserengine = 1;
				break;
			case "pjs2.0":
				$browserengine = 2;
                break;
            case "pjs2.1":
				$browserengine = 5;
				break;
			case "pjs2.5":
				$browserengine = 3;
				break;
            case "sjs0.10":
				$browserengine = 4;
				break;
            case "wpt_private":
				$browserengine = 6;
				break;
			case "wpt_public":
				$browserengine = 8;
				break;
			case "ch_headless":
				$browserengine = 7;
				break;
            default:
            	$browserengine = 5;
				break;
		}
//echo "Browser Engine selected: ".$browserengine . ": " . $_REQUEST["wbengine"] ."<br/>";
	}
	if (isset($_REQUEST["username"]))
		$username = $_REQUEST["username"];
	else
		$username = '';
	if (isset($_REQUEST["password"]))
		$password = $_REQUEST["password"];
	else
		$password = '';
	//echo("username: " .$username. " - password: ". $password);
if(isset($_REQUEST["comment"]))
{
    $runnotes = $_REQUEST["comment"];
//echo "passed comments: " .$runnotes ."<br/>";
    if( $runnotes == 'Enter text here...')
        $runnotes = '';
}

//print_r($_REQUEST);
$chheadlessserver = '';
if(isset($_REQUEST["chremoteurlandport"]))
{
	$chheadlessserver = $_REQUEST["chremoteurlandport"];
	if(strpos($chheadlessserver,"http://") ==false)
		$chheadlessserver = "http://" . $chheadlessserver;
//echo ("headless chrome remote server = " . $chheadlessserver. PHP_EOL);
}
$userip_address = get_client_ip_server();
// get current working dir
//echo "Request uri is: ".$_SERVER['REQUEST_URI']. "<br>";
  $curdir = dirname($_SERVER['REQUEST_URI'])."/";
  if(!isset($_SESSION['userIP']))
  {
	//get current loc
	//$externalContent = file_get_contents('http://checkip.dyndns.com/');
    //preg_match('/Current IP Address: ([\[\]:.[0-9a-fA-F]+)</', $externalContent, $m);
	//$externalIp = $m[1];
    $externalContent = file_get_contents('https://api.ipify.org');
	$externalIp = $externalContent;
//echo ("ipify: my external ip: ".$externalIp."<br/>");
    // check for office location by ip
    switch ($externalIp)
    {
        case '195.95.131.10':
        case '195.95.131.8':
		case '5.148.17.52':
		case '5.148.17.56':
            $geoExtLocStaticMarker = "&markers=color:red%7Clabel:N%7CLeatherhead,Surrey,England";
            $geoMarkerLetter = "N";
            $lat = "51.2994";
            $long = "-0.31";
            $city = "Leatherhead";
            $stateprov = "Surrey";
            $country = "England";
            $geoExtLoc = "Leatherhead, Surrey, England";
			$externalloc = "Leatherhead, Surrey, England";
			$b3pdbPublic = false; // forces use of the internal third party database, not the public one
            break;
        default:
        	list($externalloc,$city,$stateprov,$country,$lat,$long) = lookupLocationforIP($externalIp);
            $geoExtLocStaticMarker = "&markers=color:red%7Clabel:T%7CToaster".$city.",".$stateprov.",".$country;
            $geoExtLoc = $city.", ".$stateprov.", ".$country;
			$geoMarkerLetter = "T"; // T = Toaster Server Location - may be anywher NTS (Node Toaster Server is running)
			
			// $lat = "51.6667";
            // $long = "-0.8333";
            // $city = "Slough";
            // $stateprov = "Berkshire";
            // $country = "England";
            // $geoExtLoc = "Slough, Berkshire, England";
			// $externalloc = "Slough, Berkshire, England";
			$b3pdbPublic = true;

//echo ("toaster server location: ".$externalloc. '; lat='.$lat. '; long='.$long."<br/>");
    }
    $extlatlong = $lat.",".$long;
    $userlat = $lat;
    $userlong = $long;

    $_SESSION['userIP'] = $externalIp;
    $_SESSION['userlat'] = $lat;
    $_SESSION['userlong'] = $long;
    $_SESSION['userloc'] = $externalloc;
    $_SESSION['usergeoExtLocStaticMarker'] = $geoExtLocStaticMarker;
    $_SESSION['usergeoExtLoc'] = $geoExtLoc;
    $_SESSION['usergeoMarkerLetter'] = $geoMarkerLetter;
    $_SESSION['userloccity'] = $city;
    $_SESSION['userlocstateprov'] = $stateprov;
    $_SESSION['userloccountry'] = $country;
    session_write_close();
//echo "setting user loc lat long from lookup: ".$userlat ." " .$userlong . "<br/>";
//echo "setting user ip: ".$_SESSION['userIP']."<br/>";
  }
  else
  {
//echo "getting user loc lat long from session<br/>";
//echo "getting user ip: ".$_SESSION['userIP']."<br/>";
      $externalIp = $_SESSION['userIP'];
      $userlat = $_SESSION['userlat'];
      $userlong = $_SESSION['userlong'];
      $externalloc = $_SESSION['userloc'];
      $extlatlong  = $userlat.",".$userlong;
      $geoExtLocStaticMarker =  $_SESSION['usergeoExtLocStaticMarker'];
      $geoExtLoc = $_SESSION['usergeoExtLoc'];
      $geoMarkerLetter = $_SESSION['usergeoMarkerLetter'];
      $city = $_SESSION['userloccity'];
      $stateprov = $_SESSION['userlocstateprov'];
      $country = $_SESSION['userloccountry'];
//echo "getting user lat: ".$_SESSION['userlat']."<br/>";
//echo "getting user lon: ".$_SESSION['userlong']."<br/>";
  }
//echo ("my location: ".$externalloc. ' lat='.$userlat. '; long='.$userlong."<br/>");
getSelfHosted3PFiles();
//logging of new test
if( strpos($hostname,"gridhost.co.uk") != false)
	$logurl = 'https://www.webpagetoaster.com/toaster/sql/dblogging.php?tid='. $toasterid.'&ip='.$externalIp.'&url='.urlencode($url).'&eng=' .$_REQUEST["wbengine"] .'&hchloc=' . $chheadlessserver.'&uip='.$userip_address;
else
	$logurl = 'http://localhost/toaster/sql/dblogging.php?tid='. $toasterid.'&ip='.$externalIp.'&url='.urlencode($url).'&eng=' .$_REQUEST["wbengine"] .'&hchloc=' . $chheadlessserver;
$f = file_get_contents($logurl);

//echo "Current dir is: ".$curdir."<br/>";
  //echo realpath($curdir.'\getstatus.php')."<br/>";
  //echo realpath( '.' );
	//step 1
	$sourceurlparts = get_SourceURL($url);
	if ($debug == true)
	{
		echo("MAIN:<pre>");
		print_r($sourceurlparts);
		echo("</pre>");
	}
	//full URL path of page requested
	$fullpagepath = $url;
	//if (isset($sourceurlparts["querystring"]))
		//$fullpagepath .= $sourceurlparts["querystring"];
	list($host_domain,$host_domain_path) = getDomainHostFromURL($url,false,"main geo");
	$roothost = $host_domain;
//echo ('roothost '.$roothost.'<br/>');

// echo("host domain: ".$host_domain."<br/>");
// echo("host domain path: ".$host_domain_path."<br/>");
    debug ('roothost ',$roothost);
	$arrayroothost = array($host_domain);
	//getRootDomainAndSubDomains($arrayroothost);
	error_log("Toasting " . $url .  PHP_EOL);
//echo ('roothost after subdomains '.$roothost.'<br/>');
	// root geo IP lookup
	$rootloc  = '';
	$edgeloc = '';
	$edgelat = '';
	$edgelong = '';
	$edgeaddress = '';
	$edgename = '';
	$distance = '';
	$network = '';
	$method = '';
	$service = '';
	$loc = '';
//echo ('getting rootip<br/>');
	if($getipgeo != 'none')
	{
		$rootip = lookupIPforDomain($host_domain.".");
//echo ('returned root ip '.$rootip.' - checking for error<br/>');
        // check for error and return fail if not an IP address
        if($rootip == "error_ip") {
            // session_start();
            // $_SESSION['status'] = 'Looking up Server Domain IP';
            // $_SESSION['object'] = '';
            // $_SESSION['mimetype'] = '';
            // $_SESSION['imagepath'] = '';
            // $_SESSION['toastedfile']  = '';
			// session_write_close();
			logStatus("Status","Looking up Server Domain IP");
            ob_clean();
            //echo 'Error, incorrect host or ip';
            $dbgt=debug_backtrace();
            $data = array('error' => 'true','message' => 'Invalid URL or IP','debuginfo'=> "Main - ".  __FUNCTION__ ." ".__FUNCTION__.' '. __LINE__);
            $json = preg_replace('/[^(\x20-\x7F)]*/','', $data);
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($json);
            exit;
            }
        else {
            //echo 'root IP is Ok<br/>';
        }
        //echo ('ip '.$rootip.'<br/>');
		list($rootloc,$city,$region,$country,$rootlat,$rootlong) = lookupLocationforIP($rootip);
//echo ('ip origin location rootloc '.$rootloc.'<br/>');
//echo ('doing nslookup on '.$host_domain.'<br/>');
		// check for latlong location
		list($ll,$lat,$lon) = isthisAddressLatLong($rootloc);
		if($ll)
			$rootloc = lookupLocationForLatLong($lat,$lon);
		list($edgename,$edgeaddress) = nslookup($host_domain);
		//echo ('edgename '.$edgename.'<br/>');
		//echo ('edgeaddress '.$edgeaddress.'<br/>');
		if($edgeaddress != '')
		{
			// do reverse NS lookup with returned name
			//echo ('doing reverse NS Lookup on '.$edgeaddress.'<br/>');
			list($edgename2,$edgeaddress2) = nslookup($edgeaddress);
			//echo ('edgename 2 '.$edgename2.'<br/>');
			//echo ('edgeaddress 2 '.$edgeaddress2.'<br/>');
			if($edgename2 != '')
			{
				//$edgeloc = $rootloc;
				$edgename = $edgename2;
				$edgeaddress = $edgeaddress2;
			}
			// get location of edge server
//echo("IP geo:". $edgename. " ".$edgeaddress."; edgeloc ". $edgeloc."<br/>");
            list($edgeloc3,$city3,$region3,$country3,$lat3,$long3,$network3,$method3,$service3) = checkdomainforNamedCDNLocation($edgename,$edgeaddress);
            if($edgeloc3 != '')
            {
                $edgeloc = $edgeloc3;
                $city = $city3;
                $region = $region3;
                $country = $country3;
				$edgelat = $lat3;
                $edgelong = $long3;
                if($network3 != '')
                    $network = $network3;
                if($method3 != '')
                    $method =$method3;
                $service = $service3;
            }
		}
        $stripped_edgeloc = preg_replace('/[ ,]+/', '', $edgeloc);
        if($stripped_edgeloc == '')
        {
//echo("edgeloc is blank, setting to loc: ".$loc."<br/>");
            $edgeloc = $rootloc;
            list($latlong,$edgelat,$edgelong) = lookupLatLongForLocation($loc);
        }
		// final checks for updating rootloc based on edgeloc after namedcdn lookup
		list($ll,$lat,$lon) = isthisAddressLatLong($rootloc);
		if($ll === true) // replace location of lat and long with the named location
		{
			$rootloc = lookupLocationForLatLong($lat,$lon);
			
			if($rootloc = '')
				$rootloc = $edgeloc;
//echo ('main named rootloc '.$rootloc.' was latlong, reset to edgeloc<br/>');
		}
		// override rootloc for testing
		//$rootloc = "Oslo,Oslo,NO";
		//echo("root lat: ".$rootlat."<br/>");
		//echo("root long: ".$rootlong."<br/>");
		//echo("edge lat: ".$rootlat."<br/>");
		//echo("edge long: ".$rootlong."<br/>");
		//if($rootlat == '')
		// set root lat long to that of the root edge server lat long
		$rootlatlong = $edgelat.",".$edgelong;
		// root site info lookup - reverse ip
		//lookupReverseIP($host_domain);
		$edgelocnospaces = preg_replace('/\s+/', '', $edgeloc);
		$rootlocnospaces = preg_replace('/\s+/', '', $rootloc);
		//if($edgelocnospaces == $rootlocnospaces)
			//$edgeloc = '';
		//get distance to user's location
		$distance = round(distance($userlat,$userlong,$edgelat,$edgelong,"M"),0);
	}
	else
	{
		$rootlatlong = $rootloc;
		$rootloc = '';
	}
	//echo "Adding BASE domain ".$host_domain."<br/>";
	$arr = array(
	"Domain Name" => $host_domain,
	"Count" => 1,
	"Domain Type" => "Primary",
	"Network" => $network,
	"Service" => $service,
	"Location" => $rootloc,
	"Edge Name" => $edgename,
	"Edge Loc" => $edgeloc,
	"Edge IP" => $edgeaddress,
	"Latitude" => $edgelat,
	"Longitude" => $edgelong,
	"Distance" => $distance,
	"Method" => $method,
	"TotBytes" => 0,
	"Offset" => 99999,  
    "Company" => "",
    "Product"=> "",
	);
	$arrayDomains[] = $arr;
	//echo("init <pre>");
	// print_r($arrayDomains);
	//echo("</pre>");
	// define initial filenames
	$thispagename = pathinfo($url,PATHINFO_BASENAME);
	$thispageext = pathinfo($url,PATHINFO_EXTENSION);
	//error_log(__LINE__ .  'thispagename - '.$thispagename);
	$datedir = date("Y").'/'.date("m").'/'.date("d").'/'.date('H').'/';
	// define system filepaths, adding a trailing slash
	$filepath_domainsavedir = joinFilePaths($filepath_basesavedir,$datedir,$toasterid,$host_domain,$sourceurlparts["dirs"],DIRECTORY_SEPARATOR);
	$filepath_domainsavedirlnx1 = joinFilePaths($filepath_basesavedir,$datedir,$toasterid,DIRECTORY_SEPARATOR);
	$filepath_domainsavedirlnx2 = joinFilePaths($filepath_basesavedir,$datedir,$toasterid,$host_domain,$sourceurlparts["dirs"],DIRECTORY_SEPARATOR);
	// create the save directory before it is needed by a browser engine
	debug ("main: creating basefilepath ",$filepath_domainsavedir);
	createDomainSaveDir($filepath_domainsavedir);
	// set up the linux path to the save dir.

	if($OS != "Windows")
	{
		chmod($filepath_domainsavedirlnx1, 0777);
		chmod($filepath_domainsavedirlnx2, 0777);
		chmod($filepath_domainsavedir, 0777);
		if( strpos($hostname,"gridhost.co.uk") != false)
			$filepath_domainsavedirLnx = "/var/sites/w/webpagetoaster.com/public_html/toast/".$datedir. "/".$toasterid. "/".$host_domain;
		else
		{
			$filepath_domainsavedirLnx = "/usr/share/toast/". $datedir. "/". $toasterid. "/".$host_domain . "/" . $sourceurlparts["dirs"];
			$filepath_domainsavedir = $filepath_domainsavedirLnx;
		}
	}
	$jsfilepath_domainsavedir = joinURLPaths('/toast',$datedir,$toasterid,$host_domain,$sourceurlparts["dirs"]);
	$filepath_domainsaverootdir = joinFilePaths($filepath_basesavedir,$datedir,$toasterid,$host_domain,DIRECTORY_SEPARATOR);
	$localvpath = joinFilePaths($filepath_basesavedir,$datedir,$toasterid,$host_domain,DIRECTORY_SEPARATOR);
	$lc = substr($jsfilepath_domainsavedir,-1);
	if($lc == '/')
    {
        if($browserengine != 6 and $browserengine != 8)
        {
		    $imgname = $filepath_domainsavedir."_screencapture_". $uastr .".png";
			$jsimgname = $jsfilepath_domainsavedir."_screencapture_". $uastr .".png";
			$harname = $filepath_domainsavedir."_harfile_". $uastr .".har";
			$dumpname = $filepath_domainsavedir."_domafter_". $uastr .".txt";
        }
        else
        {
		    $imgname = $filepath_domainsavedir."_screencapture_". $uastr .".png";
			$jsimgname = $jsfilepath_domainsavedir."_screencapture_". $uastr .".png";
			$harname = $filepath_domainsavedir."_harfile_". $uastr .".har";
			$dumpname = $filepath_domainsavedir."_domafter_". $uastr .".txt";
        }
    }
    else
    {
        if($browserengine != 6 and $browserengine != 8)
        {
		    $imgname = $filepath_domainsavedir."/_screencapture_". $uastr .".png";
			$jsimgname = $jsfilepath_domainsavedir."/_screencapture_". $uastr .".png";
			$harname = $filepath_domainsavedir."/_harfile_". $uastr .".har";
			$dumpname = $filepath_domainsavedir."/_domafter_". $uastr .".txt";
        }
        else
        {
		    $imgname = $filepath_domainsavedir."/_screencapture_". $uastr .".png";
			$jsimgname = $jsfilepath_domainsavedir."/_screencapture_". $uastr .".png";
			$harname = $filepath_domainsavedir."/_harfile_". $uastr .".har";
			$dumpname = $filepath_domainsavedir."/_domafter_". $uastr .".txt";
        }
	}
	
    if($OS == 'Windows')
    {
        $browserengineoutput = "out".generateRandomString();
        $wtmp = sys_get_temp_dir();
        //echo("windows tempdir = $wtmp<br/>");
    }
    else
    {
		$browserengineoutput = $filepath_domainsavedirLnx."/out".generateRandomString();
//echo ("linux browserengineoutput = " . $browserengineoutput);
		if(strpos($hostname,"gridhost.co.uk") != false)
		{
				$jsimgname = substr($jsimgname,6);
				$jsimgname = "https://www.webpagetoaster.com/toast".$jsimgname;
//echo "jsimagename: " . $jsimgname  . "<br/>";
		}
	}
	debug("browserengineoutput" , $browserengineoutput);

	//echo("save path dir = $filepath_domainsavedir<br/>");
	//echo("save screenshot = $imgname<br/>");
	// delete the previous debug log if it exists
	if(file_exists($filepath_domainsavedir.DIRECTORY_SEPARATOR."debug.txt"))
    	@unlink($filepath_domainsavedir.DIRECTORY_SEPARATOR."debug.txt");
	//echo("save path img =$imgname<br/>");
	//echo("save path rootdir=$filepath_domainsavedir<br/>");
	//echo("vpath=$localvpath<br/>");
	if ($debug == true)
	{
		echo ("<br/>"."Host domain: ".$host_domain."<br/>");
		echo ("<br/>"."Host domain path: ".$host_domain_path."<br/>");
		echo ("Dirs ".$sourceurlparts["dirs"]."<br/>");
		echo ("File ".$thispagename."<br/>");
		echo ("Ext ".$thispageext."<br/>");
		echo ("Path ".$sourceurlparts["path"]."<br/>");
		echo ("Port ".$sourceurlparts["port"]."<br/>");
		echo ("Querystring ".$sourceurlparts["querystring"]."<br/>");
	}
	//echo("pagename: ".$thispagename."<br/>");
	//echo("Host domain: ".$host_domain."<br/>");
	if ($thispagename == $host_domain or $thispagename == $sourceurlparts["dirs"])
	{
		//echo("resetting page name<br/>");
		$thispagename = '';
	}
	// add a trailing slash if there is no filename or querystring and one is not present already
	$pathlc = substr($sourceurlparts["path"], -1);
	//echo("URL path lastchar: $pathlc<br/>");
	//echo("URL parts: ".$sourceurlparts["dirs"]."<br/>");
	//echo("URL pagename: $thispagename<br/>");
	//echo("host: $host_domain<br/>");
	$boolNeedsEndingSlash = false;
	//if($sourceurlparts["querystring"] =='' and $thispagename == '' and $pathlc != '/')
	//{
	//	$boolNeedsEndingSlash = true;
	//	$url = $url."/";
	//	//echo("Adding / to url<br/>");
	//}
	// add a filename if missing
	$thispage_raw = $thispagename;
	// sanitise querystring from pagename
	if (strpos($thispagename,"?") !== false)
	{
	    //error_log(__LINE__ .  'query string found');
		$thispagename = sanitize_file_name($thispagename,false,false);
        //error_log(__LINE__ .  '  sanitised thispagename - '.$thispagename);
	}
	if ($thispagename == '')
	{
		debug("setting filenname","index");
		$thispagename = "index";
	}
	if ($thispageext != 'htm' and $thispageext != 'html')
	{
		debug("adding extension","htm");
		$thispageext = '.htm';
	}
	else
		$thispageext = '';
	$thispagenameext = $thispagename.$thispageext;
	//echo("pagename & ext: ".$thispagenameext."<br/>");
	$fullurlpath = $url;
	debug("Main: Initial URL path",$fullurlpath);
	if (strpos($fullurlpath,"?") > 0)
	{
		$parname = substr($fullurlpath,0,strpos($fullurlpath,"?"));
		debug("Main: URL PATH without querystring",$parname);
	}
	$filepathname_rootobject_headersandbody = $filepath_domainsavedir."pageinfo_".$thispagenameext.'.txt';
//echo("root rohab " . $filepathname_rootobject_headersandbody."<br/>");
	$localfilename = $filepath_domainsavedir.$thispagenameext;
//echo("root lfn " . $localfilename."<br/>");
	$toastedfilepathname = $filepath_domainsavedir."toasted_".$thispagenameext;
    $lastchar = substr($jsfilepath_domainsavedir, -1);
	if($lastchar == "/")
	{
		$toastedwebname = $jsfilepath_domainsavedir."toasted_".urlencode($thispagenameext);
		$toastedfilepathname = $filepath_domainsavedir."toasted_".$thispagenameext;
	}
	else
		{
		$toastedwebname = $jsfilepath_domainsavedir."/toasted_".urlencode($thispagenameext);
		$toastedfilepathname = $filepath_domainsavedir."/toasted_".$thispagenameext;
		}
debug ("toast filepathname: " .$toastedfilepathname,1);
// echo ("toast filepathname: " .$toastedfilepathname."<br/>");
// echo ("toast webname: " .$toastedwebname."<br/>");
// session_start();
// 	$_SESSION['status'] = 'Processing hostname';
// 	session_write_close();
	logStatus("Status","Processing hostname");
	if( strpos($hostname,"gridhost.co.uk") != false)
	{
		$toastedwebname = 'https://www.webpagetoaster.com/toast/'. $datedir . $toasterid. "/".$host_domain . "/" . $sourceurlparts["dirs"] . "/toasted_".urlencode($thispagenameext);
//echo ($toastedwebname);
debug ("toasted webname: " .$toastedwebname ,1);
	}
	$harfile = $jsfilepath_domainsavedir.$thispagenameext.".har";
	//echo "saved as: ".$harfile."<br/>";
	//echo "Analysing website at domain: ".$host_domain."<br/>";
	//echo "Website path: ".$sourceurlparts["dirs"]."; page: ".$thispage_raw."<br/>";
	//echo "URL: ".$url."<br/>";
	//echo "Analysis saved for webpage: ".$toastedfilepathname."<br/>";
	//echo "saved as: ".$localfilename."<br/>";
	//echo "Files saved to directory: " .$filepath_domainsavedir."<br>";
	//echo "Root Page saved as: ".$filepathname_rootobject_headersandbody."<br/>";
//	createDomainSaveDir($filepath_domainsavedir);
    //   session_start();
    //   $_SESSION['status'] = 'Retrieving Root Object';
    //   $_SESSION['toastedfile'] = $toastedwebname; //$toastedfilepathname;
	//   session_write_close();
	  logStatus("Status","Retrieving Root Object");
	  logStatus("toastedfile",$toastedwebname);
	// get some basic information about the root object from a headers lookup
	list($curl_info,$curlresponseheaders) = readURLandSaveToFilePath($url,$filepathname_rootobject_headersandbody);
	$TimeOfResponse = get_Datetime_Now();
	// NEW HEADER ANALYSIS FOR ADDING THE ROOT OBJECT TO THE OBJECT TABLE
	list($protocol,$responsecode,$age,$cachecontrol,$cachecontrolPrivate,$cachecontrolPublic,$cachecontrolNoCache,$cachecontrolNoStore,$cachecontrolMaxAge,$cachecontrolSMaxAge,$cachecontrolNoTransform,$cachecontrolMustRevalidate,$cachecontrolProxyRevalidate,$connection,$contentencoding,$contentlength,$contenttype,$date,$etag,$expires,$keepalive,$lastmodifieddate,$pragma,$server,$setcookie,$upgrade,$vary,$via,$xcache,$xservedby,$xpx,$xedgelocation,$cfray,$xcdngeo,$xcdn) = extractHeadersFromCurlResponse($curlresponseheaders); //curlresponseheaders
	$mimetype = trim($contenttype);
	// get final URL from download
	$url_page = getURLFromCURL();
	list($ttime,$rdtime,$contime,$dnstime,$dstime,$dsstime) = get_timings();
	list($sc,$hdrs,$hdrlength,$contentlength,$contentsizedownloaded,$redirect_count) = examine_headers($filepathname_rootobject_headersandbody,$curlresponseheaders,$curl_info);
	$totbytesdownloaded += $contentsizedownloaded;
	$rootbytesdownloaded = $contentsizedownloaded;
	debug("Main: root file http compression status",$contentencoding);
	$gzpos = strpos($contentencoding,"gzip");
    $HTTPCompressionType = $contentencoding;
	//echo("Main: root file gzip status: ".$contentencoding." at ". $gzpos);
	if ($gzpos !== false)
	{
		addTestResult("4.1","4","Serve the root object with HTTP compression (GZIP) ","Pass");
		$boolHTTPCompressRoot = true;
	}
	else
	{
		if (strpos(trim($contentencoding),"deflate") !== false)
		{
			addTestResult("4.1","4","Serve the root object with HTTP compression (Deflate)","Pass");
			$boolHTTPCompressRoot = true;
		}
		else
		{
			if (strpos(trim($contentencoding),"br") !== false)
            {
                addTestResult("4.1","4","Serve the root object with HTTP compression (Brotli)","Pass");
                $boolHTTPCompressRoot = true;
            }
            else
            {
                addTestResult("4.1","4","Serve the root object with HTTP compression","Fail");
                $boolHTTPCompressRoot = false;
            }
		}
	}
	// identify http protocol scheme
	if($protocol == "2")
	{
		$boolHTTP2Root = true;
		addStatToFileListAnalysis("HTTP/2","Enabled","Protocol","pass");
	}
	else
	{
		$boolHTTP2Root = false;
		addStatToFileListAnalysis("HTTP/" . $protocol ,"","Protocol","info");
	}
	
	debug("Main: Root Object Mime-type",$mimetype);
	//echo("Main: Root Headers before checking for redirecctions<br/>");
	//var_dump($curlresponseheaders);
	//debug("Main: Root Headers" ,"<pre>".$curlresponseheaders."</pre>");
	//echo("<pre>");
	//print_r($curlresponseheaders);
	//echo("</pre>");
	$bodylen = extract_headersandbody($filepathname_rootobject_headersandbody,$localfilename,$curlresponseheaders);
    if($bodylen == 0)
    {
    //   session_start();
    //   $_SESSION['imagepath'] = '';
    //   $_SESSION['status']  = 'Aborting toasting - Page failed';
	//   session_write_close();
	  logStatus("Status","Aborting toasting - Page could not be reached");
	  logStatus('imagepath','');
	  logStatus('Toasting',false);
      // Get the content that is in the buffer and put it in your file //
      //file_put_contents('', ob_get_contents());
    }
	//echo("Main: Root body len = ".$bodylen."<br/>");
	// override content length if = -1
	//if($contentlength = -1)
	//	$contentlength = $contentsizedownloaded;
	debug("Main: Root Extract Redirects from Headers" ,"");
	//echo("Main: Root Extract Redirects from Headers<br/>");
	// extract redirects
	if($redirect_count > 0)
	{
		list($redirs,$newurlpath,$finalhdrs) = extract_redirects($redirect_count,$curlresponseheaders, $url,true);
		//echo("MAIN redirs: new urlpath: " .$newurlpath."<br/>");
		// sanitise querystring from pagename
		if (strpos($thispagenameext,"?") > 0)
		{
			$thispagenameext = sanitize_file_name($thispagenameext,false,false);
		}
		$localfilename = $filepath_domainsavedir.$thispagenameext;
        //echo("redir root lfn " . $localfilename);
error_log("MAIN redirs: new localfile: " .$localfilename);
        if(file_exists($localfilename))
        {
            $body = utf8_decode (file_get_contents_utf8($localfilename));
        }
        $retbodylen = strlen($body);
        //error_log("1 returned body length = ". $retbodylen);
   	}
	else
	{
	  //error_log("MAIN no redirs: new localfile: " .$localfilename);
        if(file_exists($localfilename))
        {
            //error_log("MAIN no redirs: new localfile EXISTS BEFORE BODY: " .$localfilename);
            $body = file_get_contents($localfilename);
            //error_log("MAIN no redirs: new localfile EXISTS AFTER BODY: " .$localfilename);
            //error_log("MAIN no redirs: body: " .$body);
            $retbodylen = strlen($body);
            //error_log("2  returned body lengh = ". $retbodylen);
        }
		$redirs = array ();
	}
//echo("<br/>MAIN localfile: " .$localfilename."<br/>");
	if($redirect_count == 0)
	{
		debug("Main: Root Add URL data to array" ,"");
		$arr = array(
		"id" => 1,
		"Object type" => "HTML",
		"Object name" => $url,
		"Header length" => $hdrlength,
		"Content length" => $bodylen,
		"HTTP Status" => strval(intval($sc)),
		"GZIP Status" => $contentencoding,
		"Mime type" => $mimetype,
		"Extension" => "",
		"Combined Files" => ""
		);
		$arrayOfObjects[] = $arr;
		$retbodylen = strlen($body);
            //error_log("3 returned body lengh = ". $retbodylen);
		//new
		// update array
		$arr = array(
		"Object type" => 'HTML',
		"Object source" => $url,
		"Object file" => $localfilename,
		"Object parent" => '',
		"Mime type" => $mimetype,
		"Domain" => $host_domain,
		"Domain ref" => 'Primary',
		"HTTP status" => strval(intval($sc)),
		"File extension" => '',
		"CSS ref" => '',
		"Header size" => $hdrlength,
		"Content length transmitted" => $contentlength,
		"Content size downloaded" => $contentsizedownloaded,
		"Compression" => $contentencoding,
		"Content size compressed" => '',
		"Content size uncompressed" => $bodylen,
		"Content size minified uncompressed" => '',
		"Content size minified compressed" => '',
		"Combined files" => 0,
		"JS defer" => '',
		"JS async" => '',
        "JS docwrite" => '',
		"Image type" => '',
		"Image encoding" => '',
        "Image responsive" => '',
		"Image display size" => '',
		"Image actual size" => '',
		"Metadata bytes" => '',
		"EXIF bytes" => '',
		"APP12 bytes" => '',
		"IPTC bytes" => '',
		"XMP bytes" => '',
		"Comment" => '',
		"Comment bytes" => '',
		"ICC colour profile bytes" => '',
		"Colour type" => '',
		"Colour depth" => '',
		"Interlace" => '',
		"Est. quality" => '',
		"Photoshop quality" => '',
		"Chroma subsampling" => '',
		"Animation" => '',
        "Font name" => '',
		"hdrs_Server" => $server,
		"hdrs_Protocol" => $protocol,
		"hdrs_responsecode" => $responsecode,
		"hdrs_date" => $date,
		"hdrs_lastmodifieddate" => $lastmodifieddate,
		"hdrs_age" => $age,
		"hdrs_cachecontrol" => $cachecontrol,
		"hdrs_cachecontrolPrivate" => $cachecontrolPrivate,
		"hdrs_cachecontrolPublic" => $cachecontrolPublic,
		"hdrs_cachecontrolMaxAge" => $cachecontrolMaxAge,
		"hdrs_cachecontrolSMaxAge" => $cachecontrolSMaxAge,
		"hdrs_cachecontrolNoCache" => $cachecontrolNoCache,
		"hdrs_cachecontrolNoStore" => $cachecontrolNoStore,
		"hdrs_cachecontrolNoTransform" => $cachecontrolNoTransform,
		"hdrs_cachecontrolMustRevalidate" => $cachecontrolMustRevalidate,
		"hdrs_cachecontrolProxyRevalidate" => $cachecontrolProxyRevalidate,
		"hdrs_connection" => $connection,
		"hdrs_contentencoding" => $contentencoding,
		"hdrs_contentlength" => $contentlength,
		"hdrs_expires" => $expires,
		"hdrs_etag" => $etag,
		"hdrs_keepalive" => $keepalive,
		"hdrs_pragma" => $pragma,
		"hdrs_setcookie" => $setcookie,
		"hdrs_upgrade" => $upgrade,
		"hdrs_vary" => $vary,
		"hdrs_via" => $via,
		"hdrs_xservedby" => $xservedby,
		"hdrs_xcache" => $xcache,
		"hdrs_xpx" => $xpx,
		"hdrs_xedgelocation" => $xedgelocation,
		"hdrs_cfray" => $cfray,
		"hdrs_xcdngeo" => $xcdngeo,
        "hdrs_xcdn" => $xcdn,
		"response_datetime" => $TimeOfResponse,
   		"file_section" => '',
   		"file_timing" => '',
		"offsetDuration" => '',
		"ttfbMS" => '',
		"downloadDuration" => '',
		"allMS" => '',
		"allStartMS" => '',
		"allEndMS" => '',
		"cacheSeconds" => '',
		);
		addUpdatePageObject($arr);
		$retbodylen = strlen($body);
            //error_log("4 returned body lengh = ". $retbodylen);
//echo ("Main: saving the headers against the root object: no redirs<br/>");
		if($wptHAR == false and $chhHAR == false)
		{
//echo ("(main): bypass saving the root headers against the object due to HAR processed: $url<br/>");
			addPageHeaders(html_entity_decode(htmlentities($url)),$hdrs);
		}
		//echo("<pre>");
		//print_r($hdrs);
		//echo("</pre>");
		//update page's domain data using header data
		list($locupdated,$edgelochdr) = UpdateDomainLocationFromHeader($url,$xservedby,$xpx,$xedgelocation,$server,$cfray,$xcdngeo,$xcdn,$via,$xcache,"main");
		if($locupdated == true)
		{
			$edgeloc = $edgelochdr;
		}
		// echo ('main header rootloc '.$rootloc.'<br/>');
		// echo ('main header edgeloc '.$edgeloc.'<br/>');
		//	echo("Main: ROOT loc updated: ".$edgeloc."<br/>");
		// final checks for updating rootloc based on edgeloc after cdn header lookup
		list($ll,$lat,$lon) = isthisAddressLatLong($rootloc);
		if($ll === true)
		{
			$rootloc = lookupLocationForLatLong($lat,$lon);
			//echo ('main header rootloc '.$rootloc.' is blank or a latlong, resetting to edgeloc<br/>');
			$rootloc = $edgeloc;
		}
	} // end if no redirections
	else
	{
//echo "redir";
		if (strpos($newurlpath,'noscript') == false)
		{
			$url = $newurlpath;
			$fullurlpath = $newurlpath;
			$arrayroothost = array($roothost);
//echo("Main: ROOT URL path REDIRECTED: ".$fullurlpath."<br/><br/>");
			debug("Main Root Redir: Initial URL path",$fullurlpath);
//echo("Main Root Redir: Initial URL path: " . $fullurlpath);
		// detect if new url is secure of not and reset basescheme
		if(strpos($url,'https') !== false)
			$basescheme = "https";
		else
			$basescheme = "http";
//echo (__FILE__ . "/" . __FUNCTION__ . "/" . __LINE__ . ": MAIN root redirection: new URL; scheme = " . $basescheme.  " for url: " . $url. "<br/>");
		}
	}
	$siteurl = $url;
	if($server != '')
		addStatToFileListAnalysis($server,"","Server");
?>
<script type="text/javascript" language="JavaScript" src="/toaster/js/pageinfo.js"></script>
<?php
    // session_start();
    // $_SESSION['status'] = 'Parsing the DOM';
	// session_write_close();
	logStatus('Status','Parsing the DOM');
	$retbodylen = strlen($body);
// echo("main, source DOM - returned body length = ". $retbodylen."<br>");
// echo $body; 
	// parse root object into DOM
	$returned = parseRootBodytoDOM($body, 'main, source DOM');
	if(!empty($returned))
	{
        $html = $returned;
		$pagetitle = getTitleOfPage();
	}
	else
		$pagetitle = '';
//echo $pagetitle."<br/>";
	$redir_metadata = false;
	$redir_javascript = false;
	// check for meta data refresh in the HTML, and parse the DOM again if the refresh meta tag exists
	if(intval($sc) == 200)
	{
		$redir_metadata = getMetaTags($fullurlpath);
		//if($redir_metadata != true)
			//$redir_javascript = getJSRedir($fullurlpath);
	}
	//echo ("meta redir state: " .$redir_metadata);
	//echo ("javascript redir state: " .$redir_javascript);
	if($redir_metadata == true)
	{
	    //echo ("main processing Meta redir<br/>");
		// redo the DOM
		// parse root object into DOM
		$returned = parseRootBodytoDOM($body,'main, redir_metadata');
//echo ( "returned body of html '" . "'");
		if($returned != '')
		{
			$html = $returned;
			$pagetitle = getTitleOfPage();
//echo ($pagetitle."<br/>");
		}
		else
			{
			$html = '';
			$pagetitle = '';
//echo ("empty html - no page title");
			}
		$boolRootRedirect = true;
		$redir_type  = "Meta";
		addTestResult("11.1","11","Root object redirects","Fail");
	}
	if($redir_javascript == true )
	{
	    //echo ("main processing JS redir<br/>");
		// redo the DOM
		// parse root object into DOM
	    $returned = parseRootBodytoDOM($body,'main, redir_js');
        if(!empty($returned))
            $html = $returned;
		$pagetitle = getTitleOfPage();
		//echo $pagetitle."<br/>";
		$boolRootRedirect = true;
		$redir_type  = "JavaScript";
		addTestResult("11.1","11","Root object redirects","Fail");
	}
    getListOfInlineStyleLinks();
    debug ("ROOT REDIRECTION CHAIN<br>",$rootredirchain);
		// detect if new url is secure of not and reset basescheme
		if(strpos($url,'https') !== false)
			$basescheme = "https";
		else
			$basescheme = "http";
//echo (__FILE__ . "/" . __FUNCTION__ . "/" . __LINE__ . ": MAIN rootURL; scheme = " . $basescheme.  " for url: " . $url. "<br/>");
    // set up optimisation folder for minifying the HTML
    $folder = '_Optimised_HTML'.DIRECTORY_SEPARATOR;
	$baseTextfolder =  $filepath_domainsavedir.$folder;
    if (!file_exists($baseTextfolder))
		mkdir($baseTextfolder, 0777, true);
    $path_parts = pathinfo($localfilename);
    $optfilename =  $baseTextfolder.$path_parts['filename'].".min.htm";
    //echo"saving as : ".$optfilename."<br/>";
	// minify and compress html file
	$min = new Minify_HTML($html,'xhtml');
	$packed = $min->process();
	$out = $optfilename;
	file_put_contents($out, $packed);
	$minifiedLen = strlen($packed);
	//echo($localfilename. " minification: length: $uncompressedLen --> compressed HTML: $minifiedLen <br/>");
	//lookup the object id
	list($id,$lfn) = lookupPageObject($url);
	// gzip the original file
	list($dt,$ot,$zippedfilename,$origsize,$gzipsize,$savingbytes,$savingpct) = CompressFile("HTML",$id,$localfilename,true);
	// now gzip the minified file
	list($dt,$ot,$minfile,$minorigsize,$mingzipsize,$minsavingbytes,$minsavingpct) = CompressFile("HTML",$id,$out,false);
    $arr = array(
		"Object type" => 'HTML',
		"Object source" => $url,
		"Content size compressed" => $gzipsize,
		"Content size uncompressed" => $bodylen,
		"Content size minified uncompressed" => $minifiedLen,
		"Content size minified compressed" => $mingzipsize,
	);
	// update min and gzip stats
    debug ("MAIN: updating min and gzip stats","");
	addUpdatePageObject($arr);
	// get list of style ids and classes
    debug ("MAIN: getStyleIDandClasses",$url);
	getStyleIDandClasess($url);
    //session_start();
    //$_SESSION['status'] = 'Checking for uploaded HAR file';
    //session_write_close();
 debug ("MAIN: checking for uploaded HAR","");
    if(($uploadedHAR == false or $loadContentFromHAR == false) and $browserengine != 6 and $browserengine != 8 and $browserengine != 7)
    {
    	//getCSSJSOrdering();
    	getListOfStyleLinks("before DOM load");
    	getListOfScriptLinks();
    	getListOfImages();
    	getListOfListImages();
    }
	// extra links
	if($getlinks === true)
	{
		getListOfLinks();
		getListOfImageLinks();
	}
	// analyse file
 debug ("MAIN: Analyse file",$localfilename);
	//echo "calling function from library with ". $filepathname_rootobject_headersandbody;
	$contentanalysis = analyse_file($localfilename);
 debug ("MAIN: get source files as arrays","");
	// get sources files as arrays
	$ListOfCSSFiles = getArrayOfStylesheets();
	$ListOfJSFiles = getArrayOfScriptFiles();
	$ListOfImageFiles = getArrayOfImageFiles();
	$ListOf3PCSSFiles = getArrayOf3PStylesheets();
	$ListOf3PJSFiles = getArrayOf3PScriptFiles();
	$ListOf3PImageFiles = getArrayOf3PImageFiles();
	$ListOfImageLinks = getArrayOfImageLinks();
	$ListOfLinks = getArrayOfLinks();
	$ListOfObjects = getArrayOfObjects();
	$ListOfErrors = getArrayOfErrors();
 debug ("MAIN: starting browser engine","");
 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//                 PHANTOM JS / Slimer JS / WPT
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// parse Javascript via PhantomJS and get a list of the page files, add to the object list if not already found
	// outputs the username that owns the running php/httpd process
	// (on a system with the "whoami" executable in the path)
	$res = array();
	$har = '';
	//exec('win_tools\phantomjs --web-security=no netlogresponses.js '. $url,$res); // responses only
	//echo("PhantomJS sniffing network traffic with ".$ua."<br/>");
	//exec('win_tools\phantomjs --cookies-file=c:\temp\cookies.txt --proxy=127.0.0.1:8888 --ignore-ssl-errors=true --ssl-protocol=tlsv1 netsniff.js '. $originalurl . " " . $height . " " . $width . " " . $imgname . " '" . $ua ."'" ." ".$username. " ". $password ,$res); //responses & sniff
    //echo("invoking JavaScript Engine via option ".2." for ". $originalurl."</br>");
    // set url for browser engine - if redirections detected, use latest URl else use the original
    if($boolRootRedirect == true)
    {
        $urlforbrowserengine = $url;
        switch($page_redir_total)
        {
            case 0:
                //addStatToFileListAnalysis($page_redir_total,"Redirection","on Root");
                break;
            case 1:
                addStatToFileListAnalysis($page_redir_total,"Redirection","on Root","warn");
                break;
            default:
                addStatToFileListAnalysis($page_redir_total,"Redirections","on Root","fail");
                break;
        }
    }
    else
    {
        $urlforbrowserengine = $originalurl;
    }
debug('MAIN: Running browser engine number: ', "'" . $browserengine . "'<br/>");
    $uar =  $ua; //str_replace(' ', '%20', $ua);
    switch ($browserengine)
    {
		case 0:
			$browserEngineVer = 'Toaster';
			// session_start();
			// $_SESSION['status'] = 'Running basic test only';
			// session_write_close();
			logStatus('Status','Running basic test only');
			break;
        case 1:
            $browserEngineVer = 'Webkit (PhantomJS v1.9.8)';
            // session_start();
            // $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            if($OS == "Windows")
                exec('win_tools\phantomjs --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            else
                exec('./lnx_tools/phantomjs --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            break;
        case 2:
            $browserEngineVer = 'Webkit (PhantomJS v2.0.0)';
            // session_start();
            // $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            if($OS == "Windows")
                exec('win_tools\phantomjs2 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            else
                exec('./lnx_tools/phantomjs2 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            break;
        case 3:
            // $browserEngineVer = 'Gecko (SlimerJS v0.9.5)';
            // session_start();
            // $_SESSION['status'] = 'Running ' . $browserEngineVer;
            // session_write_close();
            // if($OS == "Windows")
            //     exec('win_tools\slimerjs.bat js\netsniff_sjs.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            // else
            //     exec('./lnx_tools/slimerjs.bat js/netsniff_sjs.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
			// break;
			$browserEngineVer = 'Webkit (PhantomJS v2.5)';
            // session_start();
            // $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            if($OS == "Windows")
                exec('win_tools\phantomjs2.5 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
			else
			{
				$cmd = './lnx_tools/phantomjs2.5 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password . " 2>&1";
				error_log($cmd);
				
				exec('whoami',$who);
				error_log("whoami: ".implode($who));
				exec($cmd,$res); //responses & sniff
				error_log(implode($res));
			}
			break;
        case 4:
            $browserEngineVer = 'Gecko (SlimerJS v0.10)';
            // session_start();
            // $_SESSION['status'] = 'Running test on  ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            if($OS == "Windows")
                exec('win_tools\slimerjs-0.10.3\slimerjs.bat js\netsniff_sjs.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            else
                exec('./lnx_tools/slimerjs.bat js/netsniff_sjs.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
            break;
        case 5:
            $browserEngineVer = 'Webkit (PhantomJS v2.1.1)';
            // session_start();
            // $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            if($OS == "Windows")
                exec('win_tools\phantomjs2.1 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
			else
			{
				$cmd = './lnx_tools/phantomjs2.1 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ". $browserengineoutput." ".$username. " ". $password . " 2>&1";
				error_log($cmd);
				
				exec('whoami',$who);
				error_log("whoami: ".implode($who));
				exec($cmd,$res); //responses & sniff
//error_log(implode($res));
			}
			break;
		case 6: // private instance WPT
            $browserEngineVer = 'WebpageTest Private Instance';
            // session_start();
            // $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            $urlenc = urlencode($urlforbrowserengine);
            $testId = "";
            list ($testId,$jsonResult,$summaryCSV,$detailCSV ) = submitWPTTest($wptbrowser,$urlenc,$uar,$width,$height,$username,$password);
			$statusCode = 0;
			$timeoutcounter = 0;
            while (intval($statusCode) != 200 and $timeoutcounter < 600) {
				$statusCode = checkWPTTestStatus($testId);
				$timeoutcounter++;
                sleep(1);
             }
            // get testresults as HAR
			$har = getWPTHAR($testId);
//echo $har;
            $wptHAR = true;
            $uploadedHAR = false;
            $harfile = "WebpageTest Test No. ". $testId;
            getWPTImagePath($testId,$imgname);
			break;

		case 8: // public WPT
			$browserEngineVer = 'WebpageTest Public';
			// session_start();
			// $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
			$urlenc = urlencode($urlforbrowserengine);
			$testId = "";
			list ($testId,$jsonResult,$summaryCSV,$detailCSV ) = submitWPTTest($wptbrowser,$urlenc,$uar,$width,$height,$username,$password);
			$statusCode = 0;
			$timeoutcounter = 0;
			while (intval($statusCode) != 200 and $timeoutcounter < 600) {
				$statusCode = checkWPTTestStatus($testId);
				$timeoutcounter++;
				sleep(1);
			}
			// get testresults as HAR
			$har = getWPTHAR($testId);
//echo $har;
			$wptHAR = true;
			$uploadedHAR = false;
			$harfile = "WebpageTest Test No. ". $testId;
			getWPTImagePath($testId,$imgname);
			break;
			
		case 7:
			$browserEngineVer = 'Headless Chrome';
			// session_start();
			// $_SESSION['status'] = 'Running test on ' . $browserEngineVer;
			// session_write_close();
			logStatus('Status','Running test on ' . $browserEngineVer);
            $urlenc = urlencode($urlforbrowserengine);
			$testId = "";
//echo ('Running test on ' . $browserEngineVer . " against a Toaster NodeJS server: " . $chheadlessserver."<br/>");
				// chrome remote
			if(strpos($chheadlessserver,"http") === false)
				$chheadlessserver = 'http://' .$chheadlessserver;

			// remote headless chrome
			if($OS == "Windows")
			{
//echo ("Windows running headless chrome on remote server " . $chheadlessserver. PHP_EOL);
				//echo("headless chrome command path: " . $chheadlessserver . "?tid=". $toasterid . "&content=0&url=".$urlforbrowserengine."&action=test<br/>");
				$page = file_get_contents( $chheadlessserver . "?tid=". $toasterid . "&content=0&url=".urlencode($urlforbrowserengine)."&action=test");

//echo($chheadlessserver . "?tid=". $toasterid . "&action=gethar". "<br/>");
				$har = file_get_contents($chheadlessserver . "?tid=". $toasterid . "&action=gethar");
//echo "saving har file to " . $harname. "<br/>";
				$res = file_put_contents($harname,$har);
//echo "file save result: " . $res. "<br/>";
				$pagedom = file($chheadlessserver . "?tid=". $toasterid . "&action=getdom");
				file_put_contents($dumpname,$pagedom);
				$pagescreenshot = file_get_contents($chheadlessserver . "?tid=". $toasterid . "&action=getimg");
				file_put_contents($imgname,$pagescreenshot);
			}
			else
			{
//echo ("Windows running headless chrome on remote server " . $chheadlessserver. PHP_EOL);
				//echo("headless chrome command path: " . $chheadlessserver . "?tid=". $toasterid . "&content=0&url=".$urlforbrowserengine."&action=test<br/>");
				$page = file_get_contents( $chheadlessserver . "?tid=". $toasterid . "&content=0&url=".$urlforbrowserengine."&action=test");
	
				$har = file_get_contents($chheadlessserver . "?tid=". $toasterid . "&action=gethar");
				file_put_contents($harname,$har);
				$pagedom = file_get_contents($chheadlessserver . "?tid=". $toasterid . "&action=getdom");
				file_put_contents($dumpname,$pagedom);
				$pagescreenshot = file_get_contents($chheadlessserver . "?tid=". $toasterid . "&action=getimg");
				file_put_contents($imgname,$pagescreenshot);
			}
			// get the prepared files and save them
// echo "saving returned headless chrome files to:<br/>";
// echo "saving returned harfile: " . $harname. "<br/>";
// echo "saving returned domdump: " . $dumpname. "<br/>";
// echo "saving returned screenshot: " . $imgname. "<br/>";

			$uploadedHARFileName = $harname;
			$wptHAR = false;
			$chhHAR = true;
			$uploadedHAR = true;
			break;
		case 8: // public wpt - to do - get har file

	} // end switch



//echo("continuing after browser engine<br/>");
    // save thumnbnail of $imgname
	$fileimage = str_replace("\\\\", "\\",$imgname);
//echo "saving thumbnail from " . $fileimage . " to " . $filepath_domainsavedirLnx . "<br/>";
    if($OS == 'Windows')
        $os_cmd = 'c:\ImageMagick\mogrify -format gif -path ' . $filepath_domainsavedir . ' -thumbnail 100x100 ' . escapeshellarg($fileimage);
	else
	{
		if(strpos($hostname,"gridhost.co.uk") != false)
			$os_cmd = 'mogrify -format gif -path ' . $filepath_domainsavedirLnx . ' -thumbnail 100x100 ' . escapeshellarg($fileimage);
		else
			$os_cmd = 'mogrify -format gif -path ' . $filepath_domainsavedir . ' -thumbnail 100x100 ' . escapeshellarg($fileimage);
	}
//echo ($os_cmd."</br>");
    $eres = array();
	exec($os_cmd,$eres);
    // get pjs and slimerjs cookies and postdata
    if($browserengine < 6 and $browserengine != 0) // not WPT or headless chrome
    {
        //get phantom cookie file and add to cookie jar
        if($OS == "Windows" )
		{
            $pjsckfile = 'tmp/'.$browserengineoutput."CK";
			$pjspdfile = 'tmp/'.$browserengineoutput."PD";
		}
		else
		{
            $pjsckfile = $browserengineoutput."CK";
			$pjspdfile = $browserengineoutput."PD";
		}
// if(file_exists($pjsckfile) == true)
//    echo("pjs cookie file: ".$pjsckfile." found<br/>");
// else
//    echo("pjs cookie file: ".$pjsckfile." not found<br/>");
// if(file_exists($pjspdfile) == true)
// 	echo("pjs parameter file: ".$pjspdfile." found<br/>");
// else
// 	echo("pjs parameter file: ".$pjspdfile." not found<br/>");
        
		$pjscookiesJSON = file_get_contents($pjsckfile);
        transferPJScookies($pjscookiesJSON);

        $pjspostdataJSON = file_get_contents($pjspdfile);
        //transferPJSpostdata($pjspostdataJSON);

        //. ">".$localfilename.".har"
// echo("sniff result<pre>");
// print_r($res);
// echo("</pre>");
		$dumpname = $browserengineoutput;
		// delete temp files returned from PhantomJS
		@unlink($localfilename.".har");
		@unlink($pjsckfile);
		@unlink($pjspdfile);
    	$jsonstr = implode($res);
    	debug("<br/><?php echo $browserEngineVer;?>: processing additional resources for",$url);
       //echo("Phantom JS har file<pre>");
       //var_dump($res);
       //echo("</pre>");
       //echo implode($res);
//echo("setting har file<br/>");
        $har = implode($res);
	}
	else
		$pjspostdataJSON = '';

//echo ($browserEngineVer . ": processing additional resources for url: " . $url . "<br.>");

// prepare to process a har file if present
$rstime_ms = 0;
$domCompletetime_ms = 0;
$rstime_sec = 0;
$onload_ms = 0;
$docTime_ms = 0;
if($har != '')
	{
		// add onContentLoad as PhantomJS fails to add it
		//$har = str_replace('"onLoad"','"onContentLoad": -1,    "onLoad"',$har);
		// remove ant HAR formatting errors before "log":
		$logpos = strpos($har,'{');
		//echo ("logpos = " . $logpos. "<br/>");
		if ($logpos >1)
			$har = substr($har,$logpos);
		// delete PhantomJS errors
		$har = str_replace("'unsafe-inline'",'',$har);
		$har = str_replace("'unsafe-eval'",'',$har);
		$har = str_replace("'self'",'',$har);
		//$har = str_replace('"','\"',$har);
		$har = preg_replace( "/\s+/", " ", $har );
		$har = str_replace(chr(34),'"',$har);
		$endtext = "Unsafe JavaScript attempt";
		$firstchar = substr($har,0,1);
		//echo ("logpos = " . $logpos. "<br/>");
		if ($firstchar != '{')
		{
			//echo ("har log adding initial {<br/>");
			$har = '{'.$har;
		}
		$logendpos = strpos($har,'}'.$endtext);
		//echo ("logendpos = " . $logendpos. "<br/>");
		if ( $logendpos > 0)
			$har = substr($har,0,$logendpos+1);
		$harjson = json_decode($har,true);

		//save HAR file from browser engine
		file_put_contents($localfilename.".har",$har,FILE_APPEND);
		$harfilelength = strlen($har);
	//echo("reading har file of length . " . $harfilelength . "<br/>");
		// session_start();
		// $_SESSION['imagepath'] = $jsimgname;
		// $_SESSION['status'] = 'Reading HAR file';
		// session_write_close();
		logStatus('imagepath',$jsimgname);
		logStatus('Status','Processing HAR from ' . $browserEngineVer);
		// override the HAR file if not for WPT or headless chrome
		if($uploadedHAR == True and $wptHAR == false and $chhHAR == false)
		{
			$har = file_get_contents($uploadedHARFileName);
	//echo($uploadedHARFileName . ' - overriding HAR file from engine<br/>');
			$harjson = json_decode($har,true);
			$harfile =$uploadedHARFileName;
		}
	//echo("main processing har file<br/>");
	//debug HAR JSON string
	// echo("har<code><pre>");
	// print_r($har);
	// echo("</pre></code><br/>");
		// escape the HAR json for onward processing by the waterfall chart
		// process the HAR
		// 1) identify objects and add to the list of page objects if unknown
		// 2) identify key timing points and score info and save to scorearray
		$scoreArray = array ();
		$har = json_esc($har);
		$domLoadEnd = 0;
		foreach ($harjson['log'] as $logtype => $logval)
		{
	//echo("har<code> ".$logtype . " ". $logval->text."</code> <br/>");
			switch ($logtype)
				{
					case 'pages':
						foreach ($logval as $key => $value) {
	//echo("HAR PAGES log level 1: ".$key . " ". $value);
						$evalue =json_encode($value);
	//echo "startedDateTime: ".$value['startedDateTime']."<br/>";
	//echo "render: ".$value['_render']."<br/>";
						// add render time to array
						switch($browserengine)
						{
							case 6: // WPT private
							case 8: // WPT public
								$rst = $value['_render'];
								$onLoad = $value['_docTime'];
								$domLoadStart = $value['_domContentLoadedEventStart'];
								$domLoadEnd = $value['_domContentLoadedEventEnd'];
								$doct = $value['_fullyLoaded'];
								$statname = "Render Start Time";
								break;
							case 7: //headless chrome
								$rst = $value['pageTimings']['_firstMeaningfulPaint'];
								$domLoadStart = $value['pageTimings']['onContentLoad'];
								$onLoad = $value['pageTimings']['onLoad'];
								$doct = $value['pageTimings']['_totalTime'];
								$statname = "First Meaningful Paint";
								break;
							default:
								$rst = 0;
								$doct = 0;
								$onLoad = 0;
						} // end switch $browserengine
						$arr = array("renderms"=> $rst);
						$rstime_ms = $rst;
						$domCompletetime_ms = $domLoadEnd;
						$rstime_sec = $rstime_ms/1000;
						$onload_ms = $onLoad;
						$docTime_ms = $doct;
						$scoreArray[] = $arr;
						addStatToFileListAnalysis(number_format($rstime_sec,3), "Seconds", $statname, "info");
						break;
					} // end for each page
					break;
				case 'entries':
					$pjsObjCnt = 0;
					$pjsObjCntExisting = 0;
					$pjsObjCntNew = 0;
					$pjsredircount = 0;
					$starturl = '';
					$endurl = '';
					foreach ($logval as $key => $value) {
	//echo("log level 1: ".$key . " ". $value."<br/>");
						$evalue =json_encode($value);
						//echo "<br/>entry $key<br/>";
						//echo "startedDateTime: ".$value['startedDateTime']."<br/>";
						//echo "time: ".$value['time']."<br/>";
						//echo "request array: ".implode($value['request'])."<br/>";
						//echo "response array: ".implode($value['response'])."<br/>";
						//echo "cache array: ".implode($value['cache'])."<br/>";
						//echo "timings array: ".implode($value['timings'])."<br/>";
						$request =$value['request'];
						$ObjURL = $request['url'];
						$response =$value['response'];
						$httpstatus = $response['status'];
						$pageref = $value['pageref'];
						$requestheaders = $request['headers'];
						$responseheaders = $response['headers'];
						$timings = $value['timings'];
						// get WPT values for timings
						switch($browserengine)
						{
							case 6: // WPT private
							case 8: // WPT public
								$requestStartMS = $value['_ttfb_start'];
								$ttfbMS = $value['_ttfb_ms'];
								$contentDownloadMS = $value['_download_ms'];
								$allMS = $value['_all_ms'];
								$allStartMS = $value['_all_start'];
								$allEndMS = $value['_all_end'];
								$cacheTime = $value['_cache_time'];
								break;
							case 7: // headless chrome
								$requestStartMS = 0;
								$ttfbMS = 0;
								$contentDownloadMS = 0;
								$allMS = 0;
								$allStartMS = 0;
								$allEndMS = 0;
								$cacheTime = 0;
								break;
	//echo ($ObjURL . "; allms =  " . $allMS . ";<br/>");
							default:
								$requestStartMS = 0;
								$ttfbMS = 0;
								$contentDownloadMS = 0;
								$allMS = 0;
								$allStartMS = 0;
								$allEndMS = 0;
								$cacheTime = 0;
						} // end switch $browserengine
						foreach ($requestheaders as $reqhdrkey => $reqhdrvalue) {
//echo ("req hdr key ". $reqhdrkey. " ".implode($reqhdrvalue)."<br/>");
							if($reqhdrvalue['name'] == 'Referer')
							{
								$referer = $reqhdrvalue['value'];
								continue;
							}
							else
								$referer = '';
							if($reqhdrvalue['name'] == 'Content-Type')
								$mimetype = $reqhdrvalue['value'];
						}
						$rsphdrset = '';						
//echo "getting object headers from har file for: " . $ObjURL . "<br/>";
						foreach ($responseheaders as $rsphdrkey => $rsphdrvalue) {
//echo ("rsp hdr key ". $rsphdrkey. " ".$rsphdrvalue['name'] . ": " . $rsphdrvalue['value']."<br/>");
							//$hdrsplit = str_split($rsphdrvalue,":");
						
							$rsphdrset = $rsphdrset . $rsphdrvalue['name'] . ": " . $rsphdrvalue['value'] . PHP_EOL;
						}

					//	addPageHeaders($ObjURL,$rsphdrset);


						//echo("<pre>Response");
						//print_r($response);
						//echo("</pre><br/>");
						//echo "request url: ".$ObjURL."<br/>";
						//echo "response status code: ".$httpstatus."<br/>";
						//echo "pageref: ".$pageref."<br/>";
						//if ($referer != '')
						//   echo "referer: ".$referer."<br/>";
						if($referer == '')
							$parent = $pageref;
						else
							$parent = $referer;
						if($httpstatus >= 300 and $httpstatus <400)
							{
								$pjsredircount += 1;
						//		if($pjsredircount > 1)
							//		continue;
							}
						if($pjsredircount >= 1)
						{
							$starturl = '';
							$pjsredircount = 0;
						//	continue;
						}
						// weed out duff URL references
						if($ObjURL == 'http:/')
							continue;
	//echo("HAR processing: url: ".$ObjURL." stage: ".$pjsStage."<br/>");
						//echo("HAR REDIRECTION: ".$ObjURL. " = ".$httpstatus."<br/>");
						debug("HARfile object original: ",$ObjURL);
						$urlencoded = strpos($ObjURL,'&amp;');
						if($urlencoded != false)
						{
							$ObjURL = html_entity_decode($ObjURL);
							debug("HARfile object decoded : ",$ObjURL);
						}
						//echo "HARfile object: $ObjURL<br/>";
						// need to urlencode the querystring
						$posQM = strpos($ObjURL,"?");
						if ($posQM > 0)
						{
							$ObjURL = substr($ObjURL,0,$posQM)."?".html_entity_decode(htmlentities(substr($ObjURL,$posQM +1)));
							debug("HARfile with Querystring (htmlentities): ",$ObjURL);
							//echo("HARfile with Querystring (htmlentities): ".$ObjURL."<br/>");
						}
						else
						{
							//echo("HARfile w/o Querystring: ".$ObjURL."<br/>");
						}
						$ObjURL = htmlentities($ObjURL);
						$ObjMimeType = trim($mimetype);
						$ct = explode(";",$ObjMimeType);
						$contenttype = trim($ct[0]);
						if($contenttype != $ObjMimeType)
						{
							//echo ("HARfile content type full: ". $ObjMimeType."<br/>");
							//echo ("HARfile content type 1st: ". $contenttype."<br/>");
							$ObjMimeType = $contenttype;
						}
						//echo ("<br/>HARfile object $ObjURL : ". $obj['contentType'] ." ; content type = ". $ObjMimeType."<br/>");
						// only proceed with the END stage
						$objType = 'TBD';
						switch (trim($ObjMimeType))
							{
							case "text/html":
								$objType = 'HTML';
								break;
							case "text/css":
								$objType = 'StyleSheet';
								break;
							case "application/javascript":
							case "application/x-javascript":
							case "text/javascript":
							case "text/x-js":
								$objType = 'JavaScript';
								break;
							case "text/plain":
							case "text/xml":
							case "application/xml":
							case "application/json":
								$objType = 'Data';
								break;
							case "image/jpeg":
							case "image/gif":
							case "image/png":
							case "image/webp":
								$objType = 'Image';
								break;
							case "image/svg+xml":
								$objType = $ObjMimeType;
								break;
							case "application/x-font-woff":
							case "application/x-font-ttf":
							case "application/x-font-truetype":
							case "application/x-font-opentype":
							case "application/vnd.ms-fontobject":
							case "application/font-sfnt":
								$objType = "Font";
								break;
							default:
								$objType = $ObjMimeType;
								break;
							}
						$pjsObjCnt += 1;
						// HAR add object
	//echo("Checking HARfile object against root: $ObjURL --- $url<br/>");
						if($ObjURL != $url."/" )
						//if($ObjURL != $url and $ObjURL != $url."/" )
						{
							list($id,$lfn) = lookupPageObject($ObjURL);
							//echo("HARfile object lookup: $ObjURL; ".$id."; localfilename: ".$lfn. "<br/>");
							if (!is_numeric($id))
							{
	//echo ("HARfile new object: ".$ObjURL.": " . $ObjMimeType  ."<br/>");
								$pjsObjCntNew += 1;
								list($hd, $hp) = getDomainHostFromURL($ObjURL,false,"main processing objects from" . $browserengine);
							}
							else
							{
	//echo ("HARfile existing object ($id): ".$ObjURL.": " . $ObjMimeType  ."<br/>");
								$pjsObjCntExisting += 1;
								// UPDATE HARFile stats
								// update existing object timing
								switch ($browserengine)
								{
									case 6: // wpt private
									case 8: // wpt public
										$requestStartMS = $value['_ttfb_start'];
										$ttfbMS = $value['_ttfb_ms'];
										$contentDownloadMS = $value['_download_ms'];
										$allMS = $value['_all_ms'];
										$allStartMS = $value['_all_start'];
										$allEndMS = $value['_all_end'];
										$cacheTime = $value['_cache_time'];
										break;
	//echo ("update " . $ObjURL . "; allms =  " . $allMS . ";<br/>");
									case 7: // headless chrome
										$requestStartMS = $value['timings']['blocked'];
										$ttfbMS = $value['timings']['wait'];
										$contentDownloadMS = $value['timings']['receive'];
										$allMS = 0;
										$allStartMS = 0;
										$allEndMS = 0;
										$cacheTime = 0;
										break;
									default:
										$requestStartMS = 0;
										$ttfbMS = 0;
										$contentDownloadMS = 0;
										$allMS = 0;
										$allStartMS = 0;
										$allEndMS = 0;
										$cacheTime = 0;
								} // end switch $browserengine
								$arr = array(
								"Object source" => $ObjURL,
								"offsetDuration" => $requestStartMS,
								"ttfbMS" => $ttfbMS,
								"downloadDuration" => $contentDownloadMS,
								"allMS" => $allMS,
								"allStartMS" => $allStartMS,
								"allEndMS" => $allEndMS,
								"cacheSeconds" => $cacheTime,
								);
								addUpdatePageObject($arr);
								continue;
							}
							//test if this file is on a CDN
							$testdomain = $hd;
							//echo("HARfile checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
							if ($roothost == $hd)
							{
								debug("External '.$browserEngineVer.' FILE", "'".$ObjURL."'");
								$domref = 'Primary';
							}
							else
							{
								$domsrc = IsThisDomainaCDNofTheRootDomain($roothost,$testdomain);
								switch($domsrc)
								{
									case 'CDN':
									case 'cdn':
										debug("CDN External File", "'".$ObjURL."'");
										$domref = 'CDN';
										break;
									case 'Shard':
									case 'shard':
										debug("Shard External File d", "'".$ObjURL."'");
										$domref = 'Shard';
										break;
									default:
										debug("3rd party External File d", "'".$ObjURL."'");
										$domref = '3P';
								}
							}
							//$ObjMimeType = $obj['url'];
							// check for Base64 file - image, font, something else
							$qspos = strpos($ObjURL,'?');
							if($qspos != 0)
								$nonqs = strtolower(substr($ObjURL,0,$qspos));
							else
								$nonqs =  $ObjURL;
							//echo("HARfile checking data: in filename: ".$nonqs."<br/>");
							$datafound = strpos($nonqs,"data:");
							//echo("HARfile checking data: pos = ".$datafound."<br/>");
							if( $datafound !== false)			{
								debug("<br/>PROCESSING DATA","BASE64");
								debug("PJS Embedded Data","Base 64");
								$hd = '';
								$domref = "Embedded";
								//echo("HARfile B64 local filename: ".$lfn."<br/>"); // lfn will be derived when object is added
							}
							// strip $ObjURL - last char if /
	// strip ending slash if present
							$lastchar = substr($ObjURL, -1);
							if($lastchar == '/')
							{
								$ObjURL = substr($ObjURL, 0, -1);
							}
							// add FILE From HARfile to array
								$arr = array(
								"Object type" => $objType,
								"Object source" => $ObjURL,
								"Object file" => '',
								"Object parent" => $parent,
								"Mime type" => $ObjMimeType,
								"Domain" => $hd,
								"Domain ref" => $domref,
								"HTTP status" => '',
								"File extension" => '',
								"CSS ref" => '',
								"Header size" => '',
								"Content length transmitted" => 0,
								"Content size downloaded" => 0,
								"Compression" => '',
								"Content size compressed" => '',
								"Content size uncompressed" => '',
								"Content size minified uncompressed" => '',
								"Content size minified compressed" => '',
								"Combined files" => 0,
								"JS defer" => '',
								"JS async" => '',
								"JS docwrite" => '',
								"Image type" => '',
								"Image encoding" => '',
								"Image responsive" => '',
								"Image display size" => '',
								"Image actual size" => '',
								"Metadata bytes" => '',
								"EXIF bytes" => '',
								"APP12 bytes" => '',
								"IPTC bytes" => '',
								"XMP bytes" => '',
								"Comment" => '',
								"Comment bytes" => '',
								"ICC colour profile bytes" => '',
								"Colour type" => '',
								"Colour depth" => '',
								"Interlace" => '',
								"Est. quality" => '',
								"Photoshop quality" => '',
								"Chroma subsampling" => '',
								"Animation" => '',
								"Font name" => '',
								"hdrs_Server" => '',
								"hdrs_Protocol" => '',
								"hdrs_responsecode" => '',
								"hdrs_age" => '',
								"hdrs_date" => '',
								"hdrs_lastmodifieddate" => '',
								"hdrs_cachecontrol" => '',
								"hdrs_cachecontrolPrivate" => '',
								"hdrs_cachecontrolPublic" => '',
								"hdrs_cachecontrolMaxAge" => '',
								"hdrs_cachecontrolSMaxAge" => '',
								"hdrs_cachecontrolNoCache" => '',
								"hdrs_cachecontrolNoStore" => '',
								"hdrs_cachecontrolNoTransform" => '',
								"hdrs_cachecontrolMustRevalidate" => '',
								"hdrs_cachecontrolProxyRevalidate" => '',
								"hdrs_connection" => '',
								"hdrs_contentencoding" => '',
								"hdrs_contentlength" => '',
								"hdrs_expires" => '',
								"hdrs_etag" => '',
								"hdrs_keepalive" => '',
								"hdrs_pragma" => '',
								"hdrs_setcookie" => '',
								"hdrs_upgrade" => '',
								"hdrs_vary" => '',
								"hdrs_via" => '',
								"hdrs_xservedby" => '',
								"hdrs_xcache" => '',
								"hdrs_xpx" => '',
								"hdrs_xedgelocation" => '',
								"hdrs_cfray" => '',
								"hdrs_xcdngeo" => '',
								"hdrs_xcdn" => '',
								"response_datetime" => '',
								"file_section" => '',
								"file_timing" => '',
								"offsetDuration" => $requestStartMS,
								"ttfbMS" => $ttfbMS,
								"downloadDuration" => $contentDownloadMS,
								"allMS" => $allMS,
								"allStartMS" => $allStartMS,
								"allEndMS" => $allEndMS,
								"cacheSeconds" => $cacheTime,
								);
	//echo ("adding HARfile object: " . $ObjURL."<br/>");
								addUpdatePageObject($arr);
	//							$debugTiming = $ObjURL . "; allms: " . $value['_all_ms']. "<br/>";
	//echo($debugTiming);
								}
						else
						{ // 
							// strip $ObjURL - last char if /
	// strip ending slash if present
							$lastchar = substr($ObjURL, -1);
							if($lastchar == '/')
							{
								$ObjURL = substr($ObjURL, 0, -1);
							}

							if(isset($value['_all_ms']))
								@$debugTiming = $ObjURL . "; allms: " . $value['_all_ms']. "<br/>";
								else
								if(isset($value['time']))
									@$debugTiming = $ObjURL . "; allms: " . $value['time']. "<br/>";
							//echo("root: " . $debugTiming);
							// update object timings if they exist (in WPT test)
							if($browserengine == 6 or $browserengine == 8)
							{				
											// WPTTIMING
								//echo("Found HARfile object : $ObjURL --- $url<br/>");

								$arr = array(
								"Object source" => $ObjURL,
								"offsetDuration" => $value['_ttfb_start'],							
								"ttfbMS" => $value['_ttfb_ms'],
								"downloadDuration" => $value['_download_ms'],
								"allMS" => $value['_all_ms'],
								"allStartMS" => $value['_all_start'],
								"allEndMS" => $value['_all_end'],
								"cacheSeconds" => $value['_cache_time'],
								);
								addUpdatePageObject($arr);
							}
						}
						addPageHeaders($ObjURL,$rsphdrset);
					} // end for each entry in the entries array (in switch statement)
				break;
				default:
			} // end switch
		} // end for har log reading
		//var_dump($harjson);
	// end reading of urls from har from PhantomJS/SlimerJS/WPT
		// echo ("HARfile object: total: ".$pjsObjCnt."<br/>");
		// echo ("HARfile object: new: ".$pjsObjCntNew."<br/>");
		// echo ("HARfile object: existing: ".$pjsObjCntExisting."<br/>");

		@diagnostics($browserEngineVer ." objects: total=".$pjsObjCnt,"new=".$pjsObjCntNew,"existing=".$pjsObjCntExisting);
	} // end processing a har file if present



    // session_start();
    // $_SESSION['status'] = 'Processing Objects from '. $browserEngineVer;
	// session_write_close();
	logStatus('Status','Processing Objects from '. $browserEngineVer);
	// get css and js order for unmodified HTML source
//echo (__FUNCTION__.' '. __LINE__." Main: 1st parseRootBodytoDOM being called to check html content: "."html is not empty");
    getCSSJSOrdering('source');
    // extra processing of HTML document for modified DOM - use output from PhantomJS or SlimerJS
//echo("domdump processing from browser engine ver = " . $browserEngineVer."</br>");
	if($browserengine != 6 and $browserengine != 8) // don't run for WPT
	{
		//$modrootfilepath = "output.txt";
		if($browserengine != 7)
		{
//echo "looking for dump in: " . realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$browserengineoutput;
			$dumpname = realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$browserengineoutput;
			if(file_exists($dumpname))
				$modrootfilecontent = file_get_contents(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$browserengineoutput);
			else
				$modrootfilecontent = '';
		}
		else
		{
			if(file_exists($dumpname))
				$modrootfilecontent = file_get_contents($dumpname);
			else
				$modrootfilecontent = '';
			$browserengineoutput = $dumpname;
//echo "looking for chrome remote dump in: " .$dumpname;
		}


		// echo "<pre><xmp>";
		// echo "modified HTML:".$modrootfilecontent;
		// echo "</xmp></pre>";
		if(!empty($modrootfilecontent))
		{
			// echo "<xmp>";
			// echo "modified HTML:".$modrootfilecontent;
			// echo "</xmp>";
		// session_start();
		// $_SESSION['status'] = 'Parsing the updated DOM';
		// session_write_close();
		logStatus('Status','Parsing the updated DOM');
		$returned = parseRootBodytoDOM($modrootfilecontent,'main, updated DOM');
			if(!empty($returned))
				$html = $returned;
		}
	}

    if(!empty($html))
    {
     debug("Main: 2nd parseRootBodytoDOM being called to check html content: ","html is not empty");
//echo (__FUNCTION__.' '. __LINE__." Main: 2nd parseRootBodytoDOM being called to check html content: "."html is not empty");
	if(($uploadedHAR == false or $browserengine == 7 or $loadContentFromHAR == false) and $browserengine != 6 and $browserengine != 8)
      {
        getListOfStyleLinks("after DOM load");
    	getListOfScriptLinks();
      }
      // get css and js order for unmodified HTML source
      getCSSJSOrdering('injected');
      getListOfInlineStyleLinks();
       if($getlinks === true)
    	{
    		getListOfLinks();
    		getListOfImageLinks();
    	}
      // look through list and get more HTML files
      if($uploadedHAR == false)
      {
        getListofHTMLFiles();
      }
    }
    else
    {
        debug("Main: 2nd parseRootBodytoDOM skippped: checking html content: ","empty html body after");
//echo(__FUNCTION__.' '. __LINE__." Main: 2nd parseRootBodytoDOM skippped: checking html content: "."empty html body after");
    }
    if($uploadedHAR == false)
    {
        // session_start();
        // $_SESSION['status'] = 'Checking for Responsive Images';
		// session_write_close();
		logStatus('Status','Checking for Responsive Images');
        // look for responsive imaages
        getListOfResponsiveImages('img','srcset','sizes');
        getListOfResponsiveImages('img','data-srcset','data-sizes');
        getListOfResponsiveImages('picture,source','srcset','media');
        getListOfResponsiveImages('picture->img','srcset','media');
        getListOfHTML5Elements("audio source","src"); // checked ok
        getListOfHTML5Elements("embed","src"); // checked ok
        getListOfHTML5Elements("track","src");
        getListOfHTML5Elements("video source","src"); // checked ok
        getListOfHTML5Elements("object","data"); // checked ok
        getListOfHTML5Elements("svg","data");
	}
//error_log("Array of objects before download:");
//error_log( print_R($arrayPageObjects,TRUE) );
	// download objects
	downloadAllObjects($ListOfObjects);
// debug - display new objects and conversion to json
// error_log("Array of objects after download:");
// error_log( print_R($arrayPageObjects,TRUE) );
// error_log("json objects after utf8 conversion:");
// $arrayPageObjects = utf8_converter($arrayPageObjects);
// $jsonres = json_encode($arrayPageObjects, JSON_UNESCAPED_UNICODE);
// error_log( print_R($jsonres,TRUE) );

	// update redirections - source - destination	
    setRedirTargets();
    // session_start();
    // $_SESSION['status'] = 'Generating Page Stats.';
	// session_write_close();
	logStatus('Status','Generating Page Stats.');
	updateFileStats();
	detectJSLibs();
    if($thirdpartychain == true)
    {
        session_start();
        $_SESSION['status'] = 'Identifying Third Party Call Chain';
		session_write_close();
		logStatus('Status','Identifying Third Party Call Chain');
    	Identify3Pchains();
    }
    //	copyFiles();
    // session_start();
    // $_SESSION['status'] = 'Copying Image Files';
	// session_write_close();
	logStatus('Status','Saving Image Files For Optimisation Comparison');
    copyImageFilesToFolders();
	//
	//echo("getCompressionFileStats<br/>");
	// get stats
    // session_start();
    // $_SESSION['status'] = 'Calculating GZIP Savings';
	// session_write_close();
	logStatus('Status','Calculating GZIP Savings');
	getCompressionFileStats();
	//echo("Listing Headers<br/>");
	$ListOfHeaders = getArrayOfHeaders();
	//echo("<pre>");
	//var_dump($ListOfHeaders);
	//echo("</pre>");
	//echo("end of Listing Headers<br/>");
	//echo("MAIN: Start of Listing page objects<br/>");
	//echo("<pre>");
	//print_r($arrayPageObjects);
	//echo("</pre>");
	//echo("MAIN: End of Listing page objects<br/>");
}
    // session_start();
    // $_SESSION['status'] = 'Preparing Toasted Page';
	// session_write_close();
	logStatus('Status','Preparing Geo Locations');
	//echo("rootloc: ".$rootloc."<br/>");
	// geo lookup for root
	$markerhome = $geoExtLocStaticMarker;
	$domainmarker = "&markers=color:blue%7Clabel:P%7C".$edgeloc;
	$shards = getDomainMarkers("Shard");
	if($shards != '')
		$shardmarkers = "&markers=color:orange%7Clabel:S" . $shards;
	else
		$shardmarkers = '';
	$thirdp = getDomainMarkers("3P");
	//echo "3p = ".$thirdp."<br/>";
	if($thirdp != '')
		$thirdpartymarkers = "&markers=color:green%7Clabel:3" . $thirdp;
	else
		$thirdpartymarkers = '';
	$gurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x340&maptype=hybrid&scale=2".$domainmarker.$markerhome.$thirdpartymarkers.$shardmarkers."&key=AIzaSyA516_LkcFjBseH8045CILsvfymNK9tYiU";
	//echo "location = ".$gurl."<br/>";
	switch ($geoIPLookupMethod)
	{
	 	case 1:
			$geoIPprovider = 'DB-IP, db-ip.com';
			break;
		case 2:
			$geoIPprovider = 'Telize, www.telize.com';
			break;
		case 3:
			$geoIPprovider = 'freegeoip.net';
			break;
        case 4:
			$geoIPprovider = 'HackerTarget';
			break;
		default:
			$geoIPprovider = 'This product includes GeoLite2 data created by MaxMind, available from
			<a href="http://www.maxmind.com">http://www.maxmind.com</a>.'; // use local 
	}
	// DEBUG INFO EXTRA - ALL OBJECT INFO - ANY non-printable chars here will prevent JS operation
	// echo("array page objects before converting to JS<pre>");
	// print_r($arrayPageObjects);
	// echo("</pre>");
	// convert www.webpagetoaster.com paths for viewing
	foreach ($arrayPageObjects as $key => $valuearray)
	{
//var_dump($valuearray);
		$value = $valuearray["Object source"];
		$local = $valuearray["Object file"];
//echo "checking local fn " . $local . "<br/>";
		if(strpos($local,"/var/sites/w/webpagetoaster.com/public_html/toast") !== false )
		{
			$toastlocal = str_replace("/var/sites/w/webpagetoaster.com/public_html/toast/","https://www.webpagetoaster.com/toast/",$local);
//echo ("updating local fn" . $local . " to " . $toastlocal . "<br/>");
			$arr = array("Object source" => $value, "Object file" => $toastlocal);
             addUpdatePageObject($arr);
		}

	}
	// echo("array page objects after converting to JS<pre>");
	// print_r($arrayPageObjects);
	// echo("</pre>");

    // session_start();
    // $_SESSION['status'] = 'Preparing Toasted Page';
	// session_write_close();
	logStatus('Status','Preparing Toasted Page');
?>
<div class="wrap">
<!-- the tabs -->
<ul class="tooltabs">
	<li><a href="#tab_summary"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span>Summary</a></li>
	<li><a href="#tab_rules"><span class="glyphicon glyphicon-check" aria-hidden="true"></span>Perf. Rules</a></li>
    <li><a href="#tab_objects"><span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span>Objects</a></li>
    <li><a href="#tab_headers"><span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>Headers</a></li>
    <li id="tabcacheexp"><a href="#tab_cachanalysis"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>Cache Analysis</a></li>
    <li><a href="#tab_textfiles"><span class="glyphicon glyphicon-text-background" aria-hidden="true"></span>CSS & JS</a></li>
    <li id="tabFonts"><a href="#tab_fonts"><span class="glyphicon glyphicon-font" aria-hidden="true"></span>Fonts</a></li>
    <li><a href="#tab_imagefiles"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span>Images</a></li>
	<li><a href="#tab_imageoptimisation"><span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>Image Opt.</a></li>
    <li><a href="#tab_domains"><span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>Domains</a></li>
    <li><a href="#tab_thirdparties"><span class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span>3rd Parties</a></li>
    <li><a href="#ObjDetail"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>Object Detail</a></li>
    <li id="tablinks"><a href="#tab_links"><span class="glyphicon glyphicon-link" aria-hidden="true"></span>Links</a></li>
    <li><a href="#tab_locations"><span class="glyphicon glyphicon-globe" aria-hidden="true"></span>Locations</a></li>
    <li id="tabErrors"><a href="#tab_errors"><span class="glyphicon glyphicon-flash" aria-hidden="true"></span>Errors</a></li>
    <li id="tabHAR"><a href="#tab_HAR"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>HAR Timing</a></li>
	<li id="tabScore"><a href="#tab_Score"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>Mat Score</a></li>
    <!--<li><a href="#">Site Info</a></li>-->
</ul>
<!-- tab "panes" -->
<div class="panes">
	<div id="tab_summary" class="pane">
    	<div style="clear: both;"></div>
        <h2>Root File Analysis</h2>
        <div id="site">webpage</div>
        <div class="statstyleright">
        <form>
        <button class="btn btn-default" id="StatStyles" class="button" type="button" value="HL"><span class="glyphicon glyphicon-alert" aria-hidden="true"></span>Toggle Highlighting</button>
         </form>
         </div>
        <h3>Root HTML Static Analysis</h3>
        <div id="filestats_list"></div>
		<div style="clear: both;"></div>
        <h3 id="hdgrootredirs_table">Root Redirections</h3>
        <table id="rootredirs_table" class="dataTable table-striped" border="0"><tbody></tbody></table>
        <div style="clear: both;"></div>
        <h3>Root HTML Source Code</h3>
        <pre class="brush: html; stripBrs: true;"><?php $file = file_get_contents_utf8($localfilename);echo(htmlspecialchars($file));?>
		</pre>
        <br>HTML page after DOM manipulation:
        <pre class="brush: html; stripBrs: true;"><?php $file = file_get_contents_utf8($dumpname);echo(htmlspecialchars($file));?></pre>
		<h3 id="pjspage">Device view (default: "Above the Fold") using <?php echo $browserEngineVer;if(strpos($browserEngineVer,"WebpageTest") != false) echo " (id: ".$testId. ")";?></h3>
        <div style="height:<?php $adjheight = $height + 50; echo $adjheight;?>px; width:<?php $adjwidth = $width + 50;  echo $adjwidth;?>px;" id="pjspageimg"></div>
        <div style="clear: both;"></div>
        <!--<h2>Diagnostics</h2>
        <span id="diags" class="objects"></span>-->
    </div>
	<!-- Rule conformance -->
    <div id="tab_rules" class="pane">
    	<div style="clear: both;"></div>
        <h2>Performance Rules</h2>
        <table id="rules_table" class="table" border="1"><thead></thead><tbody></tbody></table>
    </div>
    <!-- Objects -->
    <div id="tab_objects" class="pane">
    	<div style="clear: both;"></div>
    	<h2>Objects Available to the Page</h2>
        <table id="newobj_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
    </div>
    <!-- Header analysis -->
    <div id="tab_headers" class="pane">
    <div style="clear: both;"></div>
    <h2>Object Headers</h2>
       <table id="headers_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
    </div>
    <!-- Cache Analysis -->
    <div id="tab_cacheanalysis" class="pane">
	<!-- START of Hackathon code to present analysis of cache headers - last modified date and expires date  -->
    <!-- Cache Header analysis -->
		<div style="clear: both;"></div>
		<h2>Caching Analysis</h2>
		Object Cache Analysis (in whole days) - comparison of how long an object remains fresh (days to expiry) and when it was last updated (last modified); Max Age supercedes the Expires Header.
        <ul class="subtabs">
            <li><a href="#st1"><span class="glyphicon glyphicon-align-center" aria-hidden="true"></span>Chart</a></li>
            <li><a href="#st2"><span class="glyphicon glyphicon-align-left" aria-hidden="true"></span>Table</a></li>
            <li><a href="#st3"><span class="glyphicon glyphicon-th"aria-hidden="true"></span>List</a></li>
        </ul>
		<div class="subpanes">
            <!-- display 1 -->
            <div class="subpane" id="st1">
       			Chart Comparison
                <div id="cacheAnalysisContainer" style="width:auto; height: auto; margin: 0 auto"></div>
            </div>
			</br>
 		    <!-- display 2 -->
            <div class="subpane" id="st2">
      			List of Last Modified and Expires Dates and Max Age
                <table id="cache_tables" class="dataTable table-striped">
                    <tbody></tbody>
                </table>
            </div>
			</br>
            <!-- display 3 -->
            <div class="subpane" id="st3">
            Tabular Comparison
                <table id="cacheExpLmd_table" class="cacheEL_table" border="0"><tbody></tbody></table>
            </div>
        </div>
    </div>
    <!-- Text Files -->
    <div id="tab_textfiles" class="pane">
    	<div style="clear: both;"></div>
        <h2>CSS & JS Ordering Analysis</h2>
        <div id="HCtextfiles">
            <span id="cssjstxt"><h3>CSS & JS Ordering Analysis (original HTML source)</h3></span>
			<div id="hdrsb4_container"></div>
			<div id="bodyb4_container"></div>
		</div>
        <div style="clear: both;"></div>
        <h3>CSS & JS Ordering Analysis</h3>
        <table id="fileordering_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <div id="HCtextfilesAFTER">
            <h3>CSS & JS Ordering Analysis (after any JS modification)</h3>
			<div id="hdrs_container"></div>
			<div id="body_container"></div>
		</div>
        <div style="clear: both;"></div>
        <h3>Cascading Style Sheets</h3>
        <span id="csslist" class="objects"></span>
        <table id="css_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
		<div style="clear: both;"></div>
        <h3>JavaScript Files</h3>
        <span id="jslist" class="objects"></span>
        <table id="js_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <div style="clear: both;"></div>
        <h3>GZIP Compression Savings (for uncompressed text files on all Domains)</h3>
        <span id="gzip" class="objects"></span>
        <table id="gzip_table" class="dataTable gzip_table" border="1"><tbody></tbody></table>
        <h3>Stylesheet Usage - Experimental!</h3>
        <div style="clear: both;"></div>
        <table id="cssusage_table" class="dataTable table-striped" border="0"><tbody></tbody></table>
        <h4>CSS Analysis</h4>
        <div id="accordion" class="panel-group">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Document ID Selectors</a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <?php echo("CSS Analysis: document ID Selectors<pre>");
                   print_r($rootStyleID);
                    	echo("</pre>");?>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">Document Class Selectors</a>
                    </h4>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse">
                    <div class="panel-body">
                        <?php echo("CSS Analysis: document Class Selectors<pre>");
                 print_r($rootStyleClass);
                	    echo("</pre>"); ?>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">Document Element Selectors</a>
                    </h4>
                </div>
                <div id="collapseThree" class="panel-collapse collapse">
                    <div class="panel-body">
                         <?php echo("CSS Analysis: document Element Selectors<pre>");
                	    print_r($rootElements);
                	    echo("</pre>"); ?>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">CSS Usage Summary</a>
                    </h4>
                </div>
                <div id="collapseFour" class="panel-collapse collapse">
                    <div class="panel-body">
                         <table id="cssusageall_table" class="table" border="0"><tbody></tbody></table>
                    </div>
                </div>
            </div>
        </div>
        <span id="cssusagelist2" class="objects"></span>
        <span id="cssusagelist3" class="objects"></span>
        <span id="cssusagelist3" class="objects"></span>
        <div style="clear: both;"></div>
    </div>
    <!-- Fonts -->
    <div id="tab_fonts" class="pane">
    <div style="clear: both;"></div>
        <h2>Fonts</h2>
        <table id="fonts_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <div style="clear: both;"></div>
        <form>
        <button class="btn btn-default" id="ViewFonts" class="button" type="button" value="View Text Fonts"><span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>View Fonts</button>
        <button class="btn btn-default" id="HideFonts" class="button" type="button" value="Hide Text Fonts"><span class="glyphicon glyphicon-zoom-out" aria-hidden="true"></span>Hide Fonts</button>
        </form>
        <div id="fontdefs"></div>
        <div id="fontdisplay"></div>
    </div>
    <!-- Image Files -->
    <div id="tab_imagefiles" class="pane">
    	<div style="clear: both;"></div>
	    <h2>Image File Analysis</h2>
        <span id="imagelist" class="objects"></span>
        <table id="images_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <div style="clear: both;"></div>
        <form>
        <button class="btn btn-default" id="ViewImages" class="button" type="button" value="View Images"><span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>View Images</button>
        <button class="btn btn-default" id="HideImages" class="button" type="button" value="Hide Images"><span class="glyphicon glyphicon-zoom-out" aria-hidden="true"></span>Hide Images</button>
        </form>
        <div id="theDiv">Click 'View Images' to view a filtered set of images</div>
    </div>
	 <!-- image optimisation -->
   	<div id="tab_imageoptimisation" class="pane">
    <div style="clear: both;"></div>
    <h2>Image Optimisation</h2>
    <button class="btn btn-default" type="button" id="genthumbnails" value="Generate Thumbnails"><span class="glyphicon glyphicon-play" aria-hidden="true"></span>View Thumbnails</button>
        <h3>JPEG Image Optimisation</h3>
        <label>TinyJPG API Key</label><input type="text" id="tinyjpgapikey" name="tinyjpgapikey" size="60" width="60" value="" class="long">
        <table id="optJPGimages_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3>PNG Image Optimisation</h3>
        <table id="optPNGimages_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3>GIF Image Optimisation</h3>
        <table id="optGIFimages_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3>GIF Animation Optimisation</h3>
        <table id="optGIFanimations_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3>WEBP Image Optimisation</h3>
        <table id="optWEBPimages_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3>BMP Image Optimisation</h3>
        <table id="optBMPimages_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
    </div>
    <!-- Domains -->
    <div id="tab_domains" class="pane">
    	<div style="clear: both;"></div>
    	<h2>Domain Analysis</h2>
		<div id="HCdomains">
			<div id="container_dompie2"></div>
			<div id="container_dompie"></div>
		</div>
        <table id="domains_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
    </div>
    <!-- Third Party Content -->
    <div id="tab_thirdparties" class="pane">
    <div style="clear: both;"></div>
        <h2>Third Party Object Analysis</h2>
		<div id="HC3p">
			<div id="container_3ppie"></div>
			<div id="container_3ppie2"></div>
            <div id="container_3ppie3"></div>
		</div>
        <div style="clear: both;"></div>
        <h3 id="hdg3Ptagmanagers_table">Tag Manager(s)</h3>
        <table id="3Ptagmanagers_table" class="dataTable table-striped" border="0"><tbody></tbody></table>
        <div style="clear: both;"></div>
        <h3 id="hdg3Ptags_table">Tag Descriptions</h3>
        <button class="button button2" id="ShowNew3pDomains">Show New Domains</button>
        <table id="objects3p_table" class="dataTable table-striped tab3p" border="1"><thead></thead><tbody></tbody></table>
        <div style="clear: both;"></div>
        <h3 id="hdgTPChain_table">Tag Call Chain</h3>
        <span id="txtTPChain_table">Note: some object's domains may be found in more than one other object's code. Check the "Match" column to see the degree of matching.<br/>
        "Full": full URL; "Partial": URL without querystring; "Domain": domain only</span>
        <table id="TPChain_table" class="dataTable table-striped" border="0"><thead></thead><tbody></tbody></table>
		<h3 id="hdgTPChain_chart">Tag Call Hierarchy</h3>
		<!--<input type="radio" name="tphd" id="tphd1" value="simple">Simple</input>
		<input type="radio" name="tphd" id="tphd2" value="simplegroup" checked>Group</input>
		<input type="radio" name="tphd" id="tphd3" value="multi">Multi</input>
		<input type="radio" name="tphd" id="tphd4" value="all">All</input> -->
		<div id="TPChart_div"></div>
		<h3 id="hdgTPChain_network">Tag Call Network</h3>
		<div id="network_options">
			<span class="boxoutline">Node level:<input type="radio" name="netlevel" class="netlevel" checked value="D"> By Domain
			<input type="radio" name="netlevel" class="netlevel" value="CP"> By Company &amp; Products</span>
			<span class="boxoutline">Node style:
			<input type="radio" name="netshape" class="netshape" checked value="Dot"> Nodes sized by total bytes
			<input type="radio" name="netshape" class="netshape"value="Box"> Simple names</span>
			<span class="boxoutline"> Grouping:
			<input type="radio" name="netcolouring" class="netcolouring" checked value="Groups"> Third Party Groups
			<input type="radio" name="netcolouring" class="netcolouring" value="Times"> Nav. Timing</span>
			<span class="boxoutline">Layout style:<input type="radio" name="netlayout" class="netlayout" checked value="N"> Normal
			<input type="radio" name="netlayout" class="netlayout" value="UD"> Vertical
			<input type="radio" name="netlayout" class="netlayout" value="LR"> Horizontal</span>
			<span>
			<input type="text" name="focus" class="focussearch" id="focussearch">
  			<input type="button" class="button2" id="focusnode" value="Search">
			</span>
			<span>
			<input type="button" class="button2" id="fitnodes" value="Fit">
			<input type="button" class="button1" id="toggle_bgcolour" value="Toggle background colour">
			</span>
		</div>
		<div id="TPnetwork"></div>
		<div>
			<textarea id="input_output"></textarea>
            Export as:<input type="text" id="export_fn" value="" placeholder="enter filename" size="35"></input>
            <input type="button" id="export_button" class="button1 button3" disabled onclick="exportNetwork()" value="Export"></input>
        </div>
        <h3 id="hdgTPCookies_table">Cookies</h3>
        <span id="txtTPCookies"></span>
		<h3 id="hdgTPParameters_table">Parameters</h3>
        <table id="TPParameters_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3 id="hdgTPperformance_table">Third Party Conformance to Best Practice by Object</h3>
        <table id="TPperformance_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
    	<div style="clear: both;"></div>
    	<h2>Domain Analysis</h2>
		<div id="3Pdomains">
			<div id="container_3Pdompie"></div>
            <div id="container_3Pdompie2"></div>
		</div>
        <div style="clear: both;"></div>
        <h2 id="hdgTPDomains_table">Third Party Conformance to Best Practice by Company</h3>
        <table id="TPDomains_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
		<div style="clear: both;"></div>
		<h2>Tag Waterfall</h2>
		<div id="3Ptagwaterfall">
		<button class="btn btn-default" id="tagwfToggle" class="button" type="button" value="Toggle"><span class="glyphicon glyphicon-random" aria-hidden="true"></span>Toggle View</button>
			<div id="container_3Ptagwaterfall"></div>
		</div>
		<div style="clear: both;"></div>
		<h2>Content Analysis</h2>
		<div id="3Pcontentanalysis">
		<table id="TPContent_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
		</div>
	</div>
    <!-- Object Detail -->
    <div id="ObjDetail" class="pane">
    	<div style="clear: both;"></div>
        <div id="divdd" class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" id="objitemslist" data-toggle="dropdown" aria-expanded="false">
          Select Object
          <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu" aria-labelledby="objitemslist">
          </ul>
        </div>
        <h2 id="headerobject">Root Object Headers</h2>
        <div id="objectinfo"></div>
        <span id="chresult" class="objects"></span>
        <h3>Headers</h3>
        <!--<input id="prev" type="button" value="Previous" onclick="showPrevNext(-1);" />
        <input id="next" type="button" value="Next" onclick="showPrevNext(1);" />-->
        <div id="selectType">
        <button class="btn btn-default" id="removeClass"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>Remove highlight</button>
        <label><input type="checkbox" id="shcookies" name="shcookies" class="cookies" checked/>Show Cookies</label>
        <button class="btn btn-default" id="objPrevious"><span class="glyphicon glyphicon glyphicon-backward" aria-hidden="true"></span>Previous</button>
        <button class="btn btn-default" id="objNext"><span class="glyphicon glyphicon-forward" aria-hidden="true"></span>Next</button>
        <button class="btn btn-default" id="objCacheChk"><span class="glyphicon glyphicon-eject" aria-hidden="true"></span>Cache Chk</button>
        </div>
        <div class='headersouter'>
        <!--<div id="headers" class="headers"></div>--><div id="headersn">TEST</div></div>
        <h3>Header Analysis</h3>
        <div id="headeranalysis" class="headersV"></div>
        <span id="chresult" class="objects"></span>
    </div>
    <!-- Page links -->
   	<div id="tab_links" class="pane">
    	<div style="clear: both;"></div>
    	<h2>Page Links</h2>
        <table id="links_table" class="dataTable table-striped" border="1"><thead></thead><tbody></tbody></table>
        <h3>Comma Separated List of Links</h3>
        <span id="linkscs" class="objects"></span>
        <span id="linkslist" class="objects"></span>
    </div>
    <!-- Locations -->
   	<div id="tab_locations" class="pane">
    	<div style="clear: both;"></div>
    	<h2>Server Locations</h2>
        <div id="locations">
            <h3>Static Map</h3>
        	<img src="<?php echo $gurl;?>"/>
        	<div id="domainhostloc"><span class="geoip"><h3>LEGEND</h3><?php echo $geoMarkerLetter . ' = '.$geoExtLoc;?><br/>P = Host Domain, <?php echo $rootloc ?><br/>S = Shard<br/>3 = Third Party</span>
            <div style="clear: both;"></div>
            <h3>List of Domains, edge servers and their locations</h3>
            <table id="domloc_table" class="dataTable table-striped domloc" border="1"><thead></thead><tbody></tbody></table>
            <div style="clear: both;"></div>
            <span class="geoip">IP Location information provided by <?php echo $geoIPprovider;?></span></div>
            <h3>Zoomable Map</h3>
		    <div id="map-canvas"></div>
        </div>
    </div>
    <!-- Site info
   	<div>
    	<div style="clear: both;"></div>
    	<h2>Site Info</h2>
        <h3>Shared Host Websites</h3>
        <table id="site_table" class="item_table" border="1"><thead></thead><tbody></tbody></table>
        <span id="sitelist" class="objects"></span>
    </div>-->
    <!-- Locations -->
   	<div id="tab_errors" class="pane">
    	<div style="clear: both;"></div>
        <h2>Page Errors</h2>
        <div id="errors">
        <table id="errors_table" class="dataTable table-striped" border="1"><tbody></tbody></table>
        </div>
    </div>
    <!-- HAR File and Waterfall Chart-->
   	<div id="tab_HAR" class="pane">
    	<div style="clear: both;"></div>
        <?php
        if($uploadedHAR == True)
          $harsource="HAR file from ". $browserEngineVer;
        else
          {
            $harsource="HAR file from ". $browserEngineVer;
          }?>
        <h2>Page Analysis - <?php echo $harsource ?></h2>
        <?php echo ("[Simulated] Device: ". $i. ": User Agent: ". $ua."</br>"); ?>
        <div id="HARChart">
        </div>
        <div id="HARChartLegend">
        </div>
        <div style="clear: both;"></div>
        <div id="harfile">
        <a href="<?php echo $harfile ?>" target="_blank">Download HAR File</a>
        </div>
        <div style="clear: both;"></div>
        </br>
    </div>
   	<div id="tab_Score" class="pane">
    	<div style="clear: both;"></div>
		<div id="MatScore"></div>
		<div style="clear: both;"></div>
		<div id="MatScoreDebug"></div>
	</div>
    </div> <!-- end panes -->
</div> <!-- end wrap -->
<script>
<?php
// tidy up - remove temp. files
//get file size of cookie file: delete if 0 size, or rename and move to toast folder if bigger
$cookiedata = '';
if(filesize($cookie_jar) == 0)
    unlink($cookie_jar);
else
{
    rename($cookie_jar,  $localfilename."_cookies.txt");
    $cookiedata = readcookiefile($localfilename."_cookies.txt");
}
//echo "removing temp files<br/>";
if($OS == 'Windows')
{
    if (file_exists(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$browserengineoutput)) {
		rename(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$browserengineoutput,$filepath_domainsavedir."/domafter.htm");
        }
     if (file_exists(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR."CK".$browserengineoutput)) {
        unlink(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR."CK".$browserengineoutput);
		}
	if (file_exists(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR."PD".$browserengineoutput)) {
		unlink(realpath( '.' ).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR."PD".$browserengineoutput);
		}
}
else
{ // linux
    if (file_exists($browserengineoutput)) {
		rename($browserengineoutput,$filepath_domainsavedirLnx."/domafter.htm");
        }
    if (file_exists($browserengineoutput."CK")) {
        unlink($browserengineoutput."CK");
		}
	if (file_exists($browserengineoutput."PD")) {
		unlink($browserengineoutput."PD");
		}
	if (file_exists($cookie_jar)) {
		unlink($cookie_jar);
		}		
}
    // session_start();
    // $_SESSION['status'] = 'Formatting Page (JavaScript)';
	// session_write_close();
	logStatus('Status','Toast is ready; popping up in the Toaster');
?>
var cookietext = '<?php echo utf8_converter($cookiedata); ?>';
//console.log("Starting JavaScript main");
// inline script needed due to use of PHP
	var datenowMS = Date.now();
	var url = "<?php echo $url_page; ?>";
	toasterid = "<?php echo $toasterid; ?>";
	//console.log(url);
	var domain = "<?php echo $host_domain; ?>";
	//console.log(domain);
	var savedir = "<?php if($OS=="Windows") $sd=str_replace("\\","/",$filepath_domainsavedir);else $sd = $filepath_domainsavedir;echo $sd; ?>";
    var jssavedir = "<?php echo $jsfilepath_domainsavedir; ?>";
	var mimetype = "<?php echo $mimetype; ?>";
	//console.log("mime type: " + mimetype);
	var statuscode = "<?php echo $sc; ?>";
	//console.log("status code: " + statuscode);
	//console.log("content length: " + contentlength + " bytes");
	var ttime = "<?php echo $ttime; ?>";
	var rdtime = "<?php echo $rdtime; ?>";
	var contime = "<?php echo $contime; ?>";
	var dnstime = "<?php echo $dnstime; ?>";
	var dstime = "<?php echo $dstime; ?>";
	var dsstime = "<?php echo $dsstime; ?>";
	var Width = "<?php echo $width; ?>";
	var Height = "<?php echo $height; ?>";
    //console.log("total time: " + ttime);
	//console.log("redirect time: " + rdtime);
	//console.log("connect time: " + contime);
	//console.log("dns time: " + dnstime);
	//console.log("pre-transfer time: " + dstime);
	//console.log("start-transfer time: " + dsstime);
    var JSONobj = <?php echo json_encode($hdrs); ?>;
	//console.log("headers"+JSONobj);
	var hdrlength = "<?php echo $hdrlength ?>";
	//console.log("header length: " + hdrlength);
	headerStr = <?php echo json_encode(nl2br($hdrs)); ?>;
	//document.write(JSONobj);
	var redir_count = "<?php echo $page_redir_total; ?>";
    //console.log("redirection count: " + redir_count);
	var redirset = <?php echo json_encode($redirs); ?>;
	if (redir_count > 0)
	{
		var redirstr = redirset.toString();
		var redirs = redirstr.replace(",","<br />");
		//console.log("redirections: " + redirs);
	}
	var CSSfileset = <?php echo json_encode($ListOfCSSFiles); ?>;
	var JSfileset = <?php echo json_encode($ListOfJSFiles); ?>;
	var Imgfileset = <?php echo json_encode($ListOfImageFiles); ?>;
	var CSS3Pfileset = <?php echo json_encode($ListOf3PCSSFiles); ?>;
	var JS3Pfileset = <?php echo json_encode($ListOf3PJSFiles); ?>;
	var Image3Pfileset = <?php echo json_encode($ListOf3PImageFiles); ?>;
	var ImageLinkfileset = <?php echo json_encode($ListOfImageLinks); ?>;
	var Linkfileset = <?php echo json_encode($ListOfLinks); ?>;
	var GzipAnalysis = <?php echo json_encode($gzipanalysis); ?>;
	var ImagesLink = <?php echo '"'.$imagepagelink.'"'; ?>;
	// json ready
	var ObjList = <?php echo json_encode($arrayOfObjects); ?>;
	var LinkList = <?php echo json_encode($arrayOfLinks); ?>;
	var GzipStats = <?php echo json_encode($arrayGZIPStats); ?>;
	var GzipTotals = <?php echo json_encode($arrayTotals); ?>;
	var FileStats = <?php echo json_encode($arrayFileStats); ?>;
	var FileListStats = <?php echo json_encode($arrayFileListStats); ?>;
	var PageStats = <?php echo json_encode($arrayPageStats); ?>;
	var TimesList = <?php echo json_encode($arrayOfTimings); ?>;
    var RootRedirs = <?php echo json_encode($arrayRootRedirs); ?>;
	var FileOrderList = <?php echo json_encode($arrayOrderedCSSJS); ?>;
	var ErrorList = <?php echo json_encode($arrayErrors); ?>;
	var DomainsList	 = <?php echo json_encode(utf8_converter($arrayDomains)); ?>;
	var DomainStats3PList = <?php echo json_encode(utf8_converter($array3PDomainStats)); ?>;
	var PostData = <?php echo (json_encode($pjspostdataJSON)); ?>;
	var Diags = '<?php echo($diagnostics); ?>';
	var Tests = <?php echo json_encode(utf8_converter($arrayOfTests)); ?>;
	var Rules = <?php echo json_encode(utf8_converter($arrayOfRules)); ?>;
    // error_log( print_R($arrayOfCSSSelectors,TRUE) ) / add this to php below when required
    var CSSselectors = <?php ;if(!empty($arrayOfCSSSelectors)) echo json_encode(utf8_converter($arrayOfCSSSelectors)); else echo json_encode('[{"CSS filename":"","Selector type":"element","Selector name":"","Used in HTML":"dummy"}]'); ?>;
// utf8 versions
	var NewObj = <?php echo json_encode(utf8_converter($arrayPageObjects, JSON_UNESCAPED_UNICODE)); ?>;
	var Headers = <?php echo json_encode(utf8_converter($arrayPageHeaders, JSON_UNESCAPED_UNICODE)); ?>;
	var HARReqPostData = <?php echo json_encode(utf8_converter($arrayPostData, JSON_UNESCAPED_UNICODE)); ?>;
// non utf8 versions
//	var NewObj = <?php //echo json_encode($arrayPageObjects, JSON_UNESCAPED_UNICODE); ?>;
//	var Headers = <?php //echo json_encode($arrayPageHeaders, JSON_UNESCAPED_UNICODE); ?>;
//var lenID = <?php echo "length of image data array =" . count($arrayImageData);?>;
	var ImageData = <?php echo json_encode(utf8_converter($arrayImageData));?>;
    var TagManagers = <?php echo json_encode(utf8_converter($arrayTagManagers));?>;
    var HostedThirdPartyFiles = <?php echo json_encode(utf8_converter($arrayHost3PFiles));?>;
    var ThirdPartyChain = <?php echo json_encode(utf8_converter($arrayThirdPartyChain));?>;
	// analysis of root header
	var rootheaderanalysis = '';
	rootheaderanalysis = analyseHeader(0,headerStr,mimetype,true);
	var reverseipresults = <?php echo json_encode($reverseIPResults); ?>;
	var rootlatlong = "<?php echo $rootlatlong; ?>";
    var extloc = "<?php echo $geoExtLoc; ?>";
    var extlatlong = "<?php echo $extlatlong; ?>";
	var CacheAnalysis = <?php echo json_encode(utf8_converter($arrayCacheAnalysis)); ?>;
	var CacheAnalysisBarStackChart = <?php echo json_encode(utf8_converter($objectChartData)); ?>;
    var hardata = '<?php echo $har; ?>';
	var renderStartMS = "<?php echo $rstime_ms; ?>";
	var DOMCompleteMS = "<?php echo $domCompletetime_ms; ?>";
	var onLoadMS = "<?php echo $onload_ms; ?>";
	var docTime = "<?php echo $docTime_ms; ?>";
</script>
<script type="text/javascript" charset="utf8" src="/toaster/js/jquery.min.js"></script>
<script src="/toaster/bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCSP9nBZ1aRvIZRc4tQbXznyrISL7Gt6d8"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script src="https://cdn.datatables.net/colreorder/1.3.2/js/dataTables.colReorder.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script>
	var browserEngineVer = <?php echo "'".$browserEngineVer."'";?>;
	console.log("starting mainDisplay JS");
	mainDisplay(browserEngineVer);
</script>
<script type="text/javascript" src="/toaster/js/jquery.tools.min.js"></script>
<script type="text/javascript" src="/toaster/js/jquery-ui.js"></script>
<script type="text/javascript" src="/toaster/js/har.js"></script>
<script type="text/javascript" src="/toaster/js/vis.js"></script>	
<script type="text/javascript" src="/toaster/js/jquery.collapsible.js"></script>
<script type="text/javascript" src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script type="text/javascript" src="https://code.highcharts.com/modules/funnel.js" defer></script>
<script type="text/javascript" src="https://code.highcharts.com/modules/exporting.js" defer></script>
<script src="https://code.highcharts.com/modules/offline-exporting.js" defer></script>
<script type="text/javascript" src="/toaster/toaster_tools/shCore.js"></script>
<script type="text/javascript" src="/toaster/toaster_tools/shBrushXml.js"></script>
<script type="text/javascript" src="/toaster/toaster_tools/shBrushJScript.js"></script>
<script type="text/javascript" src="/toaster/toaster_tools/shBrushCss.js"></script>
<script type="text/javascript" src="/toaster/js/jquery.formatDateTime.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.7/highlight.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<!--<script type="text/javascript" src="/toaster/toaster_tools/bpgdec8a.js" defer></script>-->
<script type="text/javascript">
	SiteTitle = <?php echo '"'.$siteurl.'"'; ?>;
	PageTitle = <?php echo '"'.urldecode($pagetitle).'"'; ?>;
	PageImage = <?php echo '"'.$jsimgname.'"'; ?>;
	HarFile = <?php echo '"'.str_replace("/", "//", $harfile).'"'; ?>; // use double slash to avoid unicode error on /u
    BrwsEngine = <?php echo '"'.$browserengine.'"'; ?>;
	displayPageInfo();
    SyntaxHighlighter.all();
	console.log("generating third party call chart");
    google.charts.load('current', {packages:["orgchart"]});
	google.charts.setOnLoadCallback(drawTPChart);
	visjs_thirdpartynetwork("D");
    // image in sized div
    if(BrwsEngine != '6' && BrwsEngine != '8')
    {
      	$('#pjspage').append(' at Resolution of <?php echo $width;?> x <?php echo $height;?> px (<?php echo str_replace("_"," ",$uastr);?>)');
    }
    else
    {
        $('#pjspage').append(' at configured WPT Resolution (<?php echo str_replace("_"," ",$uastr);?>)');
    }
    // add the image for all browser engines
	$('#pjspageimg').prepend('<img id="theImg" src="'+ PageImage +'" />');
//    console.log("adding screenshot to summary: " + PageImage);
//console.log("hardata",hardata);
	//drawWaterfall(data,"MyChart");
	if(hardata)
		renderHAR(hardata);
	else
	{
//console.log("bypassing HAR processing - no file to process");
	}
    document.title = 'Toasted - ' + url;
</script>
 </div> <!-- class="container-fluid" -->
</body>
</html>
<?php
if(file_exists($cookie_jar))
	@unlink($cookie_jar);
// session_start();
// $_SESSION['object'] = '';
// $_SESSION['mimetype'] = '';
// $_SESSION['imagepath'] = '';
// $_SESSION['toastedfile'] = '';
// $_SESSION['status']  = 'Ready to Toast';
// session_write_close();
logStatus('Status','Ready to Toast');
logStatus('Toasting',false);
// Get the content that is in the buffer and put it in your file //
//echo ("writing toast file to " . $toastedfilepathname."<br/>");
//error_log (ob_get_contents());
$toastfileresult = file_put_contents($toastedfilepathname, ob_get_contents(),LOCK_EX);
//echo ("toast file put result " . $toastfileresult."<br/>");
// add generated file to lists of tests
// $dt = date("Y-m-d H:i:s");
// $line = $array = array($url,$toastedwebname,trim($dt),str_replace("_"," ",$uastr),htmlentities(trim($pagetitle)),trim($uploadedHARFileName),trim($jsimgname),$runnotes,);
// if(substr($filepath_basesavedir,-1) != DIRECTORY_SEPARATOR)
// 	$toastedlist = $filepath_basesavedir.DIRECTORY_SEPARATOR."toasted.csv";
// else
// $toastedlist = $filepath_basesavedir."toasted.csv";
// $handle = fopen($toastedlist, "a");
// fputcsv($handle, $line,',');
// fclose($handle);
// replacement json file list of toasted pages
$dt = date("Y-m-d H:i:s");
$datedir = date("Y").'/'.date("m").'/'.date("d").'/';
if(substr($filepath_basesavedir,-1) != DIRECTORY_SEPARATOR)
	$toastedlog = $filepath_basesavedir.DIRECTORY_SEPARATOR.$datedir."toasted.json";
else
	$toastedlog = $filepath_basesavedir.$datedir."toasted.json";
$arr = array ("datetime"=>$dt, "url"=>$url,"toastedwebname"=>$toastedwebname,"browserengine"=>$browserEngineVer,"ua"=>str_replace("_"," ",$uastr),"pagetitle"=>htmlentities(trim($pagetitle)),"harfile"=>trim($uploadedHARFileName),"imgname"=>trim($jsimgname),"notes"=>$runnotes);
$logToastedResult = file_put_contents($toastedlog, json_encode($arr)."\n",FILE_APPEND); // don't lock_ex to allow reading at same time
if($logToastedResult === false)
	error_log("Failed to write to toasted json log: " . $dt . " " . $toastedwebname);
//final logging of new test
if( strpos($hostname,"gridhost.co.uk") != false)
	$logurl = 'https://www.webpagetoaster.com/toaster/sql/dblogging.php?tid='. $toasterid.'&ip='.$externalIp.'&url='.urlencode($url).'&eng=' .$_REQUEST["wbengine"] .'&hchloc=' . $chheadlessserver.'&uip='.$userip_address;
else
	$logurl = 'http://localhost/toaster/sql/dblogging.php?tid='. $toasterid.'&ip='.$externalIp.'&url='.urlencode($url).'&eng=' .$_REQUEST["wbengine"] .'&hchloc=' . $chheadlessserver;
$f = file_get_contents($logurl);
?>
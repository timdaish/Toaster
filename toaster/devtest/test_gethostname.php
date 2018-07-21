<?php
session_start();
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
include '3ptags_nccgroup_db.php';
$hostname = gethostname();
$_SESSION['status'] = 'Processing hostname';
session_write_close();
if( strpos($hostname,"gridhost.co.uk") != false)
    echo "server is www.webpagetoaster.com";
else
    echo "private server :  " . $hostname;
$path = realpath(dirname($_SERVER['DOCUMENT_ROOT']));
echo "server root path = " . $path;
?>

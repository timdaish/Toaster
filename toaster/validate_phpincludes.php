<?php
header("Content-type:text/plain");

include 'ps_functions.php';
echo "ok - ps_functions.php".PHP_EOL;
include 'downloadObject.php';
echo "ok - downloadObject.php".PHP_EOL;
include 'extract_urls.php';
echo "ok - extract_urls.php".PHP_EOL;
include 'domain_url_functions.php';
echo "ok - domain_url_functions.php".PHP_EOL;
include 'simple_html_dom.php';
echo "ok - simple_html_dom.php".PHP_EOL;
include 'tests.php';
echo "ok - tests.php".PHP_EOL;
include 'imagedecoding.php';
echo "ok - imagedecoding.php".PHP_EOL;
include 'fontdecoding.php';
echo "ok - fontdecoding.php".PHP_EOL;
include 'minify.php';
echo "ok - minify.php".PHP_EOL;
include 'class.JavaScriptPacker.php';
echo "ok - class.JavaScriptPacker.php".PHP_EOL;
include 'class.Minify.php';
echo "ok - class.Minify".PHP_EOL;
include 'class.GifDecoder.php';
echo "ok - class.GifDecoder".PHP_EOL;
include 'ttfInfo.class.php';
echo "ok - ttfInfo.class.php".PHP_EOL;
include 'wpt_functions.php';
echo "ok - wpt_functions.php".PHP_EOL;
include '3ptags_nccgroup_db.php';
echo "ok - 3ptags_nccgroup_db".PHP_EOL;
include 'getiploc.php';
echo "ok - getiploc".PHP_EOL;
echo "ALL includes ok".PHP_EOL;
?>
<?php

if (isset($_GET["url"]))
{
	$url = trim($_GET["url"]);
}
if (isset($_GET["dtm"]))
{
	$dtm = trim($_GET["dtm"]);
}

//echo("url: ".$url."<br.>");
//echo("dtm ".$dtm."<br.>");

list($curlInfo, $curlheaders) = readURLandReturnStatusCode($url,$dtm);
$curlheaders = str_replace("\r\n",'<br/>',$curlheaders);
$hdrs = implode($curlheaders);
echo $hdrs;
//echo ($url."<pre>");
//print_r ($curlheaders);
//echo ("</pre>");
//echo json_encode(nl2br($curlheaders));
//echo ($curlInfo['http_code'] );



function read_headerCache($ch, $string) {
	global $curlheaderCache;
    //echo("Received a header: ". $string.strlen($string)."<br/>");
	
	if(strlen($string) >2)
		$curlheaderCache[] = $string;
	
    return strlen($string);
}

function readURLandReturnStatusCode($url, $dtin)
{
	global $result,$ua,$curlheaderCache,$cookie_jar,$ua;
	//echo('<br/>function readURLandReturnStatusCode called for '.$url. '<br />');
	//echo ("opening file $url: $sfn<br/>");
	$curlheader = array();

	$dt = new DateTime($dtin, new DateTimeZone('GMT'));
	$dts = $dt->format('Y-m-d H:i:s');
	//echo ($dts."<br/>");	
	$dtm = gmdate('D, d M Y H:i:s T',$dt->getTimestamp());
	//$contentType = 'text/xml';
    $method = 'GET';
    $auth = '';
    $charset= 'ISO-8859-1';
	$conn = 'Connection: Keep-Alive';
	$IMS = 'If-Modified-since: ' . $dtm;
    $ka = 'Keep-Alive: 300';
	$rqheaders = Array($charset,$conn,$ka,$IMS);
	
	// set some cURL options
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	//curl_setopt($ch, CURLOPT_PROXY, $proxy);
	//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
	$ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_setopt($ch, CURLOPT_TIMEOUT,        30);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT,$ua);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_headerCache');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
	curl_setopt($ch, CURLOPT_COOKIE, $cookie_jar);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);

	curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // request headers
	
	$result = curl_exec($ch);
	
	$headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
	//echo $headerSent;
	
	// Check if any error occurred
	if (!$ch) {
		die("Couldn't initialize a cURL handle");
	}

	if (empty($ret)) {
		// some kind of an error happened
		die(curl_error($ch));
		curl_close($ch); // close cURL handler
	} else {
		$curl_info = curl_getinfo($ch);
		
		curl_close($ch); // close cURL handler	
		}




	return array($curl_info,$curlheaderCache);
}


?>

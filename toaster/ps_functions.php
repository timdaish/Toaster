<?php
$today = date("Ymd");
//$cookie_jar tempnam('/tmp','cookie');
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
  //echo 'This is a server using Windows! '. $windows."<br/>";
      $OS = "Windows";
  }
  else {
  //echo 'This is a server not using Windows!'."<br/>";
      $OS = PHP_OS;
  }
if ($OS == "Windows")
{
    $cookie_jar = tempnam("c:\\temp\\", "cky");
    $drv = substr(__DIR__, 0, 1);
    $filepath_basesavedir = $drv . ":\\toast\\";
    //$perlbasedir = $drv . ":\\xampp\\perl\bin\\";
    $perlbasedir = "e:\\xampp\\perl\bin\\";
}
else
{
    $hostname = gethostname();
    //override for webpagetoaster server
    if( strpos($hostname,"gridhost.co.uk") != false)
    {
        $cookie_jar = tempnam("/var/sites/w/webpagetoaster.com/subdomains/toast/", "cky");
        $drv = '/var/sites/w/webpagetoaster.com/subdomains';
    }
        else
    {
        $cookie_jar = tempnam("/usr/share/toast/", "cky");
        $drv = '/usr/share';
    }
    $filepath_basesavedir = $drv . "/toast/";
}
define ( CURL_HTTP_VERSION_2TLS , 4);
//echo "cookie jar file: ". $cookie_jar."<br/>" ;
$encodingoptions = "gzip,deflate,br"; // br brotli - not all php curls get it so try first and adapt if necessary
$b3pdbPublic = true;
$filepath_domainsavedir = '';
$filepath_domainsaverootdir = '';
$filepathname_rootobject_headersandbody = '';
$runnotes = '';
$toastedwebname = '';
$localvpath = '';
$result = '';
$header = '';
$redirect_count = 0;
$page_redir_total = 0;
$basescheme = '';
$body = '';
$curl_info = '';
$html = new DOMDocument();
$initsubdomain = '';
$initdomain = '';
$roothost = '';
$ua = '';
$uastr = '';
$gzipanalysis = '';
$gziptotal_originalbytes = 0;
$gziptotal_zippedbytes = 0;
$compressionlevel = 6;
$imagepagelink = '';
$thispagename = '';
$bool_b64 = false;
$objcount = 2;
$objcountimg = 0;
$objcountscript = 0;
$objcountcss = 0;
$objcountfont = 0;
$domaincount = 0;
$diagnostics = ' ';
$objCNT = 0;
$byteArray = array();
$testcount = 0;
$boolRootRedirect = false;
$RootRedirURL = '';
$redir_type = '';
$boolHTTPCompressRoot = false;
$boolHTTP2Root = false;
$HTTPCompressionType = '';
$embeddedfile_count = 0;
$embeddedcount = 0;
$totfilesize = 0;
$totbytesdownloaded = 0;
$rootbytesdownloaded = 0;
$reverseIPResults = array();
$rootloc = '';
$noofIframes = 0;
$noof404Errors = 0;
$originalurl = '';
$pagespeedcount = 0;
$noofresponvesrcsetimgs = 0;
$userlat = 0;
$userlong = 0;
$rootredirchain = '';
$noofHTML5MediaElements = 0;
$boolakamaiDebug = false;
$http_codes = '';
$maxcsschaindepth = 0;
$csschaindepth = 0;
$browserEngineVer = '';
$amplience_dynamic_images_found = false;
$amplience_dynamic_images_strip = 0;
$amplience_dynamic_images_stripnone = 0;
$amplience_dynamic_images_chroma = 0;
// db
$dbcon = '';
$dbusage = false;
// har
$uploadedHAR = false;
$uploadedHARFileName = '';
$wptHAR = false;
$chhHAR = false;
//
$boolNewCookieSessionSet = false;
// arrays for simple lists
$arrayListOfImages = array();
$arrayListOfStylesheets = array();
$arrayListOfScriptFiles = array();
$arrayListOf3PImages = array();
$arrayListOf3PStylesheets = array();
$arrayListOf3PScriptFiles = array();
$arrayListOfLinks = array();
$arrayListOfImageLinks = array();
$rootStyleID = array();
$rootStyleClass = array();
$rootStyles = array();
$rootElements = array();
// arrays for json records
$arrayPageStats = array();
$arrayFileStats = array();
$arrayFileListStats = array();
$arrayGZIPStats = array();
$arrayTotals = array();
$arrayOfLinks = array();
$arrayOfObjects = array();
$arrayOf3PObjects = array();
$arrayOfTimings = array();
$arrayOrderedCSSJS = array();
$arrayErrors = array();
$arrayDomains = array();
$arrayOfTests = array();
$arrayOfRules = array();
$arrayOfCSSSelectors = array();
// array for objects
$arrayPageObjects = array();
$arrayPageHeaders = array();
$arrayImageData = array();
$arrayCacheAnalysis = array();
$arrayRootRedirs = array();
$arrayOtherRedirs = array();
$arrayTagManagers = array();
$arrayHost3PFiles = array();
$arrayThirdPartyChain = array();
$arrayPostData = array();
//rule checks
$boolCDNused = false;


function debug($info1, $info2 = '')
{
    global $debug, $filepath_domainsavedir;
    if (is_array($info2))
        $info2 = implode($info2);
    $info2 = (string) $info2; // force a string
    if ($debug == true)
    {
        echo date("H:i:s") . " " . $info1 . ": " . $info2 . "<br/>";
        error_log($info1 . ": " . $info2);
    }
//file_put_contents($filepath_domainsavedir."\\debug.htm", $info1.": ".$info2."<br/>", FILE_APPEND);
}


function diagnostics($info1, $info2, $info3)
{
    global $diagnostics;
    $diagnostics = $diagnostics . $info1 . " - " . $info2 . " - " . $info3 . "<br/>";
}


function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++)
    {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function get_Datetime_Now()
{
    $tz_object = new DateTimeZone('UTC');
    $datetime = new DateTime();
    $datetime->setTimezone($tz_object);
    return $datetime->format('Y\-m\-d\ H:i:s');
}


function getStyleIDandClasess($initurl)
{
    debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $initurl);
    global $html, $debug, $rootStyleID, $rootStyleClass;
    debug("<br/>PROCESSING STYLE ID", "");
//echo("<br/>PROCESSING STYLE TAGS<br/>");
    foreach ($html->find('*[id]') as $id)
    {
        $attr = $id->attr;
        $rootStyleID[] = $attr['id'];
    }
    foreach ($html->find('*[class]') as $class)
        {
            $attr = $class->attr;
//$rootStyleClass[] = $attr['class'];
            $styles = explode(' ', $attr['class']);
            foreach ($styles as & $value)
            {
                if (in_array($value, $rootStyleClass))
                {
//echo "Got class of ".$value;
                }
                else
                {
                    if ($value != '')
                        $rootStyleClass[] = $value;
                }
            }
        }
// recursive function to find element selectors


        $html_e = $html->find("html", 0);
        tdom($html_e);
        return true;
    }


    function tdom($html_el)
    {
        global $rootElements;
        if (isset($html_el))
        {
            foreach ($html_el->children() as $child1)
            {
//print $child1->tag . "<br/>";
                $t = $child1->tag;
                if (in_array($t, $rootElements) == false)
                    $rootElements[] = $t;
                tdom($child1);
            }
        }
    }


    function getRootDomainAndSubDomains($testArray)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $testArray);
        global $initdomain, $initsubdomain;
//error_log(__FUNCTION__ . ' ' .__LINE__ . " testarray: ". $testArray);
        if (isset($testArray))
        {
//echo ("getting subdomains for '".$testArray. "'<br/>");
            foreach ($testArray as $k => $v)
            {
                $initsubdomain = extract_subdomains($v);
                $initdomain = extract_domain($v);
            }
            debug("Sub-Domain", $initsubdomain);
            debug("Domain", $initdomain);
        }
    }


    function get_SourceURL($url)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url);
        global $debug;
        debug("Func get_SourceURL", $url);
//echo "get_SourceURL: ",$url."<br/>";
        $u = parseUrl($url);
        if(empty($u))
            $u = parse_url($url);
        @ $h = $u["host"];
        if(isset($u["subdomain"]))
            @ $subd = $u["subdomain"];
        else
            $subd = '';
        if(isset($u["domain"]))
            @ $d = $u["domain"];
        else
            $d = '';
        if (isset($u["path"]))
            $path = $u["path"];
        else
            $path = '';
        $pu = parse_url($url);
        if(isset($pu["scheme"]))
            @ $s = $pu["scheme"];
        else
            $s = "http";
        if (isset($u["file"]))
            $f = $u["file"];
        else
            $f = '';
        if (isset($u["query"]))
            $q = $u["query"];
        else
            $q = '';
        if (isset($u["port"]))
            $port = $u["port"];
        else
            $port = '';
// get full querystring
        $qpos = strpos($url, "?");
        if ($qpos != false)
            $q = substr($url, $qpos + 1);
// get pure path - remove file from path
        $upl = strlen($path);
        $ufl = strlen($f);
//echo "<br/>path length: ".$upl."<br/>";
//echo "<br/>file length: ".$ufl."<br/>";
//check if there is any dirs or file
        if ($path == '')
        {
            if ($debug == true)
                echo "no dirs or path " . $path . "; file: " . $f . "<br/>";
// no dirs or file
            $dirs = '';
            $f = '';
        }
        else
        {
// check if the filepath = the whole of the dirs
            if ($path == "/" . $f)
            {
// either there is no dir or there is only a file
                if ($debug == true)
                    echo "path " . $path . " = file: " . $f . "<br/>";
                $posdot = strpos($path, ".");
                if ($posdot > 0)
                {
// its a file
                    $dirs = '';
                }
                else
                {
// its a direcrory
                    $dirs = $f;
                    $f = '';
                }
            }
            else
            {
//dirs and file need to be split
                $dirs = substr($path, 1, $upl - $ufl - 2);
            }
        }
        if ($debug == true)
        {
            echo "Scheme: " . $s . "<br/>";
            echo "Host: " . $h . "<br/>";
            echo "Domain: " . $d . "<br/>";
            echo "SubDomain: " . $subd . "<br/>";
            echo "Port: " . $port . "<br/>";
            echo "Path: " . $path . "<br/>";
            echo "File: " . $f . "<br/>";
            echo "Dirs: " . $dirs . "<br/>";
            echo "Querystring: " . $q . "<br/>";
        }
        $arr = array("scheme" => $s, "host" => $h, "domain" => $d, "subdomain" => $subd, "port" => $port, "path" => $path, "file" => $f, "dirs" => $dirs, "querystring" => $q);
        $sourceURL = $arr;
        if ($debug == true)
        {
//echo "<pre>";
//print_r ( $sourceURL );
//echo "</pre>";
//
//$diagnostics = (parse_url($url));
//echo "<pre>";
//print_r ( $diagnostics );
//echo "</pre>";
//
        }
        return $sourceURL;
    }


    function getDestFilepathnameFromURL($url)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url);
        $filepathname = '';
        $path_parts = pathinfo($url);
//echo $path_parts['dirname'], "\n";
//echo $path_parts['basename'], "\n";
//echo $path_parts['extension'], "\n";
//echo $path_parts['filename'], "\n"; // since PHP 5.2.0
        return $filepathname;
    }


/* gets the data from a URL */
    function get_3pdatafile($url)
    {
        $ch = curl_init();
        $timeout = 15;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    function getURLFromCURL()
    {
        debug(__FUNCTION__ . ' ' . __LINE__);
// after a CURL request is made, the final redirected URL is given
        global $curl_info;
        return $curl_info['url'];
    }


    function read_header($ch, $string)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $ch . "; " . $string);
        global $curlheader;
        if (strlen($string) > 2)
        {
            debug("Received a " . strlen($string) . " byte header", $string . "<br/>");
//echo("Received a ".strlen($string)." byte header: " . $string."<br/>");
            $curlheader[] = $string;
        }
        return strlen($string);
    }


    function read_headerNoFollow($ch, $string)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $ch . "; " . $string);
        global $curlheader;
        if (strlen($string) > 2)
        {
            debug("Received a " . strlen($string) . " byte header", $string . "<br/>");
//echo("Received a ".strlen($string)." byte header: " . $string."<br/>");
            $curlheader[] = $string;
        }
        return strlen($string);
    }


    function readURLandSaveToFilePath($url, $sfn)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url . "; " . $sfn);
        global $result, $ua, $curlheader, $cookie_jar, $username, $password, $boolakamaiDebug, $boolNewCookieSessionSet,$fullurlpath,$encodingoptions;
        debug('<br/>function readURLandSaveToFilePath called for ', $url . '<br/> the returned headers and body will be saved to ' . $sfn . '<br />');
//echo('<br/>function readURLandSaveToFilePath called for '.$url. '<br/> saving file to '.$sfn.'<br />');
//echo ("opening file $url: $sfn<br/>");
// replace misconfigured paramters ?& with ?
        $str = str_replace("?&amp;", "?", $url, $count);
        if ($count > 0)
            error_log("replaced misconfigured query string parm ?& . " . $url);
// htmlspecialchars_decode � Convert special HTML entities back to characters
        $url = htmlspecialchars_decode($url);
        $curlheader = array();
        $fp = fopen($sfn, "w");
        $method = 'GET';
        $auth = '';
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions;
        $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
        $akamaiDebugLocOnly = 'Pragma: akamai-x-cache-on';
        if ($boolakamaiDebug == false)
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebugLocOnly);
        else
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebug);
// set some cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($boolNewCookieSessionSet == false)
        {
            curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
            $boolNewCookieSessionSet = true;
        }
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
        $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_REFERER, $fullurlpath);
//curl_setopt($ch, CURLOPT_COOKIE, $cookies);
//if(strpos($url,"FileMerge") != false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
        curl_setopt($ch, CURLOPT_FILE, $fp);
//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        if ($username != '' and $password != '')
        {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers
        curl_setopt($ch, CURLOPT_ENCODING, $encodingoptions);
        $result = curl_exec($ch);
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
//echo "REQUEST HEADER<br/>".$headerSent."<br/>";
        debug("REQUEST HEADER 1", $headerSent);
// Check if any error occurred
        if (!$ch)
        {
            die("Couldn't initialize a CURL handle");
        }
        
        if (empty($result) or !$result)
        {
// some kind of an error happened
            adderrors($url, 'Curl error: ' . curl_error($ch));
//echo("curl error getting $url <br/>");
            $errno = curl_errno($ch);
            $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
            debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
            //echo "ch: " . $ch . PHP_EOL;
            
            if($errno == 61 or $errno == 23)
            {
              // echo ("Brotli decoding was not supported CURL error $url ({$errno}): " . " {$error_message}  - " . $url);
               echo ("Brotli was available from the host but decoding was not supported by the version of CURL on this server.");
                    // can't decode brotli in the current version of curl, so remove it
                    $encodingoptions = "gzip,deflate";
                    // and retry request again without brotli
                    list($curl_info, $curlheader) = readURLandSaveToFilePath($url, $sfn);
            }
            else
            {
                echo ("CURL error $url ({$errno}): " . " {$error_message}  - " . $url);
                die(curl_error($ch));
            }
        }
        else
            if ($errno = curl_errno($ch))
            {
                $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
                debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
             //   adderrors($url, 'Curl error: ' . $error_message);
            }
            else
            {
                    $curl_info = curl_getinfo($ch);
            }
        $dlfilesize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $dlstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// close file connection
        fclose($fp);
        curl_close($ch); // close cURL handler
//echo (readURLandSaveToFilePath . " : ".$url."<pre>");
//print_r ($curlheader);
//echo ("</pre>");
// if content size was zero, get cokie and retry
        if ($dlfilesize == 0 and $dlstatuscode == "200")
        {
//echo("retrying download for failed file: " . $url . "<br>");
//echo("Response headers for file<pre>");
//print_r ($curlheader);
//echo ("</pre>");
            $extracookies = '';
            foreach ($curlheader as $value)
            {
//echo("Response header for file: ".$value."<br>");
                if (strpos($value, "Cookie") != false)
                {
//echo("Response header Set cookie for file: ".$value."<br>");
                    $colonpos = strpos($value, ":");
                    $semicolonpos = strpos($value, ";");
                    $expiresnpos = strpos($value, "; expires");
                    $domainonpos = strpos($value, "; Domain");
                    $pathpos = strpos($value, "; path");
                    $extracookies = $extracookies . substr($value, $colonpos + 1, $semicolonpos - 1 - $colonpos) . ";";
                }
            }
            readURLWithExtraCookieandSaveToFilePath($url, $sfn, $extracookies);
        }
        debug('<br/>END function readURLandSaveToFilePath called for ', $url . '; <br/> saved file to ' . $sfn . '<br />');
        return array($curl_info, $curlheader);
    }


    function readURLandSaveToFilePathNoFollow($url, $sfn)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url . "; " . $sfn);
        global $result, $ua, $curlheader, $cookie_jar, $username, $password, $boolakamaiDebug, $boolNewCookieSessionSet,$fullurlpath,$encodingoptions;
        debug('<br/>function readURLandSaveToFilePathNoFollow called for ', $url . '<br/> the returned headers and body will be saved to ' . $sfn . '<br />');
//echo('<br/>function readURLandSaveToFilePath called for '.$url. '<br/> saving file to '.$sfn.'<br />');
//echo ("opening file $url: $sfn<br/>");
        $url = htmlspecialchars_decode($url);
        $curlheader = array();
        $fp = fopen($sfn, "w");
        $method = 'GET';
        $auth = '';
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions;
        $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
        $akamaiDebugLocOnly = 'Pragma: akamai-x-cache-on';
        if ($boolakamaiDebug == false)
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebugLocOnly);
        else
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebug);
// set some cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($boolNewCookieSessionSet == false)
        {
            curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
            $boolNewCookieSessionSet = true;
        }
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
//$ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//$ret = curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_headerNoFollow');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_REFERER, $fullurlpath);
//curl_setopt($ch, CURLOPT_COOKIE, $cookies);
//if(strpos($url,"FileMerge") != false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
        curl_setopt($ch, CURLOPT_FILE, $fp);
//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        if ($username != '' and $password != '')
        {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers
        curl_setopt($ch, CURLOPT_ENCODING, $encodingoptions);
        $result = curl_exec($ch);
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
//echo "REQUEST HEADER<br/>".$headerSent."<br/>";
        debug("REQUEST HEADER 2", $headerSent);
// Check if any error occurred
        if (!$ch)
        {
            die("Couldn't initialize a CURL handle");
        }
        if (empty($result) or !$result)
        {
// some kind of an error happened
         //   adderrors($url, 'Curl error: ' . curl_error($ch));
//echo("curl error getting $url <br/>");
            $curl_info = false;
            $errno = curl_errno($ch);
            $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
            debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
//die(curl_error($ch));
        }
        else
            if ($errno = curl_errno($ch))
            {
                $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
                debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
            //    adderrors($url, 'Curl error: ' . $error_message);
            }
            else
            {
                $curl_info = curl_getinfo($ch);
        }
        $dlfilesize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $dlstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// close file connection
        fclose($fp);
        curl_close($ch); // close cURL handler
//    echo ("readURLandSaveToFilePathNoFollow" . " : ".$url."<pre>");
//    print_r ($curlheader);
//    echo ("</pre>");
// if content size was zero, get cokie and retry
        if ($dlfilesize == 0 and $dlstatuscode == "200")
        {
//echo("retrying download for failed file: " . $url . "<br>");
//echo("Response headers for file<pre>");
//print_r ($curlheader);
//echo ("</pre>");
            $extracookies = '';
            foreach ($curlheader as $value)
            {
//echo("Response header for file: ".$value."<br>");
                if (strpos($value, "Cookie") != false)
                {
//echo("Response header Set cookie for file: ".$value."<br>");
                    $colonpos = strpos($value, ":");
                    $semicolonpos = strpos($value, ";");
                    $expiresnpos = strpos($value, "; expires");
                    $domainonpos = strpos($value, "; Domain");
                    $pathpos = strpos($value, "; path");
                    $extracookies = $extracookies . substr($value, $colonpos + 1, $semicolonpos - 1 - $colonpos) . ";";
                }
            }
//readURLWithExtraCookieandSaveToFilePathNoFollow($url,$sfn,$extracookies);
        }
        debug('<br/>END function readURLandSaveToFilePathNoFollow called for ', $url . '; <br/> saved file to ' . $sfn . '<br />');
        return array($curl_info, $curlheader);
    }


    function readURLWithExtraCookieandSaveToFilePath($url, $sfn, $extracookies)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url . "; " . $sfn);
        global $result, $ua, $curlheader, $cookie_jar, $username, $password, $boolakamaiDebug, $boolNewCookieSessionSet,$fullurlpath,$encodingoptions;
        debug('<br/>readURLWithExtraCookieandSaveToFilePath called for ', $url . '<br/> the returned headers and body will be saved to ' . $sfn . '<br/>');
//echo('<br/>function readURLWithExtraCookieandSaveToFilePath '.$url. '<br/> saving file to '.$sfn.'<br />');
//echo ('extra cookies set: ' . $extracookies.'<br>');
//echo ("opening file $url: $sfn<br/>");
        $url = htmlspecialchars_decode($url);
        $curlheader = array();
        $fp = fopen($sfn, "w");
        $method = 'GET';
        $auth = '';
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions;
        $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
        $akamaiDebugLocOnly = 'Pragma: akamai-x-cache-on';
        if ($boolakamaiDebug == false)
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebugLocOnly);
        else
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebug);
// set some cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($boolNewCookieSessionSet == false)
        {
            curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
            $boolNewCookieSessionSet = true;
        }
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
        $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_REFERER, $fullurlpath);
//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
//curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie_jar);
        curl_setopt($ch, CURLOPT_FILE, $fp);
//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        if ($username != '' and $password != '')
        {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers
        curl_setopt($ch, CURLOPT_ENCODING, $encodingoptions);
        $result = curl_exec($ch);
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
//echo "REQUEST HEADER on retry<br/>".$headerSent."<br/>";
        debug("REQUEST HEADER 3", $headerSent);
// Check if any error occurred
        if (!$ch)
        {
            die("Couldn't initialize a CURL handle");
        }
        if (empty($result) or !$result)
        {
// some kind of an error happened
          //  adderrors($url, 'Curl error: ' . curl_error($ch));
//echo("curl error getting $url <br/>");
            $curl_info = false;
            $errno = curl_errno($ch);
            $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
            debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
//die(curl_error($ch));
        }
        else
            if ($errno = curl_errno($ch))
            {
                $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
                debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
          //      adderrors($url, 'Curl error: ' . $error_message);
            }
            else
            {
                $curl_info = curl_getinfo($ch);
        }
        $dlfilesize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $dlstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// close file connection
        fclose($fp);
        curl_close($ch); // close cURL handler
//echo("Response headers for file retry<pre>");
//print_r ($curlheader);
//echo ("</pre>");
        debug('<br/>END function readURLandSaveToFilePath called for ', $url . '; <br/> saved file to ' . $sfn . '<br />');
        return array($curl_info, $curlheader);
    }


    function readURLWithExtraCookieandSaveToFilePathNoFollow($url, $sfn, $extracookies)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url . "; " . $sfn);
        global $result, $ua, $curlheader, $cookie_jar, $username, $password, $boolakamaiDebug, $boolNewCookieSessionSet,$fullurlpath,$encodingoptions;
        debug('<br/>readURLWithExtraCookieandSaveToFilePathNoFollow called for ', $url . '<br/> the returned headers and body will be saved to ' . $sfn . '<br/>');
//echo('<br/>function readURLWithExtraCookieandSaveToFilePath '.$url. '<br/> saving file to '.$sfn.'<br />');
//echo ('extra cookies set: ' . $extracookies.'<br>');
//echo ("opening file $url: $sfn<br/>");
        $url = htmlspecialchars_decode($url);
        $curlheader = array();
        $fp = fopen($sfn, "w");
        $method = 'GET';
        $auth = '';
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions;
        $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
        $akamaiDebugLocOnly = 'Pragma: akamai-x-cache-on';
        if ($boolakamaiDebug == false)
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebugLocOnly);
        else
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebug);
// set some cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($boolNewCookieSessionSet == false)
        {
            curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
            $boolNewCookieSessionSet = true;
        }
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
//$ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_headerNoFollow');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_REFERER, $fullurlpath);
//if(strpos($url,"FileMerge") != false);
//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
//curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie_jar);
        curl_setopt($ch, CURLOPT_FILE, $fp);
//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        if ($username != '' and $password != '')
        {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers
        curl_setopt($ch, CURLOPT_ENCODING, $encodingoptions);
        $result = curl_exec($ch);
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
//echo "REQUEST HEADER on retry<br/>".$headerSent."<br/>";
        debug("REQUEST HEADER 4", $headerSent);
// Check if any error occurred
        if (!$ch)
        {
            die("Couldn't initialize a CURL handle");
        }
        if (empty($result) or !$result)
        {
// some kind of an error happened
         //   adderrors($url, 'Curl error: ' . curl_error($ch));
//echo("curl error getting $url <br/>");
            $curl_info = false;
            $errno = curl_errno($ch);
            $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
            debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
//die(curl_error($ch));
        }
        else
            if ($errno = curl_errno($ch))
            {
                $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
                debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
             //   adderrors($url, 'Curl error: ' . $error_message);
            }
            else
            {
                $curl_info = curl_getinfo($ch);
        }
        $dlfilesize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $dlstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// close file connection
        fclose($fp);
        curl_close($ch); // close cURL handler
//echo("Response headers for file<pre>");
//print_r ($curlheader);
//echo ("</pre>");
        debug('<br/>END function readURLandSaveToFilePathNoFollow called for ', $url . '; <br/> saved file to ' . $sfn . '<br />');
//echo('<br/>END function readURLandSaveToFilePathNoFollow called for ' . $url. '; <br/>status code=' . $dlstatuscode . ' filesize=' . $dlfilesize. '; saved file to '.$sfn.'<br />');
        return array($curl_info, $curlheader);
    }


    function readURLandSaveToFilePathOnly($url, $sfn)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $url . "; " . $sfn);
        global $result, $ua, $curlheader, $cookie_jar, $username, $password, $boolakamaiDebug, $boolNewCookieSessionSet,$fullurlpath,$encodingoptions;
        debug('<br/>function readURLandSaveToFilePath called for ', $url . '<br/> the returned headers and body will be saved to ' . $sfn . '<br />');
//echo('<br/>function readURLandSaveToFilePath called for '.$url. '<br/> saving file to '.$sfn.'<br />');
//echo ("opening file $url: $sfn<br/>");
// replace misconfigured paramters ?& with ?
        $str = str_replace("?&amp;", "?", $url, $count);
        if ($count > 0)
            error_log("replaced misconfigured query string parm ?& . " . $url);
// htmlspecialchars_decode � Convert special HTML entities back to characters
        $url = htmlspecialchars_decode($url);
        $curlheader = array();
        $fp = fopen($sfn, "w");
        $method = 'GET';
        $auth = '';
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions;
        $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
        $akamaiDebugLocOnly = 'Pragma: akamai-x-cache-on';
        if ($boolakamaiDebug == false)
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebugLocOnly);
        else
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebug);
// set some cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($boolNewCookieSessionSet == false)
        {
            curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
            $boolNewCookieSessionSet = true;
        }
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
        $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_REFERER, $fullurlpath);
//curl_setopt($ch, CURLOPT_COOKIE, $cookies);
//if(strpos($url,"FileMerge") != false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
        curl_setopt($ch, CURLOPT_FILE, $fp);
//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        if ($username != '' and $password != '')
        {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers
        curl_setopt($ch, CURLOPT_ENCODING, $encodingoptions);
        $result = curl_exec($ch);
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
//echo "REQUEST HEADER<br/>".$headerSent."<br/>";
        debug("REQUEST HEADER 5", $headerSent);
// Check if any error occurred
        if (!$ch)
        {
            die("Couldn't initialize a CURL handle");
        }
        if (empty($result) or !$result)
        {
// some kind of an error happened
          //  adderrors($url, 'Curl error: ' . curl_error($ch));
//echo("curl error getting $url <br/>");
            $curl_info = false;
            $errno = curl_errno($ch);
            $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
            debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
//die(curl_error($ch));
        }
        else
            if ($errno = curl_errno($ch))
            {
                $error_message = curl_strerror($errno);
//echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");
                debug("CURL error $url ({$errno}): " . " {$error_message}  - ", $url);
            //    adderrors($url, 'Curl error: ' . $error_message);
            }
            else
            {
                $curl_info = curl_getinfo($ch);
        }
        $dlfilesize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $dlstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// close file connection
        fclose($fp);
        curl_close($ch); // close cURL handler
//echo (readURLandSaveToFilePath . " : ".$url."<pre>");
//print_r ($curlheader);
//echo ("</pre>");
// if content size was zero, get cokie and retry
        if ($dlfilesize == 0 and $dlstatuscode == "200")
        {
//echo("retrying download for failed file: " . $url . "<br>");
//echo("Response headers for file<pre>");
//print_r ($curlheader);
//echo ("</pre>");
            $extracookies = '';
            foreach ($curlheader as $value)
            {
//echo("Response header for file: ".$value."<br>");
                if (strpos($value, "Cookie") != false)
                {
//echo("Response header Set cookie for file: ".$value."<br>");
                    $colonpos = strpos($value, ":");
                    $semicolonpos = strpos($value, ";");
                    $expiresnpos = strpos($value, "; expires");
                    $domainonpos = strpos($value, "; Domain");
                    $pathpos = strpos($value, "; path");
                    $extracookies = $extracookies . substr($value, $colonpos + 1, $semicolonpos - 1 - $colonpos) . ";";
                }
            }
            readURLWithExtraCookieandSaveToFilePath($url, $sfn, $extracookies);
        }
        debug('<br/>END function readURLandSaveToFilePath called for ', $url . '; <br/> saved file to ' . $sfn . '<br />');
        return array($curl_info, $curlheader);
    }


function readFromHARandSaveToFilePath($requrl,$sourcefileNoSpaces,$sfn)
    {
        global $har,$harjson,$browserengine,$body,$arrayPostData;
        foreach ($harjson['log'] as $logtype => $logval)
        {
//echo(__FUNCTION__ . " har<code> ".$logtype . " ". $logval->text."</code><br/>");
            switch ($logtype)
                {
                    case 'pages':
                    foreach ($logval as $key => $value) {
                        //echo("PAGES log level 1: ".$key . " ". $value);
                        $evalue =json_encode($value);
                        //echo "startedDateTime: ".$value['startedDateTime']."<br/>";
                        //echo "render: ".$value['_render']."<br/>";
                        // add render time to array
                        if($browserengine == 6) // WPT only
                        {
                            $rst = $value['_render'];
                            $onLoad = $value['_docTime'];
                            $domLoadStart = $value['_domContentLoadedEventStart'];
                            $domLoadEnd = $value['_domContentLoadedEventEnd'];
                            $doct = $value['_fullyLoaded'];
                            
                        }
                        else
                        {
                            $rst = 0;
                            $doct = 0;
                            $onLoad = 0;
                            $domLoadEnd = 0;
                        }
                        $arr = array("renderms"=> $rst);
                        $rstime_ms = $rst;
                        $domCompletetime_ms = $domLoadEnd;
                        $rstime_sec = $rstime_ms/1000;
                        $onload_ms = $onLoad;
                        $docTime_ms = $doct;
                        $scoreArray[] = $arr;
                //        addStatToFileListAnalysis(number_format($rstime_sec,3), "Seconds", "Render Start Time", "info");
                    }
                        break;
                  case 'entries':
                    $pjsObjCnt = 0;
                    $pjsObjCntExisting = 0;
                    $pjsObjCntNew = 0;
                    $pjsredircount = 0;
                    $starturl = '';
                    $endurl = '';
                    foreach ($logval as $key => $value) {
//echo("log level 1: ".$key . " ". implode($value)."<br/>");
                        $evalue =json_encode($value);

                        // echo "request array: ".implode($value['request'])."<br/>";
                        // echo "response array: ".implode($value['response'])."<br/>";
                        // echo "cache array: ".implode($value['cache'])."<br/>";
                        // echo "timings array: ".implode($value['timings'])."<br/>";
                        $request =$value['request'];
                        $response =$value['response'];
                        $ObjURL = $value['_full_url'];
                        @$timings = $value['timings'];


// if(strpos($requrl,"google") != -1 and strpos($ObjURL,"google") != -1)
//                         echo($requrl . " -?- " . $ObjURL . "<br/>");

                        if($requrl != html_entity_decode($ObjURL))
                        {
                            continue;
                        }

//if(strpos($requrl,"google") != false and strpos($ObjURL,"google") != false)
//echo("URL MATCH ". $requrl . " === " . $ObjURL . "<br/>");

                        // page data
                        $pageref = $value['pageref'];
                        $filedatetime = $value["startedDateTime"];

                        // response data
                        $httpstatus = $response['status'];
                        $responsestatustext = $response['statusText'];
                        $responsehttpversion = $response['httpVersion'];
                        $responseContent = $response['content'];
                        $responseCookies = $response['cookies'];
                        $responseRedirecturl = $response['redirectURL'];
                        $responseHeadersSize = $response['headersSize'];
                        $responseHeaders = $response['headers'];
                        $responseBodySize = $response['bodySize'];
                        $responseTransferSize = $response['_transferSize'];
                        $responseContentSize = $responseContent['size'];
                        $responseObjectSize = $response['_objectSize'];
                        $responseContentMimetype = $responseContent['mimeType'];
                        $responseContentCompression = $responseContent['compression'];
                        $responseContentText = $responseContent['text'];

                        $responseHdrContentLength = $responseHeaders['Content-Length'];

                        // request data
                        $requestHeaders = $request['headers'];
                        @$requestContent = $request['content'];
                        $requestMethod = $request['method'];
                        $requestHeadersSize = $request['headersSize'];
                        $requestQueryString = $request['queryString'];
                        $requestPostData = $request['postData'];
                        $requestBodySize = $request['bodySize'];


                        // get WPT values for timings
                        if($browserengine == 6 or $browserengine == 8)
                        {
                            $requestStartMS = $value['_ttfb_start'];
                            $ttfbMS = $value['_ttfb_ms'];
                            $contentDownloadMS = $value['_download_ms'];
                            $allMS = $value['_all_ms'];
                            $allStartMS = $value['_all_start'];
                            $allEndMS = $value['_all_end'];
                            $cacheTime = $value['_cache_time'];
    //echo ($ObjURL . "; allms =  " . $allMS . ";<br/>");
                        }
                        else
                        {
                        $requestStartMS = 0;
                        $ttfbMS = 0;
                        $contentDownloadMS = 0;
                        $allMS = 0;
                        $allStartMS = 0;
                        $allEndMS = 0;
                        $cacheTime = 0;
                        }
//echo ("processing request headers<br/>");
                        // // Process request
                        foreach ($requestHeaders as $reqhdrkey => $reqhdrvalue) {
//echo ("req hdr key ". $reqhdrkey. " ".implode($reqhdrvalue)."<br/>");

                        //     if($reqhdrvalue['name'] == 'Referer')
                        //     {
                        //         $referer = $reqhdrvalue['value'];
                        //         continue;
                        //     }
                        //     else
                        //         $referer = '';
                        //     if($reqhdrvalue['name'] == 'Content-Type')
                        //         $mimetype = $reqhdrvalue['value'];
                        }
                        
                        
                        // process request post data
                        if($requestMethod == "POST" and $requestBodySize > 0)        
                        {
//echo ("<br/>".$ObjURL . " - processing request body postdata " . $requestMethod  . " of " . $requestBodySize. " bytes<br/>");
//echo "POST parameters: " . implode($requestPostData) . "<br/>";
                            
                           

//                             foreach ($requestPostData as $reqpostkey => $reqpostvalue)
//                             {
                                
//echo ("req postdata key ". $reqpostkey. " ".$reqpostvalue."<br/>");
//                                 if($reqpostkey == "text")
//                                 {
//                                     $reqpostdata = json_decode($reqpostvalue, true);
//                                     foreach ($reqpostdata as $pdkey => $pdvalue)
//                                     {
// echo ($pdkey. " ".$pdvalue."<br/>");  
//                                     }
//                                 }
//                          }
                        array_push($arrayPostData,  array("url" => $ObjURL, "postData" => $requestPostData)); //
                        }// end if a post request with data

                        // process response
                        // echo "<br/>entry $key<br/>";
                        // echo "startedDateTime: ".$value['startedDateTime']."<br/>";
                        // echo "time: ".$value['time']."<br/>";
//echo "request url: ".$ObjURL."<br/>";

//echo ("processing response headers for " . $ObjURL . "<br/>");
                        // process response headers
                        $curlheader = array();
                        $hdrcount = 0;
                        $curlheader += array($hdrcount => $responsehttpversion . " " . $httpstatus . " " . $responsestatustext);
                        foreach ($responseHeaders as $reshdrkey => $reshdrvalue)
                            {
                               $hdrcount++;
//echo ("response hdr key ". $reshdrkey. " ".implode($reshdrvalue)."<br/>");
                               $curlheader += array($hdrcount => $reshdrvalue['name'] . ": " . $reshdrvalue['value']);
                            }


                        // echo "response status code: ".$httpstatus. " " . $responsestatustext . "<br/>";
                        // echo "pageref: ".$pageref."<br/>";
                        // if ($referer != '')
                        //    echo "referer: ".$referer."<br/>";

                        // charles object size
                        if($responseTransferSize == 0)
                        $responseTransferSize = $responseContentSize;
                        // wpt object size
                        if(!$responseTransferSize)
                            $responseTransferSize = $responseObjectSize;

                        // simulate a curl_info response
                        $curl_info = array();
                        $curl_info += array("url" =>$ObjURL,
                            "content_type" => $responseContentMimetype,
                            "http_code" =>$httpstatus,
                            "header_size" => $responseHeadersSize,
                            "request_size" => $requestHeadersSize,
                            "filetime" => $filedatetime,
                            "download_content_length" => $responseHdrContentLength,
                            "size_download" => $responseTransferSize,

                        );

                        $sfn=addslashes($sfn);
                    //    $sfn=str_replace("\\","/",$sfn);
                        $responseContentText = trim($responseContentText,'"');
                        $body = $responseContentText;
                        // extract the response content
                        if($httpstatus == 200)
                        {
// echo("200 status response for " . $responseContentMimetype . " size = " . $responseContentSize . "<br/>");
// echo "200 status CONTENT <code>";
// print_r($responseContentText);
// echo "</code>";

                            if($browserengine == 6 or $browserengine == 8) // WPT
                            {
                                // webpagetest stores text content outside of HAR and images are at the normal url
                                // get content from URL
                                if($value['_body_url'])
                                {
//echo ("retrieving content from wpt: //10.90.67.11" . $value['_body_url']."<br/>");
                                    $contentUrl = "http://10.90.67.11" .$value['_body_url'];
                                }
                                else
                                {
//echo ("retrieving content from web:" . $value['_full_url']."<br/>");
                                    $contentUrl = $value['_full_url'];
                                }
                                //    $responseContent = file_get_contents($contentUrl);

                                readURLandSaveToFilePathOnly($sfn,$contentUrl);



                                // $fp = fopen($sfn, 'w');
                                // $saveres = fwrite($fp, $responseContent);
                                // fclose($fp);
// echo "<br/>WPT 200 status CONTENT <code>";
// print_r($responseContent);
// echo "</code><br/>";


                            }
                            else
                            {
                                // save it, depending on mime type
                                switch ($responseContentMimetype)
                                {
                                    case "image/jpeg" :
                                    case "image/jpg" :
                                    case "image/x-bpg" :
                                    case "image/bpg" :
                                    case "image/gif" :
                                    case "image/png" :
                                    case "image/bmp" :
                                    case "image/tiff" :
                                    case "image/webp" :
                                    case "image/svg+xml" :
                                    case "application/x-font-woff" :
                                    case "font/woff2" :
                                    case "application/x-font-ttf" :
                                    case "application/x-font-truetype" :
                                    case "application/x-font-opentype" :
                                    case "application/vnd.ms-fontobject" :
                                    case "application/font-sfnt" :
                                    case "application/octet-stream" :
                                    case "image/x-icon":
    // echo "<br/>BASE64 CONTENT <code>";
    // print_r($responseContentText);
    // echo "</code><br/>";
                                    $responseContentText = str_replace(',"encoding": "base64"','',$responseContentText);
                                        // un base 64 encode the content
    //echo "extracting image - save file name = ".$sfn."<br/>" ;
                                        $decoded = base64_decode($responseContentText,true);
    // echo '<img src="data:' . $responseContentMimetype . ';base64,' . $responseContentText . '" />';
    // echo "<br/>DECODED CONTENT <code>";
    // print_r($decoded);
    // echo "</code><br/>";
                                        $fp = fopen($sfn, 'w');
                                        $saveres = fwrite($fp, $decoded);
                                        fclose($fp);
                                
    
                                        break;
                                    // case "application/javascript":
                                    // case "application/x-javascript":
                                    // case "text/javascript":
                                    // case "text/x-js":
                                    // case "text/html":
                                    // case "text/css":
                                    // case "text/xml" :
                                    // case "application/xml" :
                                    // case "application/json" :
                                    // case "text/plain" :
                                    // echo "save file name = ".$sfn."<br/>" ;
                                    // file_put_contents($sfn,$responseContentText);
                                    //     //$saveres = file_put_contents($sfn,$responseContentText);
                                    //     // echo "<br/>CONTENT <code>";
                                    //     // print_r($responseContentText);
                                    //     // echo "</code>";
                                    //     break;
                                    default:
                                //    echo "save file name = ".$sfn."<br/>" ;
                                    $saveres = file_put_contents($sfn,$responseContentText);
                                //    echo(file_get_contents($sfn));
                                        break;
                                }
                        //      echo ("<br/>" . $sfn . ": file save results bytes = " . $saveres . "<br/>");
                            } // end content saved in har file
                        } // end if 200 status code

                    } // end for each entry in the entries array (in switch statement)
                   break;
                 default:
            } // end switch

        } // end for har log reading
        //var_dump($harjson);
      // end reading of content from HAR from PhantomJS/SlimerJS/WPT

      //  echo ("HARfile object processed:<br/>");

    
        // echo "headers<pre>";
        // print_r($curlheader);
        // echo "</pre>";
        return array($curl_info, $curlheader);
    }

    function file_force_contents($filename, $data, $flags = 0){
        if(!is_dir(dirname($filename)))
            mkdir(dirname($filename).'/', 0777, TRUE);
        return file_put_contents($filename, $data,$flags);
    }


    function addStatToPageAnalysis($txt, $val)
    {
        global $arrayPageStats;
        $arr = array($txt => $val,);
        $arrayPageStats[] = $arr;
    }


    function addStatToFileAnalysis($txt, $val)
    {
        global $arrayFileStats;
        $arr = array($txt => $val,);
        $arrayFileStats[] = $arr;
    }


    function addStatToFileListAnalysis($val, $type, $txt, $state = 'info')
    {
        global $arrayFileListStats;
//echo("addstat ". $val. " " .$type . " " . $txt."<br/>");
        $arr = array('value' => $val, 'type' => $type, 'text' => $txt, 'state' => $state);
        $arrayFileListStats[] = $arr;
    }


    function addErrors($txt, $val)
    {
        global $arrayErrors;
        $arr = array($txt => $val,);
        $arrayErrors[] = $arr;
    }


    function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++)
        {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }


    function array_search_inner($array, $attr, $val, $strict = FALSE)
    {
// Error is input array is not an array
        if (!is_array($array))
            return FALSE;
        $arlen = count($array);
//echo ("size of page object array: " . $arlen."<br/>");
// Loop the array
        foreach ($array as $key => $inner)
        {
// Error if inner item is not an array (you may want to remove this line)
            if (!is_array($inner))
                return FALSE;
// Skip entries where search key is not present
            if (!isset($inner[$attr]))
                continue;
            $objlu = $inner[$attr];
            $lr = strlen($val);
            $la = strlen($objlu);
//if(strpos($val,'a7dfd6ec2ae348719eff75fb32fbbae2.ashx') > 0 and strpos($objlu,'a7dfd6ec2ae348719eff75fb32fbbae2.ashx') > 0)
//{
//	echo('<br/>obj lookup ('. $lr .'): "'.$val.'" against key '. $key.': (' . $la. ') "'.$objlu. '"<br/>');
//	echo(strtohex($val) . ' =?= '.strtohex($objlu));
//}
//$val = str_replace($val,"&amp;",'&');
//$objlu = str_replace($objlu,"&amp;",'&');
//if (strcmp($objlu, $val) !== 0) {
//	echo '$objlu is not equal to $val in a case sensitive string comparison';
//}
//else
//{
//  	echo '$objlu is equal to $val in a case sensitive string comparison';
//}
// decoded check necessary to prevent duplication
            if ($strict)
            {
// Strict typing
                if ($objlu === $val or html_entity_decode($objlu) === html_entity_decode($val))
                    return intval($key);
            }
            else
            {
// Loose typing
                if ($objlu == $val or html_entity_decode($objlu) == html_entity_decode($val))
                    return intval($key);
            }
        } // end loop
// We didn't find it
        return 'N/A';
    }


    function array_search_innerCSSJS($array, $attr, $val, $strict = FALSE)
    {
// Error is input array is not an array
        if (!is_array($array))
            return FALSE;
        $arlen = count($array);
//echo ("size of cssjs object array: " . $arlen."<br/>");
// Loop the array
        foreach ($array as $key => $inner)
        {
// Error if inner item is not an array (you may want to remove this line)
            if (!is_array($inner))
                return false;
//echo ($key . " - " . $inner[$attr]." - ".$val ."<br/>");
// Skip entries where search key is not present
            $objlu = $inner[$attr];
            if ($objlu === $val)
                return true;
        } // end loop
// We didn't find it
        return false;
    }


    function lookupPageObject($remoteurl)
    {
        global $arrayPageObjects;
        $found = false;
        $remoteurl = html_entity_decode(htmlentities($remoteurl));

// get key if it exists
        $skey = array_search_inner($arrayPageObjects, 'Object source', $remoteurl);
        if ($skey === false)
        {
//echo "Lookup page object: $remoteurl not found - error - may be empty<br/>";
        }
        else
            if (!is_numeric($skey))
            {
//echo "Lookup page object: $remoteurl not found<br/>";
                debug("Lookup page object NOT FOUND", $remoteurl . "; new object will be added");
            }
            else
            {
//echo "loookup: $remoteurl at $skey<br/>";
                $lfn = $arrayPageObjects[$skey]['Object file'];
//echo "Loookup: $remoteurl found at $skey; local file: $lfn at $skey<br/>";
                debug("Lookup page object FOUND", $remoteurl . " at " . $skey . "; local file: " . $lfn . " at " . $skey . "<br/>");
                $found = true;
        }
        if ($found == true)
        {
//echo ("lookupPageObject: found $skey: ".$lfn."<br/>");
            $arr = array($skey, $lfn);
        }
        else
        {
//echo ("lookupPageObject: not found<br/>");
            $arr = array("N/A", "N/A");
        }
        return ($arr);
    }


    function lookupPageObjectValue($remoteurl, $value)
    {
        global $arrayPageObjects;
        $found = false;
        $remoteurl = html_entity_decode(htmlentities($remoteurl));
// get key if it exists
        $skey = array_search_inner($arrayPageObjects, 'Object source', $remoteurl);
        if ($skey === false)
        {
//echo "Lookup page object: $remoteurl not found - error - may be empty<br/>";
        }
        else
            if (!is_numeric($skey))
            {
//echo "Lookup page object: $remoteurl not found<br/>";
                debug("LookupValue page object NOT FOUND", $remoteurl . "; new object will be added");
            }
            else
            {
//echo "loookup: $remoteurl at $skey<br/>";
                $lfn = $arrayPageObjects[$skey]['Object file'];
                $val = $arrayPageObjects[$skey][$value];
//echo "Loookup: $remoteurl found at $skey; local file: $lfn at $skey<br/>";
                debug("LookupValue page object FOUND", $remoteurl . " at " . $skey . "; " . $value . " " . $val . " at " . $skey . "<br/>");
                $found = true;
        }
        if ($found == true)
        {
//echo ("LookupValue found $skey: ".$val."<br/>");
            $arr = array($skey, $val);
        }
        else
        {
//echo ("not found<br/>");
            $arr = array("N/A", "N/A");
        }
        return ($arr);
    }


    function addImageData($url, $dtype, $inData)
    {
        global $arrayImageData;
        $url = html_entity_decode(htmlentities($url));
        list($id, $lfn) = lookupPageObject($url);
//echo('addimagedata: '.$url .' = ' . $dtype.'<pre>');
//print_r($inData);
//echo('<pre/>');
        switch ($dtype)
        {
            case 'XMP' :
                $unsafedata = implode("\r\n", $inData);
//echo $dtype.'<pre>';
//print_r($inData);
//echo '</pre>';
//echo 'safe: '.preg_replace('/[^\P{C}\s]+/u', '',$unsafedata)."<br/>";
//echo "Add image Data ($dtype): $url; ID = ". $id.": $inData<br/>";
                $safeData = preg_replace('/(?!\r\n)[\p{Cc}]/', '', $unsafedata);
//echo "Add image Data ($dtype): $url; ID = ". $id.": $safeData<br/>";
                break;
            case 'XMPstr' : // XMP nut not an array
//echo "Add image Data ($dtype): $url; ID = ". $id.": $inData<br/>";
                $safeData = $inData;
                $dtype = 'XMP';
                break;
            case 'IPTC' :
                $safeData = preg_replace('/(?!\r\n)[\p{Cc}]/', '', $inData);
                break;
            default :
                $safeData = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $inData);
        }
//$safeData = preg_replace('/(?!\n)[\p{Cc}]/', '', $inData	);
        if ($dtype == '')
        {
            echo "ERROR Addingimage Data ($dtype): $url; ID = " . $id . ": $inData<br/>";
            return false;
        }
//echo "Add image Data ($dtype): $url; ID = ". $id.": $safeData<br/>";
// new headers so add to array
        $arr = array("id" => $id, $dtype => utf8_encode($safeData),);
        $arrayImageData[] = $arr;
//echo $dtype.'<pre>';
//print_r($safeData);
//echo '</pre>';
    }


    function addPageHeaders($url, $inhdrs)
    {
        global $arrayPageHeaders;
        if (trim($url) == '')
        {
//echo("APH empty url<br/>");
            return;
        }
//echo "APH: CHECKING HEADERS 1 FOR ".$url."<br/>";
        $url = html_entity_decode(htmlentities($url));
//echo "APH: CHECKING HEADERS 2 FOR ".$url."<br/>";
        list($id, $lfn) = lookupPageObject($url);
        if (!is_numeric($id))
        {
//echo('APH: object not found in object table: '.$id.' for ' . $url."<br/>");
//list($id,$lfn) = lookupPageObject($url);
//if(!is_numeric($id))
//{
//	echo('retry; object not found in object table: '.$id.' for ' . $url."<br/>");
//	//die;
//}
        }
        else
        {
//echo "APH: CHECKING HEADERS FOR ".$url."; ID = ". $id."<br/>";
            $hdrsfound = false;
            foreach ($arrayPageHeaders as $key => $value)
            {
                $kid = $value['id'];
//echo ($kid. ": headers<br/>");
                if ($kid == $id)
                {
//echo ($kid. ": headers FOUND in APH<br/>");
                    $hdrsfound = true;
                    break;
                }
                else
                {
//echo ($key. ": headers NOT found in APH<br/>");
                }
            }
            if ($hdrsfound == false)
            {
//echo "APH: ADDING HEADERS FOR ".$url."; ID = ". $id."<br/>";
// new headers - add to array  for given url id
                $arr = array("id" => $id, "Headers" => $inhdrs);
                $arrayPageHeaders[] = $arr;
            }
        }
//if($id == 0)
//{
//echo "APH: $url; ID = ". $id."<br/>";
//echo ("APH Headers 0<pre>");
//print_r ( $inhdrs);
//echo ("</pre>");
//}
    }


    function addUpdatePageObject($inarr)
    {
        global $arrayPageObjects, $objCNT, $filepath_domainsavedir, $debug, $uastr;
        $ext = '';

//error_log( print_R($inarr,TRUE) );
        $remoteurl = $inarr["Object source"];
// strip unwanted entitied coded characters - linefeed = &#xA;
//    if(strpos("&#xA;",$remoteurl) != false)
//echo ("linefeed char found in url" . $remoteurl."<br/>" );
        $remoteurl = str_replace("&#xA;", "", $remoteurl);
// remove crlf chars
//$remoteurl = str_replace(chr(10),'',$remoteurl);
//$remoteurl = str_replace(chr(13),'',$remoteurl);
// remove leading and trailing spaves
        $remoteurl = trim($remoteurl);
        $remoteurl = html_entity_decode(htmlentities($remoteurl));
        $SETlocalfile = '';
        $localfile = '';
        if (isset($inarr["Object file"]))
        {
            $SETlocalfile = $inarr["Object file"];
//if($SETlocalfile != '')
//echo("Func addUpdatePageObject ".$remoteurl." -  ".$localfile."<br/>");
        }
        $initremoteurl = $remoteurl;
        debug("<br/>Func addUpdatePageObject", $remoteurl);
        if ($debug == true)
        {
            var_dump($inarr);
        }
// echo("Func addUpdatePageObject ".$remoteurl."<br/>");
// echo("Func addUpdatePageObject ".$localfile."<br/>");
// echo("content ".$localfile."<br/>");

//$pathparts = parse_url($remoteurl);
//$scheme = $pathparts['scheme'];
//$schemelen = strlen($scheme) + 1;
//$pathexceptscheme = substr($remoteurl,$schemelen);
// urlencode each part of the path
//$encodedurl = implode("/", array_map("rawurlencode", explode("/", $pathexceptscheme)));
//echo('encd url: '.$scheme.':'.$encodedurl."<br/>");
//$newsourceurl = $scheme.':'.$encodedurl;
        $newsourceurl = html_entity_decode(htmlentities($remoteurl));
        if (!$remoteurl)
        {
//echo("undefined remoteurl: " . $remoteurl);
            addErrors($remoteurl, "Bad reference to file");
            return false;
        }
//echo("<br/>defined remoteurl: " . $remoteurl."<br/>");
//echo("defined remoteurl ent: " . htmlentities($remoteurl)."<br/>");
//echo("defined remoteurl dec: " . html_entity_decode(htmlentities($remoteurl))."<br/>");
//find url in array get id
        $found = false;
        list($id, $lfn) = lookupPageObject($remoteurl);
//echo ("object lookup: id = $id, local = $lfn<br/>");
        if (is_numeric($id))
        {
//echo "addobject lookup - found - id = $id, local = $lfn<br/>";
            $found = true;
            $foundkey = $id;
        }
        else
        {
//echo "addobject lookup - not found: " . $id . ": source url = " . $remoteurl . "<br/>";
        }
        if(isset($inarr["File extension"]))
            $extn = $inarr["File extension"];
        else
            $extn = 'ext';
        if (substr($extn, 0, 1) == '.')
        {
//echo('removing prefix dot from extension before update<br>');
            $extn = substr($extn, 1);
        }
        if ($found == false)
        {
            debug("Adding source url to object array: ", $remoteurl);
            $qspos = strpos($remoteurl, '?');
            if ($qspos > 0)
                $checkstr = strtolower(substr($remoteurl, 0, $qspos));
            else
                $checkstr = $remoteurl;
//echo "AUP: checking for encoded file: ". $checkstr ."<br/>";
// get absolute name if not a base 64 file
            if (strpos($checkstr, "data:") !== false)
            {
//echo "<br/>."__FUNCTION__.": encoded file found: ". $checkstr ."<br/>";
                $sp = strpos($remoteurl, "data:") + 5;
                $posslash = strpos($remoteurl, "/", $sp);
                $b64basetype = substr($remoteurl, $sp, $posslash - $sp);
                $possemicolon = strpos($remoteurl, ";");
                $b64type = substr($remoteurl, $posslash + 1, $possemicolon - $posslash - 1);
//echo "base 64 basetype: " . $b64basetype . " - " . $b64type . "<br/>";
                switch ($b64type)
                {
                    case 'svg+xml' :
                        $b64ext = 'svg';
                        break;
                    case 'x-font-woff' :
                    case 'application/font-woff' :
                        $b64ext = 'woff';
                        break;
                    case 'opentype' :
                        $b64ext = 'otf'; // maybe ttf
                        break;
                    default :
                        $b64ext = $b64type;
                }
//echo "base 64 found. base: " . $b64basetype . "; type: " . $b64type . "; ext: " . $b64ext . "<br/>";
//echo "add obj: base 64 found. base: " . $b64basetype . "; type: " . $b64type . "; ext: " . $b64ext . "<br/>base 64 data: $remoteurl<br/>";
//get file type for base 64
//$posim = strpos($remoteurl,":image/") + 7;
//$possemicolon = strpos($remoteurl,";");
//$b64type = substr($remoteurl,$posim, $possemicolon - $posim);
//echo("derived B64 type:".$b64type."<br/>");
                $localfile = joinFilePaths($filepath_domainsavedir . "base64", "base64file_" . $objCNT . "." . $b64ext);
                debug("Base64 file localname", $localfile . "<br/>");
//echo ("Base64 Derived local filename: ". $localfile."<br/>");
                $newsourceurl = $remoteurl;
            }
            else
            { // real object not base64
//echo "ADDPAGEOBJECT ". $remoteurl. "<br/>";
                $localfile = convertAbsoluteURLtoLocalFileName($remoteurl);
//echo "CONVERTED URL $remoteurl to localfile = ". $localfile. "<br/>";
                debug("CONVERTED URL for local file saving is", $localfile);
            }
            if (!isset($inarr["HTTP status"]))
                return false;
//echo ("Adding source url to object array: ". $remoteurl.": $objCNT: $localfile<br/>");
            debug("<br/>ADDING NEW OBJECT - object id", $objCNT);
            debug("new source url", $newsourceurl);
            debug("new local url", $localfile);
// new add to array
// for number formatting: number_format($pieces[1],0,".",",");
// override any calculated local file with a filename provided - needed for root document with a page name added
            if ($SETlocalfile != '')
                $localfile = $SETlocalfile;
//echo("ADDPAGEOBJECT adding local file to " . $localfile. " objcnt =  " . $objCNT  . "<br/>");
            $arr = array("id" => $objCNT,
             "Object type" => $inarr["Object type"],
             "Object source" => $newsourceurl,
             "Object file" => $localfile,
             "Object parent" => $inarr["Object parent"], 
             "Mime type" => $inarr["Mime type"], 
             "Domain" => $inarr["Domain"], 
             "Domain ref" => $inarr["Domain ref"], 
             "HTTP status" => $inarr["HTTP status"], 
             "File extension" => $extn, 
             "CSS ref" => $inarr['CSS ref'], 
             "Header size" => $inarr["Header size"], 
             "Content length transmitted" => $inarr["Content length transmitted"], 
             "Content size downloaded" => $inarr["Content size downloaded"], 
             "Compression" => $inarr["Compression"], 
             "Content size compressed" => $inarr["Content size compressed"], 
             "Content size uncompressed" => $inarr["Content size uncompressed"], 
             "Content size minified uncompressed" => $inarr["Content size minified uncompressed"], 
             "Content size minified compressed" => $inarr["Content size minified compressed"], 
             "Combined files" => $inarr["Combined files"], 
             "JS defer" => $inarr["JS defer"], 
             "JS async" => $inarr["JS async"], 
             "JS docwrite" => $inarr["JS docwrite"], 
             "Image type" => $inarr["Image type"], 
             "Image encoding" => $inarr["Image encoding"], 
             "Image responsive" => $inarr["Image responsive"], 
             "Image display size" => $inarr["Image display size"], 
             "Image actual size" => $inarr["Image actual size"], 
             "Metadata bytes" => $inarr["Metadata bytes"], 
             "EXIF bytes" => $inarr["EXIF bytes"], 
             "APP12 bytes" => $inarr["APP12 bytes"], 
             "IPTC bytes" => $inarr["IPTC bytes"], 
             "XMP bytes" => $inarr["XMP bytes"], 
             "Comment" => $inarr["Comment"], 
             "Comment bytes" => $inarr["Comment bytes"], 
             "ICC colour profile bytes" => $inarr["ICC colour profile bytes"], 
             "Colour type" => $inarr["Colour type"], 
             "Colour depth" => $inarr["Colour depth"], 
             "Interlace" => $inarr["Interlace"], 
             "Est. quality" => $inarr["Est. quality"], 
             "Photoshop quality" => $inarr["Photoshop quality"], 
             "Chroma subsampling" => $inarr["Chroma subsampling"], 
             "Animation" => $inarr["Animation"], 
             "Font name" => $inarr["Font name"], 
             "hdrs_Server" => $inarr["hdrs_Server"], 
             "hdrs_Protocol" => $inarr["hdrs_Protocol"], 
             "hdrs_responsecode" => $inarr["hdrs_responsecode"], 
             "hdrs_age" => $inarr["hdrs_age"], 
             "hdrs_date" => $inarr["hdrs_date"], 
             "hdrs_lastmodifieddate" => $inarr["hdrs_lastmodifieddate"], 
             "hdrs_cachecontrol" => $inarr["hdrs_cachecontrol"], 
             "hdrs_cachecontrolPrivate" => $inarr["hdrs_cachecontrolPrivate"], 
             "hdrs_cachecontrolPublic" => $inarr["hdrs_cachecontrolPublic"], 
             "hdrs_cachecontrolMaxAge" => $inarr["hdrs_cachecontrolMaxAge"], 
             "hdrs_cachecontrolSMaxAge" => $inarr["hdrs_cachecontrolSMaxAge"], 
             "hdrs_cachecontrolNoCache" => $inarr["hdrs_cachecontrolNoCache"], 
             "hdrs_cachecontrolNoStore" => $inarr["hdrs_cachecontrolNoStore"], 
             "hdrs_cachecontrolNoTransform" => '', 
             "hdrs_cachecontrolMustRevalidate" => $inarr["hdrs_cachecontrolMustRevalidate"], 
             "hdrs_cachecontrolProxyRevalidate" => $inarr["hdrs_cachecontrolProxyRevalidate"], 
             "hdrs_connection" => $inarr["hdrs_connection"], 
             "hdrs_contentencoding" => $inarr["hdrs_contentencoding"], 
             "hdrs_contentlength" => $inarr["hdrs_contentlength"], 
             "hdrs_expires" => $inarr["hdrs_expires"], 
             "hdrs_etag" => $inarr["hdrs_etag"], 
             "hdrs_keepalive" => $inarr["hdrs_keepalive"], 
             "hdrs_pragma" => $inarr["hdrs_pragma"], 
             "hdrs_setcookie" => $inarr["hdrs_setcookie"], 
             "hdrs_upgrade" => $inarr["hdrs_upgrade"], 
             "hdrs_vary" => $inarr["hdrs_vary"], 
             "hdrs_via" => $inarr["hdrs_via"], 
             "hdrs_xservedby" => $inarr["hdrs_xservedby"], 
             "hdrs_xcache" => $inarr["hdrs_xcache"], 
             "hdrs_xpx" => $inarr["hdrs_xpx"], 
             "hdrs_xedgelocation" => $inarr["hdrs_xedgelocation"], 
             "hdrs_cfray" => $inarr["hdrs_cfray"], 
             "hdrs_xcdngeo" => $inarr["hdrs_xcdngeo"], 
             "hdrs_xcdn" => $inarr["hdrs_xcdn"], 
             "response_datetime" => $inarr["response_datetime"], 
             "file_section" => $inarr["file_section"], 
             "file_timing" => $inarr["file_timing"],
             "offsetDuration" => $inarr["offsetDuration"],
		  	 "ttfbMS" => $inarr["ttfbMS"],
         	 "downloadDuration" => $inarr["downloadDuration"],
		     "allMS" => $inarr["allMS"],
             "allStartMS" => $inarr["allStartMS"],
             "allEndMS" => $inarr["allEndMS"],
             "cacheSeconds" => $inarr["cacheSeconds"],
             );
            
            // if($objCNT == "N/A")
            //     echo("error - invalid N/A " . $objCNT);

            if ($newsourceurl)
            {
                $arrayPageObjects[] = $arr;
                $objCNT = $objCNT + 1;
            }
//diagnostics("Adding URL to PageObject array",$remoteurl,$localfile);
        }
        else
        {
            if(!is_numeric($foundkey))
            {
//echo ("error attempting to updating N/A-ref object " . $inarr["Object file"]);
            }
            else
            {
//echo("Updating PageObject ". $foundkey . ": url: ".$remoteurl." -- ".$localfile. "<br/>");
         //       diagnostics("Updating URL in PageObject array",$remoteurl,$localfile);
// update the object by its key#
                debug("Updating data for source url in object array", $remoteurl);
                if (isset($inarr["Object type"]))
                {
                    $arrayPageObjects[$foundkey]["Object type"] = $inarr["Object type"];
    //echo("Update dl page object array: ".$key . "; objtype ". $update. "<br/>");
                }
                if (isset($inarr["Object source"]))
                    $arrayPageObjects[$foundkey]["Object source"] = $inarr["Object source"];
                if(isset($inarr["Object file"]))
                    $update = $inarr["Object file"];
                if (isset($inarr["Object file"]) and $update <> '')
                {
                    $arrayPageObjects[$foundkey]["Object file"] = $inarr["Object file"];
//echo("updating local file to " . $update."<br/>");
                }
                if (isset($inarr["Object parent"]))
                    $arrayPageObjects[$foundkey]["Object parent"] = $inarr["Object parent"];
                if (isset($inarr["HTTP status"]))
                {
    //echo("updating object data; $foundkey - $lfn<br/>");
    //var_dump($inarr);
                    $arrayPageObjects[$foundkey]["HTTP status"] = $inarr["HTTP status"];
    //echo("updated status = ".$arrayPageObjects[$foundkey]["HTTP status"]."<br/>");
                }
                if (isset($inarr["Domain"]))
                    $arrayPageObjects[$foundkey]["Domain"] = $inarr["Domain"];
                if (isset($inarr["Domain ref"]))
                    $arrayPageObjects[$foundkey]["Domain ref"] = $inarr["Domain ref"];
                if (isset($inarr["Mime type"]))
                    $arrayPageObjects[$foundkey]["Mime type"] = $inarr["Mime type"];
                if (isset($inarr["hdrs_Server"]))
                    $arrayPageObjects[$foundkey]["hdrs_Server"] = $inarr["hdrs_Server"];
                if (isset($inarr["hdrs_Protocol"]))
                    $arrayPageObjects[$foundkey]["hdrs_Protocol"] = $inarr["hdrs_Protocol"];
                if (isset($inarr["hdrs_responsecode"]))
                    $arrayPageObjects[$foundkey]["hdrs_responsecode"] = $inarr["hdrs_responsecode"];
                if (isset($inarr["hdrs_lastmodifieddate"]))
                    $arrayPageObjects[$foundkey]["hdrs_lastmodifieddate"] = $inarr["hdrs_lastmodifieddate"];
                if (isset($inarr["hdrs_age"]))
                    $arrayPageObjects[$foundkey]["hdrs_age"] = $inarr["hdrs_age"];
                if (isset($inarr["hdrs_date"]))
                    $arrayPageObjects[$foundkey]["hdrs_date"] = $inarr["hdrs_date"];
                if (isset($inarr["hdrs_cachecontrol"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrol"] = $inarr["hdrs_cachecontrol"];
                if (isset($inarr["hdrs_cachecontrolPrivate"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolPrivate"] = $inarr["hdrs_cachecontrolPrivate"];
                if (isset($inarr["hdrs_cachecontrolPublic"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolPublic"] = $inarr["hdrs_cachecontrolPublic"];
                if (isset($inarr["hdrs_cachecontrolMaxAge"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolMaxAge"] = $inarr["hdrs_cachecontrolMaxAge"];
                if (isset($inarr["hdrs_cachecontrolSMaxAge"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolSMaxAge"] = $inarr["hdrs_cachecontrolSMaxAge"];
                if (isset($inarr["hdrs_cachecontrolNoCache"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolNoCache"] = $inarr["hdrs_cachecontrolNoCache"];
                if (isset($inarr["hdrs_cachecontrolNoStore"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolNoStore"] = $inarr["hdrs_cachecontrolNoStore"];
                 if (isset($inarr["hdrs_cachecontrolNoTransform"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolNoTransform"] = $inarr["hdrs_cachecontrolNoTransform"];
                if (isset($inarr["hdrs_cachecontrolMustRevalidate"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolMustRevalidate"] = $inarr["hdrs_cachecontrolMustRevalidate"];
                if (isset($inarr["hdrs_cachecontrolProxyRevalidate"]))
                    $arrayPageObjects[$foundkey]["hdrs_cachecontrolProxyRevalidate"] = $inarr["hdrs_cachecontrolProxyRevalidate"];
                if (isset($inarr["hdrs_connection"]))
                    $arrayPageObjects[$foundkey]["hdrs_connection"] = $inarr["hdrs_connection"];
                if (isset($inarr["hdrs_contentlength"]))
                    $arrayPageObjects[$foundkey]["hdrs_contentlength"] = $inarr["hdrs_contentlength"];
                if (isset($inarr["hdrs_expires"]))
                    $arrayPageObjects[$foundkey]["hdrs_expires"] = $inarr["hdrs_expires"];
                if (isset($inarr["hdrs_etag"]))
                    $arrayPageObjects[$foundkey]["hdrs_etag"] = $inarr["hdrs_etag"];
                if (isset($inarr["hdrs_keepalive"]))
                    $arrayPageObjects[$foundkey]["hdrs_keepalive"] = $inarr["hdrs_keepalive"];
                if (isset($inarr["hdrs_pragma"]))
                    $arrayPageObjects[$foundkey]["hdrs_pragma"] = $inarr["hdrs_pragma"];
                if (isset($inarr["hdrs_setcookie"]))
                    $arrayPageObjects[$foundkey]["hdrs_setcookie"] = $inarr["hdrs_setcookie"];
                if (isset($inarr["hdrs_upgrade"]))
                    $arrayPageObjects[$foundkey]["hdrs_upgrade"] = $inarr["hdrs_upgrade"];
                if (isset($inarr["hdrs_vary"]))
                    $arrayPageObjects[$foundkey]["hdrs_vary"] = $inarr["hdrs_vary"];
                if (isset($inarr["hdrs_via"]))
                    $arrayPageObjects[$foundkey]["hdrs_via"] = $inarr["hdrs_via"];
                if (isset($inarr["hdrs_xservedby"]))
                    $arrayPageObjects[$foundkey]["hdrs_xservedby"] = $inarr["hdrs_xservedby"];
                if (isset($inarr["hdrs_xcache"]))
                    $arrayPageObjects[$foundkey]["hdrs_xcache"] = $inarr["hdrs_xcache"];
                if (isset($inarr["hdrs_xpx"]))
                    $arrayPageObjects[$foundkey]["hdrs_xpx"] = $inarr["hdrs_xpx"];
                if (isset($inarr["hdrs_xedgelocation"]))
                    $arrayPageObjects[$foundkey]["hdrs_xedgelocation"] = $inarr["hdrs_xedgelocation"];
                if (isset($inarr["hdrs_cfray"]))
                    $arrayPageObjects[$foundkey]["hdrs_cfray"] = $inarr["hdrs_cfray"];
                if (isset($inarr["hdrs_xcdngeo"]))
                    $arrayPageObjects[$foundkey]["hdrs_xcdngeo"] = $inarr["hdrs_xcdngeo"];
                if (isset($inarr["hdrs_xcdn"]))
                    $arrayPageObjects[$foundkey]["hdrs_xcdn"] = $inarr["hdrs_xcdn"];
                if (isset($inarr["response_datetime"]))
                    $arrayPageObjects[$foundkey]["response_datetime"] = $inarr["response_datetime"];
                if (isset($inarr["File extension"]))
                    $arrayPageObjects[$foundkey]["File extension"] = $extn;
                if (isset($inarr["Header size"]))
                    $arrayPageObjects[$foundkey]["Header size"] = $inarr["Header size"];
                 if (isset($inarr["Content length transmitted"]))
                    $arrayPageObjects[$foundkey]["Content length transmitted"] = $inarr["Content length transmitted"];
                if (isset($inarr["Content size downloaded"]))
                    $arrayPageObjects[$foundkey]["Content size downloaded"] = $inarr["Content size downloaded"];
                if (isset($inarr["Compression"]))
                    $arrayPageObjects[$foundkey]["Compression"] = trim($inarr["Compression"]);
                if (isset($inarr["Content size compressed"]))
                {
                    $arrayPageObjects[$foundkey]["Content size compressed"] = $inarr["Content size compressed"];
                    if ($arrayPageObjects[$foundkey]["Object type"] == 'Image' and $arrayPageObjects[$foundkey]["Compression"] == '')
                        $arrayPageObjects[$foundkey]["Content size compressed"] = '';
                }
                if (isset($inarr["Content size uncompressed"]))
                {
                    $arrayPageObjects[$foundkey]["Content size uncompressed"] = $inarr["Content size uncompressed"];
                    if ($arrayPageObjects[$foundkey]["Object type"] == 'Image' and $arrayPageObjects[$foundkey]["Compression"] == '')
                        $arrayPageObjects[$foundkey]["Content size uncompressed"] = '';
                }
                if (isset($inarr["Combined files"]))
                    $arrayPageObjects[$foundkey]["Combined files"] = $inarr["Combined files"];
                if (isset($inarr["Content size minified uncompressed"]))
                {
                    $arrayPageObjects[$foundkey]["Content size minified uncompressed"] = $inarr["Content size minified uncompressed"];
                    if ($arrayPageObjects[$foundkey]["Object type"] == 'Image' and $arrayPageObjects[$foundkey]["Compression"] == '')
                        $arrayPageObjects[$foundkey]["Content size minified uncompressed"] = '';
                }
                if (isset($inarr["Content size minified compressed"]))
                {
                    $arrayPageObjects[$foundkey]["Content size minified compressed"] = $inarr["Content size minified compressed"];
                    if ($arrayPageObjects[$foundkey]["Object type"] == 'Image' and $arrayPageObjects[$foundkey]["Compression"] == '')
                        $arrayPageObjects[$foundkey]["Content size minified compressed"] = '';
                }
                if (isset($inarr["CSS ref"]))
                    $arrayPageObjects[$foundkey]["CSS ref"] = $inarr["CSS ref"];
                if (isset($inarr["JS defer"]))
                {
                    if ($arrayPageObjects[$foundkey]["JS defer"] != "DEFER")
                        $arrayPageObjects[$foundkey]["JS defer"] = $inarr["JS defer"];
                }
                if (isset($inarr["JS async"]))
                {
                    if ($arrayPageObjects[$foundkey]["JS async"] != "ASYNC")
                        $arrayPageObjects[$foundkey]["JS async"] = $inarr["JS async"];
                }
                if (isset($inarr["JS docwrite"]))
                    $arrayPageObjects[$foundkey]["JS docwrite"] = $inarr["JS docwrite"];
                if (isset($inarr["Metadata bytes"]))
                    $arrayPageObjects[$foundkey]["Metadata bytes"] = $inarr["Metadata bytes"];
                if (isset($inarr["EXIF bytes"]))
                    $arrayPageObjects[$foundkey]["EXIF bytes"] = $inarr["EXIF bytes"];
                if (isset($inarr["APP12 bytes"]))
                    $arrayPageObjects[$foundkey]["APP12 bytes"] = $inarr["APP12 bytes"];
                if (isset($inarr["IPTC bytes"]))
                    $arrayPageObjects[$foundkey]["IPTC bytes"] = $inarr["IPTC bytes"];
                if (isset($inarr["XMP bytes"]))
                    $arrayPageObjects[$foundkey]["XMP bytes"] = $inarr["XMP bytes"];
                if (isset($inarr["Comment"]))
                    $arrayPageObjects[$foundkey]["Comment"] = $inarr["Comment"];
                if (isset($inarr["Comment bytes"]))
                    $arrayPageObjects[$foundkey]["Comment bytes"] = $inarr["Comment bytes"];
                if (isset($inarr["ICC colour profile bytes"]))
                    $arrayPageObjects[$foundkey]["ICC colour profile bytes"] = $inarr["ICC colour profile bytes"];
                if (isset($inarr["Image type"]))
                    $arrayPageObjects[$foundkey]["Image type"] = $inarr["Image type"];
                if (isset($inarr["Image encoding"]))
                    $arrayPageObjects[$foundkey]["Image encoding"] = $inarr["Image encoding"];
                if (isset($inarr["Image responsive"]))
                    $arrayPageObjects[$foundkey]["Image responsive"] = $inarr["Image responsive"];
                if (isset($inarr["Image display size"]))
                    $arrayPageObjects[$foundkey]["Image display size"] = $inarr["Image display size"];
                if (isset($inarr["Image actual size"]))
                    $arrayPageObjects[$foundkey]["Image actual size"] = $inarr["Image actual size"];
                if (isset($inarr["Colour type"]))
                    $arrayPageObjects[$foundkey]["Colour type"] = $inarr["Colour type"];
                if (isset($inarr["Colour depth"]))
                    $arrayPageObjects[$foundkey]["Colour depth"] = $inarr["Colour depth"];
                if (isset($inarr["Interlace"]))
                    $arrayPageObjects[$foundkey]["Interlace"] = $inarr["Interlace"];
                if (isset($inarr["Est. quality"]))
                    $arrayPageObjects[$foundkey]["Est. quality"] = $inarr["Est. quality"];
                if (isset($inarr["Photoshop quality"]))
                    $arrayPageObjects[$foundkey]["Photoshop quality"] = $inarr["Photoshop quality"];
                if (isset($inarr["Chroma subsampling"]))
                    $arrayPageObjects[$foundkey]["Chroma subsampling"] = $inarr["Chroma subsampling"];
                if (isset($inarr["Animation"]))
                    $arrayPageObjects[$foundkey]["Animation"] = $inarr["Animation"];
                if (isset($inarr["Font name"]))
                    $arrayPageObjects[$foundkey]["Font name"] = $inarr["Font name"];
                if (isset($inarr["file_section"]))
                    $arrayPageObjects[$foundkey]["file_section"] = $inarr["file_section"];
                if (isset($inarr["file_timing"]))
                    $arrayPageObjects[$foundkey]["file_timing"] = $inarr["file_timing"];
                if (isset($inarr["offsetDuration"]))
                    $arrayPageObjects[$foundkey]["offsetDuration"] = $inarr["offsetDuration"];
                if (isset($inarr["ttfbMS"]))
                    $arrayPageObjects[$foundkey]["ttfbMS"] = $inarr["ttfbMS"];
                if (isset($inarr["downloadDuration"]))
                    $arrayPageObjects[$foundkey]["downloadDuration"] = $inarr["downloadDuration"];
                if (isset($inarr["allMS"]))
                    $arrayPageObjects[$foundkey]["allMS"] = $inarr["allMS"];
                if (isset($inarr["allStartMS"]))
                    $arrayPageObjects[$foundkey]["allStartMS"] = $inarr["allStartMS"];
                if (isset($inarr["allEndMS"]))
                    $arrayPageObjects[$foundkey]["allEndMS"] = $inarr["allEndMS"];
                if (isset($inarr["cacheSeconds"]))
                    $arrayPageObjects[$foundkey]["cacheSeconds"] = $inarr["cacheSeconds"];
            }
        }
        return true;
    }


    function in_multiarray($str, $array)
    {
        $exists = false;
        if (is_array($array))
        {
            foreach ($array as $arr) :
                $exists = in_multiarray($str, $arr);
            endforeach;
        }
        else
        {
//echo $array . ' = ' . $str . "\n";
            if (strpos($array, $str) !== false)
                $exists = true;
        }
        return $exists;
    }


    function examine_headers($filename, $header, $curl_info)
    {
// echo "examine_headers curl_info";
// print_r( $curl_info);
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $filename);
        global $body, $arrayOfObjects, $http_codes;
//echo "Func: Examine headers<br />";
        $contentlength = 0;
        if (empty($curl_info['http_code']))
        {
//echo ("No HTTP code was returned<br/>");
            return;
        }
        else
        {
// load the HTTP codes
//if(!isset($http_codes))
            $http_codes = parse_ini_file("rccodes.txt");
//echo results
//echo "Server response: " . $curl_info['http_code'] . " <br />";
            $code = $curl_info['http_code'] . " " . $http_codes[$curl_info['http_code']];
            $contentlength = $curl_info['download_content_length'];
            $downloadlength = $curl_info['size_download'];
            $rdata = file_get_contents($filename);
// Check filesize - if file is too big, it cannot be processed in PHP memory, therefore truncate - TEMPORARY SOLUTION
            if (filesize($filename) > 31457280) // 10485760 = 10Mb, 31457280 = 30Mb
                $rdata = substr($rdata, 0, 3145728); // truncate to 3Mb
//echo(__FUNCTION__." ".__LINE__.": filename: ".$filename." - length: ".$downloadlength."<br/>");
            $header_size = $curl_info['header_size'];
            if (is_numeric($header_size))
            {
                $header = substr($rdata, 0, $header_size);
                $body = substr($rdata, $header_size);
            }
            else
            {
                $header = '';
                $body = '';
            }
//echo "filename: $filename Header size: " .$header_size." bytes<br />";
            if ($contentlength == -1)
            {
//echo "filename: $filename Content size: " .$contentlength." bytes<br />";
//echo "filename: $filename Download size: " .$downloadlength." bytes<br />";
                $contentlength = $downloadlength;
            }
            $redirect_count = $curl_info['redirect_count'];
            if($redirect_count > 0)
                {
                    //override the status code
                //    $code = "302 Found";
                }
            debug(__FUNCTION__ . ' ' . __LINE__ . " redirections found: ", $redirect_count);
//echo(__FUNCTION__.' '.__LINE__." redirections found: " . $redirect_count."<br/>");
// convert header to an array
            $parts = explode("\r\n\r\nHTTP/", $rdata);
//echo "filename: $filename header parts: " .count($parts)."<br />";
            $parts = (count($parts) > 1 ? 'HTTP/' : '') . array_pop($parts);
            list($headers, $body) = explode("\r\n\r\n", $parts, 2);
//echo 'ExamineHeaders (CURL)<pre>';
//print_r($headers);
//echo '</pre>';
            unset($parts);
            unset($rdata);
//echo "curl code = " . $code . PHP_EOL;
            return array($code, $headers, $header_size, $contentlength, $downloadlength, $redirect_count);
        }
    }


    function extract_headersandbody($filename, $fn, $headers)
    {
        global $loadContentFromHAR, $body, $OS,$curlresponseheaders,$headers,$encodingoptions;
        $bBRused = strpos($encodingoptions,"br");
        if($loadContentFromHAR == true)
        {
            $headers = implode($curlresponseheaders);
            //echo "extract_headersandbody headers<pre>";
            // print_r($headers);
            // echo "</pre>";
            return;
        }
// save headers to a string
// save body to a string
// save body to a file named as per the given pathname
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $fn);
       
        debug("Func: extract_headersandbody", "called for: " . $fn);
        $rdata = file_get_contents($filename);
// convert header to an array
        $parts = explode("\r\n\r\nHTTP/", $rdata);
        $parts = (count($parts) > 1 ? 'HTTP/' : '') . array_pop($parts);
        list($headers, $body) = explode("\r\n\r\n", $parts, 2);
// echo 'HEADERS<pre>';
// var_dump($headers);
// echo '</pre>';
// detect compression
        $brotli = false;
        $cheaders = explode(PHP_EOL, $headers);
//echo 'HEADERS (array)<pre>';
//print_r($headers);
//echo '</pre>';
        for ($i = 0; $i < count($cheaders); $i++)
        {
            $crh = $cheaders[$i];
            if ($crh != '')
            { // cater for HTTP/1.0, HTTP/1.1 and HTTP/2 protocols
                if (substr($crh, 0, 6) == 'HTTP/1')
                {
                    $protocol = substr($crh, 5, 3);
                    $responsecode = substr($crh, 9);
//echo "1.1 response code = " . $responsecode  . _PHP_EOL;
                }
                if (substr($crh, 0, 6) == 'HTTP/2')
                {
                    $protocol = substr($crh, 5, 3);
                    $responsecode = substr($crh, 7);
//echo "2 response code = " . $responsecode  . _PHP_EOL;
                }

                else
                {
//debug("crh",$crh); // piece1
                    $pieces = explode(": ", $crh);
//echo "1) ".$pieces[0]; // piece1
//echo "; 2) ".$pieces[1]."<br/>"; // piece2
                    $s1 = $pieces[0];
                    if (count($pieces) > 1)
                    {
                        @ $s2 = $pieces[1];
                        $pieces[1] = trim($pieces[1]);
                        switch (strtolower($s1))
                        {
                            case "content-encoding" :
                                $contentencoding = $pieces[1];
                                if ($contentencoding == 'br' and $bBRused != false)
                                {
                                    debug('Brotli content encoding detected', "brotli");
//echo ("Brotli content encoding detected<br/>");
                                    $brotli = true;
                                    // decompress for brotl
                                    @$fni = tempnam("/tmp", "bri");;
                                    @$fno = tempnam("/tmp", "bro");;
                                    $res = array();
                                    if ($OS == "Windows")
                                    {
                                        
//$tempi = tempnam("c:\\temp\\", "bri");
//$tempo = tempnam("c:\\temp\\", "bro");
                                        file_put_contents($fni, $body);
                                        exec('win_tools\bro64 -d -i ' . $fni . " -o " . escapeshellarg($fno), $res);
                                        $bodyDecodedBrotli = file_get_contents($fno);
                                        if($bodyDecodedBrotli != '')
                                            $body = $bodyDecodedBrotli;
                                    }
                                    else
                                    {
                                        // brotli linux decoder
                                        file_put_contents($fni, $body);
                                        exec('./lnx_tools/brotli -d '  . " -o " . escapeshellarg($fno) ." " . $fni, $res);
                                        $bodyDecodedBrotli = file_get_contents($fno);
                                        if($bodyDecodedBrotli != '')
                                            $body = $bodyDecodedBrotli;

                                    }
                                    unlink($fni);
                                    unlink($fno);
                                }

                                break;
                        }
                    }
                }
            }
        } // end for
//echo 'BODY<pre>';
//print_r($body);
//echo '</pre>';
        $l = strlen($body);
// save body of the file
//echo(__FUNCTION__ .': saving file content ('.$l.' bytes) as: '. $fn."<br/>");
        file_put_contents($fn, $body, LOCK_EX);
        $bodylen = strlen($body);
        unset($rdata);
        unset($parts);
        return ($bodylen);
    }


    function recursePrintStyles($node)
    {
        if ($node->nodeType !== XML_ELEMENT_NODE)
        {
            return;
        }
//echo $node->tagName . "&emsp;&ensp;";
        $n = $node->getAttribute('style');
        if (isset($n) and $n != '')
        {
//echo "'".$n."'<br/>";
            getcssurlsfromcss($n, "inline");
        }
        foreach ($node->childNodes as $childNode)
        {
            recursePrintStyles($childNode);
        }
    }


    function getListOfInlineStyleLinks()
    {
        global $html, $cssimgs;
//echo("checking for inline styles<br/>");
        $h = $html->save();
        $dom = new DOMDocument();
        if(empty($h))
            return false;
        libxml_use_internal_errors(true); // surpress warnings from DOMDocument
        $dom->loadHTML($h);
//echo(__FUNCTION__ . " " . __LINE__."<pre>");
//print_r( htmlspecialchars($h));
//echo("</pre>");
        $xpath = new DOMXPath($dom);
        $body = $xpath->query('//body')->item(0);
        recursePrintStyles($body);
/////////////////
// Find all external styles
        foreach ($html->find('style') as $element)
        {
            $inlinecss = html_entity_decode($element);
            $e = (string) $inlinecss;
            $f = html_entity_decode($e);
//echo(__FUNCTION__ . " " . __LINE__."<pre>");
//print_r( $f);
//echo("</pre>");
            $tagopenstart = strpos($f, "<style");
//echo("style tag start:".$tagopenstart."<br/>");
            $tagopenend = strpos($f, ">", $tagopenstart) + 1;
//echo("style tag end:".$tagopenend."<br/>");
            $f = substr($f, $tagopenend);
//echo("style tag inner:".$f."<br/>");
//$e = str_replace("<style>","",$e);
            $f = str_replace("</style>", "", $f);
            $inlincesswithouttags = $f;
//echo("inline css no tags ".$inlincesswithouttags."<br/>");
//echo(__FUNCTION__ . " " . __LINE__."<pre>");
//print_r( $inlincesswithouttags);
//echo("</pre>");
            if ($cssimgs == true)
                getcssurlsfromcss($e, "all"); // dependent upon optional selection
// always frun this
            if ($inlincesswithouttags != '')
            {
//echo ("sending inline css for processing<br/>");
                processCSSSelectors($inlincesswithouttags, 0, 'inline', 'inline');
            }
        }
    }


    function getcssurlsfromcss($cssfile, $mode)
    {
        global $roothost, $arrayOfObjects, $objcount, $arrayListOfImages, $cssimgs, $url, $roothost, $testdomain,$arrayPageObjects,$objcountimg;
        $boolLoaded = false;
// normal urls
        $cssurls = extract_css_urls($cssfile);
//echo 'extracting URLs from inline CSS<pre>';
//print_r($cssfile);
//echo '</pre>';
// secondary routine - get more  urls to check for Base 64 and other encoded files
        $cssurls = extract_css_bg_urls($cssfile);
//echo 'extracting background URLs from CSS bg<pre>';
//print_r($cssurls);
//echo '</pre>';
// outer loop is for each set of URLS, from either Properties or Imports
        foreach ($cssurls as $key => $item)
        {
//echo "CSS URL Key: $key<br />\n";
// inner loop
            foreach ($item as $ikey => $iitem)
            {
                debug("css item Key: $ikey; Value: $iitem", "");
//echo( "css item Key: $ikey; Value: $iitem<br/>");
                $rawurl = trim($iitem);
                debug("<br/>css getting Absolute URL for: $iitem loaded =" . $boolLoaded . "<br/>");
                $newUrl = url_to_absolute($url, trim($iitem));
                debug("css item absolute", $newUrl);
                $iitem = $newUrl;
                $str = $newUrl;
//test if this file is on a CDN
                list($hd, $hp) = getDomainHostFromURL($str, false, "processStyleLinks 2");
                $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                if ($roothost == $testdomain)
                {
                    debug("Internal Style reference found", "'" . $str . "'");
                    $domref = "Primary";
                }
                else
                {
                    $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                    switch ($domsrc)
                    {
                        case 'CDN' :
                        case 'cdn' :
                            debug("CDN External StyleSheet", "'" . $str . "'");
                            $domref = 'CDN';
                            break;
                        case 'Shard' :
                        case 'shard' :
                            debug("Shard External StyleSheet", "'" . $str . "'");
                            $domref = 'Shard';
                            break;
                        default :
                            debug("ESS 3rd party External StyleSheet", "'" . $str . "'");
                            $domref = '3P';
                    }
                } // end is this domain a CDN
                debug("checking new file found in inline style", $str . " - " . $iitem);
                if (!in_array($iitem, $arrayPageObjects))
                {
//echo "No Match found in image array for $iitem<br/>";
                    $cssref = '';
                    if (strpos($iitem, '?') > 0)
                        $eitem = substr($iitem, 0, strpos($iitem, '?'));
                    else
                        $eitem = $iitem;
                    $path_parts = pathinfo($eitem);
                    if (isset($path_parts['extension']))
                        $ext = $path_parts['extension'];
                    else
                        $ext = '';
                    debug("file type", $ext);
                    switch (strtolower($ext))
                    {
                        case 'woff' :
                        case 'woff2' :
                        case 'ttf' :
                        case 'eot' :
                        case 'otf' :
                            $exttype = "Font";
                            break;
                        case 'jpg' :
                        case 'jpeg' :
                        case 'gif' :
                        case 'png' :
                        case 'bmp' :
                        case 'tiff' :
                        case 'webp' :
                            $exttype = "Image";
                            $arrayListOfImages[] = $iitem;
                            $objcountimg = $objcountimg + 1;
                            break;
                        case 'css' :
                            $exttype = "StyleSheet";
// CSS IMPORT
                            $cssref = "@Import";
                            debug("css processing", "@import");
                            break;
                        default :
                            $exttype = "unknown (in css)";
                    }
                    $arr = array("id" => $objcount, "Object type" => $exttype, "Object name" => $iitem, "Header size" => 0);
                    $arrayOfObjects[] = $arr;
                    $objcount = $objcount + 1;
// add to array
                    $arr = array("Object type" => $exttype, "Object source" => $iitem, "Object file" => '', "Object parent" => $url, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => $cssref, "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => 0, "Content size uncompressed" => 0, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                    if ($mode == "inline" or ($cssimgs = true and $mode = "all"))
                    {
                        addUpdatePageObject($arr);
                        $objcount = $objcount + 1;
                    }
                }
                else
                {
//echo "Match found in image array for $iitem<br/>";
                }
            } // end inner loop
        } // end outer loop
    }


    function file_get_contents_utf8($fn)
    {
        $content = '';
        if(file_exists($fn))
        {
            $content = file_get_contents($fn);
            $enc = mb_detect_encoding($content, mb_list_encodings(), true);
            if ($enc === false)
            {
    //could not detect encoding
            }
            else
                if ($enc !== "UTF-8")
                {
                    $str = mb_convert_encoding($content, "UTF-8", $enc);
    //error_log('file contents not UTF8: '.$fn);
                }
                else
                {
    //UTF-8 detected
            }
        }
        return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }


    function parseRootBodytoDOM($instring, $source)
    {
        debug(__FILE__ . " " . __FUNCTION__ . ' ' . __LINE__);
        global $OS;
        $html = str_get_html($instring);
        if (empty($html))
        {
//echo ("missing html from parseRootBodytoDOM - ". $source);
            debug("Func: parseRootBodytoDOM: empty html:  " . $source);
            error_log("Func: parseRootBodytoDOM: empty html:  " . $source);
            $html = '<i></i>';
//die;
//debug("Func: parseRootBodytoDOM: DOM body"."<xml>".$instring."</xml>");
        }
        else
            debug("Func: parseRootBodytoDOM: checking html content: " . "html present");
//debug("Func: parseRootBodytoDOM: DOM",$html->saveHTML());
// DOM is returned as an object
//echo("returning parsed html<br/>");
        return $html; // non-global
    }


    function getTitleOfPage()
    {
        global $html;
        $title = '';
        @ $titleraw = $html->find('title', 0);
        if (isset($titleraw))
            $title = $titleraw->innertext;
        else
            $title = "";
//echo ("page title = ". $title."<br/>");
        return $title;
    }


    function getJSRedir($initurl)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $initurl);
        global $url, $html, $debug, $fullpagepath, $roothost, $arrayroothost, $filepath_basesavedir, $body, $boolHTTPCompressRoot, $cms, $totbytesdownloaded, $rootbytesdownloaded, $url, $page_redir_total, $rootredirchain, $arrayRootRedirs;
        $refreshFound = false;
        $generatorfound = false;
//echo("<br/>PROCESSING JS Redir<br/>");
        debug("<br/>PROCESSING JS Redir", "");
        foreach ($html->find('script') as $element)
        {
            $str = trim($element->innertext);
            debug("<br/>PROCESSING SCRIPT", $str);
//echo("<br/>PROCESSING SCRIPT: ".$str."<br/>");
// strip spaces
            $str = str_replace(' ', '', $str);
//$str = str_replace('.', '*', $str);
// an absolute path
            $posredir = strpos($str, 'document.location.href=');
            if ($posredir == 0)
                $posredir = strpos($str, 'window.location.href=');
            if ($posredir > 0 and $refreshFound != true)
            {
//echo ('func getjSRedir: js redir found ' .$posredir."<br/>" );
                $posredirs = strpos($str, '"', $posredir);
                $posredire = strpos($str, '"', $posredirs + 1);
                $refreshurlname = substr($str, $posredirs + 1, $posredire - $posredirs - 1);
                $refreshFound = true;
//echo "JS REFRESH st: = $posredirs<br/>";
//echo "JS REFRESH en: = $posredire<br/>";
                break;
            }
        } // end foreach
        if ($refreshFound == true)
        {
//echo "JS REFRESH FOUND: name = $refreshurlname<br/>";
// get absolute url from init
//echo "12 getting Absolute URL for: $refreshurlname<br/>";
            $url = url_to_absolute($initurl, $refreshurlname);
//echo (__FUNCTION__.' '.__LINE__." JavaScript redir to:  $url<br/>");
            $page_redir_total += 1;
            $rootredirchain = $rootredirchain . $page_redir_total . ") " . $initurl . ' JavaScript Redir ' . $url . '<br/>';
            $arr = array("Count" => $page_redir_total, "From" => $initurl, "To" => $url, "Method" => "JavaScript");
            $arrayRootRedirs[] = $arr;
            processRootRedir();
        }
        return $refreshFound;
    }


    function getViewState()
    {
        global $html;
        $viewstatesize = 0;
        foreach ($html->find('#__VIEWSTATE') as $element)
        {
            $viewstatesize = strlen($element);
//echo "viewstate found: size = ".$viewstatesize;
        }
        return $viewstatesize;
    }


    function getMetaTags($initurl)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $initurl);
        global $url, $html, $debug, $fullpagepath, $roothost, $arrayroothost, $filepath_basesavedir, $body, $boolHTTPCompressRoot, $cms, $totbytesdownloaded, $rootbytesdownloaded, $page_redir_total, $rootredirchain, $arrayRootRedirs;
        $refreshFound = false;
        $generatorfound = false;
        debug("<br/>PROCESSING META TAGS", "");
//echo("<br/>PROCESSING META TAGS<br/>");
// var_dump($html);
        $refresh = '';
        $cms = '';
        if(!$html)
            return false;
        foreach ($html->find('meta') as $element)
        {
            foreach ($element->attr as $key => $value)
            {
//echo "META: $key - $value<br/>";
// META GENERATOR
                if (strtolower($key) == "name" && strtolower($value) == "generator")
                {
//echo "META generator: $key - $value<br/>";
                    $generatorfound = true;
                }
                if ($generatorfound == true && strtolower($key) == "content")
                {
//echo "META: $key - $value<br/>";
                    $cms = $value;
                    $generatorfound = false;
                }
//echo "Checking for META REFRESH: ".$key." -".$value."<br/>";
                if (strtolower($key) == "http-equiv" && strtolower($value) == 'refresh')
                {
//echo "META REFRESH FOUND<br/>";
// ensure this is not in a noscript tag
                    $parent = $element->parent();
                    $parenttag = $parent->tag;
                    if ($parenttag != 'noscript')
                    {
                        $refreshFound = true;
                        $refresh = $element->attr;
                    }
//echo "meta tag parent:" . $parenttag->tag ."<br/>";
                } // end if found
                if ($refreshFound == true && strtolower($key) == "content")
                {
//echo "META REFRESH FOUND: $value<br/>";
                    $refreshparts = explode(";", $value);
                    $refreshtime = $refreshparts[0];
                    $refreshurlvalue = $refreshparts[1];
//echo "META REFRESH FOUND: time =  $refreshtime<br/>";
//echo "META REFRESH FOUND: $refreshurlvalue<br/>";
                    $refreshurlvalue = str_replace("URL", "url", $refreshurlvalue);
                    $refreshparts = explode("url=", $refreshurlvalue);
                    $refreshurlname = $refreshparts[0];
                    $refreshurlpath = $refreshparts[1];
//echo "META REFRESH FOUND: name = $refreshurlname<br/>";
//echo "META REFRESH FOUND: path = $refreshurlpath<br/>";
                    debug("META REFRESH FOUND", "path = " . $refreshurlpath);
//echo "13 getting Absolute URL for: $refreshurlpath<br/>";
// get absolute url from init
                    $url = url_to_absolute($initurl, $refreshurlpath);
//echo "META REFRESH to ". $url."<br/>";
//echo (__FUNCTION__.' '.__LINE__." META tag redir to: $url<br/>");
                    $page_redir_total += 1;
                    $rootredirchain = $rootredirchain . $page_redir_total . ") " . $initurl . ' META Refresh ' . $url . '<br/>';
                    $arr = array("Count" => $page_redir_total, "From" => $initurl, "To" => $url, "Method" => "Meta Refresh");
                    $arrayRootRedirs[] = $arr;
                    processRootRedir();
                } // end if content
            } // end for each attr
            if ($refreshFound == true)
                break;
        } // end for each meta
// echo("Meta refresh<pre>");
// print_r($refresh);
// echo("</pre>");
        return $refreshFound;
    }


    function processRootRedir()
    {
        debug(__FUNCTION__ . ' ' . __LINE__, '');
        global $url, $html, $debug, $fullpagepath, $roothost, $arrayroothost, $filepath_basesavedir, $body, $boolHTTPCompressRoot, $cms, $totbytesdownloaded, $rootbytesdownloaded, $filepath_domainsaverootdir, $localfilename, $uastr,$wptHAR,$chhHAR;
//echo "processRootRedir: New Absolute URL = $url<br/>";
        session_start();
        $_SESSION['status'] = 'Processing Root Redirection';
        session_write_close();
//step 1
        $sourceurlparts = get_SourceURL($url);
        if ($debug == true)
        {
            echo ("MAIN:<pre>");
            print_r($sourceurlparts);
            echo ("</pre>");
        }
//full URL path of page requested
        $fullpagepath = $url;
//if (isset($sourceurlparts["querystring"]))
//$fullpagepath .= $sourceurlparts["querystring"];
//echo("host domain: ".$host_domain."<br/>");
//echo("host domain path: ".$host_domain_path."<br/>");
//echo "Adding BASE domain ".$sourceurlparts["host"]."<br/>";
        $arr = array("Domain Name" => $sourceurlparts["host"], "Domain Type" => "Primary");
        $arrayDomains[] = $arr;
        list($host_domain, $host_domain_path) = getDomainHostFromURL($url, false, "processRootRedir");
        $roothost = $host_domain;
        $arrayroothost = array($host_domain);
//getRootDomainAndSubDomains($roothost);
// define initial filenames
        $thispagename = pathinfo($url, PATHINFO_BASENAME);
        $thispageext = pathinfo($url, PATHINFO_EXTENSION);
// define system filepaths, adding a trailing slash
        $filepath_domainsavedir = joinFilePaths($filepath_basesavedir, $uastr, $host_domain, $sourceurlparts["dirs"], DIRECTORY_SEPARATOR);
        $filepath_domainsaverootdir = joinFilePaths($filepath_basesavedir, $uastr, $host_domain, DIRECTORY_SEPARATOR);
        $localvpath = joinFilePaths($filepath_basesavedir, $uastr, $host_domain, DIRECTORY_SEPARATOR);
//echo("vpath=$localvpath<br/>");
        if ($debug == true)
        {
            echo ("<br/>" . "Host domain: " . $host_domain . "<br/>");
            echo ("<br/>" . "Host domain path: " . $host_domain_path . "<br/>");
            echo ("Dirs " . $sourceurlparts["dirs"] . "<br/>");
            echo ("File " . $thispagename . "<br/>");
            echo ("Ext " . $thispageext . "<br/>");
            echo ("Path " . $sourceurlparts["path"] . "<br/>");
            echo ("Port " . $sourceurlparts["port"] . "<br/>");
            echo ("Querystring " . $sourceurlparts["querystring"] . "<br/>");
        }
//echo("pagename: ".$thispagename."<br/>");
//echo("Host domain: ".$host_domain."<br/>");
        if ($thispagename == $host_domain or $thispagename == $sourceurlparts["dirs"])
        {
//echo("resetting page name<br/>");
            $thispagename = '';
        }
// add a trailing slash if there is no filename or querystring and one is not present already
        $pathlc = substr($sourceurlparts["path"], - 1);
//echo("URL path lastchar: $pathlc<br/>");
//echo("URL parts: ".$sourceurlparts["dirs"]."<br/>");
//echo("URL pagename: $thispagename<br/>");
//echo("host: $host_domain<br/>");
        $boolNeedsEndingSlash = false;
        if ($sourceurlparts["querystring"] == '' and $thispagename == '' and $pathlc != '/')
        {
            $boolNeedsEndingSlash = true;
            $url = $url . "/";
//echo("Adding / to url<br/>");
        }
// add a filename if missing
        $thispage_raw = $thispagename;
// remove querystring from pagename
//if (strpos($thispagename,"?") > 0)
//{
//	$thispagename = substr($thispagename,0,strpos($thispagename,"?"));
//}
        if ($thispagename == '')
        {
            debug("setting filenname", "index");
            $thispagename = "index";
        }
        if ($thispageext != 'htm' and $thispageext != 'html')
        {
            debug("adding extension", "htm");
            $thispageext = '.htm';
        }
        else
            $thispageext = '';
        $thispagenameext = $thispagename . $thispageext;
//echo("meta redir:  pagenameext: ".$thispagenameext."<br/>");
        $fullurlpath = $url;
        debug("meta/js redir: Initial URL path", $fullurlpath);
// remove querystring from pagename
        if (strpos($thispage_raw, "?") > 0)
        {
            $thispagename = substr($thispage_raw, 0, strpos($thispage_raw, "?"));
            $thispagenameext = $thispagename . $thispageext;
            debug("meta/js redir pgnm: URL PATH without querystring", $thispagenameext);
        }
        $filepathname_rootobject_headersandbody = $filepath_domainsavedir . "pageinfo_" . $thispagenameext . '.txt';
        $localfilename = $filepath_domainsavedir . $thispagenameext;
        $toastedfilepathname = $filepath_domainsavedir . "toasted_" . $thispagenameext;
        // echo "Analysing website: " . $host_domain . "<br/>";
        // echo "Website path: " . $sourceurlparts["dirs"] . "; page: " . $thispage_raw . "<br/>";
        // echo "URL: " . $url . "<br/>";
        // echo "Analysis saved for webpage: " . $toastedfilepathname . "<br/>";
        // echo "saved as: " . $localfilename . "<br/>";
        // echo "Files saved to directory: " . $filepath_domainsavedir . "<br>";
//echo "Root Page saved as: ".$filepathname_rootobject_headersandbody."<br/>";
        createDomainSaveDir($filepath_domainsavedir);
//echo "redirected URL: ".$url."<br/>";
// get the NEW root object and get the headers
        list($curl_info, $curlresponseheaders) = readURLandSaveToFilePath($url, $filepathname_rootobject_headersandbody);
        $TimeOfResponse = get_Datetime_Now();
// NEW HEADER ANALYSIS FOR ADDING THE NEW ROOT OBJECT TO THE OBJECT TABLE
        list($protocol, $responsecode, $age, $cachecontrol, $cachecontrolPrivate, $cachecontrolPublic, $cachecontrolNoCache, $cachecontrolNoStore, $cachecontrolMaxAge, $cachecontrolSMaxAge, $cachecontrolNoTransform, $cachecontrolMustRevalidate, $cachecontrolProxyRevalidate, $connection, $contentencoding, $contentlength, $contenttype, $date, $etag, $expires, $keepalive, $lastmodifieddate, $pragma, $server, $setcookie, $upgrade, $vary, $via, $xcache, $xservedby, $xpx, $xedgelocation, $cfray, $xcdngeo, $xcdn) = extractHeadersFromCurlResponse($curlresponseheaders); //curlresponseheaders
        $mimetype = trim($contenttype);
// get final URL from download
        $url_page = getURLFromCURL();
//echo(__FUNCTION__.' '.__LINE__." New final redirected URL = " . $url."<br/>");
        list($ttime, $rdtime, $contime, $dnstime, $dstime, $dsstime) = get_timings();
        // examnine headers
        list($sc, $hdrs, $hdrlength, $contentlength, $contentsizedownloaded, $redirect_count) = examine_headers($filepathname_rootobject_headersandbody, $curlresponseheaders, $curl_info);
        $totbytesdownloaded += $contentsizedownloaded;
        $rootbytesdownloaded = $contentsizedownloaded;
        debug("Meta redir: root file HTTP compression status", $contentencoding);
        $gzpos = strpos($contentencoding, "gzip");
        $HTTPCompressionType = $contentencoding;
//echo("Main: root file gzip status: ".$contentencoding." at ". $gzpos);
        if ($gzpos !== false)
        {
            addTestResult("4.1", "4", "Serve the root object with HTTP compression (GZIP)", "Pass");
            $boolHTTPCompressRoot = true;
        }
        else
        {
            if (strpos(trim($contentencoding), "deflate") !== false)
            {
                addTestResult("4.1", "4", "Serve the root object with HTTP compression (Deflate)", "Pass");
                $boolHTTPCompressRoot = true;
            }
            else
            {
                if (strpos(trim($contentencoding), "br") !== false)
                {
                    addTestResult("4.1", "4", "Serve the root object with HTTP compression (Brotli)", "Pass");
                    $boolHTTPCompressRoot = true;
                }
                else
                {
                    addTestResult("4.1", "4", "Serve the root object with HTTP compression", "Fail");
                    $boolHTTPCompressRoot = false;
                }
            }
        }
        debug("Root Object Mime-type", $mimetype);
//echo("Root Headers before checking for redirections<br/>");
//var_dump($curlresponseheaders);
        if ($debug == true)
        {
            echo ("<pre>");
            print_r($curlresponseheaders);
            echo ("</pre>");
        }
        $bodylen = extract_headersandbody($filepathname_rootobject_headersandbody, $localfilename, $curlresponseheaders);
        if ($redirect_count > 0)
        {
            list($redirs, $newurlpath, $finalhdrs) = extract_redirects($redirect_count, $curlresponseheaders, $url, true);
            $bodylen = extract_headersandbody($filepathname_rootobject_headersandbody, $localfilename, $curlresponseheaders);
            $returned = parseRootBodytoDOM($body, 'ProcessRootRedir');
            if (!empty($returned))
                $html = $returned;
        }
        debug(__FUNCTION__ . ' ' . __LINE__ . " Root Meta refesh Add URL data to array", "");
        $arr = array("id" => 1, "Object type" => "HTML", "Object name" => $url, "Header length" => $hdrlength, "Content length" => $bodylen, "HTTP Status" => $sc, "GZIP Status" => $contentencoding, "Mime type" => $mimetype, "Extension" => "", "Combined Files" => "");
        $arrayOfObjects[] = $arr;
//new
//echo("new obj : " .$sc);
// update array
        $arr = array("Object type" => 'HTML', "Object source" => $url, "Object file" => $localfilename, "Object parent" => '', "Mime type" => $mimetype, "Domain" => $host_domain, "Domain ref" => 'Primary', "HTTP status" => strval(intval($sc)), "File extension" => '', "CSS ref" => '', "Header size" => $hdrlength, "Content length transmitted" => $contentlength, "Content size downloaded" => $contentsizedownloaded, "Compression" => $contentencoding, "Content size compressed" => 0, "Content size uncompressed" => $bodylen, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => 0, "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => '', "EXIF bytes" => '', "APP12 bytes" => '', "IPTC bytes" => '', "XMP bytes" => '', "Comment" => '', "Comment bytes" => '', "ICC colour profile bytes" => '', "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => $server, "hdrs_Protocol" => $protocol, "hdrs_responsecode" => $responsecode, "hdrs_date" => $date, "hdrs_lastmodifieddate" => $lastmodifieddate, "hdrs_age" => $age, "hdrs_cachecontrol" => $cachecontrol, "hdrs_cachecontrolPrivate" => $cachecontrolPrivate, "hdrs_cachecontrolPublic" => $cachecontrolPublic, "hdrs_cachecontrolMaxAge" => $cachecontrolMaxAge, "hdrs_cachecontrolSMaxAge" => $cachecontrolSMaxAge, "hdrs_cachecontrolNoCache" => $cachecontrolNoCache, "hdrs_cachecontrolNoStore" => $cachecontrolNoStore, "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => $cachecontrolMustRevalidate, "hdrs_cachecontrolProxyRevalidate" => $cachecontrolProxyRevalidate, "hdrs_connection" => $connection, "hdrs_contentencoding" => $contentencoding, "hdrs_contentlength" => $contentlength, "hdrs_expires" => $expires, "hdrs_etag" => $etag, "hdrs_keepalive" => $keepalive, "hdrs_pragma" => $pragma, "hdrs_setcookie" => $setcookie, "hdrs_upgrade" => $upgrade, "hdrs_vary" => $vary, "hdrs_via" => $via, "hdrs_xservedby" => $xservedby, "hdrs_xcache" => $xcache, "hdrs_xpx" => $xpx, "hdrs_xedgelocation" => $xedgelocation, "hdrs_cfray" => $cfray, "hdrs_xcdngeo" => $xcdngeo, "hdrs_xcdn" => $xcdn, "response_datetime" => $TimeOfResponse, "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
        addUpdatePageObject($arr);
//echo ("Main: saving the headers against the root object: no redirs<br/>");
    if($wptHAR == false and $chhHAR == false)
    {
//echo ("(psfunc 1: bypass saving the root headers against the object due to HAR processed: $url<br/>");
        addPageHeaders($url, $hdrs);;
    }
        
//update page's domain data
        UpdateDomainLocationFromHeader($url, $xservedby, $xpx, $xedgelocation, $server, $cfray, $xcdngeo, $xcdn, $xcache, 'processrootdir');
//update page stats
        addTestResult("11.1", "11", "Root object redirects", "Fail");
    }


    function getListOfImages()
    {
        global $html, $arrayListOfImages, $arrayListOf3PImages, $roothost, $arrayOfObjects, $arrayOf3PObjects, $objcount, $fullurlpath, $basescheme;
// Find all images
        debug("<br/>PROCESSING IMAGES", "");
        debug("Absolute path of parent page", $fullurlpath);
        foreach ($html->find(strtolower('img')) as $element)
        {
            $str = trim($element->src);
            debug("image string", $str);
            $boolbase64 = false;
            $qspos = strpos($str, '?');
            if ($qspos > 0)
                $checkstr = strtolower(substr($str, 0, $qspos));
            else
                $checkstr = $str;
//echo "GLI: checking for encoded file: $checkstr <br/>";
            if (strpos($checkstr, "data:") !== false)
            {
                debug("<br/>PROCESSING IMAGE", "BASE64");
                debug("Image Data", "Base 64");
                $boolbase64 = true;
            }
            else
            {
                debug("<br/>PROCESSING IMAGE", $str);
                debug("Image URL", $str);
            }
// get width and height of display image
            $w = $element->width;
            $h = $element->height;
//echo ($str. ": width: ".$w."; height: ".$h."<br/.>");
            if ($str == "")
            {
//debug("Empty Image", "'".$element->src."'");
            }
            else
            {
                if ($boolbase64 == false)
                {
                    debug("Absolute path of parent page", $fullurlpath);
                    debug("External Image URL", $str);
//echo "image 1 getting Absolute URL for: $str<br/>";
                    $newUrl = url_to_absolute($fullurlpath, $str);
//echo "newurl = $newUrl<br/>";
                    if ($newUrl == '')
                    {
//echo "ERROR: Absolute URL not formed for: $str<br/>";
                    }
                    else
                    {
                        $str = $newUrl;
                    }
                    debug("External Image ABSOLUTE URL", $str);
//test if this file is on a CDN
                    list($hd, $hp) = getDomainHostFromURL($str, false, "getListOfImages");
                    $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                    if ($roothost == $hd)
                    {
                        debug("External Image", "'" . $str . "'");
                        $domref = 'Primary';
                    }
                    else
                    {
                        $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                        switch ($domsrc)
                        {
                            case 'CDN' :
                            case 'cdn' :
                                debug("CDN External Image", "'" . $str . "'");
                                $domref = 'CDN';
                                break;
                            case 'Shard' :
                            case 'shard' :
                                debug("Shard External Image", "'" . $str . "'");
                                $domref = 'Shard';
                                break;
                            default :
                                debug("EI 3rd party External Image", "'" . $str . "'");
                                $domref = '3P';
                        }
                    } // end is this domain a CDN
                }
                else
                {
// base64
                    $hd = '';
                    $domref = 'Base64';
                    debug("Internal Image data", $str);
                }
                $itemfound = false;
                foreach ($arrayListOfImages as $chkvalue)
                {
//debug("CDN External Image lookup value", $chkvalue);
                    if ($str == $chkvalue)
                    {
                        debug("Image lookup found", "Adding image");
                        $itemfound = true;
                        continue;
                    }
                }
                if ($itemfound == false)
                {
                    $arrayListOfImages[] = $str;
                    $arr = array("id" => $objcount, "Object type" => "Image", "Object name" => $str, "Header size" => 0);
                    $arrayOfObjects[] = $arr;
                }
//check images for px wording
                if (is_numeric($w) == true)
                {
                    $imgdimensions = $w . " x " . $h . " px";
                }
                else
                {
                    $imgdimensions = $w . " " . $h;
                }
// add to object array
                $arr = array("Object type" => 'Image', "Object source" => $str, "Object file" => '', "Object parent" => $fullurlpath, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => '', "Content size uncompressed" => '', "Content size minified uncompressed" => '', "Content size minified compressed" => '', "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => $imgdimensions, "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',
                   		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                addUpdatePageObject($arr);
                $objcount = $objcount + 1;
            } // end if not empty string
        } // end for
        $arrayOfObjects = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
        $arrayListOfImages = array_values(array_unique($arrayListOfImages, SORT_REGULAR));
    } // end function
    function getListOfResponsiveImages($elementname, $srcsetattr, $subelement)
    {
        global $html, $arrayListOfImages, $arrayListOf3PImages, $roothost, $arrayOfObjects, $arrayOf3PObjects, $objcount, $fullurlpath, $noofresponvesrcsetimgs, $cssimgs;
        $elementcount = 0;
        $noofresponvesrcsetimgs = 0;
// Find all responsive images
        debug("<br/>PROCESSING RESPONSIVE IMAGES", "");
        debug("Absolute path of parent page", $fullurlpath);
        foreach ($html->find($elementname) as $element)
        {
// check if this type of srcset exists
//$str = trim($element->data-srcset);
            $str = $element->$srcsetattr;
//echo $srcsetattr.' attribute str = '. $str.'<br/>';
//echo('Responsive images '.$elementname. ' '. $srcsetattr. "; length = ". strlen($str).'<br/>');
            if (strlen($str) == 0)
            {
//echo($elementname.' responsive images '. $srcsetattr. "; count = 0".'<br/>');
                continue;
            }
//else
// echo($elementname.' responsive images not 0 '. $srcsetattr. "; count = ". count($srcsetimgs).'<br/>');
            $srcsetimgs = explode(',', $str);
            $subelementdata = $element->$subelement;
            $elementcount += 1;
            debug("image string", $str);
            $boolbase64 = false;
            foreach ($srcsetimgs as $srcsetimg)
            {
                $srcsetimg = trim($srcsetimg);
                $imgset = explode(' ', $srcsetimg);
                $str = $imgset[0];
                if (count($imgset) > 1)
                    $px = $imgset[1];
                else
                    $px = '';
//echo('srcset image: ' . $str . ' - ' . $px);
                $noofresponvesrcsetimgs += 1;
                $qspos = strpos($str, '?');
                if ($qspos > 0)
                    $checkstr = strtolower(substr($str, 0, $qspos));
                else
                    $checkstr = $str;
//echo "GLI: checking for encoded file: $checkstr <br/>";
                if (strpos($checkstr, "data:") !== false)
                {
                    debug("<br/>PROCESSING RESPONSIVE IMAGE", "BASE64");
                    debug("Image Data", "Base 64");
                    $boolbase64 = true;
                }
                else
                {
                    debug("<br/>PROCESSING RESPONSIVE IMAGE", $str);
                    debug("Image URL", $str);
                }
// get width and height of display image
                $w = $element->width;
                $h = $element->height;
//echo ($str. ": width: ".$w."; height: ".$h."<br/.>");
                if ($str == "")
                {
//debug("Empty Image", "'".$element->src."'");
                }
                else
                {
                    if ($boolbase64 == false)
                    {
                        debug("Absolute path of parent page", $fullurlpath);
                        debug("External Image URL", $str);
//echo "image 1 getting Absolute URL for: $str<br/>";
                        $newUrl = url_to_absolute($fullurlpath, $str);
//echo "newurl = $newUrl<br/>";
                        if ($newUrl == '')
                        {
//echo "ERROR: Absolute URL not formed for: $str<br/>";
                        }
                        else
                        {
                            $str = $newUrl;
                        }
                        debug("External Image ABSOLUTE URL", $str);
//test if this file is on a CDN
                        list($hd, $hp) = getDomainHostFromURL($str, false, "getListOfResponsiveImages");
                        $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                        if ($roothost == $hd)
                        {
                            debug("External Image", "'" . $str . "'");
                            $domref = 'Primary';
                        }
                        else
                        {
                            $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                            switch ($domsrc)
                            {
                                case 'CDN' :
                                case 'cdn' :
                                    debug("CDN External Image", "'" . $str . "'");
                                    $domref = 'CDN';
                                    break;
                                case 'Shard' :
                                case 'shard' :
                                    debug("Shard External Image", "'" . $str . "'");
                                    $domref = 'Shard';
                                    break;
                                default :
                                    debug("EI 3rd party External Image", "'" . $str . "'");
                                    $domref = '3P';
                            }
                        } // end is this domain a CDN
                    }
                    else
                    {
// base64
                        $hd = '';
                        $domref = 'Base64';
                        debug("Internal Image data", $str);
                    }
                    $itemfound = false;
                    foreach ($arrayListOfImages as $chkvalue)
                    {
//debug("CDN External Image lookup value", $chkvalue);
                        if ($str == $chkvalue)
                        {
                            debug("Image lookup found", "Adding image");
                            $itemfound = true;
                            continue;
                        }
                    }
                    if ($itemfound == false)
                    {
                        $arrayListOfImages[] = $str;
                        $arr = array("id" => $objcount, "Object type" => "Image", "Object name" => $str, "Header size" => 0);
                        $arrayOfObjects[] = $arr;
                    }
//check images for px wording
                    if (is_numeric($w) == true)
                    {
                        $imgdimensions = $w . " x " . $h . " px";
                    }
                    else
                    {
                        $imgdimensions = $w . " " . $h;
                    }
//echo("responsive images: adding image " . $str . ": " .$elementname. ' ' . $srcsetattr .': '. $subelement. ': '. $subelementdata . '; ' . $px  . "<br/>");
// add to object array
                    $arr = array("Object type" => 'Image', "Object source" => $str, "Object file" => '', "Object parent" => $fullurlpath, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => '', "Content size uncompressed" => '', "Content size minified uncompressed" => '', "Content size minified compressed" => '', "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => $elementname . ' ' . $srcsetattr . ': ' . $subelement . ': ' . $subelementdata . '; ' . $px, "Image display size" => $imgdimensions, "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                    if ($cssimgs == true)
                    {
                        addUpdatePageObject($arr);
                        $objcount = $objcount + 1;
                    }
                } // end if not empty string
            } // end for each img srcset
        } // end for each img srcset
        if ($elementcount > 0 and $noofresponvesrcsetimgs > 0)
        {
            if ($elementname == 'img')
                addStatToFileListAnalysis($elementcount, 'Responsive', '&lt;' . $elementname . '&gt; ' . $srcsetattr);
            else
                addStatToFileListAnalysis($elementcount, 'Responsive', '&lt;picture&gt; srcset');
        }
        $arrayOfObjects = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
        $arrayListOfImages = array_values(array_unique($arrayListOfImages, SORT_REGULAR));
    } // end function
    function getListOfHTML5Elements($mediatype, $attr)
    {
        global $html, $arrayOfObjects, $objcount, $fullurlpath, $noofHTML5MediaElements, $roothost, $cssimgs;
        debug("<br/>PROCESSING HTML5 MEDIA ELEMENTS", "");
        debug("Absolute path of parent page", $fullurlpath);
        if (empty($html))
            return (false);
// Find all links
        foreach ($html->find($mediatype) as $element)
        {
            $str = trim($element->$attr);
            debug("<br/>PROCESSING HTML5 Media elements", $str);
//echo("PROCESSING HTML5 element: '" .$mediatype."': ".$str."<br/>");
            $noofHTML5MediaElements += 1;
            switch ($mediatype)
            {
                case 'audio source' :
                    $objtype = "Audio";
                    break;
                case 'video source' :
                    $objtype = "Video";
                    break;
                case 'embed' :
                case 'object source' :
                    $objtype = "Object";
                    break;
                default :
                    $objtype = $mediatype;
                    break;
            }
            if ($str !== '' and $cssimgs == true)
            {
                debug("Absolute path of parent page", $fullurlpath);
                debug("External Audio URL", $str);
//echo "image 1 getting Absolute URL for: $str<br/>";
                $newUrl = url_to_absolute($fullurlpath, $str);
//echo "newurl = $newUrl<br/>";
                if ($newUrl == '')
                {
//echo "ERROR: Absolute URL not formed for: $str<br/>";
                }
                else
                {
                    $str = $newUrl;
                }
                debug("External Audio ABSOLUTE URL", $str);
//test if this file is on a CDN
                list($hd, $hp) = getDomainHostFromURL($str, false, "getListOfResponsiveImages");
                $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                if ($roothost == $hd)
                {
                    debug("External Audio", "'" . $str . "'");
                    $domref = 'Primary';
                }
                else
                {
                    $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                    switch ($domsrc)
                    {
                        case 'CDN' :
                        case 'cdn' :
                            debug("CDN External audio", "'" . $str . "'");
                            $domref = 'CDN';
                            break;
                        case 'Shard' :
                        case 'shard' :
                            debug("Shard External audio", "'" . $str . "'");
                            $domref = 'Shard';
                            break;
                        default :
                            debug("3rd party External audio", "'" . $str . "'");
                            $domref = '3P';
                    }
                } // end is this domain a CDN
// add to object array
                $arr = array("Object type" => $objtype, "Object source" => $str, "Object file" => '', "Object parent" => $fullurlpath, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => '', "Content size uncompressed" => '', "Content size minified uncompressed" => '', "Content size minified compressed" => '', "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                addUpdatePageObject($arr);
                $objcount = $objcount + 1;
            } // end if not empty string
        }
    }


    function getListOfScriptLinks()
    {
        global $html, $arrayListOfScriptFiles, $arrayListOf3PScriptFiles, $roothost, $arrayOfObjects, $arrayOf3PObjects, $objcount, $fullurlpath, $jsfiles;
        $jsasync = '-';
        $jsdefer = '-';
        debug("<br/>PROCESSING SCRIPTS", "");
        debug("Absolute path of parent page", $fullurlpath);
        if (empty($html))
            return (false);
// Find all links
        foreach ($html->find('script') as $element)
        {
            $str = trim($element->src);
            debug("<br/>PROCESSING SCRIPT", $str);
// an absolute path
            if ($str != '')
            {
                $rawurl = $str;
//echo "script 2 getting Absolute URL for: $str<br/>";
                $newUrl = url_to_absolute($fullurlpath, $str);
                if ($newUrl == '' or $fullurlpath == $str)
                {
//echo "ERROR: Absolute URL not formed for: $str<br/>";
                    $newurl = $str;
                }
                else
                    $str = $newUrl;
                debug("External Script ABSOLUTE URL", $str);
                $rawurl = $str;
//test if this file is on a CDN
                list($hd, $hp) = getDomainHostFromURL($str, false, "getListOfScriptLinks 1");
                $testdomain = $hd;
// ensure this parent file has a filename
                $path_parts = pathinfo($fullurlpath);
                $parname = $fullurlpath;
                if (isset($path_parts['extension']))
                {
                    $parext = $path_parts['extension'];
                }
                else
                    $parext = '';
// remove querystring from parext
                if (strpos($parext, "?") > 0)
                {
                    $thispagename = substr($parext, 0, strpos($parext, "?"));
                }
                debug("Ext of parent file at URL", $parext);
                if ($parext == '')
                {
                    debug("No filename for Script at URL", $parname);
                    $parname = $fullurlpath . "/index.htm";
                    debug("Naming Script at URL", $parname);
                }
                else
                {
                    debug("name of Script at URL", $parname);
                    $parname = $fullurlpath;
                }
// check if SRC is defined for an external script file
                if ($str != '')
                {
                    debug("External Script URL", $str);
                    $rawurl = $str;
//echo "ext script 3 getting Absolute URL for: $str<br/>";
                    $newUrl = url_to_absolute($parname, $str);
                    debug("External Script ABSOLUTE URL", $newUrl);
                    $str = $newUrl;
//test if this file is on a CDN
                    list($hd, $hp) = getDomainHostFromURL($str, false, "getListOfScriptLinks 2");
                    $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                    if ($roothost == $testdomain)
                    {
                        debug("External Script", "'" . $str . "'");
                        $domref = 'Primary';
                    }
                    else
                    {
                        $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                        switch ($domsrc)
                        {
                            case 'CDN' :
                            case 'cdn' :
                                debug("CDN External Script", "'" . $str . "'");
                                $domref = 'CDN';
                                break;
                            case 'Shard' :
                            case 'shard' :
                                debug("Shard External Script", "'" . $str . "'");
                                $domref = 'Shard';
                                break;
                            default :
                                debug("3rd party External Script", "'" . $str . "'");
                                $domref = '3P';
                        }
                    } // end is this domain a CDN
                }
                if (!in_array($str, $arrayListOfScriptFiles))
                {
                    if($fullurlpath != $str)
                    {
                        $arrayListOfScriptFiles[] = $str;
                        $arr = array("id" => $objcount, "Object type" => "Script", "Object name" => $str, "Header size" => 0);
                    

                        $arrayOfObjects[] = $arr;
                        $objcount = $objcount + 1;
                    }
                }
                if ($jsfiles == true and $fullurlpath != $str)
                {
// get the script if it is different to the parent - deal with invalid URLs resolving to parent when makig absolute
                    parseJS($str, $fullurlpath);
                } // if jsfiles
// check how the script was loaded
                $scriptdeferfound = false;
                $scriptasyncfound = false;
                if (strpos($element->outertext, "defer") > 0)
                    $scriptdeferfound = true;
                if (strpos($element->outertext, "async") > 0)
                    $scriptasyncfound = true;
//debug("SCRIPT statement",$element->outertext);
                if ($scriptdeferfound == false)
                {
//echo "SCRIPT: ".$str;
//diagnostics("script defer",$str,"not found");
                    $jsdefer = "-";
                }
                else
                {
//diagnostics("script defer",$str,"found");
                    $jsdefer = "DEFER";
                }
                if ($scriptasyncfound == false)
                {
//diagnostics("script async",$str,"not found");
                    $jsasync = "-";
                }
                else
                {
//diagnostics("script async",$str,"found");
                    $jsasync = "ASYNC";
                }
// add JAVASCRIPT FILE to array
                $arr = array("Object type" => 'JavaScript', "Object source" => $rawurl, "Object file" => '', "Object parent" => $fullurlpath, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => 0, "Content size uncompressed" => 0, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => '', "JS defer" => $jsdefer, "JS async" => $jsasync, "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => '', "EXIF bytes" => '', "APP12 bytes" => '', "IPTC bytes" => '', "XMP bytes" => '', "Comment" => '', "Comment bytes" => '', "ICC colour profile bytes" => '', "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                addUpdatePageObject($arr);
            }
            else
            {
                debug("Internal Script", "");
                if ($jsfiles == true)
                {
// get the script
                    $str = trim($element->innertext);
                    parseJS($str, $fullurlpath);
//debug("INNER SCRIPT text",$str);
//echo "<pre>";
//echo $element->outertext;
//echo "</pre>";
                    $outerscript = $element->outertext;
/*

// search for google analytics

$posga = strpos($outerscript,'.google-analytics.com');

if($posga> 0)

{

diagnostics("Google Analytics found","Internal Script","");



$posgae = strpos($outerscript,"'",$posga);

$gaurl = substr($outerscript,$posga,$posgae-$posga);



//test if this file is on a CDN

list($hd, $hp) = getDomainHostFromURL($gaurl,false);

$testdomain = $hd;





if($sourceurlparts['scheme'] = "http")

{

$str = 'http://www'.$gaurl;

}

else

{

$str = 'https://ssl'.$gaurl;

}



$arrayListOfScriptFiles[] = $str;



$arr = array(

"id" => $objcount,

"Object type" => "Script",

"Object name" => $str,

"Header size" => 0

);

$arrayOfObjects[] = $arr;



$objcount = $objcount + 1;

$objcountscript = $objcountscript + 1;





// add to array

$arr = array(

"Object type" => 'JavaScript',

"Object source" => $gaurl,

"Object file" => '',

"Object parent" => $fullurlpath,

"Domain" => $hd,

"Domain ref" => '',

"HTTP status" => '',

"Mime type" => '',

"File extension" => '',

"CSS ref" => '',

"Header size" => '',

"Content length transmitted" => 0,

"Content size downloaded" => 0,

"Compression" => '',

"Content size compressed" => 0,

"Content size uncompressed" => 0,

"Content size minified uncompressed" => 0,

"Content size minified compressed" => 0,

"Combined files" => '',

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

"hdrs_Server" => '',

"hdrs_Protocol" => '',

"hdrs_responsecode" => '',

"hdrs_age" => '',

"hdrs_date" => '',

"hdrs_lastmodifieddate" => '',

"hdrs_cachecontrol" => '',

"hdrs_cachecontrolPrivate" => '',

"hdrs_cachecontrolPublic" => '',

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



);

addUpdatePageObject($arr);



}

*/
                }
            }
        }
        $arrayOfObjects = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
    }


    function parseJS($script, $currentpage)
    {
        global $arrayOfObjects, $objcount;
        $jsasync = '-';
        $jsdefer = '-';
// look for google analytics in script
//$text = file_get_contents( $url );
        $urls = extract_html_urls($script);
//echo ("<pre><code>");
//echo($script);
//echo ("</pre></code>");
        if (!empty($urls))
        {
//echo ("<pre>");
//print_r( $urls );
//echo ("</pre>");
//echo ("Script parsing<br/>");
            foreach ($urls as $skey => $a)
            {
//echo "<br/>tag type: $skey<br/>";
                switch ($skey)
                {
                    case "script" :
                        foreach ($a as $rkey => $u)
                        {
//echo "attribute: $rkey<br/>";
                            switch ($rkey)
                            {
                                case 'src' :
                                    foreach ($u as $ukey => $str)
                                    {
                                        diagnostics("tag type: $skey;", "attribute: $rkey", "$str");
//echo "url value: $ukey -  $str<br/>";
// unescape the string
                                        $str = trim(stripcslashes($str));
// remove any quotes
                                        if (substr($str, 0, 1) == "'" or substr($str, 0, 1) == '"')
                                        {
                                            $l = strlen($str);
                                            $str = substr($str, 1, $l - 2);
                                        }
                                        debug("Referenced Script URL", $str);
                                        $rawurl = $str;
//echo "js script 4 getting Absolute URL for: $str<br/>";
                                        $newUrl = url_to_absolute($currentpage, $str);
                                        debug("Referenced Script ABSOLUTE URL", $newUrl);
                                        $arrayListOfScriptFiles[] = $newUrl;
                                        debug("Included External Script", "'" . $newUrl . "'");
                                        $arr = array("id" => $objcount, "Object type" => "Script", "Object name" => $newUrl, "Header size" => 0);
                                        $arrayOfObjects[] = $arr;
                                        $objcount = $objcount + 1;
                                        list($hd, $hp) = getDomainHostFromURL($newUrl, false, "parseJS 1");
/*



// check how the script was loaded

$scriptdeferfound = false;

$scriptasyncfound =  false;

if(strpos($a->outertext,"defer") > 0)

$scriptdeferfound = true;



if(strpos($a->outertext,"async") > 0)

$scriptasyncfound = true;



//debug("SCRIPT statement",$element->outertext);



if ($scriptdeferfound == false)

{





//echo "SCRIPT: ".$str;

//diagnostics("script defer",$str,"not found");

}

else

{

//diagnostics("script defer",$str,"found");

$jsdefer = "DEFER";

}



if ($scriptasyncfound == false)

{

//diagnostics("script async",$str,"not found");

}

else

{

//diagnostics("script async",$str,"found");

$jsasync = "ASYNC";

}



*/
// add to array
                                        $arr = array("Object type" => 'JavaScript', "Object source" => $rawurl, "Object file" => '', "Object parent" => $currentpage, "Mime type" => '', "Domain" => $hd, "Domain ref" => 'parsed', "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => 0, "Content size uncompressed" => 0, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => '', "JS defer" => $jsdefer, "JS async" => $jsasync, "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => '', "EXIF bytes" => '', "APP12 bytes" => '', "IPTC bytes" => '', "XMP bytes" => '', "Comment" => '', "Comment bytes" => '', "ICC colour profile bytes" => '', "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                                        addUpdatePageObject($arr);
                                    } // end for
                                    break;
                                default :
                                    foreach ($u as $ukey => $str)
                                    {
//echo "bypass value: $ukey -  $str<br/>";
                                    } // end for
                            } // end switch
                        }
                        break;
                    case "img" :
                        foreach ($a as $rkey => $u)
                        {
//echo "attribute: $rkey<br/>";
                            switch ($rkey)
                            {
                                case 'src' :
                                    foreach ($u as $ukey => $item)
                                    {
//echo "url value: $ukey -  $item<br/>";
// remove any quotes
                                        if (substr($item, 0, 1) == "'" or substr($item, 0, 1) == '"')
                                        {
                                            $l = strlen($item);
                                            $item = substr($item, 1, $l - 2);
//echo ($item. ": width: ".$item->parent ('width')."; height: ".$item->parent ('width')."<br/.>");
                                        }
//echo("Referenced Image URL: ".$item." on ".$script."<br/>");
                                        debug("Referenced Image URL", $item);
                                        $rawurl = $item;
//echo "js image 5 getting Absolute URL for: $str<br/>";
                                        $newUrl = url_to_absolute($currentpage, $item);
                                        debug("Referenced Image ABSOLUTE URL", $newUrl);
                                        $rawurl = $newUrl;
                                        $arrayListOfImages[] = $newUrl;
                                        $arr = array("id" => $objcount, "Object type" => "Image", "Object name" => $newUrl, "Header size" => 0);
                                        $arrayOfObjects[] = $arr;
                                        $objcount = $objcount + 1;
                                        $objcountimg = $objcountimg + 1;
                                        list($hd, $hp) = getDomainHostFromURL($newUrl, false, "parseJS 2");
// add to array
                                        $arr = array("Object type" => 'Image', "Object source" => $rawurl, "Object file" => '', "Object parent" => $currentpage, "Mime type" => '', "Domain" => $hd, "Domain ref" => 'parsed', "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => 0, "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => '', "Content size uncompressed" => '', "Content size minified uncompressed" => '', "Content size minified compressed" => '', "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                                        addUpdatePageObject($arr);
                                    } // end for
                                    break;
                                default :
                                    foreach ($u as $ukey => $v)
                                    {
                                        debug("bypass value: $ukey", $v);
//echo "bypass value: $ukey -  $v<br/>";
                                    } // end for
                            } // end switch
                        }
                        break;
                    default :
                        debug("Parse JS: tag type", $skey);
//echo "<br/>tag type: '$skey' found<br/>";
                } //  end switch
            } // end for loop of urls
        }
// parse JS
//$script
//echo("processing " .$script."<br/>");
        $data = preg_match_all('/\"([^\"]*?)\"/', $script, $matches);
//echo("pre");
//print_r ($matches);
//echo("/pre");
    }


    function getListOfStyleLinks($stage)
    {
        global $html, $arrayListOfStylesheets, $arrayListOf3PStylesheets, $arrayOfObjects, $arrayListOfImages, $fullurlpath, $parname, $cssimgs, $width, $height;
        debug("<br/>Absolute path of parent page", $fullurlpath);
        debug($stage . " PROCESSING STYLESHEETS - REL LINKS", "");
//debug("<br/>PROCESSING STYLESHEETS - REL LINKS html", '<pre>'.$html.'</pre>');
//echo($stage . " ". __FUNCTION__ . " ". __LINE__."<br/>");
//echo "<xmp>";
//echo "modified HTML:".$html;
//echo "</xmp>";
        if (empty($html))
            return (false);
// Find all links
        foreach ($html->find('link[rel="stylesheet"]') as $element)
        {
            debug("<br/><br/>PROCESSING STYLESHEET - REL LINK IN HTML", $element->href);
            $str = trim($element->href);
            $media = '';
            if (isset($element->media))
                $media = $element->media;
//echo(__FUNCTION__ . " " . __LINE__ . ": ".$str." of media type:" .$media."<br>");
// always get css file if get all availabke is checked
            if ($cssimgs == true or $media == "all" or !isset($element->media))
            {
                processStyleLinks($str, "Link rel", "getstylelinks link rel a ", $fullurlpath);
            }
            else
            {
// if media is set, work out if this applies to this simulated browser
// compare device width or height against meadia query
// $width, $height
                $media = str_replace('(', '', $media);
                $media = str_replace(')', '', $media);
                $mediadata = explode(':', $media);
                $mediafeaturetype = $mediadata[0];
                if (isset($mediadata[1]))
                    $mediafeaturevalue = trim($mediadata[1]);
                else
                    $mediafeaturevalue = '';
                if (strpos($mediafeaturevalue, 'px') > 0)
                    $mediafeaturevalue = str_replace('px', '', $mediafeaturevalue);
                $mediafeaturevalue = strtolower($mediafeaturevalue);
                $sizematch = false;
                switch (strtolower($mediafeaturetype))
                {
                    case 'min-width' :
                        if ($width >= $mediafeaturevalue)
                            $sizematch = true;
                        break;
                    case 'max-width' :
                        if ($width <= $mediafeaturevalue)
                            $sizematch = true;
                        break;
                    case 'min-height' :
                        if ($height >= $mediafeaturevalue)
                            $sizematch = true;
                        break;
                    case 'max-height' :
                        if ($height <= $mediafeaturevalue)
                            $sizematch = true;
                        break;
                    case 'orientation' :
                        if ($mediafeaturevalue == "landscape" and $height < $width)
                            $sizematch = true;
                        if ($mediafeaturevalue == "portrait" and $height > $width)
                            $sizematch = true;
                        break;
                    default :
                        $sizematch = true;
                }
// always get the css file = browsers get them all
//if($sizematch == true)
//{
//echo(__FUNCTION__ . " " . __LINE__ . ": getting ".$str." of media type:" .$media."<br>");
                processStyleLinks($str, "Link rel", "getstylelinks link rel b ", $fullurlpath);
//}
//else
//{
//     echo(__FUNCTION__ . " " . __LINE__ . ": leaving ".$str." of media type:" .$media."<br>");
//}
            }
        } // end for each css links
        debug("<br/>PROCESSING STYLESHEETS - IMPORTS IN HTML", "");
// Find all links
        checkForImportsCSS($html, "getlistofstlyelinks, htmldoc", true, $fullurlpath);
//	foreach($html->find(strtolower('link[rel="stylesheet"]')) as $element)
//	{
//		debug("<br/><br/>PROCESSING STYLESHEET - IMPORT","");
//	    $str = trim($element->href);//
//
//		processStyleLinks($str);
//
//	} // end for each css links
        $arrayOfObjects = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
        $arrayListOfStylesheets = array_values(array_unique($arrayListOfStylesheets, SORT_REGULAR));
        $arrayListOfImages = array_values(array_unique($arrayListOfImages, SORT_REGULAR));
    }


    function utf8ize($d)
    {
        if (is_array($d))
        {
            foreach ($d as $k => $v)
            {
                $d[$k] = utf8ize($v);
            }
        }
        else
            if (is_string($d))
            {
                return utf8_encode($d);
            }
            return $d;
    }


    function utf8_converter($array)
    {
        if(!is_array($array))
            return $array;
        array_walk_recursive($array, function(&$item, $key){
            if(!is_object($item))
            {
                if(!mb_detect_encoding($item, 'utf-8', true)){
                    @$item = utf8_encode($item);
                }
            }
        });
        return $array;
    }

    function checkForImportsCSS($csstext, $from, $ishtml, $parentfile)
    {
        global $cssimgs, $maxcsschaindepth, $csschaindepth;
//echo ("CheckForImportsCSS invoked from ".$from."<br/>"); //
//echo ("in file: ".$parentfile."<br/>"); //
        if ($cssimgs == false)
        {
// remove commented out text if not doing all css
            $pattern = '!/\*[^*]*\*+([^/][^*]*\*+)*/!';
            $csstext = preg_replace($pattern, '', $csstext);
        }
        $noofimports = substr_count($csstext, '@import');
//echo ("Number of CSS import statements: ".$noofimports."<br/>"); //
        if ($noofimports > 0)
            $csschaindepth += 1;
        $pos = 0;
        for ($x = 1; $x <= $noofimports; $x++)
        {
            $posimport = strpos($csstext, '@import', $pos);
            $possemicolon = strpos($csstext, ';', $posimport);
            $urlstring = trim(substr($csstext, $posimport + 7, $possemicolon - ($posimport + 8)));
//echo strToHex($urlstring);
// replace quotes
            $urlstring = str_replace('"', '', $urlstring);
            $urlstring = str_replace("'", "", $urlstring);
            $urlstring = str_replace(";", "", $urlstring);
            $implen = strlen($urlstring);
//echo ("$x CSS import (".$implen."): ".$urlstring."<br/>");
//echo ("<br/>");
// extract url from urlstring
            $posleftparentthesis = strpos($urlstring, '(', 0);
            if ($posleftparentthesis != false)
            {
//echo ("( pos".$posleftparentthesis."<br/>");
                $urlstring = substr($urlstring, $posleftparentthesis + 1);
            }
            $posrightparentthesis = strpos($urlstring, ')', 0);
            if ($posrightparentthesis != false)
            {
//echo (") pos".$posrighttparentthesis."<br/>");
                $urlstring = substr($urlstring, 0, $posrightparentthesis);
            }
            debug($x . " final CSS import url ", $urlstring);
            if ($csschaindepth > $maxcsschaindepth)
                $maxcsschaindepth = $csschaindepth;
//echo ("CSS import chain depth: ".$csschaindepth ."<br/>");
//echo("processStyleLinks:  CSS @import url for " . $urlstring . " found in " . $parentfile);
            debug("processStyleLinks", " CSS @import url for " . $urlstring . " found in " . $parentfile);
            processStyleLinks($urlstring, "@Import", "getstylelinks import html", $parentfile);
            $pos = $possemicolon;
        }
    }


    function processStyleLinks($str, $cssref, $from, $parentfile)
    {
        global $html, $arrayListOfStylesheets, $arrayListOf3PStylesheets, $roothost, $arrayOfObjects, $arrayOf3PObjects, $objcount, $arrayListOfImages, $host_domain, $host_domain_path, $ua, $fullurlpath, $parname, $cssimgs, $username, $password, $filepath_domainsavedir, $RootRedirURL, $boolakamaiDebug, $cookie_jar,$encodingoptions;
//echo("processStyleLinks called from '" . $from. "' for External StyleSheet URL: ".$str."<br/>");
        debug($from . ": External StyleSheet URL", $str);
        debug("External StyleSheet URL", $str);
        debug(__FUNCTION__ . ' ' . __LINE__ . " parent page", $parentfile);
        if (empty($str))
            return (false);
// an absolute path
        if ($str != '')
        {
            $rawurl = $str;
//echo "style 6 getting Absolute URL for: $str<br/>";
            $newUrl = url_to_absolute($parentfile, $str);
            if ($newUrl == $parentfile)
            {
                echo ("css import recursion error<br/>");
                addErrors($str, "Bad file name - recursive reference to stylesheet");
                return false;
            }
            if ($newUrl == '')
            {
//echo "ERROR: Absolute URL not formed for: $str<br/>";
                addErrors($str, "Bad file name - bad reference to stylesheet");
//$rawurl = sanitize_file_name(basename($str),false,false);
//echo "<br/>7 getting Absolute URL for: $str<br/>";
                $newUrl = url_to_absolute($parentfile, $str);
                $str = $newUrl;
            }
            else
                $str = $newUrl;
        }
        else
        {
//echo "ERROR: Bad file name - empty reference to stylesheet<br/>";
            addErrors($parentfile, "Bad file name - empty reference to stylesheet");
            return false;
        }
        debug("<br/>External StyleSheet ABSOLUTE URL", $str);
//test if this file is on a CDN
        list($hd, $hp) = getDomainHostFromURL($str, false, "processStyleLinks");
        $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
        if ($roothost == $testdomain)
        {
            debug("External StyleSheet", "'" . $str . "'");
            $domref = "Primary";
        }
        else
        {
            $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
            switch ($domsrc)
            {
                case 'CDN' :
                case 'cdn' :
                    debug("CDN External StyleSheet", "'" . $str . "'");
                    $domref = 'CDN';
                    break;
                case 'Shard' :
                case 'shard' :
                    debug("Shard External StyleSheet", "'" . $str . "'");
                    $domref = 'Shard';
                    break;
                default :
                    debug("ES 3rd party External StyleSheet", "'" . $str . "'");
                    $domref = '3P';
            }
        } // end is this domain a CDN
        if (!in_array($str, $arrayListOfStylesheets))
        {
            $arrayListOfStylesheets[] = $str;
            $arr = array("id" => $objcount, "Object type" => "Stylesheet", "Object name" => $str, "Header size" => 0);
            $arrayOfObjects[] = $arr;
            $objcount = $objcount + 1;
// add to array
            $arr = array("Object type" => 'StyleSheet', "Object source" => $newUrl, "Object file" => '', "Object parent" => $parentfile, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => $cssref, "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => 0, "Content size uncompressed" => 0, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => '', "EXIF bytes" => '', "APP12 bytes" => '', "IPTC bytes" => '', "XMP bytes" => '', "Comment" => '', "Comment bytes" => '', "ICC colour profile bytes" => '', "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
            addUpdatePageObject($arr);
        }
//echo ("Parsing the CSS file<br/>");
// PARSE THE CSS FILE
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $str = rawurldecode($newUrl);
        $chars4 = substr($str, 0, 4);
        $chars2 = substr($str, 0, 2);
// ensure this parent file has a filename
        $path_parts = pathinfo($str);
        $parname = $newUrl;
        if (isset($path_parts['extension']))
        {
            $parext = $path_parts['extension'];
        }
        else
            $parext = '';
// remove querystring from parext
        if (strpos($parext, "?") > 0)
        {
            $thispagename = substr($parext, 0, strpos($parext, "?"));
        }
        debug("Ext of parent file at URL", $parext);
        if ($parext == '')
        {
            debug("No filename for Stylesheet at URL", $parname);
            $parname = $fullurlpath . "/index.htm";
            debug("Naming Stylesheet at URL", $parname);
        }
        else
        {
            debug("name of Stylesheet at URL", $parname);
        }
        debug("External Stylesheet URL", $str);
        $rawurl = $str;
//echo "8 getting Absolute URL for: $str<br/>";
        $newUrl = url_to_absolute($parname, $str);
        debug("External Stylesheet ABSOLUTE URL", $newUrl);
        $sourcefile = $newUrl;
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions;
        if ($boolRootRedirect = true)
        {
            $ref = $RootRedirURL;
            $rqheaders = Array($charset, $conn, $ka, $enc, $ref);
        }
        else
        {
            $ref = $sourcefile;
            $rqheaders = Array($charset, $conn, $ka, $enc);
        }
        if ($cssimgs == true)
        {
            debug("Extracting ALL urls from CSS file", $sourcefile);
//echo($from. ": ". $cssref." Extracting ALL available urls from CSS file: ".$sourcefile."<br/>");
        }
        else
        {
            debug("Extracting CSS urls from CSS file", $sourcefile);
//echo($from. ": ". $cssref." Extracting CSS-related urls from CSS file: ".$sourcefile."<br/>");
        }
// get the file
        $method = 'GET';
        $auth = '';
        $charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
        $conn = 'Connection: Keep-Alive';
        $ka = 'Keep-Alive: 300';
        $enc = 'Accept-Encoding:'.$encodingoptions; // remove encoding to save file
        $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
        $akamaiDebugLocOnly = 'Pragma: akamai-x-cache-on';
        if ($boolakamaiDebug == false)
            $rqheaders = Array($charset, $conn, $ka, $enc, $akamaiDebugLocOnly);
        else
            $rqheaders = Array($charset, $conn, $ka, $akamaiDebug);
        debug('save path', $filepath_domainsavedir);
// set a temporary name for css file to be downloaded
        $csstempname = tempnam($filepath_domainsavedir, 'css_');
        $fnp = fopen($csstempname, 'w');
        $chc = curl_init();
//ini_set ('user_agent', $_SERVER['HTTP_USER_AGENT']);
        $ret = curl_setopt($chc, CURLOPT_FOLLOWLOCATION, true);
        $ret = curl_setopt($chc, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_setopt($chc, CURLOPT_AUTOREFERER, true);
        $ret = curl_setopt($chc, CURLOPT_TIMEOUT, 120);
        curl_setopt($chc, CURLOPT_URL, $sourcefile);
        curl_setopt($chc, CURLOPT_FILE, $fnp);
        curl_setopt($chc, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chc, CURLOPT_REFERER, $parentfile);
        curl_setopt($chc, CURLOPT_USERAGENT, $ua);
//curl_setopt($chc, CURLOPT_COOKIE, $cookie_jar);
        curl_setopt($chc, CURLOPT_COOKIEJAR, $cookie_jar);
        curl_setopt($chc, CURLOPT_COOKIEFILE, $cookie_jar);
//curl_setopt($chc, CURLOPT_PROXY, '127.0.0.1:8888');
        if ($username != '' and $password != '')
        {
            curl_setopt($chc, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($chc, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
        curl_setopt($chc, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers
        curl_setopt($chc, CURLOPT_ENCODING, $encodingoptions);
        $result = curl_exec($chc);
        $headerSent = curl_getinfo($chc, CURLINFO_HEADER_OUT);
//echo "REQUEST HEADER<br/>".$headerSent."<br/>";
        debug("REQUEST HEADER 6", $headerSent);
        if (empty($result) or !$result)
        {
// some kind of an error happened
         //   adderrors($sourcefile, 'Curl error: ' . curl_error($chc));
//echo("curl error getting $sourcefile <br/>");
            $curl_info = false;
//die(curl_error($ch));
        }
        else
            if ($errno = curl_errno($chc))
            {
                $error_message = curl_strerror($errno);
//echo ("cURL error $sourcefile ({$errno}): "." {$error_message}"."<br/>");
             //   adderrors($sourcefile, 'Curl error: ' . $error_message);
//die("DIED 1- CSS curl error".curl_error($chc));
            }
//if (empty($result) or !$result) {
//	// some kind of an error happened
//   adderrors($sourcefile,'Curl error: ');
//	echo("curl error getting css $sourcefile <br/>");
//	//die(curl_error($chc));
//
//  // Check for errors and display the error message
//// die("DIED 2 - CSS curl error".curl_error($chc));
//
//}
            else
            {
                $curl_info = curl_getinfo($chc);
                $http_status = curl_getinfo($chc, CURLINFO_HTTP_CODE);
                $hdrsize = curl_getinfo($chc, CURLINFO_HEADER_SIZE);
                $TimeOfResponse = get_Datetime_Now();
// close file connection
                fclose($fnp);
                curl_close($chc); // close cURL handler
                if ($http_status >= 300)
                {
//echo 'HTTP Error retrieving file: ' .$sourcefile."; HTTP Status: ". $http_status."<br/>";
                    addErrors($sourcefile, "HTTP Status Code: " . $http_status);
                    return false;
                }
//else
                //echo 'HTTP Success retrieving referenced CSS file: ' . $sourcefile . "; HTTP Status: " . $http_status . "; Hdr size: " . $hdrsize . "<br/>";
                //echo ('saving temporary CSS file for Extracting CSS URLs as ' . $csstempname . '<br/>');
                $cssfile = file_get_contents($csstempname);
                unlink($csstempname); // delete the temp css file;
//echo($cssfile);
                debug("cheking css imports for downloaded file in processStyleLinks", "");
                checkForImportsCSS($cssfile, 'processStyleLinks import css', false, $sourcefile);
// only get these if getting all URLs
                if ($cssimgs == true)
                {
// normal urls
                    $cssurls = extract_css_urls($cssfile);
//echo 'URLs from CSS<pre>';
//print_r($cssurls);
//echo '</pre>';
// secondary routine - get more  urls to check for Base 64 and other encoded files
                    $cssurls = extract_css_bg_urls($cssfile);
//echo 'URLs from CSS bg<pre>';
//print_r($cssurls);
//echo '</pre>';
                }
                else
                {
                    $cssurls = array();
                }
// outer loop is for each set of URLS, from either Properties or Imports
                foreach ($cssurls as $key => $item)
                {
//echo "CSS URL Key: $key<br />\n";
                    $boolLoaded = false;
// inner loop
                    foreach ($item as $ikey => $iitem)
                    {
                        debug("css item Key: $ikey; Value: $iitem", "");
//echo( "css item Key: $ikey; Value: $iitem<br/>");
                        if ($cssimgs == false) // only add css url if found in loaded styles
                        {
// check style and url
//echo ('calling findStyleforImage: ' . $iitem . "<br/>");
                            $boolLoaded = findStyleforImage($iitem, $cssfile);
                            if ($boolLoaded == false)
                            {
                                echo "CSS URL NOT loaded by page: $iitem<br/>";
                                continue;
                            }
//echo "CSS URL loaded by page: $iitem<br/>";
                        }
                        $rawurl = trim($iitem);
                        if (substr($rawurl, 0, 5) == 'data:')
                        {
// embedded file - has no domain - or use domain of parent?
                            $domref = "";
                        }
                        else
                        {
                            debug("<br/>css getting Absolute URL for: $iitem loaded =" . $boolLoaded . "<br/>");
                            $newUrl = url_to_absolute($sourcefile, trim($iitem));
                            debug("secondary css item absolute", $newUrl);
                            $iitem = $newUrl;
                            $str = $newUrl;
//test if this file is on a CDN
                            list($hd, $hp) = getDomainHostFromURL($str, false, "processStyleLinks 2");
                            $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                            if ($roothost == $testdomain)
                            {
                                debug("External StyleSheet", "'" . $str . "'");
                                $domref = "Primary";
                            }
                            else
                            {
                                $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                                switch ($domsrc)
                                {
                                    case 'CDN' :
                                    case 'cdn' :
                                        debug("CDN External StyleSheet", "'" . $str . "'");
                                        $domref = 'CDN';
                                        break;
                                    case 'Shard' :
                                    case 'shard' :
                                        debug("Shard External StyleSheet", "'" . $str . "'");
                                        $domref = 'Shard';
                                        break;
                                    default :
                                        debug("ESS 3rd party External StyleSheet", "'" . $str . "'");
                                        $domref = '3P';
                                }
                            } // end is this domain a CDN
                        }
                        if (!in_array($iitem, $arrayListOfImages))
                        {
//echo "No Match found in image array for $iitem<br/>";
                            $cssref = '';
                            if (strpos($iitem, '?') > 0)
                                $eitem = substr($iitem, 0, strpos($iitem, '?'));
                            else
                                $eitem = $iitem;
                            $path_parts = pathinfo($eitem);
                            if (isset($path_parts['extension']))
                                $ext = $path_parts['extension'];
                            else
                                $ext = '';
                            debug("file type", $ext);
                            switch (strtolower($ext))
                            {
                                case 'woff' :
                                case 'woff2' :
                                case 'ttf' :
                                case 'eot' :
                                case 'otf' :
                                    $exttype = "Font";
                                    break;
                                case 'jpg' :
                                case 'jpeg' :
                                case 'gif' :
                                case 'png' :
                                case 'bmp' :
                                case 'tiff' :
                                case 'webp' :
                                    $exttype = "Image";
                                    $arrayListOfImages[] = $iitem;
                                    $objcountimg = $objcountimg + 1;
                                    break;
                                case 'css' :
                                    $exttype = "StyleSheet";
// CSS IMPORT
                                    $cssref = "@Import";
                                    break;
                                default :
                                    $exttype = "unknown (in css)";
                            }
                            $arr = array("id" => $objcount, "Object type" => $exttype, "Object name" => $iitem, "Header size" => 0);
                            $arrayOfObjects[] = $arr;
                            $objcount = $objcount + 1;
// add to array
                            $arr = array("Object type" => "extract ext", "Object source" => $iitem, "Object file" => '', "Object parent" => $sourcefile, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => $cssref, "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => 0, "Content size uncompressed" => 0, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                            addUpdatePageObject($arr);
                        }
                        else
                        {
//echo "Match found in image array for $iitem<br/>";
                        }
                    } // end inner loop
                } // end outer loop
        } // end for a found CSS file
//echo 'CSS styles<pre>';
//print_r($rootStyles);
//echo '</pre>';
    }


    function processCSSSelectors($css, $objid, $fn, $url)
    {
        global $arrayOfCSSSelectors;
//echo($fn.' unsplit css<pre>');
//print_r($css);
//echo('</pre>');
        $splitcss = explode('}', $css);
//echo($fn.' split css<pre>');
//print_r($splitcss);
//echo('</pre>');
        foreach ($splitcss as $selectorset)
        {
            $splitselector = explode('{', $selectorset);
            $lookinline = $splitselector[0];
//echo("selectors in line: ".$lookinline.'<br/>');
// split the phrase by any number of commas or space characters
// which include " ", \r, \t, \n and \f
            $selset = preg_split("/[\s,>]+/", $lookinline);
            foreach ($selset as $selector)
            {
                if ($selector != '')
                {
// remove right-hand side of :
                    $cp = explode(':', $selector);
                    $selectorwithtype = $cp[0];
                    $fc = substr($selectorwithtype, 0, 1);
                    $selector = substr($selectorwithtype, 1);
                    if ($selectorwithtype != '')
                    {
                        switch ($fc)
                        {
                            case '#' :
                                addCSSStyleOrIDtoArray($url, 'id', $selector);
//echo("id: ".$selector."<br/>");
                                break;
                            case '.' :
                                addCSSStyleOrIDtoArray($url, 'class', $selector);
//echo("class: ".$selector."<br/>");
                                break;
                            case '@' :
// ignore media statements
                                continue 3; // don't process all the other words on this media statement
                                break;
                            default : // element
//check for class on element
                                $clpos = strpos($selectorwithtype, '.');
                                $clinline = substr($selectorwithtype, $clpos);
                                if ($clpos !== false)
                                {
//echo("cl found in line: ".$clinline.'<br/>');
                                    $splitselectors = explode('.', $selectorwithtype);
                                    $class = $splitselectors[1];
// remove right-hand side of :
                                    $cp = explode(':', $class);
                                    $class = $cp[0];
                                    $selectorwithtype = $splitselectors[0];
                                    addCSSStyleOrIDtoArray($url, 'class', $class);
//echo("class on an element: ".$class."<br/>");
                                }
//check for id on element
                                $idpos = strpos($selectorwithtype, '#');
                                $idinline = substr($selectorwithtype, $idpos);
                                if ($idpos !== false)
                                {
//echo("cl found in line: ".$clinline.'<br/>');
                                    $splitselectors = explode('#', $selectorwithtype);
                                    $id = $splitselectors[1];
// remove right-hand side of :
                                    $cp = explode(':', $id);
                                    $id = $cp[0];
                                    $selectorwithtype = $splitselectors[0];
                                    addCSSStyleOrIDtoArray($url, 'id', $id);
//echo("class on an element: ".$class."<br/>");
                                }
                                addCSSStyleOrIDtoArray($url, 'element', $selectorwithtype);
//echo("element: ".$selectorwithtype."<br/>");
                                break;
                        }
                    }
                }
            }
        }
        $arrayOfCSSSelectors = array_values(array_unique($arrayOfCSSSelectors, SORT_REGULAR));
//echo($fn.'<pre>');
//print_r($arrayOfCSSSelectors);
//echo('</pre>');
    }


    function addCSSStyleOrIDtoArray($cssfile, $type, $selector)
    {
        global $arrayOfCSSSelectors;
//lookup style or id as used in HTML
        $usedInHTML = lookupIsSelectorUsed($type, $selector);
        $arr = array("CSS filename" => $cssfile, "Selector type" => $type, "Selector name" => $selector, "Used in HTML" => $usedInHTML);
        $arrayOfCSSSelectors[] = $arr;
    }


    function lookupIsSelectorUsed($type, $selector)
    {
        global $rootStyleID, $rootStyleClass, $rootElements;
        $found = false;
        if ($type == 'id')
        {
            foreach ($rootStyleID as $rootid)
            {
                if ($rootid == $selector)
                {
                    $found = true;
                    return 'yes';
                }
            }
        }
        else // class
        {
            if ($type == 'class')
            {
                foreach ($rootStyleClass as $rootclass)
                {
                    if ($rootclass == $selector)
                    {
                        $found = true;
                        return 'yes';
                    }
                }
            }
            else
            {
                foreach ($rootElements as $rootel)
                {
                    if ($rootel == $selector)
                    {
                        $found = true;
                        return 'yes';
                    }
                }
            }
        }
        return 'no';
    }


    function getListOfLinks()
    {
        global $html, $arrayListOfLinks, $roothost, $arrayOfLinks, $url;
// Find all links
        if (empty($html))
            return (false);
        debug("<br/>PROCESSING LINKS", "");
        foreach ($html->find('a') as $element)
        {
            $str = trim($element->href);
            $str = rawurldecode($str);
            if ($element->href == '')
                continue;
            debug("External LINK found (GLL)", "'" . $element->href . "'");
            $chars4 = substr($str, 0, 4);
            $chars2 = substr($str, 0, 2);
            if (strtolower($chars4) != "http" and $chars2 != "//")
            {
// relative path on the domain
                $arrayListOfLinks[] = $element->href;
                debug("External LINK", "'" . $element->href . "'");
                $link = $element->href;
//echo "10 getting Absolute URL for: $str<br/>";
                $abslink = url_to_absolute($url, $link);
                $arr = array("Linkname" => $abslink);
                $arrayOfLinks[] = $arr;
            }
            else
            {
// an absolute path
//test if this file is on a CDN
                list($hd, $hp) = getDomainHostFromURL($element->src, false, "getListOfLinks");
                $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                if ($roothost == $testdomain)
                {
                    debug("External Links", "'" . $ObjURL . "'");
                    $domref = 'Primary EL';
                }
                else
                {
                    $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                    switch ($domsrc)
                    {
                        case 'CDN' :
                        case 'cdn' :
                            debug("EL CDN External Link", "'" . $str . "'");
                            $domref = 'CDN';
                            $arr = array("Linkname" => $element->href);
//echo("linkname cdn: ".$element->href);
                            $arrayOfLinks[] = $arr;
                            break;
                        case 'Shard' :
                        case 'shard' :
                            debug("EL Shard External Link", "'" . $str . "'");
                            $domref = 'Shard';
                            $arr = array("Linkname" => $element->href);
//echo("linkname shard: ".$element->href);
                            $arrayOfLinks[] = $arr;
                            break;
                        default :
                            debug("EL 3rd party External Link", "'" . $str . "'");
                            $domref = '3P';
                            $arr = array("Linkname" => $element->href);
//echo("linkname 3p: ".$element->href);
                            $arrayOfLinks[] = $arr;
                            debug("3rd party External LINK", "'" . $element->href . "'");
                    }
                } // end is this domain a CDN
            }
        }
        $arrayOfLinks = array_values(array_unique($arrayOfLinks, SORT_REGULAR));
    }


    function getListOfImageLinks()
    {
        global $html, $arrayListOfImageLinks, $roothost;
        if (empty($html))
            return (false);
// Find all links
        foreach ($html->find('a') as $element)
        {
            $str = trim($element->href);
            $str = rawurldecode($str);
            if (strpos($str, "jpg") > 0)
            {
                $chars4 = substr($str, 0, 4);
                $chars2 = substr($str, 0, 2);
                if (strtolower($chars4) != "http" and $chars2 != "//")
                {
// relative path on the domain
                    $arrayListOfImageLinks[] = $element->href;
                    debug("External Image LINK", "'" . $element->href . "'");
                }
                else
                {
// an absolute path
//test if this file is on a CDN
                    list($hd, $hp) = getDomainHostFromURL($element->src, false, "getListOfImageLinks");
                    $testdomain = $hd;
                    if (IsThisDomainaCDNofTheRootDomain($roothost, $testdomain) == "CDN")
                    {
// is a CDN
                        $arrayListOfImageLinks[] = $element->href;
                        debug("CDN External Image LINK", "'" . $element->href . "'");
                    }
                    else
                    {
// is a 3rd party
                        $arrayListOfImageLinks[] = $element->href;
                        debug("IL 3rd party External Image LINK", "'" . $element->href . "'");
                    }
                } // end is this domain a CDN
            }
        }
    }


    function getListOfListImages()
    {
        global $html, $arrayListOfImages, $arrayListOf3PImages, $roothost, $arrayOfObjects, $arrayOf3PObjects, $objcount, $objcountimg;
// Find all images
        debug("<br>PROCESSING LIST IMAGES<br/>", "");
        foreach ($html->find(strtolower('li')) as $element)
        {
            $s = trim($element->style);
            if (strpos(strtolower($s), "url") > 0)
            {
                debug("List Item Background Image", "'" . $s . "'");
                $op = strpos(strtolower($s), "(");
                $cp = strpos(strtolower($s), ")");
                $img = substr("$s", $op + 1, $cp - $op - 1);
                $item = $img;
                if (!in_array($item, $arrayListOfImages))
                {
                    $arrayListOfImages[] = $item;
                    $arr = array("id" => $objcount, "Object type" => "image", "Object name" => $img, "Header size" => 0);
                    $arrayOfObjects[] = $arr;
                    $objcount = $objcount + 1;
                    $objcountimg = $objcountimg + 1;
                }
            }
        } // end for
        $arrayOfObjects = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
        $arrayListOfImages = array_values(array_unique($arrayListOfImages, SORT_REGULAR));
    }


    function GetListofHTMLFiles()
    {
// look for HTML files in the object list '
        global $arrayPageObjects, $html, $url, $noofIframes, $phantomjsversion, $arrayOfObjects, $arrayListOfImages, $browserengine, $OS;
        global $urlforbrowserengine, $height, $width, $imgname, $uar, $browserengineoutput, $username, $password;
        global $pjsObjCnt, $pjsObjCntNew, $pjsObjCntExisting;
        debug("<br/>DETECTING OTHER HTML FILES in object list", 'init count: ' . $noofIframes);
//echo("Detecting additional HTML Files<br/>");
// look for HTML files in the source - iFrames '
        foreach ($html->find(strtolower('iframe')) as $element)
        {
            $noofIframes++;
//echo ("iframe found in HTML:".$noofIframes."<br/>");
            $s = trim($element->src);
// get absolute filename
            $localfile = convertAbsoluteURLtoLocalFileName($s);
            debug("iFrame " . $noofIframes . " found, source urls in content ", "'" . $s . "'");
//echo("iFrame found: ".$s."<br/>");
            $op = strpos(strtolower($s), "(");
            $cp = strpos(strtolower($s), ")");
            $item = substr("$s", $op + 1, $cp - $op - 1);
//if (!in_array($item, $arrayListOfImages))
//{
//$arrayListOfImages[] = $item;
/*

$arr = array(

"Object type" => $objType,

"Object source" => $s,

"Object file" => '',

"Object parent" => $url,

"Mime type" => '',

"Domain" => '',

"Domain ref" => '',

"HTTP status" => '',

"File extension" => '',

"CSS ref" => '',

"Header size" => '',

"Content length transmitted" => 0,

"Content size downloaded" => 0,

"Compression" => '',

"Content size compressed" => 0,

"Content size uncompressed" => 0,

"Content size minified uncompressed" => 0,

"Content size minified compressed" => 0,

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

);

addUpdatePageObject($arr);





*/
//$objcount = $objcount + 1;
//$objcountimg = $objcountimg + 1;
//}
        } // end for
        $arrayOfObjects = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
        $arrayListOfImages = array_values(array_unique($arrayListOfImages, SORT_REGULAR));
        $bIsHTMLFile = 0;
//echo("List of all objects found<pre>");
//print_r($arrayPageObjects);
//echo("</pre>");
        $nooffiles = count($arrayPageObjects);
        foreach ($arrayPageObjects as $key => $valuearray)
        {
//var_dump($valuearray);
            $value = $valuearray["Object source"];
            $local = $valuearray["Object file"];
            $ftype = $valuearray["Mime type"];
// don't reprocess root object
            if ($key == 0)
                continue;
            debug($key, $value);
// rmeove querystring
            $qpos = strpos($value, '?');
            if ($qpos > 0)
                $value = substr($value, 0, $qpos - 1);
// get file extension
            $path_parts = pathinfo($value);
//echo $path_parts['extension'], "\n";
            if (isset($path_parts['extension']))
                $ext = $path_parts['extension'];
            else
                $ext = '';
//echo($key.": ".$value."; extension = ".$ext."<br/>");
            $bIsHTMLFile = strpos(strtolower($ext), "htm");

            if ($bIsHTMLFile !== false and $browserengine != 6 and $browserengine != 8) // dont run iframe tests for WPT
            {
//echo($key.": ".$value."; extension = ".$ext."<br/>");
//echo "extension includes htm =" .$bIsHTMLFile."<br/>";
// HTML file found
//echo("Another HTML File found in source: " . $value. "<br/>");
                debug("Another HTML File found in source", $value);
// send HTML file to PhantomJS to process
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//                 PHANTOM JS& OR SLIMERJS
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// parse Javascript via PhantomJS and get a list of the page files, add to the object list if not already found
// outputs the username that owns the running php/httpd process
// (on a system with the "whoami" executable in the path)
                $res = array();
                switch ($browserengine)
                {
                    case 1 :
                        $browserEngineVer = 'Webkit (PhantomJS v1.9.8)';
                        session_start();
                        $_SESSION['status'] = 'iframe - Rerunning ' . $browserEngineVer;
                        $_SESSION['object'] = $value;
                        session_write_close();
                        if ($OS == "Windows")
                            exec('win_tools\phantomjs --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        else
                            exec('phantomjs --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        break;
                    case 2 :
                        $browserEngineVer = 'Webkit (PhantomJS v2.0.0)';
                        session_start();
                        $_SESSION['status'] = 'iframe - Rerunning ' . $browserEngineVer;
                        $_SESSION['object'] = $value;
                        session_write_close();
                        if ($OS == "Windows")
                            exec('win_tools\phantomjs2 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        else
                            exec('phantomjs2 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        break;
                    case 3 :
                        $browserEngineVer = 'Gecko (SlimerJS v0.9.5)';
                        session_start();
                        $_SESSION['status'] = 'iframe - Rerunning ' . $browserEngineVer;
                        $_SESSION['object'] = $value;
                        session_write_close();
                        if ($OS == "Windows")
                            exec('win_tools\slimerjs.bat js\netsniff_sjs.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        else
                            exec('slimerjs.bat js/netsniff_sjs.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        break;
                    case 4 :
                        $browserEngineVer = 'Gecko (SlimerJS v0.10)';
                        session_start();
                        $_SESSION['status'] = 'iframe - Rerunning ' . $browserEngineVer;
                        $_SESSION['object'] = $value;
                        session_write_close();
                        if ($OS == "Windows")
                            exec('win_tools\slimerjs-0.10.3\slimerjs.bat js\netsniff_sjs.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        else
                            exec('slimerjs.bat js/netsniff_sjs.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        break;
                    case 5 :
                        $browserEngineVer = 'Webkit (PhantomJS v2.1.1)';
                        session_start();
                        $_SESSION['status'] = 'iframe - Rerunning ' . $browserEngineVer;
                        session_write_close();
                        if ($OS == "Windows")
                            exec('win_tools\phantomjs2.1 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js\netsniff.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        else
                            exec('phantomjs2.1 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js ' . $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar . "\"" . " " . $browserengineoutput . " " . $username . " " . $password, $res); //responses & sniff
                        break;
                    case 6:
                    case 8:
                        // WPT - no repeat required
//                         $browserEngineVer = 'WebpageTest';
//                         session_start();
//                         $_SESSION['status'] = 'iframe - Rerunning ' . $browserEngineVer;
//                         session_write_close();
//                         $urlenc = urlencode($urlforbrowserengine);
//                         $testId = "";
//                         list($testId, $jsonResult, $summaryCSV, $detailCSV) = submitWPTTest($wptbrowser, $urlenc, $uar, $width, $height, $username, $password);
//                         $statusCode = 0;
//                         while (intval($statusCode) != 200)
//                         {
//                             $statusCode = checkWPTTestStatus($testId);
//                             sleep(1);
//                         }
// // get testresults as HAR
//                         $har = getWPTHAR($testId);
//                         $wptHAR = true;
//                         $uploadedHAR = false;
//                         $harfile = "WebpageTest Test No. " . $testId;
//                         getWPTImagePath($testId, $imgname);
                        break;
                    case 7:
                    // Headless Chrome - no repeat required
                    // $browserEngineVer = 'Chrome Headless';
                    // session_start();
                    // $_SESSION['status'] = 'Running ' . $browserEngineVer;
                    // session_write_close();	
                    // $urlenc = urlencode($urlforbrowserengine);
                    // $testId = "";
                    
                    // if($OS == "Windows")
                    // {	
                    //     // use psexec to start in background, pipe stderr to stdout to capture pid
                    //     $command = '"c:\program files (x86)\google\chrome\application\chrome.exe" --headless --disable-gpu --enable-logging --remote-debugging-port=9222';
                    //     exec("win_tools\pstools\PsExec -d $command 2>&1", $output);
                    //     // capture pid on the 6th line
                    //     preg_match('/ID (\d+)/', $output[5], $matches);
                    //     $pid = $matches[1];
        
                    //     // launch chrome headless
                    //     //exec('start chrome --headless --disable-gpu --enable-logging --remote-debugging-port=9222',$output,$rv);
        
                    //     echo "Google Chrome launched with PID "  . $pid . "<br/>";
                    //     // get screenshot
                    //     //echo "getting screenshot<br/>";
                    //     exec("node win_tools/chromeremote/take_screenshot.js --url " . $urlforbrowserengine . " --pathname " . $imgname . ".png --viewportHeight " . $height . " --viewportWidth " . $width. " 2>&1", $output, $rv);
                    //     //echo implode("\n", $output);
                    //     //echo $imgname.  " - rv = " . $rv . "<br/>";
        
        
                    //     // get har
                    //     //echo "generating HAR file to " . $harname . "<br/>";
                    //     exec("node win_tools/chromeremote/node_modules/chrome-har-capturer/bin/cli.js " . $urlforbrowserengine . " --output " . $harname . " --height " . $height . " --width " . $width . " --agent \"" . $uar . "\" 2>&1", $output2, $rv);
                    //     //echo implode("\n", $output2);
                    //     //echo "rv = " . $rv. "<br/>";
        
        
                    //     // get HTML DOM, after age end with injections
                    //     //echo "dumping HTML after page load to " . $browserengineoutput. "<br/>";
                    //     exec("node win_tools/chromeremote/dump.js --url " . $urlforbrowserengine. " --pathname tmp/" . $browserengineoutput. " 2>&1", $output2, $rv);
                    //     //echo implode("\n", $output2);
                    //     //echo "rv = " . $rv. "<br/>";
        
                    //     // get testresults as HAR
                    //     $uploadedHARFileName = $harname;
                    //     $wptHAR = false;
                    //     $uploadedHAR = true;
        
                    //     // kill remote chrome headless instance
                    //     exec("win_tools\pstools\PsKill -t $pid", $output);
                    // }
                    // else
                    // {
                    //     exec('phantomjs2.1 --ignore-ssl-errors=true --ssl-protocol=tlsv1 js/netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " /tmp".  $browserengineoutput." ".$username. " ". $password,$res); //responses & sniff
                    // }
                    break;
                } // end switch
                $jsonstr = implode($res);
//echo "Phantom JS; processing additional resources for HTML files $value<br/>";
                debug("<br/>PHANTOM JS; processing additional resources for", $url);
//echo("Phantom JS urls<pre>");
//var_dump($res);
//echo("</pre>");
//$pjsObjCnt = 0;
//$pjsObjCntExisting = 0;
//$pjsObjCntNew = 0;
//$pjsredircount = 0;
                $starturl = '';
                $endurl = '';
//display urls
                foreach ($res as $key => $value)
                {
                    $obj = json_decode($value, true);
//echo "Key: $key; Value: $value<br/>";
//netlogresponses returns 2 lines for every response - stage start and end - only deal with end
                    $ObjURL = trim($obj['url']);
                    $httpstatus = trim($obj['status']);
                    $pjsStage = $obj['stage'];
//echo("Phantom JS url ".$ObjURL." stage: ".$pjsStage."<br/>");
//if($pjsStage != 'start' and $pjsStage != 'end')
//{
//		file_put_contents($localfilename.".har",$value,FILE_APPEND);
//	continue;
//}
                    if ($pjsStage == 'start')
                    {
                        $starturl = $ObjURL;
                    }
                    if ($pjsStage == 'end')
                    {
                        $endurl = $ObjURL;
                    }
                    if ($httpstatus >= 300 and $httpstatus < 400)
                    {
                        $pjsredircount += 1;
                        if ($pjsredircount > 1)
                            continue;
                    }
                    if ($starturl == $endurl or ($pjsStage == 'end' and $pjsredircount >= 1))
                    {
                        $starturl = '';
                        $pjsredircount = 0;
                        continue;
                    }
// weed out duff URL references
                    if ($ObjURL == 'http:/')
                        continue;
//echo("Phantom JS process url ".$ObjURL." stage: ".$pjsStage."<br/>");
//echo("PJS REDIRECTION: ".$ObjURL. " = ".$httpstatus."<br/>");
//if($pjsStage == 'end')
//{
//echo("Phantom JS ignore stage: ".$pjsStage."<br/>");
//continue; // ignore start messages
//}
//echo("Phantom JS object<pre>");
//var_dump($obj);
//echo("</pre>");
                    debug("<br/>EXTRA object, original URL: ", $ObjURL);
                    $urlencoded = strpos($ObjURL, '&amp;');
                    if ($urlencoded != false)
                    {
                        $ObjURL = html_entity_decode($ObjURL);
                        debug("PJS object decoded : ", $ObjURL);
                    }
//echo "PJS object: $ObjURL<br/>";
// need to urlencode the querysting
                    $posQM = strpos($ObjURL, "?");
                    if ($posQM > 0)
                    {
                        $ObjURL = substr($ObjURL, 0, $posQM) . "?" . html_entity_decode(htmlentities(substr($ObjURL, $posQM + 1)));
                        debug("PJS with Querystring (htmlentities): ", $ObjURL);
//echo("PJS with Querystring (htmlentities): ".$ObjURL."<br/>");
                    }
                    else
                    {
//echo("PJS w/o Querystring: ".$ObjURL."<br/>");
                    }
                    $ObjURL = htmlentities($ObjURL);
                    $ObjMimeType = trim($obj['contentType']);
                    $ct = explode(";", $ObjMimeType);
                    $contenttype = trim($ct[0]);
                    if ($contenttype != $ObjMimeType)
                    {
//echo ("pjs content type full: ". $ObjMimeType."<br/>");
//echo ("pjs content type 1st: ". $contenttype."<br/>");
                        $ObjMimeType = $contenttype;
                    }
//echo ("<br/>pjs object $ObjURL : ". $obj['contentType'] ." ; content type = ". $ObjMimeType."<br/>");
// only proceed with the END stage
                    $objType = 'TBD';
                    switch (trim($ObjMimeType))
                    {
                        case "text/html" :
                            $objType = "HTML";
                            break;
                        case "text/css" :
                            $objType = 'StyleSheet';
                            break;
                        case "application/javascript" :
                        case "application/x-javascript" :
                        case "text/javascript" :
                        case "text/x-js" :
                            $objType = 'JavaScript';
                            break;
                        case "text/plain" :
                            $objType = 'Data';
                            break;
                        case "text/xml" :
                        case "application/xml" :
                        case "application/json" :
                            $objType = 'Data';
                            break;
                        case "image/jpeg" :
                        case "image/jpg" :
                        case "image/x-bpg" :
                        case "image/bpg" :
                        case "image/gif" :
                        case "image/png" :
                        case "image/bmp" :
                        case "image/tiff" :
                        case "image/webp" :
                        case "image/svg+xml" :
                            $objType = 'Image';
                            break;
                        case "application/x-font-woff" :
                        case "font/woff2" :
                        case "application/x-font-ttf" :
                        case "application/x-font-truetype" :
                        case "application/x-font-opentype" :
                        case "application/vnd.ms-fontobject" :
                        case "application/font-sfnt" :
                        case "application/octet-stream" :
                            $objType = "Font";
                            break;
                        default :
                            $objType = $ObjMimeType;
                            break;
                    }
                    $pjsObjCnt += 1;
//echo("Checking PJS object against root: $ObjURL --- $url<br/>");
                    if ($ObjURL != $url and $ObjURL != $url . "/")
                    {
                        list($id, $lfn) = lookupPageObject($ObjURL);
//echo("PJS object lookup: $ObjURL; ".$id."; localfilename: ".$lfn. "<br/>");
                        if (!is_numeric($id))
                        {
//echo ("new object: ".$ObjURL.": " . $ObjMimeType  ."<br/>");
                            $pjsObjCntNew += 1;
                            list($hd, $hp) = getDomainHostFromURL($ObjURL, false, "main pjs");
                        }
                        else
                        {
//echo ("existing object ($id): ".$ObjURL.": " . $ObjMimeType  ."<br/>");
                            $pjsObjCntExisting += 1;
                            continue;
                        }
//test if this file is on a CDN
                        $testdomain = $hd;
//echo("pjs checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                        if ($roothost == $hd)
                        {
                            debug("External PHANTOM JS FILE", "'" . $ObjURL . "'");
                            $domref = 'Primary';
                        }
                        else
                        {
                            $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                            switch ($domsrc)
                            {
                                case 'CDN' :
                                case 'cdn' :
                                    debug("CDN External File", "'" . $ObjURL . "'");
                                    $domref = 'CDN';
                                    break;
                                case 'Shard' :
                                case 'shard' :
                                    debug("Shard External File", "'" . $ObjURL . "'");
                                    $domref = 'Shard';
                                    break;
                                default :
                                    debug("3rd party External File", "'" . $ObjURL . "'");
                                    $domref = '3P';
                            }
                        }
//$ObjMimeType = $obj['url'];
// check for Base64 file - image, font, something else
                        $qspos = strpos($ObjURL, '?');
                        if ($qspos != 0)
                            $nonqs = strtolower(substr($ObjURL, 0, $qspos));
                        else
                            $nonqs = $ObjURL;
//echo("PJS checking data: in filename: ".$nonqs."<br/>");
                        $datafound = strpos($nonqs, "data:");
//echo("PJS checking data: pos = ".$datafound."<br/>");
                        if ($datafound !== false)
                        {
                            debug("<br/>PROCESSING DATA", "BASE64");
                            debug("PJS Embedded Data", "Base 64");
                            $hd = '';
                            $domref = "Embedded";
//echo("PJS B64 local filename: ".$lfn."<br/>"); // lfn will be derived when object is added
                        }
// add FILE From Phantom JS to array
                        $arr = array("Object type" => $objType, "Object source" => $ObjURL, "Object file" => '', "Object parent" => '', "Mime type" => $ObjMimeType, "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => 0, "Content size uncompressed" => 0, "Content size minified uncompressed" => 0, "Content size minified compressed" => 0, "Combined files" => 0, "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => '', "EXIF bytes" => '', "APP12 bytes" => '', "IPTC bytes" => '', "XMP bytes" => '', "Comment" => '', "Comment bytes" => '', "ICC colour profile bytes" => '', "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '', "response_datetime" => '', "file_section" => '', "file_timing" => '',                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                        addUpdatePageObject($arr);
                    }
                    else
                    {
//echo("Found PJS object = root: $ObjURL --- $url<br/>");
                    }
                } // end foreach url from phantomJS
//echo ("PJS object: total: ".$pjsObjCnt."<br/>");
//echo ("PJS object: new: ".$pjsObjCntNew."<br/>");
//echo ("PJS object: existing: ".$pjsObjCntExisting."<br/>");
                diagnostics("Additional HTML: browser objects: total=" . $pjsObjCnt, "new=" . $pjsObjCntNew, "existing=" . $pjsObjCntExisting);
            }
// reset ready for next file
            $bIsHTMLFile = false;
        }
//die("quick exit: end of html file search");
    }


    function extract_redirects($redirect_count, $headers, $firsturl, $boolIsRoot)
    {
//echo ("Extract Redirects<br/>");
        global $debug, $roothost, $arrayroothost, $host_domain, $host_domain_path, $fullurlpath, $boolRootRedirect, $rootredirchain, $page_redir_total, $arrayRootRedirs, $arrayOtherRedirs,$basescheme,$wptHAR,$chhHAR;
        $redirecturls = array();
        $TimeOfResponse = get_Datetime_Now();
        $chainlimit = 10;
        $lasturl = $firsturl;
//$debug = true;
//$lasturl = html_entity_decode($lasturl);
// check if there are some redirects to process
        if ($redirect_count == 0)
        {
// none
            return array($redirecturls);
        }
// process redirects
// display merged headers for all redirections for object
//    echo "EXTRACT REDIRECTIONS (". $redirect_count . ") FOR $lasturl; headers follow <pre>";
//    print_r($headers);
//    echo "</pre>";
        if ($boolIsRoot == true)
        {
// update base url to redirected locatioon url
//	echo("<br/>Func: Extract Redirects: ROOT URL ".$lasturl. ": No. of redirs = " . $redirect_count."<br/>");
            debug("<br/>Func: Extract Redirects: ROOT URL", $lasturl);
        }
        else
        {
//echo("<br/>Func: Extract Redirects: URL ".$lasturl. "; redir count = " . $redirect_count."<br/>");
            debug("<br/>Func: Extract Redirects: URL", $lasturl);
        }
// split headers into individuals sets , split by HTTP at start of header
        $splitheaders = array();
        $index = - 1;
        foreach ($headers as $n)
        {
            if (substr($n, 0, 7) == 'HTTP/1.')
            {
                $index++;
            }
            else
                if (substr($n, 0, 6) == 'HTTP/2')
                {
                    $index++;
                }
            $splitheaders[$index][] = $n;


        }
        if ($debug == true)
        {
            echo "Redirection Split headers:<pre>";
            print_r($splitheaders);
            echo "</pre>";
        }
// work through each split header set
        $contenttype = '';
        $shcount = 0; // split header count
        $headerlocs = '';
        $domref = 'TBD REDIR';
        $objtype = 'Redirection';
        $newurlpath = $fullurlpath;
        foreach ($splitheaders as $ukey => $setofheaders) // was $headers
        {
//echo("REDIR OUTER LOOP: Header Set ukey: ". $ukey."<br/>");
            debug("REDIR OUTER LOOP: Header Set ukey", $ukey);
            $objtype = 'Redirection';
            $loc = '';
//echo "Extract Redirects: ".$ukey." of ".$redirect_count.": set of headers: ". $lasturl. "<pre>";
//print_r($setofheaders);
//echo "</pre>";
            if ($ukey > $chainlimit)
            {
//    echo("Redir Chain Limit exceeded: ".$lasturl."<br/>");
                break;
            }
            $cookiecount = 0;
            foreach ($setofheaders as $key => $val) // was $headers
            {
                debug("REDIR INNER LOOP: Header key " . $key . "; value: ", $val);
                if ($key == 0) // first HTTP/ header
                {
// this is the first header in the set so work out what it applies to
                    debug("redir headers found for", $lasturl);
//echo("$ukey): headers found for ".$lasturl."<br/>");
                    
                    // check for HTTP/1.1 or HTTP/2
//echo "checking http protocol response for " . $val . "<br/>";
                    if(strpos($val,"HTTP/1.1") !== false or strpos($val,"HTTP/1.0") !== false)
                    {
//echo "extracting HTTP1 sc <br/>";
                        $sc = substr($val, 9);
                    }
                    else
                        if(strpos($val,"HTTP/2") !== false)
                        {
                            $sc = substr($val, 7);                  
//echo "extracting HTTP2 sc <br/>";
                        }
                        else
                        {
//echo "extracting HTTP unknown sc <br/>";
                        }

                    $sccode = substr($sc, 3);
//echo "$val): status code and desc found: " . $sc . " ; desc = '" . $sccode . "'<br />";




                    $strheaders = implode($setofheaders); //$setofheaders
                    $hdrlength = strlen($strheaders);
                    list($hd, $hp) = getDomainHostFromURL($lasturl, true, "extract_redirects");
                    if ($sccode >= 300 and $sccode < 400)
                        $domref = 'redirection';
                        
                    else
                    {
                        $testdomain = $hd;
//echo("redir checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                        if ($roothost == $hd)
                        {
                            debug("Redirection for", "'" . $lasturl . "'");
                            $domref = 'Primary';
                        }
                        else
                        {
                            $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                            switch ($domsrc)
                            {
                                case 'CDN' :
                                case 'cdn' :
                                    debug("CDN External File", "'" . $lasturl . "'");
                                    $domref = 'CDN';
                                    break;
                                case 'Shard' :
                                case 'shard' :
                                    debug("Shard External File", "'" . $lasturl . "'");
                                    $domref = 'Shard';
                                    break;
                                default :
                                    debug("3rd party External File", "'" . $lasturl . "'");
                                    $domref = '3P';
                            }
                        }
                    }
//	$domref = 'TBD REDIR2';
                }
                else
                {
// for all other header keys, break the header down
                    $hdrparts = explode(":", $val, 3);
                    $hdrpartsloc = explode(":", $val, 2);
//echo("Checking object for redirected location: " . $val);
                    switch (strtolower($hdrparts[0]))
                    {
                        case "location" :
                            $val = html_entity_decode($val);
//echo ("<br/>REDIR INNER LOOP: Header key $key; value: ". $val."<br/>");
                            debug("location header found for object, redir to", $val);
// check for last char being a slash
                            $lc = substr($val, - 1);
                            if ($lc == "/")
                            {
                                $val = substr($sourcefile, 0, strlen(val) - 1);
//echo ("redir loc removing trailing / from url<br/>");
                            }
//echo("location part0: " . $hdrparts[0]."<br/>");
//echo("location part1: " . $hdrparts[1]."<br/>");
                            $newloc = trim($hdrpartsloc[1]);
//echo("location found 1: " . $newloc."<br/>");
//echo("last url: " . $lasturl."<br/>");
// get domain from newloc
//list($nhd, $hp) = getDomainHostFromURL($newloc,false);
//$nhd = trim($nhd);
//echo("new url domain: " . $nhd."<br/>");
// does the new location have a filename
                            $nlpath_parts = pathinfo($newloc);
                            $nlpath = parse_url($newloc, PHP_URL_PATH);
                            $nlquery = parse_url($newloc, PHP_URL_QUERY);
//echo ("new location filename: ".$nlpath_parts['filename']. "<br/>");
//echo ("new location filepath: ".$nlpath. "<br/>");
//echo ("new location query: ".$nlquery. "<br/>");
                            if ($nlpath_parts['filename'] == '?' . $nlquery)
                            {
//echo ("new location has no filename, query only". "<br/>");
                            }
//echo("last url domain: " . $hd."<br/>");
                            if (substr($newloc, 0, 4) != 'http') // and (substr($newloc,0,1) != "//"
                            {
//echo "11 getting Absolute URL for: $str<br/>";
                                $loc = url_to_absolute($lasturl, $newloc);
//echo("location found 2: " . $newloc."<br/>");
                            }
                            else
                            {
                                $loc = $newloc;
//echo("location found 3: " . $loc."<br/>");
                            }
//echo("location found 4: " . $loc."<br/>");
// setting new location
                            $l = trim($loc);
//echo("NEW location: absolute url ref: " . $l."<br/>");
                            $newurlpath = $l;
                            if (substr($l, 0, 4) != "http")
                            {
                                $l = url_to_absolute($lasturl, $l);
                            }
                            $headerlocs .= $l;
                            $redirecturls[] = $l;
                            
                            $domref = 'redirection';
                            $page_redir_total += 1;
                            $rootredirchain = $rootredirchain . $page_redir_total . ") " . $lasturl . ' HTTP ' . $sc . ' ' . $l . '<br/>';
                            if ($boolIsRoot == true)
                            {
                                $arr = array("Count" => $page_redir_total, "From" => $lasturl, "To" => $l, "Method" => ' HTTP ' . $sc);
                                $arrayRootRedirs[] = $arr;
                            }
                            else
                            {
                                $arr = array("Count" => $page_redir_total, "From" => $lasturl, "To" => $l, "Method" => ' HTTP ' . $sc);
                                $arrayOtherRedirs[] = $arr;
                            }
                            break;
                        case "content-type" :
                            $val = trim($hdrparts[1]);
//echo ("redirs checking content type: ". $val."<br/>");
                            $valsemipos = explode(";", $val);
                            $contenttype = trim($valsemipos[0]);
//echo ("redirs checking content type: ". $contenttype);
                            session_start();
                            $_SESSION['mimetype'] = trim($contenttype);
                            session_write_close();
                            switch (trim($contenttype))
                            {
                                case "text/html" :
                                    $objtype = "HTML";
                                    break;
                                case "text/css" :
                                    $objtype = "StyleSheet";
                                    break;
                                case "application/javascript" :
                                case "application/x-javascript" :
                                case "text/javascript" :
                                case "text/x-js" :
                                    $objtype = "JavaScript";
                                    break;
                                case "text/plain" :
                                    $objtype = 'Data';
                                    break;
                                case "text/xml" :
                                case "application/xml" :
                                case "application/json" :
                                    $objtype = 'Data';
                                    break;
                                case "image/jpeg" :
                                case "image/jpg" :
                                case "image/x-bpg" :
                                case "image/bpg" :
                                case "image/png" :
                                case "image/gif" :
                                case "image/bmp" :
                                case "image/tiff" :
                                case "image/webp" :
                                case "image/svg+xml" :
                                    $objtype = "Image";
                                    break;
                                case "application/x-font-woff" :
                                case "font/woff2" :
                                case "application/x-font-ttf" :
                                case "application/x-font-truetype" :
                                case "application/x-font-opentype" :
                                case "application/vnd.ms-fontobject" :
                                case "application/font-sfnt" :
                                    $objtype = "Font";
                                    break;
                                default :
                                    $objtype = $val;
                            }
                            break;
                        case "server" :
//$server = $val;
                            break;
                        case "set-cookie" :
                            $cookiecount += 1;
                            break;
                        default :
                            break;
                    } // end switch
                    list($protocol, $responsecode, $age, $cachecontrol, $cachecontrolPrivate, $cachecontrolPublic, $cachecontrolNoCache, $cachecontrolNoStore, $cachecontrolMaxAge, $cachecontrolSMaxAge, $cachecontrolNoTransform, $cachecontrolMustRevalidate, $cachecontrolProxyRevalidate, $connection, $contentencoding, $contentlength, $contenttype, $date, $etag, $expires, $keepalive, $lastmodifieddate, $pragma, $server, $setcookie, $upgrade, $vary, $via, $xcache, $xservedby, $xpx, $xedgelocation, $cfray, $xcdngeo, $xcdn) = extractHeadersFromCurlResponse($setofheaders);
                } // end a header other than the 1st
//echo ("<br/>REDIR INNER LOOP END<br/>");
            } //end for each header in a set
//echo ("<br/>REDIR OUTER LOOP CONTINUED: Header<br/>");
            if ($boolIsRoot == true and $ukey == $redirect_count)
            {
//echo("new loc:".$loc."<br/>");
// get domain from newloc
                list($nhd, $hp) = getDomainHostFromURL($lasturl, false, "extract_redirects 2");
                $nhd = trim($nhd);
// update main root host;
//echo("$ukey): updating root domain host from " .$roothost. " to " . $nhd."<br/>");
                $oldroothost = trim($roothost);
                list($host_domain, $host_domain_path) = getDomainHostFromURL($nhd, true, "extract_redirects 3");
                $roothost = trim($host_domain);
                $arrayroothost = array($host_domain);
//echo("updating root path: old:  $fullurlpath<br/>");
//echo("updating root path: oldroot:  $oldroothost<br/>");
//echo("updating root path: newroot:  $roothost<br/>");
                str_replace($oldroothost, $roothost, $lasturl, $i);
                $newurlpath = $lasturl;
//echo("Redirs updated root path: new: ". $newurlpath."<br/>");
                $fullurlpath = $newurlpath;
                $boolIsRoot = false; // only update the once
                $boolRootRedirect = true;
                $RootRedirURL = $fullurlpath;
                $redir_type = $sccode;
            } // a root object to be updated
            if ($ukey == 0)
            {
                $objparent = '';
                $redirparent = $firsturl;
            }
            else
                $objparent = $redirparent;
//echo ("redir $ukey of $redirect_count all: $lasturl; code = '.$sc.'<br/>");
            if ($ukey <= $redirect_count)
            {
//echo ("$ukey): redirs adding/updating the object: $lasturl; code = '$sc'<br/>");
// add or update the page array for the object
                $arr = array("Object type" => $objtype, "Object source" => $lasturl,
                 "Object file" => '',
                 "Object parent" => $objparent,
                 "Mime type" => $contenttype,
                 "Domain" => $hd,
                 "Domain ref" => $domref,
                 "HTTP status" => strval(intval($sc)),
                 "File extension" => '',
                 "CSS ref" => '',
                 "Header size" => $hdrlength, 
                 "Content length transmitted" => 0, 
                 "Content size downloaded" => 0, 
                 "Compression" => '', 
                 "Content size compressed" => 0, 
                 "Content size uncompressed" => 0, 
                 "Content size minified uncompressed" => 0, 
                 "Content size minified compressed" => 0, 
                 "Combined files" => '', 
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
                 "hdrs_age" => $age, 
                 "hdrs_date" => $date, 
                 "hdrs_lastmodifieddate" => $lastmodifieddate, 
                 "hdrs_cachecontrol" => $cachecontrol, 
                 "hdrs_cachecontrolPrivate" => $cachecontrolPrivate, 
                 "hdrs_cachecontrolPublic" => $cachecontrolPublic, 
                 "hdrs_cachecontrolMaxAge" => $cachecontrolMaxAge, 
                 "hdrs_cachecontrolSMaxAge" => $cachecontrolSMaxAge, 
                 "hdrs_cachecontrolNoCache" => $cachecontrolNoCache, 
                 "hdrs_cachecontrolNoStore" => $cachecontrolNoStore, 
                 "hdrs_cachecontrolNoTransform" => '', 
                 "hdrs_cachecontrolMustRevalidate" => $cachecontrolMustRevalidate, 
                 "hdrs_cachecontrolProxyRevalidate" => $cachecontrolProxyRevalidate, 
                 "hdrs_connection" => $connection,
                 "hdrs_contentencoding" => $contentencoding, 
                 "hdrs_contentlength" => $contentlength, 
                 "hdrs_expires" => $expires, 
                 "hdrs_etag" => $etag, 
                 "hdrs_keepalive" => $keepalive, 
                 "hdrs_pragma" => $pragma, 
                 "hdrs_setcookie" => $cookiecount, 
                 "hdrs_upgrade" => $upgrade, 
                 "hdrs_vary" => $vary, "hdrs_via" => $via, 
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
                "cacheSeconds" => '',);   
                addUpdatePageObject($arr);

// only add headers for those urls doing a redirection, not the final URL
//echo ("$ukey: saving the headers against the object: $lasturl<br/>");
//echo ("<pre>");
//print_r($setofheaders);
//echo ("</pre>");
                $shstring = implode($setofheaders);
//echo ("Redirection ".$ukey.": Adding headers to header array for ". $lasturl."<br/>");
                $thissetofheaders = explode('\r\n', $shstring); // leave as single quotes '\r\n' to remove commas when displayed
                if($wptHAR == false and $chhHAR == false)
                {
//echo ("(psfunc 2: bypass saving the object headers against the object due to HAR processed: $sourcefile<br/>");
                addPageHeaders($lasturl, $thissetofheaders);
                }
                
//update page's domain data = don't update domain or redir
//UpdateDomainLocationFromHeader($host_domain,$xservedby,$xpx,$xedgelocation,$server,$cfray,"extractredir");
            }
// update the last url to the new url
            if ($l != '')
                $lasturl = $l;
//echo ("<br/>REDIR OUTER LOOP END: Header Set ukey: $ukey"."<br/>");
        } // end for each header set
//echo("<br/>Extract Redirs: header location: $headerlocs<br/>");
//addStatToPageAnalysis("Redirect Count",$redirect_count);
//preg_match_all("/location:(.*?)\n/is", $headerlocs, $locations);
        if ($boolIsRoot == true)
        {
            if ($redirect_count > 0)
                addTestResult("11.1", "11", "Root object redirects", "Fail");
            else
                addTestResult("11.1", "11", "Root object does not redirect", "Pass");
        }
//  $debug = false;
        return array($redirecturls, $newurlpath, $thissetofheaders);
    }


    function get_timings()
    {
        global $curl_info, $arrayOfTimings;
        $arr = array("Total Time" => $curl_info['total_time'],);
        $arrayOfTimings[] = $arr;
        $arr = array("Redirect Time" => $curl_info['redirect_time'],);
        $arrayOfTimings[] = $arr;
        $arr = array("Connect Time" => $curl_info['connect_time'],);
        $arrayOfTimings[] = $arr;
        $arr = array("DNS Time" => $curl_info['namelookup_time'],);
        $arrayOfTimings[] = $arr;
        $arr = array("Pre Transfer Time" => $curl_info['pretransfer_time'],);
        $arrayOfTimings[] = $arr;
        $arr = array("Start Transfer Time" => $curl_info['starttransfer_time'],);
        $arrayOfTimings[] = $arr;
        return array($curl_info['total_time'], $curl_info['redirect_time'], $curl_info['connect_time'], $curl_info['namelookup_time'], $curl_info['pretransfer_time'], $curl_info['starttransfer_time']);
    }


    function analyse_file($fn)
    {
        debug(__FUNCTION__ . ' ' . __LINE__ . " parms", $fn);
        global $array_src, $url, $filepath_domainsavedir, $url, $arrayFileStats, $arrayFileListStats, $totfilesize, $rootbytesdownloaded;
//echo '<br />...function analyse_file called<br />';
        $viewstatesize = getViewState();
// filesize in bytes
//echo "<br />Analysis of File '<b>".$fn . "':</b>; filesize: " . filesize($fn) . ' bytes'."<br />";
// initialise the counts
        $linecount = 1;
        $tag_cnt_lines_blank = 0;
        $tag_cnt_chars_blank = 0;
        $tag_cnt_script_open = 0;
        $tag_cnt_script_close = 0;
        $tag_cnt_style_open = 0;
        $tag_cnt_style_close = 0;
        $tag_cnt_link_css = 0;
        $tag_script_flag = 0;
        $tag_style_flag = 0;
        $tag_cnt_script_lines = 0;
        $tag_cnt_style_lines = 0;
        $htmlfound = 0;
        $maxlinelength = 0;
// examine each line
        $file_handle = fopen($fn, "r");
        while (!feof($file_handle) and $file_handle)
        {
            $line = fgets($file_handle);
            if ($htmlfound == 1)
            {
//only increment line count if html doc started
                $linecount++;
// check linelength
                $linelength = strlen($line);
                if ($linelength > $maxlinelength)
                    $maxlinelength = $linelength;
// check for blank lines
                $stringnospaces = str_replace(" ", "", $line);
                $stringnospacestabs = str_replace("\t", "", $stringnospaces);
                if ($stringnospacestabs === "\n" or $stringnospacestabs === "\r\n" or $stringnospacestabs === "\r" or $stringnospacestabs === PHP_EOL or $stringnospacestabs === '')
                {
                    $tag_cnt_lines_blank++;
                    $tag_cnt_chars_blank = $tag_cnt_chars_blank + intval($linelength);
//echo($tag_cnt_chars_blank . "  -  ".intval($linelength)." - " .$stringnospacestabs."<br/>");
                }
            }
            else
            {
//check for occurrence of a tag - HTML
                $pos = strpos(strtolower($line), "<!doctype");
                if ($pos === false)
                { // note: three equal signs
// not found...
                }
                else
                {
                    $htmlfound = 1;
                }
            }
//check for occurrence of a tag - SCRIPT
            $pos = strpos(strtolower($line), "<script");
            if ($pos === false)
            { // note: three equal signs
// not found...
            }
            else
            {
                $tag_cnt_script_open = $tag_cnt_script_open + substr_count($line, "<script");
                $tag_script_flag = 1;
            }
            $pos = strpos(strtolower($line), "/script>");
            if ($pos === false)
            { // note: three equal signs
// not found...
            }
            else
            {
                $tag_cnt_script_close = $tag_cnt_script_close + substr_count($line, "/script");
                $tag_script_flag = 0;
            }
//check for occurrence of a tag - STYLE
            $pos = strpos(strtolower($line), "<style");
            if ($pos === false)
            { // note: three equal signs
// not found...
            }
            else
            {
                $tag_cnt_style_open++;
                $tag_style_flag = 1;
            }
            $pos = strpos(strtolower($line), "/style>");
            if ($pos === false)
            { // note: three equal signs
// not found...
            }
            else
            {
                $tag_cnt_style_close++;
                $tag_style_flag = 0;
            }
//check for occurrence of a tag - LINK
            $pos = strpos(strtolower($line), '<link rel="stylesheet');
            if ($pos === false)
            { // note: three equal signs
// not found...
            }
            else
            {
                $tag_cnt_link_css++;
            }
//check for occurrence of a tag - LINK
            $pos = strpos(strtolower($line), '<link rel="alternate stylesheet');
            if ($pos === false)
            { // note: three equal signs
// not found...
            }
            else
            {
                $tag_cnt_link_css++;
            }
// increment line counts if the tag is open
            if ($tag_script_flag == 1 AND ($line != "\n"))
            {
                $tag_cnt_script_lines++;
            }
            if ($tag_style_flag == 1 AND ($line != "\n"))
            {
                $tag_cnt_style_lines++;
            }
//echo $line;
        } // end loop for each line of code
        fclose($file_handle);
        $str = '';
//$str = $str . 'Number of lines: ' . $linecount."<br />";
//$str = $str .  'Number of blank lines: ' . $tag_cnt_lines_blank."<br />";
        $str = $str . '<u>Tag Counts</u><br />';
        $str = $str . '&lt;script&gt; Open tags: ' . $tag_cnt_script_open;
        $str = $str . '; &lt;/script&gt; Close tags: ' . $tag_cnt_script_close . "<br />";
        $str = $str . '&lt;style&gt; Open tags: ' . $tag_cnt_style_open;
        $str = $str . '; &lt;/style&gt; Close tags: ' . $tag_cnt_style_close . "<br />";
        $str = $str . '&lt;link&gt; stylesheet tags: ' . $tag_cnt_link_css . "<br />";
//$str = $str .  '<u>Content Counts</u><br />';
//$str = $str . 'Style Lines: ' . $tag_cnt_style_lines."<br />";
//$str = $str .  'Script Lines: ' . $tag_cnt_script_lines."<br />";
        $htmlfilesize = filesize($fn);
        $totfilesize += $htmlfilesize;
// new style for list
        if ($rootbytesdownloaded <= 500000)
            addStatToFileListAnalysis(number_format($rootbytesdownloaded), "Bytes", "HTML transmitted", 'info');
        else
            addStatToFileListAnalysis(number_format($rootbytesdownloaded), "Bytes", "HTML transmitted", 'fail');
        addStatToFileListAnalysis(number_format($linecount), "Lines", "in HTML file");
        if ($maxlinelength < 1000)
            addStatToFileListAnalysis(number_format($maxlinelength), "Bytes", "Max Line Size", 'info');
        else
            if ($maxlinelength < 5000)
                addStatToFileListAnalysis(number_format($maxlinelength), "Bytes", "Max Line Size", 'warn');
            else
                addStatToFileListAnalysis(number_format($maxlinelength), "Bytes", "Max Line Size", 'fail');
        if ($viewstatesize > 0)
        {
            if ($viewstatesize < 10000)
                addStatToFileListAnalysis(number_format($viewstatesize), "bytes", "VIEWSTATE", 'pass');
            else
                addStatToFileListAnalysis(number_format($viewstatesize), "bytes", "VIEWSTATE", 'warn');
        }
        if ($tag_cnt_lines_blank == 0)
            addStatToFileListAnalysis(number_format($tag_cnt_lines_blank), "Blank", "Lines", "pass");
        else
            if ($tag_cnt_lines_blank < 100)
                addStatToFileListAnalysis(number_format($tag_cnt_lines_blank), "Blank", "Lines", "warn");
            else
                addStatToFileListAnalysis(number_format($tag_cnt_lines_blank), "Blank", "Lines", "fail");
        if ($tag_cnt_chars_blank == 0)
            addStatToFileListAnalysis(number_format($tag_cnt_chars_blank), "Blank", "Bytes", "pass");
        else
            if ($tag_cnt_chars_blank < 100)
                addStatToFileListAnalysis(number_format($tag_cnt_chars_blank), "Blank", "Bytes", "warn");
            else
                addStatToFileListAnalysis(number_format($tag_cnt_chars_blank), "Blank", "Bytes", "fail");
        if ($tag_cnt_style_lines == 0)
            addStatToFileListAnalysis(number_format($tag_cnt_style_lines), "Inline Style", "Lines", "pass");
        else
            if ($tag_cnt_style_lines < 100)
                addStatToFileListAnalysis(number_format($tag_cnt_style_lines), "Inline Style", "Lines", "warn");
            else
                addStatToFileListAnalysis(number_format($tag_cnt_style_lines), "Inline Style", "Lines", "fail");
        if ($tag_cnt_script_lines == 0)
            addStatToFileListAnalysis(number_format($tag_cnt_script_lines), "Inline Script", "Lines", "pass");
        else
            if ($tag_cnt_style_lines < 500)
                addStatToFileListAnalysis(number_format($tag_cnt_script_lines), "Inline Script", "Lines", "warn");
            else
                addStatToFileListAnalysis(number_format($tag_cnt_script_lines), "Inline Script", "Lines", "fail");
        return $str;
    }


    function joinFilePaths()
    {
        return preg_replace('~[/\\\]+~', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, array_filter(func_get_args(),


        function ($p)
        {
            return $p !== '';
        }
        ) ) );
    }


    function joinURLPaths()
    {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg)
        {
            $paths = array_merge($paths, (array) $arg);
        }
//echo ("<pre>");
//print_r(paths);
//echo ("</pre>");
        foreach ($paths as & $path)
        {
            $path = trim($path, '/');
        }
        if (substr($args[0], 0, 1) == '/')
        {
            $paths[0] = '/' . $paths[0];
        }
        return join('/', $paths);
    }


    function createDomainSaveDir($path)
    {
//echo "Func: CreateBasePath<br>";
debug ("creating basefilepath ",$path);
//echo("creating basefilepath: ".$path."<br/>");
//encode hyphen
        if (is_dir($path))
        {
            return true;
        }
        $prev_path = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR, - 2) + 1);
        $return = createDomainSaveDir($prev_path);
        return ($return && is_writable($prev_path)) ? mkdir($path, 0777, true) : false;
    }


    function createDomainSavePath($path)
    {
        debug("creating filepath", $path);
//echo("creating filepath: ".$path."<br/>");
        if (is_dir($path))
        {
            return true;
        }
        $prev_path = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR, - 2) + 1);
        $return = createDomainSavePath($prev_path);
        return ($return && is_writable($prev_path)) ? mkdir($path, 0777, true) : false;
    }


    function getArrayOfImageFiles()
    {
        global $arrayListOfImages;
//echo "unfiltered image list<br/>";
//echo"<pre>";
//print_r($arrayListOfImages);
//echo"</pre>";
//echo "<br/>";
        $unique = array_values(array_unique($arrayListOfImages));
//$unique = $arrayListOfImages;
//echo "<br/>filtered image list<br/>";
//echo"<pre>";
//print_r($unique);
//echo"</pre>";
//echo "<br/>";
        return $unique;
    }


    function getArrayOfHeaders()
    {
        global $arrayPageHeaders;
        $unique = array_values(array_unique($arrayPageHeaders, SORT_REGULAR));
        return $unique;
    }


    function getArrayOfObjects()
    {
        global $arrayOfObjects;
        $unique = array_values(array_unique($arrayOfObjects, SORT_REGULAR));
        return $unique;
    }


    function getArrayOfScriptFiles()
    {
        global $arrayListOfScriptFiles;
        $unique = array_values(array_unique($arrayListOfScriptFiles));
        return $unique;
    }


    function getArrayOfStylesheets()
    {
        global $arrayListOfStylesheets;
        $unique = array_values(array_unique($arrayListOfStylesheets));
        return $unique;
    }


    function getArrayOf3PImageFiles()
    {
        global $arrayListOf3PImages;
        $unique = array_values(array_unique($arrayListOf3PImages));
        return $unique;
    }


    function getArrayOf3PScriptFiles()
    {
        global $arrayListOf3PScriptFiles;
        $unique = array_values(array_unique($arrayListOf3PScriptFiles));
        return $unique;
    }


    function getArrayOf3PStylesheets()
    {
        global $arrayListOf3PStylesheets;
        $unique = array_values(array_unique($arrayListOf3PStylesheets));
        return $unique;
    }


    function getArrayOfLinks()
    {
        global $arrayListOfLinks;
        $unique = array_values(array_unique($arrayListOfLinks));
        return $unique;
    }


    function getArrayOfImageLinks()
    {
        global $arrayListOfImageLinks;
        $unique = array_values(array_unique($arrayListOfImageLinks));
        return $unique;
    }


    function getArrayOfErrors()
    {
        global $arrayErrors;
        if(is_array($arrayErrors))
            $arrayErrors = array_values(array_unique($arrayErrors,SORT_REGULAR));
        else
            $arrayErrors = array();
        return $arrayErrors;
    }


    function CompressFile($objtype, $inID, $infile, $boolAddtoGZIPStats)
    {
        global $filepath_domainsavedir, $gzipanalysis, $gziptotal_originalbytes, $gziptotal_zippedbytes, $compressionlevel, $arrayGZIPStats, $arrayPageObjects;
        $filename = $infile;
        $arr = array();
//echo "GZIPping $objtype file: " .$inID. ": ". $filename."<br/>";
        $rc = strtolower(isfilegzipped($inID));
//echo ($filename.": rc = ". $rc ."<br/>");
//if($rc == 'gzip' or $rc == 'deflate')
//{
//	//echo ($inID. ": ".$filename.": file is already gzipped!<br/>");
//}
//else
//else
        {
//echo ($inID. ": ".$filename.": file is not already gzipped!<br/>");
            switch ($objtype)
            {
                case 'JavaScript' :
                    $ot = "JS";
                    break;
                case 'StyleSheet' :
                    $ot = "CSS";
                    break;
                case 'HTML' :
                    $ot = "HTML";
                    break;
                Default :
                    $ot = $objtype;
                    break;
            }
            $folder = '_Optimised_' . $ot . DIRECTORY_SEPARATOR;
            $baseTextfolder = $filepath_domainsavedir . $folder;
            if (!file_exists($baseTextfolder))
                mkdir($baseTextfolder, 0777, true);
            $path_parts = pathinfo($infile);
            $optfilename = $baseTextfolder . $path_parts['filename'] . ".gz" . $compressionlevel;
//echo"saving as : ".$optfilename."<br/>";
            $srcfile = $arrayPageObjects[$inID]['Object source'];
//$filename = $arrayPageObjects[$inID]['Object file'];
            $domref = $arrayPageObjects[$inID]['Domain ref'];
            $objtype = $arrayPageObjects[$inID]['Object type'];
            $compstatus = $arrayPageObjects[$inID]['Compression'];
            $data = implode("", file($filename));
            $origsz = filesize($filename);
            $gzdata = gzencode($data, $compressionlevel);
            $fp = fopen($optfilename, "w");
            fwrite($fp, $gzdata);
            fclose($fp);
            $gzipsz = filesize($optfilename);
            $saving = $origsz - $gzipsz;
            $savingpct = getsavingpct($saving, $origsz);
            $x = $gzipsz;
//echo 'Compression level: '.$compressionlevel.'; File size is: '.$x.'; saving = ' .$saving .' bytes ('. $savingpct .  ') <br><br>';
            $arr = array($domref, $objtype, $srcfile,
//pathinfo($filename,PATHINFO_FILENAME).".".pathinfo($filename,PATHINFO_EXTENSION), //"Filename"
                $origsz, //"Original Filesize"
                $gzipsz, //"Compressed Filesize"
                $saving, //"Saving Bytes"
                $savingpct //"Saving Pct"
            );
//echo("compr. status: ".$srcfile . " = ".$compstatus."<br/>");
            if ($boolAddtoGZIPStats and $domref != 'redirection' and trim(strtolower($compstatus)) != 'gzip' and intval($saving) > 0) //and $domref != '3P'
            {
//echo ($inID . " - ".$filename.": adding gzip stats!<br/>");
                $gzipanalysis = $gzipanalysis . $filename . ': Original Filesize: ' . $origsz . ' bytes<br/>&nbsp;&nbsp;At Compression Level:' . $compressionlevel . '; File size is: ' . $x . ' bytes; saving = ' . $saving . ' bytes (' . $savingpct . ')<br/>';
                $gziptotal_originalbytes = intval($gziptotal_originalbytes) + intval($origsz);
                $gziptotal_zippedbytes = intval($gziptotal_zippedbytes) + intval($x);
                $arrayGZIPStats[] = $arr;
// don't add minified files to stats
            }
        }
        return ($arr);
    }


    function getCompressionFileStats()
    {
        global $gziptotal_originalbytes, $gziptotal_zippedbytes, $compressionlevel, $gzt, $gzipanalysis, $arrayTotals;
        $saving = $gziptotal_originalbytes - $gziptotal_zippedbytes;
        $savingpct = getsavingpct($saving, $gziptotal_originalbytes);
        $gzt = 'Total Original Uncompressed Bytes: ' . $gziptotal_originalbytes . '<br/>&nbsp;&nbsp;At Compression Level:' . $compressionlevel . '; Compressed Bytes: ' . $gziptotal_zippedbytes . '; saving = ' . $saving . ' bytes (' . $savingpct . ')<br/>';
        $gzipanalysis = $gzipanalysis . "<br/>" . $gzt;
        $arr = array("Filename" => "Totals", "Original Filesize" => $gziptotal_originalbytes, "Compressed Filesize" => $gziptotal_zippedbytes, "Saving Bytes" => $saving, "Saving Pct" => $savingpct);
        $arrayTotals[] = $arr;
    }


    function human_filesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @ $sz[$factor];
    }


    function getsavingpct($gbytes, $obytes, $decimals = 2)
    {
        if ($obytes > 0)
            $savingpct = $gbytes / $obytes * 100;
        else
            $savingpct = 0;
        return sprintf("%.{$decimals}f", $savingpct) . '%';
    }


    function isfilegzippedBINARY($fn)
    {
// check file written has 2 bytes identifier
        $boolrc = false;
        $fp = fopen($fn, 'r');
// move to the 1st byte
        fseek($fp, 1);
        $data = fread($fp, 2); // read 2 bytes from byte 7
        fclose($fp);
// put the bytes into an array
        $value = unpack('H*', $data);
        $value = bin2hex($data);
//echo("IsFileGZipped: ".$fn. " val= ". $value."<br/");
        if ($value == '1f8b')
        {
            $boolrc = true;
        }
        return $boolrc;
    }


    function isfilegzipped($inID)
    {
        global $arrayPageObjects;
// get compression value fro object table
        $CompressionMethod = $arrayPageObjects[$inID]['Compression'];
//echo("IsFileGZipped ". $inID." = ".$CompressionMethod."<br/>");
        return trim($CompressionMethod);
    }


    function extractHeadersFromCurlResponse($curlresponseheaders)
    {
//  echo"extractHeadersFromCurlResponse:<pre>";
//  print_r($curlresponseheaders);
//  echo"</pre>";
        $age = '';
        $cachecontrol = '';
        $cachecontrolPrivate = '';
        $cachecontrolPublic = '';
        $cachecontrolNoCache = '';
        $cachecontrolNoStore = '';
        $cachecontrolMaxAge = '';
        $cachecontrolMaxAge = '';
        $cachecontrolSMaxAge = '';
        $cachecontrolSMaxAge = '';
        $cachecontrolNoTransform = '';
        $cachecontrolMustRevalidate = '';
        $cachecontrolProxyRevalidate = '';
        $xservedby = '';
        $xcache = '';
        $xpx = '';
        $xedgelocation = '';
        $cfray = '';
        $xcdngeo = '';
        $xcdn = '';
        $connection = '';
        $contentencoding = '';
        $contentlength = '';
        $contenttype = '';
        $date = '';
        $etag = '';
        $expires = '';
        $keepalive = '';
        $lastmodifieddate = '';
        $pragma = '';
        $status = '';
        $server = '';
        $setcookie = '';
        $upgrade = '';
        $vary = '';
        $via = '';
        $responsecode = '';
        $protocol = '';
        $cookiecount = 0;
        for ($i = 0; $i < count($curlresponseheaders); $i++)
        {
            $crh = $curlresponseheaders[$i];
            if ($crh != '')
            {
                if (substr($crh, 0, 5) == 'HTTP/')
                {
                    if(substr($crh, 5, 1) == "2")
                    {
                        $protocol = substr($crh, 5, 1); 
                        $responsecode = substr($crh, 7);
//echo "curl 1 response code " . $responsecode  . "<br/>";
                    }
                    else
                    {
                    $protocol = substr($crh, 5, 3);
                    $responsecode = substr($crh, 9);
//echo "curl 2 response code " . $responsecode  . "<br/>";
                    }
                }
                else
                {
                    $pieces = explode(": ", $crh);
// echo "1) ".$pieces[0]; // piece1
// echo "; 2) ".$pieces[1]."<br/>"; // piece2
                    $s1 = $pieces[0];
                    if (count($pieces) > 1)
                    {
                        @ $s2 = $pieces[1];
                        $pieces[1] = trim($pieces[1]);
                        switch (strtolower($s1))
                        {
                            case "age" :
                                $age = $pieces[1];
//echo("age: ".$age."<br/>");
                                break;
                            case "cache-control" :
                                $cachecontrol = $pieces[1];
                                $ccpieces = explode(",", $cachecontrol);
//echo("cache-control...<br/>");
                                for ($ic = 0; $ic < count($ccpieces); $ic++)
                                {
                                    $cch = trim($ccpieces[$ic]);
//echo("cache-control: ".$cch."<br/>");
                                    $cchdirective = explode("=", $cch);
                                    switch (strtolower($cchdirective[0]))
                                    {
                                        case 'private' :
                                            $cachecontrolPrivate = $cchdirective[0];
                                            break;
                                        case 'public' :
                                            $cachecontrolPublic = $cchdirective[0];
                                            break;
                                        case 'no-cache' :
                                            $cachecontrolNoCache = $cchdirective[0];
                                            break;
                                        case 'no-store' :
                                            $cachecontrolNoStore = $cchdirective[0];
                                            break;
                                        case 'max-age' :
                                            $cachecontrolMaxAge = $cchdirective[0];
                                            $cachecontrolMaxAge = $cchdirective[1];
                                            break;
                                        case 's-maxage' :
                                            $cachecontrolSMaxAge = $cchdirective[0];
                                            $cachecontrolSMaxAge = $cchdirective[1];
                                            break;
                                        case 'no-transform' :
                                            $cachecontrolNoTransform = $cchdirective[0];
                                            break;
                                        case 'must-revalidate' :
                                            $cachecontrolMustRevalidate = $cchdirective[0];
                                            break;
                                        case 'must-revalidate' :
                                            $cachecontrolProxyRevalidate = $cchdirective[0];
                                            break;
                                    }
                                }
                                break;
                            case "connection" :
                                $connection = $pieces[1];
//echo("connection = ".$connection ."<br/>");
                                break;
                            case "content-encoding" :
                                $contentencoding = $pieces[1];
                                break;
                            case "content-length" :
                                $contentlength = $pieces[1];
//echo("content length = ".$contentlength ."<br/>");
                                break;
                            case "content-type" :
                                session_start();
                                $_SESSION['mimetype'] = $pieces[1];
                                session_write_close();
                                $ct = explode(";", $pieces[1]);
                                $contenttype = $ct[0];
//echo ("content type full: ". $pieces[1]."<br/>");
//echo ("content type 1st: ". $contenttype."<br/>");
                                break;
                            case "date" :
                                $datef = date_create($pieces[1]);
                                if ($datef !== false)
                                    $date = $datef->format('Y-m-d H:i:s');
                                else
                                    $date = $pieces[1];
                                break;
                            case "etag" :
                                $etag = $pieces[1];
                                break;
                            case "expires" :
                                $datef = date_create($pieces[1]);
                                if ($datef !== false)
                                    $expires = $datef->format('Y-m-d H:i:s');
                                else
                                    $expires = $pieces[1];
                                break;
                            case "keep-alive" :
                                $keepalive = $pieces[1];
                                break;
                            case "last-modified" :
                                $datef = date_create($pieces[1]);
                                if ($datef !== false)
                                    $lastmodifieddate = $datef->format('Y-m-d H:i:s');
                                else
                                    $lastmodifieddate = $pieces[1];
                                break;
                            case "pragma" :
                                $Pragma = $pieces[1];
                                break;
                            case "server" :
                                $server = $pieces[1];
                                break;
                            case "set-cookie" :
                                $cookiecount += 1;
                                $setcookie = $cookiecount;
                                break;
                            case "status" :
                                $status = $responsecode;
                                break;
                            case "upgrade" :
                                $upgrade = $pieces[1];
                                break;
                            case "vary" :
                                $vary = $pieces[1];
                                break;
                            case "via" :
                                $via = $pieces[1];
                                break;
                            case "x-cache" :
                                $xcache = $pieces[1];
                                break;
                            case "x-served-by" :
                                $xservedby = $pieces[1];
                                break;
                            case "x-px" :
                                $xpx = $pieces[1];
                                break;
                            case "x-edge-location" :
                                $xedgelocation = $pieces[1];
                                break;
                            case "cf-ray" :
                                $cfray = $pieces[1];
                                break;
                            case "x-cdn-geo" :
                                $xcdngeo = $pieces[1];
                                break;
                            case "x-cdn" :
                                $xcdn = $pieces[1];
//echo "xcdn found: " .$xcdn."<br/>";
                                break;
                        } // end switch
                    }
                } //a header, not the HTTP repsonse code
            } // header not empty
        }

       // echo("returned responsecode = " . $responsecode . "<br/>");
        return array($protocol, $responsecode, $age, $cachecontrol, $cachecontrolPrivate, $cachecontrolPublic, $cachecontrolNoCache, $cachecontrolNoStore, $cachecontrolMaxAge, $cachecontrolSMaxAge, $cachecontrolNoTransform, $cachecontrolMustRevalidate, $cachecontrolProxyRevalidate, $connection, $contentencoding, $contentlength, $contenttype, $date, $etag, $expires, $keepalive, $lastmodifieddate, $pragma, $server, $setcookie, $upgrade, $vary, $via, $xcache, $xservedby, $xpx, $xedgelocation, $cfray, $xcdngeo, $xcdn);
    }


    function downloadAllObjects($ListOfFiles)
    {
        global $arrayPageObjects, $debug, $totfilesize, $embeddedfile_count, $embeddedcount, $totbytesdownloaded, $html, $redirect_count,$loadContentFromHAR;
//echo("Download all objects<pre>");
//print_r($ListOfFiles);
//echo("</pre>");
        $nooffiles = count($arrayPageObjects);
        debug("<br/>DOWNLOADING FILES", 'init count: ' . $nooffiles);
        session_start();
        $_SESSION['status'] = 'Downloading Objects';
        $_SESSION['mimetype'] = '';
        $_SESSION['object'] = '';
        session_write_close();
        if ($debug == true)
        {
            foreach ($arrayPageObjects as $key => $valuearray)
            {
//var_dump($valuearray);
                $value = $valuearray["Object source"];
                $local = $valuearray["Object file"];
                debug($key, $value);
//error_log($key,$value."<br/>");
            }
        }
        $objectDownloadCount = $redirect_count + 1;
        foreach ($arrayPageObjects as $key => $valuearray)
        {
            if ($key == 0 and $html != 'Disallowed Key Characters.' and $loadContentFromHAR == false)
            {
                debug("<br/>Downloaded previously: 1 of $nooffiles;  file", "");
                continue;
            }
            $value = $valuearray["Object source"];
            $fname = basename($value);
            if (strpos($fname, '?'))
                $fname = substr($fname, 0, strpos($fname, '?'));
//var_dump($valuearray);
            $objectDownloadCount += 1;
            $nooffiles = count($arrayPageObjects);
            if($nooffiles < $objectDownloadCount)
                $nooffiles = $objectDownloadCount;
            session_start();
            if($loadContentFromHAR == false)
                $_SESSION['status'] = 'Downloading and Analysing Object ' . $objectDownloadCount . " of " . $nooffiles;
            else
            $_SESSION['status'] = 'Analysing Object ' . $objectDownloadCount . " of " . $nooffiles;
            $_SESSION['object'] = html_entity_decode($fname);
            $_SESSION['mimetype'] = '';
            session_write_close();
//error_log("Downloading file ".$key." (". $fname."): memory usage = ". memory_get_usage(true));
            downloadObject($key, $valuearray);
//error_log("Downloaded file ".$key." (". $fname."): memory usage = ". memory_get_usage(true));
        }
        $nooffiles = count($arrayPageObjects);
        $totdl = formatBytes($totbytesdownloaded, 2);
        $totdla = explode(" ", $totdl);
        if (($totdla[0] < 500 and $totdla[1] == "Kilobytes") or $totdla[1] == "Bytes")
            addStatToFileListAnalysis($totdla[0], $totdla[1], "Downloaded", "pass");
        else
            if ($totdla[0] < 750 and $totdla[1] == "Kilobytes")
                addStatToFileListAnalysis($totdla[0], $totdla[1], "Downloaded", "warn");
            else
                addStatToFileListAnalysis($totdla[0], $totdla[1], "Downloaded", "fail");
        if ($nooffiles - $embeddedcount <= 36)
            addStatToFileListAnalysis(number_format($nooffiles - $embeddedcount), "Objects", "Downloaded", "pass");
        else
            addStatToFileListAnalysis(number_format($nooffiles - $embeddedcount), "Objects", "Downloaded", "fail");
        if ($embeddedcount == 1)
            addStatToFileListAnalysis(number_format($embeddedcount), "Object", "Embedded", "info");
        else
            addStatToFileListAnalysis(number_format($embeddedcount), "Objects", "Embedded", "info");
//if($embeddedfile_count == 1)
//    addStatToFileListAnalysis(number_format($embeddedfile_count),"Image","Embedded");
//else
//    addStatToFileListAnalysis(number_format($embeddedfile_count),"Images","Embedded");
        debug("DOWNLOAD COMPLETE", "");

    // echo("<pre>");
	// print_r($arrayPageObjects);
    // echo("</pre>");


//	echo("end headers " . json_encode($arrayPageHeaders));

    } // end function downloadAllObjects
    function is_valid_filename($name)
    {
        if (preg_match('/^[a-z0-9-]+\.ext$/', $name))
            return false;
        else
            return true;
    }


    function copyImageFilesToFolders()
    {
        global $arrayPageObjects, $filepath_domainsavedir, $filepath_domainsaverootdir;
        debug("COPY IMAGE FILES TO FOLDERS","");
//echo ($filepath_domainsavedir.'<br/>');
//echo ($filepath_domainsaverootdir.'<br/>');
        $nooffiles = count($arrayPageObjects);
        foreach ($arrayPageObjects as $key => $valuearray)
        {
//var_dump($valuearray);
            $value = $valuearray["Object source"];
            $local = $valuearray["Object file"];
            $ftype = $valuearray["Mime type"];
            $itype = $valuearray["Object type"];
// don't reprocess root object
            if ($itype == "Image")
            {
//echo ("Found $ftype Image to copy: $local<br/>");
                $path_parts = pathinfo($local);
                $filename = $path_parts['filename'];
                $ext = $path_parts['extension'];
debug("Found image to copy", $local);
                $folder = '_Extracted_Images';
                $baseImgfolder = $filepath_domainsavedir . $folder;
                if (!file_exists($baseImgfolder))
                    @ mkdir($baseImgfolder, 0777, true);
// set folder name = subtype
                if ($ftype != '')
                {
                    $aMediaType = explode('/', $ftype);
                    if (sizeof($aMediaType) > 1)
                        $folder = DIRECTORY_SEPARATOR . $aMediaType[1] . DIRECTORY_SEPARATOR;
                    else
                    {
                        $folder = DIRECTORY_SEPARATOR . $ftype . DIRECTORY_SEPARATOR;
                        error_log("copyImageFilesToFolders: unknown media type: " . $ftype);
                    }
                }
                else
                    $folder = DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR;
                $Imgfolder = $baseImgfolder . $folder;
                if (!file_exists($Imgfolder))
                    @ mkdir($Imgfolder, 0777, true);
                $result = false;
                if (file_exists($local))
                {
                    error_log( "trying to copy image " . $filename);
                    $result = copy($local, $Imgfolder . $filename . "." . $ext);
                }
                if($result == true)
                    error_log( "image copy successful" . $filename . $result);
                else
                error_log( "image copy failed " . $filename . $result);
            } // an image
        }
    }


    function xcopy($source, $dest, $permissions = 0755)
    {
// Check for symlinks
        if (is_link($source))
        {
            return symlink(readlink($source), $dest);
        }
// Simple copy for a file
        if (is_file($source))
        {
            return copy($source, $dest);
        }
// Make destination directory
        if (!is_dir($dest))
        {
            mkdir($dest, $permissions);
        }
// Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read())
        {
// Skip pointers
            if ($entry == '.' || $entry == '..')
            {
                continue;
            }
// Deep copy directories
            xcopy("$source/$entry", "$dest/$entry");
        }
    }


    function sanitize_file_name($string, $force_lowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "—", "–", ",", "<", ">", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', " ", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return ($force_lowercase) ? (function_exists('mb_strtolower')) ? mb_strtolower($clean, 'UTF-8') : strtolower($clean) : $clean;
    }


    function getCSSJSOrdering($mode)
    {
        global $html, $arrayOrderedCSSJS, $url, $headcss, $headjs, $bodycss, $bodyjs, $roothost, $objcount;
        if ($mode == 'source')
        {
            $headcss = 0;
            $headjs = 0;
            $bodycss = 0;
            $bodyjs = 0;
        }
// Find all src and href tags in the head
        foreach ($html->find('head') as $hd)
        {
//echo "<br/>Within the HEAD tag:";
// Find all scripts and css files
            foreach ($hd->find('script, link[rel="stylesheet"],link[rel="alternate stylesheet"],style') as $element)
            {
//echo "element found: " . $element->tag . "<br/>";
                switch ($element->tag)
                {
                    case "script" :
//echo "<br/>" . $mode . " head JavaScript " . $element->tag ."; source = " .  $element->src. "<br/>";
                        $vsrc = $element->src;
//echo "source js found: '".$vsrc."'<br/>";
                        if (isset($vsrc) and !empty($vsrc)) // can be unset for inline script
                        {
                            $vsrc = url_to_absolute($url, $vsrc);
// check if this is a modernizr script
                            if (strpos(strtolower($vsrc), "modernizr") !== false)
                                $js = "JavaScript (Modernizr)";
                            else
                                $js = "JavaScript";


                            $arr = array("Section" => "HEAD", "Timing" => $mode, "File Type" => $js, "File" => trim($vsrc));
                            $skey = array_search_innerCSSJS($arrayOrderedCSSJS, 'File', $vsrc);
                            if ($skey === false and $url != $vsrc)
                            {
                                $arrayOrderedCSSJS[] = $arr;
                                if ($mode != "injected")
                                    $headjs += 1;
                            }
                        }
                        else
                        {
//echo "; INLINE";
                        }
                        break;
                    case "link" :
//echo "<br/>" . $mode . " head Stylesheet " . $element->tag ."; source = " .  $element->src . "<br/>";
//echo "<br/>Stylesheet";
                        $vhref = $element->href;
                        if (isset($vhref))
                        {
                            $vhref = url_to_absolute($url, $vhref);
                            $arr = array("Section" => "HEAD", "Timing" => $mode, "File Type" => "StyleSheet", "File" => trim($vhref));
                            $skey = array_search_innerCSSJS($arrayOrderedCSSJS, 'File', $vhref);
                            if ($skey === false)
                            {
                                $arrayOrderedCSSJS[] = $arr;
                                $headcss += 1;
                            }
                        }
                        break;

                        case "style" :   
//echo ("style element found in head: " . $element->innertext." ");
                            // check for @import statements
                            $imports = explode(";",$element->innertext);
//echo (" with " . count($imports) . " potential @import statements" ."</br>"); 
                            
                            foreach ($imports as &$value) {
                                // check for @import
                                if(strpos($value,"@import") != false)
                                {
                                    //@import found
//echo ("value: " . $value . "<br/>");
                                    // find url src text between quotes
                                    preg_match('/"(.*?)"/', $value, $cssurl);     
//echo ("url0: " . $cssurl[0] . "<br/>");
//echo ("url1: " . $cssurl[1] . "<br/>");
                                    $vsrc = $cssurl[1];
                                    $vhref = url_to_absolute($url, $vsrc);
                                    $arr = array("Section" => "HEAD", "Timing" => $mode, "File Type" => "StyleSheet", "File" => trim($vhref));
                                    $skey = array_search_innerCSSJS($arrayOrderedCSSJS, 'File', $vhref);
                                    if ($skey === false)
                                    {
                                        $arrayOrderedCSSJS[] = $arr;
                                        $headcss += 1;
                                    }

                                }

                            }

                         break;



                }
            }
        }
        if ($headjs > 0)
            addTestResult("6.1", "6", "Place JavaScript in the BODY", "Fail");
        else
            addTestResult("6.1", "6", "Place JavaScript in the BODY", "Pass");
// Find all src and href tags in the body
        foreach ($html->find('body') as $bd)
        {
//echo "<br/><br/>Within the BODY tag:";
// Find all anchors and images
            foreach ($bd->find('script, link[rel="stylesheet"], link[rel="alternate stylesheet"],style') as $element)
            {
                switch ($element->tag)
                {
                    case "script" :
//echo "<br/>" . $mode . " body JavaScript " . $element->tag ."; source = " .  $element->src. "<br/>";
//echo "<br/>JavaScript";
                        $vsrc = $element->src;
//echo "body js found: '".$vsrc."'<br/>";
                        if (isset($vsrc) and !empty($vsrc)) // can be unset for inline script
                        {
// get absolute name of file if not inline script (= initial domain)
                            $vsrc = url_to_absolute($url, $vsrc);

                            $arr = array("Section" => "BODY", "Timing" => $mode, "File Type" => "JavaScript", "File" => trim($vsrc));
                            $skey = array_search_innerCSSJS($arrayOrderedCSSJS, 'File', $vsrc);
                            if ($skey === false and $url != $vsrc)
                            {
                                $arrayOrderedCSSJS[] = $arr;
//echo "adding $vsrc to arrayOrderedCSSJS<br/>";
                                $bodyjs += 1;
                            }
                            else
                            {
//echo "NOT adding $vsrc to arrayOrderedCSSJS<br/>"; 
                            }
                        }
                        else
                        {
//echo "; INLINE";
                        }
                        break;
                    case "link" :
//echo "<br/>" . $mode . " body StyleSheet " . $element->tag ."; source = " .  $element->src. "<br/>";
//echo "<br/>Stylesheet";
                        $vhref = $element->href;
                        if (isset($vhref))
                        {
//echo "; ".$vhref.": extracted images";
                            $vhref = url_to_absolute($url, $vhref);
                            $cssfile = $url . "/" . $vhref;
//$cssstr = file_get_contents($cssfile);
//$cssimgs = array();
//$cssimgs =  extract_css_urls($cssstr);
                            $arr = array("Section" => "BODY", "Timing" => $mode, "File Type" => "StyleSheet", "File" => trim($vhref));
                            $skey = array_search_innerCSSJS($arrayOrderedCSSJS, 'File', $vhref);
                            if ($skey === false)
                            {
                                $arrayOrderedCSSJS[] = $arr;
                                $bodycss += 1;
                            }
//var_dump($cssimgs);
                        }
                        break;
                        case "style" :   
//echo ("style element found in body: " . $element->innertext." ");
                            // check for @import statements
                            $imports = explode(";",$element->innertext);
//echo (" with " . count($imports) . " potential @import statements" ."</br>"); 
                            
                            foreach ($imports as &$value) {
                                // check for @import
                                if(strpos($value,"@import") != false)
                                {
                                    //@import found
//echo ("value: " . $value . "<br/>");
                                    // find url src text between quotes
                                    preg_match('/"(.*?)"/', $value, $cssurl);     
//echo ("url0: " . $cssurl[0] . "<br/>");
//echo ("url1: " . $cssurl[1] . "<br/>");
                                    $vsrc = $cssurl[1];
                                    $vhref = url_to_absolute($url, $vsrc);
                                    $arr = array("Section" => "BODY", "Timing" => $mode, "File Type" => "StyleSheet", "File" => trim($vhref));
                                    $skey = array_search_innerCSSJS($arrayOrderedCSSJS, 'File', $vhref);
                                    if ($skey === false)
                                    {
                                        $arrayOrderedCSSJS[] = $arr;
                                        $headcss += 1;
                                    }

                                }

                            }

                         break;


                }
            }
        }
        if ($bodycss > 0)
            addTestResult("5.1", "5", "Place Stylesheets in the HEAD", "Fail");
        else
            addTestResult("5.1", "5", "Place Stylesheets in the HEAD", "Pass");
        if ($mode == 'injected')
        {
//work through all injected files and add to download list
//echo "injected files<br/>";
            foreach ($arrayOrderedCSSJS as $key => $valuearray)
            {
// echo "<br/>".$key . " arrayOrderedCSSJS <pre>";
// print_r($valuearray);
// echo("</pre><br/>");
// add file to object list
                $str = $valuearray['File'];
// sanituze  url
//$str = sanitize_file_name($str, $force_lowercase = false, $anal = false);
                $str = str_replace(chr(10), '', $str);
                $str = str_replace(chr(13), '', $str);
                debug($mode . " file ABSOLUTE URL '", $str . "'");
//test if this file is on a CDN
                list($hd, $hp) = getDomainHostFromURL($str, false, "injected files");
                $testdomain = $hd;
//echo("checking CDN+3P: roothost: $roothost - testdomain: $hd<br/>");
                if ($roothost == $hd)
                {
                    debug("injected file on primary domain", "'" . $str . "'");
                    $domref = 'Primary';
                }
                else
                {
                    $domsrc = IsThisDomainaCDNofTheRootDomain($roothost, $testdomain);
                    switch ($domsrc)
                    {
                        case 'CDN' :
                        case 'cdn' :
                            debug("CDN injected", "'" . $str . "'");
                            $domref = 'CDN';
                            break;
                        case 'Shard' :
                        case 'shard' :
                            debug("Shard injected", "'" . $str . "'");
                            $domref = 'Shard';
                            break;
                        default :
                            debug("3rd party injected", "'" . $str . "'");
                            $domref = '3P';
                    }
                } // end is this domain a CDN
// add to object array
//echo($valuearray['File'] . ": section:" . $valuearray['Section'] . "; timing: " . $valuearray['Timing']);
                $arr = array("Object type" => $valuearray['File Type'], "Object source" => $valuearray['File'], "Object file" => '', "Object parent" => $url, "Mime type" => '', "Domain" => $hd, "Domain ref" => $domref, "HTTP status" => '', "File extension" => '', "CSS ref" => '', "Header size" => '', "Content length transmitted" => 0, "Content size downloaded" => 0, "Compression" => '', "Content size compressed" => '', "Content size uncompressed" => '', "Content size minified uncompressed" => '', "Content size minified compressed" => '', "Combined files" => '', "JS defer" => '', "JS async" => '', "JS docwrite" => '', "Image type" => '', "Image encoding" => '', "Image responsive" => '', "Image display size" => '', "Image actual size" => '', "Metadata bytes" => 0, "EXIF bytes" => 0, "APP12 bytes" => 0, "IPTC bytes" => 0, "XMP bytes" => 0, "Comment" => '', "Comment bytes" => 0, "ICC colour profile bytes" => 0, "Colour type" => '', "Colour depth" => '', "Interlace" => '', "Est. quality" => '', "Photoshop quality" => '', "Chroma subsampling" => '', "Animation" => '', "Font name" => '', "hdrs_Server" => '', "hdrs_Protocol" => '', "hdrs_responsecode" => '', "hdrs_age" => '', "hdrs_date" => '', "hdrs_lastmodifieddate" => '', "hdrs_cachecontrol" => '', "hdrs_cachecontrolPrivate" => '', "hdrs_cachecontrolPublic" => '', "hdrs_cachecontrolMaxAge" => '', "hdrs_cachecontrolSMaxAge" => '', "hdrs_cachecontrolNoCache" => '', "hdrs_cachecontrolNoStore" => '', "hdrs_cachecontrolNoTransform" => '', "hdrs_cachecontrolMustRevalidate" => '', "hdrs_cachecontrolProxyRevalidate" => '', "hdrs_connection" => '', "hdrs_contentencoding" => '', "hdrs_contentlength" => '', "hdrs_expires" => '', "hdrs_etag" => '', "hdrs_keepalive" => '', "hdrs_pragma" => '', "hdrs_setcookie" => '', "hdrs_upgrade" => '', "hdrs_vary" => '', "hdrs_via" => '', "hdrs_xservedby" => '', "hdrs_xcache" => '', "hdrs_xpx" => '', "hdrs_xedgelocation" => '', "hdrs_cfray" => '', "hdrs_xcdngeo" => '', "hdrs_xcdn" => '',
                 "response_datetime" => '',
                 "file_section" => $valuearray['Section'],
                 "file_timing" => $valuearray['Timing'],
                		"offsetDuration" => '',
                        "ttfbMS" => '',
                        "downloadDuration" => '',
                        "allMS" => '',
                        "allStartMS" => '',
                        "allEndMS" => '',
                        "cacheSeconds" => '',);
                
//echo "called lookupPageObject with " . $valuearray['File'];
                list($id, $lfn) = lookupPageObject($valuearray['File']);
                debug("injected arrayorderedCSSJS - check if file exists: " . is_numeric($id));
                if(!is_numeric($id))
                {
                    //list($id, $destobjtype) = lookupPageObjectValue($rd_dest, "Object type");
//echo "arrayorderedCSSJS id not numeric '" . $id . "'  is numeric = " . is_numeric($id) . "<br/>";
                }
                else
                {
                    // new object
                    addUpdatePageObject($arr);
                    $objcount = $objcount + 1;
                }
            }
        }
    }


    function detectJSLibs()
    {
        global $arrayPageObjects, $cms;
        $boolJSJquery = false;
        $boolJSDojo = false;
        $boolJSAngular = false;
        $boolJSMootools = false;
        $boolJSYUI = false;
        $boolJSPrototype = false;
        $boolCMSWordpress = false;
        $c = count($arrayPageObjects);
        for ($i = 0; $i < $c; $i++)
        {
            $vsrc = $arrayPageObjects[$i]['Object source'];
            $vtype = strtolower($arrayPageObjects[$i]['Object type']);
//echo($vsrc. " ". $vtype."<br/>");
            if ($vtype == 'javascript')
            {
//echo($vsrc." js found - checking libray usage<br/>");
                $vsrc = strtolower($vsrc);
// 	check for known javascript libraries
                if (strpos($vsrc, 'jquery') !== false)
                    $boolJSJquery = true;
                if (strpos($vsrc, 'yui') !== false and strpos($vsrc, 'jquery') === false)
                    $boolJSYUI = true;
                if (strpos($vsrc, 'dojo') !== false)
                    $boolJSDojo = true;
                if (strpos($vsrc, 'angular') !== false)
                    $boolJSAngular = true;
                if (strpos($vsrc, 'mootools') !== false)
                    $boolJSMootools = true;
                if (strpos($vsrc, 'prototype') !== false)
                    $boolJSPrototype = true;
// CMS
                if (strpos($vsrc, 'wp-include') !== false)
                    $boolCMSWordpress = true;
            }
        }
// JS libraries
        if ($boolJSJquery == true)
            addStatToFileListAnalysis('jQuery', "", "JS Library");
        if ($boolJSYUI == true)
            addStatToFileListAnalysis('Yahoo! UI', "", "JS Library");
        if ($boolJSDojo == true)
            addStatToFileListAnalysis('Dojo Toolkit', "", "JS Library");
        if ($boolJSAngular == true)
            addStatToFileListAnalysis('AngularJS', "", "JS Library");
        if ($boolJSMootools == true)
            addStatToFileListAnalysis('Mootools', "", "JS Library");
        if ($boolJSPrototype == true)
            addStatToFileListAnalysis('Prototypr', "", "JS Library");
// CMS
//echo('CMS: ' .$cms);
        if ($cms != '')
            addStatToFileListAnalysis($cms, "", "CMS");
        else
            if ($boolCMSWordpress == true)
                addStatToFileListAnalysis('Wordpress', "", "CMS");
    }


    function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('Bytes', 'Kilobytes', 'Megabytes', 'Gigabytes', 'Terabytes');
        $r = round(pow(1024, $base - floor($base)), $precision) . " " . $suffixes[floor($base)];
//echo($r);
        return $r;
    }


    if (!function_exists('json_esc'))
    {


        function json_esc($input, $esc_html = true)
        {
            $result = '';
            if (!is_string($input))
            {
                $input = (string) $input;
            }
            $conv = array("\x08" => '\\b', "\t" => '\\t', "\n" => '\\n', "\f" => '\\f', "\r" => '\\r', '"' => '\\"', "'" => "\\'", '\\' => '\\\\');
            if ($esc_html)
            {
                $conv['<'] = '\\u003C';
                $conv['>'] = '\\u003E';
            }
            for ($i = 0, $len = strlen($input); $i < $len; $i++)
            {
                if (isset($conv[$input[$i]]))
                {
                    $result .= $conv[$input[$i]];
                }
                else
                    if ($input[$i] < ' ')
                    {
                        $result .= sprintf('\\u%04x', ord($input[$i]));
                    }
                    else
                    {
                        $result .= $input[$i];
                }
            }
            return $result;
        }


    }


    function detectTagManager($lfn, $domain, $srcfile)
    {
        global $arrayTagManagers;
// identify file as a tag management file
// BRIGHTTAG
        if ($domain == "www.sitetagger.co.uk")
        {
            $tagman = "SiteTagger";
            $vendor = "Signal";
        }
        if ($domain == 's.btstatic.com')
        {
            $tagman = "Brighttag";
            $vendor = "Signal";
        }
// GOOGLE
        if ((($domain == 'www.google.co.uk' or $domain == "www.google.com") and strpos(strtolower($lfn), 'tagmanager.min.js') !== false) or $domain == "www.googletagmanager.com")
        {
            $tagman = "Google Tag Manager";
            $vendor = "Google";
        }
        if (strpos(strtolower($lfn), 'fls.doubleclick.net') !== false)
        {
            $tagman = "Doubleclick Floodlight";
            $vendor = "Google";
        }
// QUBIT OPENTAG
        if (strpos(strtolower($lfn), 'opentag') !== false)
        {
            $filecontent = file_get_contents($lfn);
//echo("<pre>");
//print_r($filecontent);
//echo("</pre>");
            if (strpos(strtolower($filecontent), 'opentag.qubitproducts.com') !== false and strpos(strtolower($lfn), 'smartserve') === false)
            {
                $tagman = "OpenTag";
                $vendor = "Qubit";
            }
            unset($filecontent);
        }
// ADOBE
        if ($domain == 'assets.adobedtm.com' and (strpos(strtolower($lfn), 'satellite-') !== false or strpos(strtolower($lfn), 's-code-contents-') !== false))
        {
            $tagman = "Dynamic Tag Management";
            $vendor = "Adobe Systems";
        }
// ADOBE - self hosted
//  echo("lfn:" . $lfn . "<br/>");
        if (strpos(strtolower($lfn), 'dtm.js') !== false)
        {
//echo("dtm.js lfn:" . $lfn . "<br/>");
            $filecontent = file_get_contents($lfn);
//echo("<pre>");
//print_r($filecontent);
//echo("</pre>");
            if (strpos(strtolower($filecontent), '_satellite.init') !== false)
            {
                $tagman = "Dynamic Tag Management";
                $vendor = "Adobe Systems";
            }
        }
// STORM
        if ($domain == 'js.stormcontainertag.com' or $domain == 'js.stormiq.com')
        {
            $filecontent = file_get_contents($lfn);
//echo("<pre>");
//print_r($filecontent);
//echo("</pre>");
            if (strpos(strtolower($filecontent), 'stormcontainer') !== false)
            {
                $tagman = "Storm Tag Manager";
                $vendor = "Rakuten";
            }
            unset($filecontent);
        }
// TEALIUM UTAG
        if (strpos(strtolower($lfn), 'utag.js') !== false)
        {
            $filecontent = file_get_contents($lfn);
//echo("<pre>");
//print_r($filecontent);
//echo("</pre>");
            if (strpos(strtolower($filecontent), 'tealium universal tag') !== false)
            {
                $tagman = "IQ";
                $vendor = "Tealium";
            }
            unset($filecontent);
        }
// IMPACT RADIUS
        if ($domain == 'd3cxv97fi8q177.cloudfront.net' and strpos(strtolower($lfn), 'foundation-') !== false)
        {
            $tagman = "Foundation";
            $vendor = "Impact Radius";
        }
//ENSIGHTEN Nexus Tag Delivery Network
        if ($domain == 'nexus.ensighten.com')
        {
            $tagman = "Nexus Tag Delivery Network";
            $vendor = "Ensighten";
        }
//ENSIGHTEN TagMan Tag Delivery Network
        if ($domain == 'res.levexis.com' or $domain == 'pfa.levexis.com' or $domain == 'sec.levexis.com')
        {
            $filecontent = file_get_contents($lfn);
            if (strpos(strtolower($filecontent), 'tagman ltd') !== false or strpos($filecontent, 'TAGMAN') !== false)
            {
                $tagman = "Manage";
                $vendor = "Ensighten";
            }
        }
// IBM Digital Data Exchange
        if ($domain == 'tagmanager.coremetrics.com')
        {
            $tagman = "Digital Data Exchange";
            $vendor = "IBM";
        }
// TAG COMMANDER
        if ($domain == 'cdn.tagcommander.com')
        {
            $tagman = "TagCommander";
            $vendor = "TagCommander";
        }
// UBERTAGS
        if (strpos(strtolower($lfn), 'ubertags.js') !== false)
        {
            $tagman = "Tag Management System";
            $vendor = "UberTags by Rocketfuel";
        }
// DATALICIOUS SUPERTAGS
        if ($domain == 'c.supert.ag' and strpos(strtolower($lfn), 'supertag.js') !== false)
        {
            $tagman = "SuperTag";
            $vendor = "Datalicious";
        }
// INNOMETRICS ??
// CONVERSANT TAG MAMAGER
        if (strpos(strtolower($domain), 'mastertmd.com') != false)
        {
            $tagman = "MasterTMS";
            $vendor = "Conversant";
        }
        if (strpos(strtolower($domain), 'mplxtms.com') != false)
        {
            $tagman = "Conversant Tag Manager";
            $vendor = "Conversant";
        }
        if (!empty($tagman))
        {
//echo ("Tag Manager: ".$tagman."<br/>");
// add tag manger to tag manager array if not already added
           //if (!in_multiarray($tagman, $arrayTagManagers))
            {
                $arr = array("Tagman" => $tagman, "Vendor" => $vendor, "File" => $srcfile);
                $arrayTagManagers[] = $arr;
            }
        }
    }


    function detect3PJSFile($lfn, $domain, $srcfile)
    {
        global $host_domain, $arrayHost3PFiles, $arrayDomains, $arraySelfHosted3pDescriptions;
// 1) extract comments
        if(!file_exists($lfn))
            return false;

        $filecontent = file_get_contents($lfn);
//echo("<pre>");
//print_r($filecontent);
//echo("</pre>");
        preg_match_all("#\/\*" . "((?:(?!\*\/).)*)" . "\*\/#misU", $filecontent, $matches); // comments beween */ and /*'
        if (count($matches) > 0)
        {
//echo("comments from file: ". $srcfile."<pre>");
//print_r($matches);
//echo("</pre>");
        }
//
// 2) look for 3rd party files on the host domain or shard!
        if ($domain == $host_domain)
        {
//echo("<pre>");
//print_r($filecontent);
//echo("</pre>");
            $desc = '';
            $provider = '';
            $cat = '';
            $fname = '';
            $definingtext = '';
            foreach ($arraySelfHosted3pDescriptions as $line_num => $line)
            {
                $arr = explode("\t", $line);
//error_log($arr[0]);
                if(count($arr) != 7)
                {

                }
                else
                {
                    $fname = strtolower($arr[0]);
                    $definingtext = strtolower($arr[1]);
                    $provider = $arr[2];
                    $category = html_entity_decode($arr[3]);
                    $desc = html_entity_decode($arr[4]);
                    $product = html_entity_decode($arr[5]);
                    $group = html_entity_decode($arr[6]);
                    if (!isset($product))
                        error_log($dfname . " " . $provider . " " . $cat . " " . $desc . " " . $product . " " . $group);
    //echo("checking for selfhosted 3p file".$lfn." for ".$fname."<br/>");
                    if ($fname != 'filename' and strpos(strtolower($lfn), $fname) !== false and strpos(strtolower($filecontent), $definingtext) !== false)
                    {
    //echo("found selfhosted 3p file".$lfn." for ".$fname."<br/>");
    //if(!in_multiarray($fname,$arrayHost3PFiles))
    //    {
    //      $arr = array(
    //          "File" => $fname,
    //          "Vendor" => $provider,
    //          "Product" => $product,
    //          "Category" => $category,
    //          "Desc" => $desc
    //      );
    //      $arrayHost3PFiles[] = $arr;
    //    }
                        debug("Adding local domain for self-hosted 3p file ", $fname);
                        $arr = array("Domain Name" => 'self-hosted (' . $fname . ')', "Count" => 1, "Bytes" => '', "Domain Type" => 'self-hosted', "Network" => '', "Service" => '', "Site Description" => $desc, "Company" => $provider, "Category" => $category, "Product" => $product, "Group" => $group, "Location" => '', "Edge Name" => '', "Edge Loc" => '', "Edge IP" => '', "Latitude" => '', "Longitude" => '', "Distance" => '', "Method" => '',);
                        $arrayDomains[] = $arr;
    //echo("Adding local domain for self-hosted 3p file: ".$fname."<pre>");
    //print_r($arrayDomains);
    //echo("</pre>");
                        break;
                    }
                }
            }
        } // end for each selfhosted domain
        unset($filecontent);
    }


    function Identify3Pchains()
    {
// work through all objects
// for each third party object, get domain and look in all preceding JS objects for it
        global $arrayPageObjects, $arrayThirdPartyChain, $host_domain, $arrayOtherRedirs;

        debug("THIRD PARTY CALL CHAIN ANALYSIS","");

        $matchlevel = 99;
        $objectcount = count($arrayPageObjects);
//echo ("Third Party References<br/>");
        foreach ($arrayPageObjects as $key => $valuearray)
        {
//echo("key:".$key." = value: " .$valuearray["id"]. "<br/>");
//print_r($valuearray);
// get domain stats
            $objid = $valuearray["id"];
            $objurl = $valuearray["Object source"];
            $domref = $valuearray["Domain ref"];
            $domain = $valuearray["Domain"];
            $objtype = $valuearray["Object type"];
            session_start();
            $_SESSION['status'] = 'Identifying Third Party Call Chain (' . $key . ' of ' . $objectcount . ')';
            $_SESSION['object'] = $domain;
            session_write_close();
            if (strpos($objurl, "//") != false)
                $urlwithoutscheme = substr($objurl, strpos($objurl, '//') + 2);
            else
                $urlwithoutscheme = $objurl;
//echo($objid.": checking domain for url ". $urlwithoutscheme . "; domref = '".  $domref ."'<br/>");
// only lookup for objects on 3P domains, CDNs, redirs and include Adobe analytics on a shard
            if ($domref == '3P' or $domref == 'CDN' or $domref == 'Shard' or ($domref == 'redirection' and $domain != $host_domain) or ($domref == 'Shard') and strpos($domain,'metrics.') != false )
            {
//echo("<br/>checking 3rd party chain for id: ".$objid.": url ". $urlwithoutscheme ."<br/>");
//echo ("<br/>");
                $redirfound = false;
                $matchlevel = 99;
// check redirect list first
                if (sizeof($arrayOtherRedirs) > 0)
                {
//echo("redirect list <pre>");
//print_r($arrayOtherRedirs);
//echo("</pre>end of redirect list");

                    foreach ($arrayOtherRedirs as $rd_key => $rd_value)
                    {
                        $rd_source = $rd_value["From"];
                        $rd_dest = $rd_value["To"];
                        $match = $rd_value["Method"];
                        $matchlevel = 3;
                        list($destid, $destobjtype) = lookupPageObjectValue($rd_dest, "Object type");
                        list($sourceid, $sourceobjtype) = lookupPageObjectValue($rd_source, "Object type");
                        if ($rd_source == $objurl)
                        {
                            $arr = array("Object ID" => $destid, "Object URL" => $rd_dest, "Object Type" => $destobjtype, "Match Level" => $matchlevel, "Match" => $match, "Source ID" => $sourceid, "Source URL" => $rd_source, "Source Type" => $sourceobjtype, "Line Number" => '', "status" => '');
                            $arrayThirdPartyChain[] = $arr;
                            $redirfound = true;
                        }
                    }
                } // end redirects list

                //if ($redirfound == false)
                {
//echo ("<br/>checking third party id: ".$objid . " " . $objurl . " against " . count($arrayPageObjects) . " other files<br/>");
                    foreach ($arrayPageObjects as $lu_key => $lu_valuearray)
                    {
                        $objmatchlevel = 99;
                        $lu_objid = $lu_valuearray["id"];
                        // if ($lu_objid == $objid)
                        //     continue;
                        $lu_objurl = $lu_valuearray["Object source"];
                        $lu_objfile = $lu_valuearray["Object file"];
                        $lu_domref = $lu_valuearray["Domain ref"];
                        $lu_objtype = $lu_valuearray["Object type"];
                        $lu_statuscode = $lu_valuearray["HTTP status"];
// only look in objects that are JavaScript , CSS, HTML or data
                        if (($lu_objtype != 'JavaScript' and $lu_objtype != 'StyleSheet' and $lu_objtype != 'HTML' and $lu_objtype != 'Data') or $lu_statuscode != "200") //and $lu_objtype != 'CSS'
                            continue;
//echo("id: ".$objid.": looking in object ". $lu_objid .  ": file: " . $lu_objfile ."<br/>");
// retrieve the lookup object and search
// strip subdomain from domain and try again
                        $subdomain = $domain;
                        $dotpos = strpos($domain, ".");
                        $domainonly = substr($domain, $dotpos + 1);
                        $qpos = strpos($urlwithoutscheme, "?");
                        if ($qpos != false)
                        {
                            $urlwithoutquerystring = substr($urlwithoutscheme, 0, $qpos);
                        }
                        else
                            $urlwithoutquerystring = '';
                        $filepath = parse_url($objurl, PHP_URL_PATH);
                        if (substr($filepath, 0, 1) == "/")
                            $filepath = substr($filepath, 1); // strip leading dir slash
                        $path_parts = pathinfo($urlwithoutscheme);
                        $file = $path_parts['basename'];
                        $qpos = strpos($file, "?");
                        if ($qpos != false)
                            $file = substr($file, 0, $qpos);
                        $path = $path_parts['dirname'];
                        $path = str_replace($domain, '', $path);
                        if (substr($path, 0, 1) == "/")
                            $path = substr($path, 1); // strip leading dir slash
                        $qpos = strpos($urlwithoutscheme, "?");
                        if ($qpos != false)
                        {
                            $qs = substr($urlwithoutscheme, $qpos + 1);
// get first parm name
                            $epos = strpos($qs, "=");
                            if ($epos != false)
                                $qsname = substr($qs, 0, $epos);
                            else
                            {
                                $qsname = '';
                                $epos -= 1;
                            }
// get first QS param value
                            $ppos = strpos($qs, "&");
                            if ($ppos != false)
                            {
                                $qsvalue = substr($qs, $epos + 1, $ppos);
                            }
                            else
                                $qsvalue = substr($qs, $epos + 1);
                        }
                        else
                        {
                            $qsname = '';
                            $qsvalue = '';
                        }
//echo("<br>search parms: $objid, local file: $lu_objfile, url: $objurl, url_noqs: $urlwithoutquerystring,<br>filepath: $filepath,path: $path,file: $file,<br>qsname: $qsname, qsparm: $qsvalue,<br>subdomain: $subdomain, domain: $domainonly"."<br>");
                     

                        if($objid != $lu_objid and $domain != $host_domain) // don't check itself or if it is the host domain
                        {
                        $searchresults = searchFileForText($objid, $lu_objfile, $objurl, $urlwithoutquerystring, $filepath, $path, $file, $qsname, $qsvalue, $subdomain, $domainonly,$lu_objid);
                            if (intval($searchresults['Match Level']) < 99)
                            {
    // echo("Third Party Chain Search. ". $objid. " " . $objurl . "<pre>");
    // print_r($searchresults);
    // echo("</pre>");

    
                                $matchlevel = $searchresults['Match Level'];
                                // need to compare 3plookup, if same company & product, then increase the matchlevel score to reduce its impact
                                if(lookup3PDomain($domain) == lookup3PDomain($host_domain))
                                    $matchlevel = $matchlevel + 17;

                                $match = $searchresults['Match Description'];
                                $linenumber = $searchresults['Line Number'];
    // add third party match details
                                $arr = array("Object ID" => $objid, "Object URL" => $objurl, "Object Type" => $objtype, "Match Level" => $matchlevel, "Match" => $match, "Source ID" => $lu_objid, "Source URL" => $lu_objurl, "Source Type" => $lu_objtype, "Line Number" => $linenumber, "status" => '');
                                $arrayThirdPartyChain[] = $arr;
    //mark any other matches for this object that are of a lower level of match for deletion
                                foreach ($arrayThirdPartyChain as $o_key => $o_valuearray)
                                {
                                    $o_objid = $o_valuearray["Object ID"];
                                    $o_matchlvl = $o_valuearray["Match Level"];
                                    $o_srcobjid = $o_valuearray["Source ID"];
                                    if ($objid == $o_objid)
                                    {
    //echo($o_objid  . " at match level: " . $matchlevel . " checking against " . $o_objid . " at " . $o_matchlvl . " for " . $o_srcobjid . "<br/>");
                                        if ($matchlevel < $o_matchlvl)
                                        {
    //echo(" result = delete<br/>");
    // mark entry for deletion
                                            $arrayThirdPartyChain[$o_key]["status"] = "Delete";
                                        }
                                    }
                                }
                                if ($matchlevel < 9)
                                {
    // continue with the next object if was not simply the domain
                                    //break;
                                }
    //echo ("line no.: ". $lineno. "<xml>");
    //echo( htmlentities ($buffer));
    //echo ("</xml><br/>");
    // update parent object
                            } // end if a match was found
                            else
                            {

                            }
                        } // en search object if not itself
                    } // end for each page object being searched
                } // end file check if no redirections

                // add entry for "no match"
                if($matchlevel == 99)
                {
                    // no match found
    // echo("Third Party Chain Search - no result. ". $objid. " " . $objurl . "<pre>");
    // print_r($searchresults);
    // echo("</pre>");
                    $arr = array("Object ID" => $objid, "Object URL" => $objurl, "Object Type" => $objtype, "Match Level" => $matchlevel, "Match" => "No Match", "Source ID" => '', "Source URL" => '', "Source Type" => '', "Line Number" => '', "status" => '');
                    $arrayThirdPartyChain[] = $arr;
                }

            } // if 3P
        } // end for each page object



//echo("Third Party Chain before deletions<pre>");
//print_r($arrayThirdPartyChain);
//echo("</pre>");
// now delete any records marked for deletion
        foreach ($arrayThirdPartyChain as $o_key => $o_valuearray)
        {
            $o_status = $o_valuearray["status"];
            if ($o_status == "Delete")
            {
//echo("deleting array key: ". $o_key  . "<br/>");
                unset($arrayThirdPartyChain[$o_key]);
            }
        }
        // reindex array after deletions
        $arrayThirdPartyChain = array_values($arrayThirdPartyChain);

        // sort  by match level
        usort($arrayThirdPartyChain, 'sortTPChain'); // call custom function
//  echo("Third Party Chain after deletions<pre>");
//  print_r($arrayThirdPartyChain);
//  echo("</pre>");
    }

    function sortTPChain($a, $b)
    {
        $rdiff = $a['Match Level'] - $b['Match Level']; // sort in ascending match order
        if ($rdiff) return $rdiff;
        return $a['Source ID'] - $b['Source ID']; // sort in source id order as 2nd level
    }


    function searchFileForText($id, $fn, $fullurl, $urlonly, $filepath, $path, $file, $qsname, $qsvalue, $subdomain, $domain,$lu_objid)
    {
        global $arrayPageObjects;
        $resFullURL = false;
        $resPartialURL = false;
        $resFilepath = false;
        $resPath = false;
        $resFile = false;
        $resQSName = false;
        $resQSValue = false;
        $resSubdomain = false;
        $resDomain = false;
        $matchdesc = '';
        $matchlevel = 99;
        $handle = fopen($fn, 'r');
        $resLineNo = - 1;
// check every line until a match is found or end of file
        $lineno = 0;
        while (($buffer = fgets($handle)) != false)
        {
            $lineno += 1;
            if (strpos($buffer, $fullurl) != false)
            {
                $resLineNo = $lineno;
                $resFullURL = true;
                break; // out of loop
            }
            else
            {
// only check for partial URL if there is a query string
                if ($qsvalue != '')
                { // has querystring
                    if ($resFullURL == false and strpos($buffer, $urlonly) != false)
                    {
                        $resLineNo = $lineno;
                        $resPartialURL = true;
                    }
                }
            } // end not full URL
// check query string parts
            if ($qsvalue != '')
            { // has querystring
                if (strpos($buffer, $qsvalue) != false)
                {
                    $resLineNo = $lineno;
                    $resQSValue = true;
//echo("partial match on qs value<br>");
                }
            }
            if ($qsname != '' and strlen($qsname) >= 2)
            { // has querystring
                if (strpos($buffer, $qsname) != false)
                {
                    $resLineNo = $lineno;
                    $resQSName = true;
//echo("partial match on qs name<br>");
                }
            }
            if ($filepath != '')
            {
                if (strpos($buffer, $filepath) != false)
                {
                    $resLineNo = $lineno;
                    $resFilepath = true;
//echo("partial match on filepath<br>");
                }
            }
            else
            {
// check file and path separately
                if ($file != '')
                {
                    if (strpos($buffer, $file) != false)
                    {
                        $resLineNo = $lineno;
                        $resFile = true;
//echo("partial match on file<br>");
                    }
                }
                if ($path != '')
                {
                    if (strpos($buffer, $path) != false)
                    {
                        $resLineNo = $lineno;
                        $resPath = true;
//echo("partial match on path<br>");
                    }
                }
            }
// check domains
            if ($subdomain != '')
            {
                if (strpos($buffer, $subdomain) != false)
                {
                    $resLineNo = $lineno;
                    $resSubdomain = true;
//echo("partial match on subdomain<br>");
                }
            }
   //         else
            if ($domain != '' and $resSubdomain == false)
            {
                if (strpos($buffer, $domain) != false)
                {
                    $resLineNo = $lineno;
                    $resDomain = true;
//echo("partial match on domain . " . $domain . " in " . $fn . "<br>");
                }
            }
        } // end while
        fclose($handle);
// accumulate the results based upon the various matches found
        if ($resFullURL == true)
        {
            $matchdesc = "Full URL";
            $matchlevel = 1;
        }
        else
        {
            if ($resPartialURL == true)
            {
                $matchdesc = "Partial URL";
                $matchlevel = 5;
                if ($resQSName == true)
                {
                    $matchlevel = $matchlevel - 2;
                    $matchdesc = $matchdesc . " + querystring name";
                }
                if ($resQSValue == true)
                {
                    $matchlevel = $matchlevel - 1;
                    $matchdesc = $matchdesc . " + querystring value";
                }
            }
            else
            {
// not full and not partial, see if subdomain or domain were found, then see if any extra file or path were found
                if ($resSubdomain == true or $resDomain == true)
                {
// only continue if at least subdomain or domain was found
                    if ($resSubdomain == true)
                    {
                        $matchdesc = "Subdomain";
                        $matchlevel = 9;
                    }
                    else
                    {
                        $matchdesc = "Domain";
                        $matchlevel = 11;
                    }
                    if ($resFilepath == true)
                    {
                        $matchlevel = $matchlevel - 2;
                        $matchdesc = $matchdesc . " + filepath";
                    }
                    else
                    {
                        if ($resPath == true)
                        {
                            $matchlevel = $matchlevel - 1;
                            $matchdesc = $matchdesc . " + path";
                        }
                        if ($resFile == true)
                        {
                            $matchlevel = $matchlevel - 1;
                            $matchdesc = $matchdesc . " + file";
                        }
                    }
                    if ($resQSName == true)
                    {
                        $matchlevel = $matchlevel - 1;
                        $matchdesc = $matchdesc . " + querystring name";
                    }
                    if ($resQSValue == true)
                    {
                        $matchlevel = $matchlevel - 1;
                        $matchdesc = $matchdesc . " + querystring value";
                    }
                } // end if subdomain or domain were found in file
            } // end if not a partial match
        } // end if not a full match


        // set override for anything referenced by the root object
        $objid_of_first200 = 0; // assume no redirection for now
        // find first 200 code HTML file, get object id
        foreach ($arrayPageObjects as $key => $valuearray)
        {
           // echo "key " . $key;
            //var_dump($valuearray);
            $value = $valuearray["Object source"];
            $local = $valuearray["Object file"];
            $httpstatus = $valuearray["HTTP status"];
            if($httpstatus == 200)
            {
               // echo "200 object id found: " . $key."<br/>";
                //var_dump($valuearray);
                $objid_of_first200 = $key;
                break;
            }
        }
        if($lu_objid == $objid_of_first200 and $matchlevel < 35) 
        {
            $matchlevel = 2;
        }

        if($matchlevel < 99)
        {
//echo("Match " . $matchlevel . "; against id: ".$lu_objid.": in file: " . $fn ."<br/>");
        }
        else
        {
//echo("No Match " . $matchlevel . "; against id: ".$lu_objid.": in file: " . $f ."<br/>");
        }
        $arr = array("Object ID" => $id, "Match Level" => strval($matchlevel), "Match Description" => $matchdesc, "Line Number" => strval($resLineNo));
        return $arr;
    }


    function transferPJScookies($json)
    {
        global $cookie_jar;
//echo("cookies json<pre>");
//print_r($json);
//echo("</pre>");
//unlink($cookie_jar);
// create new file
//file_put_contents($cookie_jar."x", '# Netscape HTTP Cookie File'.PHP_EOL, FILE_APPEND ,null);
//file_put_contents($cookie_jar."x", '# http://curl.haxx.se/docs/http-cookies.html'.PHP_EOL, FILE_APPEND ,null);
//file_put_contents($cookie_jar."x", '# This file was generated by the Webpage Toaster! Edit at your own risk.'.PHP_EOL.PHP_EOL, FILE_APPEND ,null);
        $expiry = '';
        $djson = json_decode($json);
        foreach ($djson as $key => $values)
        {
//echo $key.": ";
            if (gettype($values) == "object")
            {
                foreach ($values as $key => $value)
                {
//          echo $key . " ". $value."\t";
                    switch ($key)
                    {
                        case "domain" :
                            $domain = $value;
                            break;
                        case "name" :
                            $name = $value;
                            break;
                        case "value" :
                            $val = $value;
                            break;
                        case "httponly" :
                            if ($value == TRUE)
                                $httponly = '#HttpOnly_';
                            else
                                $httponly = '';
                            break;
                        case "secure" :
                            if ($value == FALSE)
                                $secure = 'FALSE';
                            else
                                $secure = 'TRUE';
                            break;
                        case "path" :
                            $path = $value;
                            break;
                        case "expiry" :
                            $expiry = $value;
                            break;
                    }
                }
            } // end if object
            $cookietxt = $httponly . $domain . "\t" . "FALSE\t" . $path . "\t" . $secure . "\t" . $expiry . "\t" . $name . "\t" . $val . PHP_EOL;
//echo($cookietxt. "<br/>");
//file_put_contents($cookie_jar."x", $cookietxt, FILE_APPEND | LOCK_EX,null);
//file_put_contents($cookie_jar, $cookietxt, FILE_APPEND);
        }
    }


    function readcookiefile($infile)
    {
        // read the file if it exists
        if(file_exists($infile))
        {
            $lines = file($infile);
    // var to hold output
            $trows = '<thead><tr><td>Domain</td><td>Flag</td><td>Path</td><td>Secure</td><td>Expiration</td><td>Name</td><td>Value</td></tr></thead>';
    // iterate over lines
            foreach ($lines as $line)
            {
    // we only care for valid cookie def lines
                if ($line[0] != '#' && $line[1] != 'H' && substr_count($line, "\t") == 6)
                {
    // get tokens in an array
                    $tokens = explode("\t", $line);
    // trim the tokens
                    $tokens = array_map('trim', $tokens);
    // let's convert the expiration to something readable
                    $tokens[4] = date('Y-m-d h:i:s', $tokens[4]);
    // escape
                    $tokens[6] = htmlspecialchars(addslashes($tokens[6]));
    // we can do different things with the tokens, here we build a table row
                    $trows .= '<tr><td>' . implode('</td><td>', $tokens) . '</td></tr>';
    // another option, make arrays to do things with later,
    // we'd have to define the arrays beforehand to use this
    // $domains[] = $tokens[0];
    // flags[] = $tokens[1];
    // and so on, and so forth
                }
            }
    // complete table and send output
    // not very useful as it is almost like the original data, but then ...
            return ('<table class=\"dataTable table-striped\" border=\"0\">' . '<tbody>' . $trows . '</tbody>' . '</table>');
        }
        else
            return ('<table></table>');
}




    function transferPJSpostdata($json)
    {
// echo("postdata json<pre>");
// print_r($json);
// echo("</pre>");

        $djson = json_decode($json);
        foreach ($djson as $key => $values)
        {
//echo $key.": ";

        }
    }


    function readpostparmsdata($infile)
    {
// read the file
        $lines = file($infile);
// var to hold output
        $trows = '<thead><tr><td>Domain</td><td>Flag</td><td>Path</td><td>Secure</td><td>Expiration</td><td>Name</td><td>Value</td></tr></thead>';
// iterate over lines
        foreach ($lines as $line)
        {
// we only care for valid cookie def lines
            if ($line[0] != '#' && $line[1] != 'H' && substr_count($line, "\t") == 6)
            {
// get tokens in an array
                $tokens = explode("\t", $line);
// trim the tokens
                $tokens = array_map('trim', $tokens);
// let's convert the expiration to something readable
                $tokens[4] = date('Y-m-d h:i:s', $tokens[4]);
// escape
                $tokens[6] = htmlspecialchars(addslashes($tokens[6]));
// we can do different things with the tokens, here we build a table row
                $trows .= '<tr><td>' . implode('</td><td>', $tokens) . '</td></tr>';
// another option, make arrays to do things with later,
// we'd have to define the arrays beforehand to use this
// $domains[] = $tokens[0];
// flags[] = $tokens[1];
// and so on, and so forth
            }
        }
// complete table and send output
// not very useful as it is almost like the original data, but then ...
        return ('<table class=\"dataTable table-striped\" border=\"0\">' . '<tbody>' . $trows . '</tbody>' . '</table>');
    }



    function setRedirTargets()
    {
        // work through redirect list and update the parents of redirects where possible
        debug("SET REDIR TARGETS","");
        global $arrayOtherRedirs, $arrayPageObjects;
        if (sizeof($arrayOtherRedirs) > 0)
        {
//echo("redirect list <pre>");
//print_r($arrayOtherRedirs);
//echo("</pre>end of redirect list");
            foreach ($arrayOtherRedirs as $rd_key => $rd_value)
            {
                $rd_source = $rd_value["From"];
                $rd_dest = $rd_value["To"];
                $match = $rd_value["Method"];
                $matchlevel = 1;
                list($destid, $destobjtype) = lookupPageObjectValue($rd_dest, "Object type");
                list($sourceid, $sourceobjtype) = lookupPageObjectValue($rd_source, "Object type");
                $lfn = $arrayPageObjects[$sourceid]['Object file'];
                
                if(is_numeric($destid))
                    $arrayPageObjects[$destid]['Object file'] = $lfn;
                else
                {
                    debug ("Object update prevented", $destid);
                }
            }
        } // if redirs exist
    }


# www.lsauer.com, 2012 lo sauer
# desc: kill a process on Linux, MacOS, Windows without a process-control library
#      in the php setup or environment
$kill = function($pid){ return stripos(php_uname('s'), 'win')>-1 
    ? exec("taskkill /F /PID $pid") : exec("kill -9 $pid");
};
?>

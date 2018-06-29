<?php
    $OS = "Windows";
include 'ps_functions.php';
    $curlheader = '';
    $url =  'https://nghttp2.org/'; //'https://http2.cloudflare.com/';
    $sfn = "c:\\temp\\curltest.txt";
    $cookie_jar = tempnam("c:\\temp\\","cky");
    $boolNewCookieSessionSet = false;
    $ua = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36";





    $curlheader = array();

    $fp = fopen($sfn, "w");

    $method = 'GET';
    $auth = '';
    $charset= 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
    $conn = 'Connection: Keep-Alive';
    $ka = 'Keep-Alive: 300';
    $enc = 'Accept-Encoding:gzip, deflate, br';
    $boolakamaiDebug = false;
    $akamaiDebug = 'Pragma: akamai-x-cache-on, akamai-x-cache-remote-on, akamai-x-check-cacheable, akamai-x-get-cache-key, akamai-x-get-true-cache-key, akamai-x-get-extracted-values, akamai-x-get-ssl-client-session-id, akamai-x-serial-no, akamai-x-get-request-id, akamai-x-feo-trace';
    if($boolakamaiDebug == false)
        $rqheaders = Array($charset,$conn,$ka,$enc);
    else
        $rqheaders = Array($charset,$conn,$ka,$enc,$akamaiDebug);


    // set some cURL options
    $ch = curl_init();
    if (!defined('CURL_HTTP_VERSION_2_0')) {
        define('CURL_HTTP_VERSION_2_0', 3);
    }
    curl_setopt($ch, CURLOPT_URL,$url);

    if($boolNewCookieSessionSet == false)
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
    curl_setopt($ch, CURLOPT_USERAGENT,$ua);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https

    // HTTP/2
    curl_setopt($ch,CURLOPT_HTTP_VERSION, 3);

    //curl_setopt($ch, CURLOPT_COOKIE, $cookies);

    //if(strpos($url,"FileMerge") != false);
    //curl_setopt($ch, CURLOPT_COOKIE, "ASP.NET_SessionId=czd33tbyhkmcixrdvgoz0ljr; SiteCurrentCulture_1=en-GB; _ga=GA1.3.520702782.1439707644; flxpxlPv_652307=2|0; flxpxlPv_652308=2|0; J250KbXbTDTvWqHKGpaMTOHA6dXEq6NOLz2WSnbLsd8%3D=; QU%2Fxy0p8BQVtZZndVWZVo%2FmsFC9IARDu5c%2BoAdnc%2FGE%3D=; flxpxlTs_652309=12029|0; visid_incap_318169=Iiov4Gx4Q/uolCBYd0U7Pfwx0FUAAAAAQUIPAAAAAACudp6mO0kW9TN0uvIYpJR9; incap_ses_47_318169=x8MWDxZTuQlzTQqKZfqmAJ980FUAAAAAYMl4PUuAGy0EtYlv+rdxOA==; ___utmvmaButyyw=LnzRZUyJGyy; ___utmvbaButyyw=UZN XhSOjalg: zt");

    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie_jar);
    curl_setopt($ch, CURLOPT_FILE, $fp);

	//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
    //if($username != '' and $password != '')
    //{
    //    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    //}
    //curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $rqheaders); // add additional request headers

    $result = curl_exec($ch);

    $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );

echo "REQUEST HEADER<br/>".$headerSent."<br/>";


//echo "RESPONSE<pre/>";
//print_r($result);
//echo "<pre/>";

echo "RESPONSE<pre/>";
print_r($curlheader);
echo "<pre/>";


    // Check if any error occurred
    if (!$ch) {
    	die("Couldn't initialize a CURL handle");
    }

    if (empty($result) or !$result) {
        // some kind of an error happened
        //echo("curl error getting $url <br/>");
        $curl_info =  false;
        $errno = curl_errno($ch);
        $error_message = curl_strerror($errno);
echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");

        //die(curl_error($ch));
    }
    else
        if($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
echo ("CURL error $url ({$errno}): "." {$error_message}"."<br/>");

        }
        else {
            $curl_info = curl_getinfo($ch);
        }


        $dlfilesize = curl_getinfo($ch,CURLINFO_SIZE_DOWNLOAD);
        $dlstatuscode= curl_getinfo($ch,CURLINFO_HTTP_CODE);

    // close file connection
    fclose($fp);
    curl_close($ch); // close cURL handler

    ?>
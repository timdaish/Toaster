<?php
include "api-config.php";
function convertAbsoluteURLtoLocalFileName($sourcefile)
{
	global $filepath_domainsavedir, $filepath_domainsaverootdir,$host_domain,$debug;
	$localfile = '';
    //if(strpos($sourcefile,"FileMerge") > 0 or strpos($sourcefile,"WebResource") > 0)
	//    $funcdebug = true;
    //else
    $funcdebug = false;
    if($debug == true)
        $funcdebug = true;
    $originalsourcefile = trim($sourcefile);
	if(trim($sourcefile) == '' or trim($sourcefile) == 'javascript:false')
		return false;
	if($funcdebug == true)
    {
//error_log (__FUNCTION__ . " ".$sourcefile);
//echo (__FUNCTION__ . " ".$sourcefile. "<br/>");
	//echo "<br/>Generating Local FileName for: ".$sourcefile."<br/>";
    }
    // remove crlf chars
    $sourcefile = str_replace(chr(10),'',$sourcefile);
    $sourcefile = str_replace(chr(13),'',$sourcefile);
	// decode html entities - Phantom will have added these for source matching
	$sourcefile = html_entity_decode($sourcefile);
	$sourcefile = str_replace('%2F','',$sourcefile); // remove \ char before url decoding
	// url decode as well to convert %5B, %5D chars etc.
	$sourcefile = urldecode($sourcefile);
	// delete chars that prevent local saving
	$sourcefile = str_replace('=','',$sourcefile);
//	if($funcdebug == true)
//		echo "<br/>decoded Local FileName for: ".$sourcefile."<br/>";
	// remove http:// from url
	$sourcefile = str_replace('http://','',$sourcefile);
	$sourcefile = str_replace('https://','',$sourcefile);
	// strip the .ext if provided as an extension
	$path_parts = pathinfo($sourcefile);
    if(isset($path_parts['extension']))
    {
    	if($path_parts['extension'] ==  'ext') // strip .ext extension
    	{
    		$sourcefile = str_replace('.ext','',$sourcefile);
    	}
	}
	// replace &amp;
	$decodedsourcefile = html_entity_decode($sourcefile);
	$nonampdecodedsourcefile = str_replace('amp','',$decodedsourcefile);
    $sourcefile = htmlentities($nonampdecodedsourcefile);
    if($funcdebug == true)
	    echo "<br/>init FileName w/o &	: ".$sourcefile."<br/>";
	if($funcdebug == true)
	{
		echo("filepath_domainsavedir: ".$filepath_domainsavedir."<br/>");
		echo("filepath_domainsaverootdir: ".$filepath_domainsaverootdir."<br/>");
		echo("host_domain: ".$host_domain."<br/>");
	}
    if($sourcefile == $host_domain)
    {
        // only a url provided
//echo("host_domain = sourcefile, adding index.htm<br/>");
        $sourcefile = $sourcefile . "\index.htm";
    }
    // check host_domain for 2part tld
    $twopartdomainpart2 = '';
    $twopartdomainpos = strpos($host_domain,'.co.uk');
    if($twopartdomainpos !== false)
        $twopartdomainpart2 = '.uk';
    $twopartdomainpos = strpos($host_domain,'.com.au');
    if($twopartdomainpos !== false)
        $twopartdomainpart2 = '.au';
	$host = parse_url($originalsourcefile , PHP_URL_HOST);
	$path = parse_url($originalsourcefile , PHP_URL_PATH);
	if($funcdebug == true)
	{
		echo("host: ".$host."<br/>");
		echo("path: ".$path."<br/>");
	}
	$sourceurlparts = get_SourceURL($originalsourcefile );
	$path_parts = pathinfo($path);
	if($funcdebug == true)
	{
		echo("sourceparts array:<pre>");
		print_r($sourceurlparts);
		echo("</pre>");
        error_log("sourceparts array " . print_R($sourceurlparts,true));
	}
    if (count($sourceurlparts) == 0)
        return(false);
    if($twopartdomainpart2 != '')
    {
        // update host and domain if two-part domain detected
        //$sourceurlparts['host'] = $sourceurlparts['host'].$twopartdomainpart2;
        //$sourceurlparts['domain'] = $sourceurlparts['domain'].$twopartdomainpart2;
        //error_log(__FUNCTION__." 2part domain host : ". $sourceurlparts['host']);
        //error_log(__FUNCTION__." 2part domain TLD: ". $sourceurlparts['domain']);
    }
	if($funcdebug == true)
	{
		echo("twopart tld sourceparts array:<pre>");
		print_r($sourceurlparts);
		echo("</pre>");
	}
    if($funcdebug == true)
	{
		echo("path_parts array:<pre>");
		print_r($path_parts);
		echo("</pre>");
	}
    //check for query string and sanitise it
    $qs = $sourceurlparts['querystring'];
    if($qs != '')
    {
        $qs = trim($qs);
//echo ("querystring found: ". $qs ."<br>");
        $qs = filter_var($qs,FILTER_SANITIZE_EMAIL);
//echo ("filtered querystring: ". $qs ."<br>");
    	$thisquerystring = $qs;
		if(strlen($qs) > 100)
		{
			$qs = substr($qs,0,100);
//echo ("truncating querystring: ". $qs ."<br>");
		}
    }
//$thisquerystring = $qs;
	//
	$thisfilename = trim($path_parts['filename']);
	// remove invalid characters from filename, startung with colon :
	$thisfilename = str_replace(":","",$thisfilename);
	$thisfilename = str_replace("%3A","",$thisfilename);

//echo ($thisfilename . "<br/>");
    
	if($qs == '')
        $thisfilename = sanitize_file_name($thisfilename,false,false);
    else
        $thisfilename = sanitize_file_name($thisfilename."_".$qs,false,false);
	if($thisfilename == '')
		$thisfilename = 'index.htm';
	$thisdomain = trim($sourceurlparts['host']);
	$thisdirs =  trim($sourceurlparts['dirs']); //trim($path_parts['dirname']);
	if($thisdirs == '.')
		$thisdirs = '';
    if($thisdirs == '' and isset($path_parts['dirname']))
    {
        //get dirs by removing new host from path
        $thisdirs = str_replace($sourceurlparts['host'].'/','',$path_parts['dirname']);
//echo("revised path dirs: ".$thisdirs."<br/>");
    }
    if($thisdirs == $host_domain)
    {
        //get dirs by removing new host from path
        $thisdirs = '';
//echo("revised path dirs: ".$thisdirs."<br/>");
    }
    // remove spaces from this dirs
    $thisdirs = str_replace(" ","",$thisdirs);
    $thisdirs = str_replace("%20","",$thisdirs);
	//
    // check for bookmark
	$hpos = strpos($sourcefile , "#");
	debug("cnv checking source file for hash bookmark string",$thisfilename ." - ".$hpos);
	//if($hpos>0)
	//{
    //echo($sourcefile  .' file has a # bookmark at pos '. $hpos.'<br/>');
	//}
	$fnlength = strlen($thisfilename);
	if($fnlength > 100)
	{
		//echo("file: ". $thisfilename."<br/>");
		$thisfilename = substr($thisfilename,0,100);
	}
	$extra = '';
	if (isset($path_parts['extension']))
	{
		//remove invalid characters from filename, startung with colon :
     	//gen-delims  = ":" / "/" / "?" / "#" / "[" / "]" / "@"
		//sub-delims  = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
		$ext = $path_parts['extension'];
		$colonpos = strpos($path_parts['extension'],":");
		if($colonpos != false)
		{
		    $extra = substr($path_parts['extension'],$colonpos + 1);
			$ext = substr($path_parts['extension'],0,$colonpos);

		}
		$ext =".".$ext;
		debug("parts extension",$ext);
        // replace dynamic page extentsions
		if ($ext == '.php' or $ext == '.ashx' or $ext == '.aspx' or $ext == '.asmx' or $ext == '.asvc' or $ext == '.axd'  or $ext == '.jsp')
			{
                if(substr($thisfilename,-4) == $ext)
            	    $thisfilename = str_replace($ext,".ext",$thisfilename);
                else
                    $thisfilename = $thisfilename . ".ext";
			}
		// truncate any extensions that are too long
            $thisfilename = $thisfilename.$extra.$ext;
	}
	else
	{
		//echo("adding missing extension:".$thisfilename."<br/>");
		$thisfilename = $thisfilename.'.ext';
	}
	//check if this file is on the primary domain
	if($thisdomain == $host_domain)
	{
		$localfile = joinFilePaths($filepath_domainsaverootdir,$thisdirs,$thisfilename);
		if($funcdebug == true)
		{
			echo "final path making: on domain<br/>";
			echo "filepath_domainsaverootdir: ".$filepath_domainsaverootdir."<br/>";
			echo "thisdirs: ".$thisdirs."<br/>";
			echo "thisfilename: ".$thisfilename."<br/>";
		}
    debug ("thisfilename: ".$thisfilename."<br/>");
	}
	else
	{
		// not on domain
		// add the domain immediately below the host domain
		if($funcdebug == true)
		{
			echo "final path making: not on domain<br/>";
			echo "filepath_domainsaverootdir: ".$filepath_domainsaverootdir."<br/>";
			echo "thisdomain: ".$thisdomain."<br/>";
			echo "thisdirs: ".$thisdirs."<br/>";
			echo "thisfilename: ".$thisfilename."<br/>";
		}
        debug ("thisfilename: ".$thisfilename."<br/>");
		$localfile = joinFilePaths($filepath_domainsaverootdir,$thisdomain ,$thisdirs,$thisfilename); //removed after rootdir
	}
	if($funcdebug == true)
    {
		echo "new local filename: ".$localfile."<br/>";
		error_log( "new local filename: ".$localfile);
	}
	return $localfile;
}
function updateDomainBytes($srcfile, $bytes,$timing_offset) // $arrayOfObjects[$key]['Domain'],$contentlength);
{
    // update domain table with bytes from downloaded object
    global $arrayDomains,$array3PDomainStats;
        $u = parseUrl($srcfile);
        @ $h = $u["host"];
        @ $subd = $u["subdomain"];
        @ $d = $u["domain"];
//echo("updateDomainBytes: " . $srcfile. " = " . $d . " " . $bytes . "<br/>");
    list($hostdomain, $p) = getDomainHostFromURL($srcfile,false,"UpdateDomainLocationFromHeader");
//echo("updateDomainBytes: " . $hostdomain . " is key found?: " .$keyfound . "<br/>");
	$keyfound = lookupDomain($hostdomain);
//echo($hostdomain . " updateDomainBytes: key found: " .$keyfound);
    if($keyfound != -1)
    {
        //echo ("key found" . $keyfound. "<br/>");
        $cbytes = intval($arrayDomains[$keyfound]["TotBytes"]);
        $arrayDomains[$keyfound]["TotBytes"] = strval($cbytes + $bytes);
        $company = $arrayDomains[$keyfound]["Company"];
        $product = $arrayDomains[$keyfound]["Product"];

		$offset =  intval($arrayDomains[$keyfound]["Offset"]);
		if(intval($timing_offset) < $offset and $timing_offset != '')
			$arrayDomains[$keyfound]["Offset"] = $timing_offset;

    }
//    if($company == "")
//echo "empty company when updating bytes: " . $d . "<br/>";
    $keyfound = lookupDomain3P($d,$company,$product);
//echo("updateDomainBytes: 3p key found: " .$keyfound);
    if($keyfound != -1)
    {

        $cbytes = intval($array3PDomainStats[$keyfound]["TotBytes"]);
        $array3PDomainStats[$keyfound]["TotBytes"] = strval($cbytes + $bytes);

		$offset =  intval($arrayDomains[$keyfound]["Offset"]);
		if(intval($timing_offset) < $offset and $timing_offset != '')
			$arrayDomains[$keyfound]["Offset"] = $timing_offset;

//echo ("updateDomainBytes:key found" . $keyfound.  " " . $hostdomain  . "; curroffset: " . $arrayDomains[$keyfound]["Offset"] . " newoffset:" . $timing_offset . "<br/>");

    }
}
function getDomainHostFromURL($url,$boolAddDomain,$debuginfo)
{
	global $roothost,$basescheme;
	debug("getDomainHostFromURL called from ",$debuginfo);
	if (substr($url,0,2) == '//')
	{
		$url = $basescheme . ":" . $url;
	}
	//if (strtolower(substr($url,0,4)) != 'http')
	//{
	//	$url = "http://" . $url;
	//}
	debug("<br/>Func GetDomainHostFromURL",$url);
	$parse = parse_url($url);
	//print_r($parse);
	$thispagename = pathinfo($url,PATHINFO_BASENAME);
	$h = '';
	if(isset($parse['host']))
	{
		$h = $parse['host'];
		if($boolAddDomain == true)
			AddDomainToArray($h,$roothost);
	}
	$p = '';
	if(isset($parse['path']))
	{
		$p = $parse['path'];
		$qs = '';
		$qspos = strpos($thispagename,"?");
		if($qspos !== false)
		{			
			$thispagename = substr($thispagename,0,$qspos);
			$qs = substr($thispagename,$qspos);
		}
		$thispagenamelen = strlen($thispagename);
		debug("path",$p);
		$pathlen = strlen($p) - 1;  // remove leading slash char from count
		$hostpathlen = ($pathlen - $thispagenamelen - 1); // remove trailing slash from count
		debug("hostpathlen",$hostpathlen);
		if(substr($p,0) != $thispagename)
		{
			debug("basename",$thispagename);
			$p = substr($p,1,$hostpathlen);
			debug("domain host path",$p);
		}
		else
			debug("domain host has no path",$p);
	}	
	if(isset($parse['host']))
	{
		$h = $parse['host'];
		debug("found domain host",$h);
	}
	else
	{
		debug("domain host is path",$p);
		$h = $p;
		$p = "";
	}
	return array($h,$p);
}
function extract_domain($domain)
{
    if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
    {
        return $matches['domain'];
    } else {
        return $domain;
    }
}
function extract_subdomains($domain)
{
	if ($domain == '')
		return $domain ;
    $subdomains = $domain;
    $domain = extract_domain($subdomains);
	//echo("domain and subdomains: ".$domain."; ".$subdomains."<br/>");
    $subdomains = rtrim(strstr($subdomains, $domain, true), '.');
    return $subdomains;
}
function parseUrl($url) {
    $r  = "^(?:(?P<scheme>\w+)://)?";
    $r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
    $r .= "(?P<host>(?:(?P<subdomain>[\w\.]+)\.)?" . "(?P<domain>[a-zA-Z0-9_-]+\.(?P<extension>\w+)))";
    $r .= "(?::(?P<port>\d+))?";
    $r .= "(?P<path>[\w-/]*/(?P<file>\w+(?:\.[a-zA-Z0-9_-]+)?)?)?";
    $r .= "(?:\?(?P<query>[\w=&]+))?";
    $r .= "(?:#(?P<anchor>\w+))?";
    $r = "!$r!";                                                // Delimiters
    preg_match ( $r, $url, $out );
    return $out;
}
function url_to_absolute( $baseUrl, $relativeUrl )
{
	  global $basescheme;
  	// replace JS exceptions
  	if(strpos($relativeUrl,"' + new Date().getTime() + '") or strpos($relativeUrl,'" + new Date().getTime() + "'))
	{
//echo("js date found, replacing with real datetime</br>");
		$replacedate = date('c',  strtotime('now'));
		$relativeUrl = str_replace("' + new Date().getTime() + '",$replacedate,$relativeUrl);
	}
	// check for absolute already
	if(strToLower(substr($relativeUrl,0,7)) == 'http://' or strToLower(substr($relativeUrl,0,8)) == 'https://')
		return ($relativeUrl);
	// add scheme for url starting as double slash
//echo(__FILE__ . "/" . __FUNCTION__ . "/" . __LINE__ . ": first two chars = '" .substr($relativeUrl,0,2) . "'");
	if(substr($relativeUrl,0,2) == '//')
	{
		//$relativeUrl = parse_url($url, PHP_URL_SCHEME).":".$relativeUrl; // check if this should be https if base is on https
		$relativeUrl = $basescheme.":".$relativeUrl; // check if this should be https if base is on https
//	echo($basescheme . " basescheme added to " . $relativeUrl . "</br>");
		return ($relativeUrl);
	}
	//check for wrong slashes in relative URL
	if(strpos($relativeUrl,"\\") !== false)
	{
		//echo ("SLASHES Error in relative URL: ".$relativeUrl."<br/>");
		$relativeUrl = str_replace("\\","/",$relativeUrl);
		addErrors($relativeUrl,"Incorrect directory path (wrong slashes");
	}
	//remove querystring from parent baseUrl
	$req = strpos($baseUrl,"?");
	if($req != 0)
		$baseUrl = substr($baseUrl,0,$req);
	$req = strpos($relativeUrl,"?");
	if($req != 0)
	{
		//echo("url to abs: with querystring"."<br/>");
		$qs = substr($relativeUrl,$req);
		$relativeUrl = substr($relativeUrl,0,$req);
		//echo("url to abs: with querystring: " . $qs."<br/>");
	}
	else
		$qs = '';
	//echo("url to abs: rel url w/o qs: ".$relativeUrl."<br/>");
	$re = html_entity_decode($relativeUrl);
	debug("func url_to_absolute","based upon ". $baseUrl);
	debug("func url_to_absolute","converting URL: ".$re." based upon ". $baseUrl);
	//echo("func url_to_absolute: "."converting URL: $re based upon $baseUrl<br/>");
    $relativeUrl = str_replace("&#xA;","",$relativeUrl);
    $relativeUrl = trim($relativeUrl);
    $relativeUrl = str_replace(" ","%20",$relativeUrl);
  	$r = split_url(html_entity_decode($relativeUrl));
    // check If relative URL is valid
    if ( $r === FALSE )
	{
		//echo("func url_to_absolute: ". "converting URL: $relativeUrl based upon $baseUrl<br/>");
		//echo ("relative url issue: url is not valid (false)<br/>");
		addErrors($relativeUrl,'relative url invalid');
		//echo("0 abs url: ".join_url( $r )."<br/>");
        //return FALSE;
	}
    // If relative URL has a scheme, clean path and return.
    if ( !empty( $r['scheme'] ) )
    {
        if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
            $r['path'] = url_remove_dot_segments( $r['path'] );
		//echo("1 abs url: ".join_url( $r )."<br/>");
        return join_url( $r ).$qs;
    }
    // Make sure the base URL is absolute.
	$b = split_url(	trim( $baseUrl) ); 
//	echo "split url (" . $baseUrl . "): ";
//print_r($b);
    if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
	{
		$baseimp = implode($baseUrl);
//echo ("converting url: $baseUrl (".$baseimp.")base url is not absolute<br/>");
//echo ("scheme: ".$b['scheme']."<br/>");
//echo ("host: ".$b['host']."<br/>");
		addErrors($relativeUrl,'base url invalid when converting to absolute');
        //echo("2 abs url: ".join_url( $r )."<br/>");
		return FALSE;
	}
    // If relative URL has an authority, clean path and return.
    if ( isset( $r['host'] ) )
    {
        if ( !empty( $r['path'] ) )
            $r['path'] = url_remove_dot_segments( $r['path'] );
		debug("func url_to_absolute final: ",join_url($r));
		//echo("3 abs url: ".join_url( $r )."<br/>");
        return join_url( $r ).$qs;
    } 
    if ( isset( $r['port'] ))
        unset( $r['port'] );
    if ( isset( $r['user'] ))
        unset( $r['user'] );
    if ( isset( $r['pass'] ))
        unset( $r['pass'] );
    // Copy base authority.
    $r['host'] = $b['host'];
    if ( isset( $b['port'] ) and $b['port'] != '80')
	{ $r['port'] = $b['port'];
	//echo("abs port: ".$r['port']."<br/>");
	}
    if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
    if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];
	$r['scheme'] = $b['scheme'];
    // If relative URL has no path, use base path
    if ( empty( $r['path'] ) )
    {
		//echo "converting url - rel has no path: $relativeUrl <br/>";
        if ( !empty( $b['path'] ) )
            $r['path'] = $b['path'];
        if ( !isset( $r['query'] ) && isset( $b['query'] ) )
            $r['query'] = $b['query'];
		debug("func url_to_absolute final: ",join_url($r));
		//echo("4 abs url: ".join_url( $r )."<br/>");
        return join_url( $r ).$qs;
    }
    // If relative URL path doesn't start with /, merge with base path
    if ( $r['path'][0] != '/')
    {
		//echo "converting url - rel does not start with /: $relativeUrl <br/>";
        $base = @mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
        if ( $base === FALSE ) $base = '';
        $r['path'] = $base . '/' . $r['path'];
    }
	else
	{
		//echo "converting url - starts with a /: $relativeUrl <br/>";
	}
	//echo "converting url - at end: $relativeUrl <br/>";
    $r['path'] = url_remove_dot_segments( $r['path'] );
	debug("func url_to_absolute final: ",join_url($r));
	//echo("5 abs url: ".join_url( $r )."<br/>");
    return join_url( $r ).$qs;
}
function url_remove_dot_segments( $path )
{
    // multi-byte character explode
    $inSegs  = preg_split( '!/!u', $path );
    $outSegs = array( );
    foreach ( $inSegs as $seg )
    {
        if ( $seg == '' || $seg == '.')
            continue;
        if ( $seg == '..' )
            array_pop( $outSegs );
        else
            array_push( $outSegs, $seg );
    }
    $outPath = implode( '/', $outSegs );
    if ( $path[0] == '/' )
        $outPath = '/' . $outPath;
    // compare last multi-byte character against '/'
    if ( $outPath != '/' &&
        (mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
        $outPath .= '/';
    return $outPath;
}
function split_url( $url, $decode=FALSE )
{
	$parts = [];
    $xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=|';
    $xpchar        = $xunressub . ':@%';
    $xscheme       = '([a-zA-Z][a-zA-Z\d+-.]*)';
    $xuserinfo     = '((['  . $xunressub . '%]*)' .
                     '(:([' . $xunressub . ':%]*))?)';
    $xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';
    $xipv6         = '(\[([a-fA-F\d.:]+)\])';
    $xhost_name    = '([a-zA-Z\d-.%]+)';
    $xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
    $xport         = '(\d*)';
    $xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
                     '?(:' . $xport . ')?)';
    $xslash_seg    = '(/[' . $xpchar . ']*)';
    $xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
    $xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
    $xpath_abs     = '(/(' . $xpath_rel . ')?)';
    $xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
                     '|' . $xpath_rel . ')';
    $xqueryfrag    = '([' . $xpchar . '/?' . ']*)';
    $xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
                     '(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';
    // Split the URL into components.
    if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
        return FALSE;
    if ( !empty($m[2]) )        $parts['scheme']  = strtolower($m[2]);
    if ( !empty($m[7]) ) {
        if ( isset( $m[9] ) )   $parts['user']    = $m[9];
        else            $parts['user']    = '';
    }
    if ( !empty($m[10]) )       $parts['pass']    = $m[11];
    if ( !empty($m[13]) )       $h=$parts['host'] = $m[13];
    else if ( !empty($m[14]) )  $parts['host']    = $m[14];
    else if ( !empty($m[16]) )  $parts['host']    = $m[16];
    else if ( !empty( $m[5] ) ) $parts['host']    = '';
    if ( !empty($m[17]) )
	{       $parts['port']    = $m[18];
		//echo("split url port: " .$m[18]."<br/>");
	}
    if ( !empty($m[19]) )       $parts['path']    = $m[19];
    else if ( !empty($m[21]) )  $parts['path']    = $m[21];
    else if ( !empty($m[25]) )  $parts['path']    = $m[25];
    if ( !empty($m[27]) )       $parts['query']   = $m[28];
    if ( !empty($m[29]) )       $parts['fragment']= $m[30];
    if ( !$decode )
        return $parts;
    if ( !empty($parts['user']) )
        $parts['user']     = rawurldecode( $parts['user'] );
    if ( !empty($parts['pass']) )
        $parts['pass']     = rawurldecode( $parts['pass'] );
    if ( !empty($parts['path']) )
        $parts['path']     = rawurldecode( $parts['path'] );
    if ( isset($h) )
        $parts['host']     = rawurldecode( $parts['host'] );
    if ( !empty($parts['query']) )
        $parts['query']    = rawurldecode( $parts['query'] );
    if ( !empty($parts['fragment']) )
        $parts['fragment'] = rawurldecode( $parts['fragment'] );
    return $parts;
}
function join_url( $parts, $encode=FALSE )
{
    if ( $encode )
    {
        if ( isset( $parts['user'] ) )
            $parts['user']     = rawurlencode( $parts['user'] );
        if ( isset( $parts['pass'] ) )
            $parts['pass']     = rawurlencode( $parts['pass'] );
        if ( isset( $parts['host'] ) &&
            !preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) )
            $parts['host']     = rawurlencode( $parts['host'] );
        if ( !empty( $parts['path'] ) )
            $parts['path']     = preg_replace( '!%2F!ui', '/',
                rawurlencode( $parts['path'] ) );
        if ( isset( $parts['query'] ) )
            $parts['query']    = rawurlencode( $parts['query'] );
        if ( isset( $parts['fragment'] ) )
            $parts['fragment'] = rawurlencode( $parts['fragment'] );
    }
    $url = '';
    if ( !empty( $parts['scheme'] ) )
        $url .= $parts['scheme'] . ':';
    if ( isset( $parts['host'] ) )
    {
        $url .= '//';
        if ( isset( $parts['user'] ) )
        {
            $url .= $parts['user'];
            if ( isset( $parts['pass'] ) )
                $url .= ':' . $parts['pass'];
            $url .= '@';
        }
        if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) )
            $url .= '[' . $parts['host'] . ']'; // IPv6
        else
            $url .= $parts['host'];             // IPv4 or name
        if ( isset( $parts['port'] ) )
            $url .= ':' . $parts['port'];
        if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' )
            $url .= '/';
    }
    if ( !empty( $parts['path'] ) )
        $url .= $parts['path'];
    if ( isset( $parts['query'] ) )
        $url .= '?' . $parts['query'];
    if ( isset( $parts['fragment'] ) )
        $url .= '#' . $parts['fragment'];
    return $url;
}
function AddDomainToArray($url,$roothost)
{
	global $arrayDomains,$sourceurlparts,$getipgeo,$userlat,$userlong,$array3PDomainStats;
	if($url == '')
		return;
	list($hostdomain, $p) = getDomainHostFromURL($url,false,"AddDomainToArray");
	//echo("domain checking: ".$url."<br/>");
	//echo("retrieved host domain: ".$hostdomain."<br/>");
	//echo("retrieved host domain path: ".$p."<br/>");
	$ln = strlen($hostdomain);
	//echo("retrieved host domain path length: ".$ln."<br/>");
	$domref = 'Primary';
	if (trim($hostdomain) == '')
	{
		// set to base domain
		$hostdomain = $sourceurlparts["host"];
	}
	else
    {
        if (($roothost . "/" == $hostdomain) or ($roothost == $hostdomain . "/"))
        {
            $domref = 'Primary';
        }
        else
		    if($roothost !=$hostdomain and $roothost != '')
    		{
    			//echo("n domain counting: ".$url."<br/>");
    			//echo("n retrieved host domain: ".$hostdomain."<br/>");
    			//echo("n retrieved root domain: ".$roothost."<br/>");
    			//echo("n retrieved host domain path: ".$p."<br/>");
    			// determine what type of domaain this is
    			$domsrc = IsThisDomainaCDNofTheRootDomain($roothost,$hostdomain);
    			switch($domsrc)
    				{
    					case 'CDN':
                        case 'cdn':
    						debug("CDN External File", "'".$url."'");
    						$domref = 'CDN';
    						break;
    					case 'Shard':
                        case 'shard':
    						debug("Shard External File", "'".$url."'");
    						$domref = 'Shard';
    						break;
    					default:
    						debug("3rd party External File", "'".$url."'");
    						$domref = '3P';
    				}
    		}
    }
	$keyfound = lookupDomain($hostdomain);
	//$found = false;
	//foreach($arrayDomains as $key =>$value)
	//{
	//	if($hostdomain == $value['Domain Name'])
	//	{
	//		//echo( $key.": Got domain already ".$hostdomain."<br/>");
	//		debug( $key.": Got domain already ",$hostdomain);
	//		$found = true;
	//		$keyfound = $key;
	//		break;
	//	}
	//}
//echo("is domain found?: ".$keyfound ."<br/>");
	if ($keyfound < 0)
	{
//echo("new domain: ".$hostdomain."<br/>");
		$sitedesc = '';
		$domprovider = '';
		$category = '';
        $group = '';
        $product = '';
		if($domref == '3P' or $domref =="CDN" or $domref =="Shard" or $domref == "redirection")
		{
//echo 'New 3P Domain identified, attempting to look up domain meta description: '.$hostdomain.'<br/>';
			if($hostdomain != '')
			{
				list($sitedesc,$domprovider,$category,$product,$group) = get3PDescription($hostdomain);
//echo ($domref.' domain checked, provider: '.$domprovider.'<br/>');
			}
		}
		// geo location
		$loc = '';
		$edgeloc = '';
		$edgename = '';
		$edgeaddress = '';
		$lat = '';
		$long = '';
		$distance = '';
		$network = '';
		$method = '';
		$service = '';
		//echo("getipgeo ".$getipgeo."<br/>");
		if($getipgeo != 'none')
		{
			if(($domref != '3P' and $domref != 'CDN' and $domref != "redirection" and $getipgeo == 'domain') or $getipgeo == 'all')
			{
//echo("getting" . $getipgeo." new domain geo: ".$hostdomain."<br/>");
                $ip = lookupIPforDomain($hostdomain);
                list($loc, $city,$region,$country,$lat,$long) = lookupLocationforIP($ip);
//echo ('ip '.$ip.'<br/>');
//echo ('doing nslookup on domain IP '.$hostdomain.'<br/>');
                list($edgename,$edgeaddress) = nslookup($hostdomain);
//echo ('edgename '.$edgename.'<br/>');
//echo ('edgeaddress '.$edgeaddress.'<br/>');
                if($edgeaddress != '')
                {
                	// do reverse NS lookup with returned name
//echo ('doing reverse NS Lookup on edge IP '.$edgeaddress.'<br/>');
                	list($edgename2,$edgeaddress2) = nslookup($edgeaddress);
//echo ('reverse NS: edgename '.$edgename2.'<br/>');
//echo ('reverse NS: edgeaddress '.$edgeaddress2.'<br/>');
                	if($edgename2 != '')
                	{
                		$edgeloc = $loc;
                		$edgename = $edgename2;
                		$edgeaddress = $edgeaddress2;
                	}
//echo("IP geo:". $edgename. " ".$edgeaddress."; edgeloc ". $edgeloc."<br/>");
    				list($edgeloc3,$city3,$region3,$country3,$lat3,$long3,$network3,$method3,$service3) = checkdomainforNamedCDNLocation($edgename,$edgeaddress);
//echo(__FUNCTION__." ".__LINE__." IP geo lookup:". $edgename. " ".$edgeaddress."; edgeloc ". $edgeloc3. "; method: ". $method3. "<br/>");
                    if($edgeloc3 != '')
                    {
                        $edgeloc = $edgeloc3;
                        $city = $city3;
                        $region = $region3;
                        $country = $country3;
                        $lat = $lat3;
                        $long =$long3;
                        if($network3 != '')
                            $network = $network3;
                        if($method3 != '')
                            $method =$method3;
                        $service = $service3;
                    }
//echo("name geo: ".$edgename. " ".$edgeaddress."; edgeloc ". $edgeloc."<br/>");
                }
                // overrides for known CDNs not using a specific edgename
                if(strpos($hostdomain,".edgesuite.net" != false))
                {
                    $network = "Akamai";
                }
				$edgelocnospaces = preg_replace('/\s+/', '', $edgeloc);
				$locnospaces = preg_replace('/\s+/', '', $loc);
				//if($edgelocnospaces == $locnospaces)
				//	$edgeloc = '';
                //echo($hostdomain. ': lat='.$lat. '; long='.$long."<br/>");
				//get distance to user's location
				$distance = round(distance($userlat, $userlong ,$lat,$long,"M"),0);
				// check $loc for lat long and replace with blank
				$locparts = explode(',',$loc);
				if(count($locparts) == 2 and (is_numeric(abs($locparts[0])) or is_float(abs($locparts[0]))) and (is_numeric(abs($locparts[1])) or is_float(abs($locparts[0]))))
					$loc = '';
			}
		}
		else
		{
			//echo("new domain non-geo: ".$hostdomain."<br/>");
		}
        // update domeref if shard is on a network
        //if($domref == "Shard" and $network != '')
       // {
       //   $domref = "CDN";
       // }
        // check for missing edgeloc and set to loc if possible
        $stripped_edgeloc = preg_replace('/[ ,]+/', '', $edgeloc);
        if($stripped_edgeloc == '')
        {
//echo("edgeloc is blank, setting to loc: ".$loc."<br/>");
            $edgeloc = $loc;
            list($latlong,$lat,$long) = lookupLatLongForLocation($loc);
            $distance = round(distance($userlat, $userlong ,$lat,$long,"M"),0);
        }
		//echo("Adding new subdomain ".$hostdomain.";  domref=".$domref.";  nw=".$network."<br/>");
		debug("Adding new domain to subdomain array",$hostdomain);
		$arr = array(
		"Domain Name" => $hostdomain,
		"Count" => 1,
		"Domain Type" => $domref,
		"Network" => $network,
		"Service" => $service,
		"Site Description" => $sitedesc,
		"Company" => $domprovider,
		"Category" => $category,
        "Product" => $product,
        "Group" => $group,
		"Location" => $loc,
		"Edge Name" => $edgename,
		"Edge Loc" => $edgeloc,
		"Edge IP" => $edgeaddress,
		"Latitude" => $lat,
		"Longitude" => $long,
		"Distance" => $distance,
		"Method" => $method,
        "TotBytes"  => 0,
		"Offset" => 99999,
		);
		$arrayDomains[] = $arr;
		//echo("Adding new domain ".$hostdomain.";  domref=".$domref.";  nw=".$network."<br/>");
	}
	else
	{
		//update key record
		debug("Incrementing subdomain count",$hostdomain);
		$c = intval($arrayDomains[$keyfound]["Count"]);
		$c = $c + 1;
		//echo("Incrementing domain count by 1 for ".$hostdomain." ".strval($c)."<br/>");
		$arrayDomains[$keyfound]["Count"] = $c;
        // get company and product for domain to enable 3p domain updates
        $domprovider = $arrayDomains[$keyfound]["Company"];
        $product = $arrayDomains[$keyfound]["Product"];
	}
//    if($domprovider == "")
//echo $url . " empty company<br/>";
    // deal with A NEW third party company and product - 3ps plus shards like Adobe Analytics
    if($domref == "3P" or $domref == "redirection" or $domref == "CDN" or  ($domref == "Shard" and (strpos($url,"metrics.") !== false) or strpos($url,"metric.") !== false))
    {
        $u = parseUrl($url);
        @ $h = $u["host"];
        @ $subd = $u["subdomain"];
        @ $d = $u["domain"];
//echo("Checking a product exists in 3p domain array: " .  $url . " ". $domprovider . " - " . $product . "<br/>");
        $key3p = lookupDomain3P($d,$domprovider,$product);
        if($key3p == -1)
        {
    		debug("Adding new product to 3p domain array",$d);
//echo("Adding new product to 3p domain array: " . $domprovider . " - " . $product . "<br/>");
    		$arr = array(
    		"Domain Name" => $hostdomain,
    		"Count" => 1,
    		"Domain Type" => $domref,
    		"Network" => $network,
    		"Service" => $service,
    		"Site Description" => $sitedesc,
    		"Company" => $domprovider,
    		"Category" => $category,
            "Product" => $product,
            "Group" => $group,
    		"Location" => $loc,
    		"Edge Name" => $edgename,
    		"Edge Loc" => $edgeloc,
    		"Edge IP" => $edgeaddress,
    		"Latitude" => $lat,
    		"Longitude" => $long,
    		"Distance" => $distance,
    		"Method" => $method,
			"TotBytes"  => 0,
			"Offset" => 99999,
    		);
            $array3PDomainStats[] = $arr;
        }
	else
    	{
    		//update key record
    		debug("Incrementing product count",$d);
    		$c = intval($array3PDomainStats[$key3p]["Count"]);
    		$c = $c + 1;
            $array3PDomainStats[$key3p]["Count"] = $c;
            $dc = $array3PDomainStats[$key3p]["Domain Name"];
            debug("Concatenating domain",$dc);
            if(strpos($dc,$hostdomain) === false)
            {
                $dc = $dc . ";" . $hostdomain;
                $array3PDomainStats[$key3p]["Domain Name"] = $dc;
            }
			$dp = $array3PDomainStats[$key3p]["Product"];
			if($dp!= '')
			{
				debug("Concatenating product",$dp);
				if(strpos($dp,$product) === false)
				{
					$dp = $dp . "; " . $product;
					$array3PDomainStats[$key3p]["Product"] = $dp;
				}
			}
//echo("Incrementing product count by 1 for " .$hostdomain.": " . $company . " - " . $product . " =".strval($c)."<br/>");
    	}
        // sort array by total number of bytes
//        $bytetotal = array();
//        foreach ($array3PDomainStats as $key => $row)
//        {
//            $bytetotal[$key] = $row['TotBytes'];
//        }
//        array_multisort($bytetotal, SORT_DESC, $array3PDomainStats);
    } // if a 3P domain
}  // end function AddDomainToArray
function isShardonCDN($domain)
{
    global $arrayDomains;
//echo(" checking domain for update " . $domain."<br/>");
    $domainfound = '';
    $result = false;
    foreach($arrayDomains as $k => $v)
    {
   //echo($k . " - " . $v."<br/>");
        if($k == 'Domain Name' and $v == $domain)
        {
            $domainfound = true;
        }
        if($k == 'Network' and $v == 'CDN' and $domainfound == true)
        {
            $result = true;
            break;
        }
    }
    return $result;
}
function checkdomainforNamedCDNLocation($edgename,$edgeaddress)
{
		$boolknownCDN = false;
		$network = '';
		$method = 'IP';
		$service = '';
        $city = '';
        $region = '';
		$country = '';
		$edgeloc = '';
		// for safety, convert to lowercase
		$edgename = strtolower($edgename);
		// check known CDNs
		//google
		if(strpos($edgename,"1e100.net") !== false or strpos($edgename,".google.com") !== false)
		{
			$boolknownCDN = true;
			$googleparts = explode(".",$edgename);
//echo("exploding: ".$edgename."<pre>");
//print_r($googleparts);
//echo("</pre>");
			$string = substr($googleparts[0],0,3);
			$googleIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
            $l = strlen($googleIATACode);
//error_log('google IATA code '.$googleIATACode);
            $network = 'Google';
            if($l == 3)
            {
    			list($edgeloc,$latlong,$lat,$long) = lookupIATAAirportCode($googleIATACode);
//echo($googleIATACode. " = google loc: ". $edgeloc."<br/>");
    			if(strlen($edgeloc) < 1)
                {
    				$boolknownCDN = false;
                    $edgeloc = '';
                    $latlong = '';
                    $lat = '';
                    $long = '';
                    $boolknownCDN = false;
                }
                else
                {
    			    $method = 'Name';
                }
            }
            else
            {
                $edgeloc = '';
                $latlong = '';
                $lat = '';
                $long = '';
                $boolknownCDN = false;
            }
		}
		// akamai - replaced by ip lookup on ip address in x-cache debug header
		if(strpos($edgename,".deploy.akamaitechnologies") !== false or strpos($edgename,".deploy.static.akamaitechnologies") !== false)
		{
			$boolknownCDN = true;
			$x = strpos($edgename,".deploy");
			$edgenameip = substr($edgename,0,$x);
			$string = substr($edgenameip,1);
			$atloc = str_replace("-",".",$string);
//echo ('Akamai server ip derived by name: '.$atloc.'<br/>');
			list($edgeloc,$city,$region,$country,$lat,$long) = lookupLocationforIP($atloc);
//echo ('Akamai server loc: '.$edgeloc.'<br/>');
//echo ('Akamai server city: '.$city.'<br/>');
//echo ('Akamai server region: '.$region.'<br/>');
//echo ('Akamai server country: '.$country.'<br/>');
			$network = 'Akamai';
			$method = 'Name';
			if(!isset($city) or $city == '')
				$edgeloc = $country;
			//override location
			$edgeloc = "London, United Kingdom";
			//echo ('Akamai o/r server loc: '.$edgeloc.'<br/>');
			list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
		}
		if(strpos($edgename,".akamaiedge.net") !== false)
		{
			$network = 'Akamai';
			$boolknownCDN = true;
			$method = 'Name';
			//override location
			$edgeloc = "London, United Kingdom";
			list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
		}
		if(strpos($edgename,".akadns.net") !== false)
		{
			$network = 'Akamai';
			//$boolknownCDN = true;
			//$method = 'Name';
			//override location
			//$edgeloc = "London, United Kingdom";
			//list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
		}
		if(strpos($edgename,"akamai.net") !== false)
		{
			$network = 'Akamai';
			$boolknownCDN = true;
			$method = 'Name';
			//override location
			$edgeloc = "London, United Kingdom";
			list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
		}
		if(strpos($edgename,".twimg.com") !== false or strpos($edgename,".twitter.com") !== false or strpos($edgename,".akamaihd.net") !== false)
		{
			// Twitter on Akamai
			$network = 'Akamai';
			$boolknownCDN = true;
			$method = 'Name';
			//override location
			$edgeloc = "London, United Kingdom";
			list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
		}
		// EDGECAST - and white labels: SOFTLAYER
		if(strpos($edgename,"static.reverse.softlayer.com") !== false or strpos($edgename,"static.sl-reverse.com") != false)
		{
			$network = 'IBM Softlayer (on Edgecast)';
			if(strpos($edgename,"wac.") !== false )
				$service = "HTTP Small Object";
			if(strpos($edgename,"wpc.") !== false )
				$service = "HTTP Large File"	;
			if(strpos($edgename,"fml.") !== false )
				$service = "Flash Media Streaming (Live)";
			if(strpos($edgename,"fms.") !== false )
				$service = "Flash Media Streaming (On-Demand)";
			if(strpos($edgename,"wms.") !== false )
				$service = "Windows Media Streaming)";
            if(strpos($edgename,"adn.") !== false )
				$service = "Application Delivery Network)";
		}
		if(strpos($edgename,".edgecastcdn.net") !== false or strpos($edgename,".systemcdn.net") !== false or strpos($edgename,".transactcdn.net") !== false or strpos($edgename,".etacdn.net") !== false)
		{
			$network = 'Edgecast';
			if(strpos($edgename,"wac.") !== false )
				$service = "HTTP Small Object";
			if(strpos($edgename,"wpc.") !== false )
				$service = "HTTP Large File"	;
			if(strpos($edgename,"fml.") !== false )
				$service = "Flash Media Streaming (Live)";
			if(strpos($edgename,"fms.") !== false )
				$service = "Flash Media Streaming (On-Demand)";
			if(strpos($edgename,"wms.") !== false )
				$service = "Windows Media Streaming)";
           	if(strpos($edgename,"adn.") !== false )
				$service = "Application Delivery Network)";
		}
		// EDGECAST - VERIZON
		if(strpos($edgename,".v1cdn.net") !== false or strpos($edgename,".v2cdn.net") !== false  or strpos($edgename,".v3cdn.net") !== false or strpos($edgename,".v4cdn.net") !== false or strpos($edgename,".v5cdn.net") !== false )
		{
			$network = 'Edgecast';
            if(strpos($edgename,"wac.") !== false )
				$service = "HTTP Small Object";
			if(strpos($edgename,"wpc.") !== false )
				$service = "HTTP Large File"	;
			if(strpos($edgename,"fml.") !== false )
				$service = "Flash Media Streaming (Live)";
			if(strpos($edgename,"fms.") !== false )
				$service = "Flash Media Streaming (On-Demand)";
			if(strpos($edgename,"wms.") !== false )
				$service = "Windows Media Streaming)";
           	if(strpos($edgename,"adn.") !== false )
				$service = "Application Delivery Network)";
		}
		if(strpos($edgename,".hwcdn.net") !== false )
		{
			$network = 'Highwinds';
		}
		// More Google
		if(strpos($edgename,".googlehosted.com") !== false or strpos($edgename,"googlesyndication.") !== false or strpos($edgename,".googleapis.com") !== false or strpos($edgename,".doubleclick.net") !== false)
		{
			$network = 'Google';
		}
		// AOL
		if(strpos($edgename,".aol.com") !== false)
		{
			$network = 'AOL Time Warner';
		}
		// NETDNA
		if(strpos($edgename,".netdna-ssl.com") !== false)
		{
			$network = 'NetDNA';
		}
		if(strpos($edgename,".netdna-cdn.com") !== false)
		{
			$network = 'NetDNA';
		}
		if(strpos($edgename,".netdna.com") !== false)
		{
			$network = 'NetDNA';
		}
		if(strpos($edgename,"ib.anycast.adnxs.com") !== false)
		{
			$network = 'AppNexus CDN';
		}
		if(strpos($edgename,".footprint.net") !== false)
		{
			$network = 'Level3';
		}
		if(strpos($edgename,".ukfast.net") !== false)
		{
			$network = 'Level3';
		}
		if(strpos($edgename,".internapcdn.net") !== false)
		{
			$network = 'Internap';
		}
		if(strpos($edgename,".cachefly.net") !== false)
		{
			$network = 'Cachefly';
		}
		if(strpos($edgename,".iis.net") !== false or strpos($edgename,".msedge.net") !== false or strpos($edgename,"-msedge.net") !== false or strpos($edgename,".cloudapp.net") !== false)
		{
			$network = 'Microsoft';
		}
		if(strpos($edgename,".vo.msecnd.net") !== false or strpos($edgename,".core.windows.net") !== false)
		{
			$network = 'Microsoft Azure';
		}
		if(strpos($edgename,".cloudapp.azure.com") !== false)
		{
			$network = 'Microsoft Azure';
            $llparts = explode(".",$edgename);
            // find the part with cloudapp
            $pos = array_search("cloudapp",$llparts) - 1;
			$string = $llparts[$pos];
			switch(strtolower($string))
			{
				case "westeurope":
					$edgeloc = "Amsterdam, Netherlands";
					break;
                case "northeurope":
					$edgeloc = "Dublin, Ireland";
					break;
                case "northeastgermany":
                    $edgeloc = "Magdeburg, Saxony-Anhalt, Germany";
					break;
                case "centralgermany":
                    $edgeloc = "Frankfurt, Hesse, Germany";
					break;
                case "westuk":
                    $edgeloc = "Cardiff, Wales, United Kingdom";
					break;
                case "southUK":
                    $edgeloc = "London, United Kingdom";
					break;
                case "centralfrance":
                    $edgeloc = "France";
					break;
                case "southfrance":
                    $edgeloc = "France";
					break;
                case "eastus":
                    $edgeloc = "Ashburn, Virginia, USA";
					break;
                default:
                    $edgeloc = "TBD";
                    break;
			//echo ('Azure cloudapp server id: '.$string.' @ location = '.$edgeloc.'<br/>');
            } // end switch azure
            if($edgeloc != 'TBD')
    			list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
            else
                $edgeloc  = ''; // reset
		}
		if(strpos($edgename,".simplecdn.net") !== false)
		{
			$network = 'Simple CDN';
		}
		if(strpos($edgename,".instacontent.net") !== false or strpos($edgename,".mirror-image.net") !== false)
		{
			$network = 'Mirror Image';
		}
		if(strpos($edgename,".ay1.b.yahoo.com") !== false or strpos($edgename,".yimg") !== false or strpos($edgename,".yahooapis.com") !== false)
		{
			$network = 'Yahoo';
		}		
		if(strpos($edgename,".insnw.net") !== false or strpos($edgename,".inscname.net") !== false)
		{
			$network = 'Instart Logic';
		}
		if(strpos($edgename,".cotcdn.net") !== false)
		{
			$network = 'Cotendo CDN';
		}		
		if(strpos($edgename,"bo.lt") !== false)
		{
			$network = 'BO.LT';
		}
		if(strpos($edgename,".afxcdn.net") !== false)
		{
			$network = 'afxcdn.net';
		}		
		if(strpos($edgename,".lxdns.com") !== false)
		{
			$network = 'ChinaNetCenter';
		}
		if(strpos($edgename,".att-dsa.net") !== false)
		{
			$network = 'AT&T';
		}		
		if(strpos($edgename,".voxcdn.net") !== false)
		{
			$network = 'VoxCDN';
		}		
		if(strpos($edgename,".bluehatnetwork.com") !== false)
		{
			$network = 'Blue Hat Network';
		}
		if(strpos($edgename,".swiftcdn1.com") !== false)
		{
			$network = 'SwiftCDN';
		}
		if(strpos($edgename,".gslb.taobao.com") !== false)
		{
			$network = 'Taobao'  ;
		}				
		if(strpos($edgename,".gslb.tbcache.com") !== false)
		{
			$network = 'Alimama'  ;
		}				
		if(strpos($edgename,".yottaa.net") !== false)
		{
			$network = 'Yottaa'  ;
		}				
		if(strpos($edgename,".cubecdn.net") !== false)
		{
			$network = 'cubeCDN'  ;
		}
        if(strpos($edgename,"zyo.above.net") !== false or strpos($edgename,"zayo.com") !== false)
		{
			$network = 'Zayo Group'  ;
		}
        if(strpos($edgename,".aaplimg.com") !== false)
		{
			$network = 'Apple'  ;
		}
		//if(strpos($edgename,"") !== false)
		//{
		//	$network = ''  ;
		//}
		// LimeLight
		if(strpos($edgename,".llnw.net") !== false or strpos($edgename,".llnwd.net") !== false)
		{
			$boolknownCDN = true;
			$llparts = explode(".",$edgename);
			$string = $llparts[1];
			$llIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
			//echo ('Limelight IATA code '.$llIATACode.'<br/>');
			list($edgeloc,$latlong,$lat,$long) = lookupIATAAirportCode($llIATACode);
			$method = "Name";
			$network = 'Limelight';
		}
		//verisign
		if(strpos($edgename,".verisign.com") !== false)
		{
			$boolknownCDN = true;
			$llparts = explode(".",$edgename);
			$string = $llparts[1];
			$llIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
			//echo ('Limelight IATA code '.$llIATACode.'<br/>');
			list($edgeloc,$latlong,$lat,$long) = lookupIATAAirportCode($llIATACode);
			$method = "Name";
			$network = 'Verisign';
		}
		//CDNIFY
		if(strpos($edgename,".cdnify.net") !== false)
		{
			$boolknownCDN = true;
			$cdnparts = explode(".",$edgename);
			$string = $cdnparts[0];
			// remove numerics from string
			$cdncity = preg_replace('/[0-9]+/', '', $string);
			switch($cdncity)
			{
				case "london":
					$edgeloc = "London, United Kingdom";
					list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
					$method = "Name";
					break;
				default:
					$boolknownCDN = false;
			}
			$network = 'CDNify';
		}
		//CDN77
		if(strpos($edgename,".cdn77.com") !== false)
		{
			$boolknownCDN = true;
			$cdn77parts = explode("-",$edgename);
			$network = 'CDN77';
			$string = $cdn77parts[0];
			if($string== "london")
			{
				$edgeloc = "London, United Kingdom";
				list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
				$method = "Name";
			}
			else
				$boolknownCDN = false;
		}
		// amazonaws
		if(strpos($edgename,".amazonaws") !== false)
		{
			$boolknownCDN = true;
			$awsparts = explode(".",$edgename);
			//echo("exploding: ".$edgename."<pre>");
			//print_r($awsparts);
			//echo("</pre>");
			$string = $awsparts[1];
			//echo ('AWS server id: '.$string.'<br/>');
			switch(strtolower($string))
			{
				case "eu-west-1":
					$edgeloc = "Dublin,Ireland";
					break;
				case "us-east-1":
					$edgeloc = "Virginia,US";
					break;
				case "us-west-1":
					$edgeloc = "California,US";
					break;
				case "us-west-2":
					$edgeloc = "Oregon,US";
					break;
				case "ap-northeast-1":
					$edgeloc = "Tokyo,Japan";
					break;
				case "ap-southwest-1":
					$edgeloc = "Singapore";
					break;
				case "sa-east-1":
					$edgeloc = "San Paulo,Brazil";
					break;
				case "ap-southeast-2":
					$edgeloc = "Sydney,Australia";
					break;
				case 'eu-central-1':
					$edgeloc = "Frankfurt, Germany";
					break;
				case "compute-1":
					//A few services, such as Amazon EC2, let you specify an endpoint that does not include a specific region, for example, https://ec2.amazonaws.com. In that case, AWS routes the endpoint to us-east-1
					$edgeloc = "Virginia,US"; //Amazon Elastic Compute Cloud
					break;
				default:
					$edgeloc = "Virginia,US";
			}
			//echo ('AWS server id: '.$string.' @ location = '.$edgeloc.'<br/>');
			list($latlong,$lat,$long) = lookupLatLongForLocation($edgeloc);
			$network = 'Amazon AWS';
			$method = 'Name';
			$servicestring = strtolower($awsparts[0]);
			$serviceparts = explode("-",$servicestring);
			switch ($serviceparts[0])
			{
				case "s3":
					$service = "Simple Storage Sevice";
					break;
				case "ec2":
					$service = "Elastic Compute Cloud";
					break;
				default:
					$service = $serviceparts[0];
			}
			//echo ('AWS service: '.$string.'<br/>');
		}
		// amazon cloudfront
		if(strpos($edgename,".cloudfront.net") !== false)
		{
			$boolknownCDN = true;
			$cloudfrontparts = explode(".",$edgename);
			//echo("exploding: ".$edgename."<pre>");
			//print_r($cloudfrontparts);
			//echo("</pre>");
			$string = $cloudfrontparts[1];
			$cloudfrontIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
			//echo ('cloudfront IATA code '.$cloudfrontIATACode.'<br/>');
			list($edgeloc,$latlong,$lat,$long) =  lookupIATAAirportCode($cloudfrontIATACode);
			$network = 'Amazon Cloudfront';
			$method = 'Name';
		}
		//facebook.com
		// e.g. edge-star6-shv-02-lhr3.facebook.com
		if(strpos($edgename,".facebook.com") !== false or strpos($edgename,".fbcdn.net") !== false)
		{
			$boolknownCDN = true;
			$fbparts1 = explode(".",$edgename);
			//echo("exploding: ".$edgename."<pre>");
			//print_r($fbparts1);
			//echo("</pre>");
			$fbparts2 = explode("-",$fbparts1[0]);
			$fbparts2 = explode("-",$fbparts1[0]);
			$cnt = count($fbparts2) - 1;
			//echo("exploding: ".$fbparts1[0]."<pre>");
			//print_r($fbparts2);
			//echo("</pre>");
			$string = substr($fbparts2[$cnt],0,3); // get airport code from last part of name before the domain name
			$fbIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
			//echo ('Facebook IATA code '.$fbIATACode.'<br/>');
            // airport code lookup disabled - Nov 2016 due to returning invalid data FRT for Dublin location - IPV6
			//list($edgeloc,$latlong,$lat,$long) =  lookupIATAAirportCode($fbIATACode);
			$network = 'Facebook';
		    $method = 'Name';
            $edgeloc = "Dublin,Ireland";
            $lat = "53.362692045811";
            $long = "-6.2581935465529";
            $latlong = $lat.",".$long;
		}
		// AppNexus
		if(strpos($edgename,".adnexus.net") !== false)
		{
			// eg. float.1308.bm-impbus.prod.fra1.adnexus.net // 6 parts
            // e.g. 244.bm-nginx-loadbalancer.mgmt.fra1.adnexus.net // 5 parts
            // e.g. 205.bm-nginx-loadbalancer.mgmt.ams1.adnexus.net
			$boolknownCDN = true;
			$adnparts = explode(".",$edgename);
			//echo("exploding: ".$edgename."<pre>");
			//print_r($adnparts);
			//echo("</pre>");
			$cnt = count($adnparts);
			$string = substr($adnparts[$cnt-3],0,3); // adjust to number of parts in array
			$adnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
			//echo ('Ad Nexus IATA code '.$adnIATACode.'<br/>');
			list($edgeloc,$latlong,$lat,$long) =  lookupIATAAirportCode($adnIATACode);
			$method = "Name";
			$network = 'AppNexus';
		}
		// CDNetworks
		if(strpos($edgename,".cdngc.net") !== false)
		{
            $network = "CDNetworks";
		    $method = "Name";
        }
        //Section.io formely Squixa
       	if(strpos($edgename,".section.io") !== false)
        {
            $method = "Name";
            $network = "Section.io";
        }
		// lookup location for other address		  // cater for error on API lookup
//echo ("IP edgeloc check " . $edgeloc . " " . $latlong . " " . $lat . " " . $long);
		if(($boolknownCDN == false or $latlong == '') and $edgeloc == '') 
		{
			list($edgeloc,$city,$region,$country,$lat,$long) = lookupLocationforIP($edgeaddress);
//echo ('other nw: edge loc '.$edgeloc.' from ip: '.$edgeaddress.'<br/>');
			if(isset($city))
				$eloc = $city;
			else
				$eloc = '';
			if(isset($region))
				if($eloc == '')
					$eloc = $region;
				else
					$eloc .= ", ".$region;
			if(isset($country))
				if($eloc == '')
					$eloc = $country;
				else
					$eloc .= ", ".$country;
			if($eloc != '')
				$edgeloc = $eloc;
			// if only lat long set, get location from it
			$method = 'IP';
		}
		//echo ('edge loc: '.$edgeloc.'<br/>');
	return array($edgeloc,$city,$region,$country,$lat,$long,$network,$method,$service);
}
Function UpdateDomainLocationFromHeader($inurl,$xservedby,$xpx,$xedgelocation,$server,$cfray,$xcdngeo,$xcdn,$via,$xcache,$debuginfo)
{
	global $arrayDomains,$userlat,$userlong;
	$cdnIATACode = '';
	$service = '';
    $network = '';
	if($xservedby == '' and $xpx == '' and $xedgelocation == '' and $server == '' and $cfray == '' and $xcdngeo == '' and $xcdn == '' and $via = '' and $xcache = '')
		return false;
//echo("<br>UpdateDomainLocationFromHeader called from ".$debuginfo."<br/>");
	// get host domain
	list($hostdomain, $p) = getDomainHostFromURL($inurl,false,"UpdateDomainLocationFromHeader");
	//get edgeservename for the domain
	$edgeserver = getEdgeNameForDomain($hostdomain);
    // AKAMAI - from a debug header - this will override the default London location given for an edgeserver name match
    if($xcache != ''and strpos($edgeserver,"deploy.static.akamaitechnologies.com") !== false)
    {
        $network = "Akamai";
		$method = "Header " . "(X-Cache)";
        $cdnparts = explode("-",$xcache);
//echo("Akamai exploding: ".$xcache."<pre>");
//print_r($cdnparts);
//echo("</pre>");
        $ip1 = filter_var(substr($cdnparts[0],-3), FILTER_SANITIZE_NUMBER_INT);
        $ip4 = filter_var(substr($cdnparts[3],0,3), FILTER_SANITIZE_NUMBER_INT);
        $ip = $ip1 . '.' . $cdnparts[1] .'.' . $cdnparts[2] . '.' . $ip4 ;
//echo("Akamai IP: ".$ip."<br/>");
        list($edgeloc, $city,$region,$country,$lat,$long) = lookupLocationforIP($ip);
//echo("Akamai edgeloc: ".$edgeloc."<br/>");
		if ($country == '' || $country == "United Kingdom")
		{
			// fill in values
			$edgeloc = "London, United Kingdom";
			$city = "London";
			$country = "United Kingdom";
		}
//echo "akamai x-cache derived ip: '" . $ip . "' = " . $edgeloc . "<br/>";
//echo ('Akamai server loc: '.$edgeloc.'<br/>');
//echo ('Akamai server city: '.$city.'<br/>');
//echo ('Akamai server region: '.$region.'<br/>');
//echo ('Akamai server country: '.$country.'<br/>');
    }
    // FASTLY
    if($xservedby != '' and strpos($edgeserver,"fastly") !== false)
    {
        $network = "Fastly";
		$method = "Header " . "(X-Served-By)";
        $cdnparts = explode("-",$xservedby);
//echo("Fastly exploding: ".$xservedby."<pre>");
//print_r($cdnparts);
//echo("</pre>");
		$cnt = count($cdnparts) - 1;
        $string = $cdnparts[$cnt];
		// remove Twitter prefix if at least 5 chars
		$strlen = strlen($string);
		if(strtolower(substr($string,0,2)) == "tw" and $strlen >= 5)
			$string = substr($string,2);
        $cdnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
//echo ('Fasly cdn IATA code '.$cdnIATACode.'<br/>');
    }
	// CDNetworks
    if($xpx != '')
    {
        $network = "CDNetworks";
		$method = "Header " . "(X-Px)";
        $cdnparts = explode(".",$xpx);
// e.g. ht h0-s204.p5-cdg.cdngp.net
//echo("CDNetworks exploding: ".$xpx."<pre>");
//print_r($cdnparts);
//echo("</pre>");
		$cnt = count($cdnparts) - 1;
        $string = $cdnparts[1];
		$hpos = strpos($string,'-');
		$string = substr($string,$hpos+1,3);
        $cdnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
    //echo ('CDNetworks cdn IATA code '.$cdnIATACode.'<br/>');
    }
	// KEYCDN, CDN.net, CDNsun
    if($xedgelocation != '')
    {
		// recognise network
		if(strpos(strtolower($edgeserver),'keycdn') !== false)
			$prov = "keycdn";
		if(strpos(strtolower($edgeserver),'cdn.net') !== false)
			$prov = "cdn.net";
		if(strpos(strtolower($edgeserver),'cdnsun.com') !== false or strpos(strtolower($edgeserver),'cdnsun.net') !== false )
			$prov = "cdnsun";
//echo ('xedgelocation cdn provider: '.$prov .'<br/>');
		// KeyCDN
		if($prov == "keycdn")
		{
			$cdnparts = explode(".",$xedgelocation);
			if (count($cdnparts) == 1)
			{
				// e.g. defr
				//echo("KeyCDN exploding: ".$xedgelocation."<pre>");
				//print_r($cdnparts);
				//echo("</pre>");
				$cnt = count($cdnparts) - 1;
				$string = $cdnparts[0];
				$countrycode = strtolower(substr($string,0,2));
				$citycode = strtolower(substr($string,2,2));
				//echo ('KeyCDN cdn countrycode '.$countrycode.'<br/>');
				//echo ('KeyCDN cdn citycode '.$citycode.'<br/>');
				switch($countrycode)
				{
					case 'de':
						switch($citycode)
						{
							case "fr":
								$cdnIATACode = "FRA"; // Frankfurt, Germany
								break;
							default:
								$cdnIATACode = 	$string;
						}
						break;
					default:
						$cdnIATACode = 	$string;
				}
				$network = "KeyCDN";
				$method = "Header " . "(X-Edge-Location)";
			}
		}
		if($prov == "cdn.net")
		{
			$network = "CDN.net";
			$method = "Header " . "(X-Edge-Location)";
			$edgeloc = $xedgelocation;
		}
		if($prov == "cdnsun")
		{
			$network = "CDNsun";
			$method = "Header " . "(X-Edge-Location)";
			$edgeloc = $xedgelocation;
		}		
        //$cdnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
        //echo ($prov .' cdn IATA code '.$cdnIATACode.'<br/>');
		//echo ($prov .' cd edgeloc '.$edgeloc .'<br/>');
    }
	// EDGECAST // BitGravity, Microsoft Azure on Edgecast
    if($server != '' and $network == '')
    {
//echo ('Edgecast server string '.$server.'<br/>');
//echo ('network string '.$network.'<br/>');
		$bitgravityfound = false;
		$edgecastfound = false;
		$ecsecd = substr($server,0,3);
        $ecacc = substr($server,0,5);
        switch ($ecsecd)
        {
            case "ECD":
            case "ECS":
                $edgecastfound = true;
       		    $network = "Edgecast";
                break;
            case "ECA":
    		    $edgecastfound = true;
    		    $network = "Edgecast";
                break;
            default:
    			$bg = substr($server,0,2);
    			if($bg == 'v/')
                {
    				$bitgravityfound = true;
                    $network = "BitGravity";
                }
    			// v/1.1.1/v1lhr1-www
        }
		if($edgecastfound == true)
		{
			//echo ('Edgecast found server string '.$server.'<br/>');
			$method = "Header " . "(Server)";
			$cdnparts = explode('(',$server);
			// echo("Edgecast exploding: ".$server."<pre>");
			// print_r($cdnparts);
			// echo("</pre>");
			$string = substr($cdnparts[1],0,3);
			//echo ('Edgecast string '.$string.'<br/>');
			$cdnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
//echo ('Edgecast cdn IATA code: '.$cdnIATACode.'<br/>');
			if(strpos($edgeserver,"wac.") !== false )
				$service = "HTTP Small Object";
			if(strpos($edgeserver,"wpc.") !== false )
				$service = "HTTP Large File"	;
			if(strpos($edgeserver,"fml.") !== false )
				$service = "Flash Media Streaming (Live)";
			if(strpos($edgeserver,"fms.") !== false )
				$service = "Flash Media Streaming (On-Demand)";
			if(strpos($edgeserver,"wms.") !== false )
				$service = "Windows Media Streaming)";
            if(strpos($edgeserver,"adn.") !== false )
				$service = "Application Delivery Network";
		}  // end edgecast
		else
    		if($bitgravityfound  == true)
    		{
    			//echo ('BitGravity found server string '.$server.'<br/>');
    			$method = "Header " . "(Server)";
    			$cdnparts = explode('/',$server);
    			//echo("BitGravity exploding: ".$server."<pre>");
    			//print_r($cdnparts);
    			//echo("</pre>");
    			$string = substr($cdnparts[2],2,3);
    			//echo ('BitGravity string '.$string.'<br/>');
    			if($string != '')
    			{
    				$cdnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
    				//echo ('BitGravity cdn IATA code '.$cdnIATACode.'<br/>');
    			}
    			else
    			{
    				$network = "";
    			}
    		} // end bitgravity
    } // end if not an empty server header
	// CLOUDFLARE
    if($cfray != '')
    {
        $network = "Cloudflare";
		$method = "Header " . "(CF-Ray)";
        $cdnparts = explode("-",$cfray);
        //echo("Cloudflare exploding: ".$cfray."<pre>");
        //print_r($cdnparts);
        //echo("</pre>");
		$cnt = count($cdnparts) - 1;
        $string = $cdnparts[$cnt];
        $cdnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);
        //echo ('Cloudflare cdn IATA code '.$cdnIATACode.'<br/>');
    }
    // INCAPSULA and others named in xcdn header
    if($xcdn != '' and $network == '')
    {
        $network = $xcdn;
	//	$method = "Header " . "(X-CDN)";
        //echo('header CDN: '. $network.'<br/>');
    }
	// update domain if network is identified
	if($network != '')
	{
		// if IATA code set, get location
		if($cdnIATACode != '')
		{
//echo ('getting location for network: '. $network .' IATA code '.$cdnIATACode.'<br/>');
			list($edgeloc,$latlong,$lat,$long) = lookupIATAAirportCode($cdnIATACode);
			//echo ("airportcode lookup for: " . $inurl. "; code = ".$cdnIATACode . ": network: " . $network . "; method: " . $method . " " . $cdnparts[0] . " " . $cdnparts[1] . " " . $cdnparts[2] . " " . $cdnparts[03]."<br/>");
            //$edgeloc = lookupLocationForLatLong($lat,$long);
		}
		// else already have location
//echo ($network.': location '.$edgeloc.'<br/>');
		//echo( "searching for host ".$hostdomain . " on ".$inurl."<br/>");
		// find domain
		$keyfound = lookupDomain($hostdomain);
		//$found = false;
		//foreach($arrayDomains as $key =>$value)
		//{
		//	//echo("<pre>");
		//	//print_r ($value);
		//	//echo("</pre>");
		//	if($hostdomain == $value['Domain Name'])
		//	{
		//		//echo( $key.": checking " . $hostdomain." domain against ".$value['Domain Name']."<br/>");
		//		$found = true;
		//		$keyfound = $key;
		//		break;
		//	}
		//}
		//if($keyfound < 0)
		//	echo( "Not found domain ".$hostdomain);
		//else
		//	echo( "Found domain ".$hostdomain);
        //echo($hostdomain. '(locheader): lat='.$lat. '; long='.$long."<br/>");
		//get distance to user location
		$distance = round(distance($userlat, $userlong ,$lat,$long,"M"),0);
		// update domain
		if($network !='')
			$arrayDomains[$keyfound]["Network"] = $network;
		if($edgeloc !='')	
			$arrayDomains[$keyfound]["Edge Loc"] = $edgeloc;
		if($lat !='')	
			$arrayDomains[$keyfound]["Latitude"] = $lat;
		if($long!='')		
			$arrayDomains[$keyfound]["Longitude"] = $long;
		if($distance !='')	
			$arrayDomains[$keyfound]["Distance"] = $distance;
		if($method !='')	
			$arrayDomains[$keyfound]["Method"] = $method;
		if($service !='')	
			$arrayDomains[$keyfound]["Service"] = $service;
        // update domeref if shard is on a network
        //$domref = $arrayDomains[$keyfound]["Domain Type"];
        //if($domref == "Shard" and $network != '')
        //{
        //  $domref = "CDN";
        //  $arrayDomains[$keyfound]["Domain Type"] = $domref;
        //}
		if($debuginfo == "main")
		{
			// update rootloc
			$arrayDomains[$keyfound]["Location"] = $edgeloc;
		}
		return array(true,$edgeloc);
	}
		return array(false,'');
}
function lookupDomain($hostdomain)
{
	global $arrayDomains;
	$found = false;
	foreach($arrayDomains as $key =>$value)
	{
//echo("<pre>");
//print_r ($value);
//echo("</pre>");
		if($hostdomain == $value['Domain Name'])
		{
//echo( $key.": checking " . $hostdomain." domain against ".$value['Domain Name']."<br/>");
			$found = true;
			break;
		}
	}
	if($found === true)
    {
//echo( $key.": found " . $hostdomain." domain against ".$value['Domain Name']."<br/>");
    	return $key;
    }
	else
		return -1;
}


function lookup3PDomain($domain)
{
	global $array3PDomainStats;

	$company = '';
	$product = '';
	$coprod = '';
	$found = false;
		if(!isset($array3PDomainStats))
			return -1;
		foreach($array3PDomainStats as $key =>$value)
		{
	// echo "<br/>lookup 3p domain product called for: " . $domain . "<br/>";
	// echo("<pre>");
	// print_r ($value);
	// echo("</pre>");
	// echo( $key.": checking domain ". $domain. " against ".$value['Domain Name']."<br/>");
			if(strpos($value['Domain Name'],$domain) != false )
			{
	// echo( $key.": FOUND checking " . $company." domain against ".$value['Company'] . " " . $product = $value['Product'] ."<br/>");
				$found = true;
				$company = $value['Company'];
				$product = $value['Product'];
				break;
			}
		}

	if($company != '')
	{
		if(strpos($product,$company) == -1)
		{
			$coprod = $company + " " + $product;
		}
		else
		{
			$coprod = $product;
		}
	}

	return($coprod);
}

function lookupDomain3P($hostdomain,$company,$product)
{
	global $array3PDomainStats;
	$found = false;
    if(!isset($array3PDomainStats))
        return -1;
	foreach($array3PDomainStats as $key =>$value)
	{
//echo "<br/>lookup 3p domain product called for: " . $hostdomain . " " . $company . " - " . $product . "<br/>";
//echo("<pre>");
//print_r ($value);
//echo("</pre>");
//echo( $key.": checking " . $company." domain ". $hostdomain. " against ".$value['Company']."<br/>");
		if($company == $value['Company'])
		{
//echo( $key.": FOUND checking " . $company." domain against ".$value['Company']."<br/>");
			$found = true;
			break;
		}
	}
	if($found === true)
    {
//echo( $key.": found " . $hostdomain." domain against ".$value['Domain Name']."<br/>");
    	return $key;
    }
	else
		return -1;
}
function getEdgeNameForDomain($hostdomain)
{
	global $arrayDomains;
	$k = lookupDomain($hostdomain);
	$edgename = $arrayDomains[$k]['Edge Name'];
	return $edgename;
}
function get_http_response_code($url) {
    @$headers = get_headers($url);
    return substr($headers[0], 9, 3);
}
function lookupIATAAirportCode($code)
{
	global $OS;
//echo(__FUNCTION__  . " - IATA code lookup for $code.." . PHP_EOL);
	$airportlocation = '';
	$latlong = '';
	$lat = '';
	$long = '';
    $l = strlen($code);
    if($l == 3 and ctype_alnum($code) and !empty($code)) // ensure 3 letters and alpha chars
    {
//echo ("checking in airportscitiesstates.json" . PHP_EOL);
        // lookup locally
        if($OS == "Windows")
            $str = file_get_contents("toaster_tools\airportscitiesstates.json");
        else
            $str = file_get_contents("toaster_tools/airportscitiesstates.json");

		$found = false;
		$json = json_decode($str);
		foreach($json as $item)
		{
			if(strToLower($item->code) == strToLower($code))
			{
		//        echo $item->iata.": ".$item->city.", ".$item->country."<br/>";
				// echo("<pre>");
				// var_dump( $item);
				// echo("</pre>");
				$found = true;
			
		
			  $airportlocation = $item->city.", ".$item->state .", ".$item->country;
//echo "airport location = " .$airportlocation;
		
				  break;
			}
		}
        unset($json);
        // lookup in 2nd local file if airport location was not found locally first
        if($found == false)
        {
//echo ("checking in airports.dat" . PHP_EOL);
			if($OS == "Windows")
            	$str = file_get_contents("toaster_tools\airports.dat");
        	else
				$str = file_get_contents("toaster_tools/airports.dat");
			
			// Loop through our array, show HTML source as HTML source; and line numbers too.
			foreach ($str as $line_num => $line) {
//echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
				$line = str_replace('"','',$line);
				$data = explode(',', $line);
//var_dump($data);
				if(strToLower($data[4]) == strToLower($code))
					{
						$found == true;
//echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
						// for ($x = 0; $x < count($data); $x++)
						// {
						// 	echo ($x . " = " . $data[$x]. PHP_EOL);
						// }
						//$arr = array ($searchcode, $data[2], $data[3], $data[6], $data[7]);
						$airportlocation = $data[2] . ", " . $data[3];
						$latlong = $data[6] . ", " . $data[7];
						$lat = $data[6];
						$long = $data[7];
//echo("airportcode reserve lookup: " . $code . " = " . $airportlocation . "<br/>");
						break;
					}
			}
			unset($filename);
        } // end found false
// echo "IATA code lookup result: ".$code. " : ".$airportlocation;
    	//return $airportlocation;
        return array($airportlocation,$latlong,$lat,$long);
    }
    else
        return '';
}
function get3PDomain($url) {
  $pieces = parse_url('http://'.$url);
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
  {
    //echo('3pdomain: '.$regs['domain'].'<br/>');
	return $regs['domain'];
  }
  //echo('3pdomain: false'.'<br/>');
  return false;
}
function get3PDescription($indomain)
{
	global $dbusage;
	$sitedesc = '';
	$category = '';
	$product = '';
    $group = '';
	$domprovider = '';

	if($dbusage == false)
	{
		return array($sitedesc,$domprovider,$category,$product,$group);
	}

//echo('start func get3PDescription<br/>'.'arg indomain: '.$indomain.'<br/>');
	// try subdomain first
	list($sitedesc,$domprovider,$category,$product,$group) = lookup3PDescriptionDirect($indomain);
	if ($domprovider == '')
	{
		//if subdomain not defined, try the actual domain
		$newdomain = get3PDomain($indomain);
		list($sitedesc,$domprovider,$category,$product,$group) = lookup3PDescriptionDirect($newdomain);
		//echo('domain return value: '.$sitedesc.'<br/>');
	}
	else
	{
		//echo('subdomain return value: '.$sitedesc.'<br/>');
	}
	//echo('return value: '.$sitedesc.'<br/>');
	//echo('end func get3PDescription<br/>');
	$sitedesc = preg_replace( '/[^[:print:]]/', '',$sitedesc);
	return array($sitedesc,$domprovider,$category,$product,$group);
}
function read3PDescriptionsFromFile()
{
	global $array3pDescriptions;
	$array3pDescriptions = file('toaster_tools/3P_desc.txt');
	//echo ("TOASTING... please wait.... <br>Retrieving Third party descriptions from file:<pre>");
	//print_r ($array3pDescriptions);
	//echo("</pre>");
}
function readSelfHosted3PDescriptionsFromFile()
{
	global $arraySelfHosted3pDescriptions;
	$arraySelfHosted3pDescriptions = file('toaster_tools/3PSelfHosted_desc.txt');
//	echo ("TOASTING... please wait.... <br>Retrieving self hosted Third party descriptions from file:<pre>");
//	print_r ($arraySelfHosted3pDescriptions);
//	echo("</pre>");
}
function read3PDescriptionsFromDB()
{
    get3PtagsAll(); // retrieve all 3P tags from NCC group database
}

function metricsCheckSameDomainsForSubDomains($domain,$host_domain)
{
	$sameDomain = false;
    // detect domain overrides
    if(strpos($domain,"metrics.") !== false or strpos($domain,"metric.") !== false)
    {
		//
		$firstdot = strpos($domain,'.');
		$maindomain = substr($domain,$firstdot);

		$firstdot = strpos($host_domain,'.');
		$mainhostdomain = substr($host_domain,$firstdot);

//echo("comparing metrics 3party:  " . $maindomain . " to host: " .  $mainhostdomain . "<br/>");
		if($maindomain == $mainhostdomain)
				$sameDomain = true;
	}
	return $sameDomain;
}

function lookup3PDescriptionDirect($domain)
{
	global $host_domain, $b3pdbPublic;
    $domaindesc = '';
    $domainprovider = '';
    $domaincat = '';
    $domainproduct = '';
    $domaingroup = '';

	// strip query string
	if (strpos($domain, "?") > 0)
	{
		$domainnoqs = substr($domain, 0, strpos($domain, "?"));
	}
	else
		$domainnoqs = $domain;

	if (metricsCheckSameDomainsForSubDomains($domain,$host_domain))
	{
	// omtrdc.net - .metrics.[host] is actually on Adobe Analytics Site Catalyst
	$domainnoqs = "sc.omtrdc.net";
//echo ("overriding domain " . $domain . " with Adobe Analytics<br/>");
	}

//echo ("looking up 3p domain " . $domainnoqs  . "; domtype: " . $domtype . "; host= " . $host_domain .  "<br/>");
    // make a curl request to the API directly
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($b3pdbPublic)
		curl_setopt($ch, CURLOPT_URL, 'https://www.webpagetoaster.com/lookup3pdb.php?host=//'.$domainnoqs.'/'); // public 3pdb
	else
		curl_setopt($ch, CURLOPT_URL, 'https://tagdb.nccgroup-webperf.com/2/find?host='.$domainnoqs); // private database - internal NCC Group/Eggplant use only
	
    $result = curl_exec($ch);
    curl_close($ch);
    // check if result is empty
//
//    if(count(json_decode($result,1))==0)
//
//    {
//
//echo ("The domain '" . $domain  . "' is not recognised");
//
//    }
    // decode json object
    $objjson = json_decode($result);
    $x = count(json_decode($result,1));
//error_log( "3p lookup: " . $domain .  " count:" . $x);
    if($x!=0)
    {
// echo "<pre>";
// var_dump($objjson );
// echo "</pre>";
            $domain3P = $objjson->domain;
            $objproduct = $objjson->product;
       	    $objcompany= $objproduct->company;
            $domainprovider = $objjson->company->name;
    		$domaindesc = html_entity_decode($objproduct->description);
			$domaindesc = str_replace('"','',$domaindesc);
			$domaindesc = str_replace("'","",$domaindesc);
            $objcat = $objproduct->category;
            $objgroup = $objcat->group;
            @$domainproduct  = html_entity_decode($objproduct->name);
    		$domaingroup = html_entity_decode($objgroup->name);
       		$domaincat = html_entity_decode($objcat->name);
    //        @$domaingroup  = html_entity_decode($arr[5]);
    //        $domainRegex = html_entity_decode($arr[6]);
//echo $domainprovider . "; cat: " . $domaincat . "; product: " . $domainproduct . "; group: ". $domaingroup . "; desc: " . $domaindesc . "<br/>";
    }
    return array($domaindesc,$domainprovider,$domaincat,$domainproduct,$domaingroup);
}
function lookup3PDescription($domain)
{
	global $array3pDescriptions,$dbusage,$host_domain;
	$desc = '';
	$domprovider = '';
	$cat = '';
    $prod = '';
    $group = '';
    $regex = '';
    $foundFixed = false;
    //lookup for full domain first and then, if not found, do regex match
    foreach ($array3pDescriptions as $line_num => $line) {
		if($dbusage == true)
		{
			$arr = $line;
		}
		else
		{
			$arr = explode("\t",$line);
		}
        if(!isset($arr[3]))
        {
            error_log("3p domain with missing desc: " . $domain3P );
        }
        $desc = '';
        $domain3P = $arr[0];
		$domainprovider = $arr[1];
		$domaincat = html_entity_decode($arr[2]);
		$domaindesc = html_entity_decode($arr[3]);
        @$domainproduct  = html_entity_decode($arr[4]);
        @$domaingroup  = html_entity_decode($arr[5]);
        $domainRegex = html_entity_decode($arr[6]);
 //echo("checking ".$domain3P." for ".$domain."<br/>");
        if(!isset($domainproduct))
          error_log($domain3P ." " .$domainprovider. " " . $domaincat . " " . $domaindesc . " " .$domainproduct . " " .$domaingroup ." missing product" );
        if(!isset($domaindesc))
          error_log($domain3P ." " .$domainprovider. " " . $domaincat . " " . $domaindesc . " " .$domainproduct . " " .$domaingroup ." missing description" );
//  lookup exact
    	if(trim(strtolower($domain)) == trim(strtolower($domain3P)))
        {
            //echo("full match found checking subject: ".$subject ."<br/>");
			$desc = $domaindesc;
			$desc = str_replace('"','',$desc);
			$desc = str_replace("'","",$desc);
			$domprovider = $domainprovider;
			$cat = $domaincat;
            $prod = $domainproduct;
            $group = $domaingroup;
//echo("checking ".$domain3P." for ".$domain." FOUND; group= ". $group. "; product= ". $prod. "<br/>");
//echo("checking ".$domain3P." for ".$domain." FOUND; desc= " . $domaindesc. "<br/>");
            $foundFixed = true;
            break;
	    }
        // exceptions - Adobe Analytics metrics. or smetrics. subdomains on the host domain
        if($foundFixed == true)
        {
            //echo $domain . " was not found in the 3p database - host: " . $host_domain . "<br/>";
            if((strpos($domain,"metrics.") !== false or strpos($domain,"metric.") !== false) and IsThisDomainaCDNofTheRootDomain($host_domain.$domain) == "Shard")
            {
//echo $domain . " not found in 3P DB: applying exception; this is identified as Adobe Analytics" . "<br/>";
                // omtrdc.net
                list($desc,$domprovider,$cat,$prod,$group) = lookup3PDescription("sc.omtrdc.net");
//echo("diverted to: " . $domprovider . ": " . $cat . " " . $prod);
            }
        }
    } // end of fixed full match
    // do regex match if not found as a fixed valud
    if($foundFixed == false)
    {
        // regex
    	foreach ($array3pDescriptions as $line_num => $line) {
    		if($dbusage == true)
    		{
    			$arr = $line;
    		}
    		else
    		{
    			$arr = explode("\t",$line);
    		}
            if(!isset($arr[3]))
            {
                error_log("3p domain with missing desc: " . $domain3P );
            }
            $desc = '';
            $domain3P = $arr[0];
    		$domainprovider = $arr[1];
    		$domaincat = html_entity_decode($arr[2]);
    		$domaindesc = html_entity_decode($arr[3]);
            @$domainproduct  = html_entity_decode($arr[4]);
            @$domaingroup  = html_entity_decode($arr[5]);
            $domainRegex = html_entity_decode($arr[6]);
     //echo("checking ".$domain3P." for ".$domain."<br/>");
            if(!isset($domainproduct))
              error_log($domain3P ." " .$domainprovider. " " . $domaincat . " " . $domaindesc . " " .$domainproduct . " " .$domaingroup ." missing product" );
            if(!isset($domaindesc))
              error_log($domain3P ." " .$domainprovider. " " . $domaincat . " " . $domaindesc . " " .$domainproduct . " " .$domaingroup ." missing description" );
    //  lookup exact
    //		if(trim(strtolower($domain)) == trim(strtolower($domain3P)))
    // lookup regex
            $subject = trim(strtolower($domain));
            $pattern =  $domainRegex;
            $pattern = "@" . str_replace("/","",$pattern) . "@"; // remove forward slashes for matching to a domain and add new delimiters
            $foundByRegex = preg_match($pattern, $subject);
//echo("regex checking subject: ".$subject ." for pattern:".$pattern."<br/>");
            if($foundByRegex == 1)
    		{
//echo("regex found checking subject: ".$subject ." for pattern:".$pattern."<br/>");
    			$desc = $domaindesc;
    			$desc = str_replace('"','',$desc);
    			$desc = str_replace("'","",$desc);
    			$domprovider = $domainprovider;
    			$cat = $domaincat;
                $prod = $domainproduct;
                $group = $domaingroup;
//echo("regex checking ".$domain3P." for ".$domain." FOUND; group= ". $group. "; product= ". $prod. "<br/>");
//echo("regex checking ".$domain3P." for ".$domain." FOUND; desc= " . $domaindesc. "<br/>");
    			break;
    		}
            else
                {
                    //echo("regex NOT found checking subject: ".$subject ." for pattern:".$pattern."<br/>");
                }
    	}
        // NCC Group RUM fix
        //if(strpos(strtolower($domain),"nccgroup-webperf") != false)
    //    {
    //        $prod =  "Real User Montoring from NCC Group Web Performance";
    //        $domprovider = "NCC Group";
    //        $group = "Analytics";
    //        $cat = "Performance";
    //        $desc = "Our Real User Monitoring service tells the story of your websites performance from the perspective of the people who use it";
    //    }
        // exceptions - Adobe Analytics metrics. or smetrics. subdomains on the host domain
        if($foundByRegex != 1)
        {
            //echo $domain . " was not found in the 3p database - host: " . $host_domain . "<br/>";
            if((strpos($domain,"metrics.") !== false or strpos($domain,"metric.") !== false) and IsThisDomainaCDNofTheRootDomain($host_domain.$domain) == "Shard")
            {
//echo $domain . " not found in 3P DB: applying exception; this is identified as Adobe Analytics" . "<br/>";
                // omtrdc.net
                list($desc,$domprovider,$cat,$prod,$group) = lookup3PDescription("sc.omtrdc.net");
//echo("diverted to: " . $domprovider . ": " . $cat . " " . $prod);
            }
        }
    }
    // comparison for new view
//echo "third party info from extract<br/>";
//echo $domprovider . "; cat: " . $cat . "; product: " . $prod . "; group: ". $group . "; desc: " . $desc . "<br/>";
//echo "third party info from direct lookup<br/>";
//    lookup3PDescriptionDirect($domain);
	return array($desc,$domprovider,$cat,$prod,$group);
}
function getSelfHosted3PFiles()
{
	global $arraySelfHosted3pDescriptions,$dbusage;
	$desc = '';
	$domprovider = '';
	$cat = '';
	foreach ($arraySelfHosted3pDescriptions as $line_num => $line) {
		if($dbusage == true)
		{
			$arr = $line;
		}
		else
		{
			$arr = explode("\t",$line);
		}
		$domain3P = $arr[0];
		$domainprovider = $arr[1];
		$domaincat = html_entity_decode($arr[2]);
		$domaindesc = html_entity_decode($arr[3]);
        $domainproduct  = html_entity_decode($arr[4]);
		//echo("checking ".$domain3P." for ".$domain."<br/>");
		if(trim($domain) == trim($domain3P))
		{
			$desc = $domaindesc;
			$desc = str_replace('"','',$desc);
			$desc = str_replace("'","",$desc);
			$domprovider = $domainprovider;
			$cat = $domaincat;
            $prod = $domainproduct;
//echo("checking ".$domain3P." for ".$domain." FOUND; product= ". $prod. "<br/>");
			break;
		}
	}
	return array($desc,$domprovider,$cat,$prod);
}
function dbConnect()
{
	global $dbcon;
    $host="";
    $port=3306;
    $socket="";
    $user="";
    $password="";
    $dbname="";
	//echo("connecting to DB<br/>");
	$dbcon = new mysqli($host, $user, $password, $dbname, $port, $socket)
		or die ('Could not connect to the database server' . mysqli_connect_error());
	//echo("connected to DB<br/>");
}
function dbConnectWrite()
{
    $host="";
    $port=3306;
    $socket="";
    $user="";
    $password="";
    $dbname="";
	//echo("connecting to DB<br/>");
	$dbcon = new mysqli($host, $user, $password, $dbname, $port, $socket)
		or die ('Could not connect to the database server' . mysqli_connect_error());
	//echo("connected to DB<br/>");
	return $dbcon;
}
function dbGetThirdPartiesFromToasterDB()
{
	global $dbcon, $array3pDescriptions;
	if (!$dbcon) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
    	//exit();
	}
	$query = "SELECT * FROM thirdparties";
	//echo("retrieving 3P data from DB<br/>");
	if ($stmt = $dbcon->prepare($query)) {
		$stmt->execute();
		$stmt->bind_result($field1,$field2,$field3,$field4,$field5,$field6);
		while ($stmt->fetch()) {
			//printf("%s, %s\n", $field1, $field2);
			//echo("$field2<br/>");
            if(is_null($field6))
                $field6 = '';
			$arr =  array($field2,$field3,$field4,$field5,$field6);
			$array3pDescriptions[] = $arr;
		}
		$stmt->close();
	}
//echo ("TOASTING... please wait.... <br>Retrieving Third party descriptions from database:<pre>");
//print_r ($array3pDescriptions);
//echo("</pre>data from db:");
}
function dbClose()
{
	global $dbcon;
	//echo("closing DB<br/>");
	$dbcon->close();
}
function IsThisDomainaCDNofTheRootDomain($rootDomain,$testDomain)
{
	global $arrayroothost,$dbusage;
	$res = false;
    //check for specific CDN; e.g. RackCDN, that is not its own network
    if(strpos($testDomain,'.rackcdn.com') !== false)
    {
        //echo("RackCDN found<br/>");
        return 'CDN';
    }
    if(strpos($testDomain,'.edgesuite.net') !== false)
    {
        //echo("RackCDN found<br/>");
        return 'CDN';
    }
	//echo "getting subdomains<br/>";
	foreach($arrayroothost as $k => $v)
	{
		//$rootsubdomain = extract_subdomains($v);
		$rootdomain = extract_domain($v);
		//echo("checking against root domain on: ".$rootdomain."<br/>");
	}
	//foreach($testDomain as $k => $v)
	//{
		//echo("checking: ".$v."<br/>");
		$testsubdomain = extract_subdomains($testDomain);
		$testdomain = extract_domain($testDomain);
		//echo("checking the test domain of: ".$testdomain."<br/>");
	//}
	$firstdot = strpos($rootdomain,'.');
	$maindomain = substr($rootdomain,0,$firstdot);
	if($maindomain == '')
	{
		//echo("error checking for main domain on: ".$maindomain."<br/>");
		return false;
	}
    // SHARDS
	//DB lookup for shard of domain - need to amend for new group datatabase
//	if($dbusage == true)
//	{
	  //echo("Is this a defined shard? : "."checking DB for '". $testDomain."' for record of '". $rootDomain."'<br/>");
//		if (dbLookupDomainShard($rootDomain,$testDomain))
//		{
//			//echo("Found a defined shard : "."checking shard DB for '". $testDomain."' for record of '". $rootDomain."'<br/>");
//			return 'Shard';
//		}
 //   }
//    else
//    {
      //echo("Is this a defined shard? : "."checking file for '". $testDomain."' for record of '". $rootDomain."'<br/>");
        if(FileLookupDomainShard($rootDomain,$testDomain))
        {
          // file lookup
          //echo("Found a defined shard : "."checking shard file for '". $testDomain."' for record of '". $rootDomain."'<br/>");
  	      return 'Shard';
        }
 //   }
	//echo("checking for main domain on: ".$maindomain."<br/>");
	debug("Is this a shard or cdn","comparing ". $testdomain." against ". $maindomain);
	//echo("Is this a shard or cdn? : "."checking '". $testdomain."' for presence of '". $maindomain."'");
	$x = strpos($testdomain,$maindomain);
	//echo "; needle pos = ".$x;  
	if( $x !== false)
	{
		//echo '; main domain name found in object domain = SHARD<br/>';
	}
	if($testdomain == $rootdomain or strpos($testdomain,$maindomain) !== false )
	{
		//echo '; main domain name found in object domain = SHARD<br/>';
	}
	if($testdomain == $rootdomain or strpos($testdomain,$maindomain) !== false )
	{
		debug($testdomain," is a DOMAIN SHARD of the host");
		return 'Shard';
	}
	//echo("; Neither Shard nor CDN = 3P<br/>");
	return $res;
}
function dbLookupDomainShard($domain, $spotshard)
{
	dbConnect();
	$IsAShard = dbLookupShard($domain, $spotshard);
	dbClose();
	return $IsAShard;
}
function readShardsFromFile()
{
	global $arrayShards;
    if(file_exists('toaster_tools/shards.csv'))
	    $arrayShards = file('toaster_tools/shards.csv');
	//echo ("TOASTING... please wait.... <br>Retrieving Shards from file:<pre>");
	//print_r ($arrayShards);
	//echo("</pre>");
}
function FileLookupDomainShard($rootDomain,$testDomain)
{
	global $arrayShards;
    $result = false;
	foreach ($arrayShards as $line_num => $line) {
	    $arr = explode(",",$line);
		$hostdomain = html_entity_decode($arr[0]);
		$sharddomain = html_entity_decode($arr[1]);
		//echo("shard file checking ".$rootDomain." and ".$testDomain."<br/>");
		if(trim($hostdomain) == trim($rootDomain) and trim($sharddomain) == trim($testDomain))
		{
			$result = true;
            //echo(__FUNCTION__ . " found shard in file for ".$rootDomain." and ".$testDomain."<br/>");
			break;
		}
	}
	return $result;
}
function dbLookupShard($domain, $shard)
{
	global $dbcon, $array3pDescriptions;
	$IsAShard = false;
	if (!$dbcon) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
    	//exit();
	}
	$query = 'SELECT count(*) FROM shards '.
	'WHERE DomainShard="'.$shard.'" AND DomainName="'.$domain.'"';
	//echo("Retrieving Shard data from DB: $query <br/>");
	if ($stmt = $dbcon->prepare($query)) {
		$stmt->execute();
		$stmt->bind_result($field1);
		while ($stmt->fetch()) {
			//printf("result: %s\n", $field1);
			//echo("$field2<br/>");
			if ($field1 == 1)
				$IsAShard = true;					
		} // end while
		$stmt->close();
	} //end if
	return $IsAShard;
} // end function dbLookupShard
function lookupIPforDomain($inDomain)
{
    $ipaddress = $inDomain;
    $ipaddress = gethostbyname($inDomain);
    if ($ipaddress == $inDomain or $ipaddress == '92.242.132.15') {
        //echo "No ip address for host, so host "
        //     . "not currently available in DNS and "
        //     . "probably offline for some time<BR>";
        return 'error_ip';
    }
    else {
        //echo "good hostname, ipaddress = $ipaddress<BR>";
    }
	//echo("<br>".$inDomain." ;ip = ".$ipaddress."<br/>");
	return $ipaddress;
}
function lookupLocationforIP($inIP)
{
	global $geoIPLookupMethod,$OS,$apikey_dbip;
	$addr = 'addr='.$inIP;
	$api_key= 'api_key=' . $apikey_dbip;
	$parameters = '?'.$addr . '&' . $api_key;
	$response = '';
//echo (__FUNCTION__ . " - IP lookup using " . $geoIPLookupMethod);
	// METHOD 1 - DBIP - uses API KEY
	if($geoIPLookupMethod == 1)
	{
//		echo ("Using Geo API1 - ".$inIP."<br/>");
		//echo("calling geo api with parms: ".$parameters."<br/>");
		$response = '';
		$response = file_get_contents('http://api.db-ip.com/addrinfo'.$parameters);
		$response = json_decode($response);
		//echo ("response<br/>");
		$addr = $response->address;
		$country = $response->country;
		$stateprov = $response->stateprov;
		$city = $response->city;
		$af = array($city, $stateprov, $country);
        $tc = implode(", ", array_filter($af));
		//echo("1) API response:<pre>");
		//print_r($response);
		//echo("</pre>");
		$lat = '';
		$long = '';
        // switch to next provider if number of queries per day exceeded
        if(isset($response->error))
        {
            if($response->error == "maximum number of queries per day exceeded")
                $geoIPLookupMethod = 2;
        }
	}
	// METHOD 2 - TELIZE
	if( $geoIPLookupMethod == 2)
	//if that fails try 2nd method
	{
//		echo ("Using Geo API2 - ".$inIP."<br/>");
		$parameters = $inIP;
		$response = file_get_contents('http://www.telize.com/geoip/'.$parameters);
		$response = json_decode($response);
//echo("2) API response:<pre>");
//print_r($response);
//echo("</pre>");
		$lat = $response->latitude;
		$long = $response->longitude;
		$country_code = $response->country_code;
		if(isset($response->region))
			$stateprov = $response->region;
		else
			$stateprov = '';
		if(isset($response->city))
			$city = $response->city;
		else
			$city = '';
		if(isset($response->country))
			$country = $response->country;
		else
			$country= '';
        $af = array($city, $stateprov, $country);
        $tc = implode(", ", array_filter($af));
        if($tc = "")
            $tc = $lat.",".$long;
	}
	// METHOD 3 - FreeGeoIP
	if($geoIPLookupMethod == 3)
	//if that fails try 3rd method
	{
		//echo ("Using Geo API3 - ".$inIP."<br/>");
        $apiurl = 'http://freegeoip.net/json/185.119.173.18'; // . $inIP;
        $result = file_get_contents($apiurl);
        //echo("3) API response:<pre>");
        //print_r($result);
        //debug("FreeGeoIP lookup for " . $parameters,implode($response));
        //echo("</pre>");
        $response = json_decode($result);
        //if($response === NULL)
        //    debug("FreeGeoIP lookup", "failed NULL response");
     //else
            debug("FreeGeoIP lookup", $response->latitude . " " . $response->longitude );
		//echo("3) API response:<pre>");
		//print_r($response);
        //debug("FreeGeoIP lookup for " . $inIP,var_dump($response));
		//echo("</pre>");
		$lat = $response->latitude;
		$long = $response->longitude;
		$country_code = $response->country_code;
		$country = $response->country_name;
		if(isset($response->region_name))
			$stateprov = $response->region_name;
		else
			$stateprov = '';
		if(isset($response->city))
			$city = $response->city;
		else
			$city = '';
        $af = array($city, $stateprov, $country);
        $tc = implode(", ", array_filter($af));
        if($tc = "")
            $tc = $lat.",".$long;
	}
// METHOD 4 - HackerTarget
	if($geoIPLookupMethod == 4)
	//try 4th method
	{
//		echo ("Using Geo API4 - ".$inIP."<br/>");
		$parameters = $inIP;
		$response = file_get_contents('http://api.hackertarget.com/geoip/?q='.$parameters);
		//echo("4) API response:<pre>");
		//print_r($response)."<br/>";
        //hex_dump($response);
		//echo("</pre>");
        //echo "count: ". count($response)."<br/>";
        $lines = explode(chr(10),$response);
        //echo "line count: ". count($lines)."<br/>";
//IP Address: 54.148.121.63
//Country: US
//State: Oregon
//City: Boardman
//Latitude: 45.778801
//Longitude: -119.528999
        if($OS == "Windows")
            $names = json_decode(file_get_contents("toaster_tools/countrynames.json"), true);
        else
            $names = json_decode(file_get_contents("toaster_tools/countrynames.json"), true);
        //echo "names ". $names;
        $lat = '';
        $long = '';
        $city = '';
        $country = '';
        $country_code = '';
        $stateprov = '';
        foreach($lines as $line)
        {
          # do something with $line
          //echo("line = ".$line."<br/>");
          $lineparts = explode(':',$line);
          $v = '';
         //echo($lineparts[0]." === ".$lineparts[1]."<br/>");
          @$v = trim($lineparts[1]);
          switch ($lineparts[0])
          {
            case 'IP Address':
                break;
            case "Country":
                $country_code = $v;
                $country = $names[$country_code];
                //echo("country: ".$country."<br/>");
                break;
            case "State":
                $stateprov = $v;
                //echo($stateprov."<br/>");
                if( $stateprov  == 'N/A')
                     $stateprov  = '';
                break;
            case "City":
                $city = $v;
                //echo($city."<br/>");
                if($city == 'N/A')
                    $city = '';
                break;
            case "Latitude":
                $lat = $v;
                //echo($lat."<br/>");
                break;
            case "Longitude":
                $long = $v;
                //echo($long."<br/>");
                break;
          }
        }
        $af = array($city, $stateprov, $country);
        $tc = implode(", ", array_filter($af));
		//if($country == 'N/A' or $country == '')
		//{
		//	$tc = $lat.",".$long;
		//}
        //echo($tc."<br/>");
	}
	//generic latlong lookup for location
	$tc_nospaces = str_replace(" ","+",$tc);
	if(!is_numeric(substr($tc_nospaces,0,1)) and $tc_nospaces != '') // location is not lat long
		list($latlong,$lat,$long) = lookupLatLongForLocation($tc_nospaces);
	else // location is already lat long
		$latlong = $tc;
    // add randomness to lat and long to prevent positioning all markers overlaying each other
    $random1 = ((rand()*(0.04/getrandmax()))-0.02);
    $random2 = ((rand()*(0.04/getrandmax()))-0.02);
    if($lat != 0 and $long != 0)
    {
        $lat += $random1;
        $long += $random2;
    }
/*echo ('IP: '.$inIP.'<br/>');
echo ('lookupLocationforIP loc: '.$tc.'<br/>');
echo ('lookupLocationforIP city: '.$city.'<br/>');
echo ('lookupLocationforIP region: '.$stateprov .'<br/>');
echo ('lookupLocationforIP country: '.$country.'<br/>');
echo ('lookupLocationforIP lat & long: '.$lat.' '.$long.'<br/><br/>');
*/
	return array($tc,$city,$stateprov,$country,$lat,$long);
}
function lookupLatLongForLocation($inAddr)
{
	global $apikey_googlemaps;
    if($inAddr == '')
    {
        error_log("error: lookupLatLongForLocation: '". $inAddr ."'");
        return array('','','');
    }
	//echo("Geocode API call for: ".$inAddr."<br/>");
	$xlat = '';
	$xlong = '';
	$inAddr = str_replace(' ','+',$inAddr);	
	$parameters = "address=".$inAddr . "&key=" . $apikey_googlemaps;
	$response = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?'.$parameters);
	$response = json_decode($response);
	$status = $response->{'status'}[0];
	if($status == "OVER_QUERY_LIMIT")
	{
		echo("GOOGLE API Location Lookup - daily quota exceeded!");
	}
	else
	{
//echo("Geocode API response:<pre>");
//print_r($response);
//echo("</pre>");
		@$xlat = $response->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		@$xlong = $response->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
		//echo("lookupLatLongForLcocation lat: ".$xlat."<br/>");
		//echo("lookupLatLongForLcocation long: ".$xlong."<br/>");
	}
	// replace nulls.
	if (is_null($xlat))
	{
		$xlat = '';
		$xlong = '';
	}
    // add randomness to lat and long to prevent positioning all markers overlaying each other
    $random1 = ((rand()*(0.04/getrandmax()))-0.02);
    $random2 = ((rand()*(0.04/getrandmax()))-0.02);
    if($xlat != 0 and $xlong != 0 )
    {
        $xlat += $random1;
        $xlong += $random2;
    }
	$latlong = $xlat.",".$xlong;
	return array($latlong,$xlat,$xlong);
}
function isthisAddressLatLong($inaddr)
{
	//echo("isthisAddressLatLong call for: ".$inaddr."<br/>");
	$boolreturn = false;
	$ll = explode(',',$inaddr);
    $plat = '';
	$plon = '';
	if(count($ll) == 2)
	{
		$plat = $ll[0];
		$plon = $ll[1];
		$blatnum = is_numeric($plat);
		$blonnum = is_numeric($plon);
		//echo("pot lat: ".$plat." = ".$blatnum ."<br/>");
		//echo("pot lon: ".$plon." = ".$blonnum."<br/>");
		if($blatnum and $blonnum)
			$boolreturn = true;
	}
	return array($boolreturn,$plat,$plon);
}
function lookupLocationForLatLong($lat,$long)
{
	global $apikey_googlemaps;
	//echo("Geocode API  lookuplocation call for: ".$lat. ", ".$long."<br/>");
	$latlng = $lat.",".$long;	
	$parameters = "latlng=".$latlng . "&key=" .$apikey_googlemaps;
	$response = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?'.$parameters);
	$response = json_decode($response);
	//echo("Geocode API response:<pre>");
	//print_r($response);
	//echo("</pre>");
	$addr = $response->{'results'}[0]->{'formatted_address'};
	//echo("lookupLcocationForLatLong addr: ".$addr."<br/>");
	return array($addr);
}
function getDomainMarkers($domtype)
{
	global $arrayDomains;
	$dm = '';
	foreach($arrayDomains as $v)
	{	
		if($v['Domain Type'] == $domtype)
		{
			$loc = $v['Location'];
            $lat= $v['Latitude'];
            $long = $v['Longitude'];
			$edgeloc = $v['Edge Loc'];
//			if($edgeloc != '')
            if($lat != "" and $long != "")
				$dm.= "%7C".$lat . "," . $long;
//			else
//				$dm.= "%7C".$loc;
		}
	}
	//echo $dm;
	return $dm;
};
function lookupReverseIP($inDomain)
{
	global $reverseIPResults;
	$parameters = '?url='.$inDomain. "&output=json";
	//echo("calling reverse lookup api with parms: ".$parameters."<br/>");
	$response = '';
	$response = file_get_contents('http://reverseip.logontube.com/'.$parameters);
	$reverseIPResults = json_decode($response);
	//echo("ReverseIP response:<pre>");
	//print_r($reverseIPResults);
	//echo("</pre>");
	//echo ("response<br/>");
	return true;
}
function NSlookup($DomainOrIP)
{
	exec('nslookup -timeout=20 '.$DomainOrIP,$res);
	//echo "NS Lookup for ".$DomainOrIP."<br/>";
	//echo "<pre>";
	//print_r($res);
	//echo("</pre>");
	$edgename = '';
	$edgeaddress = '';
	$boolFound = false;
	// extract address from results
	foreach($res as $k => $v)
	{	
		if($v != '' and $k > 1)	
		{
			//echo("k = ".$k. "; v = " .$v."<br/>");
			$splitv = explode(":",$v);
			//echo("0 = ".$splitv[0]. "; 1 = " .$splitv[1]."<br/>");
			if($splitv[0] == 'Name')
			{
				$edgename= trim($splitv[1]);
			}
			if($splitv[0] == 'Addresses' or $splitv[0] == 'Address')
			{
				$edgeaddress = trim($splitv[1]);
				if (isset($splitv[2]))
				{
					// found IPv6 address
					//echo ("ip6 found<br/>");
					$edgeaddress = trim($v);
					$edgeaddress = str_replace('Addresses:','',$edgeaddress);
					$edgeaddress = str_replace('Address:','',$edgeaddress);
					$edgeaddress = trim($edgeaddress);
				}  
				break;
			}
		}
	}
	//echo ("edge name = '".$edgename."'<br/>");
	//echo ("edge address = '".$edgeaddress."'<br/>");
	return array($edgename,$edgeaddress);
}
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
if((!$lon2 and !$lat2) or ($lat1 == 0 and $lat2 ==0))
	return 0;
    $lon1 = (float)$lon1;
    $lon2 = (float)$lon2;
    $lat1 = (float)$lat1;
    $lat2 = (float)$lat2;
//echo(__FUNCTION__ .": lat1:".$lat1.' lon1:'.$lon1.'; lat2:'.$lat2.' lon2:'.$lon2.'<br/>');
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);
  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}
?>

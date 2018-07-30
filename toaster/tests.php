<?php


function addTestResult($testid, $testruleset, $testname, $testresult)
{
    global $arrayOfTests;


    if (!in_array($testid, $arrayOfTests))
    {
        debug("adding test to list", $testname);
        //echo("<br/>adding test to list: ". $testname ." = ". $testresult);


        $arr = array(
            "id" => $testid,
            "Ruleset" => $testruleset,
            "Test" => $testname,
            "Result" => $testresult
        );
        $arrayOfTests[] = $arr;

    }
    else
    {
        debug("existing test found", $testname);
    }

}


function addUpdateRules($ruleid, $ruleset, $conformance, $optimisation,$testresult)
{
    global $arrayOfRules;


    if (!myInArray($arrayOfRules,$ruleid,"id"))
    {
        //debug("adding rule to list", $ruleset);
        //echo("<br/>adding rule to list: ". $testruleset);


        $arr = array(
            "id" => $ruleid,
            "Ruleset" => $ruleset,
            "State" => $conformance,
            "Optimsation" => $optimisation,
            "Result" => $testresult
        );
        $arrayOfRules[] = $arr;

    }
    else
    {

        $arr = array(
            "id" => $ruleid,
            "Ruleset" => $ruleset,
            "State" => $conformance,
            "Optimsation" => $optimisation,
            "Result" => $testresult
        );
        $arrayOfRules[$ruleid - 1] = $arr;

        //debug("existing rule found", $testname);
    }



}

function myInArray($array, $value, $key){
    //loop through the array
    foreach ($array as $val) {
        //if $val is an array cal myInArray again with $val as array input
        if(is_array($val)){
            if(myInArray($val,$value,$key))
                return true;
        }
        //else check if the given key has $value as value
        else{
            if($array[$key]==$value)
                return true;
        }
    }
    return false;
}

function addInitialRules(){

    addUpdateRules('1', 'Make Fewer HTTP Requests', '', '', '');
    addUpdateRules('2', 'Use a Content Delivery Network', '', '', '');
    addUpdateRules('3', 'Add an Expires Header', '', '', '');
    addUpdateRules('4', 'Compress Components', '', '', '');
    addUpdateRules('5', 'Put Stylesheets at the Top', '', '', '');
    addUpdateRules('6', 'Put Scripts at the Bottom', '', '', '');
    addUpdateRules('7', 'Avoid CSS Expressions', '', '', '');
    addUpdateRules('8', 'Make JavaScript and CSS External', '', '', '');
    addUpdateRules('9', 'Reduce DNS Lookups', '', '', '');
    addUpdateRules('10', 'Minify JavaScript', '', '', '');
    addUpdateRules('11', 'Avoid Redirects', '', '', '');
    addUpdateRules('12', 'Remove Duplicate Scripts', '', '', '');
    addUpdateRules('13', 'Configure ETags', '', '', '');
    addUpdateRules('14', 'Make AJAX Cacheable', '', '', '');
    addUpdateRules('15', 'Split the Initial Payload', '', '', '');
    addUpdateRules('16', 'Load Scripts Without Blocking', '', '', '');
    addUpdateRules('17', 'Couple Asynchronous Scripts', '', '', '');
    addUpdateRules('18', 'Position Inline Scripts', '', '', '');
    addUpdateRules('19', 'Optimise Images', '', '', '');
    addUpdateRules('20', 'Shard Dominant Domains', '', '', '');
}


function secondsToDays($ss) {
    if(is_numeric($ss))
    {
        $d = floor($ss/86400);

        // round up if required
        if($d * 86400 !=$ss)
            $d += 1;
    }
    else
    {
        $d = $ss;
    }
    return $d;
}

function updateFileStats()
{
    debug("UPDATE FILE STATS","");
	global $objcountcss,
           $objcountscript,
           $objcountimg,
           $arrayPageObjects,
           $arrayDomains,
           $headcss,
           $headjs,
           $bodycss,
           $bodyjs,
           $boolRootRedirect,
           
           $HTTPCompressionType,
           $redir_type,
           $redirect_count,
           $boolHTTPCompressRoot,
           $noofIframes,
           $noof404Errors,
           $arrayCacheAnalysis,
           $objectChartData,
           $pagespeedcount,
           $arrayOfCSSSelectors,
           $objcountfont,
           $arrayTagManagers,
           $noofHTML5MediaElements,
           $maxcsschaindepth,
           $amplience_dynamic_images_found,
           $amplience_dynamic_images_strip,
           $amplience_dynamic_images_stripnone,
           $amplience_dynamic_images_chroma,
           $boolHTTP2Root;

    // do stats
    addStatToFileListAnalysis($noofIframes,"IFrames","Used","info");

    if($noof404Errors == 0)
        addStatToFileListAnalysis($noof404Errors,"404 Errors","Count","pass");
    else
        addStatToFileListAnalysis($noof404Errors,"404 Errors","Count","fail");

    addStatToFileAnalysis("No. of CSS Files",$objcountcss);
    addStatToFileAnalysis("No. of JS Files",$objcountscript);
    addStatToFileAnalysis("No. of Images",$objcountimg);

    // newlist style
    if($objcountcss == 1)
        addStatToFileListAnalysis($objcountcss,"CSS File","Downloaded");
    else
        addStatToFileListAnalysis($objcountcss,"CSS Files","Downloaded");
    if($objcountscript == 1)
        addStatToFileListAnalysis($objcountscript,"JS File","Downloaded");
    else
        addStatToFileListAnalysis($objcountscript,"JS Files","Downloaded");

    if($objcountimg == 1)
        addStatToFileListAnalysis($objcountimg,"Image","Downloaded");
    else
        addStatToFileListAnalysis($objcountimg,"Images","Downloaded");

    if($objcountfont == 1)
        addStatToFileListAnalysis($objcountfont,"Font","Downloaded");
    else
        addStatToFileListAnalysis($objcountfont,"Fonts","Downloaded");
    if($pagespeedcount > 0)
        addStatToFileListAnalysis($pagespeedcount,"PageSpeed","Optimised Objects");

    //if($redirect_count == 1)
    //    addStatToFileListAnalysis($redirect_count,"Redirection","On Root Object","warn");
    //else
    //    if($redirect_count > 1)
    //    addStatToFileListAnalysis($redirect_count,"Redirections","On Root Object","fail");


    if($noofHTML5MediaElements == 1)
        addStatToFileListAnalysis($noofHTML5MediaElements,"HTML5 Media Element","");
    else
        if($noofHTML5MediaElements > 0)
            addStatToFileListAnalysis($noofHTML5MediaElements,"HTML5 Media Elements","");

    // get total number of objects across all domains
    $noofobjects = count($arrayPageObjects);

    // count number of occurrences of a value in the object table
    $cdncount = 0;
    $cdndomains = array();
    $domainCount = 0;
    $shardcount = 0;
    $sharddomains = array();
    $dom3P = array();
    $jscount = 0;
    $csscount = 0;
    $dom3PCount = 0;
    $noofobjsDomainPrimary = 0;
    $noofobjsDomainCDN = 0;
    $noofobjsDomainShard = 0;
    $noofobjsDomain3P = 0;
    $noofrequests = 0;
    $noofobjB64  = 0;
    $noofobjData = 0;
    $noofredirs = 0;
    $noofShards = 0;
    $noof3Ps = 0;
    $noofimageswithmetadata = 0;
    $sumimagemetadata = 0;
    $noofjpegsHQ = 0;
    $noofunminJS = 0;
    $namedCDN = '';
    $bEtagsUsed = false;
    $listofExpiresPeriods = array();
    $noofExpires = 0;
    $noofmaxageExpires = 0;
    $network = '';
    $noofCSSImports = 0;

    foreach ($arrayPageObjects as $key => $valuearray)
    {

        //echo("key:".$key." = value: " .$value. "<br/>");

        //print_r($valuearray);

        // get domain stats
        $objurl = $valuearray["Object source"];
        $domref = $valuearray["Domain ref"];
        $domain = $valuearray["Domain"];
        $objtype = $valuearray["Object type"];
        $etags = $valuearray["hdrs_etag"];
        $expires = $valuearray["hdrs_expires"];
        $maxageseconds = $valuearray["hdrs_cachecontrolMaxAge"];
        if($maxageseconds)
            $maxagedays = secondsToDays($maxageseconds);
        else
            $maxagedays = 0;
        $lastmod = $valuearray["hdrs_lastmodifieddate"];
        $bytestransmitted = $valuearray["Content length transmitted"];
        $mimetype = $valuearray["Mime type"];
        $response_datetime = $valuearray["response_datetime"];

        //get host for url
        list($hostdomain, $p) = getDomainHostFromURL($objurl ,false,"updateFileStats");
        //echo("domain returned<br/>");
        //lookup domain entry for host domain, get network
        $keyfound = lookupDomain($hostdomain);
        if($keyfound != -1)
            $network = $arrayDomains[$keyfound]['Network'];
        //if($network != '')
        //echo("domain network found: key: ".$keyfound." network: " .$network. "<br/>");

        //echo("checking domain references<br/>");
        if($domref == "Primary")
        {
            // check etags
            if($etags != '')
            {
                $bEtagsUsed = true;
                //echo('etags used<br/>');
            }
            else
            {
                //echo('etags not used<br/>');
            }

            if($maxagedays > 0)
            {
                //echo('maxage set: '.$maxageseconds.' s = '.$maxagedays.' d<br/>');
                $noofmaxageExpires =$noofmaxageExpires + 1;

                $int = $maxagedays;

                if (!in_array($int, $listofExpiresPeriods))
                    {
                        $listofExpiresPeriods[] = $int;
                    }
            }

            if($expires != '' and $expires != '0' and $expires != '-1')
            {
                //echo('expires set<br/>');

                $noofExpires = $noofExpires + 1;
                //echo($objurl.' '.$expires.'<br/>');

                if ($expires != 0)
                {

                      // calculate difference between dates
                      $datetime2 = new DateTime($expires);
                      $datetime2->add(new DateInterval('P1D'));
                      $datetime1 = new DateTime($response_datetime);
                      $interval = $datetime1->diff($datetime2);
                      $int = $interval->format('%R%a');
                      //echo("noof days = " . $int . "<br/>");

                    if (!in_array($int, $listofExpiresPeriods))
                    {
                        $listofExpiresPeriods[] = $int;
                    }

                } // end if expires != 0

            } // end if expires is set
          //else
          //echo('expires not set<br/>');

        } // end if primary domain ref


        if($domref == 'Shard')
        {
            $shardcount += 1;
            $sharddomains[] = $domain;

            // check etags
            if($etags != '')
            {
                $bEtagsUsed = true;
            }


           if($expires != '' and $expires != '0' and $expires != '-1')
            {
                //echo('expires set<br/>');

                $noofExpires = $noofExpires +1;
                //echo($objurl.' '.$expires.'<br/>');

                if ($expires != 0)
                {

                      // calculate difference between dates
                      $datetime2 = new DateTime($expires);
                      $datetime2->add(new DateInterval('P1D'));
                      $datetime1 = new DateTime($response_datetime);
                      $interval = $datetime1->diff($datetime2);
                      $int = $interval->format('%R%a');
                      //echo("noof days = " . $int . "<br/>");

                    if (!in_array($int, $listofExpiresPeriods))
                    {
                        $listofExpiresPeriods[] = $int;
                    }

                } // end if expires != 0

            } // end if expires is set
        } // end if shard

        if(strpos($expires,'access') !== false)
                {
                    //echo "access found in expires header; old value = " . $expires."<br/>";
                    $accessint = explode(' ',$expires);
                    $accessnumber = $accessint[2];
                    $accessperiod = $accessint[3];

                    $expiresnew = date_create($response_datetime);
                    date_add($expiresnew, date_interval_create_from_date_string($accessnumber . " " . $accessperiod));
                    //echo "access found in expires header: $accessnumber : $accessperiod . new value = " . $expiresnew."<br/>";
                    $expires = $expiresnew->format('Y-m-d H:i:s');
                }

        // cache period analysis
        if($domref == "Primary" or $domref == 'Shard' or $domref == 'CDN')
        {
            // add to cacheanalysis array
            if((($maxagedays > 0) or ($expires != '' and $expires != '0' and $expires != '-1')) and $lastmod != '')
            {

                // last modified date - calculate difference
                $datetime2 = new DateTime($lastmod);
                $datetime2->add(new DateInterval('P1D'));
                $datetime1 = new DateTime();
                $interval = $datetime1->diff($datetime2);
                $intLastMod = $interval->format('%r%a');

                $intExpires = 0;



                if($expires != '' and $expires != '0' and $expires != '-1')
                {
                    // expiry date - calculate difference
                    try{
                    $datetime2 = new DateTime($expires);}
                    catch (Exception $e) {error_log( $e->getMessage() . " - " . $objurl . " - expires = ".$expires->format('Y-m-d H:i:s'));}

                    $datetime2->add(new DateInterval('P1D'));

                    $datetime1 = new DateTime($response_datetime);
                    $interval = $datetime1->diff($datetime2);
                    $intExpires = $interval->format('%r%a');
                }

                $diffdaysMethod = "Expires";
                $diffdays = $intExpires;

                if($maxagedays > 0)
                {
                    $diffdaysMethod = "MaxAge";
                    $diffdays = $maxagedays;
                }


                // only add to arrat if maxage or expores > 0
                if($diffdays > 0)
                {
                  $arr = array(
                      "ObjectURL" => $objurl,
                      "MimeType" => $mimetype,
                      "BytesTransmitted" => $bytestransmitted,
                      "LastModDate" => $lastmod,
                      "LastModDateDiffDays" => $intLastMod,
                      "ExpiresDate" => $expires,
                      "ExpiresDateDiffDays" => $intExpires,
                      "MaxAgeDays" => $maxagedays,
                      "DiffDaysMethod" => $diffdaysMethod,
                      "DiffDays" => $diffdays
                  );
                  // add to array
                  $arrayCacheAnalysis[] = $arr;
                }
            }

        }

        if($domref == '3P')
        {
            $dom3PCount += 1;
            $dom3P[] = $domain;
        }

        //echo("domain refs<br/>");
        //echo($domref.": ".$network."<br/>");
        // get count of objects from domains
        switch ($domref)
        {

            case 'Primary':
                //echo ("primary: ". $network."<br/>");
                if($network != '')
                {
                    $noofobjsDomainCDN  += 1;
                    $namedCDN = $network;
                    //echo ("stats primary - named cdn: ". $namedCDN."<br/>");
                    $noofobjsDomainPrimary += 1;
                }
                else
                    $noofobjsDomainPrimary += 1;
                  $noofrequests += 1;
                break;

            case 'Shard':
                if($network != '')
                {
                    //echo ("stats shard - named cdn b4: ". $namedCDN."<br/>");
                    if($namedCDN == '')
                    {
                        $namedCDN = $network;
                    //echo ("stats shard - named cdn: ". $namedCDN."<br/>");
                    }
                    $noofobjsDomainShard += 1;
                }
                else
                    $noofobjsDomainShard += 1;
                $noofrequests += 1;
                break;

            case 'CDN':
                if($network != '')
                {
                    $noofobjsDomainCDN  += 1;
                    //echo ("stats cdn - named cdn b4: ". $namedCDN."<br/>");
                    if($namedCDN == '')
                    {
                        $namedCDN = $network;
                    //echo ("stats cdn - named cdn: ". $namedCDN."<br/>");
                    }
                }
                else
                    $noofobjsDomainCDN += 1;
                $noofrequests += 1;
                break;

            case '3P':
                $noofobjsDomain3P += 1;
                $noofrequests += 1;
                break;

            case 'Embedded':
                $noofobjData +=1;
                break;

            case 'Base64':
                $noofobjB64 +=1;
                break;

            case 'redirection':
                $noofredirs +=1;
                $noofrequests += 1;
                break;

        }

        //echo("stats<br/>");
        // get filetype stats
        $otype = $valuearray["Object type"];
        $cssref = $valuearray["CSS ref"];

        if($otype == 'JavaScript')
        {
            if($domref != '3P')
                $jscount += 1; // only count primary, shards and CDNs
        }
        if($otype == 'StyleSheet')
        {
            if($domref != '3P')
                $csscount += 1; // only count primary, shards and CDNs

            // count css with imports
            if($cssref == '@Import')
            {
                $noofCSSImports = $noofCSSImports + 1;
            }
        }


        if($objtype == 'Image' and $domref !='3P')
        {
            $md = $valuearray["Metadata bytes"];
            if($md > 0)
            {
                $noofimageswithmetadata += 1;
                $sumimagemetadata += $md;
            }
            $jpegopt = $valuearray["Est. quality"];
            if((int)$jpegopt > 80)
                $noofjpegsHQ += 1;

        }

        if($objtype == 'JavaScript' and $domref !='3P')
        {
            $unminssize = $valuearray["Content size minified uncompressed"];
            $minsize = $valuearray["Content size minified compressed"];

            if($unminssize > ($minsize * 1.2))
            {
                $noofunminJS += 1;
            }
        }


    } // end for each object

    // sort array of cacheheaderanalysis
    array_sort_by_column($arrayCacheAnalysis, 'MimeType');

    // sort expires period ascending
    sort($listofExpiresPeriods, SORT_NUMERIC);
    //echo('List of expiry periods<pre>');
    //print_r($listofExpiresPeriods);
    //echo('</pre>');
    $sortedExpiresPeriods = implode(';',$listofExpiresPeriods);
    // get max expiry period
    $c = count($listofExpiresPeriods);
    if($c > 0)
    {
    //echo("c: ".$c);
      $minExpiresPeriod = $listofExpiresPeriods[0];
      $maxExpiresPeriod = $listofExpiresPeriods[$c-1];
      //echo("max expiry period: ".$maxExpiresPeriod);
      $maxExpiresDays = intval($maxExpiresPeriod);
      $minExpiresDays = intval($minExpiresPeriod);
      //echo("max expiry days: ".$maxExpiresDays);
    }


    foreach ($arrayDomains as $key => $valuearray)
    {

        //echo("key:".$key." = value: " .$value. "<br/>");

        //print_r($valuearray);

        // get domain stats
        $domref = $valuearray["Domain Type"];

        switch($domref)
        {
            case '3P':
                $noof3Ps +=1;
                $domainCount++;
                break;
            case "Shard":
                $noofShards +=1;
                break;
            default:
                $domainCount++;
                break;
        }

    }



//echo ("saving stats<br/>");

//echo ("saving stat. noofcss imports: ".$noofCSSImports ."<br/>");
    if($noofCSSImports > 0)
    {
        addStatToFileListAnalysis($noofCSSImports,"@Import","External StyleSheets","fail");
        //addStatToFileListAnalysis($maxcsschaindepth,"@Import Depth","External StyleSheets","fail");
    }

    // stats
    if($noofShards == 0 and ($noofobjsDomainPrimary > 36))
    {
        addStatToFileListAnalysis($noofShards,"Shard","Used","fail");
    }
    else
        if($noofShards >= 1 and $noofShards < 3)
            addStatToFileListAnalysis($noofShards,"Shards","Used","pass");
        else
            if($noofShards >3)
                addStatToFileListAnalysis($noofShards,"Shards","Used","fail");
            else
               addStatToFileListAnalysis($noofShards,"Shards","Used","info");

    $shardresults = array_unique($sharddomains);
    $noofshards = count($shardresults);
    $pct = number_format($shardcount / $noofrequests * 100,0);
    if($noofShards >= 1  and $shardcount / $noofrequests > 0.2)
    {
        addStatToFileListAnalysis($noofobjsDomainShard,"Objects","From Shards","pass");
    }
    else
        if($noofShards == 0 and ($noofobjsDomainPrimary > 36))
            addStatToFileListAnalysis($noofobjsDomainShard,"Objects","From Shards","fail");
        else
            addStatToFileListAnalysis($noofobjsDomainShard,"Objects","From Shards","info");


    //echo ("stats 1 - named cdn: ". $namedCDN."<br/>");
    if($namedCDN != '')
    {
        //echo ("stats 2 - named cdn: ". $namedCDN."<br/>");
        addStatToFileListAnalysis($namedCDN,"","CDN","pass");
    }
    else
        if ($noofobjsDomainPrimary > 36)
            addStatToFileListAnalysis("","","No CDN","info");
        else
            addStatToFileListAnalysis("","","No CDN","info");

    //else
    //echo ("stats 3 - unnamed cdn<br/>");
    addStatToFileListAnalysis($noofobjsDomainCDN,"Objects","From CDN");



    if($noof3Ps == 1)
        addStatToFileListAnalysis($noof3Ps,"Third Party","");
    else
        addStatToFileListAnalysis($noof3Ps,"Third Party","Domains");
    addStatToFileListAnalysis($noofobjsDomain3P,"Objects","From 3rd Parties");


    $vendorsarray = array ();
    foreach ($arrayTagManagers as $key => $value)
    {

        //echo("key:".$key." = value: " .$value. "<br/>");

        //print_r($valuearray);

        // get domain stats
        $tagman = $value["Tagman"];
        $vendor = $value["Vendor"];
        $soucefile = $value["File"];



        if (!in_array($vendor,$vendorsarray))
        {
          $vendorsarray[] = $vendor;
            addStatToFileListAnalysis($tagman,$vendor,"Tag Manager");
        }
    }



    // rules


    // rule 1
    if($noofrequests <= 36)
    {
        $rec = 'The number of requests is fewer than the recommended maximum of 36.';
        $result = 'pass';
    }
    else
    {
        $rec = 'Reduce the number of requests. This page exceeds the recommended maximum of 36.';
        $result = 'fail';
    }
    $stats = 'This page downloads '. $noofrequests .' objects in total.<br/>';
    if($noofobjB64 > 0)
        $stats .= '* This includes '.$noofobjB64.' embedded objects as Base64-encoded data files<br/><br/>';
    if($noofobjData > 0)
        $stats .= '* This includes '.$noofobjData.' embedded objects as data files<br/><br/>';
    $stats .= '* '.$noofobjsDomainPrimary.' objects are served from Primary domain<br/>';
    $stats .= '* '.$noofobjsDomainShard.' objects are served from Shards of the Primary domain<br/>';
    $stats .= '* '.$noofobjsDomain3P.' objects are served from Third Party domains<br/>';
    addUpdateRules('1', 'Make Fewer HTTP Requests', $stats, $rec, $result);


    // add rules
    // Rule 2
    $noofDomainRequests = $noofobjsDomainPrimary + $noofobjsDomainShard;
    $pct = number_format($noofobjsDomainCDN / $noofDomainRequests * 100,0);
    if($noofobjsDomainCDN / $noofDomainRequests > 0.2)
    {
        $rec = 'The number of requests from a CDN is > 20%.';
        $result = 'pass';
    }
    else
    {
        if($noofDomainRequests >= 20)
        {
            if($pct == 0)
            {
                $rec = 'Consider using a CDN for some of the objects. No domain objects are served from a CDN.';
                $result = 'fail';
            }
            else
            {
                $rec = 'Consider using a CDN for more of the objects. Only '.$pct.'% of domain objects are served from a CDN.';
                $result = 'fail';
            }
        }
        else
        {
            $rec = 'There are an insufficient number of domain objects to make use of a CDN';
            $result = 'n/a';
        }
    }
    if ($namedCDN !='')
        addUpdateRules('2', 'Use a Content Delivery Network', 'This page downloads '. $noofobjsDomainCDN .' objects from a CDN of the primary domain or shard.', $rec, $result);
    else
        addUpdateRules('2', 'Use a Content Delivery Network', 'This page does not use a CDN for the primary domain or shard.', $rec, $result);


    // rule 3 expires  headers
    if($noofExpires > 0)
    {
        $rec = 'Consider using more far-future Expires headers';
        if($minExpiresDays <0)
        {
            $rec = 'Consider setting far-future Expires headers, some expiry dates are in the past.';
            $result = 'fail';
        }
        else
            if($maxExpiresDays <28)
            {
                $rec = 'Consider setting some longer far-future Expires headers.';
                $result = 'warning';
            }
            else
            {
                $rec = 'Consider using longer far-future Expires headers.';
                $result = 'pass';
            }
        addUpdateRules('3', 'Add an Expires Header', 'This page sets '.$noofExpires.' Expires headers with periods of:'.$sortedExpiresPeriods . " days", $rec, $result);
    }
    else
    {
        $rec = 'Consider using far future Expires headers';
        $result = 'fail';
        addUpdateRules('3', 'Add an Expires Header', 'This page does not employ Expires headers.', $rec, $result);
    }




    // Rule 4
    if($boolHTTPCompressRoot == true)
    {
        $rec = '';
        $cond = ' ';
        $result = 'pass';
        if($HTTPCompressionType == "br")
            $HTTPCompressionType = "Brotli";
        addUpdateRules('4', 'Compress Components', 'The root object is'.$cond.'served compressed using ' . $HTTPCompressionType . '.', $rec, $result);
    }
    else
    {
        $rec = 'Enable server HTTP compression for HTML and other text mime-types.';
        $cond = ' not ';
        $result = 'fail';
        addUpdateRules('4', 'Compress Components', 'The root object is'.$cond.'served compressed.', $rec, $result);
    }



    // Rule 5
    if($bodycss == 0)
    {
        $rec = '';
        $result = 'pass';
    }
    else
    {
        $rec = 'Move the links to refer to external Stylesheets to the HEAD section.';
        $result = 'fail';
    }
    addUpdateRules('5', 'Put Stylesheets at the top', 'At least '. $bodycss .' stylesheets are referenced in the body', $rec, $result);


    // Rule 6
    if($headjs == 0)
    {
        $rec = '';
        $result = 'pass';
    }
    else
    {
        $rec = 'Move the script tags to refer to external JavaScript files to the BODY section.';
        $result = 'fail';
    }
    addUpdateRules('6', 'Put Scripts at the Bottom', 'At least '. $headjs .' scripts are referenced in the HEAD of the HTML source document (not injected by JavaScript after DOM load.)', $rec, $result);


    // Rule 8

    if($jscount == 0 and $csscount ==0 and $noofobjsDomainPrimary <= 5)
    {
        $rec = '';
        $result = 'pass';
    }
    else
    {
        if($jscount <= 2 and $csscount <= 2)
        {
            $rec = 'Move inlined JavaScript and CSS statements to external files where possible.';
            $result = 'fail';
        }
        else
        {
            $rec = 'Move inlined JavaScript and CSS statements to external files where possible.';
            $result = 'warning';
        }

    }
    addUpdateRules('8', 'Make JavaScript and CSS External', $jscount.' external JavaScript and '.$csscount.' external CSS files are referenced (from non-third party domains).', $rec, $result);


    // rules 9


    if($domainCount > 20)
    {
        $rec = 'Reduce the number of domains to reduce the number of DNS lookups.';
        $result = 'fail';
    }
    else
        if($domainCount > 10)
        {
            $rec = 'Reduce the number of domains to reduce the number of DNS lookups.';
            $result = 'warning';
        }
        else
        {
            $rec = 'Continue to monitor third parties to retain a low number of DNS lookups.';
            $result = 'pass';
        }


    addUpdateRules('9', 'Reduce DNS Lookups', $domainCount . ' domains are referenced, of which ' .$noof3Ps . ' domains are from third party domains.', $rec, $result);





    // rule 10
    if($noofunminJS == 0)
    {
        $minjs = 'The JavaScript files appear to be minified.';
        $rec = '';
        $result = 'pass';
    }
    else
    {
        $minjs = $noofunminJS . ' JavaScript files do not appear to be minified';
        $rec = 'Ensure that JavaScirpt files served from the domain are minified.';
        $result = 'fail';
    }
    addUpdateRules('10', 'Minify JavaScript', $minjs, $rec, $result);



    // rule 11
    if($boolRootRedirect == false)
    {
        $rec = '';
        $result = 'pass';
    }
    else
    {
        $rec = 'Ensure that the root object does not perform a redirection.';
        $result = 'fail';
    }
    addUpdateRules('11', 'Avoid Redirects', 'The root object performs '.$redirect_count.' redirect(s) ('.$redir_type.').', $rec, $result);



    // rule 13 etags
    if($bEtagsUsed == false)
    {
        $rec = '';
        $result = 'pass';
        addUpdateRules('13', 'Configure ETags', 'ETags are not used on Primary and Shards', $rec, $result);
    }
    else
    {
        $rec = 'Ensure that eTags are properly configured.';
        $result = 'warning';
        addUpdateRules('13', 'Configure ETags', 'ETags are used on Primary and Shards (config not checked)', $rec, $result);
    }







    // rule 19
    if($noofimageswithmetadata == 0 and $noofjpegsHQ == 0)
    {
        $optim = 'Images are quite well optimised.';
        $rec = 'Review images further to ensure optimisation benefits are achieved.';
        $result = 'pass';
    }
    else
    {
        $optim = '';
        if($noofimageswithmetadata > 0)
            $optim = $noofimageswithmetadata . ' images have metadata totalling '. $sumimagemetadata . ' bytes.<br/>';
        if($noofjpegsHQ > 0)
            $optim .= $noofjpegsHQ . ' JPEG images exceed an estimated quality level of 80% and could be further optimised.';

        $rec = 'Optimise JPEG, PNG, GIF and WEBP images to reduce file size.';
        $result = 'fail';
    }
    addUpdateRules('19', 'Optimise Images', $optim, $rec, $result);

    // Rule 20
    $shardresults = array_unique($sharddomains);
    $noofshards = count($shardresults);
    $pct = number_format($shardcount / $noofrequests * 100,0);
    if($shardcount / $noofrequests > 0.2)
    {
        $rec = 'The number of requests from a Shard is > 20%.';
        $result = 'pass';
    }
    else
    {
        $rec = 'Consider using a Shard for some of the objects. Only '.$pct.'% of objects are served from a CDN.';
        $result = 'fail';
    }
    addUpdateRules('20', 'Shard Dominant Domains', 'This page references '.$shardcount.' objects from '.$noofshards.' Shards in addition to the primary domain.', $rec, $result);

    //Sorted tabular Data based on my type
    $objectTabularData = $arrayCacheAnalysis;
    /*Function (within a function) is used to sort a 2D array based on its element (ie. MimeType in this case)
      and returns sorted array*/
    function sortMimeType($a, $b) {
        return strcmp($a['MimeType'], $b['MimeType'])<0?1:-1;
    }
    usort($objectTabularData, 'sortMimeType');

    //Chart Data
    $objectChartData = objectMimeTypeAnalysisIn4Chart($arrayCacheAnalysis);





    // css analysis -ids and classes
    //echo(__FILE__ ." ". __FUNCTION__.' '. __LINE__ ." CSSAnalysis: ". $fn.'<pre>');
    //print_r($arrayOfCSSSelectors);
    //echo('</pre>');
    $cssTotalEx = 0;
    $cssidsEx = 0;
    $cssclassesEx = 0;
    $csselementsEx = 0;
    $cssUsedEx = 0;
    $cssTotalIn = 0;
    $cssidsIn = 0;
    $cssclassesIn = 0;
    $csselementsIn = 0;
    $cssUsedIn = 0;
    //echo(__FILE__ ." ". __FUNCTION__.' '. __LINE__ .' CSSAnalysis<pre>');
    foreach($arrayOfCSSSelectors as $item => $value)
    {

        if($value['CSS filename'] != "inline")
        {

          $cssTotalEx +=1;
          //echo $item . " ". $value['CSS filename']. " ". $value['Selector type']. " ". $value['Selector name']. " ". $value['Used in HTML']."<br/>";
          if($value['Used in HTML'] == 'yes')
            $cssUsedEx +=1;
          if($value['Selector type'] == 'id')
            $cssidsEx +=1;
          else
            if($value['Selector type'] == 'class')
                $cssclassesEx +=1;
            else // elementS
                $csselementsEx +=1;
        }
        else
            {

              $cssTotalIn +=1;
              //echo $item . " ". $value['CSS filename']. " ". $value['Selector type']. " ". $value['Selector name']. " ". $value['Used in HTML']."<br/>";
              if($value['Used in HTML'] == 'yes')
                $cssUsedIn +=1;
              if($value['Selector type'] == 'id')
                $cssidsIn +=1;
              else
                if($value['Selector type'] == 'class')
                    $cssclassesIn +=1;
                else // elementS
                    $csselementsIn +=1;
            }

       //"CSS filename" => $cssfile,
       //"Selector type" => $type,
       //"Selector name" => $selector,
       //"Used in HTML" => $usedInHTML

    }
    //echo('</pre>');
    // Internal
    if($cssTotalIn > 0)
    {
        addStatToFileListAnalysis($cssTotalIn,"CSS Selectors","Defined as Inline Styles","warn");
        $cssPctIn = round($cssUsedIn/$cssTotalIn * 100,0);

        if($cssPctIn < 20)
        {
            addStatToFileListAnalysis($cssPctIn.'%',"CSS Selectors","Used in Inline Styles","fail");
        }
        else
            {
                if($cssPctIn >= 60)
                {
                    addStatToFileListAnalysis($cssPctIn.'%',"CSS Selectors","Used Inline Styles","pass");
                }
                else
                    addStatToFileListAnalysis($cssPctIn.'%',"CSS Selectors","Used Inline Styles","warn");
            }
    }
    else
        addStatToFileListAnalysis($cssTotalIn,"CSS Selectors","Defined as Inline Styles","pass");

    // External
    if($cssTotalEx > 0)
    {
        addStatToFileListAnalysis($cssTotalEx,"CSS Selectors","Defined in External StyleSheets","pass");
        $cssPctEx = round($cssUsedEx/$cssTotalEx * 100,0);

        if($cssPctEx < 20)
        {
            addStatToFileListAnalysis($cssPctEx.'%',"CSS Selectors","Used in External StyleSheets","fail");
        }
        else
            {
                if($cssPctEx >= 60)
                {
                    addStatToFileListAnalysis($cssPctEx.'%',"CSS Selectors","Used in External StyleSheets","pass");
                }
                else
                    addStatToFileListAnalysis($cssPctEx.'%',"CSS Selectors","Used in External StyleSheets","warn");
            }
    }

    // AMPLIENCE
    if($amplience_dynamic_images_found == true)
    {
         $ampstatus = "info";
        $ampstrip = "Metadata stripped (" . strval($amplience_dynamic_images_strip) ." of " . strval($amplience_dynamic_images_stripnone + $amplience_dynamic_images_strip) . ")";
        if($amplience_dynamic_images_strip == 0)
            $ampstatus = "fail";
        if($amplience_dynamic_images_stripnone == 0)
            $ampstatus = "pass";
        if($amplience_dynamic_images_strip > 0 and  $amplience_dynamic_images_stripnone> 0)
            $ampstatus = "warn";

 /*       if($amplience_dynamic_images_chroma > 0)
        {
            $ampchroma = "Chroma changed: (" . strval($amplience_dynamic_images_chroma) .")";
        }
        else
        {
            $ampchroma = '';
        }
        $amptext = implode(",",array($ampstrip,$ampchroma));
*/
        addStatToFileListAnalysis("Amplience","Dynamic Imaging",$ampstrip,$ampstatus);
    }


}

/**
 * Formatter Function returns chart's data to push into HighChart
 * @param $arrayCached
 * @return mixed
 */
function objectMimeTypeAnalysisIn4Chart($arrayCached)
{
    $categories = array();
    $categoriesMimeType = array();
    $chartData = $modifiedBefore = $expireAfter = array();
    $xMin = $xMax = 0;
    $green = '#ADF7C5';
    $red = '#F7ADBD';
    $black = '#ABA9AA';
    foreach ($arrayCached as $objs) {
        $categories[] = getFileNameOnly($objs['ObjectURL']);
		$categoriesMimeType[] = $objs['MimeType'];
        $dayDiff = $objs['DiffDays'] - abs($objs['LastModDateDiffDays']);

        $modifiedBefore[] = (Object) array('y'=>intval($objs['LastModDateDiffDays']), 'color'=> getColorIntensity('black', (abs($dayDiff)%10)));
        $expireAfter[] =
            $dayDiff > 0 ?
                (Object) array('y'=>intval($objs['DiffDays']), 'color'=> getColorIntensity('green', (abs($dayDiff)%10))) :
                (Object) array('y'=>intval($objs['DiffDays']), 'color'=> getColorIntensity('red', (abs($dayDiff)%10)));

        $xMin = intval($objs['LastModDateDiffDays']) < $xMin ? intval($objs['LastModDateDiffDays']) : $xMin;
        $xMax = intval($objs['DiffDays']) > $xMax ? intval($objs['DiffDays']) : $xMax;
    }

    $chartData['xAxisMin'] = $xMin;
    $chartData['xAxisMax'] = $xMax;
    $chartData['categories'] = $categories;
	$chartData['categoriesMimeType'] = $categoriesMimeType;
    $chartData['pastValues'] = $modifiedBefore;
    $chartData['exipryValues'] = $expireAfter;

    return $chartData;
}

/**
 * Function returns the file name only by ignoring the queryString and full url
 * @param $url
 * @return mixed
 */
function getFileNameOnly($url)
{
    $filName = explode('/', $url);
    $fileName = $filName[(sizeof($filName)-1)];
    $finalName = preg_replace('/\?.*/', '', $fileName);
    return $finalName;
}

/**
 * Function should return color code based on different $intensity value
 * @param $color
 * @param $intensity
 * @return mixed
 */
function getColorIntensity($color, $intensity) {
    $red = array('#FFE3E3', '#F7C3C3', '#FAB1B1',  '#FAA0A0', '#FA8989', '#FC6A6A', '#FC4949', '#FC3A3A', '#FC1E1E', '#FA0202');
    $green = array('#D7FCD7', '#C2FCC2', '#AFFAAF', '#9AFC9A', '#87FA87', '#6FFC6F', '#54F754', '#3EFA3E', '#23FA23', '#07EB07');
    $black = array('#BDBFBD', '#A7A8A7', '#919191', '#797A79', '#686968', '#515251', '#3F403F', '#2B2B2B', '#232423', '#080808');

    if ($color == 'red') {
        return $red[$intensity];
    } else if ($color == 'green') {
        return $green[$intensity];
    } else {
        return $black[$intensity];
    }
}

/**
 * Function to sort multi-dimension array
 * @param $arr
 * @param $col
 * @param int $dir
 */
function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}
?>

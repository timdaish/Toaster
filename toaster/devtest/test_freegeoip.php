<?php



       $apiurl = 'http://freegeoip.net/json/81.152.49.225';

$result = file_get_contents($apiurl);


        //echo("3) API response:<pre>");
        print_r($result);
        //debug("FreeGeoIP lookup for " . $parameters,implode($response));
        //echo("</pre>");

            $response = json_decode($result);

        echo ("FreeGeoIP lookup: ".  $response->ip . " " . $response->city);
?>

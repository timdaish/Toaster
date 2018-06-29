<?php
echo "<h1>PHP CURL TEST</h1>";
if(_iscurl())
{
	echo "CURL enabled<br/>";
    // Get curl version array
    $version = curl_version();

    // These are the bitfields that can be used
    // to check for features in the curl build
    $bitfields = Array(
                'CURL_VERSION_IPV6',
                'CURL_VERSION_KERBEROS4',
                'CURL_VERSION_SSL',
                'CURL_VERSION_LIBZ'
                );

    echo "version: " . $version['version'] .  "<br/>";
    echo "features: " . $version["features"].  "<br/>";

    foreach($bitfields as $feature)
    {
        echo $feature . ($version['features'] & constant($feature) ? ' matches<br/>' : ' does not match<br/>');
        echo PHP_EOL;
    }


    if ($version["features"] & CURL_VERSION_HTTP2 !== 0)
    {
    // HTTP/2 support
        echo "HTTP/2 capable<br/>";
    }
    else
    {
        echo "not HTTP/2 capable<br/>";
    }

}
else
	echo "CURL disabled<br/>";

//////////////////////////////////////

    function _iscurl(){
    if(function_exists('curl_version'))

      return true;

    else
      return false;
    }
?>
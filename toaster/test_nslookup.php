<?php
header("Content-type:text/plain");

if ( $safe_mode = ini_get( 'safe_mode' ) && strtolower( $safe_mode ) != 'off' )
{
    echo 'Safe Mode is Disabled';
}
else
    echo 'Safe Mode is Enabled<br/>';


if ( in_array( 'exec', array_map( 'trim', explode( ',', ini_get( 'disable_functions' ) ) ) ) )
{
    echo 'exec is Disabled';
}
else
echo 'exec is Enabled<br/>';

$DomainOrIP = "www.google-analytics.com";
$strNslookup  = 'nslookup -timeout=20 '.$DomainOrIP;
	exec($strNslookup,$res);

		echo "NS Lookup for ".$DomainOrIP.PHP_EOL;
		echo "cmd = " . $strNslookup . PHP_EOL;
		echo "<pre>";
		print_r($res);
        echo("</pre>").PHP_EOL;
if(!$res)
{

$hostip  = gethostbyname($DomainOrIP);
echo "getbyhostname for ".$DomainOrIP.PHP_EOL;
echo $hostip .PHP_EOL;

$edge = gethostbyaddr($hostip );
echo "gethostbyaddr for ".$hostip.PHP_EOL;
echo $edge.PHP_EOL;

$edgeip  = gethostbyname($edge);
echo "getbyhostname for ".$edge.PHP_EOL;
echo $edgeip .PHP_EOL;
}
?>
<?php
require '..\GeoLite2-City\geoip2.phar';
header("Content-Type: text/plain");
use GeoIp2\Database\Reader;

function getIPLoc($ip)
{
// This creates the Reader object, which should be reused across
// lookups.
$reader = new Reader('..\GeoLite2-City\GeoLite2-City.mmdb');

$record = $reader->city($ip);

return array($ip, $record->country->name,$record->country->isoCode,
 $record->mostSpecificSubdivision->name, $record->mostSpecificSubdivision->isoCode,
  $record->city->name,$record->postal->code,
  $record->location->latitude,$record->location->longitude );
}
?>
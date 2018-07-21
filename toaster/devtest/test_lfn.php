<?php
$serverName = 'http://'.$_SERVER['SERVER_NAME'];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    //echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
    //echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
include 'domain_url_functions.php';
include 'ps_functions.php';
$sn='https://pbs.twimg.com/media/DExhI2ZXgAAGzX_.jpg:small';

$local = convertAbsoluteURLtoLocalFileName($sn);

echo $local;
?>

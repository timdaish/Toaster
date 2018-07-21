<!DOCTYPE html>

<html>

<head>
  <title>PhantomJS test</title>
</head>

<body>

<?php
$debuglog = "/var/sites/w/webpagetoaster.com/subdomains/toast/debug.txt";
file_put_contents($debuglog, "DEBUG LOG started" . PHP_EOL);
ini_set("log_errors", 1);
ini_set("error_log", $debuglog);
$urlforbrowserengine = "www.bluebella.com/collections/lingerie";
$height = "600";
$width = "800";
$uastr ="Chrome_Desktop";
$filepath_domainsavedir = "/var/sites/w/webpagetoaster.com/subdomains/toast/Chrome_Desktop/" . $urlforbrowserengine;
$imgname = $filepath_domainsavedir."/_screencapture_". $uastr .".png";
$uar = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36";
$browserengineoutput = tempnam($filepath_domainsavedir."/",'out') . ".txt";
error_log(is_writable($filepath_domainsavedir) ? "Temp dir is writable" : "Temp dir is not writable");
error_log("filepath_domainsavedir: ".$filepath_domainsavedir);
error_log("browserengineoutput: ".$browserengineoutput);
$username = '';
$password = '';


exec('whoami',$who);
error_log("whoami: ".implode($who));

$cmd = '/var/www/toaster/lnx_tools/phantomjs2.1 --ignore-ssl-errors=true --ssl-protocol=tlsv1 /var/www/toaster/js/netsniff.js '. $urlforbrowserengine . " " . $height . " " . $width . " " . $imgname . " \"" . $uar ."\"" . " ".  $browserengineoutput." ".$username. " ". $password;
error_log($cmd);


exec($cmd,$res); //responses & sniff
error_log(implode($res));

echo "<br/><pre>";
var_dump($res);
echo "</pre>";

?>

</body>
</html>

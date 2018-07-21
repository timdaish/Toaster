<!DOCTYPE html>

<html>

<head>
  <title>Hello!</title>
</head>

<body>

<?php

	$adnparts = explode(".","205.bm-nginx-loadbalancer.mgmt.ams1.adnexus.net");



			echo("exploding: ".$edgename."<pre>");

			print_r($adnparts);

			echo("</pre>");



			$cnt = count($adnparts);

			$string = substr($adnparts[$cnt-3],0,3); // adjust to number of parts in array



			$adnIATACode = preg_replace("/[^a-zA-Z]/", "", $string);

            echo "count:" . $cnt  . " code = " . $adnIATACode;


?>

</body>
</html>

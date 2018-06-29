<?php

	//echo("Geocode API  lookuplocation call for: ".$lat. ", ".$long."<br/>");
	$latlng = "51.4964,-0.1224";
	$parameters = "latlng=".$latlng . "&amp;key=AIzaSyCSP9nBZ1aRvIZRc4tQbXznyrISL7Gt6d8";
	$response = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?'.$parameters);
    
	//$response = json_decode($response);

echo("Geocode API response:<pre>");
print_r($response);
echo("</pre>");


?>
<?php

    if (isset($_GET['iatacode'])) {
        $code = $_GET['iatacode'];
    }else{
        // Fallback behaviour goes here
        return;
        }
    
    		$apikey = "e7d866fc-242e-4d2d-9627-e76574f69b72"; // IATACODES API
        	$urlparm = "https://iatacodes.org/api/v6/mixed?api_key=". $apikey ."&method[cities][code]=".$code;





            if(get_http_response_code($urlparm) != "200"){

                echo "airportcode lookup service: error for " . $code . "<br/>";

                $latlong = '';

                $lat = '';

                $long = '';

                $airportlocation = $code;

            }else{

                $response = file_get_contents($urlparm);

            	$res = json_decode($response);

                $airportlocation = $res->cities;

				var_dump($res);
            }
//echo("airport code lookup - remote: ".$code. " ". $airportlocation);



function get_http_response_code($url) {

    @$headers = get_headers($url);

    return substr($headers[0], 9, 3);

}

?>
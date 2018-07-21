<?php
// lookup airport code

if (isset($_GET['iatacode'])) {
    $searchcode = $_GET['iatacode'];
}else{
    // Fallback behaviour goes here
    return;
}

$str = file_get_contents("win_tools\airportscitiesstates.json");

$found = false;
$json = json_decode($str);
foreach($json as $item)
{
    if(strToLower($item->code) == strToLower($searchcode))
    {
//        echo $item->iata.": ".$item->city.", ".$item->country."<br/>";
        echo("<pre>");
        var_dump( $item);
        echo("</pre>");
        $found = true;
    

      $airportlocation = $item->city.", ".$item->state .", ".$item->country;
      echo "airport location = " .$airportlocation;

          break;
    }
}
if($found == false)
{
    echo "not found";
}
else
{
    
}

?>

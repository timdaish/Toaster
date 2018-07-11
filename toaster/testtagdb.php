<?php

$domain = 'thebrighttag.com';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, 'https://ncctagdb.herokuapp.com/2/find?host='.$domain);
$result = curl_exec($ch);
curl_close($ch);

// check if result is empty
if(count(json_decode($result,1))==0)
{
    echo ("The domain '" . $domain  . "' is not recognised");
    exit;
}

// declode json object
$objjson = json_decode($result);
// convert json to php array
$objarray = json_decode($result,true);




echo ("Third party data from database as JSON<br/>");
echo ($result);

echo ("<br/>Third party data from database as array:<pre>");
print_r ($objarray);
echo("</pre>");


echo ("Third party data<br/>");
echo ("<br/>from json:&nbsp;&nbsp;" . $objjson->domain . " " . $objjson->company->name . " " . $objjson->product->name);
echo ("<br/>from array:&nbsp;" . $objarray['domain'] . " " . $objarray['company']['name'] . " " . $objarray['product']['name']);
?>

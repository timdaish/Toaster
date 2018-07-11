<?php

$textcookies = file_get_contents('/toast/cookies.txt');
ConvertPJSCookiesToNetscape($textcookies);

function ConvertPJSCookiesToNetscape($PJScookies) {

echo ("original:<br/>".$PJScookies."<br/>");

$lines = explode('\\0\\0\\0',$PJScookies);

echo("lines:<pre>");
print_r($lines);
echo("</pre>");

};





?>

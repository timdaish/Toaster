<?php
$response = file_get_contents("https://www.webpagetest.org/runtest.php?url=http%3A%2F%2Fwww.bbc.co.uk&f=json");
print_r($response);
?>

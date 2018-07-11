<!DOCTYPE html>

<html>

<head>
  <title>SlimerJS test</title>
</head>

<body>

<?php

// 0.10.0pre

// 0.10.1
$cmd = 'win_tools\slimerjs-0.10.1\slimerjs.bat js\netsniff_raw.js http://www.bbc.co.uk';


//$cmd = 'win_tools\test_bf.bat'; // works as a test

exec($cmd,$res);

echo "<br/><pre>";
var_dump($res);
echo "</pre>";

?>

</body>
</html>

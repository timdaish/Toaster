<html>
<head>

</head>
<body>
<?php
echo "<h1>Testing of PhantomJS via PHP shell command</h1>";
$data = shell_exec('toaster_tools\phantomjs.exe js\netlog.js http://www.bbc.co.uk'); //responses
?>

<img src="tmp/test.png"></img>

<?php
$json_string = json_encode($data, JSON_PRETTY_PRINT);

if(strpos($json_string,"X-Squid-Error") != false)
	echo '<h2 style="color:red;">SQUID ERROR DETECTED</h2>';

$obj = json_decode($json_string);
//echo "Response<pre>";
// var_dump ($obj);
// echo "</pre>";
?>


</body>
</html>
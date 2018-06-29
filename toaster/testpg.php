<html>
<head>
</head>
<body>
<?php
echo "<h1>testing file_get_contents via PHP</h1>";
$file = file_get_contents('http://www.bbc.co.uk');
var_dump($file);
?>
</body>
</html>
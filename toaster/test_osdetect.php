<!DOCTYPE html>

<html>

<head>
  <title>Hello!</title>
</head>

<body>

<?php
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    echo 'This is a server using Windows! '. $windows."<br/>";
} else {
    echo 'This is a server not using Windows!'."<br/>";
}
echo php_uname()."<br/>";
echo PHP_OS."<br/>";
echo php_uname('s')."<br/>";
?>

</body>
</html>

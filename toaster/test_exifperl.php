<?php
    $drv = substr(__DIR__,0,1);
    $filepath_basesavedir= $drv.":\\toast\\";
    $perlbasedir = $drv.":\\xampp\\perl\bin\\";
exec( $perlbasedir. 'perl toaster_tools\ExifToolPerl\exiftool.pl c:\temp\dd_xmas.jpg',$res);


$o = implode($res);
echo ('exiftool perl output: ' . $o."<br/>");
?>
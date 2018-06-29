<?php
    include 'ttfInfo.class.php';
    $fontinfo = getFontInfo('c:\temp\slick.ttf');
    echo '<pre>';
    print_r($fontinfo);
    echo '</pre>';

    echo("name = ".$fontinfo[1]);
    echo("name = ".$fontinfo[2]);
    echo("name = ".$fontinfo[3]);
    echo("name = ".$fontinfo[4]);
?>
<?php
    $sdip = gethostbyname("www.sterlingfurniture.co.uk");
    $sddomain = gethostbyaddr($sdip);
    print "IP: $sdip\n";
    print "Domain: $sddomain\n";

    echo PHP_EOL;
    $msips = gethostbynamel("www.sterlingfurniture.co.uk");
    var_dump($msips);
?>
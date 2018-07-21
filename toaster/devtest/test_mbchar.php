<?php
//print_r( mb_list_encodings());
echo "before<br/>";
$base = mb_strrchr( "http://www.bbc.co.uk", '/', true);

if($base === FALSE)
    echo"false<br/>";
else
    echo "'".$base."'<br/>";
echo "after<br/>";
?>

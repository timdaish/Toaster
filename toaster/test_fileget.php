<?php



$homepage = file_get_contents('http://www.bbc.co.uk');

$g = gzdecodeA($homepage);
echo ("<pre>".$g."</pre>");


function gzdecodeA($data){
   $g=tempnam('/tmp','ff'); @file_put_contents($g,$data); ob_start(); readgzfile($g); $d=ob_get_clean(); unlink($g); return $d;
   }

?>
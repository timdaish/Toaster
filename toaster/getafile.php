<?php
if( $_REQUEST["name"] )
{
   $url = $_REQUEST['name'];
   $data = file_get_contents( $url);
   //$data = str_replace("<","&lt;",$data);
   //$data = str_replace(">","&gt;",$data);
    echo ($data);
}
?>

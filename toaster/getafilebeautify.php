<?php
$serverName = 'http://'.$_SERVER['SERVER_NAME'];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    //echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
    //echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
if( $_REQUEST["name"] )
{
   $url = $_REQUEST['name'];
   $type = $_REQUEST['type'];

   $res = array();
   switch($type)
   {
      case "JavaScript":
       if($OS == "Windows")
       {
            exec('win_tools\jsbeautify ' . $url ,$res);
            $data = implode(PHP_EOL,$res);
       }
        else
        {
//echo $url;
            $data = file_get_contents("$url");
        }
       
       break;

      case "StyleSheet":
       if($OS == "Windows")
       {
            exec('win_tools\cssbeautify ' . $url ,$res);
            $data = implode(PHP_EOL,$res);
       }
        else
            $data = file_get_contents($url);
       
       break;

       default:
          $data = file_get_contents( $url);
    }
   //$data = str_replace("<","&lt;",$data);
   //$data = str_replace(">","&gt;",$data);
    echo ($data);
}
?>

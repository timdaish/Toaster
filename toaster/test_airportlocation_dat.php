<?php
// lookup airport code

if (isset($_GET['iatacode'])) {
    $searchcode = $_GET['iatacode'];
}else{
    // Fallback behaviour goes here
    return;
}

$found = false;

$filename = file("toaster_tools\airports.dat");

// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($filename as $line_num => $line) {
    //echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
    $line = str_replace('"','',$line);
    $data = explode(',', $line);
    //var_dump($data);
    if(strToLower($data[4]) == strToLower($searchcode))
        {
            $found == true;
            echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
           
            for ($x = 0; $x < count($data); $x++)
            {
                echo ($x . " = " . $data[$x]. PHP_EOL);

                
            }
            break;
            $arr = array ($searchcode, $data[2], $data[3], $data[6], $data[7]);
        }
}
//return $arr;



// if($found == false)
// {
//     echo "not found";
// }

?>
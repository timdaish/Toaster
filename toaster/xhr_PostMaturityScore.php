<?php

//Step1
 $conn= new mysqli('10.90.65.17','tim','64RPcvvUU72qe5bh','maturityscore');
 
 /* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

/* change character set to utf8 */
if (!$conn->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
    exit();
} else {
    //printf("Current character set: %s\n", $mysqli->character_set_name());
}


   //query for insert data into tables
   $url = utf8_decode($_REQUEST['url']);
   $score = $_REQUEST['score'];


   $sql = "INSERT INTO `simplescore` 
         (`url`,`score`)
         VALUES
         ('$url','$score')
         ON DUPLICATE KEY UPDATE
         `url`='$url',`score`='$score'";

      if ($conn->query($sql) === TRUE) {
         //echo "New record created or updated successfully";
         $res = true;
      } else {
          //echo "Error: " . $sql . "<br>" . $conn->error;
          $res = false;
      }


   $conn->close();
   echo $res;
?>

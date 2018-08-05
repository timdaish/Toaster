<?php
$servername = "10.169.0.175";
$username = "webpaget1_stats";
$password = "!rv3uIFBFRKw3Vd3i";
$database = "webpaget1_stats";

// Create connection
$conn = new mysqli($servername, $username, $password,$database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//echo "Connected successfully";
?>
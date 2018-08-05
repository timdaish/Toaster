<?php
$servername = "localhost";
$username = "toasteradmin";
$password = "lolitasLoisAndAva";
$database = "webpaget1_stats";

// Create connection
$conn = new mysqli($servername, $username, $password,$database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//echo "Connected successfully";
?>
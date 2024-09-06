<?php
$servername = "sql211.infinityfree.com";
$username = "if0_37234904"; 
$password = "J18Yz5aVe0FU";
$dbname = "if0_37234904_champions";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

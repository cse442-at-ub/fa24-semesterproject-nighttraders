<?php
include_once('config.php');

// Create connection
$conn = new mysqli(SERVER_NAME, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

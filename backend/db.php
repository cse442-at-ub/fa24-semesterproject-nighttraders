<?php

$servername = "localhost";
$username = "dlincogn";
$password = "50503958";
$dbname = "cse442_2024_fall_team_e_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connection successful";
?>

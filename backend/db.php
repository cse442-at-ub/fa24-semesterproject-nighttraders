<?php
// backend-stocksAPI/db.php or backend-monte/db.php

=======
include_once('config.php');

// Create connection
$conn = new mysqli(SERVER_NAME, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Set character set to utf8mb4 for proper encoding
if (!$conn->set_charset("utf8mb4")) {
    die(json_encode(['error' => "Error loading character set utf8mb4: " . $conn->error]));
}
?>

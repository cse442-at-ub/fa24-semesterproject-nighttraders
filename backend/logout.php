<?php
// backend/logout.php

session_start();
session_destroy();

// header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Origin: https://se-prod.cse.buffalo.edu');
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

echo json_encode(['code' => 200, 'message' => 'Logged out successfully']);
?>

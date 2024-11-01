<?php
// backend-stocksAPI/logout.php or backend-monte/logout.php
include_once('config.php'); // Add this line first


session_start();
session_destroy();

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

echo json_encode(['code' => 200, 'message' => 'Logged out successfully']);
?>

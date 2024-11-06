<?php
// backend-stocksAPI/dashboard.php or backend-monte/dashboard.php
include_once('config.php'); // Add this line first


header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Start the session
session_start();

// Check if the user is logged in
if (isset($_SESSION["user"])) {
    // Return the user's information as JSON
    echo json_encode([
        "username" => $_SESSION["user"]
    ]);
} else {
    // If not logged in, return an error message
    http_response_code(401); // 401 Unauthorized
    echo json_encode([
        "error" => "User not logged in."
    ]);
}
?>

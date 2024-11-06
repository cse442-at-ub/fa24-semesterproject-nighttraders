<?php
// backend/getAllStocks.php
include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

// Start session to check if user is logged in
session_start();

if (!isset($_SESSION["user"])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

// Fetch all stocks from the database
$sql = "SELECT Symbol, Name, Exchange, Sector, Industry FROM stockInfo";
$result = $conn->query($sql);

if ($result) {
    $stocks = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['stocks' => $stocks]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch stocks from database.']);
}
?>


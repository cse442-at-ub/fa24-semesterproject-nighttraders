<?php
// backend/getStockPrice.php
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

$symbol = $_GET['symbol'] ?? null;

if (!$symbol) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Symbol is required']);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT AnalystTargetPrice FROM stockInfo WHERE Symbol = ?");
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Server error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $symbol);
$stmt->execute();
$result = $stmt->get_result();
$stockData = $result->fetch_assoc();
$stmt->close();

if ($stockData && isset($stockData['AnalystTargetPrice'])) {
    $price = floatval($stockData['AnalystTargetPrice']);
    echo json_encode(['price' => $price]);
} else {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Stock not found or price unavailable']);
}
?>

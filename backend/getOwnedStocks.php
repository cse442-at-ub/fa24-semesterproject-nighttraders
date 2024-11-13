<?php
// backend/getOwnedStocks.php
include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['user'];

// Fetch current OwnedStocks for the user
$stmt = $conn->prepare('SELECT OwnedStocks FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'User not found']);
    exit;
}

$ownedStocks = $user['OwnedStocks'] ? json_decode($user['OwnedStocks'], true) : [];

echo json_encode(['OwnedStocks' => $ownedStocks]);
?>

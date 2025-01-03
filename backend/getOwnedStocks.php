<?php
session_start();

// backend/getOwnedStocks.php
include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');


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

// Ensure OwnedStocks is an array of objects with 'symbol', 'quantity', and 'price'
$formattedStocks = [];
foreach ($ownedStocks as $stock) {
    if (is_array($stock) && isset($stock['symbol']) && isset($stock['quantity']) && isset($stock['price'])) {
        $formattedStocks[] = [
            'symbol' => $stock['symbol'],
            'quantity' => (int)$stock['quantity'],
            'price' => (int)$stock['price']
        ];
    }
}

echo json_encode(['OwnedStocks' => $formattedStocks]);
?>

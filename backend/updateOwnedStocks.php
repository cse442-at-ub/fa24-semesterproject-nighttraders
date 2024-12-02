<?php
// backend/updateOwnedStocks.php
include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['user'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['symbol']) || !isset($input['quantity']) || !isset($input['price'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$symbol = $input['symbol'];
$quantity = (int)$input['quantity'];
$price = (int)$input['price'];

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

// Update the quantity for the symbol
$found = false;
foreach ($ownedStocks as &$stock) {
    if ($stock['symbol'] === $symbol) {
        $stock['quantity'] = $quantity;
        $stock['price'] = $price;
        $found = true;
        break;
    }
}

// if (!$found) {
//     // If stock not in list, add it
//     $ownedStocks[] = ['symbol' => $symbol, 'quantity' => $quantity];
// }
if (!$found && $quantity > 0) {
    // If stock not in list and quantity is greater than zero, add it
    $ownedStocks[] = ['symbol' => $symbol, 'quantity' => $quantity, 'price' => $price];
}

// **Remove this section to allow quantity = 0 stocks to remain**
// // Remove stocks with zero quantity
// $ownedStocks = array_filter($ownedStocks, function($stock) {
//     return $stock['quantity'] > 0;
// });

// Update the OwnedStocks field in the database
$ownedStocksJson = json_encode($ownedStocks);
$stmt = $conn->prepare('UPDATE users SET OwnedStocks = ? WHERE username = ?');
$stmt->bind_param('ss', $ownedStocksJson, $username);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'OwnedStocks' => $ownedStocks]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to update OwnedStocks']);
}
$stmt->close();
$conn->close();
?>
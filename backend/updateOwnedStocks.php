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
if (!$input || !isset($input['symbol']) || !isset($input['action'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$symbol = $input['symbol'];
$action = $input['action']; // 'add' or 'remove'

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

if ($action === 'add') {
    // Add the stock to OwnedStocks if not already present
    if (!in_array($symbol, $ownedStocks)) {
        $ownedStocks[] = $symbol;
    }
} elseif ($action === 'remove') {
    // Remove the stock from OwnedStocks
    $ownedStocks = array_filter($ownedStocks, function($s) use ($symbol) {
        return $s !== $symbol;
    });
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

// Update the OwnedStocks field in the database
$ownedStocksJson = json_encode(array_values($ownedStocks)); // Re-index array
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

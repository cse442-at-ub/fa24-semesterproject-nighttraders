<?php
// backend/deactivateAccount.php
session_start();

include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');



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

// Fetch the user from the database
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
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

$userId = $user['id'];

// Delete the user's data from the database
$stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);

if ($stmt->execute()) {
    session_destroy(); // Log the user out
    echo json_encode(['success' => true, 'message' => 'Account deactivated successfully']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to deactivate account']);
}

$stmt->close();
$conn->close();
?>

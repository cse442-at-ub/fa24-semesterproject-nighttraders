<?php
include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

// Start session to check if user is logged in
session_start();

function updateUserSettings() {
    global $conn;

    $username = $_SESSION['user'];

    // get user ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $userId = $result->fetch_assoc()['id'];

    $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");

    // get new values from POST data
    $newUsername = htmlspecialchars($_POST['username'] ?? '');
    $newEmail = htmlspecialchars($_POST['email'] ?? '');
    $newPassword = htmlspecialchars($_POST['password'] ?? '');

    // check if email is already registered
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $newEmail);
    $stmt->execute();
    $Result = $stmt->get_result();
    $rowCount = $Result->num_rows;
    if ($rowCount > 0) {
        die(json_encode(['error' => 'Your updated email is already registered']));
    }

    // check if username is already registered
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $newUsername);
    $stmt->execute();
    $Result = $stmt->get_result();
    $rowCount = $Result->num_rows;
    if ($rowCount > 0) {
        die(json_encode(['error' => 'Your updated username is already registered']));
    }

    // hash new password if there is one
    if ($newPassword) {
        $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    } else {
        $newHashedPassword = null;
    }

    $updateStmt->bind_param("sssi", $newUsername, $newEmail, $newHashedPassword, $userId);

    if ($updateStmt->execute()) {
        return json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    } else {
        return json_encode(['error' => true, 'message' => 'Failed to update settings']);
    }
}
echo updateUserSettings();

?>

<?php

include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

// Start session to check if user is logged in
session_start();

function getCurrentUserSettings() {
    global $conn;

    $username = $_SESSION['user'];

    // get entry from users based on username
    $stmt = $conn->prepare("SELECT username, email, birthdate FROM users WHERE username = ?");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $userSettings = $result->fetch_assoc();
    $stmt->close();

    return json_encode($userSettings);
}

echo getCurrentUserSettings();

?>
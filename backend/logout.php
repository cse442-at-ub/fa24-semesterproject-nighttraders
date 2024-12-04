<?php
include_once('config.php');

session_start();

// Unset all session variables
$_SESSION = array();

// If you want to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

// Respond with a success message
echo json_encode(['code' => 200, 'message' => 'Logged out successfully']);
?>

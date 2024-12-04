<?php
// backend/login.php

session_start();

include_once('db.php');
include_once('config.php');

header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Enforce HTTPS
if ((!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != 'on'))) {
    header('Location: ' . 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
    exit();
}

// If user is already logged in, destroy the existing session
if (isset($_SESSION["user"])) {
    // Unset all session variables
    $_SESSION = array();

    // Get session cookie parameters
    $params = session_get_cookie_params();

    // Delete the session cookie
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        '', // Ensure domain matches config.php; set to empty string if domain was removed
        $params["secure"],
        $params["httponly"]
    );

    // Destroy the session
    session_destroy();

    // Start a new session for the new login
    session_start();
}

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST["login"])) {
        die(json_encode(['error' => 'Invalid request']));
    }

    $email = htmlspecialchars($_POST["email"] ?? '');
    $password = htmlspecialchars($_POST["password"] ?? '');
    
    if (empty($email)) {
        die(json_encode(['error' => 'Email is required']));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['error' => 'Invalid email format']));
    }

    // Prepare and execute statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die(json_encode(["code" => 500, "error" => "Server error"]));
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $Result = $stmt->get_result();
    $user = $Result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user["password"])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION["user"] = $user["username"];       
            die(json_encode(["code" => 200, "message" => "Login successful"]));
        } else {
            die(json_encode(["code" => 401, "error" => "Invalid credentials"]));
        }
    } else {
        die(json_encode(["code" => 401, "error" => "Invalid credentials"]));
    }
} else {
    die(json_encode(['error' => 'Invalid request method']));
}
?>

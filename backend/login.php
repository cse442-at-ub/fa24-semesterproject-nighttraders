<?php
// backend-stocksAPI/login.php or backend-monte/login.php

session_start();
include_once('db.php');
include_once('config.php'); // Add this line first


header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Prevent accidental exposure of middleware configuration
/*
if ((!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != 'on'))) {
    header('Location: ' . 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
}
*/

// Check if user is already logged in
if (isset($_SESSION["user"])) {
    die(json_encode(["code" => 200, "message" => "Already logged in"]));
}

// Handle login if the POST parameter 'login' is set
if (isset($_POST["login"])) {
    // Validate input
    if (empty($_POST["email"]) || empty($_POST["password"])) {
        die(json_encode(['code' => 400, 'error' => 'Email and password are required']));
    }

    $email = $_POST["email"];
    $password = $_POST["password"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['code' => 400, 'error' => 'Invalid email format']));
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT username, password FROM users WHERE email = ?");
    if (!$stmt) {
        die(json_encode(["code" => 500, "error" => "Server error: " . $conn->error]));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $Result = $stmt->get_result();
    $user = $Result->fetch_assoc();

    if ($user) {
        // Verify the password using password_verify
        if (password_verify($password, $user["password"])) {
            $_SESSION["user"] = $user["username"];
            die(json_encode(["code" => 200, "message" => "Login successful"]));
        } else {
            die(json_encode(["code" => 401, "error" => "Invalid credentials"]));
        }
    } else {
        die(json_encode(["code" => 401, "error" => "Invalid credentials"]));
    }
}
?>

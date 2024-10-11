<?php
session_start();
include_once('db.php');

header('Access-Control-Allow-Origin: http://localhost:3000');
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if(
    (!isset($_SERVER['HTTPS'])||
    ($_SERVER['HTTPS']!='on')))
    {
    header('Location: '.
    'https://'.
    $_SERVER['SERVER_NAME'].
    $_SERVER['PHP_SELF']);
    }

// if user is already logged in
if (isset($_SESSION["user"])) {
    // redirect to dashboard
    // header("Location: .php");
    }

if (isset($_POST["login"])) {
    if (!isset($_POST["email"]) || empty($_POST["email"])) {
        die(json_encode(['error' => 'Email is required']));
    }
    $email = $_POST["email"];
    $password = $_POST["password"];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['error' => 'Invalid email format']));
    }
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if ($user) {
        if (password_verify($password, $user["password"])) {
            $_SESSION["user"] = $user["username"];       
            // redirect to dashboard
            // header("Location: .php");
            die(json_encode(["code" => 200, "message" => "Login successful"]));
        }else{
            die(json_encode(["code" => 401, "error" => "Invalid credentials"]));
        }
    }else{
        die(json_encode(["code" => 401, "error" => "Invalid credentials"]));
    }
}
?>

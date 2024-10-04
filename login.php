<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include_once('db.php');

session_start();

if (isset($_SESSION["user"])) {
    // redirect to dashboard
    // header("Location: .php");
    }

if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $hashPassword = password_hash($password,PASSWORD_BCRYPT);
    if ($user) {
        if (password_verify($hashPassword, $user["password"])) {
            session_start();
            $_SESSION["user"] = "yes";
            // redirect to dashboard
            // header("Location: .php");
            die();
        }else{
            die(json_encode(['error' => 'Invalid credentials.']));
        }
    }else{
        die(json_encode(['error' => 'Invalid credentials.']));
    }
}
?>

<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    // Generate a secret token when needed on page & one has not yet existed
    $_SESSION['csrf_token']= bin2hex(random_bytes(32));
    }

if (empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'],
    $_POST['csrf_token'])) {
    http_response_code(401);
    echo('Fail');
    exit();
    }

include_once('db.php');

header('Access-Control-Allow-Origin: https://se-prod.cse.buffalo.edu');
header('Access-Control-Allow-Credentials: true');
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
    die(json_encode(["code" => 200, "message" => "Already logged in"]));
}

if (isset($_POST["login"])) {

    $email = htmlspecialchars($_POST["email"] ?? '');
    $password = htmlspecialchars($_POST["password"] ?? '');
    
    if (!isset($email) || empty($email)) {
        die(json_encode(['error' => 'Email is required']));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['error' => 'Invalid email format']));
    }

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

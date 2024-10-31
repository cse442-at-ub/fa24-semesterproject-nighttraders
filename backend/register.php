<?php

header('Access-Control-Allow-Origin: https://se-prod.cse.buffalo.edu');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include_once('db.php');

if(
    (!isset($_SERVER['HTTPS'])||
    ($_SERVER['HTTPS']!='on')))
    {
    header('Location: '.
    'https://'.
    $_SERVER['SERVER_NAME'].
    $_SERVER['PHP_SELF']);
    }

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Invalid request method']));
}

// extract data from input
$username = $_POST["username"] ?? '';
$password = $_POST["password"] ?? '';  
$birthdate = $_POST["birthday"] ?? ''; 
$email = $_POST["email"] ?? '';
$passwordRepeat = $_POST["repeat_password"] ?? '';
$passwordHash = password_hash($password, PASSWORD_BCRYPT); // create hashed password

if (empty($username) OR empty($email) OR empty($password) OR empty($passwordRepeat)) {
    die(json_encode(['error' => 'All fields are required']));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['error' => 'Email is not valid']));
}
if (strlen($password) < 8) {
    die(json_encode(['error' => 'Password must be at least 8 characters']));
}
if (!preg_match('/[A-Z]/', $password)){
    die(json_encode(['error' => 'Password requires at least one capital letter']));
}
$specialChars = "!@#$%^&*()_+-=[]{}|;:,.<>?/~`";
if (!preg_match('/[' . preg_quote($specialChars, '/') . ']/', $password)){
    die(json_encode(['error' => 'Password requires at least one symbol']));
}
if ($password !== $passwordRepeat) {
    die(json_encode(['error' => 'Passwords do not match']));
}

// check if email is already registered
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$Result = $stmt->get_result();
$rowCount = $Result->num_rows;
if ($rowCount > 0) {
    die(json_encode(['error' => 'The email is already registered']));
}

// check if username is already registered
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$Result = $stmt->get_result();
$rowCount = $Result->num_rows;
if ($rowCount > 0) {
    die(json_encode(['error' => 'The username is already registered']));
}

// Insert the user into the database
$stmt = $conn->prepare("INSERT INTO users (username, password, birthdate, email) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("ssss", $username, $passwordHash, $birthdate, $email); // pass the hashed password into the "password" column
    if ($stmt->execute()) {
        echo json_encode(['code' => 200, 'message' => 'Successfully Registered']);
    } else {
        echo json_encode(['code' => 500, 'error' => 'Registration failed. Please try again.']);
    }
} else {
    echo json_encode(['code' => 500, 'error' => 'Something went wrong. Please try again.']);
}

$conn->close();
?>

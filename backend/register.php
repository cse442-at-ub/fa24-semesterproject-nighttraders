<?php
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

// extract data from input
$username = $_POST["username"]; 
$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
$birthdate = $_POST["birthday"];
$password = trim($_POST["password"]);
$passwordRepeat = trim($_POST["repeat_password"]);
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

if (empty($username) OR empty($email) OR empty($password) OR empty($passwordRepeat)) {
    die(json_encode(['error' => 'All fields are required']));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['error' => 'Email is not valid']));
}   
if (strlen($password)<8) {
    die(json_encode(['error' => 'Password must be at least 8 characters']));
}
if (!preg_match('/[A-Z]/', $password)){
    die(json_encode(['error' => 'Password requires at least one capital letter']));
}
$specialChars = "!@#$%^&*()_+-=[]{}|;:,.<>?/~`";
if (!preg_match('/[' . preg_quote($specialChars, '/') . ']/', $password)){
    die(json_encode(['error' => 'Password requires at least one symbol']));
}
if ($password!==$passwordRepeat) {
    die(json_encode(['error' => 'Passwords do not match']));
}
// check if email is already registered
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);
$rowCount = mysqli_num_rows($result);
if ($rowCount>0) {
    die(json_encode(['error' => 'The email is already registered']));
}
// check if username is already registered
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$rowCount = mysqli_num_rows($result);
if ($rowCount>0) {
    die(json_encode(['error' => 'The username is already registered']));
}

// Insert the user into the database
$sql = "INSERT INTO users (username, password, birthdate, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ssss", $username, $passwordHash, $birthdate, $email); 
    $stmt->execute();
    die(json_encode(["code" => 200, "error" => "Successfully registered"]));
}else{
    die(json_encode(["code" => 401, "error" => "Register unsuccessful"]));
}
$conn->close();
?>
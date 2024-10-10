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
if (isset($_POST["submit"])) {
    $username = $_POST["username"]; 
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $birthday = date('Y-m-d', strtotime(trim($_POST["birthday"])));
    $password = trim($_POST["password"]);
    $passwordRepeat = trim($_POST["repeat_password"]);

    $username = htmlspecialchars($username);
    $email = htmlspecialchars($email);
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
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $rowCount = mysqli_num_rows($result);
    if ($rowCount>0) {
        die(json_encode(['error' => 'The email is already registered']));
    }
    // check if username is already registered
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    $rowCount = mysqli_num_rows($result);
    if ($rowCount>0) {
        die(json_encode(['error' => 'The username is already registered']));
    }

    $sql = "INSERT INTO users (username, passwordHash, birthdate, email) VALUES (?, ?, ?, ? )";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $username, $hashed, $birthdate, $email);
        $stmt->execute();
    }else{
        die("Something went wrong");
    }
}
// Insert the user into the database
$sql = "INSERT INTO users (username, password, birthdate, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ssss", $username, $passwordHash, $birthdate, $email); 
    $stmt->execute();
    die("Successfully Registered");
}else{
    die("Something went wrong");
}

$conn->close();
?>


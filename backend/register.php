<?php

include_once('db.php');

// extract data from input
$username = $_POST["username"]; //up to 100 charaters long
$password = $_POST["password"];  // up to 100 characters long
$birthdate = $_POST["birthday"]; //up to 150 characters long 
$email = $_POST["email"];
$passwordRepeat = $_POST["repeat_password"];
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

if (empty($username) OR empty($email) OR empty($password) OR empty($passwordRepeat)) {
    die(json_encode(['error' => 'All fields are required.']));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['error' => 'Email is not valid.']));
}
if (strlen($password)<8) {
    die(json_encode(['error' => 'An account with this email already exists.']));
}
if (!preg_match('/[A-Z]/', $password)){
    die(json_encode(['error' => 'Password requires at least one capital letter.']));
}
$specialChars = "!@#$%^&*()_+-=[]{}|;:,.<>?/~`";
if (!preg_match('/[' . preg_quote($specialChars, '/') . ']/', $password)){
    die(json_encode(['error' => 'Password requires at least one symbol.']));
}
if ($password!==$passwordRepeat) {
    die(json_encode(['error' => 'Passwords do not match.']));
}
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);
$rowCount = mysqli_num_rows($result);
if ($rowCount>0) {
    die(json_encode(['error' => 'The email is already registered.']));
}

$sql = "INSERT INTO users (username, passwordHash, birthdate, email) VALUES (?, ?, ?, ? )";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ssss", $username, $hashed_password, $birthdate, $email);
    $stmt->execute();
}else{
    die("Something went wrong");
}

$conn->close();
?>
<?php

//Start by connecting to the sql database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersTest";

//Start connection
$conn = new mysqli($servername, $username, $password, $dbname);


// checking if it is properly connected, chatgpt reccomended I do this but the syntax confuses me
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "If you are reading this than the connection was successful!";


/* READ THIS FOR INFO ON DATABASE
//The database takes in these fields:

$username = "testuser"; //up to 100 charaters long max
$password = "testpassword"; // up to 100 characters long max
$birthdate = "2000-01-01";
$email = "testuser@example.com"; //up to 150 characters long max

//And you would insert into the database like this
$sql = "INSERT INTO users (id, username, password, birthdate, email) VALUES (NULL, '$username', '$password', '$birthdate', '$email')";
//Make sure you set the id to null because it is auto-incrimenting

if ($conn->query($sql) === TRUE) {
    echo "Added user to the users table";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

*/

$conn->close();
?>
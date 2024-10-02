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
echo "If you are reading this than the connection was successful!"



?>
<?php
// fetch an individual stock from the database
session_start();
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

// for production, uncomment line below, and delete line 18
// header('Access-Control-Allow-Origin: https://se-prod.cse.buffalo.edu');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die(json_encode(['error' => 'Invalid request method']));
}

$symbol = $_GET["symbol"] ?? '';

// get the stock from db based on symbol
$stmt = $conn->prepare("SELECT * FROM stockInfo WHERE Symbol = ?");
$stmt->bind_param("s", $symbol);
$stmt->execute();
$result = $stmt->get_result();
$stock = $result->fetch_assoc();

// store the stock information in session
if ($stock){
    $_SESSION['selected_stock'] = $stock;
    die(json_encode(["code" => 200, "message" => "Stock retrieved successfully"]));
    } else {
    die(json_encode(["code" => 401, "error" => "Stock couldn't be retrieved"]));
    }

$conn->close();
?>

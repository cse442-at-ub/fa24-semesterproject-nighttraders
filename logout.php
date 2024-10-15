<?php
// Enable CORS for local testing if needed
header('Access-Control-Allow-Origin: http://localhost:3000');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Start the session
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
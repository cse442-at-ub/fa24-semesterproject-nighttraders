<?php
//Remember to add this to package.json:
//  "homepage": "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/",



// backend-stocksAPI/config.php or backend-monte/config.php

// Base URLs
define('BASE_URL', 'https://se-prod.cse.buffalo.edu');

define('FRONTEND_URL', 'https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/');
define('BACKEND_URL', 'https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/backend/');


// Database configuration
define('API_KEY', 'OC18QBU4BLSDMQKA');
define('SERVER_NAME', 'localhost');
define('DB_USER', 'dlincogn');
define('DB_PASS', '50503958');
define('DB_NAME', 'cse442_2024_fall_team_e_db');



// // For local development environment

// // Base URLs
// define('BASE_URL', 'http://localhost');  // Changed to localhost
// define('FRONTEND_URL', 'http://localhost');  // Adjust folder name
// define('BACKEND_URL', 'http://localhost/backend');  // Adjust folder name

// // Database configuration
// define('API_KEY', 'OC18QBU4BLSDMQKA');  // Keep your API key
// define('SERVER_NAME', 'localhost');  // Keep localhost
// define('DB_USER', 'root');  // Default XAMPP/MAMP username
// define('DB_PASS', '');  // Default XAMPP password is blank, MAMP uses 'root'
// define('DB_NAME', 'usersTest');  // Your local database name

// Set secure session cookie parameters
ini_set('session.cookie_secure', '1'); // Ensure cookies are sent over HTTPS
ini_set('session.cookie_httponly', '1'); // Prevent JavaScript access to session cookies
ini_set('session.use_strict_mode', '1'); // Enforce strict session ID mode

// config.php
// Remove 'domain' or set it to an empty string if not necessary
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    // 'domain' => '', // Commented out or set to an empty string
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);


// Ensure session is started with the above parameters
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

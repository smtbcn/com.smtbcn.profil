<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'profil_smtbcn');
define('DB_USER', 'profil_smtbcn');
define('DB_PASS', 'w3eWNV7wydMa84VbXrVk');

// API Security
define('API_KEY', 'milasoft_secure_key_2025');

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-KEY, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

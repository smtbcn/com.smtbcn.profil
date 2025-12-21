<?php
// PHP Backend Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'profil_smtbcn');
define('DB_USER', 'profil_smtbcn');
define('DB_PASS', 'w3eWNV7wydMa84VbXrVk');

// API Security Key (Uygulama ile backend arasındaki güvenliği sağlamak için)
define('API_KEY', 'milasoft_secure_key_2025');

// CORS Ayarları (Uygulamanın sunucuya erişebilmesi için)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-KEY, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}


// Hata ayıklama modunu kapatın (Production'da kapalı olmalı)
error_reporting(0);
ini_set('display_errors', 0);
?>
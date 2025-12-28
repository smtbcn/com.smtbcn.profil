<?php
ini_set('session.gc_maxlifetime', 2592000); // 30 days
session_set_cookie_params([
    'lifetime' => 2592000,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

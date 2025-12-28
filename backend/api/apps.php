<?php
require_once '../config/config.php';
require_once '../core/Database.php';

header('Content-Type: application/json');

// API Key Check
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM applications WHERE is_active = 1 ORDER BY sort_order ASC");
    $apps = $stmt->fetchAll();

    echo json_encode($apps);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

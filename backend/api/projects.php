<?php
require_once '../config/config.php';
require_once '../core/Database.php';

header('Content-Type: application/json');

if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized Access']);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
    $projects = $stmt->fetchAll();

    echo json_encode($projects);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<?php
session_start();
require_once '../config/config.php';
require_once '../core/Database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die("Unauthorized");
}

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['order']) && isset($data['table'])) {
    $db = Database::getInstance();
    $table = $data['table']; // applications or skills

    foreach ($data['order'] as $index => $id) {
        $stmt = $db->prepare("UPDATE {$table} SET sort_order = ? WHERE id = ?");
        $stmt->execute([$index + 1, (int) $id]);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
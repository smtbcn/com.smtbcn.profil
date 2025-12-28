<?php
require_once '../core/Session.php';
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Security.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['table']) && isset($data['items'])) {
        $db = Database::getInstance();
        $table = $data['table'];
        $items = $data['items']; // Array of [id, order]

        // Allowed tables list for security
        $allowed_tables = ['applications', 'projects', 'skills', 'timeline'];
        if (!in_array($table, $allowed_tables)) {
            echo json_encode(['error' => 'Invalid table']);
            exit;
        }

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE $table SET sort_order = ? WHERE id = ?");

            foreach ($items as $item) {
                $stmt->execute([$item['order'], $item['id']]);
            }

            $db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Missing data']);
    }
}

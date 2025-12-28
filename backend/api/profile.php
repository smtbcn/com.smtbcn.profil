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

    // Get Status
    $stmt = $db->query("SELECT * FROM profile_status LIMIT 1");
    $status = $stmt->fetch();

    // Get Skills
    $stmt = $db->query("SELECT * FROM skills WHERE is_active = 1 ORDER BY sort_order ASC");
    $skills = $stmt->fetchAll();

    // Get Timeline
    $stmt = $db->query("SELECT * FROM timeline WHERE is_active = 1 ORDER BY sort_order ASC");
    $timeline = $stmt->fetchAll();

    echo json_encode([
        'status' => $status['status_key'] ?? 'offline',
        'current_activity' => [
            'tr' => $status['activity_tr'] ?? '',
            'en' => $status['activity_en'] ?? ''
        ],
        'about' => [
            'tr' => $status['about_tr'] ?? '',
            'en' => $status['about_en'] ?? ''
        ],
        'skills' => $skills,
        'timeline' => $timeline
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

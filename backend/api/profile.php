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

    // 1. Profil, Durum ve Hakkımda Bilgisi
    $stmtStatus = $db->query("SELECT * FROM profile_status ORDER BY id DESC LIMIT 1");
    $statusData = $stmtStatus->fetch();

    // 2. Yetenekleri Çek
    $stmtSkills = $db->query("SELECT name, color, icon FROM skills WHERE is_active = 1 ORDER BY sort_order ASC");
    $skillsData = $stmtSkills->fetchAll();

    // 3. Zaman Tünelini Çek
    $stmtTimeline = $db->query("SELECT * FROM timeline ORDER BY sort_order ASC, id DESC");
    $timelineData = $stmtTimeline->fetchAll();

    // Yanıtı Oluştur
    $response = [
        'status' => $statusData['status_key'] ?? 'online',
        'current_activity' => [
            'tr' => $statusData['activity_tr'] ?? 'Geliştiriliyor...',
            'en' => $statusData['activity_en'] ?? 'Developing...'
        ],
        'about' => [
            'tr' => $statusData['about_tr'] ?? '',
            'en' => $statusData['about_en'] ?? ''
        ],
        'skills' => $skillsData ?: [],
        'timeline' => $timelineData ?: []
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()]);
}
?>
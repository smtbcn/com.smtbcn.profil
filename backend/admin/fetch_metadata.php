<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../core/StoreScraper.php';

$url = $_GET['url'] ?? '';

if (filter_var($url, FILTER_VALIDATE_URL)) {
    try {
        $metadata = StoreScraper::fetchMetadata($url);
        if ($metadata && !empty($metadata['name'])) {
            echo json_encode($metadata);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Bilgiler alınamadı. Lütfen URL\'yi kontrol edin veya manuel girin.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Sistem hatası: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz URL formatı.']);
}
?>
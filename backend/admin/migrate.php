<?php
session_start();
require_once '../config/config.php';
require_once '../core/Database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die("Yetkisiz Giriş!");
}

try {
    $db = Database::getInstance();
    echo "<h2>Sistem Yapılandırma (Admin ve Timeline Güncellemesi)</h2>";
    echo "<ul>";

    // 1. Timeline Tablosunu Oluştur
    echo "<li>Timeline tablosu oluşturuluyor... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `timeline` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title_tr` varchar(255) NOT NULL,
        `title_en` varchar(255) NOT NULL,
        `desc_tr` text,
        `desc_en` text,
        `event_date` varchar(50) DEFAULT NULL,
        `icon` varchar(50) DEFAULT 'rocket',
        `type` varchar(20) DEFAULT 'custom', -- project, app, work, education, milestone
        `color` varchar(20) DEFAULT '#238636',
        `link` text,
        `sort_order` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<b>TAMAM</b></li>";

    // 2. Skills Tablosu Kontrolü
    $stmt = $db->query("DESCRIBE skills");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('icon', $cols)) {
        $db->exec("ALTER TABLE `skills` ADD `icon` VARCHAR(50) DEFAULT 'code' AFTER `color` ");
        echo "<li>Skills: 'icon' sütunu eklendi.</li>";
    }

    // 3. Admin Tablosunu Oluştur
    echo "<li>Admin tablosu kontrol ediliyor... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Kolon Kontrolü (Eski tablolarda password yerine pass kalmış olabilir veya eksik olabilir)
    $stmt = $db->query("DESCRIBE admins");
    $adminCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('password', $adminCols)) {
        if (in_array('pass', $adminCols)) {
            $db->exec("ALTER TABLE `admins` CHANGE `pass` `password` VARCHAR(255) NOT NULL");
            echo "<li>Admin: 'pass' sütunu 'password' olarak güncellendi.</li>";
        } else {
            $db->exec("ALTER TABLE `admins` ADD `password` VARCHAR(255) NOT NULL AFTER `username` ");
            echo "<li>Admin: 'password' sütunu eklendi.</li>";
        }
    }

    // Varsayılan Admin Hesabı (Eğer hiç yoksa)
    $check = $db->query("SELECT id FROM admins LIMIT 1");
    if (!$check->fetch()) {
        $pass = password_hash('admin123', PASSWORD_BCRYPT);
        $db->exec("INSERT INTO admins (username, password) VALUES ('admin', '$pass')");
        echo "<b>VARSAYILAN HESAP OLUŞTURULDU (admin / admin123)</b> ";
    }
    echo "<b>TAMAM</b></li>";

    echo "</ul>";
    echo "<h3 style='color:#238636'>✓ İşlem Başarıyla Tamamlandı!</h3>";
    echo "<p><a href='dashboard.php' style='padding:10px 20px; background:#238636; color:white; text-decoration:none; border-radius:5px;'>Dashboard'a Dön</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color:#f85149;'>HATA: " . $e->getMessage() . "</h2>";
}
?>
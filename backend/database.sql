-- SMTBCN Portfolio Database Structure

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Yetki ve Kullanıcı Tablosu
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Profil ve Canlı Durum Tablosu
CREATE TABLE IF NOT EXISTS `profile_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_key` varchar(20) DEFAULT 'online', -- online, busy, coding, travel
  `activity_tr` text,
  `activity_en` text,
  `about_tr` text,
  `about_en` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Yetenekler Tablosu
CREATE TABLE IF NOT EXISTS `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT '#FFFFFF',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Uygulamalar Tablosu
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_key` varchar(50) DEFAULT NULL, -- 'android', 'apple'
  `name_tr` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `desc_tr` text,
  `desc_en` text,
  `icon` text,

  `color` varchar(20) DEFAULT '#FFFFFF',
  `url` text,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Projeler Tablosu
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `html_url` text,
  `language` varchar(50) DEFAULT NULL,
  `stargazers_count` int(11) DEFAULT 0,
  `forks_count` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan Verileri Ekleme
INSERT INTO `admins` (`username`, `password_hash`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO `profile_status` (`status_key`, `activity_tr`, `activity_en`, `about_tr`, `about_en`) VALUES 
('online', 'Şu an Portfolyo Uygulamamı geliştiriyorum... 🚀', 'Developing my Portfolio App... 🚀', 
'<h1>Merhaba!</h1><p>Ben Samet, tutkulu bir <strong>Mobil Uygulama Geliştiricisiyim</strong>. React Native ve modern teknolojilerle dünya standartlarında çözümler üretiyorum.</p>', 
'<h1>Hello!</h1><p>I am Samet, a passionate <strong>Mobile App Developer</strong>. Creating world-class solutions with React Native and modern technologies.</p>');

INSERT INTO `skills` (`name`, `color`, `sort_order`) VALUES 
('React Native', '#61DAFB', 1),
('TypeScript', '#3178C6', 2),
('PHP / MySQL', '#777BB4', 3);

COMMIT;

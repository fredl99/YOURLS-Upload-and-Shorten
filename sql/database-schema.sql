-- YOURLS Upload and Shorten Plugin Database Schema
-- Version: 2.0.0
-- Author: News Targeted
-- Description: Database tables for file upload management and settings

-- Upload files table
CREATE TABLE IF NOT EXISTS `yourls_upload_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `short_url` varchar(200) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `storage_location` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `upload_date` datetime NOT NULL,
  `expiration_date` datetime DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT 'public',
  `download_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `short_url` (`short_url`),
  KEY `expiration_date` (`expiration_date`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `upload_date` (`upload_date`),
  KEY `storage_location` (`storage_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upload settings table
CREATE TABLE IF NOT EXISTS `yourls_upload_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO `yourls_upload_settings` (`setting_key`, `setting_value`) VALUES
('storage_locations', '{"default":{"name":"Default Storage","path":"/home/newstargeted.com/yourls.newstargeted.com/uploads/","url":"https://yourls.newstargeted.com/uploads/","enabled":true}}'),
('default_storage', 'default'),
('expiration_default', 'never'),
('expiration_custom_days', '30'),
('max_file_size', '10'),
('allowed_file_types', '["jpg","jpeg","png","gif","pdf","doc","docx","txt","zip","mp3","mp4"]'),
('blocked_file_types', '["php","php3","php4","php5","phtml","exe","bat","sh"]'),
('frontend_uploads_enabled', '1'),
('frontend_message', 'Upload files and get short URLs instantly!'),
('require_auth', '0'),
('rate_limit_uploads', '10'),
('rate_limit_window', '3600'),
('plugin_version', '2.0.0'),
('last_cleanup', '0');

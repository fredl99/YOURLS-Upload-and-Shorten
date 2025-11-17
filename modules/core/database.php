<?php
/**
 * Database Management Module
 * Handles database schema creation and queries for upload plugin
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Create database tables on plugin activation
 */
function upload_create_database_tables() {
    global $ydb;
    
    try {
        // Create upload_files table
        $upload_files_sql = "
            CREATE TABLE IF NOT EXISTS `" . YOURLS_DB_PREFIX . "upload_files` (
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
                KEY `uploaded_by` (`uploaded_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $ydb->query($upload_files_sql);
        
        // Create upload_settings table
        $upload_settings_sql = "
            CREATE TABLE IF NOT EXISTS `" . YOURLS_DB_PREFIX . "upload_settings` (
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text NOT NULL,
                PRIMARY KEY (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $ydb->query($upload_settings_sql);
        
        // Insert default settings
        upload_set_default_settings();
        
        return true;
        
    } catch (Exception $e) {
        error_log('Upload plugin database creation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Set default plugin settings
 */
function upload_set_default_settings() {
    global $ydb;
    
    $default_settings = [
        'storage_locations' => json_encode([
            'default' => [
                'name' => 'Default Storage',
                'path' => YOURLS_SITE . '/uploads/',
                'enabled' => true
            ]
        ]),
        'default_storage' => 'default',
        'expiration_default' => 'never',
        'expiration_custom_days' => '30',
        'max_file_size' => '10', // MB
        'allowed_file_types' => json_encode(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip']),
        'blocked_file_types' => json_encode(['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'sh']),
        'frontend_uploads_enabled' => '1',
        'frontend_message' => 'Upload files and get short URLs instantly!',
        'require_auth' => '0',
        'rate_limit_uploads' => '10', // per hour
        'rate_limit_window' => '3600' // seconds
    ];
    
    foreach ($default_settings as $key => $value) {
        $sql = "INSERT IGNORE INTO `" . YOURLS_DB_PREFIX . "upload_settings` 
                (`setting_key`, `setting_value`) VALUES (%s, %s)";
        $ydb->query($sql, $key, $value);
    }
}

/**
 * Get setting value from database
 * 
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function upload_get_setting($key, $default = null) {
    global $ydb;
    
    try {
        $sql = "SELECT `setting_value` FROM `" . YOURLS_DB_PREFIX . "upload_settings` 
                WHERE `setting_key` = %s";
        $result = $ydb->get_var($sql, $key);
        
        if ($result === null) {
            return $default;
        }
        
        // Try to decode JSON, return as-is if not JSON
        $decoded = json_decode($result, true);
        return ($decoded !== null) ? $decoded : $result;
        
    } catch (Exception $e) {
        error_log('Upload plugin setting retrieval failed: ' . $e->getMessage());
        return $default;
    }
}

/**
 * Update setting value in database
 * 
 * @param string $key Setting key
 * @param mixed $value Setting value
 * @return bool Success status
 */
function upload_update_setting($key, $value) {
    global $ydb;
    
    try {
        // Encode arrays/objects as JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        $sql = "INSERT INTO `" . YOURLS_DB_PREFIX . "upload_settings` 
                (`setting_key`, `setting_value`) VALUES (%s, %s)
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)";
        
        $result = $ydb->query($sql, $key, $value);
        return ($result !== false);
        
    } catch (Exception $e) {
        error_log('Upload plugin setting update failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all settings as associative array
 * 
 * @return array All settings
 */
function upload_get_all_settings() {
    global $ydb;
    
    try {
        $sql = "SELECT `setting_key`, `setting_value` FROM `" . YOURLS_DB_PREFIX . "upload_settings`";
        $results = $ydb->get_results($sql);
        
        $settings = [];
        foreach ($results as $row) {
            $decoded = json_decode($row->setting_value, true);
            $settings[$row->setting_key] = ($decoded !== null) ? $decoded : $row->setting_value;
        }
        
        return $settings;
        
    } catch (Exception $e) {
        error_log('Upload plugin settings retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Store upload file record in database
 * 
 * @param array $file_data File information
 * @return int|false File ID or false on failure
 */
function upload_store_file_record($file_data) {
    global $ydb;
    
    try {
        $sql = "INSERT INTO `" . YOURLS_DB_PREFIX . "upload_files` 
                (`short_url`, `filename`, `original_filename`, `file_path`, `storage_location`, 
                 `file_size`, `mime_type`, `upload_date`, `expiration_date`, `uploaded_by`) 
                VALUES (%s, %s, %s, %s, %s, %d, %s, %s, %s, %s)";
        
        $result = $ydb->query(
            $sql,
            $file_data['short_url'],
            $file_data['filename'],
            $file_data['original_filename'],
            $file_data['file_path'],
            $file_data['storage_location'],
            $file_data['file_size'],
            $file_data['mime_type'] ?? null,
            $file_data['upload_date'],
            $file_data['expiration_date'] ?? null,
            $file_data['uploaded_by'] ?? 'public'
        );
        
        return $result ? $ydb->insert_id : false;
        
    } catch (Exception $e) {
        error_log('Upload plugin file record storage failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get file record by short URL
 * 
 * @param string $short_url Short URL
 * @return object|null File record or null
 */
function upload_get_file_record($short_url) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `short_url` = %s LIMIT 1";
        return $ydb->get_row($sql, $short_url);
        
    } catch (Exception $e) {
        error_log('Upload plugin file record retrieval failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Update file record
 * 
 * @param int $file_id File ID
 * @param array $data Update data
 * @return bool Success status
 */
function upload_update_file_record($file_id, $data) {
    global $ydb;
    
    try {
        $set_clauses = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $set_clauses[] = "`$key` = %s";
            $values[] = $value;
        }
        
        $values[] = $file_id;
        
        $sql = "UPDATE `" . YOURLS_DB_PREFIX . "upload_files` 
                SET " . implode(', ', $set_clauses) . " 
                WHERE `id` = %d";
        
        return $ydb->query($sql, ...$values) !== false;
        
    } catch (Exception $e) {
        error_log('Upload plugin file record update failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get expired files
 * 
 * @param int $limit Maximum number of files to return
 * @return array Expired file records
 */
function upload_get_expired_files($limit = 100) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `expiration_date` IS NOT NULL 
                AND `expiration_date` <= NOW() 
                ORDER BY `expiration_date` ASC 
                LIMIT %d";
        
        return $ydb->get_results($sql, $limit);
        
    } catch (Exception $e) {
        error_log('Upload plugin expired files retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Delete file record
 * 
 * @param int $file_id File ID
 * @return bool Success status
 */
function upload_delete_file_record($file_id) {
    global $ydb;
    
    try {
        $sql = "DELETE FROM `" . YOURLS_DB_PREFIX . "upload_files` WHERE `id` = %d";
        return $ydb->query($sql, $file_id) !== false;
        
    } catch (Exception $e) {
        error_log('Upload plugin file record deletion failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get files by uploader
 * 
 * @param string $uploaded_by Uploader identifier
 * @param int $limit Maximum number of files
 * @param int $offset Offset for pagination
 * @return array File records
 */
function upload_get_files_by_uploader($uploaded_by, $limit = 50, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `uploaded_by` = %s 
                ORDER BY `upload_date` DESC 
                LIMIT %d OFFSET %d";
        
        return $ydb->get_results($sql, $uploaded_by, $limit, $offset);
        
    } catch (Exception $e) {
        error_log('Upload plugin files by uploader retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Increment download count
 * 
 * @param string $short_url Short URL
 * @return bool Success status
 */
function upload_increment_download_count($short_url) {
    global $ydb;
    
    try {
        $sql = "UPDATE `" . YOURLS_DB_PREFIX . "upload_files` 
                SET `download_count` = `download_count` + 1 
                WHERE `short_url` = %s";
        
        return $ydb->query($sql, $short_url) !== false;
        
    } catch (Exception $e) {
        error_log('Upload plugin download count increment failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get overall storage statistics
 * 
 * @return array Storage statistics
 */
function upload_get_overall_storage_stats() {
    global $ydb;
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(`file_size`) as total_size,
                    AVG(`file_size`) as avg_size,
                    MAX(`file_size`) as max_size,
                    MIN(`file_size`) as min_size
                FROM `" . YOURLS_DB_PREFIX . "upload_files`";
        
        return $ydb->get_row($sql);
        
    } catch (Exception $e) {
        error_log('Upload plugin storage stats retrieval failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get file record by ID
 * 
 * @param int $file_id File ID
 * @return object|null File record
 */
function upload_get_file_record_by_id($file_id) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `id` = %d LIMIT 1";
        return $ydb->get_row($sql, $file_id);
        
    } catch (Exception $e) {
        error_log('Upload plugin file record retrieval by ID failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get file record by file path
 * 
 * @param string $file_path File path
 * @return object|null File record
 */
function upload_get_file_record_by_path($file_path) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `file_path` = %s LIMIT 1";
        return $ydb->get_row($sql, $file_path);
        
    } catch (Exception $e) {
        error_log('Upload plugin file record retrieval by path failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Delete file record by ID
 * 
 * @param int $file_id File ID
 * @return bool Success
 */
function upload_delete_file_record_by_id($file_id) {
    global $ydb;
    
    try {
        $sql = "DELETE FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `id` = %d";
        
        return $ydb->query($sql, $file_id) !== false;
        
    } catch (Exception $e) {
        error_log('Upload plugin file record deletion failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get files by storage location
 * 
 * @param string $storage_location Storage location key
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array Files
 */
function upload_get_files_by_storage_location($storage_location, $limit = 100, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `storage_location` = %s 
                ORDER BY `upload_date` DESC 
                LIMIT %d OFFSET %d";
        
        return $ydb->get_results($sql, $storage_location, $limit, $offset) ?: [];
        
    } catch (Exception $e) {
        error_log('Upload plugin files by storage retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get storage statistics for a specific location
 * 
 * @param string $storage_location Storage location key
 * @return array Statistics
 */
function upload_get_storage_location_stats($storage_location) {
    global $ydb;
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(`file_size`) as total_size,
                    AVG(`file_size`) as avg_size,
                    MAX(`file_size`) as max_size,
                    MIN(`file_size`) as min_size,
                    MIN(`upload_date`) as oldest_file,
                    MAX(`upload_date`) as newest_file
                FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `storage_location` = %s";
        
        $result = $ydb->get_row($sql, $storage_location);
        
        return [
            'total_files' => (int) ($result->total_files ?? 0),
            'total_size' => (int) ($result->total_size ?? 0),
            'avg_size' => (int) ($result->avg_size ?? 0),
            'max_size' => (int) ($result->max_size ?? 0),
            'min_size' => (int) ($result->min_size ?? 0),
            'oldest_file' => $result->oldest_file,
            'newest_file' => $result->newest_file
        ];
        
    } catch (Exception $e) {
        error_log('Upload plugin storage location stats retrieval failed: ' . $e->getMessage());
        return [
            'total_files' => 0,
            'total_size' => 0,
            'avg_size' => 0,
            'max_size' => 0,
            'min_size' => 0,
            'oldest_file' => null,
            'newest_file' => null
        ];
    }
}

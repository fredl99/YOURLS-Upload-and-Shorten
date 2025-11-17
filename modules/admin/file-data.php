<?php
/**
 * File Data Module
 * Functions for retrieving file data and statistics
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Get all files with pagination
 */
function upload_get_all_files($limit = 20, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                ORDER BY `upload_date` DESC 
                LIMIT %d OFFSET %d";
        
        return $ydb->get_results($sql, $limit, $offset) ?: [];
        
    } catch (Exception $e) {
        error_log('Upload plugin file retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get total files count
 */
function upload_get_total_files_count() {
    global $ydb;
    
    try {
        $sql = "SELECT COUNT(*) as count FROM `" . YOURLS_DB_PREFIX . "upload_files`";
        $result = $ydb->get_row($sql);
        
        return (int) ($result->count ?? 0);
        
    } catch (Exception $e) {
        error_log('Upload plugin file count retrieval failed: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get files with filters
 */
function upload_get_files_filtered($filters = [], $limit = 20, $offset = 0) {
    global $ydb;
    
    try {
        $where_conditions = [];
        $where_values = [];
        
        // File type filter
        if (!empty($filters['type'])) {
            switch ($filters['type']) {
                case 'image':
                    $where_conditions[] = "`mime_type` LIKE 'image/%'";
                    break;
                case 'document':
                    $where_conditions[] = "(`mime_type` LIKE 'application/pdf%' OR `mime_type` LIKE 'application/msword%' OR `mime_type` LIKE 'application/vnd.openxmlformats%')";
                    break;
                case 'archive':
                    $where_conditions[] = "(`mime_type` LIKE 'application/zip%' OR `mime_type` LIKE 'application/x-rar%' OR `mime_type` LIKE 'application/x-7z%')";
                    break;
                case 'other':
                    $where_conditions[] = "`mime_type` NOT LIKE 'image/%' AND `mime_type` NOT LIKE 'application/pdf%' AND `mime_type` NOT LIKE 'application/msword%' AND `mime_type` NOT LIKE 'application/vnd.openxmlformats%' AND `mime_type` NOT LIKE 'application/zip%' AND `mime_type` NOT LIKE 'application/x-rar%' AND `mime_type` NOT LIKE 'application/x-7z%'";
                    break;
            }
        }
        
        // Expiration filter
        if (!empty($filters['expired'])) {
            $now = date('Y-m-d H:i:s');
            switch ($filters['expired']) {
                case 'expired':
                    $where_conditions[] = "`expiration_date` IS NOT NULL AND `expiration_date` < %s";
                    $where_values[] = $now;
                    break;
                case 'expiring_soon':
                    $week_from_now = date('Y-m-d H:i:s', strtotime('+7 days'));
                    $where_conditions[] = "`expiration_date` IS NOT NULL AND `expiration_date` BETWEEN %s AND %s";
                    $where_values[] = $now;
                    $where_values[] = $week_from_now;
                    break;
                case 'never':
                    $where_conditions[] = "`expiration_date` IS NULL";
                    break;
            }
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where_conditions[] = "(`original_filename` LIKE %s OR `short_url` LIKE %s)";
            $search_term = '%' . $ydb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Build query
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files`";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY `upload_date` DESC LIMIT %d OFFSET %d";
        $where_values[] = $limit;
        $where_values[] = $offset;
        
        return $ydb->get_results($sql, ...$where_values) ?: [];
        
    } catch (Exception $e) {
        error_log('Upload plugin filtered file retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get files by storage location
 */
function upload_get_files_by_storage($storage_location, $limit = 20, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE `storage_location` = %s 
                ORDER BY `upload_date` DESC 
                LIMIT %d OFFSET %d";
        
        return $ydb->get_results($sql, $storage_location, $limit, $offset) ?: [];
        
    } catch (Exception $e) {
        error_log('Upload plugin storage file retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get file statistics
 */
function upload_get_file_statistics() {
    global $ydb;
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(`file_size`) as total_size,
                    AVG(`file_size`) as avg_size,
                    MAX(`file_size`) as max_size,
                    MIN(`file_size`) as min_size,
                    COUNT(CASE WHEN `expiration_date` IS NULL THEN 1 END) as never_expire,
                    COUNT(CASE WHEN `expiration_date` < NOW() THEN 1 END) as expired,
                    COUNT(CASE WHEN `expiration_date` BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 END) as expiring_soon
                FROM `" . YOURLS_DB_PREFIX . "upload_files`";
        
        $result = $ydb->get_row($sql);
        
        return [
            'total_files' => (int) ($result->total_files ?? 0),
            'total_size' => (int) ($result->total_size ?? 0),
            'avg_size' => (int) ($result->avg_size ?? 0),
            'max_size' => (int) ($result->max_size ?? 0),
            'min_size' => (int) ($result->min_size ?? 0),
            'never_expire' => (int) ($result->never_expire ?? 0),
            'expired' => (int) ($result->expired ?? 0),
            'expiring_soon' => (int) ($result->expiring_soon ?? 0)
        ];
        
    } catch (Exception $e) {
        error_log('Upload plugin file statistics retrieval failed: ' . $e->getMessage());
        return [
            'total_files' => 0,
            'total_size' => 0,
            'avg_size' => 0,
            'max_size' => 0,
            'min_size' => 0,
            'never_expire' => 0,
            'expired' => 0,
            'expiring_soon' => 0
        ];
    }
}

/**
 * Get recent files
 */
function upload_get_recent_files($limit = 10) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                ORDER BY `upload_date` DESC 
                LIMIT %d";
        
        return $ydb->get_results($sql, $limit) ?: [];
        
    } catch (Exception $e) {
        error_log('Upload plugin recent files retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get most downloaded files
 */
function upload_get_most_downloaded_files($limit = 10) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                ORDER BY `download_count` DESC 
                LIMIT %d";
        
        return $ydb->get_results($sql, $limit) ?: [];
        
    } catch (Exception $e) {
        error_log('Upload plugin most downloaded files retrieval failed: ' . $e->getMessage());
        return [];
    }
}

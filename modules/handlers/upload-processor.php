<?php


/**
 * Get upload statistics
 * 
 * @return array Upload statistics
 */
function upload_get_upload_stats() {
    global $ydb;
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_uploads,
                    SUM(file_size) as total_size,
                    AVG(file_size) as avg_size,
                    COUNT(CASE WHEN expiration_date IS NULL THEN 1 END) as permanent_files,
                    COUNT(CASE WHEN expiration_date IS NOT NULL AND expiration_date > NOW() THEN 1 END) as active_files,
                    COUNT(CASE WHEN expiration_date IS NOT NULL AND expiration_date <= NOW() THEN 1 END) as expired_files
                FROM `" . YOURLS_DB_PREFIX . "upload_files`";
        
        $stats = $ydb->get_row($sql);
        
        // Get recent uploads (last 24 hours)
        $recent_sql = "SELECT COUNT(*) FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                      WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $recent_uploads = (int) $ydb->get_var($recent_sql);
        
        $stats->recent_uploads = $recent_uploads;
        
        return $stats;
        
    } catch (Exception $e) {
        error_log('Upload stats retrieval failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Clean up failed uploads
 * 
 * @param int $max_age_hours Maximum age in hours
 * @return int Number of files cleaned up
 */
function upload_cleanup_failed_uploads($max_age_hours = 24) {
    global $ydb;
    
    try {
        // Find files without corresponding short URLs
        $sql = "SELECT uf.* FROM `" . YOURLS_DB_PREFIX . "upload_files` uf
                LEFT JOIN `" . YOURLS_DB_PREFIX . "url` u ON uf.short_url = u.url
                WHERE u.url IS NULL 
                AND uf.upload_date < DATE_SUB(NOW(), INTERVAL %d HOUR)";
        
        $orphaned_files = $ydb->get_results($sql, $max_age_hours);
        $cleaned_count = 0;
        
        foreach ($orphaned_files as $file) {
            // Delete file from storage
            upload_delete_file_from_storage($file->file_path);
            
            // Delete file record
            if (upload_delete_file_record($file->id)) {
                $cleaned_count++;
            }
        }
        
        return $cleaned_count;
        
    } catch (Exception $e) {
        error_log('Failed upload cleanup error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get upload history for user
 * 
 * @param string $uploaded_by User identifier
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array Upload history
 */
function upload_get_upload_history($uploaded_by, $limit = 20, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE uploaded_by = %s 
                ORDER BY upload_date DESC 
                LIMIT %d OFFSET %d";
        
        return $ydb->get_results($sql, $uploaded_by, $limit, $offset);
        
    } catch (Exception $e) {
        error_log('Upload history retrieval failed: ' . $e->getMessage());
        return [];
    }
}

/**
 * Update file download count
 * 
 * @param string $short_url Short URL
 * @return bool Success status
 */
function upload_update_download_count($short_url) {
    return upload_increment_download_count($short_url);
}

/**
 * Get file by short URL
 * 
 * @param string $short_url Short URL
 * @return object|null File data
 */
function upload_get_file_by_short_url($short_url) {
    return upload_get_file_record($short_url);
}

/**
 * Check if file exists and is accessible
 * 
 * @param string $short_url Short URL
 * @return bool File exists and accessible
 */
function upload_is_file_accessible($short_url) {
    $file = upload_get_file_by_short_url($short_url);
    
    if (!$file) {
        return false;
    }
    
    // Check if file exists on disk
    if (!upload_file_exists($file->file_path)) {
        return false;
    }
    
    // Check if file is expired
    if ($file->expiration_date && strtotime($file->expiration_date) < time()) {
        return false;
    }
    
    return true;
}

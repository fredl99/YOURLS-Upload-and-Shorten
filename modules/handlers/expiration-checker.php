<?php
/**
 * Expiration Checker Module
 * Handles scheduled expiration checks and cleanup
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Check for expired files (scheduled task)
 * This function is called on every 100th page load to minimize overhead
 */
function upload_check_expired_files() {
    // Only run on every 100th page load to minimize overhead
    static $check_counter = 0;
    $check_counter++;
    
    if ($check_counter % 100 !== 0) {
        return;
    }
    
    // Check if cleanup was run recently (within last hour)
    $last_cleanup = upload_get_setting('last_cleanup', 0);
    if (time() - $last_cleanup < 3600) {
        return;
    }
    
    try {
        // Get expired files
        $expired_files = upload_get_expired_files(100);
        
        if (empty($expired_files)) {
            // Update last cleanup time even if no files to clean
            upload_update_setting('last_cleanup', time());
            return;
        }
        
        $cleaned_count = 0;
        $errors = [];
        
        foreach ($expired_files as $file) {
            $cleanup_result = upload_cleanup_expired_file($file);
            
            if ($cleanup_result['success']) {
                $cleaned_count++;
            } else {
                $errors[] = "File ID {$file->id}: " . $cleanup_result['error'];
            }
        }
        
        // Update last cleanup time
        upload_update_setting('last_cleanup', time());
        
        // Log cleanup results
        if ($cleaned_count > 0) {
            error_log("Upload plugin: Cleaned up {$cleaned_count} expired files");
        }
        
        if (!empty($errors)) {
            error_log("Upload plugin cleanup errors: " . implode('; ', $errors));
        }
        
    } catch (Exception $e) {
        error_log('Upload plugin expiration check failed: ' . $e->getMessage());
    }
}

/**
 * Clean up a single expired file
 * 
 * @param object $file File data
 * @return array Cleanup result
 */
function upload_cleanup_expired_file($file) {
    $result = [
        'success' => false,
        'error' => null
    ];
    
    try {
        // Delete file from storage
        if (upload_file_exists($file->file_path)) {
            if (!upload_delete_file_from_storage($file->file_path)) {
                $result['error'] = 'Failed to delete file from storage';
                return $result;
            }
        }
        
        // Remove short URL from YOURLS database
        $keyword = yourls_get_keyword_from_url($file->short_url);
        if ($keyword) {
            $delete_result = yourls_delete_url_by_keyword($keyword);
            if (!$delete_result) {
                $result['error'] = 'Failed to delete short URL from YOURLS';
                return $result;
            }
        }
        
        // Delete file record from our database
        if (!upload_delete_file_record($file->id)) {
            $result['error'] = 'Failed to delete file record';
            return $result;
        }
        
        $result['success'] = true;
        return $result;
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
        return $result;
    }
}

/**
 * Get files expiring soon (within specified days)
 * 
 * @param int $days Number of days
 * @param int $limit Maximum number of files
 * @return array Files expiring soon
 */
function upload_get_files_expiring_soon($days = 7, $limit = 50) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE expiration_date IS NOT NULL 
                AND expiration_date > NOW() 
                AND expiration_date <= DATE_ADD(NOW(), INTERVAL %d DAY)
                ORDER BY expiration_date ASC 
                LIMIT %d";
        
        return $ydb->get_results($sql, $days, $limit);
        
    } catch (Exception $e) {
        error_log('Upload plugin: Failed to get expiring files - ' . $e->getMessage());
        return [];
    }
}

/**
 * Extend file expiration
 * 
 * @param int $file_id File ID
 * @param int $days Number of days to extend
 * @return bool Success status
 */
function upload_extend_file_expiration($file_id, $days) {
    try {
        $file = upload_get_file_by_id($file_id);
        if (!$file) {
            return false;
        }
        
        $current_expiration = $file->expiration_date;
        $new_expiration = null;
        
        if ($current_expiration) {
            // Extend existing expiration
            $new_expiration = date('Y-m-d H:i:s', strtotime($current_expiration . " +{$days} days"));
        } else {
            // Set new expiration
            $new_expiration = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }
        
        return upload_update_file_record($file_id, ['expiration_date' => $new_expiration]);
        
    } catch (Exception $e) {
        error_log('Upload plugin: Failed to extend file expiration - ' . $e->getMessage());
        return false;
    }
}

/**
 * Remove file expiration (make permanent)
 * 
 * @param int $file_id File ID
 * @return bool Success status
 */
function upload_remove_file_expiration($file_id) {
    try {
        return upload_update_file_record($file_id, ['expiration_date' => null]);
    } catch (Exception $e) {
        error_log('Upload plugin: Failed to remove file expiration - ' . $e->getMessage());
        return false;
    }
}

/**
 * Get expiration statistics
 * 
 * @return array Expiration statistics
 */
function upload_get_expiration_stats() {
    global $ydb;
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    COUNT(CASE WHEN expiration_date IS NULL THEN 1 END) as permanent_files,
                    COUNT(CASE WHEN expiration_date IS NOT NULL AND expiration_date > NOW() THEN 1 END) as active_files,
                    COUNT(CASE WHEN expiration_date IS NOT NULL AND expiration_date <= NOW() THEN 1 END) as expired_files,
                    COUNT(CASE WHEN expiration_date IS NOT NULL AND expiration_date > NOW() AND expiration_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 END) as expiring_soon
                FROM `" . YOURLS_DB_PREFIX . "upload_files`";
        
        return $ydb->get_row($sql);
        
    } catch (Exception $e) {
        error_log('Upload plugin: Failed to get expiration stats - ' . $e->getMessage());
        return null;
    }
}

/**
 * Schedule immediate cleanup (for admin use)
 * 
 * @return array Cleanup result
 */
function upload_schedule_immediate_cleanup() {
    $result = [
        'success' => false,
        'cleaned_count' => 0,
        'errors' => []
    ];
    
    try {
        // Get expired files
        $expired_files = upload_get_expired_files(200); // Limit for immediate cleanup
        
        if (empty($expired_files)) {
            $result['success'] = true;
            return $result;
        }
        
        $cleaned_count = 0;
        $errors = [];
        
        foreach ($expired_files as $file) {
            $cleanup_result = upload_cleanup_expired_file($file);
            
            if ($cleanup_result['success']) {
                $cleaned_count++;
            } else {
                $errors[] = "File ID {$file->id}: " . $cleanup_result['error'];
            }
        }
        
        $result['success'] = true;
        $result['cleaned_count'] = $cleaned_count;
        $result['errors'] = $errors;
        
        // Update last cleanup time
        upload_update_setting('last_cleanup', time());
        
        return $result;
        
    } catch (Exception $e) {
        $result['errors'][] = $e->getMessage();
        return $result;
    }
}

/**
 * Get cleanup history
 * 
 * @param int $limit Limit
 * @return array Cleanup history
 */
function upload_get_cleanup_history($limit = 20) {
    // This would typically be stored in a separate table
    // For now, we'll return basic info from settings
    $last_cleanup = upload_get_setting('last_cleanup', 0);
    
    if ($last_cleanup) {
        return [
            [
                'timestamp' => $last_cleanup,
                'date' => date('Y-m-d H:i:s', $last_cleanup),
                'type' => 'scheduled'
            ]
        ];
    }
    
    return [];
}

/**
 * Check if cleanup is needed
 * 
 * @return bool Cleanup needed
 */
function upload_is_cleanup_needed() {
    $last_cleanup = upload_get_setting('last_cleanup', 0);
    
    // Run cleanup if it hasn't been run in the last 2 hours
    if (time() - $last_cleanup > 7200) {
        return true;
    }
    
    // Also check if there are many expired files
    $expired_count = upload_get_expired_files_count();
    return $expired_count > 10;
}

/**
 * Get count of expired files
 * 
 * @return int Expired files count
 */
function upload_get_expired_files_count() {
    global $ydb;
    
    try {
        $sql = "SELECT COUNT(*) FROM `" . YOURLS_DB_PREFIX . "upload_files` 
                WHERE expiration_date IS NOT NULL AND expiration_date <= NOW()";
        
        return (int) $ydb->get_var($sql);
        
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get cleanup statistics
 * 
 * @return array Cleanup statistics
 */
function upload_get_cleanup_stats() {
    $stats = [
        'last_cleanup' => upload_get_setting('last_cleanup', 0),
        'expired_files' => upload_get_expired_files_count(),
        'expiring_soon' => count(upload_get_files_expiring_soon(7)),
        'cleanup_needed' => upload_is_cleanup_needed()
    ];
    
    if ($stats['last_cleanup']) {
        $stats['last_cleanup_date'] = date('Y-m-d H:i:s', $stats['last_cleanup']);
        $stats['hours_since_cleanup'] = round((time() - $stats['last_cleanup']) / 3600, 1);
    }
    
    return $stats;
}

/**
 * Set up expiration hooks
 */
function upload_setup_expiration_hooks() {
    // Hook into YOURLS shutdown to check for expired files
    yourls_add_action('shutdown', 'upload_check_expired_files');
    
    // Hook into admin page load to show expiration warnings
    yourls_add_action('admin_init', 'upload_check_expiration_warnings');
}

/**
 * Check for expiration warnings in admin
 */
function upload_check_expiration_warnings() {
    // Only show warnings to admin users
    if (!yourls_is_admin()) {
        return;
    }
    
    $stats = upload_get_cleanup_stats();
    
    // Show warning if cleanup is needed
    if ($stats['cleanup_needed']) {
        $message = sprintf(
            __('Upload plugin: %d expired files need cleanup. <a href="%s">Run cleanup now</a>.', 'upload-and-shorten'),
            $stats['expired_files'],
            yourls_admin_url('plugins.php?page=upload-and-shorten&sub=cleanup')
        );
        
        yourls_add_notice($message, 'warning');
    }
    
    // Show warning if many files are expiring soon
    if ($stats['expiring_soon'] > 20) {
        $message = sprintf(
            __('Upload plugin: %d files are expiring within 7 days.', 'upload-and-shorten'),
            $stats['expiring_soon']
        );
        
        yourls_add_notice($message, 'info');
    }
}

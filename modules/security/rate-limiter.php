<?php
/**
 * Rate Limiter Module
 * Rate limiting for uploads and API calls
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Check rate limit for IP address
 * 
 * @param string $ip IP address
 * @param string $action Action type
 * @param int $limit Rate limit
 * @param int $window Time window in seconds
 * @return bool Within rate limit
 */
function upload_check_rate_limit($ip = null, $action = 'upload', $limit = null, $window = null) {
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    if ($limit === null) {
        $frontend_settings = upload_get_frontend_settings();
        $limit = $frontend_settings['rate_limit_uploads'];
    }
    
    if ($window === null) {
        $frontend_settings = upload_get_frontend_settings();
        $window = $frontend_settings['rate_limit_window'];
    }
    
    if ($limit <= 0) {
        return true; // No rate limiting
    }
    
    $key = 'upload_rate_limit_' . md5($ip . '_' . $action);
    
    // Get current count
    $current_count = (int) get_transient($key);
    
    if ($current_count >= $limit) {
        return false;
    }
    
    // Increment count
    set_transient($key, $current_count + 1, $window);
    
    return true;
}

/**
 * Get rate limit status
 * 
 * @param string $ip IP address
 * @param string $action Action type
 * @return array Rate limit status
 */
function upload_get_rate_limit_status($ip = null, $action = 'upload') {
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    $frontend_settings = upload_get_frontend_settings();
    $limit = $frontend_settings['rate_limit_uploads'];
    $window = $frontend_settings['rate_limit_window'];
    
    $key = 'upload_rate_limit_' . md5($ip . '_' . $action);
    $current_count = (int) get_transient($key);
    
    return [
        'current' => $current_count,
        'limit' => $limit,
        'remaining' => max(0, $limit - $current_count),
        'window' => $window,
        'reset_time' => time() + $window
    ];
}

/**
 * Clear rate limit for IP
 * 
 * @param string $ip IP address
 * @param string $action Action type
 * @return bool Success status
 */
function upload_clear_rate_limit($ip = null, $action = 'upload') {
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    $key = 'upload_rate_limit_' . md5($ip . '_' . $action);
    return delete_transient($key);
}

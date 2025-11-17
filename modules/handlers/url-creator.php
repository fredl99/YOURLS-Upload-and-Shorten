<?php
/**
 * URL Creator Module
 * Wrapper for YOURLS URL creation with enhanced features
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Create short URL with enhanced error handling
 * 
 * @param string $url Long URL
 * @param string $keyword Custom keyword (optional)
 * @param string $title Title (optional)
 * @param array $options Additional options
 * @return array Result array
 */
function upload_create_short_url($url, $keyword = '', $title = '', $options = []) {
    $result = [
        'success' => false,
        'shorturl' => null,
        'message' => '',
        'status' => 'fail',
        'title' => $title,
        'url' => $url,
        'keyword' => $keyword
    ];
    
    try {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $result['message'] = __('Invalid URL format', 'upload-and-shorten');
            return $result;
        }
        
        // Check if URL already exists
        if (empty($keyword)) {
            $existing = yourls_get_keyword_from_url($url);
            if ($existing) {
                $result['success'] = true;
                $result['shorturl'] = yourls_site_url() . '/' . $existing;
                $result['message'] = __('URL already exists', 'upload-and-shorten');
                $result['status'] = 'success';
                return $result;
            }
        }
        
        // Validate keyword if provided
        if (!empty($keyword)) {
            $keyword_validation = upload_validate_keyword($keyword);
            if (!$keyword_validation['valid']) {
                $result['message'] = $keyword_validation['error'];
                return $result;
            }
        }
        
        // Create short URL using YOURLS function
        $yourls_result = yourls_add_new_link($url, $keyword, $title);
        
        if ($yourls_result && isset($yourls_result['shorturl'])) {
            $result['success'] = true;
            $result['shorturl'] = $yourls_result['shorturl'];
            $result['message'] = $yourls_result['message'] ?? __('URL created successfully', 'upload-and-shorten');
            $result['status'] = $yourls_result['status'] ?? 'success';
            $result['keyword'] = $yourls_result['keyword'] ?? $keyword;
        } else {
            $result['message'] = $yourls_result['message'] ?? __('Failed to create short URL', 'upload-and-shorten');
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Upload plugin URL creation error: ' . $e->getMessage());
        $result['message'] = __('URL creation failed', 'upload-and-shorten');
        return $result;
    }
}

/**
 * Validate custom keyword
 * 
 * @param string $keyword Keyword to validate
 * @return array Validation result
 */
function upload_validate_keyword($keyword) {
    $result = [
        'valid' => false,
        'error' => ''
    ];
    
    // Check if keyword is empty
    if (empty($keyword)) {
        $result['error'] = __('Keyword cannot be empty', 'upload-and-shorten');
        return $result;
    }
    
    // Check length
    if (strlen($keyword) < 1 || strlen($keyword) > 200) {
        $result['error'] = __('Keyword must be between 1 and 200 characters', 'upload-and-shorten');
        return $result;
    }
    
    // Check for invalid characters
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $keyword)) {
        $result['error'] = __('Keyword can only contain letters, numbers, hyphens, and underscores', 'upload-and-shorten');
        return $result;
    }
    
    // Check if keyword already exists
    if (yourls_keyword_is_taken($keyword)) {
        $result['error'] = __('Keyword is already taken', 'upload-and-shorten');
        return $result;
    }
    
    // Check against reserved keywords
    $reserved_keywords = yourls_get_reserved_keywords();
    if (in_array($keyword, $reserved_keywords)) {
        $result['error'] = __('Keyword is reserved', 'upload-and-shorten');
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Generate random keyword
 * 
 * @param int $length Keyword length
 * @param string $prefix Optional prefix
 * @return string Generated keyword
 */
function upload_generate_keyword($length = 6, $prefix = '') {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $keyword = $prefix;
    
    for ($i = 0; $i < $length; $i++) {
        $keyword .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    // Check if keyword is available
    if (yourls_keyword_is_taken($keyword)) {
        // Try again with different random values
        return upload_generate_keyword($length, $prefix);
    }
    
    return $keyword;
}

/**
 * Get URL statistics
 * 
 * @param string $short_url Short URL
 * @return array URL statistics
 */
function upload_get_url_stats($short_url) {
    $keyword = yourls_get_keyword_from_url($short_url);
    
    if (!$keyword) {
        return null;
    }
    
    try {
        $url_data = yourls_get_url_stats($keyword);
        
        if (!$url_data) {
            return null;
        }
        
        return [
            'keyword' => $keyword,
            'url' => $url_data['url'],
            'title' => $url_data['title'],
            'timestamp' => $url_data['timestamp'],
            'ip' => $url_data['ip'],
            'clicks' => $url_data['clicks'],
            'created' => date('Y-m-d H:i:s', $url_data['timestamp'])
        ];
        
    } catch (Exception $e) {
        error_log('Upload plugin URL stats error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Delete short URL
 * 
 * @param string $short_url Short URL
 * @return bool Success status
 */
function upload_delete_short_url($short_url) {
    try {
        $keyword = yourls_get_keyword_from_url($short_url);
        
        if (!$keyword) {
            return false;
        }
        
        return yourls_delete_url_by_keyword($keyword);
        
    } catch (Exception $e) {
        error_log('Upload plugin URL deletion error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update short URL
 * 
 * @param string $short_url Short URL
 * @param array $updates Updates to apply
 * @return bool Success status
 */
function upload_update_short_url($short_url, $updates) {
    try {
        $keyword = yourls_get_keyword_from_url($short_url);
        
        if (!$keyword) {
            return false;
        }
        
        // Get current URL data
        $url_data = yourls_get_url_stats($keyword);
        if (!$url_data) {
            return false;
        }
        
        // Update title if provided
        if (isset($updates['title'])) {
            global $ydb;
            $sql = "UPDATE `" . YOURLS_DB_PREFIX . "url` SET `title` = %s WHERE `keyword` = %s";
            $result = $ydb->query($sql, $updates['title'], $keyword);
            
            if (!$result) {
                return false;
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log('Upload plugin URL update error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if URL exists
 * 
 * @param string $url Long URL
 * @return string|false Existing short URL or false
 */
function upload_url_exists($url) {
    try {
        $keyword = yourls_get_keyword_from_url($url);
        
        if ($keyword) {
            return yourls_site_url() . '/' . $keyword;
        }
        
        return false;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get URL by keyword
 * 
 * @param string $keyword Short URL keyword
 * @return array|false URL data or false
 */
function upload_get_url_by_keyword($keyword) {
    try {
        $url_data = yourls_get_url_stats($keyword);
        
        if ($url_data) {
            return [
                'keyword' => $keyword,
                'url' => $url_data['url'],
                'title' => $url_data['title'],
                'timestamp' => $url_data['timestamp'],
                'ip' => $url_data['ip'],
                'clicks' => $url_data['clicks'],
                'shorturl' => yourls_site_url() . '/' . $keyword
            ];
        }
        
        return false;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Search URLs
 * 
 * @param string $query Search query
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array Search results
 */
function upload_search_urls($query, $limit = 20, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "url` 
                WHERE `url` LIKE %s OR `title` LIKE %s OR `keyword` LIKE %s
                ORDER BY `timestamp` DESC 
                LIMIT %d OFFSET %d";
        
        $search_term = '%' . $ydb->escape($query) . '%';
        $results = $ydb->get_results($sql, $search_term, $search_term, $search_term, $limit, $offset);
        
        $urls = [];
        foreach ($results as $result) {
            $urls[] = [
                'keyword' => $result->keyword,
                'url' => $result->url,
                'title' => $result->title,
                'timestamp' => $result->timestamp,
                'ip' => $result->ip,
                'clicks' => $result->clicks,
                'shorturl' => yourls_site_url() . '/' . $result->keyword
            ];
        }
        
        return $urls;
        
    } catch (Exception $e) {
        error_log('Upload plugin URL search error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get recent URLs
 * 
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array Recent URLs
 */
function upload_get_recent_urls($limit = 20, $offset = 0) {
    global $ydb;
    
    try {
        $sql = "SELECT * FROM `" . YOURLS_DB_PREFIX . "url` 
                ORDER BY `timestamp` DESC 
                LIMIT %d OFFSET %d";
        
        $results = $ydb->get_results($sql, $limit, $offset);
        
        $urls = [];
        foreach ($results as $result) {
            $urls[] = [
                'keyword' => $result->keyword,
                'url' => $result->url,
                'title' => $result->title,
                'timestamp' => $result->timestamp,
                'ip' => $result->ip,
                'clicks' => $result->clicks,
                'shorturl' => yourls_site_url() . '/' . $result->keyword
            ];
        }
        
        return $urls;
        
    } catch (Exception $e) {
        error_log('Upload plugin recent URLs error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get URL statistics summary
 * 
 * @return array Statistics summary
 */
function upload_get_url_stats_summary() {
    global $ydb;
    
    try {
        $sql = "SELECT 
                    COUNT(*) as total_urls,
                    SUM(clicks) as total_clicks,
                    AVG(clicks) as avg_clicks,
                    MAX(clicks) as max_clicks,
                    COUNT(CASE WHEN clicks = 0 THEN 1 END) as unused_urls
                FROM `" . YOURLS_DB_PREFIX . "url`";
        
        $stats = $ydb->get_row($sql);
        
        // Get recent URLs count (last 24 hours)
        $recent_sql = "SELECT COUNT(*) FROM `" . YOURLS_DB_PREFIX . "url` 
                      WHERE timestamp >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))";
        $recent_urls = (int) $ydb->get_var($recent_sql);
        
        $stats->recent_urls = $recent_urls;
        
        return $stats;
        
    } catch (Exception $e) {
        error_log('Upload plugin URL stats summary error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Validate URL format and accessibility
 * 
 * @param string $url URL to validate
 * @return array Validation result
 */
function upload_validate_url($url) {
    $result = [
        'valid' => false,
        'accessible' => false,
        'error' => ''
    ];
    
    // Check URL format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $result['error'] = __('Invalid URL format', 'upload-and-shorten');
        return $result;
    }
    
    $result['valid'] = true;
    
    // Check if URL is accessible (optional, can be disabled for performance)
    if (defined('UPLOAD_VALIDATE_URL_ACCESSIBILITY') && UPLOAD_VALIDATE_URL_ACCESSIBILITY) {
        $headers = @get_headers($url, 1);
        
        if ($headers && strpos($headers[0], '200') !== false) {
            $result['accessible'] = true;
        } else {
            $result['error'] = __('URL is not accessible', 'upload-and-shorten');
        }
    } else {
        $result['accessible'] = true; // Assume accessible if not checking
    }
    
    return $result;
}

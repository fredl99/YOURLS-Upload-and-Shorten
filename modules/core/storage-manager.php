<?php
/**
 * Storage Manager Module
 * Handles storage location management and file operations
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Get storage location by key
 * 
 * @param string $key Storage location key
 * @return array|null Storage location data
 */
function upload_get_storage_location($key) {
    $locations = upload_get_storage_locations();
    return $locations[$key] ?? null;
}

/**
 * Get storage path for location
 * 
 * @param string $key Storage location key
 * @return string|null Storage path
 */
function upload_get_storage_path($key) {
    $location = upload_get_storage_location($key);
    return $location['path'] ?? null;
}

/**
 * Get web URL for storage location
 * 
 * @param string $key Storage location key
 * @return string|null Web URL
 */
function upload_get_storage_url($key) {
    $location = upload_get_storage_location($key);
    return $location['url'] ?? null;
}

/**
 * Check if storage location is enabled
 * 
 * @param string $key Storage location key
 * @return bool Is enabled
 */
function upload_is_storage_enabled($key) {
    $location = upload_get_storage_location($key);
    return $location['enabled'] ?? false;
}

/**
 * Create storage directory if it doesn't exist
 * 
 * @param string $path Directory path
 * @param int $permissions Directory permissions
 * @return bool Success status
 */
function upload_create_storage_directory($path, $permissions = 0755) {
    if (file_exists($path)) {
        return is_dir($path) && is_writable($path);
    }
    
    // Create directory recursively
    if (!mkdir($path, $permissions, true)) {
        return false;
    }
    
    // Create .htaccess for security
    upload_create_storage_htaccess($path);
    
    return true;
}

/**
 * Create .htaccess file for storage directory
 * 
 * @param string $path Directory path
 * @return bool Success status
 */
function upload_create_storage_htaccess($path) {
    $htaccess_content = "# Prevent PHP execution
<FilesMatch \"\\.(php|php3|php4|php5|phtml)$\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes

# Allow file downloads
<FilesMatch \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|zip|txt|mp3|mp4)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options nosniff
    Header set X-Frame-Options DENY
</IfModule>";

    $htaccess_path = rtrim($path, '/') . '/.htaccess';
    
    return file_put_contents($htaccess_path, $htaccess_content) !== false;
}

/**
 * Move uploaded file to storage location
 * 
 * @param string $tmp_path Temporary file path
 * @param string $storage_key Storage location key
 * @param string $filename Target filename
 * @return array Result with success status and file path
 */
function upload_move_file_to_storage($tmp_path, $storage_key, $filename) {
    $result = [
        'success' => false,
        'file_path' => null,
        'web_url' => null,
        'error' => null
    ];
    
    // Get storage location
    $storage_path = upload_get_storage_path($storage_key);
    if (!$storage_path) {
        $result['error'] = 'Invalid storage location';
        return $result;
    }
    
    // Ensure storage directory exists
    if (!upload_create_storage_directory($storage_path)) {
        $result['error'] = 'Could not create storage directory';
        return $result;
    }
    
    // Generate unique filename
    $unique_filename = upload_generate_unique_filename($storage_path, $filename);
    $target_path = rtrim($storage_path, '/') . '/' . $unique_filename;
    
    // Move file
    if (move_uploaded_file($tmp_path, $target_path)) {
        $result['success'] = true;
        $result['file_path'] = $target_path;
        $result['web_url'] = upload_get_storage_url($storage_key) . $unique_filename;
    } else {
        $result['error'] = 'Failed to move uploaded file';
    }
    
    return $result;
}

/**
 * Delete file from storage
 * 
 * @param string $file_path File path
 * @return bool Success status
 */
function upload_delete_file_from_storage($file_path) {
    if (!file_exists($file_path)) {
        return true; // File doesn't exist, consider it deleted
    }
    
    if (!is_file($file_path)) {
        return false; // Not a file
    }
    
    return unlink($file_path);
}

/**
 * Get file size from storage
 * 
 * @param string $file_path File path
 * @return int|false File size in bytes or false
 */
function upload_get_file_size($file_path) {
    if (!file_exists($file_path) || !is_file($file_path)) {
        return false;
    }
    
    return filesize($file_path);
}

/**
 * Check if file exists in storage
 * 
 * @param string $file_path File path
 * @return bool File exists
 */
function upload_file_exists($file_path) {
    return file_exists($file_path) && is_file($file_path);
}

/**
 * Get storage usage statistics
 * 
 * @param string $storage_key Storage location key
 * @return array Storage statistics
 */
function upload_get_storage_stats($storage_key) {
    $storage_path = upload_get_storage_path($storage_key);
    
    if (!$storage_path || !is_dir($storage_path)) {
        return [
            'total_files' => 0,
            'total_size' => 0,
            'available_space' => 0
        ];
    }
    
    $total_files = 0;
    $total_size = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($storage_path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $total_files++;
            $total_size += $file->getSize();
        }
    }
    
    return [
        'total_files' => $total_files,
        'total_size' => $total_size,
        'available_space' => disk_free_space($storage_path)
    ];
}

/**
 * Clean up old files from storage
 * 
 * @param string $storage_key Storage location key
 * @param int $max_age_days Maximum age in days
 * @return int Number of files cleaned up
 */
function upload_cleanup_old_files($storage_key, $max_age_days = 30) {
    $storage_path = upload_get_storage_path($storage_key);
    
    if (!$storage_path || !is_dir($storage_path)) {
        return 0;
    }
    
    $cutoff_time = time() - ($max_age_days * 24 * 60 * 60);
    $cleaned_count = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($storage_path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getMTime() < $cutoff_time) {
            if (unlink($file->getPathname())) {
                $cleaned_count++;
            }
        }
    }
    
    return $cleaned_count;
}

/**
 * Get list of files in storage
 * 
 * @param string $storage_key Storage location key
 * @param int $limit Maximum number of files
 * @param int $offset Offset for pagination
 * @return array File list
 */
function upload_get_storage_files($storage_key, $limit = 50, $offset = 0) {
    $storage_path = upload_get_storage_path($storage_key);
    
    if (!$storage_path || !is_dir($storage_path)) {
        return [];
    }
    
    $files = [];
    $count = 0;
    $skipped = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($storage_path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            if ($skipped < $offset) {
                $skipped++;
                continue;
            }
            
            if ($count >= $limit) {
                break;
            }
            
            $files[] = [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
                'extension' => $file->getExtension()
            ];
            
            $count++;
        }
    }
    
    return $files;
}

/**
 * Validate storage location configuration
 * 
 * @param array $location Location configuration
 * @return array Validation result
 */
function upload_validate_storage_location($location) {
    $result = [
        'valid' => false,
        'errors' => [],
        'warnings' => []
    ];
    
    // Check required fields
    if (empty($location['name'])) {
        $result['errors'][] = 'Location name is required';
    }
    
    if (empty($location['path'])) {
        $result['errors'][] = 'Storage path is required';
    }
    
    if (empty($location['url'])) {
        $result['errors'][] = 'Web URL is required';
    }
    
    if (!empty($result['errors'])) {
        return $result;
    }
    
    // Validate path
    $path_validation = upload_validate_storage_path($location['path']);
    if (!$path_validation['valid']) {
        $result['errors'][] = $path_validation['message'];
        return $result;
    }
    
    // Validate URL format
    if (!filter_var($location['url'], FILTER_VALIDATE_URL)) {
        $result['errors'][] = 'Invalid web URL format';
        return $result;
    }
    
    // Check if URL ends with slash
    if (!preg_match('/\/$/', $location['url'])) {
        $result['warnings'][] = 'Web URL should end with a slash';
    }
    
    // Check if path ends with slash
    if (!preg_match('/\/$/', $location['path'])) {
        $result['warnings'][] = 'Storage path should end with a slash';
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Test storage location
 * 
 * @param string $key Storage location key
 * @return array Test result
 */
function upload_test_storage_location($key) {
    $result = [
        'success' => false,
        'errors' => [],
        'info' => []
    ];
    
    $location = upload_get_storage_location($key);
    if (!$location) {
        $result['errors'][] = 'Storage location not found';
        return $result;
    }
    
    // Validate configuration
    $validation = upload_validate_storage_location($location);
    if (!$validation['valid']) {
        $result['errors'] = $validation['errors'];
        return $result;
    }
    
    // Test directory creation
    if (!upload_create_storage_directory($location['path'])) {
        $result['errors'][] = 'Could not create or access storage directory';
        return $result;
    }
    
    // Test file write
    $test_file = rtrim($location['path'], '/') . '/test_' . time() . '.txt';
    $test_content = 'Test file created at ' . date('Y-m-d H:i:s');
    
    if (file_put_contents($test_file, $test_content) === false) {
        $result['errors'][] = 'Could not write test file';
        return $result;
    }
    
    // Test file read
    if (file_get_contents($test_file) !== $test_content) {
        $result['errors'][] = 'Could not read test file';
        unlink($test_file);
        return $result;
    }
    
    // Clean up test file
    unlink($test_file);
    
    // Get storage info
    $stats = upload_get_storage_stats($key);
    $result['info'] = [
        'total_files' => $stats['total_files'],
        'total_size' => upload_format_file_size($stats['total_size']),
        'available_space' => upload_format_file_size($stats['available_space'])
    ];
    
    $result['success'] = true;
    return $result;
}

/**
 * Get overall storage statistics
 * 
 * @return array Storage statistics
 */
function upload_get_storage_statistics() {
    $locations = upload_get_storage_locations();
    $total_files = 0;
    $total_size = 0;
    $available_space = PHP_INT_MAX;
    
    foreach ($locations as $key => $location) {
        if ($location['enabled']) {
            $stats = upload_get_storage_stats($key);
            $total_files += $stats['total_files'];
            $total_size += $stats['total_size'];
            $available_space = min($available_space, $stats['available_space']);
        }
    }
    
    return [
        'total_files' => $total_files,
        'total_size' => $total_size,
        'available_space' => $available_space === PHP_INT_MAX ? 0 : $available_space
    ];
}


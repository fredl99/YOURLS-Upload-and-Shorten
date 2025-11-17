<?php
/**
 * Path Validator Module
 * Path traversal and security validation
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Validate file path for security
 * 
 * @param string $path File path
 * @return array Validation result
 */
function upload_validate_path($path) {
    $result = [
        'valid' => false,
        'error' => '',
        'normalized_path' => ''
    ];
    
    // Check for null bytes
    if (strpos($path, "\0") !== false) {
        $result['error'] = 'Path contains null bytes';
        return $result;
    }
    
    // Normalize path
    $normalized = realpath($path);
    if ($normalized === false) {
        $result['error'] = 'Invalid path';
        return $result;
    }
    
    $result['normalized_path'] = $normalized;
    
    // Check for directory traversal
    if (strpos($normalized, '..') !== false) {
        $result['error'] = 'Path traversal detected';
        return $result;
    }
    
    // Check if path is within allowed directories
    $allowed_dirs = upload_get_allowed_directories();
    $is_allowed = false;
    
    foreach ($allowed_dirs as $allowed_dir) {
        if (strpos($normalized, $allowed_dir) === 0) {
            $is_allowed = true;
            break;
        }
    }
    
    if (!$is_allowed) {
        $result['error'] = 'Path outside allowed directories';
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Get allowed directories for file operations
 * 
 * @return array Allowed directories
 */
function upload_get_allowed_directories() {
    $directories = [
        YOURLS_ABSPATH . 'user/plugins/YOURLS-Upload-and-Shorten-master/uploads/',
        sys_get_temp_dir()
    ];
    
    // Add storage locations
    $storage_locations = upload_get_storage_locations();
    foreach ($storage_locations as $location) {
        if ($location['enabled'] && !empty($location['path'])) {
            $directories[] = $location['path'];
        }
    }
    
    return array_unique($directories);
}

/**
 * Sanitize filename for security
 * 
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function upload_sanitize_filename_security($filename) {
    // Remove null bytes
    $filename = str_replace("\0", '', $filename);
    
    // Remove directory traversal attempts
    $filename = str_replace(['../', '..\\'], '', $filename);
    
    // Remove control characters
    $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
    
    // Limit length
    if (strlen($filename) > 255) {
        $path_info = pathinfo($filename);
        $name = substr($path_info['filename'], 0, 200);
        $extension = $path_info['extension'] ?? '';
        $filename = $name . ($extension ? '.' . $extension : '');
    }
    
    return $filename;
}

/**
 * Check if file extension is dangerous
 * 
 * @param string $extension File extension
 * @return bool Is dangerous
 */
function upload_is_dangerous_extension($extension) {
    $dangerous_extensions = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'pht', 'phar',
        'exe', 'bat', 'cmd', 'com', 'scr', 'pif',
        'sh', 'bash', 'csh', 'ksh', 'zsh', 'fish',
        'ps1', 'vbs', 'js', 'jar', 'app', 'deb', 'rpm', 'msi'
    ];
    
    return in_array(strtolower($extension), $dangerous_extensions);
}

/**
 * Validate upload directory
 * 
 * @param string $directory Directory path
 * @return array Validation result
 */
function upload_validate_upload_directory($directory) {
    $result = [
        'valid' => false,
        'writable' => false,
        'secure' => false,
        'error' => ''
    ];
    
    // Check if directory exists
    if (!is_dir($directory)) {
        $result['error'] = 'Directory does not exist';
        return $result;
    }
    
    // Check if writable
    if (!is_writable($directory)) {
        $result['error'] = 'Directory is not writable';
        return $result;
    }
    
    $result['writable'] = true;
    
    // Check if directory is secure (outside web root or properly protected)
    $web_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (!empty($web_root) && strpos(realpath($directory), realpath($web_root)) === 0) {
        // Directory is within web root, check for .htaccess protection
        $htaccess_file = $directory . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $result['error'] = 'Directory is within web root but not protected';
            return $result;
        }
    }
    
    $result['secure'] = true;
    $result['valid'] = true;
    
    return $result;
}

<?php
/**
 * Error Logger Module
 * Centralized error logging
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Log error message
 * 
 * @param string $message Error message
 * @param string $level Log level
 * @param array $context Additional context
 * @return void
 */
function upload_log_error($message, $level = 'ERROR', $context = []) {
    $log_entry = upload_format_log_entry($message, $level, $context);
    upload_write_log($log_entry);
}

/**
 * Log warning message
 * 
 * @param string $message Warning message
 * @param array $context Additional context
 * @return void
 */
function upload_log_warning($message, $context = []) {
    upload_log_error($message, 'WARNING', $context);
}

/**
 * Log info message
 * 
 * @param string $message Info message
 * @param array $context Additional context
 * @return void
 */
function upload_log_info($message, $context = []) {
    upload_log_error($message, 'INFO', $context);
}

/**
 * Log debug message
 * 
 * @param string $message Debug message
 * @param array $context Additional context
 * @return void
 */
function upload_log_debug($message, $context = []) {
    if (defined('UPLOAD_DEBUG') && UPLOAD_DEBUG) {
        upload_log_error($message, 'DEBUG', $context);
    }
}

/**
 * Format log entry
 * 
 * @param string $message Log message
 * @param string $level Log level
 * @param array $context Additional context
 * @return string Formatted log entry
 */
function upload_format_log_entry($message, $level, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $request_uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
    
    $log_entry = "[{$timestamp}] [{$level}] [{$ip}] {$message}";
    
    if (!empty($context)) {
        $log_entry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    
    $log_entry .= " | URI: {$request_uri} | UA: {$user_agent}";
    $log_entry .= PHP_EOL;
    
    return $log_entry;
}

/**
 * Write log entry to file
 * 
 * @param string $log_entry Log entry
 * @return void
 */
function upload_write_log($log_entry) {
    $log_file = upload_get_log_file();
    $log_dir = dirname($log_file);
    
    // Create log directory if it doesn't exist
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Write to log file
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Rotate log file if it's too large
    upload_rotate_log_if_needed($log_file);
}

/**
 * Get log file path
 * 
 * @return string Log file path
 */
function upload_get_log_file() {
    $log_dir = YOURLS_ABSPATH . 'user/logs/';
    return $log_dir . 'upload-plugin.log';
}

/**
 * Rotate log file if it's too large
 * 
 * @param string $log_file Log file path
 * @return void
 */
function upload_rotate_log_if_needed($log_file) {
    $max_size = 10 * 1024 * 1024; // 10MB
    
    if (!file_exists($log_file) || filesize($log_file) < $max_size) {
        return;
    }
    
    $rotated_file = $log_file . '.' . date('Y-m-d-H-i-s');
    rename($log_file, $rotated_file);
    
    // Keep only last 5 rotated files
    upload_cleanup_old_logs(dirname($log_file));
}

/**
 * Clean up old log files
 * 
 * @param string $log_dir Log directory
 * @return void
 */
function upload_cleanup_old_logs($log_dir) {
    $pattern = $log_dir . '/upload-plugin.log.*';
    $log_files = glob($pattern);
    
    if (count($log_files) <= 5) {
        return;
    }
    
    // Sort by modification time (oldest first)
    usort($log_files, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Remove oldest files, keeping only 5
    $files_to_remove = array_slice($log_files, 0, count($log_files) - 5);
    foreach ($files_to_remove as $file) {
        unlink($file);
    }
}

/**
 * Get log entries
 * 
 * @param int $limit Number of entries to retrieve
 * @param string $level Filter by log level
 * @return array Log entries
 */
function upload_get_log_entries($limit = 100, $level = null) {
    $log_file = upload_get_log_file();
    
    if (!file_exists($log_file)) {
        return [];
    }
    
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $entries = [];
    
    // Filter by level if specified
    if ($level) {
        $lines = array_filter($lines, function($line) use ($level) {
            return strpos($line, "[{$level}]") !== false;
        });
    }
    
    // Get last N entries
    $lines = array_slice($lines, -$limit);
    
    foreach ($lines as $line) {
        $entries[] = upload_parse_log_entry($line);
    }
    
    return $entries;
}

/**
 * Parse log entry
 * 
 * @param string $line Log line
 * @return array Parsed log entry
 */
function upload_parse_log_entry($line) {
    $pattern = '/^\[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] (.+?)(?:\s+\|\s+Context:\s+(.+?))?(?:\s+\|\s+URI:\s+(.+?))?(?:\s+\|\s+UA:\s+(.+?))?$/';
    
    if (preg_match($pattern, $line, $matches)) {
        return [
            'timestamp' => $matches[1],
            'level' => $matches[2],
            'ip' => $matches[3],
            'message' => $matches[4],
            'context' => isset($matches[5]) ? json_decode($matches[5], true) : null,
            'uri' => $matches[6] ?? null,
            'user_agent' => $matches[7] ?? null
        ];
    }
    
    return [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => 'UNKNOWN',
        'ip' => 'unknown',
        'message' => $line,
        'context' => null,
        'uri' => null,
        'user_agent' => null
    ];
}

/**
 * Clear log file
 * 
 * @return bool Success status
 */
function upload_clear_log() {
    $log_file = upload_get_log_file();
    
    if (file_exists($log_file)) {
        return unlink($log_file);
    }
    
    return true;
}

/**
 * Get log statistics
 * 
 * @return array Log statistics
 */
function upload_get_log_stats() {
    $log_file = upload_get_log_file();
    
    if (!file_exists($log_file)) {
        return [
            'total_entries' => 0,
            'error_count' => 0,
            'warning_count' => 0,
            'info_count' => 0,
            'debug_count' => 0,
            'file_size' => 0,
            'last_entry' => null
        ];
    }
    
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $stats = [
        'total_entries' => count($lines),
        'error_count' => 0,
        'warning_count' => 0,
        'info_count' => 0,
        'debug_count' => 0,
        'file_size' => filesize($log_file),
        'last_entry' => null
    ];
    
    foreach ($lines as $line) {
        if (strpos($line, '[ERROR]') !== false) {
            $stats['error_count']++;
        } elseif (strpos($line, '[WARNING]') !== false) {
            $stats['warning_count']++;
        } elseif (strpos($line, '[INFO]') !== false) {
            $stats['info_count']++;
        } elseif (strpos($line, '[DEBUG]') !== false) {
            $stats['debug_count']++;
        }
    }
    
    if (!empty($lines)) {
        $last_entry = upload_parse_log_entry(end($lines));
        $stats['last_entry'] = $last_entry['timestamp'];
    }
    
    return $stats;
}

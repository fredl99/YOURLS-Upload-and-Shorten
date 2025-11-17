<?php
/**
 * File Scanner Module
 * Malware and content scanning
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Scan file for malicious content
 * 
 * @param string $file_path File path
 * @return array Scan result
 */
function upload_scan_file($file_path) {
    $result = [
        'safe' => true,
        'threats' => [],
        'warnings' => []
    ];
    
    if (!file_exists($file_path)) {
        $result['safe'] = false;
        $result['threats'][] = 'File does not exist';
        return $result;
    }
    
    // Check file size
    $file_size = filesize($file_path);
    if ($file_size > 100 * 1024 * 1024) { // 100MB limit for scanning
        $result['warnings'][] = 'File too large for complete scan';
        return $result;
    }
    
    // Read file content
    $content = file_get_contents($file_path, false, null, 0, min($file_size, 1024 * 1024)); // Read first 1MB
    
    // Check for PHP code
    if (upload_contains_php_code($content)) {
        $result['safe'] = false;
        $result['threats'][] = 'Contains PHP code';
    }
    
    // Check for script tags
    if (upload_contains_script_tags($content)) {
        $result['safe'] = false;
        $result['threats'][] = 'Contains script tags';
    }
    
    // Check for executable signatures
    if (upload_contains_executable_signatures($content)) {
        $result['safe'] = false;
        $result['threats'][] = 'Contains executable signatures';
    }
    
    // Check for suspicious patterns
    $suspicious_patterns = upload_get_suspicious_patterns();
    foreach ($suspicious_patterns as $pattern => $description) {
        if (preg_match($pattern, $content)) {
            $result['warnings'][] = $description;
        }
    }
    
    return $result;
}

/**
 * Check if content contains PHP code
 * 
 * @param string $content File content
 * @return bool Contains PHP code
 */
function upload_contains_php_code($content) {
    $php_patterns = [
        '/<\?php/i',
        '/<\?=/i',
        '/\?>/i',
        '/<\?/i'
    ];
    
    foreach ($php_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if content contains script tags
 * 
 * @param string $content File content
 * @return bool Contains script tags
 */
function upload_contains_script_tags($content) {
    return preg_match('/<script[^>]*>/i', $content) || preg_match('/<\/script>/i', $content);
}

/**
 * Check if content contains executable signatures
 * 
 * @param string $content File content
 * @return bool Contains executable signatures
 */
function upload_contains_executable_signatures($content) {
    $executable_signatures = [
        "\x4D\x5A", // PE executable
        "\x7F\x45\x4C\x46", // ELF executable
        "#!", // Shell script
        "\xCA\xFE\xBA\xBE", // Java class file
        "\x50\x4B\x03\x04", // ZIP file (could contain executables)
        "\x52\x61\x72\x21\x1A\x07\x00" // RAR file
    ];
    
    foreach ($executable_signatures as $signature) {
        if (strpos($content, $signature) === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get suspicious patterns to check
 * 
 * @return array Suspicious patterns
 */
function upload_get_suspicious_patterns() {
    return [
        '/eval\s*\(/i' => 'Contains eval() function',
        '/base64_decode\s*\(/i' => 'Contains base64_decode() function',
        '/system\s*\(/i' => 'Contains system() function',
        '/exec\s*\(/i' => 'Contains exec() function',
        '/shell_exec\s*\(/i' => 'Contains shell_exec() function',
        '/passthru\s*\(/i' => 'Contains passthru() function',
        '/file_get_contents\s*\(/i' => 'Contains file_get_contents() function',
        '/fopen\s*\(/i' => 'Contains fopen() function',
        '/fwrite\s*\(/i' => 'Contains fwrite() function',
        '/chmod\s*\(/i' => 'Contains chmod() function',
        '/unlink\s*\(/i' => 'Contains unlink() function',
        '/rmdir\s*\(/i' => 'Contains rmdir() function',
        '/mkdir\s*\(/i' => 'Contains mkdir() function',
        '/move_uploaded_file\s*\(/i' => 'Contains move_uploaded_file() function',
        '/copy\s*\(/i' => 'Contains copy() function',
        '/rename\s*\(/i' => 'Contains rename() function',
        '/include\s*\(/i' => 'Contains include() function',
        '/require\s*\(/i' => 'Contains require() function',
        '/include_once\s*\(/i' => 'Contains include_once() function',
        '/require_once\s*\(/i' => 'Contains require_once() function',
        '/\$_GET\s*\[/i' => 'Contains $_GET usage',
        '/\$_POST\s*\[/i' => 'Contains $_POST usage',
        '/\$_REQUEST\s*\[/i' => 'Contains $_REQUEST usage',
        '/\$_COOKIE\s*\[/i' => 'Contains $_COOKIE usage',
        '/\$_SESSION\s*\[/i' => 'Contains $_SESSION usage',
        '/\$_SERVER\s*\[/i' => 'Contains $_SERVER usage',
        '/\$_ENV\s*\[/i' => 'Contains $_ENV usage',
        '/\$_FILES\s*\[/i' => 'Contains $_FILES usage',
        '/\$_GLOBALS\s*\[/i' => 'Contains $_GLOBALS usage',
        '/mail\s*\(/i' => 'Contains mail() function',
        '/header\s*\(/i' => 'Contains header() function',
        '/setcookie\s*\(/i' => 'Contains setcookie() function',
        '/session_start\s*\(/i' => 'Contains session_start() function',
        '/session_destroy\s*\(/i' => 'Contains session_destroy() function',
        '/session_regenerate_id\s*\(/i' => 'Contains session_regenerate_id() function',
        '/crypt\s*\(/i' => 'Contains crypt() function',
        '/md5\s*\(/i' => 'Contains md5() function',
        '/sha1\s*\(/i' => 'Contains sha1() function',
        '/hash\s*\(/i' => 'Contains hash() function',
        '/password_hash\s*\(/i' => 'Contains password_hash() function',
        '/password_verify\s*\(/i' => 'Contains password_verify() function',
        '/openssl_encrypt\s*\(/i' => 'Contains openssl_encrypt() function',
        '/openssl_decrypt\s*\(/i' => 'Contains openssl_decrypt() function',
        '/curl_exec\s*\(/i' => 'Contains curl_exec() function',
        '/file_get_contents\s*\(/i' => 'Contains file_get_contents() function',
        '/fopen\s*\(/i' => 'Contains fopen() function',
        '/fwrite\s*\(/i' => 'Contains fwrite() function',
        '/fread\s*\(/i' => 'Contains fread() function',
        '/fclose\s*\(/i' => 'Contains fclose() function',
        '/fseek\s*\(/i' => 'Contains fseek() function',
        '/ftell\s*\(/i' => 'Contains ftell() function',
        '/rewind\s*\(/i' => 'Contains rewind() function',
        '/feof\s*\(/i' => 'Contains feof() function',
        '/fgetc\s*\(/i' => 'Contains fgetc() function',
        '/fgets\s*\(/i' => 'Contains fgets() function',
        '/fgetcsv\s*\(/i' => 'Contains fgetcsv() function',
        '/fputcsv\s*\(/i' => 'Contains fputcsv() function',
        '/fscanf\s*\(/i' => 'Contains fscanf() function',
        '/fprintf\s*\(/i' => 'Contains fprintf() function',
        '/fputs\s*\(/i' => 'Contains fputs() function',
        '/fread\s*\(/i' => 'Contains fread() function',
        '/fwrite\s*\(/i' => 'Contains fwrite() function',
        '/fpassthru\s*\(/i' => 'Contains fpassthru() function',
        '/fgetss\s*\(/i' => 'Contains fgetss() function',
        '/fgetcsv\s*\(/i' => 'Contains fgetcsv() function',
        '/fputcsv\s*\(/i' => 'Contains fputcsv() function',
        '/fscanf\s*\(/i' => 'Contains fscanf() function',
        '/fprintf\s*\(/i' => 'Contains fprintf() function',
        '/fputs\s*\(/i' => 'Contains fputs() function',
        '/fread\s*\(/i' => 'Contains fread() function',
        '/fwrite\s*\(/i' => 'Contains fwrite() function',
        '/fpassthru\s*\(/i' => 'Contains fpassthru() function',
        '/fgetss\s*\(/i' => 'Contains fgetss() function'
    ];
}

/**
 * Scan file with external scanner (if available)
 * 
 * @param string $file_path File path
 * @return array Scan result
 */
function upload_scan_file_external($file_path) {
    $result = [
        'safe' => true,
        'threats' => [],
        'warnings' => []
    ];
    
    // Check if ClamAV is available
    if (function_exists('clamav_scan_file')) {
        $scan_result = clamav_scan_file($file_path);
        if ($scan_result !== false) {
            $result['safe'] = false;
            $result['threats'][] = 'Virus detected: ' . $scan_result;
        }
        return $result;
    }
    
    // Check if ClamAV command line is available
    $clamav_path = '/usr/bin/clamscan';
    if (file_exists($clamav_path)) {
        $output = [];
        $return_code = 0;
        exec($clamav_path . ' --no-summary ' . escapeshellarg($file_path) . ' 2>&1', $output, $return_code);
        
        if ($return_code === 1) {
            $result['safe'] = false;
            $result['threats'][] = 'Virus detected: ' . implode(' ', $output);
        }
        return $result;
    }
    
    // No external scanner available
    $result['warnings'][] = 'No external virus scanner available';
    return $result;
}

/**
 * Get file MIME type using multiple methods
 * 
 * @param string $file_path File path
 * @return string MIME type
 */
function upload_get_secure_mime_type($file_path) {
    // Try finfo first (most reliable)
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        if ($mime_type && $mime_type !== 'application/octet-stream') {
            return $mime_type;
        }
    }
    
    // Try mime_content_type as fallback
    if (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file_path);
        if ($mime_type && $mime_type !== 'application/octet-stream') {
            return $mime_type;
        }
    }
    
    // Fallback to extension-based detection
    return upload_get_mime_type_by_extension($file_path);
}

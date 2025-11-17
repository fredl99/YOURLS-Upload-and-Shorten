<?php
/**
 * File Helpers Module
 * File operation utilities
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Format file size for display
 * 
 * @param int $bytes File size in bytes
 * @param int $precision Decimal precision
 * @return string Formatted size
 */
function upload_format_file_size($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Get file extension from filename
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function upload_get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Sanitize filename for safe storage
 * 
 * @param string $filename Original filename
 * @param string $method Sanitization method
 * @return string Sanitized filename
 */
function upload_sanitize_filename($filename, $method = 'browser-safe') {
    $path_info = pathinfo($filename);
    $name = $path_info['filename'] ?? '';
    $extension = $path_info['extension'] ?? '';
    
    switch ($method) {
        case 'original':
            return $filename;
            
        case 'browser-safe':
            // Remove or replace unsafe characters
            $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
            $name = preg_replace('/_{2,}/', '_', $name);
            $name = trim($name, '._-');
            
            if (empty($name)) {
                $name = 'file';
            }
            
            return $extension ? $name . '.' . $extension : $name;
            
        case 'randomized':
            // Generate random filename
            $random = substr(md5($filename . time() . mt_rand()), 0, 12);
            return $extension ? $random . '.' . $extension : $random;
            
        case 'drop-extension':
            return $name;
            
        default:
            return $filename;
    }
}

/**
 * Generate unique filename to avoid conflicts
 * 
 * @param string $directory Target directory
 * @param string $filename Desired filename
 * @return string Unique filename
 */
function upload_generate_unique_filename($directory, $filename) {
    $path_info = pathinfo($filename);
    $name = $path_info['filename'] ?? '';
    $extension = $path_info['extension'] ?? '';
    
    $base_name = $name;
    $counter = 1;
    
    while (file_exists($directory . '/' . $filename)) {
        $filename = $base_name . '.' . $counter . ($extension ? '.' . $extension : '');
        $counter++;
    }
    
    return $filename;
}

/**
 * Check if file is an image
 * 
 * @param string $file_path File path
 * @return bool Is image
 */
function upload_is_image($file_path) {
    $image_types = [
        IMAGETYPE_GIF,
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_BMP,
        IMAGETYPE_WBMP,
        IMAGETYPE_XBM,
        IMAGETYPE_WEBP
    ];
    
    $image_type = exif_imagetype($file_path);
    return $image_type !== false && in_array($image_type, $image_types);
}

/**
 * Get image dimensions
 * 
 * @param string $file_path File path
 * @return array|false Image dimensions or false
 */
function upload_get_image_dimensions($file_path) {
    if (!upload_is_image($file_path)) {
        return false;
    }
    
    $info = getimagesize($file_path);
    if ($info === false) {
        return false;
    }
    
    return [
        'width' => $info[0],
        'height' => $info[1],
        'type' => $info[2],
        'mime' => $info['mime']
    ];
}

/**
 * Create thumbnail for image
 * 
 * @param string $source_path Source image path
 * @param string $thumb_path Thumbnail path
 * @param int $max_width Maximum width
 * @param int $max_height Maximum height
 * @return bool Success status
 */
function upload_create_thumbnail($source_path, $thumb_path, $max_width = 150, $max_height = 150) {
    if (!upload_is_image($source_path)) {
        return false;
    }
    
    $dimensions = upload_get_image_dimensions($source_path);
    if (!$dimensions) {
        return false;
    }
    
    $source_width = $dimensions['width'];
    $source_height = $dimensions['height'];
    
    // Calculate thumbnail dimensions
    $ratio = min($max_width / $source_width, $max_height / $source_height);
    $thumb_width = round($source_width * $ratio);
    $thumb_height = round($source_height * $ratio);
    
    // Create source image resource
    switch ($dimensions['type']) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP:
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // Create thumbnail
    $thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);
    
    // Preserve transparency for PNG and GIF
    if ($dimensions['type'] == IMAGETYPE_PNG || $dimensions['type'] == IMAGETYPE_GIF) {
        imagealphablending($thumb_image, false);
        imagesavealpha($thumb_image, true);
        $transparent = imagecolorallocatealpha($thumb_image, 255, 255, 255, 127);
        imagefilledrectangle($thumb_image, 0, 0, $thumb_width, $thumb_height, $transparent);
    }
    
    // Resize image
    imagecopyresampled($thumb_image, $source_image, 0, 0, 0, 0, $thumb_width, $thumb_height, $source_width, $source_height);
    
    // Save thumbnail
    $result = false;
    switch ($dimensions['type']) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($thumb_image, $thumb_path, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($thumb_image, $thumb_path, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($thumb_image, $thumb_path);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($thumb_image, $thumb_path, 85);
            break;
    }
    
    // Clean up
    imagedestroy($source_image);
    imagedestroy($thumb_image);
    
    return $result;
}

/**
 * Get file hash
 * 
 * @param string $file_path File path
 * @param string $algorithm Hash algorithm
 * @return string|false File hash or false
 */
function upload_get_file_hash($file_path, $algorithm = 'md5') {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $hash = hash_file($algorithm, $file_path);
    return $hash ?: false;
}

/**
 * Check if two files are identical
 * 
 * @param string $file1_path First file path
 * @param string $file2_path Second file path
 * @return bool Files are identical
 */
function upload_files_identical($file1_path, $file2_path) {
    if (!file_exists($file1_path) || !file_exists($file2_path)) {
        return false;
    }
    
    if (filesize($file1_path) !== filesize($file2_path)) {
        return false;
    }
    
    $hash1 = upload_get_file_hash($file1_path, 'md5');
    $hash2 = upload_get_file_hash($file2_path, 'md5');
    
    return $hash1 && $hash2 && $hash1 === $hash2;
}

/**
 * Copy file with progress callback
 * 
 * @param string $source Source file path
 * @param string $destination Destination file path
 * @param callable $progress_callback Progress callback function
 * @return bool Success status
 */
function upload_copy_file_with_progress($source, $destination, $progress_callback = null) {
    if (!file_exists($source)) {
        return false;
    }
    
    $source_handle = fopen($source, 'rb');
    if (!$source_handle) {
        return false;
    }
    
    $destination_handle = fopen($destination, 'wb');
    if (!$destination_handle) {
        fclose($source_handle);
        return false;
    }
    
    $file_size = filesize($source);
    $copied = 0;
    
    while (!feof($source_handle)) {
        $chunk = fread($source_handle, 8192);
        if ($chunk === false) {
            fclose($source_handle);
            fclose($destination_handle);
            return false;
        }
        
        if (fwrite($destination_handle, $chunk) === false) {
            fclose($source_handle);
            fclose($destination_handle);
            return false;
        }
        
        $copied += strlen($chunk);
        
        if ($progress_callback && is_callable($progress_callback)) {
            $progress = ($file_size > 0) ? ($copied / $file_size) * 100 : 0;
            call_user_func($progress_callback, $progress, $copied, $file_size);
        }
    }
    
    fclose($source_handle);
    fclose($destination_handle);
    
    return true;
}

/**
 * Get file MIME type by extension
 * 
 * @param string $file_path File path
 * @return string|null MIME type
 */
function upload_get_mime_type_by_extension($file_path) {
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    $mime_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        '7z' => 'application/x-7z-compressed',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime'
    ];
    
    return $mime_types[$extension] ?? null;
}

<?php
/**
 * Date Helpers Module
 * Date and time utilities
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Calculate expiration date
 * 
 * @param string $expiration_type Expiration type
 * @param int $custom_days Custom days (if applicable)
 * @return string|null Expiration date or null
 */
function upload_calculate_expiration_date($expiration_type, $custom_days = null) {
    if ($expiration_type === 'never') {
        return null;
    }
    
    $days = null;
    
    switch ($expiration_type) {
        case '1':
            $days = 1;
            break;
        case '7':
            $days = 7;
            break;
        case '31':
            $days = 31;
            break;
        case '90':
            $days = 90;
            break;
        case 'custom':
            $days = $custom_days ?? upload_get_custom_expiration_days();
            break;
    }
    
    if ($days !== null) {
        return date('Y-m-d H:i:s', strtotime("+{$days} days"));
    }
    
    return null;
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function upload_format_date($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return __('Never', 'upload-and-shorten');
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    
    return date($format, $timestamp);
}

/**
 * Get human readable time difference
 * 
 * @param string $date Date string
 * @return string Human readable difference
 */
function upload_human_time_diff($date) {
    if (empty($date)) {
        return __('Never', 'upload-and-shorten');
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    
    $now = time();
    $diff = $timestamp - $now;
    
    if ($diff < 0) {
        return __('Expired', 'upload-and-shorten');
    }
    
    $days = floor($diff / (24 * 60 * 60));
    $hours = floor(($diff % (24 * 60 * 60)) / (60 * 60));
    $minutes = floor(($diff % (60 * 60)) / 60);
    
    if ($days > 0) {
        return sprintf(_n('%d day', '%d days', $days, 'upload-and-shorten'), $days);
    } elseif ($hours > 0) {
        return sprintf(_n('%d hour', '%d hours', $hours, 'upload-and-shorten'), $hours);
    } elseif ($minutes > 0) {
        return sprintf(_n('%d minute', '%d minutes', $minutes, 'upload-and-shorten'), $minutes);
    } else {
        return __('Less than a minute', 'upload-and-shorten');
    }
}

/**
 * Check if date is expired
 * 
 * @param string $date Date string
 * @return bool Is expired
 */
function upload_is_expired($date) {
    if (empty($date)) {
        return false; // Never expires
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return false;
    }
    
    return $timestamp < time();
}

/**
 * Check if date is expiring soon
 * 
 * @param string $date Date string
 * @param int $days Number of days to check
 * @return bool Is expiring soon
 */
function upload_is_expiring_soon($date, $days = 7) {
    if (empty($date)) {
        return false; // Never expires
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return false;
    }
    
    $threshold = time() + ($days * 24 * 60 * 60);
    return $timestamp <= $threshold && $timestamp > time();
}

/**
 * Get timezone offset
 * 
 * @return int Timezone offset in seconds
 */
function upload_get_timezone_offset() {
    $timezone = date_default_timezone_get();
    $datetime = new DateTime('now', new DateTimeZone($timezone));
    return $datetime->getOffset();
}

/**
 * Convert UTC to local time
 * 
 * @param string $utc_date UTC date string
 * @return string Local date string
 */
function upload_utc_to_local($utc_date) {
    if (empty($utc_date)) {
        return $utc_date;
    }
    
    $utc_timestamp = strtotime($utc_date . ' UTC');
    if ($utc_timestamp === false) {
        return $utc_date;
    }
    
    return date('Y-m-d H:i:s', $utc_timestamp);
}

/**
 * Convert local time to UTC
 * 
 * @param string $local_date Local date string
 * @return string UTC date string
 */
function upload_local_to_utc($local_date) {
    if (empty($local_date)) {
        return $local_date;
    }
    
    $local_timestamp = strtotime($local_date);
    if ($local_timestamp === false) {
        return $local_date;
    }
    
    return gmdate('Y-m-d H:i:s', $local_timestamp);
}

/**
 * Get date range for queries
 * 
 * @param string $period Period (today, week, month, year)
 * @return array Date range
 */
function upload_get_date_range($period) {
    $now = time();
    
    switch ($period) {
        case 'today':
            return [
                'start' => date('Y-m-d 00:00:00', $now),
                'end' => date('Y-m-d 23:59:59', $now)
            ];
            
        case 'week':
            $start = strtotime('monday this week', $now);
            return [
                'start' => date('Y-m-d 00:00:00', $start),
                'end' => date('Y-m-d 23:59:59', $start + (6 * 24 * 60 * 60))
            ];
            
        case 'month':
            return [
                'start' => date('Y-m-01 00:00:00', $now),
                'end' => date('Y-m-t 23:59:59', $now)
            ];
            
        case 'year':
            return [
                'start' => date('Y-01-01 00:00:00', $now),
                'end' => date('Y-12-31 23:59:59', $now)
            ];
            
        default:
            return [
                'start' => date('Y-m-d 00:00:00', $now),
                'end' => date('Y-m-d 23:59:59', $now)
            ];
    }
}

/**
 * Get relative date string
 * 
 * @param string $date Date string
 * @return string Relative date
 */
function upload_relative_date($date) {
    if (empty($date)) {
        return __('Never', 'upload-and-shorten');
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return __('Just now', 'upload-and-shorten');
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'upload-and-shorten'), $minutes);
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'upload-and-shorten'), $hours);
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return sprintf(_n('%d day ago', '%d days ago', $days, 'upload-and-shorten'), $days);
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return sprintf(_n('%d month ago', '%d months ago', $months, 'upload-and-shorten'), $months);
    } else {
        $years = floor($diff / 31536000);
        return sprintf(_n('%d year ago', '%d years ago', $years, 'upload-and-shorten'), $years);
    }
}

/**
 * Validate date string
 * 
 * @param string $date Date string
 * @param string $format Expected format
 * @return bool Is valid date
 */
function upload_validate_date($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return true; // Empty dates are valid
    }
    
    $datetime = DateTime::createFromFormat($format, $date);
    return $datetime && $datetime->format($format) === $date;
}

/**
 * Get expiration status
 * 
 * @param string $expiration_date Expiration date
 * @return string Status
 */
function upload_get_expiration_status($expiration_date) {
    if (empty($expiration_date)) {
        return 'permanent';
    }
    
    if (upload_is_expired($expiration_date)) {
        return 'expired';
    }
    
    if (upload_is_expiring_soon($expiration_date, 7)) {
        return 'expiring_soon';
    }
    
    return 'active';
}

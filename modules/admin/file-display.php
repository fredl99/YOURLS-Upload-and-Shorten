<?php
/**
 * File Display Module
 * Functions for displaying file manager interface
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display file manager interface
 */
function upload_display_file_manager() {
    // Process actions
    if (isset($_POST['file_action'])) {
        $result = upload_process_file_action();
        if ($result) {
            echo $result;
        }
    }
    
    // Get files
    $page = (int) ($_GET['page'] ?? 1);
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $files = upload_get_all_files($per_page, $offset);
    $total_files = upload_get_total_files_count();
    $total_pages = ceil($total_files / $per_page);
    
    echo '<div class="file-manager-container">';
    
    // Header
    echo '<div class="file-manager-header">';
    echo '<h2>' . __('Uploaded Files', 'upload-and-shorten') . '</h2>';
    echo '<div class="file-stats">';
    echo '<span>' . sprintf(__('Total Files: %d', 'upload-and-shorten'), $total_files) . '</span>';
    echo '</div>';
    echo '</div>';
    
    // Filters
    echo '<div class="file-filters">';
    echo '<form method="get" class="filter-form">';
    echo '<input type="hidden" name="page" value="1">';
    
    echo '<div class="filter-group">';
    echo '<label for="filter_type">' . __('File Type:', 'upload-and-shorten') . '</label>';
    echo '<select name="filter_type" id="filter_type">';
    echo '<option value="">' . __('All Types', 'upload-and-shorten') . '</option>';
    echo '<option value="image">' . __('Images', 'upload-and-shorten') . '</option>';
    echo '<option value="document">' . __('Documents', 'upload-and-shorten') . '</option>';
    echo '<option value="archive">' . __('Archives', 'upload-and-shorten') . '</option>';
    echo '<option value="other">' . __('Other', 'upload-and-shorten') . '</option>';
    echo '</select>';
    echo '</div>';
    
    echo '<div class="filter-group">';
    echo '<label for="filter_expired">' . __('Expiration:', 'upload-and-shorten') . '</label>';
    echo '<select name="filter_expired" id="filter_expired">';
    echo '<option value="">' . __('All Files', 'upload-and-shorten') . '</option>';
    echo '<option value="expired">' . __('Expired', 'upload-and-shorten') . '</option>';
    echo '<option value="expiring_soon">' . __('Expiring Soon', 'upload-and-shorten') . '</option>';
    echo '<option value="never">' . __('Never Expires', 'upload-and-shorten') . '</option>';
    echo '</select>';
    echo '</div>';
    
    echo '<div class="filter-group">';
    echo '<button type="submit" class="btn btn-primary">' . __('Filter', 'upload-and-shorten') . '</button>';
    echo '<a href="' . yourls_admin_url('admin.php?page=upload_file_manager') . '" class="btn btn-secondary">' . __('Clear', 'upload-and-shorten') . '</a>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    
    // Bulk actions
    echo '<div class="bulk-actions">';
    echo '<form method="post" class="bulk-form" onsubmit="return confirm(\'' . __('Are you sure?', 'upload-and-shorten') . '\')">';
    echo '<div class="bulk-controls">';
    echo '<input type="checkbox" id="select-all" class="select-all">';
    echo '<label for="select-all">' . __('Select All', 'upload-and-shorten') . '</label>';
    echo '<select name="bulk_action" class="bulk-action-select">';
    echo '<option value="">' . __('Bulk Actions', 'upload-and-shorten') . '</option>';
    echo '<option value="extend_expiration">' . __('Extend Expiration', 'upload-and-shorten') . '</option>';
    echo '<option value="delete">' . __('Delete', 'upload-and-shorten') . '</option>';
    echo '</select>';
    echo '<button type="submit" class="btn btn-warning">' . __('Apply', 'upload-and-shorten') . '</button>';
    echo '</div>';
    
    // Files table
    echo '<div class="files-table-container">';
    echo '<table class="files-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th><input type="checkbox" id="select-all-header" class="select-all"></th>';
    echo '<th>' . __('File', 'upload-and-shorten') . '</th>';
    echo '<th>' . __('Short URL', 'upload-and-shorten') . '</th>';
    echo '<th>' . __('Size', 'upload-and-shorten') . '</th>';
    echo '<th>' . __('Upload Date', 'upload-and-shorten') . '</th>';
    echo '<th>' . __('Expires', 'upload-and-shorten') . '</th>';
    echo '<th>' . __('Downloads', 'upload-and-shorten') . '</th>';
    echo '<th>' . __('Actions', 'upload-and-shorten') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if (empty($files)) {
        echo '<tr><td colspan="8" class="no-files">' . __('No files found', 'upload-and-shorten') . '</td></tr>';
    } else {
        foreach ($files as $file) {
            upload_display_file_row($file);
        }
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    echo '</form>';
    
    // Pagination
    if ($total_pages > 1) {
        upload_display_pagination($page, $total_pages);
    }
    
    echo '</div>';
    
    // Add scripts
    upload_add_file_manager_scripts();
}

/**
 * Display file row
 */
function upload_display_file_row($file) {
    $icon = upload_get_file_icon($file->mime_type);
    $expires_text = upload_get_expiration_text($file->expiration_date);
    $expires_class = upload_get_expiration_class($file->expiration_date);
    
    echo '<tr class="file-row" data-file-id="' . $file->id . '">';
    
    // Checkbox
    echo '<td><input type="checkbox" name="file_ids[]" value="' . $file->id . '" class="file-checkbox"></td>';
    
    // File info
    echo '<td class="file-info">';
    echo '<div class="file-icon">' . $icon . '</div>';
    echo '<div class="file-details">';
    echo '<div class="file-name" title="' . esc_attr($file->original_filename) . '">' . esc_html($file->original_filename) . '</div>';
    echo '<div class="file-mime">' . esc_html($file->mime_type) . '</div>';
    echo '</div>';
    echo '</td>';
    
    // Short URL
    echo '<td class="short-url">';
    echo '<div class="url-container">';
    echo '<input type="text" value="' . esc_attr($file->short_url) . '" readonly class="url-input">';
    echo '<button type="button" class="btn-copy" data-copy="' . esc_attr($file->short_url) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
    echo '</div>';
    echo '</td>';
    
    // File size
    echo '<td class="file-size">' . upload_format_file_size($file->file_size) . '</td>';
    
    // Upload date
    echo '<td class="upload-date">' . date('Y-m-d H:i', strtotime($file->upload_date)) . '</td>';
    
    // Expiration
    echo '<td class="expiration ' . $expires_class . '">' . $expires_text . '</td>';
    
    // Download count
    echo '<td class="download-count">' . number_format($file->download_count) . '</td>';
    
    // Actions
    echo '<td class="actions">';
    echo '<div class="action-buttons">';
    echo '<a href="' . esc_url($file->short_url) . '" target="_blank" class="btn btn-sm btn-primary" title="' . __('View', 'upload-and-shorten') . '">' . __('View', 'upload-and-shorten') . '</a>';
    echo '<button type="button" class="btn btn-sm btn-info extend-expiration" data-file-id="' . $file->id . '" title="' . __('Extend Expiration', 'upload-and-shorten') . '">' . __('Extend', 'upload-and-shorten') . '</button>';
    echo '<button type="button" class="btn btn-sm btn-danger delete-file" data-file-id="' . $file->id . '" title="' . __('Delete', 'upload-and-shorten') . '">' . __('Delete', 'upload-and-shorten') . '</button>';
    echo '</div>';
    echo '</td>';
    
    echo '</tr>';
}

/**
 * Get file icon based on MIME type
 */
function upload_get_file_icon($mime_type) {
    $icon_map = [
        'image/' => '🖼️',
        'video/' => '🎥',
        'audio/' => '🎵',
        'application/pdf' => '📄',
        'application/msword' => '📝',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '📝',
        'application/vnd.ms-excel' => '📊',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '📊',
        'application/vnd.ms-powerpoint' => '📊',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '📊',
        'application/zip' => '📦',
        'application/x-rar-compressed' => '📦',
        'application/x-7z-compressed' => '📦',
        'text/' => '📄',
        'application/json' => '📄',
        'application/xml' => '📄'
    ];
    
    foreach ($icon_map as $prefix => $icon) {
        if (strpos($mime_type, $prefix) === 0) {
            return $icon;
        }
    }
    
    return '📄'; // Default icon
}

/**
 * Get expiration text
 */
function upload_get_expiration_text($expiration_date) {
    if (empty($expiration_date)) {
        return __('Never', 'upload-and-shorten');
    }
    
    $expires = strtotime($expiration_date);
    $now = time();
    
    if ($expires <= $now) {
        return __('Expired', 'upload-and-shorten');
    }
    
    $diff = $expires - $now;
    $days = floor($diff / (24 * 60 * 60));
    
    if ($days <= 7) {
        return sprintf(__('%d days left', 'upload-and-shorten'), $days);
    }
    
    return date('Y-m-d', $expires);
}

/**
 * Get expiration CSS class
 */
function upload_get_expiration_class($expiration_date) {
    if (empty($expiration_date)) {
        return 'never';
    }
    
    $expires = strtotime($expiration_date);
    $now = time();
    
    if ($expires <= $now) {
        return 'expired';
    }
    
    $diff = $expires - $now;
    $days = floor($diff / (24 * 60 * 60));
    
    if ($days <= 7) {
        return 'expiring-soon';
    }
    
    return 'normal';
}

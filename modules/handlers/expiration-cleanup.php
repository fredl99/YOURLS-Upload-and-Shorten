<?php


/**
 * Get storage usage statistics
 * 
 * @return array Storage usage stats
 */
function upload_get_storage_usage_stats() {
    $storage_locations = upload_get_storage_locations();
    $total_size = 0;
    $total_files = 0;
    
    foreach ($storage_locations as $location) {
        if ($location['enabled']) {
            $stats = upload_get_storage_stats($location['key'] ?? 'default');
            $total_size += $stats['total_size'] ?? 0;
            $total_files += $stats['total_files'] ?? 0;
        }
    }
    
    return [
        'total_size' => $total_size,
        'total_files' => $total_files,
        'formatted_size' => upload_format_file_size($total_size)
    ];
}

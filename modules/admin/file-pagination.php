<?php
/**
 * File Pagination Module
 * Functions for pagination and UI components
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display pagination
 */
function upload_display_pagination($current_page, $total_pages) {
    if ($total_pages <= 1) {
        return;
    }
    
    echo '<div class="pagination-container">';
    echo '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        echo '<a href="' . upload_get_pagination_url($prev_page) . '" class="pagination-link prev">' . __('Previous', 'upload-and-shorten') . '</a>';
    } else {
        echo '<span class="pagination-link prev disabled">' . __('Previous', 'upload-and-shorten') . '</span>';
    }
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        echo '<a href="' . upload_get_pagination_url(1) . '" class="pagination-link">1</a>';
        if ($start_page > 2) {
            echo '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="pagination-link current">' . $i . '</span>';
        } else {
            echo '<a href="' . upload_get_pagination_url($i) . '" class="pagination-link">' . $i . '</a>';
        }
    }
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            echo '<span class="pagination-ellipsis">...</span>';
        }
        echo '<a href="' . upload_get_pagination_url($total_pages) . '" class="pagination-link">' . $total_pages . '</a>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        echo '<a href="' . upload_get_pagination_url($next_page) . '" class="pagination-link next">' . __('Next', 'upload-and-shorten') . '</a>';
    } else {
        echo '<span class="pagination-link next disabled">' . __('Next', 'upload-and-shorten') . '</span>';
    }
    
    echo '</div>';
    echo '<div class="pagination-info">';
    echo sprintf(__('Page %d of %d', 'upload-and-shorten'), $current_page, $total_pages);
    echo '</div>';
    echo '</div>';
}

/**
 * Get pagination URL
 */
function upload_get_pagination_url($page) {
    $url = yourls_admin_url('admin.php?page=upload_file_manager');
    $url .= '&page=' . $page;
    
    // Preserve filters
    $filters = ['filter_type', 'filter_expired', 'search'];
    foreach ($filters as $filter) {
        if (isset($_GET[$filter]) && !empty($_GET[$filter])) {
            $url .= '&' . $filter . '=' . urlencode($_GET[$filter]);
        }
    }
    
    return $url;
}

// Functions moved to separate modules:
// - upload_add_file_manager_scripts() -> file-scripts.php
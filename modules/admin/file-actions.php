<?php


/**
 * Display message
 */
function upload_display_message($message, $type = 'info') {
    $class = 'notice notice-' . $type;
    return '<div class="' . $class . '"><p>' . $message . '</p></div>';
}

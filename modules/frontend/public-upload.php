<?php
/**
 * Public Upload Module
 * Frontend upload interface for public users
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display public upload page
 */
function upload_display_frontend_upload_page() {
    // Check if frontend uploads are enabled
    $frontend_settings = upload_get_frontend_settings();
    if (!$frontend_settings['enabled']) {
        http_response_code(404);
        die('Upload service not available');
    }
    
    // Check authentication if required
    if ($frontend_settings['require_auth'] && !yourls_is_valid_user()) {
        header('Location: ' . yourls_admin_url('login.php'));
        exit;
    }
    
    // Include frontend header
    if (file_exists(YOURLS_ABSPATH . 'frontend/header.php')) {
        include YOURLS_ABSPATH . 'frontend/header.php';
    } else {
        upload_display_basic_header();
    }
    
    echo '<div class="upload-frontend-container">';
    
    // Display upload form
    upload_display_frontend_form();
    
    echo '</div>';
    
    // Include frontend footer
    if (file_exists(YOURLS_ABSPATH . 'frontend/footer.php')) {
        include YOURLS_ABSPATH . 'frontend/footer.php';
    } else {
        upload_display_basic_footer();
    }
}

/**
 * Display frontend upload form
 */
function upload_display_frontend_form() {
    // Display upload form HTML
    upload_display_upload_form_html();
    
    // Add styles and scripts
    upload_add_upload_styles();
    upload_add_upload_scripts();
}

// Functions moved to separate modules:
// - upload_display_basic_header() -> upload-templates.php
// - upload_display_basic_footer() -> upload-templates.php
// - upload_display_upload_form_html() -> upload-templates.php
// - upload_add_upload_styles() -> upload-styles.php
// - upload_add_upload_scripts() -> upload-scripts.php
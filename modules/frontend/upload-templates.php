<?php
/**
 * Upload Templates Module
 * HTML templates for frontend upload interface
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display basic header if frontend header doesn't exist
 */
function upload_display_basic_header() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo __('File Upload', 'upload-and-shorten'); ?> - <?php echo YOURLS_SITE; ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class="container">
    <?php
}

/**
 * Display basic footer if frontend footer doesn't exist
 */
function upload_display_basic_footer() {
    ?>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Display upload form HTML
 */
function upload_display_upload_form_html() {
    $frontend_settings = upload_get_frontend_settings();
    
    echo '<div class="upload-form-container">';
    
    // Header
    echo '<div class="upload-header">';
    echo '<h1>' . __('File Upload', 'upload-and-shorten') . '</h1>';
    if (!empty($frontend_settings['message'])) {
        echo '<p class="upload-message">' . esc_html($frontend_settings['message']) . '</p>';
    }
    echo '</div>';
    
    // Upload form
    echo '<form id="frontend-upload-form" class="upload-form" enctype="multipart/form-data">';
    echo wp_nonce_field('upload_frontend_form', 'upload_nonce', true, false);
    
    // File selection
    echo '<div class="upload-section">';
    echo '<label for="file_upload" class="file-label">' . __('Select Files', 'upload-and-shorten') . '</label>';
    echo '<div class="file-input-container">';
    echo '<input type="file" id="file_upload" name="file" class="file-input" multiple>';
    echo '<label for="file_upload" class="file-input-label">';
    echo '<span class="file-input-text">' . __('Choose files or drag and drop', 'upload-and-shorten') . '</span>';
    echo '<span class="file-input-button">' . __('Browse', 'upload-and-shorten') . '</span>';
    echo '</label>';
    echo '</div>';
    echo '<div id="file-preview" class="file-preview" style="display: none;"></div>';
    echo '<div id="file-list" class="file-list" style="display: none;"></div>';
    echo '</div>';
    
    // Optional fields
    echo '<div class="upload-section">';
    echo '<h3>' . __('Optional Settings', 'upload-and-shorten') . '</h3>';
    
    echo '<div class="form-row">';
    echo '<div class="form-group">';
    echo '<label for="custom_shortname">' . __('Custom Short URL', 'upload-and-shorten') . '</label>';
    echo '<input type="text" id="custom_shortname" name="custom_shortname" class="form-control" placeholder="' . __('Leave empty for auto-generated', 'upload-and-shorten') . '">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="custom_title">' . __('Custom Title', 'upload-and-shorten') . '</label>';
    echo '<input type="text" id="custom_title" name="custom_title" class="form-control" placeholder="' . __('Leave empty for auto-generated', 'upload-and-shorten') . '">';
    echo '</div>';
    echo '</div>';
    
    // Storage location selection
    echo '<div class="form-row">';
    echo '<div class="form-group">';
    echo '<label for="storage_location">' . __('Storage Location', 'upload-and-shorten') . '</label>';
    echo '<select name="storage_location" id="storage_location" class="form-control">';
    
    $storage_locations = upload_get_storage_locations();
    $default_storage = upload_get_setting('default_storage', 'default');
    
    foreach ($storage_locations as $key => $location) {
        if ($location['enabled']) {
            $selected = ($key === $default_storage) ? ' selected' : '';
            echo '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($location['name']) . '</option>';
        }
    }
    
    echo '</select>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="expiration_type">' . __('File Expiration', 'upload-and-shorten') . '</label>';
    echo '<select name="expiration_type" id="expiration_type" class="form-control">';
    
    $expiration_options = upload_get_expiration_options();
    foreach ($expiration_options as $value => $label) {
        $selected = ($value === 'never') ? ' selected' : '';
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }
    
    echo '</select>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="form-row">';
    echo '<div class="form-group" id="custom-days-group" style="display: none;">';
    echo '<label for="custom_days">' . __('Custom Days', 'upload-and-shorten') . '</label>';
    echo '<input type="number" id="custom_days" name="custom_days" class="form-control" min="1" max="365" value="30">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="filename_method">' . __('Filename Method', 'upload-and-shorten') . '</label>';
    echo '<select name="filename_method" id="filename_method" class="form-control">';
    echo '<option value="browser-safe">' . __('Browser Safe', 'upload-and-shorten') . '</option>';
    echo '<option value="original">' . __('Original', 'upload-and-shorten') . '</option>';
    echo '<option value="randomized">' . __('Randomized', 'upload-and-shorten') . '</option>';
    echo '</select>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    
    // Submit button
    echo '<div class="upload-section">';
    echo '<button type="submit" class="btn btn-primary btn-upload">';
    echo '<span class="btn-text">' . __('Upload & Create Short URL', 'upload-and-shorten') . '</span>';
    echo '<span class="btn-loading" style="display: none;">' . __('Uploading...', 'upload-and-shorten') . '</span>';
    echo '</button>';
    echo '</div>';
    
    echo '</form>';
    
    // Results container
    echo '<div id="upload-results" class="upload-results" style="display: none;"></div>';
    
    echo '</div>';
}

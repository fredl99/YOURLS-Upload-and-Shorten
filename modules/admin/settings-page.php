<?php
/**
 * Admin Settings Page Module
 * Settings management interface for upload plugin
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display admin settings page
 */
function upload_display_settings_page() {
    // Process form submission
    $message = '';
    if (isset($_POST['settings_submit'])) {
        $message = upload_process_settings_form();
    }
    
    // Get current settings
    $settings = upload_get_admin_settings();
    
    echo '<div class="upload-settings-container">';
    
    // Display message
    if ($message) {
        echo $message;
    }
    
    // Settings tabs
    echo '<div class="settings-tabs">';
    echo '<button class="tab-button active" data-tab="storage">' . __('Storage Locations', 'upload-and-shorten') . '</button>';
    echo '<button class="tab-button" data-tab="expiration">' . __('File Expiration', 'upload-and-shorten') . '</button>';
    echo '<button class="tab-button" data-tab="limits">' . __('Upload Limits', 'upload-and-shorten') . '</button>';
    echo '<button class="tab-button" data-tab="frontend">' . __('Frontend Settings', 'upload-and-shorten') . '</button>';
    echo '</div>';
    
    // Settings form
    echo '<form method="post" id="settings-form">';
    echo wp_nonce_field('upload_settings_form', 'settings_nonce', true, false);
    
    // Storage Locations Tab
    echo '<div id="storage-tab" class="tab-content active">';
    upload_display_storage_settings($settings);
    echo '</div>';
    
    // Expiration Settings Tab
    echo '<div id="expiration-tab" class="tab-content">';
    upload_display_expiration_settings($settings);
    echo '</div>';
    
    // Upload Limits Tab
    echo '<div id="limits-tab" class="tab-content">';
    upload_display_limits_settings($settings);
    echo '</div>';
    
    // Frontend Settings Tab
    echo '<div id="frontend-tab" class="tab-content">';
    upload_display_frontend_settings($settings);
    echo '</div>';
    
    // Submit button
    echo '<div class="settings-submit">';
    echo '<button type="submit" name="settings_submit" class="btn btn-primary">' . __('Save Settings', 'upload-and-shorten') . '</button>';
    echo '</div>';
    
    echo '</form>';
    echo '</div>';
    
    // Add JavaScript
    upload_add_settings_scripts();
}

/**
 * Display storage settings
 * 
 * @param array $settings Current settings
 */
function upload_display_storage_settings($settings) {
    echo '<h3>' . __('Storage Locations', 'upload-and-shorten') . '</h3>';
    echo '<p>' . __('Configure where uploaded files are stored. You can set up multiple storage locations for different purposes.', 'upload-and-shorten') . '</p>';
    
    // Storage statistics
    $storage_stats = upload_get_storage_statistics();
    if ($storage_stats) {
        echo '<div class="storage-stats">';
        echo '<h4>' . __('Storage Statistics', 'upload-and-shorten') . '</h4>';
        echo '<div class="stats-grid">';
        echo '<div class="stat-item">';
        echo '<span class="stat-label">' . __('Total Files', 'upload-and-shorten') . ':</span>';
        echo '<span class="stat-value">' . number_format($storage_stats['total_files']) . '</span>';
        echo '</div>';
        echo '<div class="stat-item">';
        echo '<span class="stat-label">' . __('Total Size', 'upload-and-shorten') . ':</span>';
        echo '<span class="stat-value">' . upload_format_file_size($storage_stats['total_size']) . '</span>';
        echo '</div>';
        echo '<div class="stat-item">';
        echo '<span class="stat-label">' . __('Available Space', 'upload-and-shorten') . ':</span>';
        echo '<span class="stat-value">' . upload_format_file_size($storage_stats['available_space']) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '<div id="storage-locations">';
    $locations = $settings['storage_locations'];
    
    foreach ($locations as $key => $location) {
        $location_stats = upload_get_storage_stats($key);
        echo '<div class="storage-location" data-key="' . esc_attr($key) . '">';
        echo '<div class="location-header">';
        echo '<h4>' . esc_html($location['name']) . '</h4>';
        echo '<div class="location-status">';
        if ($location['enabled']) {
            echo '<span class="status-enabled">' . __('Enabled', 'upload-and-shorten') . '</span>';
        } else {
            echo '<span class="status-disabled">' . __('Disabled', 'upload-and-shorten') . '</span>';
        }
        echo '</div>';
        echo '<div class="location-actions">';
        echo '<button type="button" class="btn-test-location" data-key="' . esc_attr($key) . '">' . __('Test', 'upload-and-shorten') . '</button>';
        if ($key !== 'default') {
            echo '<button type="button" class="btn-remove-location" data-key="' . esc_attr($key) . '">' . __('Remove', 'upload-and-shorten') . '</button>';
        }
        echo '</div>';
        echo '</div>';
        
        echo '<div class="location-details">';
        echo '<div class="form-row">';
        echo '<div class="form-group">';
        echo '<label>' . __('Name', 'upload-and-shorten') . '</label>';
        echo '<input type="text" name="storage_locations[' . esc_attr($key) . '][name]" value="' . esc_attr($location['name']) . '" class="form-control" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label>';
        echo '<input type="checkbox" name="storage_locations[' . esc_attr($key) . '][enabled]" value="1"' . ($location['enabled'] ? ' checked' : '') . '>';
        echo ' ' . __('Enabled', 'upload-and-shorten');
        echo '</label>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label>' . __('Storage Path', 'upload-and-shorten') . '</label>';
        echo '<input type="text" name="storage_locations[' . esc_attr($key) . '][path]" value="' . esc_attr($location['path']) . '" class="form-control" required>';
        echo '<small>' . __('Full path to storage directory (e.g., /home/user/public_html/uploads/)', 'upload-and-shorten') . '</small>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label>' . __('Web URL', 'upload-and-shorten') . '</label>';
        echo '<input type="url" name="storage_locations[' . esc_attr($key) . '][url]" value="' . esc_attr($location['url']) . '" class="form-control" required>';
        echo '<small>' . __('Public URL to access files (e.g., https://yoursite.com/uploads/)', 'upload-and-shorten') . '</small>';
        echo '</div>';
        
        // Location-specific settings
        echo '<div class="form-row">';
        echo '<div class="form-group">';
        echo '<label>' . __('Max File Size (MB)', 'upload-and-shorten') . '</label>';
        echo '<input type="number" name="storage_locations[' . esc_attr($key) . '][max_size]" value="' . esc_attr($location['max_size'] ?? 10) . '" class="form-control" min="1" max="1000">';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label>' . __('Auto Cleanup (days)', 'upload-and-shorten') . '</label>';
        echo '<input type="number" name="storage_locations[' . esc_attr($key) . '][auto_cleanup]" value="' . esc_attr($location['auto_cleanup'] ?? 0) . '" class="form-control" min="0" max="365">';
        echo '<small>' . __('0 = disabled, files will be cleaned up after this many days', 'upload-and-shorten') . '</small>';
        echo '</div>';
        echo '</div>';
        
        // Location statistics
        if ($location_stats) {
            echo '<div class="location-stats">';
            echo '<h5>' . __('Location Statistics', 'upload-and-shorten') . '</h5>';
            echo '<div class="stats-row">';
            echo '<span>' . __('Files:', 'upload-and-shorten') . ' ' . number_format($location_stats['total_files']) . '</span>';
            echo '<span>' . __('Size:', 'upload-and-shorten') . ' ' . upload_format_file_size($location_stats['total_size']) . '</span>';
            echo '<span>' . __('Available:', 'upload-and-shorten') . ' ' . upload_format_file_size($location_stats['available_space']) . '</span>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    
    echo '<div class="add-location">';
    echo '<button type="button" id="add-storage-location" class="btn btn-secondary">' . __('Add Storage Location', 'upload-and-shorten') . '</button>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="default_storage">' . __('Default Storage Location', 'upload-and-shorten') . '</label>';
    echo '<select name="default_storage" id="default_storage" class="form-control">';
    foreach ($locations as $key => $location) {
        if ($location['enabled']) {
            $selected = ($key === $settings['default_storage']) ? ' selected' : '';
            echo '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($location['name']) . '</option>';
        }
    }
    echo '</select>';
    echo '<small>' . __('This location will be used by default for new uploads', 'upload-and-shorten') . '</small>';
    echo '</div>';
}

/**
 * Display expiration settings
 * 
 * @param array $settings Current settings
 */
function upload_display_expiration_settings($settings) {
    echo '<h3>' . __('File Expiration Settings', 'upload-and-shorten') . '</h3>';
    echo '<p>' . __('Configure default file expiration periods.', 'upload-and-shorten') . '</p>';
    
    $expiration_options = upload_get_expiration_options();
    
    echo '<div class="form-group">';
    echo '<label for="expiration_default">' . __('Default Expiration Period', 'upload-and-shorten') . '</label>';
    echo '<select name="expiration_default" id="expiration_default" class="form-control">';
    foreach ($expiration_options as $value => $label) {
        $selected = ($value === $settings['expiration_default']) ? ' selected' : '';
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    
    echo '<div class="form-group" id="custom-days-group">';
    echo '<label for="expiration_custom_days">' . __('Custom Days', 'upload-and-shorten') . '</label>';
    echo '<input type="number" name="expiration_custom_days" id="expiration_custom_days" value="' . esc_attr($settings['expiration_custom_days']) . '" class="form-control" min="1" max="365">';
    echo '<small>' . __('Number of days for custom expiration', 'upload-and-shorten') . '</small>';
    echo '</div>';
}

/**
 * Display limits settings
 * 
 * @param array $settings Current settings
 */
function upload_display_limits_settings($settings) {
    echo '<h3>' . __('Upload Limits', 'upload-and-shorten') . '</h3>';
    echo '<p>' . __('Configure file size and type restrictions.', 'upload-and-shorten') . '</p>';
    
    echo '<div class="form-group">';
    echo '<label for="max_file_size">' . __('Maximum File Size (MB)', 'upload-and-shorten') . '</label>';
    echo '<input type="number" name="max_file_size" id="max_file_size" value="' . esc_attr($settings['max_file_size']) . '" class="form-control" min="1" max="1000">';
    echo '<small>' . __('Maximum file size allowed for uploads', 'upload-and-shorten') . '</small>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="allowed_file_types">' . __('Allowed File Types', 'upload-and-shorten') . '</label>';
    echo '<input type="text" name="allowed_file_types" id="allowed_file_types" value="' . esc_attr(implode(', ', $settings['allowed_file_types'])) . '" class="form-control">';
    echo '<small>' . __('Comma-separated list of allowed file extensions (leave empty to allow all)', 'upload-and-shorten') . '</small>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="blocked_file_types">' . __('Blocked File Types', 'upload-and-shorten') . '</label>';
    echo '<input type="text" name="blocked_file_types" id="blocked_file_types" value="' . esc_attr(implode(', ', $settings['blocked_file_types'])) . '" class="form-control">';
    echo '<small>' . __('Comma-separated list of blocked file extensions', 'upload-and-shorten') . '</small>';
    echo '</div>';
}

/**
 * Display frontend settings
 * 
 * @param array $settings Current settings
 */
function upload_display_frontend_settings($settings) {
    echo '<h3>' . __('Frontend Upload Settings', 'upload-and-shorten') . '</h3>';
    echo '<p>' . __('Configure public upload functionality.', 'upload-and-shorten') . '</p>';
    
    $frontend = $settings['frontend'];
    
    echo '<div class="form-group">';
    echo '<label>';
    echo '<input type="checkbox" name="frontend[enabled]" value="1"' . ($frontend['enabled'] ? ' checked' : '') . '>';
    echo ' ' . __('Enable Frontend Uploads', 'upload-and-shorten');
    echo '</label>';
    echo '<small>' . __('Allow public users to upload files', 'upload-and-shorten') . '</small>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="frontend_message">' . __('Upload Page Message', 'upload-and-shorten') . '</label>';
    echo '<textarea name="frontend[message]" id="frontend_message" class="form-control" rows="3">' . esc_textarea($frontend['message']) . '</textarea>';
    echo '<small>' . __('Message displayed on the public upload page', 'upload-and-shorten') . '</small>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label>';
    echo '<input type="checkbox" name="frontend[require_auth]" value="1"' . ($frontend['require_auth'] ? ' checked' : '') . '>';
    echo ' ' . __('Require Authentication', 'upload-and-shorten');
    echo '</label>';
    echo '<small>' . __('Require users to be logged in to upload files', 'upload-and-shorten') . '</small>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="rate_limit_uploads">' . __('Rate Limit (uploads per hour)', 'upload-and-shorten') . '</label>';
    echo '<input type="number" name="frontend[rate_limit_uploads]" id="rate_limit_uploads" value="' . esc_attr($frontend['rate_limit_uploads']) . '" class="form-control" min="1" max="1000">';
    echo '<small>' . __('Maximum uploads per IP address per hour', 'upload-and-shorten') . '</small>';
    echo '</div>';
}

/**
 * Process settings form submission
 * 
 * @return string Result message
 */
function upload_process_settings_form() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['settings_nonce'] ?? '', 'upload_settings_form')) {
        return upload_display_error(__('Security check failed', 'upload-and-shorten'));
    }
    
    $form_data = $_POST;
    
    // Process storage locations
    if (isset($form_data['storage_locations'])) {
        $locations = [];
        foreach ($form_data['storage_locations'] as $key => $location) {
            if (!empty($location['name']) && !empty($location['path']) && !empty($location['url'])) {
                $locations[$key] = [
                    'name' => sanitize_text_field($location['name']),
                    'path' => sanitize_text_field($location['path']),
                    'url' => esc_url_raw($location['url']),
                    'enabled' => isset($location['enabled'])
                ];
            }
        }
        $form_data['storage_locations'] = $locations;
    }
    
    // Process file types
    if (isset($form_data['allowed_file_types'])) {
        $types = array_map('trim', explode(',', $form_data['allowed_file_types']));
        $form_data['allowed_file_types'] = array_filter($types);
    }
    
    if (isset($form_data['blocked_file_types'])) {
        $types = array_map('trim', explode(',', $form_data['blocked_file_types']));
        $form_data['blocked_file_types'] = array_filter($types);
    }
    
    // Update settings
    if (upload_update_admin_settings($form_data)) {
        return upload_display_success(__('Settings saved successfully!', 'upload-and-shorten'));
    } else {
        return upload_display_error(__('Failed to save settings', 'upload-and-shorten'));
    }
}

/**
 * Add JavaScript for settings page
 */
function upload_add_settings_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                this.classList.add('active');
                document.getElementById(targetTab + '-tab').classList.add('active');
            });
        });
        
        // Expiration type change handler
        const expirationSelect = document.getElementById('expiration_default');
        const customDaysGroup = document.getElementById('custom-days-group');
        
        if (expirationSelect && customDaysGroup) {
            function toggleCustomDays() {
                if (expirationSelect.value === 'custom') {
                    customDaysGroup.style.display = 'block';
                } else {
                    customDaysGroup.style.display = 'none';
                }
            }
            
            expirationSelect.addEventListener('change', toggleCustomDays);
            toggleCustomDays(); // Initial call
        }
        
        // Add storage location
        const addLocationBtn = document.getElementById('add-storage-location');
        if (addLocationBtn) {
            addLocationBtn.addEventListener('click', function() {
                addStorageLocation();
            });
        }
        
        // Remove storage location
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-location')) {
                const key = e.target.getAttribute('data-key');
                removeStorageLocation(key);
            }
        });
        
        // Test storage location
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-test-location')) {
                const key = e.target.getAttribute('data-key');
                testStorageLocation(key);
            }
        });
    });
    
    function addStorageLocation() {
        const container = document.getElementById('storage-locations');
        const key = 'location_' + Date.now();
        
        const locationHtml = `
            <div class="storage-location" data-key="${key}">
                <div class="location-header">
                    <h4>New Location</h4>
                    <div class="location-actions">
                        <button type="button" class="btn-test-location" data-key="${key}">Test</button>
                        <button type="button" class="btn-remove-location" data-key="${key}">Remove</button>
                    </div>
                </div>
                <div class="location-details">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="storage_locations[${key}][name]" value="New Location" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Storage Path</label>
                        <input type="text" name="storage_locations[${key}][path]" value="" class="form-control">
                        <small>Full path to storage directory</small>
                    </div>
                    <div class="form-group">
                        <label>Web URL</label>
                        <input type="url" name="storage_locations[${key}][url]" value="" class="form-control">
                        <small>Public URL to access files</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="storage_locations[${key}][enabled]" value="1" checked>
                            Enabled
                        </label>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', locationHtml);
    }
    
    function removeStorageLocation(key) {
        const location = document.querySelector(`[data-key="${key}"]`);
        if (location) {
            location.remove();
        }
    }
    
    function testStorageLocation(key) {
        const button = document.querySelector(`[data-key="${key}"].btn-test-location`);
        if (button) {
            button.textContent = 'Testing...';
            button.disabled = true;
        }
        
        // Simulate test (in real implementation, this would be an AJAX call)
        setTimeout(function() {
            if (button) {
                button.textContent = 'Test';
                button.disabled = false;
                alert('Storage location test completed (simulated)');
            }
        }, 2000);
    }
    </script>
    
    <style>
    .upload-settings-container {
        max-width: 1000px;
        margin: 20px 0;
    }
    
    .settings-tabs {
        display: flex;
        border-bottom: 2px solid #ddd;
        margin-bottom: 20px;
    }
    
    .tab-button {
        background: none;
        border: none;
        padding: 12px 20px;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.3s;
    }
    
    .tab-button.active {
        border-bottom-color: #007cba;
        color: #007cba;
        font-weight: bold;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .storage-location {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .location-header {
        background: #f8f9fa;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .location-header h4 {
        margin: 0;
    }
    
    .location-actions {
        display: flex;
        gap: 10px;
    }
    
    .location-details {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 12px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: #007cba;
        color: white;
    }
    
    .btn-primary:hover {
        background: #005a87;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #545b62;
    }
    
    .btn-test-location, .btn-remove-location {
        background: #28a745;
        color: white;
        padding: 6px 12px;
        font-size: 12px;
    }
    
    .btn-remove-location {
        background: #dc3545;
    }
    
    .settings-submit {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }
    
    .upload-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .upload-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    </style>
    <?php
}

<?php
/*
Plugin Name: Upload & Shorten
Plugin URI: https://github.com/fredl/YOURLS-Upload-and-Shorten
Description: Upload files and create short URLs for them
Version: 2.0.0
Author: Fredl (Original)
Author URI: https://github.com/fredl
Requires PHP: 7.4
Tested up to PHP: 8.6
*/

// Prevent direct access
if (!defined('YOURLS_ABSPATH')) {
    die('Direct access not allowed');
}

// Define plugin constants
define('UPLOAD_PLUGIN_VERSION', '2.0.0');
define('UPLOAD_PLUGIN_PATH', dirname(__FILE__));
define('UPLOAD_PLUGIN_URL', (defined('YOURLS_SITE') ? YOURLS_SITE : '') . '/user/plugins/YOURLS-Upload-and-Shorten-master');

// Plugin activation
function upload_plugin_activation() {
    // Create database tables
    upload_create_database_tables();
    
    // Set default settings
    upload_set_default_settings();
    
    error_log('Upload plugin v' . UPLOAD_PLUGIN_VERSION . ' activated');
}

// Plugin deactivation
function upload_plugin_deactivation() {
    error_log('Upload plugin v' . UPLOAD_PLUGIN_VERSION . ' deactivated');
}

// Initialize plugin
function upload_plugin_init() {
    // Load text domain for translations
    yourls_load_custom_textdomain('upload-and-shorten', UPLOAD_PLUGIN_PATH . '/l10n/');
}

// Admin page functions
function upload_admin_page() {
    try {
        error_log('DEBUG: upload_admin_page() function called');
        echo '<!-- DEBUG: upload_admin_page() function called -->';
        
        // Add responsive CSS framework
        echo '<style>
        /* Upload Plugin Responsive CSS Framework */
        .upload-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .upload-card {
            background: var(--card-bg, #2d3748);
            border: 1px solid var(--card-border, #4a5568);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .upload-header {
            color: var(--text-primary, #ffffff);
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .upload-subheader {
            color: var(--text-secondary, #a0aec0);
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        .upload-form {
            background: var(--form-bg, #2d3748);
            border: 1px solid var(--form-border, #4a5568);
            border-radius: 8px;
            padding: 25px;
        }
        
        .upload-input {
            width: 100%;
            padding: 12px;
            background: var(--input-bg, #1a202c);
            border: 2px dashed var(--input-border, #4a5568);
            border-radius: 6px;
            color: var(--input-text, #e2e8f0);
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .upload-input:focus {
            outline: none;
            border-color: var(--accent-color, #667eea);
        }
        
        .upload-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .upload-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .upload-success {
            color: #c6f6d5;
            padding: 15px;
            background: #22543d;
            border: 1px solid #48bb78;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #48bb78;
        }
        
        .upload-error {
            color: #fed7d7;
            padding: 15px;
            background: #742a2a;
            border: 1px solid #e53e3e;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #e53e3e;
        }
        
        .upload-info {
            color: #90cdf4;
            padding: 15px;
            background: #1a365d;
            border: 1px solid #3182ce;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #3182ce;
        }
        
        .server-limits {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .limit-item {
            background: var(--limit-bg, #1a202c);
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }
        
        .limit-label {
            color: var(--text-secondary, #a0aec0);
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .limit-value {
            color: var(--text-primary, #e2e8f0);
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .upload-container {
                padding: 10px;
            }
            
            .upload-card {
                padding: 15px;
            }
            
            .upload-header {
                font-size: 20px;
            }
            
            .server-limits {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
            }
            
            .upload-button {
                width: 100%;
                margin-top: 10px;
            }
        }
        
        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            :root {
                --card-bg: #ffffff;
                --card-border: #e2e8f0;
                --text-primary: #1a202c;
                --text-secondary: #4a5568;
                --form-bg: #f7fafc;
                --form-border: #e2e8f0;
                --input-bg: #ffffff;
                --input-border: #cbd5e0;
                --input-text: #2d3748;
                --accent-color: #3182ce;
                --limit-bg: #f7fafc;
            }
        }
        </style>';
        
        echo '<div class="upload-container">';
        echo '<h2 class="upload-header">📤 Upload & Shorten Plugin</h2>';
        echo '<div class="upload-card">';
        echo '<p style="color: var(--text-primary, #e2e8f0); margin: 0 0 10px 0;">✅ Plugin is loaded successfully!</p>';
        echo '<p style="color: var(--text-secondary, #a0aec0); margin: 0; font-size: 14px;">Version: ' . UPLOAD_PLUGIN_VERSION . '</p>';
        echo '</div>';
        
        // Display server limits
        $server_limits = upload_get_server_limits();
        echo '<div class="upload-card">';
        echo '<h3 style="color: var(--text-primary, #e2e8f0); margin-bottom: 15px;">⚙️ Server Upload Limits</h3>';
        echo '<div class="server-limits">';
        echo '<div class="limit-item"><div class="limit-label">Max File Size</div><div class="limit-value">' . $server_limits['upload_max_filesize'] . '</div></div>';
        echo '<div class="limit-item"><div class="limit-label">Post Max Size</div><div class="limit-value">' . $server_limits['post_max_size'] . '</div></div>';
        echo '<div class="limit-item"><div class="limit-label">Max File Uploads</div><div class="limit-value">' . $server_limits['max_file_uploads'] . '</div></div>';
        echo '<div class="limit-item"><div class="limit-label">Memory Limit</div><div class="limit-value">' . $server_limits['memory_limit'] . '</div></div>';
        echo '<div class="limit-item"><div class="limit-label">Max Execution Time</div><div class="limit-value">' . $server_limits['max_execution_time'] . 's</div></div>';
        echo '</div>';
        echo '</div>';
        
        // Enhanced upload form with responsive styling
        echo '<div class="upload-card">';
        echo '<h3 style="color: var(--text-primary, #e2e8f0); margin-bottom: 20px;">📁 File Upload</h3>';
        echo '<form method="post" enctype="multipart/form-data" class="upload-form">';
        echo '<div style="margin-bottom: 20px;">';
        echo '<label for="upload_file" style="display: block; color: var(--text-primary, #e2e8f0); font-weight: 600; margin-bottom: 10px; font-size: 16px;">Select file to upload:</label>';
        echo '<input type="file" name="upload_file" id="upload_file" required class="upload-input">';
        echo '</div>';
        echo '<div style="text-align: center;">';
        echo '<input type="submit" name="upload_submit" value="🚀 Upload & Create Short URL" class="upload-button">';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        
        // Handle file upload
        if (isset($_POST['upload_submit']) && isset($_FILES['upload_file'])) {
            upload_handle_file_upload();
        }
        
        echo '</div>'; // Close upload-container
        
    } catch (Exception $e) {
        echo '<div class="upload-error">';
        echo '<strong>❌ Error in upload_admin_page(): ' . htmlspecialchars($e->getMessage()) . '</strong>';
        echo '</div>';
        error_log('Error in upload_admin_page(): ' . $e->getMessage());
    }
}

function upload_settings_page() {
    try {
        // Handle form submission
        if (isset($_POST['upload_settings_submit'])) {
            upload_save_settings();
        }
        
        // Get current settings
        $settings = upload_get_settings();
        
        echo '<div class="wrap">';
        echo '<h1 style="color: #ffffff; margin-bottom: 20px;">⚙️ Upload Settings</h1>';
        echo '<p style="color: #a0aec0; margin-bottom: 25px;">Configure file upload settings and storage options.</p>';
        
        echo '<div style="background: #2d3748; padding: 25px; border-radius: 8px; border: 1px solid #4a5568;">';
        echo '<form method="post" class="upload-settings-form">';
        echo '<table class="form-table" style="width: 100%; max-width: 800px; color: #e2e8f0;">';
        
        // Max file size
        echo '<tr style="border-bottom: 1px solid #4a5568;">';
        echo '<th scope="row" style="width: 200px; padding: 15px 10px; color: #e2e8f0;"><label for="max_file_size">📏 Max File Size (MB)</label></th>';
        echo '<td style="padding: 15px 10px;"><input type="number" id="max_file_size" name="max_file_size" value="' . $settings['max_file_size'] . '" min="1" max="100" style="width: 100px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;" />';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">Maximum file size allowed for uploads (1-100 MB)</p></td>';
        echo '</tr>';
        
        // Allowed file types
        echo '<tr style="border-bottom: 1px solid #4a5568;">';
        echo '<th scope="row" style="padding: 15px 10px; color: #e2e8f0;"><label for="allowed_types">📁 Allowed File Types</label></th>';
        echo '<td style="padding: 15px 10px;"><input type="text" id="allowed_types" name="allowed_types" value="' . $settings['allowed_types'] . '" placeholder="jpg,jpeg,png,gif,pdf,doc,docx" style="width: 400px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;" />';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">Comma-separated list of allowed file extensions (without dots)</p></td>';
        echo '</tr>';
        
        // File retention
        echo '<tr style="border-bottom: 1px solid #4a5568;">';
        echo '<th scope="row" style="padding: 15px 10px; color: #e2e8f0;"><label for="file_retention">⏰ File Retention</label></th>';
        echo '<td style="padding: 15px 10px;">';
        echo '<select id="file_retention" name="file_retention" style="width: 200px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;">';
        $retention_options = array(
            'never' => 'Never (Keep Forever)',
            '24h' => '24 Hours',
            '7d' => '7 Days',
            '31d' => '31 Days',
            '90d' => '90 Days'
        );
        foreach ($retention_options as $value => $label) {
            $selected = ($settings['file_retention'] == $value) ? ' selected' : '';
            echo '<option value="' . $value . '"' . $selected . ' style="background: #1a202c; color: #e2e8f0;">' . $label . '</option>';
        }
        echo '</select>';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">How long to keep uploaded files before automatic deletion</p>';
        echo '</td>';
        echo '</tr>';
        
        // Frontend upload settings section
        echo '<tr style="border-bottom: 1px solid #4a5568;">';
        echo '<th scope="row" style="padding: 15px 10px; color: #e2e8f0;"><label for="frontend_upload_enabled">🌐 Enable Frontend Uploads</label></th>';
        echo '<td style="padding: 15px 10px;">';
        $frontend_enabled = isset($settings['frontend_upload_enabled']) ? $settings['frontend_upload_enabled'] : 'no';
        echo '<select id="frontend_upload_enabled" name="frontend_upload_enabled" style="width: 150px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;">';
        echo '<option value="yes"' . ($frontend_enabled == 'yes' ? ' selected' : '') . ' style="background: #1a202c; color: #e2e8f0;">Yes - Enable</option>';
        echo '<option value="no"' . ($frontend_enabled == 'no' ? ' selected' : '') . ' style="background: #1a202c; color: #e2e8f0;">No - Disable</option>';
        echo '</select>';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">Allow public users to upload files on the frontend</p>';
        echo '</td>';
        echo '</tr>';
        
        // Frontend max file size
        echo '<tr style="border-bottom: 1px solid #4a5568;">';
        echo '<th scope="row" style="padding: 15px 10px; color: #e2e8f0;"><label for="frontend_max_file_size">📏 Frontend Max File Size (MB)</label></th>';
        echo '<td style="padding: 15px 10px;"><input type="number" id="frontend_max_file_size" name="frontend_max_file_size" value="' . (isset($settings['frontend_max_file_size']) ? $settings['frontend_max_file_size'] : '5') . '" min="1" max="100" style="width: 100px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;" />';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">Maximum file size allowed for frontend uploads (1-100 MB)</p></td>';
        echo '</tr>';
        
        // Frontend allowed file types
        echo '<tr style="border-bottom: 1px solid #4a5568;">';
        echo '<th scope="row" style="padding: 15px 10px; color: #e2e8f0;"><label for="frontend_allowed_types">📁 Frontend Allowed File Types</label></th>';
        echo '<td style="padding: 15px 10px;"><input type="text" id="frontend_allowed_types" name="frontend_allowed_types" value="' . (isset($settings['frontend_allowed_types']) ? $settings['frontend_allowed_types'] : 'jpg,jpeg,png,gif,pdf,txt') . '" placeholder="jpg,jpeg,png,gif,pdf,txt" style="width: 400px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;" />';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">Comma-separated list of allowed file extensions for frontend uploads (without dots)</p></td>';
        echo '</tr>';
        
        // Storage location
        echo '<tr>';
        echo '<th scope="row" style="padding: 15px 10px; color: #e2e8f0;"><label for="storage_location">💾 Storage Location</label></th>';
        echo '<td style="padding: 15px 10px;"><input type="text" id="storage_location" name="storage_location" value="' . $settings['storage_location'] . '" placeholder="/path/to/uploads" style="width: 400px; padding: 8px; background: #1a202c; border: 1px solid #4a5568; border-radius: 4px; color: #e2e8f0;" />';
        echo '<p class="description" style="color: #a0aec0; margin: 5px 0 0 0; font-size: 14px;">Directory path where uploaded files will be stored</p></td>';
        echo '</tr>';
        
        echo '</table>';
        echo '<div style="margin-top: 25px; text-align: center; padding-top: 20px; border-top: 1px solid #4a5568;">';
        echo '<input type="submit" name="upload_settings_submit" class="button button-primary" value="💾 Save Settings" style="padding: 12px 25px; font-size: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 15px;" />';
        echo '<a href="?page=upload-and-shorten" class="button" style="padding: 12px 25px; font-size: 16px; background: #4a5568; color: #e2e8f0; text-decoration: none; border-radius: 8px; font-weight: 600;">← Back to Upload</a>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<p>Error in upload_settings_page(): ' . $e->getMessage() . '</p>';
        error_log('Error in upload_settings_page(): ' . $e->getMessage());
    }
}

function upload_files_page() {
    try {
        // Handle file actions
        if (isset($_GET['action']) && isset($_GET['file_id'])) {
            $action = $_GET['action'];
            $file_id = intval($_GET['file_id']);
            
            if ($action == 'delete') {
                upload_delete_file($file_id);
            }
        }
        
        // Get uploaded files
        $files = upload_get_files();
        
        echo '<div class="wrap">';
        echo '<h1 style="color: #ffffff; margin-bottom: 20px;">📁 Uploaded Files</h1>';
        echo '<p style="color: #a0aec0; margin-bottom: 25px;">Manage your uploaded files and view their short URLs.</p>';
        
        if (empty($files)) {
            echo '<div style="text-align: center; padding: 40px; background: #2d3748; border: 1px solid #4a5568; border-radius: 8px; margin: 20px 0;">';
            echo '<h3 style="color: #e2e8f0; margin-bottom: 15px;">📂 No files uploaded yet</h3>';
            echo '<p style="color: #a0aec0;">Upload your first file using the <a href="?page=upload-and-shorten" style="color: #63b3ed; text-decoration: none;">Upload & Shorten</a> page.</p>';
            echo '</div>';
            echo '</div>';
            return;
        }
        
        echo '<div style="margin: 20px 0; padding: 15px; background: #1a365d; border-left: 4px solid #3182ce; border-radius: 8px; border: 1px solid #3182ce;">';
        echo '<strong style="color: #90cdf4;">📊 Statistics:</strong> ';
        echo '<span style="color: #e2e8f0;">Total Files: <strong style="color: #63b3ed;">' . count($files) . '</strong></span> | ';
        echo '<span style="color: #e2e8f0;">Total Size: <strong style="color: #63b3ed;">' . upload_format_file_size(array_sum(array_column($files, 'file_size'))) . '</strong></span> | ';
        echo '<span style="color: #e2e8f0;">Total Downloads: <strong style="color: #63b3ed;">' . array_sum(array_column($files, 'download_count')) . '</strong></span>';
        echo '</div>';
        
        echo '<div style="background: #2d3748; border-radius: 8px; overflow: hidden; border: 1px solid #4a5568;">';
        echo '<table class="widefat fixed striped" style="width: 100%; border-collapse: collapse; margin: 0;">';
        echo '<thead style="background: #1a202c;">';
        echo '<tr>';
        echo '<th style="padding: 15px; text-align: left; color: #e2e8f0; font-weight: 600; border-bottom: 2px solid #4a5568;">📄 File Name</th>';
        echo '<th style="padding: 15px; text-align: left; color: #e2e8f0; font-weight: 600; border-bottom: 2px solid #4a5568;">🔗 Short URL</th>';
        echo '<th style="padding: 15px; text-align: left; color: #e2e8f0; font-weight: 600; border-bottom: 2px solid #4a5568;">📏 Size</th>';
        echo '<th style="padding: 15px; text-align: left; color: #e2e8f0; font-weight: 600; border-bottom: 2px solid #4a5568;">📅 Upload Date</th>';
        echo '<th style="padding: 15px; text-align: center; color: #e2e8f0; font-weight: 600; border-bottom: 2px solid #4a5568;">📊 Downloads</th>';
        echo '<th style="padding: 15px; text-align: center; color: #e2e8f0; font-weight: 600; border-bottom: 2px solid #4a5568;">⚙️ Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($files as $file) {
            $file_extension = strtoupper(pathinfo($file['original_name'], PATHINFO_EXTENSION));
            $upload_date = date('M j, Y g:i A', strtotime($file['upload_date']));
            
            echo '<tr style="border-bottom: 1px solid #4a5568; background: #2d3748;">';
            echo '<td style="padding: 15px; vertical-align: top;">';
            echo '<strong style="color: #e2e8f0;">' . htmlspecialchars($file['original_name']) . '</strong><br>';
            echo '<small style="color: #a0aec0;">Type: ' . $file_extension . ' | MIME: ' . $file['mime_type'] . '</small>';
            echo '</td>';
            echo '<td style="padding: 15px; vertical-align: top;">';
            echo '<a href="' . YOURLS_SITE . '/' . $file['short_url'] . '" target="_blank" style="color: #63b3ed; text-decoration: none; font-weight: 600; word-break: break-all;">';
            echo YOURLS_SITE . '/' . $file['short_url'];
            echo '</a><br>';
            echo '<small style="color: #a0aec0;">Click to test</small>';
            echo '</td>';
            echo '<td style="padding: 15px; vertical-align: top; text-align: right; color: #e2e8f0;">' . upload_format_file_size($file['file_size']) . '</td>';
            echo '<td style="padding: 15px; vertical-align: top; color: #e2e8f0;">' . $upload_date . '</td>';
            echo '<td style="padding: 15px; vertical-align: top; text-align: center;">';
            echo '<span style="background: #3182ce; color: #e2e8f0; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600;">' . $file['download_count'] . '</span>';
            echo '</td>';
            echo '<td style="padding: 15px; vertical-align: top; text-align: center;">';
            echo '<a href="?page=upload-files&action=delete&file_id=' . $file['id'] . '" onclick="return confirm(\'Are you sure you want to delete this file? This action cannot be undone.\')" class="button button-small" style="background: #e53e3e; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">🗑️ Delete</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        echo '<div style="margin-top: 25px; text-align: center; padding-top: 20px;">';
        echo '<a href="?page=upload-and-shorten" class="button button-primary" style="padding: 12px 25px; font-size: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 15px; text-decoration: none;">📤 Upload New File</a>';
        echo '<a href="?page=upload-settings" class="button" style="padding: 12px 25px; font-size: 16px; background: #4a5568; color: #e2e8f0; text-decoration: none; border-radius: 8px; font-weight: 600;">⚙️ Settings</a>';
        echo '</div>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<p>Error in upload_files_page(): ' . $e->getMessage() . '</p>';
        error_log('Error in upload_files_page(): ' . $e->getMessage());
    }
}

// Handle file upload
function upload_handle_file_upload() {
    try {
        $file = $_FILES['upload_file'];
        
        // Basic validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="upload-error">';
            echo '<strong>❌ Upload failed with error code: ' . $file['error'] . '</strong>';
            echo '</div>';
            return;
        }
        
        // Get settings for validation
        $settings = upload_get_settings();
        $allowed_types = array_map('trim', explode(',', $settings['allowed_types']));
        $max_size = $settings['max_file_size'] * 1024 * 1024; // Convert MB to bytes
        
        // Check file size
        if ($file['size'] > $max_size) {
            echo '<div class="upload-error">';
            echo '<strong>❌ File too large. Maximum size allowed: ' . $settings['max_file_size'] . ' MB</strong>';
            echo '</div>';
            return;
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            echo '<div class="upload-error">';
            echo '<strong>❌ File type not allowed. Supported types: ' . implode(', ', array_map('strtoupper', $allowed_types)) . '</strong>';
            echo '</div>';
            return;
        }
        
        // Force web-accessible upload directory
        $upload_dir = YOURLS_ABSPATH . '/uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                echo '<div class="upload-error">';
                echo '<strong>❌ Failed to create upload directory: ' . htmlspecialchars($upload_dir) . '</strong>';
                echo '</div>';
                return;
            }
        }
        
        // Generate unique filename
        $unique_filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . '/' . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Create web-accessible URL for the file
        $web_url = YOURLS_SITE . '/uploads/' . $unique_filename;
        
        // Create short URL using YOURLS database
        $short_url = upload_create_short_url($file_path, $file['name'], $web_url);

        if ($short_url) {
            echo '<div class="upload-success">';
            echo '<strong>✅ File uploaded successfully!</strong>';
            echo '</div>';
            echo '<div class="upload-card">';
            echo '<h4 style="color: var(--text-primary, #e2e8f0); margin: 0 0 15px 0; font-size: 18px;">📋 Upload Details:</h4>';
            echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
            echo '<div class="limit-item"><div class="limit-label">File</div><div class="limit-value">' . htmlspecialchars($file['name']) . '</div></div>';
            echo '<div class="limit-item"><div class="limit-label">Size</div><div class="limit-value">' . upload_format_file_size($file['size']) . '</div></div>';
            echo '<div class="limit-item"><div class="limit-label">Type</div><div class="limit-value">' . strtoupper($file_extension) . '</div></div>';
            echo '</div>';
            echo '<div class="upload-info" style="margin-top: 15px;">';
            echo '<strong>🔗 Short URL:</strong><br>';
            echo '<a href="' . YOURLS_SITE . '/' . $short_url . '" target="_blank" style="color: #63b3ed; text-decoration: none; font-weight: 600; word-break: break-all;">' . YOURLS_SITE . '/' . $short_url . '</a>';
            echo '<br><small style="color: #a0aec0; margin-top: 5px; display: block;">Direct file URL: <a href="' . $web_url . '" target="_blank" style="color: #63b3ed;">' . $web_url . '</a></small>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="upload-error">';
            echo '<strong>❌ Failed to create short URL.</strong>';
            echo '</div>';
        }
        } else {
            echo '<div class="upload-error">';
            echo '<strong>❌ Failed to move uploaded file.</strong>';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="upload-error">';
        echo '<strong>❌ Upload error: ' . htmlspecialchars($e->getMessage()) . '</strong>';
        echo '</div>';
        error_log('Upload error: ' . $e->getMessage());
    }
}

// Create short URL using YOURLS built-in function
function upload_create_short_url($file_path, $original_name, $web_url = null) {
    global $ydb;

    // Use web URL if provided, otherwise use file path
    $redirect_url = $web_url ? $web_url : $file_path;

    // Use YOURLS's built-in function to create short URL
    // This ensures proper caching and keyword validation
    $result = yourls_add_new_link($redirect_url, '', $original_name);
    
    if ($result && isset($result['status']) && $result['status'] == 'success') {
        // Get keyword from result - YOURLS returns it in the 'url' array
        $keyword = isset($result['url']['keyword']) ? $result['url']['keyword'] : '';
        
        if (empty($keyword)) {
            error_log('Upload plugin: Failed to get keyword from YOURLS result');
            return false;
        }
        
        // Store file metadata in uploads table
        $uploads_table = YOURLS_DB_PREFIX . 'uploads';
        $file_insert_query = "INSERT INTO `$uploads_table` (short_url, original_name, file_path, file_size, mime_type, upload_date, download_count, expires_at, storage_location) VALUES (:short_url, :original_name, :file_path, :file_size, :mime_type, :upload_date, :download_count, :expires_at, :storage_location)";
        
        try {
            $ydb->perform($file_insert_query, [
                'short_url' => $keyword,
                'original_name' => $original_name,
                'file_path' => $file_path,
                'file_size' => filesize($file_path),
                'mime_type' => mime_content_type($file_path),
                'upload_date' => date('Y-m-d H:i:s'),
                'download_count' => 0,
                'expires_at' => null,
                'storage_location' => 'web-accessible'
            ]);
            error_log('Upload plugin: Successfully stored file metadata for keyword: ' . $keyword);
        } catch (Exception $e) {
            error_log('Upload plugin: Failed to store file metadata: ' . $e->getMessage());
        }
        
        return $keyword;
    }
    
    return false;
}

// Get random keyword (simple implementation)
function yourls_get_random_keyword() {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $keyword = '';
    $length = 6;
    
    for ($i = 0; $i < $length; $i++) {
        $keyword .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $keyword;
}

// Database functions
function upload_create_database_tables() {
    global $ydb;
    
    // Create uploads table
    $table = YOURLS_DB_PREFIX . 'uploads';
    $sql = "CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `short_url` varchar(255) NOT NULL,
        `original_name` varchar(255) NOT NULL,
        `file_path` varchar(500) NOT NULL,
        `file_size` bigint(20) NOT NULL,
        `mime_type` varchar(100) NOT NULL,
        `upload_date` datetime NOT NULL,
        `download_count` int(11) DEFAULT 0,
        `expires_at` datetime DEFAULT NULL,
        `storage_location` varchar(255) DEFAULT 'default',
        PRIMARY KEY (`id`),
        UNIQUE KEY `short_url` (`short_url`),
        KEY `upload_date` (`upload_date`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $ydb->query($sql);
}

function upload_set_default_settings() {
    global $ydb;
    
    $defaults = array(
        'upload_max_file_size' => 10,
        'upload_allowed_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip',
        'upload_file_retention' => 'never',
        'upload_storage_location' => YOURLS_ABSPATH . '/uploads/',
        'upload_frontend_upload_enabled' => 'no',
        'upload_frontend_max_file_size' => 5,
        'upload_frontend_allowed_types' => 'jpg,jpeg,png,gif,pdf,txt'
    );
    
    foreach ($defaults as $key => $value) {
        $query = "SELECT option_value FROM " . YOURLS_DB_PREFIX . "options WHERE option_name = :key";
        $existing = $ydb->fetchValue($query, ['key' => $key]);
        if (!$existing) {
            $insert_query = "INSERT INTO " . YOURLS_DB_PREFIX . "options (option_name, option_value) VALUES (:option_name, :option_value)";
            $ydb->perform($insert_query, ['option_name' => $key, 'option_value' => $value]);
        }
    }
}

function upload_get_settings() {
    global $ydb;
    
    $settings = array();
    $query = "SELECT option_name, option_value FROM " . YOURLS_DB_PREFIX . "options WHERE option_name LIKE 'upload_%'";
    $options = $ydb->fetchAll($query);
    
    foreach ($options as $option) {
        $key = str_replace('upload_', '', $option['option_name']);
        $settings[$key] = $option['option_value'];
    }
    
    // Handle the frontend_enabled key from database (upload_frontend_upload_enabled)
    if (isset($settings['frontend_enabled'])) {
        // Move frontend_enabled to frontend_upload_enabled for consistency
        $settings['frontend_upload_enabled'] = $settings['frontend_enabled'];
        unset($settings['frontend_enabled']);
    }
    
    // Set defaults ONLY if not found in database
    $defaults = array(
        'max_file_size' => 10,
        'allowed_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip',
        'file_retention' => 'never',
        'storage_location' => YOURLS_ABSPATH . '/uploads/',
        'frontend_max_file_size' => 5,
        'frontend_allowed_types' => 'jpg,jpeg,png,gif,pdf,txt'
    );
    
    // Only set defaults for keys that don't exist in the database
    foreach ($defaults as $key => $default) {
        if (!isset($settings[$key])) {
            $settings[$key] = $default;
        }
    }
    
    // Set default for frontend_upload_enabled only if not in database
    if (!isset($settings['frontend_upload_enabled'])) {
        $settings['frontend_upload_enabled'] = 'no';
    }
    
    
    return $settings;
}

function upload_save_settings() {
    global $ydb;
    
    $settings = array(
        'upload_max_file_size' => intval($_POST['max_file_size']),
        'upload_allowed_types' => sanitize_text_field($_POST['allowed_types']),
        'upload_file_retention' => sanitize_text_field($_POST['file_retention']),
        'upload_storage_location' => YOURLS_ABSPATH . '/uploads/', // Force web-accessible location
        'upload_frontend_upload_enabled' => sanitize_text_field($_POST['frontend_upload_enabled']),
        'upload_frontend_max_file_size' => intval($_POST['frontend_max_file_size']),
        'upload_frontend_allowed_types' => sanitize_text_field($_POST['frontend_allowed_types'])
    );
    
    foreach ($settings as $key => $value) {
        $query = "SELECT option_value FROM " . YOURLS_DB_PREFIX . "options WHERE option_name = :key";
        $existing = $ydb->fetchValue($query, ['key' => $key]);
        if ($existing) {
            $update_query = "UPDATE " . YOURLS_DB_PREFIX . "options SET option_value = :option_value WHERE option_name = :option_name";
            $ydb->perform($update_query, ['option_value' => $value, 'option_name' => $key]);
        } else {
            $insert_query = "INSERT INTO " . YOURLS_DB_PREFIX . "options (option_name, option_value) VALUES (:option_name, :option_value)";
            $ydb->perform($insert_query, ['option_name' => $key, 'option_value' => $value]);
        }
    }
    
            echo '<div style="color: #c6f6d5; padding: 15px; background: #22543d; border: 1px solid #48bb78; border-radius: 8px; margin: 15px 0; border-left: 4px solid #48bb78;">';
            echo '<strong style="color: #c6f6d5;">✅ Settings saved successfully!</strong>';
            echo '</div>';
}

function upload_get_files() {
    global $ydb;
    
    $table = YOURLS_DB_PREFIX . 'uploads';
    $query = "SELECT * FROM `$table` ORDER BY upload_date DESC";
    $files = $ydb->fetchAll($query);
    
    return $files ? $files : array();
}

function upload_delete_file($file_id) {
    global $ydb;
    
    $table = YOURLS_DB_PREFIX . 'uploads';
    $query = "SELECT * FROM `$table` WHERE id = :id";
    $file = $ydb->fetchOne($query, ['id' => $file_id]);
    
    if ($file) {
        // Delete physical file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        // Delete from database
        $delete_query = "DELETE FROM `$table` WHERE id = :id";
        $ydb->perform($delete_query, ['id' => $file_id]);
        
        // Delete from YOURLS URLs table
        $url_delete_query = "DELETE FROM " . YOURLS_DB_PREFIX . "url WHERE keyword = :keyword";
        $ydb->perform($url_delete_query, ['keyword' => $file['short_url']]);
        
                echo '<div style="color: #c6f6d5; padding: 15px; background: #22543d; border: 1px solid #48bb78; border-radius: 8px; margin: 15px 0; border-left: 4px solid #48bb78;">';
                echo '<strong style="color: #c6f6d5;">✅ File deleted successfully!</strong>';
                echo '</div>';
    }
}

function upload_format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function sanitize_text_field($text) {
    return trim(strip_tags($text));
}

// Get server upload limits
function upload_get_server_limits() {
    $limits = array();
    
    // Get PHP upload limits
    $limits['upload_max_filesize'] = ini_get('upload_max_filesize');
    $limits['post_max_size'] = ini_get('post_max_size');
    $limits['max_file_uploads'] = ini_get('max_file_uploads');
    $limits['memory_limit'] = ini_get('memory_limit');
    $limits['max_execution_time'] = ini_get('max_execution_time');
    
    // Convert to bytes for comparison
    $limits['upload_max_filesize_bytes'] = upload_parse_size($limits['upload_max_filesize']);
    $limits['post_max_size_bytes'] = upload_parse_size($limits['post_max_size']);
    
    return $limits;
}

// Parse size string (e.g., "10M", "2G") to bytes
function upload_parse_size($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

// Register admin pages using the same approach as working plugins
yourls_add_action( 'plugins_loaded', 'upload_register_pages' );
function upload_register_pages() {
    yourls_register_plugin_page( 'upload-and-shorten', 'Upload & Shorten', 'upload_admin_page' );
    yourls_register_plugin_page( 'upload-settings', 'Upload Settings', 'upload_settings_page' );
    yourls_register_plugin_page( 'upload-files', 'Uploaded Files', 'upload_files_page' );
}

// Register hooks
yourls_add_action('plugins_loaded', 'upload_plugin_init');
yourls_add_action('deactivated_plugin', 'upload_plugin_deactivation');
yourls_add_action('activated_plugin', 'upload_plugin_activation');

// Add frontend upload interface - support both standard and custom frontends
yourls_add_action('html_head', 'upload_frontend_styles');
yourls_add_action('html_footer', 'upload_frontend_scripts');

// Hook for standard YOURLS frontend
yourls_add_action('html_footer', 'upload_frontend_interface_standard');

// Hook for custom frontend (Sleeky2)
yourls_add_action('html_footer', 'upload_frontend_interface_custom');

// Frontend upload functions
function upload_frontend_styles() {
    $settings = upload_get_settings();
    if ($settings['frontend_upload_enabled'] == 'yes') {
        echo '<style>
        .upload-frontend-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .upload-frontend-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .upload-frontend-header h3 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .upload-frontend-header p {
            color: #4a5568;
            font-size: 14px;
        }
        
        .upload-frontend-form {
            margin-bottom: 20px;
        }
        
        .upload-frontend-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            background: #f7fafc;
            color: #2d3748;
            font-size: 14px;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }
        
        .upload-frontend-input:hover {
            border-color: #667eea;
        }
        
        .upload-frontend-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s ease;
            margin-top: 10px;
        }
        
        .upload-frontend-button:hover {
            transform: translateY(-2px);
        }
        
        .upload-frontend-info {
            background: #e6fffa;
            border: 1px solid #81e6d9;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .upload-frontend-info h4 {
            color: #234e52;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .upload-frontend-info ul {
            color: #234e52;
            margin: 0;
            padding-left: 20px;
        }
        
        .upload-frontend-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .upload-frontend-success h4 {
            color: #22543d;
            margin-bottom: 10px;
        }
        
        .upload-frontend-success a {
            color: #38a169;
            text-decoration: none;
            font-weight: 600;
        }
        
        .upload-frontend-error {
            background: #fed7d7;
            border: 1px solid #fc8181;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .upload-frontend-error h4 {
            color: #742a2a;
            margin-bottom: 10px;
        }
        </style>';
    }
}

function upload_frontend_scripts() {
    $settings = upload_get_settings();
    if ($settings['frontend_upload_enabled'] == 'yes') {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const uploadForm = document.getElementById("upload-frontend-form");
            if (uploadForm) {
                uploadForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    
                    const fileInput = document.getElementById("upload-frontend-file");
                    const submitButton = document.getElementById("upload-frontend-submit");
                    
                    if (!fileInput.files.length) {
                        alert("Please select a file to upload.");
                        return;
                    }
                    
                    submitButton.disabled = true;
                    submitButton.value = "Uploading...";
                    
                    const formData = new FormData();
                    formData.append("upload_file", fileInput.files[0]);
                    formData.append("frontend_upload", "1");
                    
                    fetch(window.location.href, {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Reload the page to show the result
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("Upload failed. Please try again.");
                        submitButton.disabled = false;
                        submitButton.value = "Upload & Shorten";
                    });
                });
            }
        });
        </script>';
    }
}

function upload_frontend_interface_standard() {
    $settings = upload_get_settings();
    
    // Only show on the main page and if frontend uploads are enabled
    if ($settings['frontend_upload_enabled'] == 'yes' && !yourls_is_admin()) {
        // Check if this is the standard YOURLS frontend (not custom Sleeky2)
        if (!file_exists('frontend/header.php')) {
            $allowed_types = explode(',', $settings['frontend_allowed_types']);
            $allowed_types_upper = array_map('strtoupper', $allowed_types);
            
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Find the URL shortening form and add upload interface after it
                const shortenForm = document.querySelector("form[method=\"post\"]");
                if (shortenForm && !document.querySelector(".upload-frontend-container")) {
                    const uploadInterface = `' . addslashes('
                    <div class="upload-frontend-container">
                        <div class="upload-frontend-header">
                            <h3>📤 Upload & Shorten File</h3>
                            <p>Upload a file and get a short URL to share it</p>
                        </div>
                        
                        <div class="upload-frontend-info">
                            <h4>📋 Upload Guidelines:</h4>
                            <ul>
                                <li><strong>Max file size:</strong> ' . $settings['frontend_max_file_size'] . ' MB</li>
                                <li><strong>Allowed types:</strong> ' . implode(', ', $allowed_types_upper) . '</li>
                                <li><strong>Privacy:</strong> Files are publicly accessible via the short URL</li>
                            </ul>
                        </div>
                        
                        <form id="upload-frontend-form" method="post" enctype="multipart/form-data">
                            <div class="upload-frontend-form">
                                <input type="file" name="upload_file" id="upload-frontend-file" class="upload-frontend-input" accept=".' . implode(',.', $allowed_types) . '" required>
                                <input type="hidden" name="frontend_upload" value="1">
                            </div>
                            <div style="text-align: center;">
                                <input type="submit" id="upload-frontend-submit" class="upload-frontend-button" value="🚀 Upload & Shorten">
                            </div>
                        </form>
                    </div>') . '`;
                    
                    shortenForm.insertAdjacentHTML("afterend", uploadInterface);
                }
            });
            </script>';
        }
    }
}

function upload_frontend_interface_custom() {
    $settings = upload_get_settings();
    
    // Only show on the main page and if frontend uploads are enabled
    if ($settings['frontend_upload_enabled'] == 'yes' && !yourls_is_admin()) {
        // Check if this is the custom Sleeky2 frontend
        if (file_exists('frontend/header.php')) {
            $allowed_types = explode(',', $settings['frontend_allowed_types']);
            $allowed_types_upper = array_map('strtoupper', $allowed_types);
            
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Find the URL shortening form in Sleeky2 frontend and add upload interface after it
                const shortenForm = document.querySelector("form[method=\"post\"]");
                if (shortenForm && !document.querySelector(".upload-frontend-container")) {
                    const uploadInterface = `' . addslashes('
                    <div class="card-body px-md-5">
                        <div style="background: rgba(255, 255, 255, 0.95); border-radius: 12px; padding: 25px; margin: 20px 0; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px);">
                            <div style="text-align: center; margin-bottom: 20px;">
                                <h3 style="color: #2d3748; margin-bottom: 10px; font-size: 24px;">📤 Upload & Shorten File</h3>
                                <p style="color: #4a5568; font-size: 14px;">Upload a file and get a short URL to share it</p>
                            </div>
                            
                            <div style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 15px; margin: 15px 0;">
                                <h4 style="color: #234e52; margin-bottom: 10px; font-size: 16px;">📋 Upload Guidelines:</h4>
                                <ul style="color: #234e52; margin: 0; padding-left: 20px;">
                                    <li><strong>Max file size:</strong> ' . $settings['frontend_max_file_size'] . ' MB</li>
                                    <li><strong>Allowed types:</strong> ' . strtoupper(str_replace(',', ', ', $settings['frontend_allowed_types'])) . '</li>
                                    <li><strong>Privacy:</strong> Files are publicly accessible via the short URL</li>
                                </ul>
                            </div>
                            
                            <form method="post" enctype="multipart/form-data" style="margin-bottom: 20px;">
                                <div style="margin-bottom: 20px;">
                                    <input type="file" name="upload_file" style="width: 100%; padding: 12px; border: 2px dashed #cbd5e0; border-radius: 8px; background: #f7fafc; color: #2d3748; font-size: 14px; transition: border-color 0.3s ease; cursor: pointer;" accept=".' . str_replace(',', ',.', $settings['frontend_allowed_types']) . '" required>
                                    <input type="hidden" name="frontend_upload" value="1">
                                </div>
                                <div style="text-align: center;">
                                    <input type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 25px; font-size: 14px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: transform 0.2s ease; margin-top: 10px;" value="🚀 Upload & Shorten">
                                </div>
                            </form>
                        </div>
                    </div>') . '`;
                    
                    shortenForm.insertAdjacentHTML("afterend", uploadInterface);
                }
            });
            </script>';
        }
    }
}

// Handle frontend file uploads
yourls_add_action('init', 'upload_handle_frontend_upload');

function upload_handle_frontend_upload() {
    if (isset($_POST['frontend_upload']) && $_POST['frontend_upload'] == '1') {
        $settings = upload_get_settings();
        
        if ($settings['frontend_upload_enabled'] != 'yes') {
            return;
        }
        
        try {
            $file = $_FILES['upload_file'];
            
            // Basic validation
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['upload_error'] = 'Upload failed with error code: ' . $file['error'];
                return;
            }
            
            // Get frontend settings for validation
            $allowed_types = array_map('trim', explode(',', $settings['frontend_allowed_types']));
            $max_size = $settings['frontend_max_file_size'] * 1024 * 1024; // Convert MB to bytes
            
            // Check file size
            if ($file['size'] > $max_size) {
                $_SESSION['upload_error'] = 'File too large. Maximum size allowed: ' . $settings['frontend_max_file_size'] . ' MB';
                return;
            }
            
            // Check file type
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_types)) {
                $_SESSION['upload_error'] = 'File type not allowed. Supported types: ' . implode(', ', array_map('strtoupper', $allowed_types));
                return;
            }
            
            // Force web-accessible upload directory
            $upload_dir = YOURLS_ABSPATH . '/uploads/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $_SESSION['upload_error'] = 'Failed to create upload directory';
                    return;
                }
            }
            
            // Generate unique filename
            $unique_filename = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . '/' . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Create web-accessible URL for the file
                $web_url = YOURLS_SITE . '/uploads/' . $unique_filename;
                
                // Create short URL using YOURLS database
                $short_url = upload_create_short_url($file_path, $file['name'], $web_url);
                
                if ($short_url) {
                    $_SESSION['upload_success'] = array(
                        'short_url' => $short_url,
                        'file_name' => $file['name'],
                        'file_size' => $file['size'],
                        'file_type' => $file_extension
                    );
                } else {
                    $_SESSION['upload_error'] = 'Failed to create short URL';
                }
            } else {
                $_SESSION['upload_error'] = 'Failed to move uploaded file';
            }
            
        } catch (Exception $e) {
            $_SESSION['upload_error'] = 'Upload error: ' . $e->getMessage();
            error_log('Frontend upload error: ' . $e->getMessage());
        }
    }
}

echo "<!-- Upload plugin v" . UPLOAD_PLUGIN_VERSION . " loaded -->\n";
echo "<!-- DEBUG: Plugin file loaded at " . date('Y-m-d H:i:s') . " -->\n";
?>
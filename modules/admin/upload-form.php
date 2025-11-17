<?php
/**
 * Admin Upload Form Module
 * Enhanced admin upload interface with modern UI
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display enhanced admin upload form
 */
function upload_display_admin_form() {
    // Check if form was submitted
    $upload_result = '';
    if (isset($_POST['upload_submit']) && $_POST['upload_submit'] !== '') {
        $upload_result = upload_process_admin_upload();
    }
    
    // Get current settings
    $storage_locations = upload_get_storage_locations();
    $default_storage = upload_get_setting('default_storage', 'default');
    $expiration_options = upload_get_expiration_options();
    $default_expiration = upload_get_default_expiration();
    
    echo '<div class="upload-admin-container">';
    
    // Display result message
    if ($upload_result) {
        echo $upload_result;
    }
    
    // Upload form
    echo '<form method="post" enctype="multipart/form-data" id="upload-form" class="upload-form">';
    echo wp_nonce_field('upload_admin_form', 'upload_nonce', true, false);
    
    // File selection
    echo '<div class="upload-section">';
    echo '<h3>' . __('Select File', 'upload-and-shorten') . '</h3>';
    echo '<div class="file-input-container">';
    echo '<input type="file" id="file_upload" name="file_upload" class="file-input" required>';
    echo '<label for="file_upload" class="file-input-label">';
    echo '<span class="file-input-text">' . __('Choose file or drag and drop', 'upload-and-shorten') . '</span>';
    echo '<span class="file-input-button">' . __('Browse', 'upload-and-shorten') . '</span>';
    echo '</label>';
    echo '</div>';
    echo '<div id="file-preview" class="file-preview" style="display: none;"></div>';
    echo '</div>';
    
    // Storage location selection
    if (!empty($storage_locations)) {
        echo '<div class="upload-section">';
        echo '<h3>' . __('Storage Location', 'upload-and-shorten') . '</h3>';
        echo '<select name="storage_location" id="storage_location" class="form-select">';
        foreach ($storage_locations as $key => $location) {
            if ($location['enabled']) {
                $selected = ($key === $default_storage) ? ' selected' : '';
                echo '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($location['name']) . '</option>';
            }
        }
        echo '</select>';
        echo '</div>';
    }
    
    // YOURLS options
    echo '<div class="upload-section">';
    echo '<h3>' . __('YOURLS Options', 'upload-and-shorten') . '</h3>';
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
    echo '</div>';
    
    // Expiration settings
    echo '<div class="upload-section">';
    echo '<h3>' . __('File Expiration', 'upload-and-shorten') . '</h3>';
    echo '<div class="form-row">';
    echo '<div class="form-group">';
    echo '<label for="expiration_type">' . __('Expiration Period', 'upload-and-shorten') . '</label>';
    echo '<select name="expiration_type" id="expiration_type" class="form-select">';
    foreach ($expiration_options as $value => $label) {
        $selected = ($value === $default_expiration) ? ' selected' : '';
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '<div class="form-group" id="custom-days-group" style="display: none;">';
    echo '<label for="custom_days">' . __('Custom Days', 'upload-and-shorten') . '</label>';
    echo '<input type="number" id="custom_days" name="custom_days" class="form-control" min="1" max="365" value="' . upload_get_custom_expiration_days() . '">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Filename handling
    echo '<div class="upload-section">';
    echo '<h3>' . __('Filename Handling', 'upload-and-shorten') . '</h3>';
    echo '<div class="radio-group">';
    
    $filename_options = [
        'original' => [
            'label' => __('Keep Original', 'upload-and-shorten'),
            'description' => __('Filename will not be changed', 'upload-and-shorten')
        ],
        'browser-safe' => [
            'label' => __('Browser Safe', 'upload-and-shorten'),
            'description' => __('Remove unsafe characters for web browsers', 'upload-and-shorten')
        ],
        'randomized' => [
            'label' => __('Randomized', 'upload-and-shorten'),
            'description' => __('Generate random filename for privacy', 'upload-and-shorten')
        ]
    ];
    
    foreach ($filename_options as $value => $option) {
        $checked = ($value === 'original') ? ' checked' : '';
        echo '<div class="radio-option">';
        echo '<input type="radio" id="filename_' . $value . '" name="filename_method" value="' . $value . '"' . $checked . '>';
        echo '<label for="filename_' . $value . '">';
        echo '<strong>' . esc_html($option['label']) . '</strong><br>';
        echo '<small>' . esc_html($option['description']) . '</small>';
        echo '</label>';
        echo '</div>';
    }
    
    echo '<div class="checkbox-option">';
    echo '<input type="checkbox" id="drop_extension" name="drop_extension">';
    echo '<label for="drop_extension">';
    echo '<strong>' . __('Hide File Extension', 'upload-and-shorten') . '</strong><br>';
    echo '<small>' . __('Remove file extension from filename', 'upload-and-shorten') . '</small>';
    echo '</label>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    // Submit button
    echo '<div class="upload-section">';
    echo '<button type="submit" name="upload_submit" class="btn btn-primary btn-upload">';
    echo '<span class="btn-text">' . __('Upload & Create Short URL', 'upload-and-shorten') . '</span>';
    echo '<span class="btn-loading" style="display: none;">' . __('Uploading...', 'upload-and-shorten') . '</span>';
    echo '</button>';
    echo '</div>';
    
    echo '</form>';
    echo '</div>';
    
    // Add JavaScript for form enhancements
    upload_add_form_scripts();
}


/**
 * Display success message
 * 
 * @param array $short_url_result Short URL result
 * @param array $move_result Move result
 * @param string $original_filename Original filename
 * @return string Success message HTML
 */
function upload_display_success($short_url_result, $move_result, $original_filename) {
    $html = '<div class="upload-success">';
    $html .= '<h4>' . sprintf(__('File "%s" uploaded successfully!', 'upload-and-shorten'), esc_html($original_filename)) . '</h4>';
    $html .= '<div class="upload-links">';
    
    // Direct link
    $html .= '<div class="link-group">';
    $html .= '<label>' . __('Direct Link:', 'upload-and-shorten') . '</label>';
    $html .= '<div class="link-container">';
    $html .= '<input type="text" value="' . esc_attr($move_result['web_url']) . '" readonly class="link-input">';
    $html .= '<button type="button" class="btn-copy" data-copy="' . esc_attr($move_result['web_url']) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Short link
    $html .= '<div class="link-group">';
    $html .= '<label>' . __('Short URL:', 'upload-and-shorten') . '</label>';
    $html .= '<div class="link-container">';
    $html .= '<input type="text" value="' . esc_attr($short_url_result['shorturl']) . '" readonly class="link-input">';
    $html .= '<button type="button" class="btn-copy" data-copy="' . esc_attr($short_url_result['shorturl']) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Display error message
 * 
 * @param string $message Error message
 * @return string Error message HTML
 */
function upload_display_error($message) {
    return '<div class="upload-error"><strong>' . __('Error:', 'upload-and-shorten') . '</strong> ' . $message . '</div>';
}

/**
 * Add JavaScript for form enhancements
 */
function upload_add_form_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('upload-form');
        const fileInput = document.getElementById('file_upload');
        const filePreview = document.getElementById('file-preview');
        const expirationType = document.getElementById('expiration_type');
        const customDaysGroup = document.getElementById('custom-days-group');
        const submitBtn = document.querySelector('.btn-upload');
        const btnText = document.querySelector('.btn-text');
        const btnLoading = document.querySelector('.btn-loading');
        
        // File input change handler
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                displayFilePreview(file);
            } else {
                filePreview.style.display = 'none';
            }
        });
        
        // Expiration type change handler
        expirationType.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDaysGroup.style.display = 'block';
            } else {
                customDaysGroup.style.display = 'none';
            }
        });
        
        // Form submission handler
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
        });
        
        // Copy button handlers
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-copy')) {
                const text = e.target.getAttribute('data-copy');
                navigator.clipboard.writeText(text).then(function() {
                    e.target.textContent = '<?php _e('Copied!', 'upload-and-shorten'); ?>';
                    setTimeout(function() {
                        e.target.textContent = '<?php _e('Copy', 'upload-and-shorten'); ?>';
                    }, 2000);
                });
            }
        });
        
        // Drag and drop handlers
        const fileInputContainer = document.querySelector('.file-input-container');
        
        fileInputContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        fileInputContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
        
        fileInputContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                displayFilePreview(files[0]);
            }
        });
    });
    
    function displayFilePreview(file) {
        const preview = document.getElementById('file-preview');
        preview.innerHTML = '';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            preview.appendChild(img);
        }
        
        const info = document.createElement('div');
        info.innerHTML = '<strong>' + file.name + '</strong><br>' + 
                        'Size: ' + formatFileSize(file.size) + '<br>' +
                        'Type: ' + file.type;
        preview.appendChild(info);
        
        preview.style.display = 'block';
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    </script>
    
    <style>
    .upload-admin-container {
        max-width: 800px;
        margin: 20px 0;
    }
    
    .upload-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .upload-section h3 {
        margin-top: 0;
        color: #333;
        border-bottom: 2px solid #007cba;
        padding-bottom: 10px;
    }
    
    .file-input-container {
        position: relative;
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        transition: border-color 0.3s;
    }
    
    .file-input-container.drag-over {
        border-color: #007cba;
        background-color: #f0f8ff;
    }
    
    .file-input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-input-label {
        cursor: pointer;
        display: block;
    }
    
    .file-input-text {
        display: block;
        font-size: 16px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .file-input-button {
        background: #007cba;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        display: inline-block;
    }
    
    .file-preview {
        margin-top: 15px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 4px;
        text-align: center;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .form-group {
        flex: 1;
        min-width: 200px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-control, .form-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .radio-group, .checkbox-option {
        margin-bottom: 15px;
    }
    
    .radio-option, .checkbox-option {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px;
        border: 1px solid #eee;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    .radio-option input, .checkbox-option input {
        margin-top: 2px;
    }
    
    .btn-upload {
        background: #007cba;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .btn-upload:hover {
        background: #005a87;
    }
    
    .btn-upload:disabled {
        background: #ccc;
        cursor: not-allowed;
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
    
    .upload-links {
        margin-top: 15px;
    }
    
    .link-group {
        margin-bottom: 15px;
    }
    
    .link-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .link-container {
        display: flex;
        gap: 10px;
    }
    
    .link-input {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #f9f9f9;
    }
    
    .btn-copy {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .btn-copy:hover {
        background: #218838;
    }
    </style>
    <?php
}

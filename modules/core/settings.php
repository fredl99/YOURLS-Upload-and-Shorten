<?php


/**
 * Get file size limit in bytes
 * 
 * @return int File size limit in bytes
 */
function upload_get_file_size_limit() {
    $mb_limit = (int) upload_get_setting('max_file_size', 10);
    return $mb_limit * 1024 * 1024; // Convert MB to bytes
}

/**
 * Set file size limit
 * 
 * @param int $mb_limit Limit in MB
 * @return bool Success status
 */
function upload_set_file_size_limit($mb_limit) {
    $mb_limit = max(1, (int) $mb_limit);
    return upload_update_setting('max_file_size', $mb_limit);
}

/**
 * Get allowed file types
 * 
 * @return array Allowed file types
 */
function upload_get_allowed_file_types() {
    $types = upload_get_setting('allowed_file_types', []);
    
    if (is_string($types)) {
        $types = json_decode($types, true) ?: [];
    }
    
    return $types;
}

/**
 * Set allowed file types
 * 
 * @param array $types File types
 * @return bool Success status
 */
function upload_set_allowed_file_types($types) {
    $types = array_map('strtolower', array_map('trim', $types));
    return upload_update_setting('allowed_file_types', $types);
}

/**
 * Get blocked file types
 * 
 * @return array Blocked file types
 */
function upload_get_blocked_file_types() {
    $types = upload_get_setting('blocked_file_types', []);
    
    if (is_string($types)) {
        $types = json_decode($types, true) ?: [];
    }
    
    return $types;
}

/**
 * Set blocked file types
 * 
 * @param array $types File types
 * @return bool Success status
 */
function upload_set_blocked_file_types($types) {
    $types = array_map('strtolower', array_map('trim', $types));
    return upload_update_setting('blocked_file_types', $types);
}

/**
 * Check if file type is allowed
 * 
 * @param string $extension File extension
 * @return bool Is allowed
 */
function upload_is_file_type_allowed($extension) {
    $extension = strtolower(trim($extension, '.'));
    
    $allowed = upload_get_allowed_file_types();
    $blocked = upload_get_blocked_file_types();
    
    // If blocked list has items, check against it
    if (!empty($blocked)) {
        return !in_array($extension, $blocked);
    }
    
    // If allowed list has items, check against it
    if (!empty($allowed)) {
        return in_array($extension, $allowed);
    }
    
    // Default: allow all if no restrictions
    return true;
}

/**
 * Get frontend upload settings
 * 
 * @return array Frontend settings
 */
function upload_get_frontend_settings() {
    return [
        'enabled' => (bool) upload_get_setting('frontend_uploads_enabled', true),
        'message' => upload_get_setting('frontend_message', 'Upload files and get short URLs instantly!'),
        'require_auth' => (bool) upload_get_setting('require_auth', false),
        'rate_limit_uploads' => (int) upload_get_setting('rate_limit_uploads', 10),
        'rate_limit_window' => (int) upload_get_setting('rate_limit_window', 3600)
    ];
}

/**
 * Update frontend settings
 * 
 * @param array $settings Settings array
 * @return bool Success status
 */
function upload_update_frontend_settings($settings) {
    $success = true;
    
    if (isset($settings['enabled'])) {
        $success &= upload_update_setting('frontend_uploads_enabled', $settings['enabled'] ? '1' : '0');
    }
    
    if (isset($settings['message'])) {
        $success &= upload_update_setting('frontend_message', $settings['message']);
    }
    
    if (isset($settings['require_auth'])) {
        $success &= upload_update_setting('require_auth', $settings['require_auth'] ? '1' : '0');
    }
    
    if (isset($settings['rate_limit_uploads'])) {
        $success &= upload_update_setting('rate_limit_uploads', max(1, (int) $settings['rate_limit_uploads']));
    }
    
    if (isset($settings['rate_limit_window'])) {
        $success &= upload_update_setting('rate_limit_window', max(60, (int) $settings['rate_limit_window']));
    }
    
    return $success;
}

/**
 * Get all plugin settings for admin interface
 * 
 * @return array All settings
 */
function upload_get_admin_settings() {
    return [
        'storage_locations' => upload_get_storage_locations(),
        'default_storage' => upload_get_setting('default_storage', 'default'),
        'expiration_default' => upload_get_default_expiration(),
        'expiration_custom_days' => upload_get_custom_expiration_days(),
        'max_file_size' => upload_get_setting('max_file_size', 10),
        'allowed_file_types' => upload_get_allowed_file_types(),
        'blocked_file_types' => upload_get_blocked_file_types(),
        'frontend' => upload_get_frontend_settings()
    ];
}

/**
 * Update all settings from admin form
 * 
 * @param array $form_data Form data
 * @return bool Success status
 */
function upload_update_admin_settings($form_data) {
    $success = true;
    
    // Update storage locations
    if (isset($form_data['storage_locations'])) {
        $success &= upload_update_setting('storage_locations', $form_data['storage_locations']);
    }
    
    // Update default storage
    if (isset($form_data['default_storage'])) {
        $success &= upload_set_default_storage($form_data['default_storage']);
    }
    
    // Update expiration settings
    if (isset($form_data['expiration_default'])) {
        $success &= upload_set_default_expiration($form_data['expiration_default']);
    }
    
    if (isset($form_data['expiration_custom_days'])) {
        $success &= upload_set_custom_expiration_days($form_data['expiration_custom_days']);
    }
    
    // Update file size limit
    if (isset($form_data['max_file_size'])) {
        $success &= upload_set_file_size_limit($form_data['max_file_size']);
    }
    
    // Update file types
    if (isset($form_data['allowed_file_types'])) {
        $success &= upload_set_allowed_file_types($form_data['allowed_file_types']);
    }
    
    if (isset($form_data['blocked_file_types'])) {
        $success &= upload_set_blocked_file_types($form_data['blocked_file_types']);
    }
    
    // Update frontend settings
    if (isset($form_data['frontend'])) {
        $success &= upload_update_frontend_settings($form_data['frontend']);
    }
    
    return $success;
}

/**
 * Get storage locations
 * 
 * @return array Storage locations
 */
function upload_get_storage_locations() {
    $default_locations = [
        'default' => [
            'name' => 'Default Storage',
            'path' => UPLOAD_PLUGIN_PATH . '/uploads/',
            'url' => UPLOAD_PLUGIN_URL . '/uploads/',
            'enabled' => true,
            'max_size' => 10,
            'auto_cleanup' => 30
        ]
    ];
    
    $saved_locations = upload_get_setting('storage_locations', $default_locations);
    
    // Ensure default location exists
    if (!isset($saved_locations['default'])) {
        $saved_locations['default'] = $default_locations['default'];
    }
    
    return $saved_locations;
}

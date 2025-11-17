<?php
/**
 * CSRF Protection Module
 * Cross-Site Request Forgery protection
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Generate CSRF token
 * 
 * @param string $action Action name
 * @return string CSRF token
 */
function upload_generate_csrf_token($action = 'default') {
    $token = yourls_create_nonce('upload_' . $action);
    return $token;
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @param string $action Action name
 * @return bool Token is valid
 */
function upload_verify_csrf_token($token, $action = 'default') {
    return yourls_verify_nonce($token, 'upload_' . $action);
}

/**
 * Generate CSRF token field
 * 
 * @param string $action Action name
 * @param string $name Field name
 * @return string HTML input field
 */
function upload_csrf_token_field($action = 'default', $name = 'upload_nonce') {
    $token = upload_generate_csrf_token($action);
    return '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($token) . '">';
}

/**
 * Check CSRF protection for request
 * 
 * @param string $action Action name
 * @return bool Request is valid
 */
function upload_check_csrf_protection($action = 'default') {
    $token = $_POST['upload_nonce'] ?? $_GET['upload_nonce'] ?? '';
    return upload_verify_csrf_token($token, $action);
}

/**
 * Require CSRF protection (die if invalid)
 * 
 * @param string $action Action name
 * @return void
 */
function upload_require_csrf_protection($action = 'default') {
    if (!upload_check_csrf_protection($action)) {
        http_response_code(403);
        die('CSRF protection failed');
    }
}

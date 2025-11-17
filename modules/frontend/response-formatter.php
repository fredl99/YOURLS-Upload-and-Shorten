<?php
/**
 * Response Formatter Module
 * Formats responses for frontend and API
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Format upload success response
 * 
 * @param array $result Upload result
 * @param string $format Response format (json, html, xml)
 * @return string Formatted response
 */
function upload_format_success_response($result, $format = 'json') {
    $data = [
        'success' => true,
        'short_url' => $result['short_url'],
        'direct_url' => $result['direct_url'],
        'file_id' => $result['file_id'],
        'file_info' => $result['file_info'],
        'message' => __('File uploaded successfully', 'upload-and-shorten'),
        'timestamp' => date('c')
    ];
    
    switch ($format) {
        case 'json':
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        case 'html':
            return upload_format_html_response($data, 'success');
            
        case 'xml':
            return upload_format_xml_response($data, 'success');
            
        default:
            return json_encode($data);
    }
}

/**
 * Format upload error response
 * 
 * @param string $error Error message
 * @param string $format Response format (json, html, xml)
 * @return string Formatted response
 */
function upload_format_error_response($error, $format = 'json') {
    $data = [
        'success' => false,
        'error' => $error,
        'message' => __('Upload failed', 'upload-and-shorten'),
        'timestamp' => date('c')
    ];
    
    switch ($format) {
        case 'json':
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        case 'html':
            return upload_format_html_response($data, 'error');
            
        case 'xml':
            return upload_format_xml_response($data, 'success');
            
        default:
            return json_encode($data);
    }
}

/**
 * Format HTML response
 * 
 * @param array $data Response data
 * @param string $type Response type (success, error, info)
 * @return string HTML response
 */
function upload_format_html_response($data, $type = 'success') {
    $class = 'upload-' . $type;
    $icon = $type === 'success' ? '✓' : ($type === 'error' ? '✗' : 'ℹ');
    
    $html = '<div class="' . $class . '">';
    $html .= '<div class="response-header">';
    $html .= '<span class="response-icon">' . $icon . '</span>';
    $html .= '<h3>' . esc_html($data['message']) . '</h3>';
    $html .= '</div>';
    
    if ($data['success'] && isset($data['short_url'])) {
        $html .= '<div class="response-links">';
        
        if (isset($data['direct_url'])) {
            $html .= '<div class="link-group">';
            $html .= '<label>' . __('Direct Link:', 'upload-and-shorten') . '</label>';
            $html .= '<div class="link-container">';
            $html .= '<input type="text" value="' . esc_attr($data['direct_url']) . '" readonly class="link-input">';
            $html .= '<button type="button" class="btn-copy" data-copy="' . esc_attr($data['direct_url']) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '<div class="link-group">';
        $html .= '<label>' . __('Short URL:', 'upload-and-shorten') . '</label>';
        $html .= '<div class="link-container">';
        $html .= '<input type="text" value="' . esc_attr($data['short_url']) . '" readonly class="link-input">';
        $html .= '<button type="button" class="btn-copy" data-copy="' . esc_attr($data['short_url']) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
    }
    
    if (!$data['success'] && isset($data['error'])) {
        $html .= '<div class="response-error">';
        $html .= '<p>' . esc_html($data['error']) . '</p>';
        $html .= '</div>';
    }
    
    $html .= '<div class="response-footer">';
    $html .= '<small>' . __('Timestamp:', 'upload-and-shorten') . ' ' . esc_html($data['timestamp']) . '</small>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Format XML response
 * 
 * @param array $data Response data
 * @param string $type Response type
 * @return string XML response
 */
function upload_format_xml_response($data, $type = 'success') {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<response type="' . $type . '">' . "\n";
    $xml .= '  <success>' . ($data['success'] ? 'true' : 'false') . '</success>' . "\n";
    $xml .= '  <message>' . htmlspecialchars($data['message']) . '</message>' . "\n";
    
    if (isset($data['short_url'])) {
        $xml .= '  <short_url>' . htmlspecialchars($data['short_url']) . '</short_url>' . "\n";
    }
    
    if (isset($data['direct_url'])) {
        $xml .= '  <direct_url>' . htmlspecialchars($data['direct_url']) . '</direct_url>' . "\n";
    }
    
    if (isset($data['file_id'])) {
        $xml .= '  <file_id>' . htmlspecialchars($data['file_id']) . '</file_id>' . "\n";
    }
    
    if (isset($data['error'])) {
        $xml .= '  <error>' . htmlspecialchars($data['error']) . '</error>' . "\n";
    }
    
    $xml .= '  <timestamp>' . htmlspecialchars($data['timestamp']) . '</timestamp>' . "\n";
    $xml .= '</response>';
    
    return $xml;
}

/**
 * Format file info response
 * 
 * @param object $file File data
 * @param string $format Response format
 * @return string Formatted response
 */
function upload_format_file_info_response($file, $format = 'json') {
    $data = [
        'success' => true,
        'file_info' => [
            'id' => $file->id,
            'filename' => $file->original_filename,
            'size' => $file->file_size,
            'mime_type' => $file->mime_type,
            'upload_date' => $file->upload_date,
            'expiration_date' => $file->expiration_date,
            'download_count' => $file->download_count,
            'uploaded_by' => $file->uploaded_by,
            'is_accessible' => upload_is_file_accessible($file)
        ],
        'short_url' => $file->short_url,
        'timestamp' => date('c')
    ];
    
    switch ($format) {
        case 'json':
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        case 'html':
            return upload_format_file_info_html($data);
            
        case 'xml':
            return upload_format_file_info_xml($data);
            
        default:
            return json_encode($data);
    }
}

/**
 * Format file info HTML
 * 
 * @param array $data File data
 * @return string HTML response
 */
function upload_format_file_info_html($data) {
    $file = $data['file_info'];
    
    $html = '<div class="file-info">';
    $html .= '<h3>' . __('File Information', 'upload-and-shorten') . '</h3>';
    
    $html .= '<div class="file-details">';
    $html .= '<div class="detail-row">';
    $html .= '<label>' . __('Filename:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . esc_html($file['filename']) . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="detail-row">';
    $html .= '<label>' . __('Size:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . upload_format_file_size($file['size']) . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="detail-row">';
    $html .= '<label>' . __('Type:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . esc_html($file['mime_type']) . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="detail-row">';
    $html .= '<label>' . __('Upload Date:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . esc_html($file['upload_date']) . '</span>';
    $html .= '</div>';
    
    if ($file['expiration_date']) {
        $html .= '<div class="detail-row">';
        $html .= '<label>' . __('Expires:', 'upload-and-shorten') . '</label>';
        $html .= '<span>' . esc_html($file['expiration_date']) . '</span>';
        $html .= '</div>';
    }
    
    $html .= '<div class="detail-row">';
    $html .= '<label>' . __('Downloads:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . number_format($file['download_count']) . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="detail-row">';
    $html .= '<label>' . __('Status:', 'upload-and-shorten') . '</label>';
    $html .= '<span class="status-' . ($file['is_accessible'] ? 'accessible' : 'inaccessible') . '">';
    $html .= $file['is_accessible'] ? __('Accessible', 'upload-and-shorten') : __('Not Accessible', 'upload-and-shorten');
    $html .= '</span>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    if (isset($data['short_url'])) {
        $html .= '<div class="file-links">';
        $html .= '<div class="link-group">';
        $html .= '<label>' . __('Short URL:', 'upload-and-shorten') . '</label>';
        $html .= '<div class="link-container">';
        $html .= '<input type="text" value="' . esc_attr($data['short_url']) . '" readonly class="link-input">';
        $html .= '<button type="button" class="btn-copy" data-copy="' . esc_attr($data['short_url']) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Format file info XML
 * 
 * @param array $data File data
 * @return string XML response
 */
function upload_format_file_info_xml($data) {
    $file = $data['file_info'];
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<file_info>' . "\n";
    $xml .= '  <id>' . htmlspecialchars($file['id']) . '</id>' . "\n";
    $xml .= '  <filename>' . htmlspecialchars($file['filename']) . '</filename>' . "\n";
    $xml .= '  <size>' . htmlspecialchars($file['size']) . '</size>' . "\n";
    $xml .= '  <mime_type>' . htmlspecialchars($file['mime_type']) . '</mime_type>' . "\n";
    $xml .= '  <upload_date>' . htmlspecialchars($file['upload_date']) . '</upload_date>' . "\n";
    $xml .= '  <expiration_date>' . htmlspecialchars($file['expiration_date']) . '</expiration_date>' . "\n";
    $xml .= '  <download_count>' . htmlspecialchars($file['download_count']) . '</download_count>' . "\n";
    $xml .= '  <uploaded_by>' . htmlspecialchars($file['uploaded_by']) . '</uploaded_by>' . "\n";
    $xml .= '  <is_accessible>' . ($file['is_accessible'] ? 'true' : 'false') . '</is_accessible>' . "\n";
    
    if (isset($data['short_url'])) {
        $xml .= '  <short_url>' . htmlspecialchars($data['short_url']) . '</short_url>' . "\n";
    }
    
    $xml .= '  <timestamp>' . htmlspecialchars($data['timestamp']) . '</timestamp>' . "\n";
    $xml .= '</file_info>';
    
    return $xml;
}

/**
 * Format bulk upload response
 * 
 * @param array $results Upload results
 * @param string $format Response format
 * @return string Formatted response
 */
function upload_format_bulk_response($results, $format = 'json') {
    $data = [
        'success' => $results['error_count'] === 0,
        'total_files' => $results['total_files'],
        'success_count' => $results['success_count'],
        'error_count' => $results['error_count'],
        'results' => $results['results'],
        'message' => sprintf(
            __('Processed %d files: %d successful, %d failed', 'upload-and-shorten'),
            $results['total_files'],
            $results['success_count'],
            $results['error_count']
        ),
        'timestamp' => date('c')
    ];
    
    switch ($format) {
        case 'json':
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        case 'html':
            return upload_format_bulk_html($data);
            
        case 'xml':
            return upload_format_bulk_xml($data);
            
        default:
            return json_encode($data);
    }
}

/**
 * Format bulk upload HTML
 * 
 * @param array $data Bulk data
 * @return string HTML response
 */
function upload_format_bulk_html($data) {
    $html = '<div class="bulk-upload-results">';
    $html .= '<h3>' . __('Bulk Upload Results', 'upload-and-shorten') . '</h3>';
    
    $html .= '<div class="bulk-summary">';
    $html .= '<div class="summary-item">';
    $html .= '<label>' . __('Total Files:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . $data['total_files'] . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="summary-item success">';
    $html .= '<label>' . __('Successful:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . $data['success_count'] . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="summary-item error">';
    $html .= '<label>' . __('Failed:', 'upload-and-shorten') . '</label>';
    $html .= '<span>' . $data['error_count'] . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="bulk-details">';
    foreach ($data['results'] as $result) {
        $class = $result['success'] ? 'success' : 'error';
        $html .= '<div class="file-result ' . $class . '">';
        $html .= '<div class="file-name">' . esc_html($result['filename']) . '</div>';
        
        if ($result['success']) {
            $html .= '<div class="file-links">';
            if (isset($result['short_url'])) {
                $html .= '<div class="link-group">';
                $html .= '<label>' . __('Short URL:', 'upload-and-shorten') . '</label>';
                $html .= '<div class="link-container">';
                $html .= '<input type="text" value="' . esc_attr($result['short_url']) . '" readonly class="link-input">';
                $html .= '<button type="button" class="btn-copy" data-copy="' . esc_attr($result['short_url']) . '">' . __('Copy', 'upload-and-shorten') . '</button>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<div class="file-error">' . esc_html($result['error']) . '</div>';
        }
        
        $html .= '</div>';
    }
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Format bulk upload XML
 * 
 * @param array $data Bulk data
 * @return string XML response
 */
function upload_format_bulk_xml($data) {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<bulk_upload_results>' . "\n";
    $xml .= '  <success>' . ($data['success'] ? 'true' : 'false') . '</success>' . "\n";
    $xml .= '  <total_files>' . $data['total_files'] . '</total_files>' . "\n";
    $xml .= '  <success_count>' . $data['success_count'] . '</success_count>' . "\n";
    $xml .= '  <error_count>' . $data['error_count'] . '</error_count>' . "\n";
    $xml .= '  <message>' . htmlspecialchars($data['message']) . '</message>' . "\n";
    $xml .= '  <timestamp>' . htmlspecialchars($data['timestamp']) . '</timestamp>' . "\n";
    
    $xml .= '  <results>' . "\n";
    foreach ($data['results'] as $result) {
        $xml .= '    <file_result>' . "\n";
        $xml .= '      <filename>' . htmlspecialchars($result['filename']) . '</filename>' . "\n";
        $xml .= '      <success>' . ($result['success'] ? 'true' : 'false') . '</success>' . "\n";
        
        if (isset($result['short_url'])) {
            $xml .= '      <short_url>' . htmlspecialchars($result['short_url']) . '</short_url>' . "\n";
        }
        
        if (isset($result['direct_url'])) {
            $xml .= '      <direct_url>' . htmlspecialchars($result['direct_url']) . '</direct_url>' . "\n";
        }
        
        if (isset($result['error'])) {
            $xml .= '      <error>' . htmlspecialchars($result['error']) . '</error>' . "\n";
        }
        
        $xml .= '    </file_result>' . "\n";
    }
    $xml .= '  </results>' . "\n";
    $xml .= '</bulk_upload_results>';
    
    return $xml;
}

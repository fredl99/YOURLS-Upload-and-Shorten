<?php
/**
 * UI Components Module
 * Functions for UI components like modals, buttons, cards, etc.
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Display stats card
 */
function upload_display_stats_card($title, $value, $subtitle = '', $icon = '') {
    echo '<div class="stats-card">';
    
    if (!empty($icon)) {
        echo '<div class="stats-icon">' . $icon . '</div>';
    }
    
    echo '<div class="stats-content">';
    echo '<div class="stats-title">' . esc_html($title) . '</div>';
    echo '<div class="stats-value">' . esc_html($value) . '</div>';
    
    if (!empty($subtitle)) {
        echo '<div class="stats-subtitle">' . esc_html($subtitle) . '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Display action button
 */
function upload_display_action_button($text, $action, $attributes = []) {
    $default_attributes = [
        'class' => 'btn btn-primary',
        'type' => 'button'
    ];
    
    $attributes = array_merge($default_attributes, $attributes);
    
    if (isset($attributes['data-action'])) {
        $attributes['data-action'] = $action;
    }
    
    echo '<button' . upload_build_attributes($attributes) . '>';
    echo esc_html($text);
    echo '</button>';
}

/**
 * Display modal
 */
function upload_display_modal($id, $title, $content, $buttons = []) {
    echo '<div id="' . esc_attr($id) . '" class="upload-modal" style="display: none;">';
    echo '<div class="modal-overlay"></div>';
    echo '<div class="modal-content">';
    
    // Modal header
    echo '<div class="modal-header">';
    echo '<h3 class="modal-title">' . esc_html($title) . '</h3>';
    echo '<button type="button" class="modal-close" data-modal="' . esc_attr($id) . '">&times;</button>';
    echo '</div>';
    
    // Modal body
    echo '<div class="modal-body">';
    echo $content;
    echo '</div>';
    
    // Modal footer
    if (!empty($buttons)) {
        echo '<div class="modal-footer">';
        foreach ($buttons as $button) {
            $button_attributes = $button['attributes'] ?? [];
            $button_text = $button['text'] ?? 'Button';
            $button_class = $button['class'] ?? 'btn btn-secondary';
            
            if (!isset($button_attributes['class'])) {
                $button_attributes['class'] = $button_class;
            }
            
            echo '<button' . upload_build_attributes($button_attributes) . '>';
            echo esc_html($button_text);
            echo '</button>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Display toast notification
 */
function upload_display_toast($message, $type = 'info', $duration = 5000) {
    $toast_id = 'toast_' . uniqid();
    
    echo '<div id="' . $toast_id . '" class="upload-toast toast-' . esc_attr($type) . '" style="display: none;">';
    echo '<div class="toast-content">';
    echo '<div class="toast-message">' . esc_html($message) . '</div>';
    echo '<button type="button" class="toast-close" data-toast="' . $toast_id . '">&times;</button>';
    echo '</div>';
    echo '</div>';
    
    // Auto-hide after duration
    echo '<script>';
    echo 'setTimeout(function() {';
    echo 'var toast = document.getElementById("' . $toast_id . '");';
    echo 'if (toast) { toast.style.display = "none"; }';
    echo '}, ' . $duration . ');';
    echo '</script>';
}

/**
 * Display tab navigation
 */
function upload_display_tabs($tabs, $active_tab = '') {
    if (empty($tabs)) {
        return;
    }
    
    echo '<div class="upload-tabs">';
    echo '<nav class="tab-nav">';
    
    foreach ($tabs as $tab_id => $tab) {
        $tab_class = 'tab-link';
        if ($tab_id === $active_tab) {
            $tab_class .= ' active';
        }
        
        echo '<a href="#' . esc_attr($tab_id) . '" class="' . $tab_class . '" data-tab="' . esc_attr($tab_id) . '">';
        echo esc_html($tab['title']);
        echo '</a>';
    }
    
    echo '</nav>';
    echo '<div class="tab-content">';
    
    foreach ($tabs as $tab_id => $tab) {
        $tab_class = 'tab-pane';
        if ($tab_id === $active_tab) {
            $tab_class .= ' active';
        }
        
        echo '<div id="' . esc_attr($tab_id) . '" class="' . $tab_class . '">';
        if (isset($tab['content'])) {
            echo $tab['content'];
        }
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Display data table
 */
function upload_display_data_table($data, $columns, $attributes = []) {
    if (empty($data) || empty($columns)) {
        return;
    }
    
    $table_class = 'upload-data-table';
    if (isset($attributes['class'])) {
        $table_class .= ' ' . $attributes['class'];
    }
    
    echo '<div class="table-container">';
    echo '<table class="' . $table_class . '">';
    
    // Table header
    echo '<thead>';
    echo '<tr>';
    foreach ($columns as $column) {
        $th_class = isset($column['class']) ? ' class="' . esc_attr($column['class']) . '"' : '';
        echo '<th' . $th_class . '>' . esc_html($column['title']) . '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    
    // Table body
    echo '<tbody>';
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($columns as $column_key => $column) {
            $td_class = isset($column['class']) ? ' class="' . esc_attr($column['class']) . '"' : '';
            $cell_value = isset($row[$column_key]) ? $row[$column_key] : '';
            
            if (isset($column['callback']) && is_callable($column['callback'])) {
                $cell_value = call_user_func($column['callback'], $row, $column_key);
            }
            
            echo '<td' . $td_class . '>' . $cell_value . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    
    echo '</table>';
    echo '</div>';
}

/**
 * Display progress indicator
 */
function upload_display_progress_indicator($steps, $current_step = 0) {
    if (empty($steps)) {
        return;
    }
    
    echo '<div class="progress-indicator">';
    
    foreach ($steps as $index => $step) {
        $step_class = 'progress-step';
        if ($index < $current_step) {
            $step_class .= ' completed';
        } elseif ($index === $current_step) {
            $step_class .= ' current';
        }
        
        echo '<div class="' . $step_class . '">';
        echo '<div class="step-number">' . ($index + 1) . '</div>';
        echo '<div class="step-label">' . esc_html($step) . '</div>';
        echo '</div>';
        
        if ($index < count($steps) - 1) {
            echo '<div class="step-connector"></div>';
        }
    }
    
    echo '</div>';
}

/**
 * Display confirmation dialog
 */
function upload_display_confirmation_dialog($id, $title, $message, $confirm_text = 'Confirm', $cancel_text = 'Cancel') {
    echo '<div id="' . esc_attr($id) . '" class="confirmation-dialog" style="display: none;">';
    echo '<div class="dialog-overlay"></div>';
    echo '<div class="dialog-content">';
    echo '<div class="dialog-header">';
    echo '<h3>' . esc_html($title) . '</h3>';
    echo '</div>';
    echo '<div class="dialog-body">';
    echo '<p>' . esc_html($message) . '</p>';
    echo '</div>';
    echo '<div class="dialog-footer">';
    echo '<button type="button" class="btn btn-secondary dialog-cancel">' . esc_html($cancel_text) . '</button>';
    echo '<button type="button" class="btn btn-danger dialog-confirm">' . esc_html($confirm_text) . '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
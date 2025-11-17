<?php
/**
 * UI Styles Module
 * CSS styles for admin UI components
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Add common styles
 */
function upload_add_common_styles() {
    ?>
    <style>
    /* Loading indicator */
    .upload-loading {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px;
        text-align: center;
    }
    
    .spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007cba;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    .spinner.is-active {
        display: inline-block;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Progress bar */
    .upload-progress {
        margin: 10px 0;
    }
    
    .progress-text {
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .progress-bar {
        width: 100%;
        height: 20px;
        background-color: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #007cba, #005a87);
        transition: width 0.3s ease;
    }
    
    .progress-percentage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 12px;
        font-weight: bold;
        color: #333;
    }
    
    /* File preview */
    .file-preview {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #f9f9f9;
    }
    
    .file-icon {
        font-size: 24px;
    }
    
    .file-info {
        flex: 1;
    }
    
    .file-name {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .file-details {
        display: flex;
        gap: 15px;
        font-size: 12px;
        color: #666;
    }
    
    .file-actions {
        display: flex;
        gap: 10px;
    }
    
    /* Form fields */
    .form-field {
        margin-bottom: 20px;
    }
    
    .form-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-field input,
    .form-field select,
    .form-field textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: #007cba;
        box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
    }
    
    .field-description {
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }
    
    /* File type selector */
    .file-type-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    
    .file-type-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .file-type-option:hover {
        background-color: #f8f9fa;
    }
    
    .file-type-option input[type="checkbox"] {
        width: auto;
        margin: 0;
    }
    
    .file-type-option label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        cursor: pointer;
        flex: 1;
    }
    
    .file-type-icon {
        font-size: 16px;
    }
    
    .file-type-name {
        font-weight: bold;
    }
    
    .file-type-extensions {
        font-size: 11px;
        color: #666;
    }
    
    /* Radio options */
    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    
    .radio-option input[type="radio"] {
        width: auto;
        margin: 0;
    }
    
    .radio-option label {
        margin: 0;
        cursor: pointer;
    }
    
    /* Stats cards */
    .stats-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .stats-icon {
        font-size: 24px;
    }
    
    .stats-content {
        flex: 1;
    }
    
    .stats-title {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .stats-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    
    .stats-subtitle {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    
    /* Buttons */
    .btn {
        display: inline-block;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
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
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c82333;
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .btn-success:hover {
        background: #218838;
    }
    
    .btn-warning {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-warning:hover {
        background: #e0a800;
    }
    
    .btn-info {
        background: #17a2b8;
        color: white;
    }
    
    .btn-info:hover {
        background: #138496;
    }
    
    /* Modal */
    .upload-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .modal-title {
        margin: 0;
        font-size: 18px;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px;
        border-top: 1px solid #ddd;
    }
    
    /* Toast notifications */
    .upload-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 9998;
        min-width: 300px;
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
    }
    
    .toast-message {
        flex: 1;
    }
    
    .toast-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #666;
        margin-left: 10px;
    }
    
    .toast-info {
        border-left: 4px solid #17a2b8;
    }
    
    .toast-success {
        border-left: 4px solid #28a745;
    }
    
    .toast-warning {
        border-left: 4px solid #ffc107;
    }
    
    .toast-error {
        border-left: 4px solid #dc3545;
    }
    
    /* Tabs */
    .upload-tabs {
        margin: 20px 0;
    }
    
    .tab-nav {
        display: flex;
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .tab-link {
        padding: 10px 20px;
        text-decoration: none;
        color: #666;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .tab-link:hover {
        color: #007cba;
    }
    
    .tab-link.active {
        color: #007cba;
        border-bottom-color: #007cba;
    }
    
    .tab-pane {
        display: none;
    }
    
    .tab-pane.active {
        display: block;
    }
    
    /* Data table */
    .table-container {
        overflow-x: auto;
        margin: 20px 0;
    }
    
    .upload-data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border: 1px solid #ddd;
    }
    
    .upload-data-table th,
    .upload-data-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .upload-data-table th {
        background: #f8f9fa;
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    /* Progress indicator */
    .progress-indicator {
        display: flex;
        align-items: center;
        margin: 20px 0;
    }
    
    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
    }
    
    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #ddd;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .progress-step.completed .step-number {
        background: #28a745;
    }
    
    .progress-step.current .step-number {
        background: #007cba;
    }
    
    .step-label {
        font-size: 12px;
        color: #666;
    }
    
    .step-connector {
        width: 50px;
        height: 2px;
        background: #ddd;
        margin: 0 10px;
    }
    
    /* Confirmation dialog */
    .confirmation-dialog {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }
    
    .dialog-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    }
    
    .dialog-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-width: 400px;
        width: 90%;
    }
    
    .dialog-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .dialog-header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .dialog-body {
        padding: 20px;
    }
    
    .dialog-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px;
        border-top: 1px solid #ddd;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .stats-card {
            flex-direction: column;
            text-align: center;
        }
        
        .file-type-selector {
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            width: 95%;
        }
        
        .upload-toast {
            right: 10px;
            left: 10px;
            min-width: auto;
        }
        
        .tab-nav {
            flex-wrap: wrap;
        }
        
        .progress-indicator {
            flex-wrap: wrap;
        }
        
        .step-connector {
            display: none;
        }
    }
    </style>
    <?php
}

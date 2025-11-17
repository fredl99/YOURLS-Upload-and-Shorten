<?php
/**
 * Upload Styles Module
 * CSS styles for frontend upload interface
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Add upload styles
 */
function upload_add_upload_styles() {
    ?>
    <style>
    .upload-frontend-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .upload-form-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 30px;
    }
    
    .upload-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .upload-header h1 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .upload-message {
        color: #666;
        font-size: 16px;
    }
    
    .upload-section {
        margin-bottom: 25px;
    }
    
    .upload-section h3 {
        color: #333;
        margin-bottom: 15px;
        border-bottom: 2px solid #007cba;
        padding-bottom: 5px;
    }
    
    .file-label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
        color: #333;
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
    
    .file-list {
        margin-top: 15px;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .file-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
    }
    
    .file-item:last-child {
        border-bottom: none;
    }
    
    .file-info {
        flex: 1;
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .file-name {
        font-weight: bold;
        color: #333;
    }
    
    .file-size {
        color: #666;
        font-size: 12px;
    }
    
    .file-type {
        color: #666;
        font-size: 12px;
        background: #f0f0f0;
        padding: 2px 6px;
        border-radius: 3px;
    }
    
    .file-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-remove-file {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .btn-remove-file:hover {
        background: #c82333;
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
        color: #333;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .btn-primary {
        background: #007cba;
        color: white;
    }
    
    .btn-primary:hover {
        background: #005a87;
    }
    
    .btn-primary:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    
    .upload-results {
        margin-top: 30px;
    }
    
    .upload-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 20px;
        border-radius: 4px;
    }
    
    .upload-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 20px;
        border-radius: 4px;
    }
    
    .upload-links {
        margin-top: 15px;
    }
    
    .upload-summary {
        margin-bottom: 20px;
        padding: 10px;
        background: #e7f3ff;
        border-radius: 4px;
        text-align: center;
    }
    
    .upload-results-list {
        margin-top: 20px;
    }
    
    .result-item {
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    
    .result-item.success {
        background: #f8f9fa;
        border-color: #28a745;
    }
    
    .result-item.error {
        background: #fff5f5;
        border-color: #dc3545;
    }
    
    .result-filename {
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .result-links {
        margin-top: 10px;
    }
    
    .result-error {
        color: #dc3545;
        font-style: italic;
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
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
        }
        
        .link-container {
            flex-direction: column;
        }
    }
    </style>
    <?php
}

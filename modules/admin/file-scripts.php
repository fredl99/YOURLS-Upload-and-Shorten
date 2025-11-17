<?php
/**
 * File Scripts Module
 * JavaScript and CSS for file manager interface
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Add file manager scripts
 */
function upload_add_file_manager_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const selectAllHeaderCheckbox = document.getElementById('select-all-header');
        const fileCheckboxes = document.querySelectorAll('.file-checkbox');
        
        function updateSelectAllState() {
            const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
            const totalCount = fileCheckboxes.length;
            
            if (checkedCount === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
                selectAllHeaderCheckbox.indeterminate = false;
                selectAllHeaderCheckbox.checked = false;
            } else if (checkedCount === totalCount) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
                selectAllHeaderCheckbox.indeterminate = false;
                selectAllHeaderCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllHeaderCheckbox.indeterminate = true;
            }
        }
        
        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                fileCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectAllState();
            });
        }
        
        // Select all header checkbox
        if (selectAllHeaderCheckbox) {
            selectAllHeaderCheckbox.addEventListener('change', function() {
                fileCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectAllState();
            });
        }
        
        // Individual file checkboxes
        fileCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectAllState);
        });
        
        // Copy URL functionality
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
        
        // Extend expiration
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('extend-expiration')) {
                const fileId = e.target.getAttribute('data-file-id');
                if (confirm('<?php _e('Extend expiration by 30 days?', 'upload-and-shorten'); ?>')) {
                    extendFileExpiration(fileId);
                }
            }
        });
        
        // Delete file
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-file')) {
                const fileId = e.target.getAttribute('data-file-id');
                if (confirm('<?php _e('Are you sure you want to delete this file?', 'upload-and-shorten'); ?>')) {
                    deleteFile(fileId);
                }
            }
        });
        
        // Bulk actions
        const bulkForm = document.querySelector('.bulk-form');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
                const bulkAction = document.querySelector('.bulk-action-select').value;
                
                if (selectedFiles.length === 0) {
                    e.preventDefault();
                    alert('<?php _e('Please select files first', 'upload-and-shorten'); ?>');
                    return;
                }
                
                if (!bulkAction) {
                    e.preventDefault();
                    alert('<?php _e('Please select an action', 'upload-and-shorten'); ?>');
                    return;
                }
            });
        }
    });
    
    function extendFileExpiration(fileId) {
        const formData = new FormData();
        formData.append('action', 'extend_file_expiration');
        formData.append('file_id', fileId);
        formData.append('nonce', '<?php echo wp_create_nonce('upload_file_action'); ?>');
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    function deleteFile(fileId) {
        const formData = new FormData();
        formData.append('action', 'delete_file');
        formData.append('file_id', fileId);
        formData.append('nonce', '<?php echo wp_create_nonce('upload_file_action'); ?>');
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    </script>
    <?php
    
    // Add CSS
    upload_add_file_manager_styles();
}

/**
 * Add file manager styles
 */
function upload_add_file_manager_styles() {
    ?>
    <style>
    .file-manager-container {
        margin: 20px 0;
    }
    
    .file-manager-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }
    
    .file-stats {
        color: #666;
        font-size: 14px;
    }
    
    .file-filters {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .filter-form {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .filter-group label {
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    .filter-group select {
        padding: 5px 10px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    
    .bulk-actions {
        background: #fff3cd;
        padding: 10px 15px;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .bulk-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .bulk-action-select {
        padding: 5px 10px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    
    .files-table-container {
        overflow-x: auto;
    }
    
    .files-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border: 1px solid #ddd;
    }
    
    .files-table th,
    .files-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .files-table th {
        background: #f8f9fa;
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    .file-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .file-icon {
        font-size: 20px;
    }
    
    .file-details {
        flex: 1;
    }
    
    .file-name {
        font-weight: bold;
        margin-bottom: 2px;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .file-mime {
        font-size: 11px;
        color: #666;
    }
    
    .url-container {
        display: flex;
        gap: 5px;
    }
    
    .url-input {
        flex: 1;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 11px;
    }
    
    .btn-copy {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 11px;
    }
    
    .btn-copy:hover {
        background: #218838;
    }
    
    .expiration.expired {
        color: #dc3545;
        font-weight: bold;
    }
    
    .expiration.expiring-soon {
        color: #ffc107;
        font-weight: bold;
    }
    
    .expiration.never {
        color: #6c757d;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .btn {
        padding: 5px 10px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 11px;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-sm {
        padding: 3px 8px;
        font-size: 10px;
    }
    
    .btn-primary {
        background: #007cba;
        color: white;
    }
    
    .btn-info {
        background: #17a2b8;
        color: white;
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-warning {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .no-files {
        text-align: center;
        padding: 40px;
        color: #666;
        font-style: italic;
    }
    
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }
    
    .pagination {
        display: flex;
        gap: 5px;
    }
    
    .pagination-link {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-decoration: none;
        color: #007cba;
        border-radius: 3px;
    }
    
    .pagination-link:hover {
        background: #f8f9fa;
    }
    
    .pagination-link.current {
        background: #007cba;
        color: white;
        border-color: #007cba;
    }
    
    .pagination-link.disabled {
        color: #6c757d;
        cursor: not-allowed;
    }
    
    .pagination-ellipsis {
        padding: 8px 12px;
        color: #6c757d;
    }
    
    .pagination-info {
        color: #666;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .bulk-controls {
            flex-wrap: wrap;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .pagination-container {
            flex-direction: column;
            gap: 10px;
        }
    }
    </style>
    <?php
}

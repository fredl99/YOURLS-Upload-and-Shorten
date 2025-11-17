<?php
/**
 * Upload Scripts Module
 * JavaScript for frontend upload interface
 * 
 * @package YOURLS-Upload-and-Shorten
 * @version 2.0.0
 * @author News Targeted
 * @since 1.0.0
 */

// Module loaded by plugin

/**
 * Add upload JavaScript
 */
function upload_add_upload_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('frontend-upload-form');
        const fileInput = document.getElementById('file_upload');
        const filePreview = document.getElementById('file-preview');
        const fileList = document.getElementById('file-list');
        const expirationType = document.getElementById('expiration_type');
        const customDaysGroup = document.getElementById('custom-days-group');
        const submitBtn = document.querySelector('.btn-upload');
        const btnText = document.querySelector('.btn-text');
        const btnLoading = document.querySelector('.btn-loading');
        const resultsContainer = document.getElementById('upload-results');
        
        // File input change handler
        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            if (files.length > 0) {
                if (files.length === 1) {
                    displayFilePreview(files[0]);
                } else {
                    displayFileList(files);
                }
            } else {
                filePreview.style.display = 'none';
                fileList.style.display = 'none';
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
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            
            // Submit via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                displayUploadResult(data);
            })
            .catch(error => {
                displayUploadError('Upload failed: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            });
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
            
            const files = Array.from(e.dataTransfer.files);
            if (files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                if (files.length === 1) {
                    displayFilePreview(files[0]);
                } else {
                    displayFileList(files);
                }
            }
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
    });
    
    function displayFilePreview(file) {
        const preview = document.getElementById('file-preview');
        const fileList = document.getElementById('file-list');
        
        preview.innerHTML = '';
        fileList.style.display = 'none';
        
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
    
    function displayFileList(files) {
        const preview = document.getElementById('file-preview');
        const fileList = document.getElementById('file-list');
        
        preview.style.display = 'none';
        fileList.innerHTML = '';
        
        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-info">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${formatFileSize(file.size)}</span>
                    <span class="file-type">${file.type}</span>
                </div>
                <div class="file-actions">
                    <button type="button" class="btn-remove-file" data-index="${index}">Remove</button>
                </div>
            `;
            fileList.appendChild(fileItem);
        });
        
        fileList.style.display = 'block';
        
        // Add remove file handlers
        fileList.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-file')) {
                const index = parseInt(e.target.getAttribute('data-index'));
                removeFileFromList(index);
            }
        });
    }
    
    function removeFileFromList(index) {
        const dt = new DataTransfer();
        const fileInput = document.getElementById('file_upload');
        const files = Array.from(fileInput.files);
        
        files.splice(index, 1);
        
        files.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        
        if (files.length === 0) {
            document.getElementById('file-list').style.display = 'none';
        } else if (files.length === 1) {
            displayFilePreview(files[0]);
        } else {
            displayFileList(files);
        }
    }
    
    function displayUploadResult(data) {
        const resultsContainer = document.getElementById('upload-results');
        
        if (data.success) {
            if (data.results && data.results.length > 1) {
                // Multiple files uploaded
                let html = `
                    <div class="upload-success">
                        <h3><?php _e('Upload Complete!', 'upload-and-shorten'); ?></h3>
                        <div class="upload-summary">
                            <p><?php _e('Successfully uploaded', 'upload-and-shorten'); ?>: ${data.success_count} / ${data.total_files} <?php _e('files', 'upload-and-shorten'); ?></p>
                        </div>
                        <div class="upload-results-list">
                `;
                
                data.results.forEach((result, index) => {
                    if (result.success) {
                        html += `
                            <div class="result-item success">
                                <div class="result-filename">${result.filename}</div>
                                <div class="result-links">
                                    <div class="link-group">
                                        <label><?php _e('Direct Link:', 'upload-and-shorten'); ?></label>
                                        <div class="link-container">
                                            <input type="text" value="${result.direct_url}" readonly class="link-input">
                                            <button type="button" class="btn-copy" data-copy="${result.direct_url}"><?php _e('Copy', 'upload-and-shorten'); ?></button>
                                        </div>
                                    </div>
                                    <div class="link-group">
                                        <label><?php _e('Short URL:', 'upload-and-shorten'); ?></label>
                                        <div class="link-container">
                                            <input type="text" value="${result.short_url}" readonly class="link-input">
                                            <button type="button" class="btn-copy" data-copy="${result.short_url}"><?php _e('Copy', 'upload-and-shorten'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="result-item error">
                                <div class="result-filename">${result.filename}</div>
                                <div class="result-error">${result.error}</div>
                            </div>
                        `;
                    }
                });
                
                html += `
                        </div>
                    </div>
                `;
                
                resultsContainer.innerHTML = html;
            } else {
                // Single file uploaded
                resultsContainer.innerHTML = `
                    <div class="upload-success">
                        <h3><?php _e('Upload Successful!', 'upload-and-shorten'); ?></h3>
                        <div class="upload-links">
                            <div class="link-group">
                                <label><?php _e('Direct Link:', 'upload-and-shorten'); ?></label>
                                <div class="link-container">
                                    <input type="text" value="${data.direct_url}" readonly class="link-input">
                                    <button type="button" class="btn-copy" data-copy="${data.direct_url}"><?php _e('Copy', 'upload-and-shorten'); ?></button>
                                </div>
                            </div>
                            <div class="link-group">
                                <label><?php _e('Short URL:', 'upload-and-shorten'); ?></label>
                                <div class="link-container">
                                    <input type="text" value="${data.short_url}" readonly class="link-input">
                                    <button type="button" class="btn-copy" data-copy="${data.short_url}"><?php _e('Copy', 'upload-and-shorten'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        } else {
            resultsContainer.innerHTML = `
                <div class="upload-error">
                    <h3><?php _e('Upload Failed', 'upload-and-shorten'); ?></h3>
                    <p>${data.error}</p>
                </div>
            `;
        }
        
        resultsContainer.style.display = 'block';
        resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    function displayUploadError(message) {
        const resultsContainer = document.getElementById('upload-results');
        resultsContainer.innerHTML = `
            <div class="upload-error">
                <h3><?php _e('Upload Failed', 'upload-and-shorten'); ?></h3>
                <p>${message}</p>
            </div>
        `;
        resultsContainer.style.display = 'block';
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    </script>
    <?php
}

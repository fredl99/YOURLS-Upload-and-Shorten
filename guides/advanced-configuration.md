# Advanced Configuration

Advanced customization options for power users and specific use cases.

## Custom Storage Location

You can customize where files are stored beyond the default upload directory.

### Requirements

The storage path should be:

- **Web-accessible** - Files need to be accessible via HTTP
- **Writable** - The web server must have write permissions
- **Secure** - Protected with .htaccess to prevent PHP execution

### Setting Up Custom Storage

1. Create the storage directory:

```bash
mkdir -p /path/to/custom/storage
chown www-data:www-data /path/to/custom/storage
chmod 755 /path/to/custom/storage
```

2. Configure in admin panel:
   - Go to **Upload Settings**
   - Set "Storage Location" to your custom path
   - Test the location using the "Test Storage" button
   - Save settings

### Using Absolute Paths

You can use absolute paths for storage outside the YOURLS directory:

```
/var/www/files/uploads/
/home/user/public_html/files/
/mnt/storage/yourls-uploads/
```

### Using Relative Paths

Relative paths are based on YOURLS installation directory:

```
user/uploads/
../shared-files/
uploads/documents/
```

### Multiple Storage Locations

Configure different locations for different purposes:

```
/uploads/images/      - For images only
/uploads/documents/   - For PDFs and documents
/uploads/archives/    - For ZIP files
/uploads/temporary/   - For files with short expiration
```

## Rate Limiting

Configure rate limits to prevent abuse and control resource usage.

### Default Rate Limits

- **Admin uploads:** Unlimited
- **Frontend uploads:** 10 uploads per hour per IP
- **API uploads:** 20 uploads per hour per API key

### Customizing Rate Limits

Add to your YOURLS `user/config.php`:

```php
// Uploads per hour for frontend users
define('UPLOAD_RATE_LIMIT', 5);

// Time window in seconds (3600 = 1 hour)
define('UPLOAD_RATE_WINDOW', 3600);

// Uploads per hour for authenticated admin users
define('UPLOAD_ADMIN_RATE_LIMIT', 100);
```

### Rate Limit Response

When limit is exceeded, users receive:
- Clear error message indicating rate limit
- Time until they can upload again
- Current upload count in the time window

### Bypassing Rate Limits

To whitelist specific IPs from rate limiting:

```php
define('UPLOAD_RATE_LIMIT_WHITELIST', [
    '192.168.1.100',
    '10.0.0.50',
]);
```

### Monitoring Rate Limits

View rate limit statistics:
- Go to **Admin Panel → Upload Statistics**
- View "Rate Limit Events" section
- See blocked uploads by IP
- Identify potential abuse patterns

## File Validation

The plugin validates files on multiple levels to ensure security and compliance.

### Validation Levels

1. **Extension Checking** - Basic validation based on file extension
2. **MIME Type Validation** - Checks actual file content type
3. **Content Scanning** - Scans file content for malicious patterns
4. **Size Limit Enforcement** - Ensures file doesn't exceed limits
5. **Dangerous Extension Blocking** - Blocks executables and scripts

### Configuring MIME Types

Define allowed MIME types in `user/config.php`:

```php
define('UPLOAD_ALLOWED_MIMES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/zip',
    'text/plain',
]);
```

### Custom Validation Rules

Add custom validation logic:

```php
// In user/config.php or custom plugin
yourls_add_filter('upload_validate_file', 'my_custom_validation');

function my_custom_validation($file) {
    // Custom validation logic
    if ($file['size'] > 5000000 && $file['type'] == 'image/jpeg') {
        return ['error' => 'JPEG images must be under 5MB'];
    }
    return $file;
}
```

### Content Scanning

Enable deep content scanning for enhanced security:

```php
define('UPLOAD_DEEP_SCAN', true);  // Enables thorough content analysis
define('UPLOAD_SCAN_LEVEL', 'strict');  // Options: 'basic', 'moderate', 'strict'
```

### Blocking Specific Patterns

Block files containing specific patterns:

```php
define('UPLOAD_BLOCKED_PATTERNS', [
    '<?php',
    '<script>',
    'eval(',
    'base64_decode(',
]);
```

## CyberPanel & LiteSpeed Integration

Optimize the plugin for CyberPanel with OpenLiteSpeed or LiteSpeed Enterprise.

### LiteSpeed Cache Integration

Enable caching for download URLs:

```apache
# Add to .htaccess in uploads directory
<IfModule LiteSpeed>
    CacheLookup on
    RewriteRule .* - [E=Cache-Control:max-age=86400]
</IfModule>
```

### OpenLiteSpeed Optimization

For OpenLiteSpeed on CyberPanel:

1. Enable directory browsing protection:

```apache
Options -Indexes
```

2. Set proper MIME types:

```apache
<IfModule mod_mime.c>
    AddType image/jpeg .jpg .jpeg
    AddType image/png .png
    AddType image/gif .gif
    AddType application/pdf .pdf
</IfModule>
```

3. Restart OpenLiteSpeed:

```bash
systemctl restart lsws
```

### LiteSpeed Enterprise Optimization

For LiteSpeed Enterprise:

1. Apply changes to .htaccess

2. Restart LiteSpeed:

```bash
systemctl restart lsws
```

3. Verify changes:

```bash
curl -I https://yourls.example.com/uploads/test.jpg
```

### CyberPanel File Manager Integration

The plugin respects CyberPanel's file ownership rules:

- Main plugin directory: `yourls_user:nobody`
- Files and subdirectories: `yourls_user:yourls_user`

## API Integration

Extend the plugin with programmatic access.

### API Endpoints

The plugin provides REST API endpoints:

```
POST /api/upload           - Upload a file
GET  /api/files            - List uploaded files
GET  /api/files/{id}       - Get file details
DELETE /api/files/{id}     - Delete a file
PUT  /api/files/{id}       - Update file metadata
```

### Authentication

API requests require YOURLS authentication:

```bash
curl -X POST https://yourls.example.com/api/upload \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -F "file=@document.pdf"
```

### Response Format

All API responses use JSON:

```json
{
    "status": "success",
    "data": {
        "file_id": "abc123",
        "short_url": "https://yourls.example.com/abc123",
        "filename": "document.pdf"
    }
}
```

## Custom Themes Integration

Make the plugin work seamlessly with custom YOURLS themes.

### Styling Upload Forms

Override default styles in your theme:

```css
/* In your theme's style.css */
.upload-container {
    background: #your-color;
    border: 1px solid #your-border;
}

.upload-button {
    background: #your-button-color;
}
```

### Custom Templates

Override plugin templates by creating files in your theme:

```
your-theme/
└── upload-and-shorten/
    ├── upload-form.php
    ├── file-list.php
    └── settings-page.php
```

### Theme Hooks

Use available hooks in your theme:

```php
// Add custom content before upload form
yourls_add_action('upload_before_form', 'my_custom_content');

function my_custom_content() {
    echo '<div class="custom-notice">Upload your files here!</div>';
}
```

## Performance Optimization

Optimize plugin performance for large-scale deployments.

### Database Optimization

Add indexes for better query performance:

```sql
ALTER TABLE yourls_upload_files ADD INDEX idx_upload_date (upload_date);
ALTER TABLE yourls_upload_files ADD INDEX idx_expiration (expiration_date);
ALTER TABLE yourls_upload_files ADD INDEX idx_user (user_id);
```

### Caching

Enable object caching for frequently accessed data:

```php
define('UPLOAD_ENABLE_CACHE', true);
define('UPLOAD_CACHE_TTL', 3600);  // 1 hour
```

### File Storage Optimization

For large files or high traffic:

1. Use separate storage server
2. Implement CDN integration
3. Enable HTTP/2 for faster delivery
4. Use proper MIME types for browser caching

### Clean Up Old Data

Regularly clean up expired rate limit data:

```bash
# Add to cron
0 0 * * * /usr/bin/php /path/to/yourls/user/plugins/YOURLS-Upload-and-Shorten-Advanced/cleanup-rate-limits.php
```

## Security Hardening

Additional security measures for production environments.

### Disable Directory Listing

Add to uploads directory .htaccess:

```apache
Options -Indexes
```

### Implement IP Restrictions

Restrict uploads to specific IPs:

```php
define('UPLOAD_ALLOWED_IPS', [
    '192.168.1.0/24',
    '10.0.0.0/8',
]);
```

### Enable Audit Logging

Track all upload activities:

```php
define('UPLOAD_AUDIT_LOG', true);
define('UPLOAD_AUDIT_FILE', '/var/log/yourls/uploads.log');
```

### Virus Scanning

Integrate with ClamAV for virus scanning:

```php
define('UPLOAD_VIRUS_SCAN', true);
define('UPLOAD_CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl');
```

## Next Steps

- [Troubleshoot common issues](troubleshooting.md)
- [Configure multiple languages](internationalization.md)
- [Back to usage guide](usage.md)

## Back to Guides

[← Back to Documentation Index](README.md)


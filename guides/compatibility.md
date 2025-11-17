# Compatibility Guide

Complete compatibility information for the Upload and Shorten Advanced plugin.

## System Requirements

### YOURLS Version

- **Minimum:** YOURLS 1.7 or higher
- **Recommended:** YOURLS 1.9+
- **Tested:** YOURLS 1.10.2

### PHP Version

- **Minimum:** PHP 7.4
- **Maximum:** PHP 8.6
- **Recommended:** PHP 8.1 or higher

**Required PHP Extensions:**
- fileinfo
- mysqli
- json
- mbstring

### Database

- **MySQL:** 5.6 or higher
- **MariaDB:** 10.0 or higher

### Web Servers

The plugin is compatible with all major web servers:

- **Apache** 2.4+
- **Nginx** 1.18+
- **OpenLiteSpeed** 1.6+
- **LiteSpeed Enterprise** 5.4+

## Control Panel Compatibility

### CyberPanel

Fully compatible with CyberPanel installations:

- **OpenLiteSpeed** - Standard Apache-compatible directives
- **LiteSpeed Enterprise** - Enhanced with optional LiteSpeed-specific optimizations
- **File Management** - Seamless integration with CyberPanel's file management
- **Version Support** - CyberPanel 2.x and 3.x

### cPanel

Compatible with cPanel installations:

- Works with both Apache and LiteSpeed
- Compatible with cPanel EasyApache 4
- Supports cPanel file manager
- Version support: cPanel 11.x and higher

### Plesk

Compatible with Plesk installations:

- Works with Apache, Nginx, and LiteSpeed
- Compatible with Plesk Onyx and later
- Supports Plesk file manager
- Version support: Plesk 17.x and higher

## Operating System Compatibility

### Linux Distributions

Tested and optimized for:

- **AlmaLinux** 8.8, 9.6, 10
- **Rocky Linux** 8.x, 9.x
- **CentOS** 7.x, 8.x
- **Ubuntu** 20.04, 22.04, 24.04
- **Debian** 10, 11, 12

### Other Systems

While primarily tested on Linux, the plugin should work on:

- **FreeBSD** - With compatible PHP and web server
- **Windows Server** - Using IIS or XAMPP
- **macOS** - For development purposes

## Web Server Specific Notes

### Apache

- Requires mod_rewrite enabled
- .htaccess support required
- mod_mime recommended for proper file type handling

### Nginx

- Requires proper location blocks for file serving
- MIME type configuration needed
- URL rewriting rules required

### OpenLiteSpeed

- .htaccess changes apply automatically
- Apache-compatible directives work out of the box
- No restart needed after .htaccess changes
- Optional LiteSpeed Cache integration available

### LiteSpeed Enterprise

- .htaccess changes require server restart
- Full Apache compatibility
- Enhanced caching options available
- Support for LiteSpeed-specific optimizations

## CyberPanel & LiteSpeed Integration

The plugin includes .htaccess rules that are fully compatible with both OpenLiteSpeed and LiteSpeed Enterprise:

### Features

- **Apache Directives** - Standard Apache-compatible rules
- **LiteSpeed Optimizations** - Optional enhanced features
- **Automatic Protection** - Prevents PHP execution in uploads
- **Cache Control** - Proper headers for optimal caching

### Configuration

The plugin automatically configures:

1. File protection rules
2. MIME type handling
3. Cache control headers
4. Security restrictions

### Testing on CyberPanel

Verified on:
- AlmaLinux 8.8 + OpenLiteSpeed
- AlmaLinux 9.6 + OpenLiteSpeed
- AlmaLinux 10 + LiteSpeed Enterprise

## Theme Compatibility

The plugin works seamlessly with both default and custom YOURLS themes.

### Default YOURLS Theme

Full compatibility with the standard YOURLS interface:
- Admin panel integration
- Frontend forms (if enabled)
- Statistics display

### Sleeky2 Theme

Fully compatible with [Sleeky2](https://sleeky.flynntes.com/):
- Frontend theme integration
- Backend theme support
- Responsive design maintained

### Custom Themes

Works with most custom YOURLS themes through:
- Standard YOURLS hooks
- YOURLS filters
- Action callbacks
- Template system

### Theme Integration

The plugin uses standard YOURLS hooks:
- `admin_page_before_content`
- `html_head`
- `admin_menu`
- `upload_frontend_form` (custom hook)

### Responsive Design

The plugin's responsive design ensures compatibility across all devices and themes:
- Mobile-first approach
- Flexible layouts
- Adaptive components
- Touch-friendly interfaces

## Plugin Compatibility

### Compatible Plugins

Tested and confirmed working with:

- **YOURLS Download Plugin** - For easy installation
- **Advanced Reserved URLs** - URL reservation system
- **Allow Aliases** - Multiple short URLs for same target
- **Antispam** - Spam protection integration
- **API Action** - Extended API functionality
- **Change Error Messages** - Custom error messages
- **Enhanced Auth** - Advanced authentication
- **Force Lowercase** - Lowercase URL enforcement
- **QR Code** - QR code generation
- **Sleeky Backend** - Modern admin interface

### Potential Conflicts

Be aware of potential issues with:

- **Custom Upload Plugins** - May conflict with file upload handling
- **Heavy API Modifiers** - Could interfere with upload API
- **Custom Frontend Forms** - May duplicate upload forms

### Plugin Load Order

For best compatibility, ensure this plugin loads after:
- Authentication plugins
- Database modifiers
- Core API extensions

## Browser Compatibility

### Desktop Browsers

- **Chrome/Chromium** 90+
- **Firefox** 88+
- **Safari** 14+
- **Edge** 90+
- **Opera** 76+

### Mobile Browsers

- **Chrome Mobile** (Android)
- **Safari Mobile** (iOS)
- **Firefox Mobile**
- **Samsung Internet**

### Required Features

The plugin requires modern browser features:
- JavaScript ES6
- Drag and Drop API
- File API
- Fetch API
- CSS Flexbox/Grid

## Version History

### Current Version: 2.0.0

- **Release Date:** October 2025
- **YOURLS:** 1.7+
- **PHP:** 7.4 - 8.6
- **Status:** Stable

### Legacy Versions

For older YOURLS or PHP versions, see:
- [Version 1.0.0 Changelog](../changelogs/v1.0.0.md)

## Upgrade Paths

### From Version 1.x

Version 2.0.0 includes automatic migration:
1. Backup your installation
2. Deactivate version 1.x
3. Replace files with 2.0.0
4. Activate plugin
5. Migration runs automatically

### PHP Version Upgrades

When upgrading PHP:
1. Ensure target PHP version is 7.4 - 8.6
2. Test in staging environment
3. Update PHP
4. Clear any PHP caches
5. Test plugin functionality

### YOURLS Upgrades

When upgrading YOURLS:
1. Backup database and files
2. Upgrade YOURLS
3. Test plugin activation
4. Verify file uploads work
5. Check settings persistence

## Testing Recommendations

### Before Deployment

Test these features:
1. File upload (admin and frontend)
2. Short URL generation
3. File downloads
4. Settings persistence
5. Database operations

### After Updates

Verify after system updates:
1. PHP version changes
2. Web server updates
3. Control panel updates
4. YOURLS updates
5. Plugin updates

## Getting Help

If you have compatibility issues:

- **Discord:** [Join our Discord Server](https://discord.gg/nx9Kzrk)
- **GitHub Issues:** [Report compatibility issues](https://github.com/master3395/YOURLS-Upload-and-Shorten-Advanced/issues)
- **Email:** [info@newstargeted.com](mailto:info@newstargeted.com)

## Back to Guides

[← Back to Documentation Index](README.md)


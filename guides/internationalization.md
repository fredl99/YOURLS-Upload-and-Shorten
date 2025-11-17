# Internationalization

Language support and translation information for the Upload and Shorten Advanced plugin.

## Supported Languages

The plugin is available in multiple languages:

| Language | Code | Status | Contributor |
|----------|------|--------|-------------|
| English | `en_US` | ✅ Complete | Built-in |
| German | `de_DE` | ✅ Complete | Fredl |
| French | `fr_FR` | ✅ Complete | Alfonso Vivancos |
| Spanish | `es_ES` | ✅ Complete | Alfonso Vivancos |
| Chinese (Simplified) | `zh_CN` | ✅ Complete | Mo Lun |
| Norwegian (Bokmål) | `nb_NO` | ✅ Complete | Master3395 |

## Configuring Your Language

To use a specific language, add this to your YOURLS `user/config.php`:

```php
define('YOURLS_LANG', 'de_DE'); // Replace with your preferred language code
```

### Available Language Codes

- `en_US` - English (United States)
- `de_DE` - German (Germany)
- `fr_FR` - French (France)
- `es_ES` - Spanish (Spain)
- `zh_CN` - Chinese Simplified (China)
- `nb_NO` - Norwegian Bokmål (Norway)

## Language Files

Translation files are located in the `l10n/` directory:

```
l10n/
├── en_US.php      # English
├── de_DE.php      # German
├── fr_FR.php      # French
├── es_ES.php      # Spanish
├── zh_CN.php      # Chinese Simplified
└── nb_NO.php      # Norwegian Bokmål
```

## Creating a New Translation

Want to translate the plugin into your language? Here's how:

### 1. Copy the English Template

```bash
cd /path/to/yourls/user/plugins/YOURLS-Upload-and-Shorten-Advanced/l10n/
cp en_US.php your_LOCALE.php
```

Replace `your_LOCALE` with your language code (e.g., `it_IT` for Italian, `pt_BR` for Brazilian Portuguese).

### 2. Translate the Strings

Open the file and translate each string:

```php
<?php
return [
    'upload_file' => 'Upload File',           // Translate this
    'choose_file' => 'Choose File',           // And this
    'max_file_size' => 'Maximum file size',   // And so on...
];
```

### 3. Test Your Translation

1. Set your language in `user/config.php`:

```php
define('YOURLS_LANG', 'your_LOCALE');
```

2. Clear any caches
3. Visit your YOURLS admin panel
4. Verify all strings are translated correctly

### 4. Submit Your Translation

Share your translation with the community:

1. Fork the repository on GitHub
2. Add your translation file to the `l10n/` directory
3. Update the language table in this documentation
4. Create a pull request
5. Your translation will be reviewed and merged

## Translation Guidelines

When creating translations:

### Best Practices

1. **Keep it natural** - Translate meaning, not word-for-word
2. **Be consistent** - Use the same terms throughout
3. **Match context** - Consider where the text appears
4. **Check length** - Ensure translations fit in UI elements
5. **Use proper encoding** - Save files as UTF-8

### Common Terms

Maintain consistency with these standard translations:

| English | Purpose |
|---------|---------|
| Upload | Action of uploading files |
| Download | Action of downloading files |
| Short URL | The shortened link |
| File | A document or media file |
| Settings | Configuration options |
| Save | Commit changes |
| Cancel | Abort action |
| Delete | Remove item |
| Edit | Modify item |

### Technical Terms

Some terms should remain in English or use common international terms:

- URL
- PHP
- MySQL
- API
- HTTP/HTTPS
- Admin
- Plugin

### Placeholders

Some strings contain placeholders that should not be translated:

```php
'file_size_limit' => 'Maximum file size: %s MB'  // %s is replaced with a number
'uploaded_by' => 'Uploaded by %s on %s'          // %s are replaced with username and date
```

Keep placeholders (`%s`, `%d`, `{variable}`) in your translation in the appropriate position.

## Right-to-Left (RTL) Languages

For RTL languages (Arabic, Hebrew, etc.):

### 1. Create Translation File

```bash
cp en_US.php ar_AR.php  # For Arabic
```

### 2. Add RTL Flag

Include RTL indicator in your translation file:

```php
<?php
return [
    '_rtl' => true,  // Enables RTL mode
    'upload_file' => 'رفع ملف',
    // ... rest of translations
];
```

### 3. RTL Styling

The plugin will automatically apply RTL styles when the `_rtl` flag is detected.

## Updating Existing Translations

If you find errors or improvements for existing translations:

1. Edit the appropriate language file in `l10n/`
2. Test your changes
3. Submit a pull request with:
   - Description of changes
   - Reason for the update
   - Your name for attribution

## Translation Status

### Complete Translations (100%)

All UI strings are translated:
- English (en_US)
- German (de_DE)
- French (fr_FR)
- Spanish (es_ES)
- Chinese Simplified (zh_CN)
- Norwegian Bokmål (nb_NO)

### Partial Translations

Currently no partial translations. If you start a translation but don't finish, please still submit it so others can help complete it!

## Credits

### Translators

Special thanks to our translation contributors:

- **Fredl** - German translation
- **Alfonso Vivancos** - French and Spanish translations
- **Mo Lun** - Chinese Simplified translation
- **Master3395** - Norwegian Bokmål translation

Want to see your name here? Contribute a translation!

## Getting Help

Need help with translations?

- **Discord:** [Join our Discord Server](https://discord.gg/nx9Kzrk)
- **Email:** [info@newstargeted.com](mailto:info@newstargeted.com)
- **GitHub:** [Open an issue](https://github.com/master3395/YOURLS-Upload-and-Shorten-Advanced/issues)

## Next Steps

- [Return to configuration guide](configuration.md)
- [Learn about advanced features](advanced-configuration.md)
- [Troubleshoot issues](troubleshooting.md)

## Back to Guides

[← Back to Documentation Index](README.md)


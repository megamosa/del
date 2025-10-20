# WhatsApp Chat Pro - Language Files

This directory contains translation files for the WhatsApp Chat Pro WordPress plugin.

## Files Structure

- `whatsapp-chat-pro.pot` - Translation template file
- `ar.po` - Arabic translation (source)
- `ar.mo` - Arabic translation (compiled)
- `en_US.po` - English translation (source)
- `en_US.mo` - English translation (compiled)

## How to Use

### For Translators

1. **Download the POT file** - Use `whatsapp-chat-pro.pot` as your translation template
2. **Create your language PO file** - Copy the POT file and rename it to your language code (e.g., `fr_FR.po` for French)
3. **Translate the strings** - Edit the PO file and translate all the `msgstr` entries
4. **Compile to MO** - Use tools like Poedit, Loco Translate, or WP-CLI to compile PO to MO

### For Developers

1. **Load text domain** - The plugin automatically loads the text domain `whatsapp-chat-pro`
2. **Use translation functions** - All strings use `_e()` and `__()` functions
3. **Language files location** - WordPress will look for language files in `/languages/` directory

## Translation Tools

### Recommended Tools

1. **Poedit** - Desktop application for translation
2. **Loco Translate** - WordPress plugin for translation
3. **WP-CLI** - Command line tool for WordPress
4. **Online tools** - Various online PO/MO editors

### WP-CLI Commands

```bash
# Generate POT file
wp i18n make-pot . languages/whatsapp-chat-pro.pot --domain=whatsapp-chat-pro

# Compile PO to MO
wp i18n make-mo languages/

# Update PO files from POT
wp i18n update-po languages/
```

## Language Codes

- `ar` - Arabic
- `en_US` - English (United States)
- `fr_FR` - French (France)
- `de_DE` - German (Germany)
- `es_ES` - Spanish (Spain)
- `it_IT` - Italian (Italy)
- `pt_BR` - Portuguese (Brazil)
- `ru_RU` - Russian (Russia)
- `zh_CN` - Chinese (Simplified)
- `ja` - Japanese

## Contributing Translations

1. Fork the repository
2. Create your language PO file
3. Translate all strings
4. Compile to MO
5. Submit a pull request

## Support

For translation support, please contact:
- Email: support@getjustplug.com
- Website: https://getjustplug.com/support


# AddToSome Share Buttons

A performant, modular WordPress plugin that adds customizable share buttons to your posts and pages. Built with clean, maintainable code.

## Features

- **Multiple Sharing Services**
  - Pinterest sharing with image support
  - Facebook sharing (with optional App ID for enhanced dialog)
  - X (formerly Twitter) sharing (with optional handle)
  - Pocket save functionality
  - Email sharing with pre-filled content
  - Native browser sharing (Web Share API)

- **Customization Options**
  - Adjustable icon size (10-300 pixels)
  - Flexible placement (top, bottom, or both)
  - Per-service enable/disable toggles
  - Reorder enabled buttons via drag-and-drop in the admin
  - Platform-specific configuration

- **Performance Optimized**
  - Lazy loading of assets
  - Minimal JavaScript footprint
  - Clean, semantic HTML output
  - No external dependencies

## Installation

1. Upload the `add-to-some` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings > AddToSome

## Configuration

### Basic Settings

- **Icon Style**: Set the size of share button icons (10-300 pixels)
- **Share Buttons**: Enable/disable individual sharing services
- **Placement**: Choose where buttons appear on single posts

### Advanced Options

#### Facebook App ID

For enhanced Facebook sharing features:

1. Visit [developers.facebook.com](https://developers.facebook.com/)
2. Create a new app (Consumer type)
3. Copy the App ID from your app dashboard
4. Paste it in the plugin settings

Without an App ID, the plugin uses Facebook's basic sharer functionality.

#### X (Twitter) Handle

Add your X handle to include "via @yourhandle" in shared tweets.

## Architecture

The plugin follows a modern, object-oriented architecture with clear separation of concerns:

### File Structure

```text
add-to-some/
├── add-to-some.php           # Main plugin file and bootstrapper
├── includes/
│   ├── autoloader.php        # PSR-4 style autoloader
│   ├── compat.php            # Backward compatibility functions
│   └── classes/
│       ├── class-plugin.php      # Main plugin controller
│       ├── class-settings.php    # Settings management
│       ├── class-admin.php       # Admin interface
│       ├── class-frontend.php    # Frontend functionality
│       ├── class-share-buttons.php # Share link generation
│       └── class-renderer.php    # HTML rendering
├── js/
│   ├── admin.js              # Admin UI enhancements
│   └── frontend.js           # Native share functionality
└── README.md
```

### Class Overview

- **Plugin**: Main controller, singleton pattern, coordinates all components
- **Settings**: Manages options, validation, and sanitization
- **Admin**: Handles admin interface, settings page, and field rendering
- **Frontend**: Content filtering and asset enqueueing
- **ShareButtons**: Generates platform-specific share URLs
- **Renderer**: Builds HTML output with proper escaping

### Coding Standards

The plugin adheres to:

- WordPress VIP coding standards
- PSR-4 autoloading conventions
- WordPress security best practices
- Proper data validation and sanitization
- Contextual escaping for all output

## Hooks and Filters

### Actions

- `plugins_loaded` - Plugin initialization
- `admin_menu` - Settings page registration
- `admin_enqueue_scripts` - Admin assets
- `wp_enqueue_scripts` - Frontend assets
- `init` - Text domain loading

### Filters

- `the_content` - Button injection (priority 98)
- `plugin_action_links_*` - Settings link in plugins list
- `xwp_add_to_some_display_buttons` - Control button visibility

## Developer API

### Getting Plugin Options

```php
// Using the Settings class (recommended)
$settings = \XWP\AddToSome\Settings::get_instance();
$options = $settings->get_options();

// Using compatibility function
$options = xwp_add_to_some_get_options();
```

### Customizing Button Display

```php
// Prevent buttons on specific pages
add_filter( 'xwp_add_to_some_display_buttons', function( $display ) {
    if ( is_page( 'special-page' ) ) {
        return false;
    }
    return $display;
} );
```

### Rendering Buttons Programmatically

```php
// Get the plugin instance
$plugin = \XWP\AddToSome\Plugin::get_instance();
$settings = $plugin->get_settings();
$options = $settings->get_options();

// Create renderer and output buttons
$renderer = new \XWP\AddToSome\Renderer( $options, $post_id );
echo $renderer->render();
```

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- JavaScript enabled for native sharing

## Security

- All user inputs are sanitized
- All outputs are properly escaped
- Nonces used for form submissions
- Capability checks for admin functions
- No direct file access allowed

## Performance

- Minimal database queries (single option)
- Assets loaded only when needed
- No render-blocking resources
- Efficient DOM manipulation

## License

GPLv2 or later

## Support

For issues, feature requests, or contributions, please use the plugin's support forum or repository.

## Changelog

### 1.0.1

- Added drag-and-drop reordering of enabled share buttons in the admin
- Renderer output now respects the saved order
- Bumped plugin and asset versions for cache-busting

### 1.0.0

- Complete refactor with modular architecture
- Improved code organization and readability
- Enhanced security and performance

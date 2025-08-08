<?php
/**
 * Autoloader for AddToSome plugin classes.
 *
 * @package AddToSome
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register autoloader for plugin classes.
 *
 * @param string $class_name The class name to load.
 */
spl_autoload_register( function ( $class_name ) {
	// Check if it's our namespace.
	$namespace = 'XWP\\AddToSome\\';
	
	if ( strpos( $class_name, $namespace ) !== 0 ) {
		return;
	}

	// Remove namespace from class name.
	$class_name = str_replace( $namespace, '', $class_name );
	
    // Convert CamelCase to kebab-case and build filename.
    // Example: ShareButtons -> class-share-buttons.php
    $kebab = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $class_name ) );
    $kebab = str_replace( '_', '-', $kebab );
    $filename = 'class-' . $kebab . '.php';

    // Build file path.
    $file = plugin_dir_path( __FILE__ ) . 'classes/' . $filename;
	
	// Load file if it exists.
	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

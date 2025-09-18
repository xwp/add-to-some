<?php
/**
 * Plugin Name: AddToSome Share Buttons
 * Description: Performant share buttons for your pages including Pinterest, Facebook, X, Pocket, Email and native sharing.
 * Version: 1.0.3
 * Author: XWP
 * Author URI: https://xwp.co/
 * License: GPLv2+
 * Text Domain: add-to-some
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package AddToSome
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'XWP_ADD_TO_SOME_VERSION', '1.0.3' );
define( 'XWP_ADD_TO_SOME_FILE', __FILE__ );
define( 'XWP_ADD_TO_SOME_PATH', plugin_dir_path( __FILE__ ) );
define( 'XWP_ADD_TO_SOME_URL', plugin_dir_url( __FILE__ ) );

// Load autoloader.
require_once XWP_ADD_TO_SOME_PATH . 'includes/autoloader.php';

// Load compatibility functions.
require_once XWP_ADD_TO_SOME_PATH . 'includes/compat.php';

// Initialize the plugin.
add_action( 'plugins_loaded', function() {
	\XWP\AddToSome\Plugin::get_instance( XWP_ADD_TO_SOME_FILE );
}, 5 );

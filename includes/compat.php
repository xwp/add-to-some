<?php
/**
 * Compatibility functions for AddToSome plugin.
 *
 * Provides backward compatibility for direct function calls.
 *
 * @package AddToSome
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin options (backward compatibility).
 *
 * @deprecated 1.0.0 Use \XWP\AddToSome\Settings::get_instance()->get_options() instead.
 * @return array Plugin options.
 */
function xwp_add_to_some_get_options() {
	return \XWP\AddToSome\Settings::get_instance()->get_options();
}

/**
 * Get default options (backward compatibility).
 *
 * @deprecated 1.0.0 Use \XWP\AddToSome\Settings::get_instance()->get_defaults() instead.
 * @return array Default options.
 */
function xwp_add_to_some_default_options() {
	return \XWP\AddToSome\Settings::get_instance()->get_defaults();
}

/**
 * Sanitize options (backward compatibility).
 *
 * @deprecated 1.0.0 Use \XWP\AddToSome\Settings::get_instance()->sanitize_options() instead.
 * @param array $input Input to sanitize.
 * @return array Sanitized options.
 */
function xwp_add_to_some_sanitize_options( $input ) {
	return \XWP\AddToSome\Settings::get_instance()->sanitize_options( $input );
}

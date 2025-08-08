<?php
/**
 * Settings management class for AddToSome plugin.
 *
 * @package AddToSome
 * @since 1.0.0
 */

namespace XWP\AddToSome;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 *
 * Handles all plugin settings and options management.
 */
class Settings {

	/**
	 * Option key for storing plugin settings.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'xwp_add_to_some_options';

	/**
	 * Instance of this class.
	 *
	 * @var Settings|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get default options.
	 *
	 * @return array Default plugin options.
	 */
	public function get_defaults() {
		return array(
			'icon_size'       => 32,
			'placement'       => 'bottom', // top|bottom|both.
			'display_top'     => false,    // Derived flag for UI convenience.
			'display_bottom'  => true,     // Derived flag for UI convenience.
			'buttons'         => array(
				'pinterest' => false,
				'facebook'  => true,
				'x'         => true,
				'pocket'    => false,
				'email'     => true,
				'native'    => true,
			),
			'facebook_app_id' => '',
			'x_handle'        => '',
		);
	}

	/**
	 * Get merged options with defaults.
	 *
	 * @return array Merged options.
	 */
	public function get_options() {
		$defaults = $this->get_defaults();
		$saved    = get_option( self::OPTION_KEY, array() );
		
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		
		$options = wp_parse_args( $saved, $defaults );

		// Normalize and validate options.
		$options = $this->normalize_options( $options, $defaults );

		return $options;
	}

	/**
	 * Normalize and validate options.
	 *
	 * @param array $options Options to normalize.
	 * @param array $defaults Default options.
	 * @return array Normalized options.
	 */
	private function normalize_options( $options, $defaults ) {
		// Normalize booleans.
		$options['display_top']    = (bool) $options['display_top'];
		$options['display_bottom'] = (bool) $options['display_bottom'];
		
		// Validate icon size.
		$options['icon_size'] = $this->validate_icon_size( $options['icon_size'] );
		
		// Validate placement.
		$options['placement'] = $this->validate_placement( $options['placement'] );

		// Normalize buttons.
		$options['buttons'] = $this->normalize_buttons( $options['buttons'], $defaults['buttons'] );

		// Normalize Facebook App ID & X handle.
		$options['facebook_app_id'] = $this->sanitize_facebook_app_id( $options['facebook_app_id'] ?? '' );
		$options['x_handle']        = $this->sanitize_x_handle( $options['x_handle'] ?? '' );

		return $options;
	}

	/**
	 * Validate icon size.
	 *
	 * @param mixed $size Icon size value.
	 * @return int Valid icon size.
	 */
	private function validate_icon_size( $size ) {
		$size = absint( $size );
		return max( 10, min( 300, $size ) );
	}

	/**
	 * Validate placement option.
	 *
	 * @param string $placement Placement value.
	 * @return string Valid placement.
	 */
	private function validate_placement( $placement ) {
		$valid_placements = array( 'top', 'bottom', 'both' );
		return in_array( $placement, $valid_placements, true ) ? $placement : 'bottom';
	}

	/**
	 * Normalize button options.
	 *
	 * @param mixed $buttons Button options.
	 * @param array $defaults Default button options.
	 * @return array Normalized button options.
	 */
	private function normalize_buttons( $buttons, $defaults ) {
		if ( empty( $buttons ) || ! is_array( $buttons ) ) {
			return $defaults;
		}

		$normalized = array();
		$button_keys = array( 'pinterest', 'facebook', 'x', 'pocket', 'email', 'native' );
		
		foreach ( $button_keys as $key ) {
			$normalized[ $key ] = ! empty( $buttons[ $key ] );
		}
		
		return $normalized;
	}

	/**
	 * Sanitize Facebook App ID.
	 *
	 * @param mixed $app_id Facebook App ID.
	 * @return string Sanitized App ID.
	 */
	private function sanitize_facebook_app_id( $app_id ) {
		return preg_replace( '/[^0-9]/', '', (string) $app_id );
	}

	/**
	 * Sanitize X (Twitter) handle.
	 *
	 * @param mixed $handle X handle.
	 * @return string Sanitized handle.
	 */
	private function sanitize_x_handle( $handle ) {
		$handle = (string) $handle;
		$handle = ltrim( $handle, '@' );
		$handle = preg_replace( '/[^a-z0-9_]/i', '', $handle );
		return strtolower( $handle );
	}

	/**
	 * Sanitize options from request.
	 *
	 * @param array $input Input data to sanitize.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $input ) {
		if ( ! is_array( $input ) ) {
			return $this->get_defaults();
		}

		$defaults = $this->get_defaults();

		// Sanitize icon size.
		$icon_size = isset( $input['icon_size'] ) ? absint( $input['icon_size'] ) : $defaults['icon_size'];
		$icon_size = $this->validate_icon_size( $icon_size );

		// Sanitize placement.
		$placement = isset( $input['placement'] ) ? sanitize_text_field( (string) $input['placement'] ) : $defaults['placement'];
		$placement = $this->validate_placement( $placement );

		// Map checkboxes to booleans.
		$buttons = array();
		foreach ( array( 'pinterest', 'facebook', 'x', 'pocket', 'email', 'native' ) as $key ) {
			$buttons[ $key ] = ! empty( $input['buttons'][ $key ] );
		}

		// Sanitize Facebook App ID.
		$facebook_app_id = $this->sanitize_facebook_app_id( $input['facebook_app_id'] ?? '' );

		// Sanitize X handle.
		$x_handle = $this->sanitize_x_handle( $input['x_handle'] ?? '' );

		// Derived convenience flags.
		$display_top    = in_array( $placement, array( 'top', 'both' ), true );
		$display_bottom = in_array( $placement, array( 'bottom', 'both' ), true );

		return array(
			'icon_size'       => $icon_size,
			'placement'       => $placement,
			'display_top'     => $display_top,
			'display_bottom'  => $display_bottom,
			'buttons'         => $buttons,
			'facebook_app_id' => $facebook_app_id,
			'x_handle'        => $x_handle,
		);
	}

	/**
	 * Get available button types.
	 *
	 * @return array Button types with labels.
	 */
	public function get_button_types() {
		return array(
			'pinterest' => __( 'Pinterest', 'add-to-some' ),
			'facebook'  => __( 'Facebook', 'add-to-some' ),
			'x'         => __( 'X', 'add-to-some' ),
			'pocket'    => __( 'Pocket', 'add-to-some' ),
			'email'     => __( 'Email', 'add-to-some' ),
			'native'    => __( 'Native sharing', 'add-to-some' ),
		);
	}

	/**
	 * Get placement options.
	 *
	 * @return array Placement options with labels.
	 */
	public function get_placement_options() {
		return array(
			'bottom' => __( 'bottom', 'add-to-some' ),
			'top'    => __( 'top', 'add-to-some' ),
			'both'   => __( 'top & bottom', 'add-to-some' ),
		);
	}
}

<?php
/**
 * Renderer class for AddToSome plugin.
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
 * Renderer class.
 *
 * Handles rendering of share buttons markup.
 */
class Renderer {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Share buttons instance.
	 *
	 * @var ShareButtons
	 */
	private $share_buttons;

	/**
	 * Constructor.
	 *
	 * @param array    $options Plugin options.
	 * @param int|null $post_id Post ID (optional).
	 */
	public function __construct( $options, $post_id = null ) {
		$this->options = $options;
		$this->share_buttons = new ShareButtons( $options, $post_id );
	}

	/**
	 * Render share buttons.
	 *
	 * @return string Rendered HTML.
	 */
	public function render() {
		$enabled_buttons = $this->get_enabled_buttons();
		
		if ( empty( $enabled_buttons ) ) {
			return '';
		}

		$links = $this->share_buttons->generate_links();
		
		return $this->build_html( $enabled_buttons, $links );
	}

	/**
	 * Get enabled buttons.
	 *
	 * @return array Enabled button keys.
	 */
	private function get_enabled_buttons() {
		$enabled = array();
		
		if ( empty( $this->options['buttons'] ) || ! is_array( $this->options['buttons'] ) ) {
			return $enabled;
		}
		
		$order = ( is_array( $this->options['order'] ?? null ) ) 
			? $this->options['order'] 
			: array_keys( $this->options['buttons'] );

		foreach ( $order as $key ) {
			if ( ! empty( $this->options['buttons'][ $key ] ?? false ) ) {
				$enabled[] = $key;
			}
		}
		
		return $enabled;
	}

	/**
	 * Build HTML for share buttons.
	 *
	 * @param array $enabled_buttons Enabled button keys.
	 * @param array $links Generated links.
	 * @return string HTML markup.
	 */
	private function build_html( $enabled_buttons, $links ) {
		// Using null coalescing operator for cleaner validation
		$icon_size = absint( $this->options['icon_size'] ?? 32 );
		
		$html = '<div class="add-to-some" style="--ats-icon-size: ' . esc_attr( $icon_size ) . 'px; --ats-icon-padding: 0.25rem; height: calc( ( var(--ats-icon-padding) * 2 ) + var(--ats-icon-size) ); overflow: hidden; visibility: hidden;">';
		
		foreach ( (array) $enabled_buttons as $key ) {
			if ( isset( $links[ $key ] ) ) {
				$html .= '<div class="add-to-some__icon add-to-some__icon--' . esc_attr( $key ) . '">' . $links[ $key ] . '</div>';
			}
		}
		
		$html .= '</div>';

		return $html;
	}
}

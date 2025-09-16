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
		$order = isset( $this->options['order'] ) && is_array( $this->options['order'] ) ? $this->options['order'] : array_keys( $this->options['buttons'] );

		foreach ( $order as $key ) {
			if ( ! empty( $this->options['buttons'][ $key ] ) ) {
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
		$html = '<div class="add-to-some" style="--ats-icon-size: ' . $this->options['icon_size'] . 'px; --ats-icon-padding: 0.25rem; height: calc( ( var(--ats-icon-padding) * 2 ) + var(--ats-icon-size) ); overflow: hidden; visibility: hidden;">';
		
		foreach ( $enabled_buttons as $key ) {
			$html .= '<div class="add-to-some__icon add-to-some__icon--' . $key . '">' . $links[ $key ] . '</div>';
		}
		
		$html .= '</div>';

		return $html;
	}
}

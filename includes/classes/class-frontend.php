<?php
/**
 * Frontend functionality for AddToSome plugin.
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
 * Frontend class.
 *
 * Handles frontend display and content filtering.
 */
class Frontend {

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Initialize frontend functionality.
	 */
	public function init() {
		add_filter( 'the_content', array( $this, 'filter_content' ), 98 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
		add_filter( 'style_loader_tag', array( $this, 'filter_style_loader_tag' ), 10, 4 );
	}

	/**
	 * Filter content to add share buttons.
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function filter_content( $content ) {
		if ( ! $this->should_display_buttons() ) {
			return $content;
		}

		$options = $this->settings->get_options();
		$buttons_html = $this->render_buttons( $options );

		return $this->insert_buttons( $content, $buttons_html, $options['placement'] );
	}

	/**
	 * Check if buttons should be displayed.
	 *
	 * @return bool Whether to display buttons.
	 */
	private function should_display_buttons() {
		// Don't show in admin, feeds, or non-singular pages.
		if ( is_admin() || is_feed() || ! is_singular( 'post') ) {
			return false;
		}

		// Allow filtering whether to show buttons.
		return apply_filters( 'xwp_add_to_some_display_buttons', true );
	}

	/**
	 * Insert buttons into content based on placement.
	 *
	 * @param string $content Post content.
	 * @param string $buttons_html Buttons HTML.
	 * @param string $placement Placement option.
	 * @return string Modified content.
	 */
	private function insert_buttons( $content, $buttons_html, $placement ) {
		switch ( $placement ) {
			case 'both':
				return $buttons_html . $content . $buttons_html;
			
			case 'top':
				return $buttons_html . $content;
			
			case 'bottom':
			default:
				return $content . $buttons_html;
		}
	}

	/**
	 * Render share buttons.
	 *
	 * @param array $options Plugin options.
	 * @return string Buttons HTML.
	 */
	public function render_buttons( $options ) {
		$renderer = new Renderer( $options );
		return $renderer->render();
	}

	/**
	 * Maybe enqueue frontend assets.
	 */
	public function maybe_enqueue_assets() {
		if ( is_admin() || ! is_singular( 'post') ) {
			return;
		}

		$options = $this->settings->get_options();
		
		// Enqueue non-render-blocking stylesheet using media="print" + onload trick.
		wp_enqueue_style(
			'xwp-add-to-some-frontend-style',
			plugins_url( 'css/frontend.css', dirname( __DIR__ ) ),
			array(),
			XWP_ADD_TO_SOME_VERSION,
			'print'
		);

		// Only enqueue if native sharing is enabled.
		if ( ! empty( $options['buttons']['native'] ) ) {
			$this->enqueue_native_share_script();
		}
	}

	/**
	 * Enqueue native share script.
	 */
	private function enqueue_native_share_script() {
		wp_enqueue_script(
			'xwp-add-to-some-frontend',
			plugins_url( 'js/frontend.js', dirname( __DIR__ ) ),
			array(),
			XWP_ADD_TO_SOME_VERSION,
			true
		);
	}

	/**
	 * Filter the style tag for our handle to add onload and switch media to all after load.
	 *
	 * @param string $html   The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $href   The stylesheet's source URL.
	 * @param string $media  The stylesheet's media attribute.
	 * @return string Possibly modified HTML.
	 */
	public function filter_style_loader_tag( $html, $handle, $href, $media ) {
		if ( 'xwp-add-to-some-frontend-style' !== $handle ) {
			return $html;
		}

		$html = str_replace( '/>', ' onload="this.media=\'all\'" />', $html );

		return $html;
	}
}

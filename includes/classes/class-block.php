<?php
/**
 * Gutenberg block functionality for AddToSome plugin.
 *
 * @package AddToSome
 * @since 1.1.0
 */

namespace XWP\AddToSome;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block class.
 *
 * Handles Gutenberg block registration and rendering.
 */
class Block {

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
	 * Initialize block functionality.
	 */
	public function init() {
		add_action( 'init', array( self::class, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the Gutenberg block.
	 */
	public static function register_block() {
		register_block_type(
			'add-to-some/share-buttons',
			array(
				'render_callback' => array( self::class, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'xwp-add-to-some-block-editor',
			XWP_ADD_TO_SOME_URL . 'js/block-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
			XWP_ADD_TO_SOME_VERSION,
			true
		);

		// Enqueue frontend CSS for realistic preview.
		wp_enqueue_style(
			'xwp-add-to-some-frontend-style-editor',
			XWP_ADD_TO_SOME_URL . 'css/frontend.css',
			array(),
			XWP_ADD_TO_SOME_VERSION
		);

		wp_enqueue_style(
			'xwp-add-to-some-block-editor-style',
			XWP_ADD_TO_SOME_URL . 'css/block-editor.css',
			array( 'xwp-add-to-some-frontend-style-editor' ),
			XWP_ADD_TO_SOME_VERSION
		);

		// Pass preview HTML and settings URL to the editor.
		$preview_html = self::render_preview();

		wp_localize_script(
			'xwp-add-to-some-block-editor',
			'addToSomeBlock',
			array(
				'settingsUrl' => admin_url( 'options-general.php?page=add-to-some' ),
				'previewHtml' => $preview_html,
				'previewImageUrl' => XWP_ADD_TO_SOME_URL . 'images/block-preview.jpg',
			)
		);
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @return string Block HTML.
	 */
	public static function render_block() {
		$settings = Settings::get_instance();
		$options = $settings->get_options();
		$renderer = new Renderer( $options );

		return $renderer->render();
	}

	/**
	 * Render preview HTML for the block editor.
	 *
	 * @return string Preview HTML.
	 */
	public static function render_preview() {
		$settings = Settings::get_instance();
		$options = $settings->get_options();
		$renderer = new Renderer( $options, 0 );

		return $renderer->render_preview();
	}
}

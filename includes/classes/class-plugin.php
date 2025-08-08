<?php
/**
 * Main plugin class for AddToSome.
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
 * Plugin class.
 *
 * Main plugin controller.
 */
class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Admin instance.
	 *
	 * @var Admin
	 */
	private $admin;

	/**
	 * Frontend instance.
	 *
	 * @var Frontend
	 */
	private $frontend;

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Get singleton instance.
	 *
	 * @param string $plugin_file Main plugin file path.
	 * @return Plugin
	 */
	public static function get_instance( $plugin_file = '' ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $plugin_file );
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Main plugin file path.
	 */
	private function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->init();
	}

	/**
	 * Initialize plugin.
	 */
	private function init() {
		// Load text domain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Initialize components.
		$this->settings = Settings::get_instance();
		
		// Initialize admin functionality.
		if ( is_admin() ) {
			$this->admin = new Admin( $this->settings );
			$this->admin->init();
		}

		// Initialize frontend functionality.
		$this->frontend = new Frontend( $this->settings );
		$this->frontend->init();

		// Add plugin action links.
		add_filter(
			'plugin_action_links_' . plugin_basename( $this->plugin_file ),
			array( $this, 'add_action_links' )
		);
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'add-to-some',
			false,
			dirname( plugin_basename( $this->plugin_file ) ) . '/languages'
		);
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_action_links( $links ) {
		$settings_url = admin_url( 'options-general.php?page=add-to-some' );
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Settings', 'add-to-some' )
		);
		
		array_unshift( $links, $settings_link );
		
		return $links;
	}

	/**
	 * Get settings instance.
	 *
	 * @return Settings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Get admin instance.
	 *
	 * @return Admin|null
	 */
	public function get_admin() {
		return $this->admin;
	}

	/**
	 * Get frontend instance.
	 *
	 * @return Frontend
	 */
	public function get_frontend() {
		return $this->frontend;
	}
}

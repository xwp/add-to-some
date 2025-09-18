<?php
/**
 * Admin functionality for AddToSome plugin.
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
 * Admin class.
 *
 * Handles admin interface and settings page.
 */
class Admin {

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
	 * Initialize admin functionality.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register settings and admin page.
	 */
	public function register_settings() {
		register_setting(
			'xwp_add_to_some',
			Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize_options' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'xwp_add_to_some_main',
			'',
			'__return_false',
			'xwp_add_to_some'
		);

		// Icon size field.
		add_settings_field(
			'icon_size',
			__( 'Icon Style', 'add-to-some' ),
			array( $this, 'render_icon_size_field' ),
			'xwp_add_to_some',
			'xwp_add_to_some_main'
		);

		// Share buttons field.
		add_settings_field(
			'buttons',
			__( 'Share Buttons', 'add-to-some' ),
			array( $this, 'render_buttons_field' ),
			'xwp_add_to_some',
			'xwp_add_to_some_main'
		);

		// Placement field.
		add_settings_field(
			'placement',
			__( 'Placement on single posts', 'add-to-some' ),
			array( $this, 'render_placement_field' ),
			'xwp_add_to_some',
			'xwp_add_to_some_main'
		);

		// Add options page.
		add_options_page(
			__( 'AddToSome Settings', 'add-to-some' ),
			__( 'AddToSome', 'add-to-some' ),
			'manage_options',
			'add-to-some',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render icon size field.
	 */
	public function render_icon_size_field() {
		$options = $this->settings->get_options();
		$field_name = esc_attr( Settings::OPTION_KEY . '[icon_size]' );
		$value = esc_attr( (string) ( $options['icon_size'] ?? 32 ) );
		
		printf(
			'<label><input name="%s" type="number" min="10" max="300" step="2" class="small-text" value="%s"> %s</label>',
			$field_name,
			$value,
			esc_html__( 'pixels', 'add-to-some' )
		);
	}

	/**
	 * Render buttons field.
	 */
	public function render_buttons_field() {
		$options = $this->settings->get_options();
		$button_types = $this->settings->get_button_types();

		// Determine render order using saved options with null coalescing
		$order = ( is_array( $options['order'] ?? null ) ) 
			? $options['order'] 
			: array_keys( $button_types );

		// Hidden input to store order as comma-separated list.
		$hidden_name  = esc_attr( Settings::OPTION_KEY . '[order]' );
		$hidden_value = esc_attr( implode( ',', $order ) );

		echo '<input type="hidden" id="xwp-ats-order" name="' . $hidden_name . '" value="' . $hidden_value . '" />';
		echo '<fieldset id="xwp-ats-buttons" style="display:grid; gap:10px; max-width:560px">';
		
		foreach ( $order as $key ) {
			if ( isset( $button_types[ $key ] ) ) {
				$this->render_button_option( $key, $button_types[ $key ], $options );
			}
		}
		
		echo '</fieldset>';
	}

	/**
	 * Render individual button option.
	 *
	 * @param string $key Button key.
	 * @param string $label Button label.
	 * @param array  $options Current options.
	 */
	private function render_button_option( $key, $label, $options ) {
		$checked  = ! empty( $options['buttons'][ $key ] ?? false ) ? 'checked' : '';
		$input_id = 'xwp-ats-btn-' . $key;
		$field_name = esc_attr( Settings::OPTION_KEY . '[buttons][' . $key . ']' );

		echo '<div class="xwp-ats-row xwp-ats-row-' . esc_attr( $key ) . '" draggable="true" data-key="' . esc_attr( $key ) . '">';
		echo '<span class="xwp-ats-handle" aria-label="' . esc_attr__( 'Drag to reorder', 'add-to-some' ) . '" style="cursor:move; user-select:none; margin-right:8px;">↕︎</span> ';
		
		printf(
			'<label><input id="%s" type="checkbox" name="%s" value="1" %s> %s</label>',
			esc_attr( $input_id ),
			$field_name,
			$checked,
			esc_html( $label )
		);

		// Render sub-options for specific buttons.
		if ( 'facebook' === $key ) {
			$this->render_facebook_suboptions( $options, $checked );
		} elseif ( 'x' === $key ) {
			$this->render_x_suboptions( $options, $checked );
		}

		echo '</div>';
	}

	/**
	 * Render Facebook sub-options.
	 *
	 * @param array  $options Current options.
	 * @param string $checked Whether Facebook is checked.
	 */
	private function render_facebook_suboptions( $options, $checked ) {
		$app_id = $options['facebook_app_id'] ?? '';
		$style  = $checked ? '' : ' style="display:none"';
		$field_name = esc_attr( Settings::OPTION_KEY . '[facebook_app_id]' );

		echo '<div class="xwp-ats-suboptions xwp-ats-facebook-app-id"' . $style . '>';
		
		printf(
			'<label>%s: <input type="text" class="regular-text" name="%s" value="%s" placeholder="123456789012345" /></label>',
			esc_html__( 'Facebook App ID', 'add-to-some' ),
			$field_name,
			esc_attr( $app_id )
		);

		$this->render_facebook_help_text();
		
		echo '</div>';
	}

	/**
	 * Render Facebook help text.
	 */
	private function render_facebook_help_text() {
		$facebook_doc_url = 'https://developers.facebook.com/docs/development/create-an-app/';
		$facebook_developers_url = 'https://developers.facebook.com/';
		
		// translators: 1: <a> tag open to developers.facebook.com, 2: </a>, 3: <a> tag open to documentation, 4: </a>.
		$description = sprintf(
			__( 'Without an APP ID, it falls back to basic share. Go to %1$sdevelopers.facebook.com%2$s - My Apps - Create App (Consumer), give it a name and copy the App ID. (%3$sFacebook documentation%4$s).', 'add-to-some' ),
			'<a href="' . esc_url( $facebook_developers_url ) . '" target="_blank" rel="noopener">',
			'</a>',
			'<a href="' . esc_url( $facebook_doc_url ) . '" target="_blank" rel="noopener">',
			'</a>'
		);
		
		echo '<p class="description">' . $description . '</p>';
	}

	/**
	 * Render X (Twitter) sub-options.
	 *
	 * @param array  $options Current options.
	 * @param string $checked Whether X is checked.
	 */
	private function render_x_suboptions( $options, $checked ) {
		$handle = $options['x_handle'] ?? '';
		$style  = $checked ? '' : ' style="display:none"';
		$field_name = esc_attr( Settings::OPTION_KEY . '[x_handle]' );

		echo '<div class="xwp-ats-suboptions xwp-ats-x-handle"' . $style . '>';
		
		printf(
			'<label style="display:block; margin:6px 0 0 26px">%s: @ <input type="text" class="regular-text" name="%s" value="%s" placeholder="yoursite" /></label>',
			esc_html__( 'X handle to share via', 'add-to-some' ),
			$field_name,
			esc_attr( $handle )
		);
		
		echo '</div>';
	}

	/**
	 * Render placement field.
	 */
	public function render_placement_field() {
		$options = $this->settings->get_options();
		$placement = $options['placement'] ?? 'bottom';
		$placement_options = $this->settings->get_placement_options();
		$field_name = esc_attr( Settings::OPTION_KEY . '[placement]' );

		echo '<select name="' . $field_name . '">';
		
		foreach ( $placement_options as $value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $placement, $value, false ),
				esc_html( $label )
			);
		}
		
		echo '</select>';
	}

	/**
	 * Enqueue admin JS for settings page.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_add-to-some' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'xwp-add-to-some-admin',
			plugins_url( 'js/admin.js', dirname( __DIR__ ) ),
			array(),
			XWP_ADD_TO_SOME_VERSION,
			true
		);
	}

	/**
	 * Render admin settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'AddToSome Settings', 'add-to-some' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'xwp_add_to_some' );
				do_settings_sections( 'xwp_add_to_some' );
				submit_button( __( 'Save Changes', 'add-to-some' ) );
				?>
			</form>
		</div>
		<?php
	}
}

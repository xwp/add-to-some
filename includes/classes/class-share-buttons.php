<?php
/**
 * Share buttons generation class.
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
 * ShareButtons class.
 *
 * Handles generation of share button links and markup.
 */
class ShareButtons {

	/**
	 * Post data for sharing.
	 *
	 * @var array
	 */
	private $post_data;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param array    $options Plugin options.
	 * @param int|null $post_id Post ID (optional).
	 */
	public function __construct( $options, $post_id = null ) {
		$this->options = $options;
		$this->post_data = $this->get_post_data( $post_id );
	}

	/**
	 * Get post data for sharing.
	 *
	 * @param int|null $post_id Post ID.
	 * @return array Post data.
	 */
	private function get_post_data( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$permalink = get_permalink( $post_id );
		$title     = get_the_title( $post_id );
		$excerpt   = $this->get_safe_excerpt( $post_id );
		$image     = get_the_post_thumbnail_url( $post_id, 'full' );

		return array(
			'id'        => $post_id,
			'permalink' => $permalink,
			'title'     => $title,
			'excerpt'   => $excerpt,
			'image'     => $image,
		);
	}

	/**
	 * Get safe excerpt without causing recursion.
	 *
	 * @param int $post_id Post ID.
	 * @return string Safe excerpt.
	 */
	private function get_safe_excerpt( $post_id ) {
		// Avoid calling get_the_excerpt() inside the_content filter to prevent recursion.
		$raw_excerpt = get_post_field( 'post_excerpt', $post_id );
		
		if ( '' === $raw_excerpt ) {
			$raw_content = get_post_field( 'post_content', $post_id );
			$stripped    = wp_strip_all_tags( $raw_content );
			$raw_excerpt = wp_trim_words( $stripped, 40, '' );
		}
		
		return $raw_excerpt;
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
		
		foreach ( $this->options['buttons'] as $key => $is_enabled ) {
			if ( $is_enabled ) {
				$enabled[] = $key;
			}
		}
		
		return $enabled;
	}

	/**
	 * Generate all share links.
	 *
	 * @return array Share links.
	 */
	public function generate_links() {
		$links = array();
		$enabled = $this->get_enabled_buttons();

		foreach ( $enabled as $button_key ) {
			$method = 'generate_' . $button_key . '_link';
			
			if ( method_exists( $this, $method ) ) {
				$link = $this->$method();
				if ( $link ) {
					$links[ $button_key ] = $link;
				}
			}
		}

		return $links;
	}

	/**
	 * Generate LinkedIn share link.
	 *
	 * @return string LinkedIn share link HTML.
	 */
	private function generate_linkedin_link() {
		$href = add_query_arg(
			array(
				'url' => $this->post_data['permalink'] ?? '',
			),
			'https://www.linkedin.com/sharing/share-offsite/'
		);

		return sprintf(
			'<a href="%s" rel="nofollow noopener external" target="_blank" title="%s"></a>',
			esc_url( $href ),
			esc_attr__( 'Share on LinkedIn', 'add-to-some' )
		);
	}

	/**
	 * Generate Reddit share link.
	 *
	 * @return string Reddit share link HTML.
	 */
	private function generate_reddit_link() {
		$href = add_query_arg(
			array(
				'url'   => $this->post_data['permalink'] ?? '',
				'title' => $this->post_data['title'] ?? '',
			),
			'https://www.reddit.com/submit'
		);

		return sprintf(
			'<a href="%s" rel="nofollow noopener external" target="_blank" title="%s"></a>',
			esc_url( $href ),
			esc_attr__( 'Share on Reddit', 'add-to-some' )
		);
	}

	/**
	 * Generate Facebook share link.
	 *
	 * @return string Facebook share link HTML.
	 */
	private function generate_facebook_link() {
		$href = $this->build_facebook_url();
		
		return sprintf(
			'<a href="%s" rel="nofollow noopener external" target="_blank" title="%s"></a>',
			esc_url( $href ),
			esc_attr__( 'Share on Facebook', 'add-to-some' )
		);
	}

	/**
	 * Build Facebook share URL.
	 *
	 * @return string Facebook share URL.
	 */
	private function build_facebook_url() {
		if ( ! empty( $this->options['facebook_app_id'] ?? '' ) ) {
			return add_query_arg(
				array(
					'app_id'  => $this->options['facebook_app_id'],
					'href'    => $this->post_data['permalink'] ?? '',
					'display' => 'popup',
				),
				'https://www.facebook.com/dialog/share'
			);
		}

		// Fallback to basic sharer.
		$args = array(
			'u'       => $this->post_data['permalink'] ?? '',
			'title'   => $this->post_data['title'] ?? '',
			'summary' => $this->post_data['excerpt'] ?? '',
		);
		
		if ( ! empty( $this->post_data['image'] ?? '' ) ) {
			$args['image'] = $this->post_data['image'];
		}
		
		return add_query_arg( $args, 'https://www.facebook.com/sharer.php' );
	}

	/**
	 * Generate X (Twitter) share link.
	 *
	 * @return string X share link HTML.
	 */
	private function generate_x_link() {
		$permalink = $this->post_data['permalink'] ?? '';
		
		$args = array(
			'original_referer' => $permalink,
			'tw_p'             => 'tweetbutton',
			'url'              => $permalink,
		);
		
		if ( ! empty( $this->options['x_handle'] ?? '' ) ) {
			$args['via'] = $this->options['x_handle'];
		}
		
		$href = add_query_arg( $args, 'https://twitter.com/intent/tweet' );
		
		return sprintf(
			'<a href="%s" rel="nofollow noopener external" target="_blank" title="%s"></a>',
			esc_url( $href ),
			esc_attr__( 'Share on X', 'add-to-some' )
		);
	}

	/**
	 * Generate Pinterest share link.
	 *
	 * @return string Pinterest share link HTML.
	 */
	private function generate_pinterest_link() {
		$args = array(
			'url'         => $this->post_data['permalink'] ?? '',
			'description' => $this->post_data['title'] ?? '',
		);
		
		if ( ! empty( $this->post_data['image'] ?? '' ) ) {
			$args['media'] = $this->post_data['image'];
		}
		
		$href = add_query_arg( $args, 'https://pinterest.com/pin/create/link/' );
		
		return sprintf(
			'<a href="%s" rel="nofollow noopener external" target="_blank" title="%s"></a>',
			esc_url( $href ),
			esc_attr__( 'Save to Pinterest', 'add-to-some' )
		);
	}

	/**
	 * Generate email share link.
	 *
	 * @return string Email share link HTML.
	 */
	private function generate_email_link() {
		$title     = (string) ( $this->post_data['title'] ?? '' );
		$excerpt   = (string) ( $this->post_data['excerpt'] ?? '' );
		$permalink = $this->post_data['permalink'] ?? '';
		
		$subject = rawurlencode( $title );
		$body    = rawurlencode( 
			trim( $excerpt ) . 
			( $excerpt ? "\n\n" : '' ) . 
			$permalink 
		);
		
		$href = 'mailto:?subject=' . $subject . '&body=' . $body;
		
		return sprintf(
			'<a href="%s" title="%s" class="ats-email-share"></a>',
			esc_url( $href ),
			esc_attr__( 'Share via Email', 'add-to-some' )
		);
	}

	/**
	 * Generate Pocket share link.
	 *
	 * @return string Pocket share link HTML.
	 */
	private function generate_pocket_link() {
		$href = add_query_arg(
			array(
				'url'   => $this->post_data['permalink'] ?? '',
				'title' => $this->post_data['title'] ?? '',
			),
			'https://getpocket.com/save'
		);
		
		return sprintf(
			'<a href="%s" rel="nofollow noopener external" target="_blank" title="%s"></a>',
			esc_url( $href ),
			esc_attr__( 'Save to Pocket', 'add-to-some' )
		);
	}

	/**
	 * Generate native share link.
	 *
	 * @return string Native share link HTML.
	 */
	private function generate_native_link() {
		$permalink = $this->post_data['permalink'] ?? '';
		$title     = $this->post_data['title'] ?? '';
		$excerpt   = (string) ( $this->post_data['excerpt'] ?? '' );
		
		return sprintf(
			'<a href="%s" class="ats-native-share" data-url="%s" data-title="%s" data-text="%s" title="%s"></a>',
			esc_url( $permalink ),
			esc_attr( $permalink ),
			esc_attr( $title ),
			esc_attr( wp_strip_all_tags( $excerpt ) ),
			esc_html__( 'Native Share', 'add-to-some' )
		);
	}
}

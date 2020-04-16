<?php

/**
 *
 * @since 5.5
 */
class CFF_Education {

	var $plugin_version;

	/**
	 * Constructor.
	 *
	 * @since 5.5
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 5.5
	 */
	public function hooks() {
		$this->plugin_version = defined( 'WPW_SL_STORE_URL' ) ? 'pro' : 'free';
	}

	/**
	 * "Did You Know?" messages.
	 *
	 * @since 5.5
	 */
	public function dyk_messages() {

		$free_messages = array(
			array(
				'title' => esc_html__( 'Did You Know?', 'custom-facebook-feed' ),
				'content' => esc_html__( 'Our Pro version offers a visual moderation system. You can create a "white list" or block individual posts in your feeds.', 'custom-facebook-feed' ),
				'more' => 'https://smashballoon.com/guide-to-moderation-mode/',
				'item' => 1,
			),
			array(
				'title' => esc_html__( 'Did You Know?', 'custom-facebook-feed' ),
				'content' => esc_html__( 'There are several layouts to display your feed if you upgrade to Pro. Choose from the standard grid, carousel, highlight, or masonry layouts.', 'custom-facebook-feed' ),
				'more' => 'https://smashballoon.com/instagram-feed/demo/highlight/',
				'item' => 2,
			),
		);

		$pro_messages = array(
			array(
				'title' => esc_html__( 'Did You Know?', 'custom-facebook-feed' ),
				'content' => esc_html__( 'We now offer a YouTube plugin for displaying video content on your WordPress site. It\'s easy to set up and blends in with your theme just like Instagram Feed', 'custom-facebook-feed' ),
				'more' => 'https://smashballoon.com/youtube-feed/',
				'item' => 1,
			),
			array(
				'title' => esc_html__( 'Did You Know?', 'custom-facebook-feed' ),
				'content' => esc_html__( 'Have a question or need help with your plugin? Our support team is here to help. Let our experts help you keep your feeds up and running.', 'custom-facebook-feed' ),
				'more' => 'https://smashballoon.com/support/',
				'item' => 2,
			),
		);

		if ( $this->plugin_version === 'pro' ) {
			return $pro_messages;
		}
		return $free_messages;

	}

	/**
	 * "Did You Know?" random message.
	 *
	 * @since 5.5
	 */
	public function dyk_message_rnd() {

		$messages = $this->dyk_messages();

		$index = array_rand( $messages );

		return $messages[ $index ];
	}

	/**
	 * "Did You Know?" display message.
	 *
	 * @since 5.5
	 *
	 */
	public function dyk_display() {

		$dyk_message  = $this->dyk_message_rnd();

		if ( ! empty( $dyk_message['more'] ) ) {
			$dyk_message['more'] = add_query_arg(
				array(
					'utm_source'   => 'WordPress',
					'utm_medium'   => 'DYK Email',
					'utm_campaign' => 'pluginfree',
					'utm_content'  => $dyk_message['item'],
				),
				$dyk_message['more']
			);
		}

		return $dyk_message;
	}
}

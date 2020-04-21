<?php
/**
 * Class CFF_Error_Reporter
 *
 * Set as a global object to record and report errors
 *
 * @since
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class CFF_Error_Reporter
{
	/**
	 * @var array
	 */
	var $errors;

	/**
	 * @var array
	 */
	var $frontend_error;
	
	var $reporter_key;

	/**
	 * CFF_Error_Reporter constructor.
	 */
	public function __construct() {
		$this->reporter_key = 'cff_error_reporter';
		$this->errors = get_option( $this->reporter_key, array() );
		$this->frontend_error = '';
		add_action( 'cff_feed_issue_email', array( $this, 'maybe_trigger_report_email_send' ) );
		add_action( 'wp_ajax_cff_dismiss_critical_notice', array( $this, 'dismiss_critical_notice' ) );
		add_action( 'wp_footer', array( $this, 'critical_error_notice' ), 300 );
		add_action( 'admin_notices', array( $this, 'admin_error_notices' ) );
	}
	
	/**
	 * @return array
	 *
	 * @since 2.0/4.0
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * @param $type
	 * @param $message_array
	 *
	 * @since 2.0/4.0
	 */
	public function add_error( $type, $args ) {
		if ( $type === 'accesstoken' ) {
			$accesstoken_error_exists = false;
			if ( isset( $this->errors['accesstoken'] ) ) {
				foreach ($this->errors['accesstoken'] as $accesstoken ) {
					if ( $args['accesstoken'] === $accesstoken['accesstoken'] ) {
						$accesstoken_error_exists = true;
					}
				}
			}

			if ( !$accesstoken_error_exists ) {
				$this->errors['accesstoken'][] = array(
					'accesstoken' => $args['accesstoken'],
					'post_id' => $args['post_id'],
					'errorno' => $args['errorno']
				);
			}

		} else {
			$this->errors[ $type ] = array(
				'accesstoken' => $args['accesstoken'],
				'public_message' => $args['public_message'],
				'admin_message' => $args['admin_message'],
				'frontend_directions' => $args['frontend_directions'],
				'backend_directions' => $args['backend_directions'],
				'post_id' => $args['post_id'],
				'errorno' => $args['errorno']
			);
		}

		update_option( $this->reporter_key, $this->errors, false );
	}

	/**
	 * @param $type
	 *
	 * @since 2.0/4.0
	 */
	public function remove_error( $type ) {
		$errors = $this->errors;

		if ( isset( $errors[ $type ] ) ) {
			unset( $errors[ $type ] );
		}

		if ( empty( $errors ) ) {
			delete_option( $this->reporter_key );
			$this->errors = array();
		} else {
			update_option( $this->reporter_key, $errors, false );
			$this->errors = $errors;
		}
	}

	public function remove_all_errors() {
		delete_option( $this->reporter_key );
		$this->errors = array();
	}

	/**
	 * @param $type
	 * @param $message
	 *
	 * @since 2.0/5.0
	 */
	public function add_frontend_error( $message, $directions ) {
		$this->frontend_error = $message . $directions;
	}

	public function remove_frontend_error() {
		$this->frontend_error = '';
	}

	/**
	 * @return string
	 *
	 * @since 2.0/5.0
	 */
	public function get_frontend_error() {
		return $this->frontend_error;
	}

	public function get_critical_errors() {
		$errors = $this->get_errors();

		return $errors;
	}

	public function are_critical_errors() {
		$are_errors = false;

		$errors = $this->get_errors();

		foreach ( $errors as $error ) {
			$are_errors = true;
		}

		return $are_errors;
	}


	/**
	 * Load the critical notice for logged in users.
	 */
	function critical_error_notice() {
		// Don't do anything for guests.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Only show this to users who are not tracked.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ! $this->are_critical_errors() ) {
			return;
		}


		// Don't show if already dismissed.
		if ( get_option( 'cff_dismiss_critical_notice', false ) ) {
			return;
		}

		$options = get_option('cff_style_settings');
		if ( isset( $options['disable_admin_notice'] ) && $options['disable_admin_notice'] === 'on' ) {
			return;
		}

		?>
		<div class="cff-critical-notice cff-critical-notice-hide">
			<div class="cff-critical-notice-icon">
				<img src="<?php echo CFF_PLUGIN_URL . 'img/cff-icon.png'; ?>" width="45" alt="Custom Facebook Feed icon" />
			</div>
			<div class="cff-critical-notice-text">
				<h3><?php esc_html_e( 'Custom Facebook Feed Critical Issue', 'custom-facebook-feed' ); ?></h3>
				<p>
					<?php
					$doc_url = admin_url() . 'admin.php?page=cff-top';
					// Translators: %s is the link to the article where more details about critical are listed.
					printf( esc_html__( 'An issue is preventing your Custom Facebook Feeds from updating. %1$sResolve this issue%2$s.', 'custom-facebook-feed' ), '<a href="' . esc_url( $doc_url ) . '" target="_blank">', '</a>' );
					?>
				</p>
			</div>
			<div class="cff-critical-notice-close">&times;</div>
		</div>
		<style type="text/css">
			.cff-critical-notice {
				position: fixed;
				bottom: 20px;
				right: 15px;
				font-family: Arial, Helvetica, "Trebuchet MS", sans-serif;
				background: #fff;
				box-shadow: 0 0 10px 0 #dedede;
				padding: 10px 10px;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 325px;
				max-width: calc( 100% - 30px );
				border-radius: 6px;
				transition: bottom 700ms ease;
				z-index: 10000;
			}

			.cff-critical-notice h3 {
				font-size: 13px;
				color: #222;
				font-weight: 700;
				margin: 0 0 4px;
				padding: 0;
				line-height: 1;
				border: none;
			}

			.cff-critical-notice p {
				font-size: 12px;
				color: #7f7f7f;
				font-weight: 400;
				margin: 0;
				padding: 0;
				line-height: 1.2;
				border: none;
			}

			.cff-critical-notice p a {
				color: #7f7f7f;
				font-size: 12px;
				line-height: 1.2;
				margin: 0;
				padding: 0;
				text-decoration: underline;
				font-weight: 400;
			}

			.cff-critical-notice p a:hover {
				color: #666;
			}

			.cff-critical-notice-icon img {
				height: auto;
				display: block;
				margin: 0;
			}

			.cff-critical-notice-icon {
				padding: 0;
				border-radius: 4px;
				flex-grow: 0;
				flex-shrink: 0;
				margin-right: 12px;
				overflow: hidden;
			}

			.cff-critical-notice-close {
				padding: 10px;
				margin: -12px -9px 0 0;
				border: none;
				box-shadow: none;
				border-radius: 0;
				color: #7f7f7f;
				background: transparent;
				line-height: 1;
				align-self: flex-start;
				cursor: pointer;
				font-weight: 400;
			}
			.cff-critical-notice-close:hover,
			.cff-critical-notice-close:focus{
				color: #111;
			}

			.cff-critical-notice.cff-critical-notice-hide {
				bottom: -200px;
			}
		</style>
		<?php

		if ( ! wp_script_is( 'jquery', 'queue' ) ) {
			wp_enqueue_script( 'jquery' );
		}
		?>
		<script>
            if ( 'undefined' !== typeof jQuery ) {
                jQuery( document ).ready( function ( $ ) {
                    /* Don't show the notice if we don't have a way to hide it (no js, no jQuery). */
                    $( document.querySelector( '.cff-critical-notice' ) ).removeClass( 'cff-critical-notice-hide' );
                    $( document.querySelector( '.cff-critical-notice-close' ) ).on( 'click', function ( e ) {
                        e.preventDefault();
                        $( this ).closest( '.cff-critical-notice' ).addClass( 'cff-critical-notice-hide' );
                        $.ajax( {
                            url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                            method: 'POST',
                            data: {
                                action: 'cff_dismiss_critical_notice',
                                nonce: '<?php echo esc_js( wp_create_nonce( 'cff-critical-notice' ) ); ?>',
                            }
                        } );
                    } );
                } );
            }
		</script>
		<?php
	}

	/**
	 * Ajax handler to hide the critical notice.
	 */
	public function dismiss_critical_notice() {

		check_ajax_referer( 'cff-critical-notice', 'nonce' );

		update_option( 'cff_dismiss_critical_notice', 1, false );

		wp_die();

	}

	public function send_report_email() {
		$options = get_option( 'cff_style_settings', array() );

		$to_string = ! empty( $options['email_notification_addresses'] ) ? str_replace( ' ', '', $options['email_notification_addresses'] ) : get_option( 'admin_email', '' );

		$to_array_raw = explode( ',', $to_string );
		$to_array = array();

		foreach ( $to_array_raw as $email ) {
			if ( is_email( $email ) ) {
				$to_array[] = $email;
			}
		}

		if ( empty( $to_array ) ) {
			return false;
		}
		$from_name = esc_html( wp_specialchars_decode( get_bloginfo( 'name' ) ) );
		$email_from = $from_name . ' <' . get_option( 'admin_email', $to_array[0] ) . '>';
		$header_from  = "From: " . $email_from;

		$headers = array( 'Content-Type: text/html; charset=utf-8', $header_from );

		$header_image = CFF_PLUGIN_URL . 'img/balloon-120.png';
		$title = __( 'Custom Facebook Feed Report for ' . home_url() );
		$link = admin_url( 'admin.php?page=cff-top');
		//&tab=customize-advanced
		$footer_link = admin_url('admin.php?page=cff-style&tab=misc&flag=emails');
		$bold = __( 'There\'s an Issue with a Facebook Feed on Your Website', 'custom-facebook-feed' );
		$details = '<p>' . __( 'A Custom Facebook Feed on your website is currently unable to connect to Facebook to retrieve new posts. Don\'t worry, your feed is still being displayed using a cached version, but is no longer able to display new posts.', 'custom-facebook-feed' ) . '</p>';
		$details .= '<p>' . sprintf( __( 'This is caused by an issue with your Facebook account connecting to the Facebook API. For information on the exact issue and directions on how to resolve it, please visit the %sCustom Facebook Feed settings page%s on your website.', 'custom-facebook-feed' ), '<a href="' . esc_url( $link ) . '">', '</a>' ). '</p>';
		$message_content = '<h6 style="padding:0;word-wrap:normal;font-family:\'Helvetica Neue\',Helvetica,Arial,sans-serif;font-weight:bold;line-height:130%;font-size: 16px;color:#444444;text-align:inherit;margin:0 0 20px 0;Margin:0 0 20px 0;">' . $bold . '</h6>' . $details;
		include_once CFF_PLUGIN_DIR . 'class-cff-education.php';
		$educator = new CFF_Education();
		$dyk_message = $educator->dyk_display();
		ob_start();
		include CFF_PLUGIN_DIR . 'email.php';
		$email_body = ob_get_contents();
		ob_get_clean();
		$sent = wp_mail( $to_array, $title, $email_body, $headers );

		return $sent;
	}

	public function maybe_trigger_report_email_send() {
		if ( ! $this->are_critical_errors() ) {
			return;
		}
		$options = get_option('cff_style_settings');

		if ( isset( $options['enable_email_report'] ) && empty( $options['enable_email_report'] ) ) {
			return;
		}

		$this->send_report_email();
	}

	public function admin_error_notices() {
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'cff-top', 'cff-style' )) ) {
			
			$errors = $this->get_critical_errors();
			if ( $this->are_critical_errors() && ! empty( $errors ) ) : 
				if ( isset( $errors['wp_remote_get'] ) ) {
					$error = $errors['wp_remote_get'];
					$error_message = $error['admin_message'];
					$button = $error['backend_directions'];
					$post_id = $error['post_id'];
					$directions = '<p class="cff-error-directions">';
					$directions .= $button;
					$directions .= '<button data-url="'.get_the_permalink( $post_id ).'" class="cff-clear-errors-visit-page cff-space-left button button-secondary">' . __( 'View Feed and Retry', 'custom-facebook-feed' )  . '</button>';
					$directions .=	'</p>';
				} elseif ( isset( $errors['api'] ) ) {
					$error = $errors['api'];
					$error_message = $error['admin_message'];
					$button = $error['backend_directions'];
					$post_id = $error['post_id'];
					$directions = '<p class="cff-error-directions">';
					$directions .= $button;
					$directions .= '<button data-url="'.get_the_permalink( $post_id ).'" class="cff-clear-errors-visit-page cff-space-left button button-secondary">' . __( 'View Feed and Retry', 'custom-facebook-feed' )  . '</button>';
					$directions .=	'</p>';
				} else {
					$error = $errors['accesstoken'];
					
					$tokens = array();
					$post_id = false;
					foreach ( $error as $token ) {
						$tokens[] = $token['accesstoken'];
						$post_id = $token['post_id'];
					}
					$error_message = sprintf( __( 'The access token %s is invalid or has expired.', 'custom-facebook-feed' ), implode( $tokens, ', ' ) );
					$directions = '<p class="cff-error-directions">';
					$directions .= '<button class="button button-primary cff-reconnect">' . __( 'Reconnect Your Account', 'custom-facebook-feed' )  . '</button>';
					$directions .= '<button data-url="'.get_the_permalink( $post_id ).'" class="cff-clear-errors-visit-page cff-space-left button button-secondary">' . __( 'View Feed and Retry', 'custom-facebook-feed' )  . '</button>';
					$directions .=	'</p>';
				}
				?>
				<div class="notice notice-warning is-dismissible cff-admin-notice">
					<p><strong><?php echo esc_html__( 'Custom Facebook Feed is encountering an error and your feeds may not be updating due to the following reasons:', 'custom-facebook-feed') ; ?></strong></p>

					<?php echo $error_message; ?>

					<?php echo $directions; ?>
				</div>
			<?php endif;
		}

	}

}
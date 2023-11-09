<?php
/**
 * Handling license activation, deactivation & license status check.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    themehigh-license-handler
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'THWCFE_EDD_Updater_Helper' ) ) :

	/**
	 * Template Class Doc Comment
	 *
	 * EDD Updater Helper Class
	 *
	 * @link     https://themehigh.com
	 */
	class THWCFE_EDD_Updater_Helper {

		/**
		 * Main plugin file path
		 *
		 * @var  string
		 */
		private $file = '';

		/**
		 * Plugin & license details in an array format
		 *
		 * @var  array
		 */
		private $data = array();

		/**
		 * Class constructor
		 *
		 * @param  string $file main plugin file path.
		 * @param  array  $data Plugin & license related data in the array format with proper keys.
		 */
		public function __construct( $file, $data ) {
			$plugin_header      = $this->read_plugin_header( $file );
			$this->product_name = $plugin_header['Plugin Name'];

			$this->api_url         = trailingslashit( $data['api_url'] );
			$this->item_id         = $data['item_id'];
			$this->item_identifier = $data['item_identifier'];

			$this->wp_override      = isset( $data['wp_override'] ) ? (bool) $data['wp_override'] : false;
			$this->beta             = isset( $data['beta'] ) ? (bool) $data['beta'] : false;
			$this->license_page_url = isset( $data['license_page_url'] ) ? $data['license_page_url'] : '';
			$this->sw_identifier    = $this->prepare_software_identifier( $this->item_identifier );
			$this->license_data_key = $this->sw_identifier . '_license_data';
			if ( is_multisite() ) {
				$this->domain = str_ireplace( array( 'http://', 'https://' ), '', network_site_url() );
			} else {
				$this->domain = str_ireplace( array( 'http://', 'https://' ), '', home_url() );
			}

			// Set up hooks.
			$this->init( $file );
		}


		/**
		 * Set up WordPress filters & actions to hook into WP's update process.
		 *
		 * @uses add_filter()
		 *
		 * @param  string $file plugin main file.
		 *
		 * @return void
		 */
		public function init( $file ) {
			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'log_developer_data' ) );
				add_action( 'admin_init', array( $this, 'prepare_license_form_shortcode' ) );
				add_action( 'admin_init', array( $this, 'license_form_listener' ) );
				add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
				add_action( 'in_plugin_update_message-' . basename( dirname( $file ) ) . '/' . basename( $file ), array( $this, 'plugin_update_message' ), 10, 2 );
			}

			add_action( 'wp', array( $this, 'schedule_license_status_check_daily_cron' ) );
			add_action( 'check_license_' . $this->sw_identifier, array( $this, 'check_license' ) );
		}

		/**
		 * Write content on WordPress debug log file.
		 *
		 * @since  1.0.0
		 * @param  mixed $log  Content to be printed. It may be string, array or object.
		 */
		private function write_log( $log ) {
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( print_r( $log, true ) );
				} else {
					error_log( $log );
				}
			}
		}


		/**
		 * Writing software identifier, short code & license option key for developer in to log file.
		 *
		 * @since  1.0.0
		 */
		public function log_developer_data() {
			/**
			* Apply filter to log developer data for plugin.
			*
			* @since  1.0.0
			*/
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_license_client_log_developer_data', false ) ) {
				$this->write_log( '----- Developer tips start -----' );
				$this->write_log( 'Software identifier: ' . $this->sw_identifier );
				$this->write_log( 'License form shortcode: [' . $this->sw_identifier . '_license_form]' );
				$this->write_log( 'Option table key for license data: ' . $this->license_data_key );
				$this->write_log( '----- Developer tips end -----' );
			}
		} // function log_developer_data


		/**
		 * Read plugin header.
		 *
		 * @since  1.0.0
		 * @param string $file Plugin main file.
		 * @return array
		 */
		private function read_plugin_header( $file ) {
			$plugin_data = get_file_data(
				$file,
				array(
					'Plugin Name' => 'Plugin Name',
				),
				'plugin'
			);

			return $plugin_data;
		} // function read_plugin_header

		/**
		 * Preparing unique identifier.
		 *
		 * @since  1.0.0
		 * @param  string $identifier  Unique string / name for the plugin.
		 * @return string
		 */
		public static function prepare_software_identifier( $identifier ) {
			$identifier    = preg_replace( '/\s+/', '_', $identifier );
			$sw_identifier = str_replace( '-', '_', $identifier );
			$sw_identifier = 'th_' . sanitize_key( $sw_identifier );
			return $sw_identifier;
		} // function prepare_software_identifier


		/**
		 * Updating license data.
		 *
		 * @since  1.0.0
		 * @param  Array $data Updated license data to be saved.
		 */
		private function update_license_data( $data ) {
			if ( empty( $data ) ) {
				if ( is_multisite() ) {
					delete_site_option( $this->license_data_key );
				} else {
					delete_option( $this->license_data_key );
				}
			} else {
				$existing_data = $this->get_license_data();
				$updated_data  = array_merge( $existing_data, $data );
				if ( is_multisite() ) {
					update_site_option( $this->license_data_key, $updated_data, 'no' );
				} else {
					update_option( $this->license_data_key, $updated_data, 'no' );
				}
			}
		} // function update_license_data


		/**
		 * Get license data.
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_license_data() {
			$license_data = false;
			if ( is_multisite() ) {
				$license_data = get_site_option( $this->license_data_key );
			} else {
				$license_data = get_option( $this->license_data_key );
			}
			return is_array( $license_data ) && ! empty( $license_data ) ? $license_data : array();
		} // function get_license_data


		/**
		 * Returns a valid date in the Y-m-d H:i:s format.
		 *
		 * @since  1.0.0
		 * @param  string $date The date string to be formated.
		 * @param  string $format The date format.
		 * @return bool
		 */
		public static function valid_date_format( $date, $format = 'Y-m-d H:i:s' ) {
			$d = DateTime::createFromFormat( $format, $date );
			// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
			return $d && $d->format( $format ) === $date;
		} // function valid_date_format


		/**
		 * Hook to create the license form short code.
		 *
		 * @since  1.0.0
		 */
		public function prepare_license_form_shortcode() {
			add_shortcode( $this->sw_identifier . '_license_form', array( $this, 'license_form_shortcode_callback' ) );
		} // function prepare_license_form_shortcode


		/**
		 * Callback of the license form short code.
		 *
		 * @since  1.0.0
		 * @param  array $atts Shortcode attributes.
		 */
		public function license_form_shortcode_callback( $atts ) {
			$params = shortcode_atts(
				array(
					'wrapper_class' => 'th-license-form',
				),
				$atts
			);
			ob_start();
			$this->output_license_form_container( $params );
			return ob_get_clean();
		} // function license_form_shortcode_callback


		/**
		 * Display the license form container.
		 *
		 * @since  1.0.0
		 * @param  array $params Properties for the form.
		 */
		private function output_license_form_container( $params ) {
			$wrapper_class = $params['wrapper_class'];
			$license_data  = $this->get_license_data();
			$status        = isset( $license_data['status'] ) ? $license_data['status'] : false;
			$license_key   = isset( $license_data['license_key'] ) ? $license_data['license_key'] : false;
			$box_style     = 'margin-top: 20px; padding: 20px 30px 10px 30px; background-color: #fff; box-shadow: 1px 1px 5px 1px rgba(0,0,0,.1); min-height: 240px;';
			$box_left      = $box_style;
			$box_right     = $box_style;

			if ( $license_key ) {
				$box_left  .= 'width: 35%; float:left; margin-right: 20px;';
				$box_right .= 'width: 35%; float:left;';
			} else {
				$box_left .= 'width: 70%;';
			}
			?>
		<div style="<?php echo esc_html( $box_left ); ?>" class="<?php echo esc_attr( $wrapper_class ); ?> <?php echo esc_attr( $this->sw_identifier ); ?>-license_form">
			<?php
			$this->output_license_form( $license_data );
			?>
		</div>
			<?php

			if ( $license_key ) {
				?>
			<div style="<?php echo esc_html( $box_right ); ?>" class="<?php echo esc_attr( $this->sw_identifier ); ?>-license_info">
				<?php
				$this->output_license_info( $license_data );
				?>
			</div>
			<div style="clear: both;"></div>
				<?php
			}
		} // function output_license_form_container


		/**
		 * Display the license form.
		 *
		 * @since  1.0.0
		 * @param  array $license_data Saved license data.
		 */
		private function output_license_form( $license_data ) {
			$license_key = isset( $license_data['license_key'] ) ? $license_data['license_key'] : '';
			$status      = isset( $license_data['status'] ) ? $license_data['status'] : false;

			$input_style         = 'width: 100%; padding: 10px;';
			$license_field_attr  = 'name="license_key"';
			$license_field_attr .= ' placeholder="License key ( e.g. LDXXRJZQ341X9TH9GFMADYDAA15PE8 )"';
			$form_title_note     = '';
			$form_footer_note    = '';

			if ( $license_key ) {
				$license_field_attr .= ' value="' . $license_key . '"';
				$license_field_attr .= ' readonly';
				$btn_label           = 'Deactivate';
				$btn_action          = 'deactivate';
				$form_footer_note    = 'Deactivate License Key so that it can be used on another domain.';
			} else {
				$license_field_attr .= ' value=""';
				$btn_label           = 'Activate';
				$btn_action          = 'activate';

				$license_form_title_note = 'Enter your License Key and hit activate button.';

				/**
				* Apply filter to modify title note.
				*
				* @since  1.0.0
				*/
				$license_form_title_note = apply_filters( 'thedd_license_form_title_note', $license_form_title_note, $this->sw_identifier );

				if ( $license_form_title_note ) {
					$form_title_note = '<p>' . $license_form_title_note . '</p>';
				}
			}
			$btn_action .= '-' . $this->sw_identifier;
			?>
			<h1>Software License Key</h1>
			<?php echo wp_kses( $form_title_note, wp_kses_allowed_html( 'post' ) ); ?>
			<form method='post' action='' >
				<p>
					<input type="text" <?php echo wp_kses( $license_field_attr, wp_kses_allowed_html( 'strip' ) ); ?> style="<?php echo esc_attr( $input_style ); ?>" />
					<?php wp_nonce_field( 'handle_license_form', 'nonce_license_form' ); ?>
				</p>
				<p>
					<button type="submit" name="action" value="<?php echo esc_attr( $btn_action ); ?>" class="button-primary"><?php echo esc_html( $btn_label ); ?></button>
				</p>
			</form>
			<?php
			echo esc_html( $form_footer_note );
		} // function output_license_form


		/**
		 * Display the license information.
		 *
		 * @since  1.0.0
		 * @param  array $license_data Saved license data.
		 */
		private function output_license_info( $license_data ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
			?>
			<h1><?php esc_html_e( 'License Details', 'text-domain' ); ?></h1>
			<?php
			$l_status       = isset( $license_data['status'] ) ? $license_data['status'] : '';
			$expiry         = isset( $license_data['expiry'] ) ? $license_data['expiry'] : '';
			$wp_date_format = get_option( 'date_format' );
			if ( $wp_date_format && $expiry && $this->valid_date_format( $expiry ) ) {
				$expiry = date_format( date_create( $expiry ), $wp_date_format );
			} elseif ( 'lifetime' === $expiry ) {
				$expiry = 'Lifetime';
			}

			if ( ( 'valid' === $l_status ) || ( 'active' === $l_status ) ) {
				$l_status_string = '<label style="color: green;">' . ucwords( $l_status ) . '<label>';
			} else {
				$l_status_string = '<label style="color: red;">' . ucwords( $l_status ) . '<label>';
			}

			$cell_style = 'padding: 10px 0; border-bottom: 1px solid #eee;';
			?>
			<table width="100%" style="font-size: 15px;">
				<tbody>
					<tr style="border-bottom: 1px solid ">
						<td style="<?php echo esc_attr( $cell_style ); ?>" width="40%"><strong><?php esc_html_e( 'License status', 'text-domain' ); ?></strong></td>
						<td style="<?php echo esc_attr( $cell_style ); ?>"><strong><?php echo wp_kses( $l_status_string, $allowed_html ); ?></strong></td>
					</tr>
					<?php if ( $expiry ) { ?>
						<tr>
							<td style="<?php echo esc_attr( $cell_style ); ?>"><strong><?php esc_html_e( 'Expiry', 'text-domain' ); ?></strong></td>
							<td style="<?php echo esc_attr( $cell_style ); ?>"> <?php echo esc_html( $expiry ); ?> </td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php
		} // function output_license_info


		/**
		 * Trigger on license activation and deactivation events.
		 *
		 * @since  1.0.0
		 */
		public function license_form_listener() {
			$post = wp_unslash( $_POST );
			if ( isset( $post['nonce_license_form'] ) && $post['nonce_license_form'] ) {
				if ( ! wp_verify_nonce( $post['nonce_license_form'], 'handle_license_form' ) ) {
					die( 'You are not authorized to perform this action.' );

				} else {
					$action = isset( $post['action'] ) ? $post['action'] : '';
					if ( 'activate-' . $this->sw_identifier === $action ) {
						$license_key = isset( $post['license_key'] ) ? $post['license_key'] : '';
						if ( $license_key ) {
							$this->trigger_license_request( 'activate', $post );
						}
					} elseif ( 'deactivate-' . $this->sw_identifier === $action ) {
						$this->trigger_license_request( 'deactivate', $post );
					}
				}
			}
		} // function license_form_listener

		/**
		 * Handling license activation and deactivation requests.
		 *
		 * @since  1.0.0
		 * @param  string $action The action to be performed.
		 * @param  array  $posted Submitted form data.
		 */
		private function trigger_license_request( $action, $posted ) {
			// data to send in our API request.
			$request_data = $this->prepare_request_data( $action, $posted );

			/**
			* Apply filter to log details during license activation & deactivation.
			*
			* @since  1.0.0
			*/
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_enable_log_on_helper', false ) ) {
				$this->write_log( '--- trigger_license_request ---' );
				$this->write_log( 'action' );
				$this->write_log( $action );
				$this->write_log( 'posted' );
				$this->write_log( $posted );
				$this->write_log( 'request_data' );
				$this->write_log( $request_data );
				$this->write_log( '--- trigger_license_request ---' );
			}

			$request = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $request_data,
				)
			);

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {

				/**
				* Apply filter to log response details on license activation & deactivation.
				*
				* @since  1.0.0
				*/
				if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_enable_log_on_helper', false ) ) {
					$this->write_log( '--- License request error ' . $action . '' );
					$this->write_log( $request );
					$this->write_log( '--- License request error ---' );
				}
			} else {
				$response     = wp_remote_retrieve_body( $request );
				$response_obj = json_decode( $response );

				/**
				* Apply filter to log response details on license activation & deactivation.
				*
				* @since  1.0.0
				*/
				if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_enable_log_on_helper', false ) ) {
					$this->write_log( '--- License request success ---' );
					$this->write_log( $response_obj );
					$this->write_log( '--- License request success ---' );
				}

				if ( $response_obj && ( false === $response_obj->success ) ) {
					if ( isset( $response_obj->error ) ) {

						switch ( $response_obj->error ) {

							case 'expired':
								if ( 'activate' === $action ) {
									$message                     = __( 'Your license key expired.', 'th_edd_license_helper' );
									$license_data                = $this->prepare_data_from_response( $response_obj );
									$license_data['license_key'] = isset( $posted['license_key'] ) ? $posted['license_key'] : '';
									$license_data['domain']      = isset( $request_data['url'] ) ? $request_data['url'] : '';
									$license_data['status']      = 'expired';
									$this->update_license_data( $license_data );
								} elseif ( 'deactivate' === $action ) {
									$message = __( 'License deactivated successfully.', 'th_edd_license_helper' );
									$this->update_license_data( array() );
								}

								break;

							case 'disabled':
							case 'revoked':
								$message = __( 'Your license key has been disabled.' );
								break;

							case 'missing':
								$message = __( 'Invalid license.' );
								break;

							case 'invalid':
							case 'site_inactive':
								$message = __( 'Your license is not active for this URL.' );
								break;

							case 'item_name_mismatch':
								/* translators: %s: Plugin name. */
								$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $this->product_name );
								break;

							case 'no_activations_left':
								$message = __( 'Your license key has reached its activation limit.' );
								break;

							default:
								$message = __( 'An error occurred, please try again.' );
								break;
						}
						if ( ! ( 'expired' === $response_obj->error ) ) {
							$this->update_license_data( array() );
						}
					} else {
						$message = __( 'An error occurred, please try again.' );
						$this->update_license_data( array() );
					}

					$allowed_html = wp_kses_allowed_html( 'post' );
					add_action(
						'admin_notices',
						function() use ( $message ) {
							?>
							<div class="notice notice-error <?php echo esc_attr( $this->sw_identifier ); ?>-admin-notice"><p><?php echo esc_html( $message ); ?></p></div>
							<?php
						}
					);

				} else {
					if ( 'activate' === $action ) {
						$message                     = __( 'License activated successfully.', 'th_edd_license_helper' );
						$license_data                = $this->prepare_data_from_response( $response_obj );
						$license_data['license_key'] = isset( $posted['license_key'] ) ? $posted['license_key'] : '';
						$license_data['domain']      = isset( $request_data['url'] ) ? $request_data['url'] : '';
						$this->update_license_data( $license_data );
					} elseif ( 'deactivate' === $action ) {
						$message = __( 'License deactivated successfully.', 'th_edd_license_helper' );
						$this->update_license_data( array() );
					}

					add_action(
						'admin_notices',
						function() use ( $message ) {
							?>
							<div class="notice notice-success  <?php echo esc_attr( $this->sw_identifier ); ?>-admin-notice"><p><?php echo esc_html( $message ); ?></p></div>
							<?php
						}
					);
				}
			}

		} // function trigger_license_request

		/**
		 * Preparing data to be submitted on license activation and deactivation requests.
		 *
		 * @since  1.0.0
		 * @param  string $action The action to be performed.
		 * @param  array  $posted Submitted form data.
		 */
		private function prepare_request_data( $action, $posted ) {
			// Validate Posted Data & create data array to POST to API.
			$license_key = isset( $posted['license_key'] ) ? $posted['license_key'] : '';
			$license_key = trim( $license_key );
			if ( 'activate' === $action ) {

				$api_params = array(
					'edd_action'  => 'activate_license',
					'license'     => $license_key,
					'item_id'     => $this->item_id,
					'url'         => $this->domain,
					'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
				);

			} elseif ( 'deactivate' === $action ) {

				$api_params = array(
					'edd_action'  => 'deactivate_license',
					'license'     => $license_key,
					'item_id'     => $this->item_id,
					'url'         => $this->domain,
					'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
				);

			}
			return $api_params;
		} // function prepare_request_data


		/**
		 * Preparing data to be saved from response.
		 *
		 * @since  1.0.0
		 * @param  object $response_obj The JSON decoded response object of license request.
		 * @return  Array
		 */
		private function prepare_data_from_response( $response_obj ) {
			return array(
				'status' => $response_obj->license,
				'expiry' => $response_obj->expires,
			);
		} // function prepare_data_from_response


		/**
		 * Display admin notice if license is not activated.
		 *
		 * @since  1.0.0
		 */
		public function display_admin_notices() {
			/**
			* Apply filter to show admin notice.
			*
			* @since  1.0.0
			*/
			if ( ! apply_filters( 'themehigh_show_admin_notice', true, $this->sw_identifier ) ) {
				return;
			}

			$ldata = $this->get_license_data();

			if ( ! $ldata ) {
				$notice = 'The license of <strong>%s</strong> is not activated. <a href="%s">Click here</a> to activate the license.';
			}

			if ( ! empty( $notice ) ) {
				if ( is_multisite() ) {
					/**
					* Apply filter to show admin notice in subsite in multi site.
					*
					* @since  1.0.0
					*/
					$enable_notification_sub_site = apply_filters( 'themehigh_enable_notifications_sub_site', true, $this->sw_identifier );

					if ( is_main_site() ) {
						$this->show_admin_notice_content( $notice, 'admin_notice' );
					} else {
						if ( $enable_notification_sub_site ) {
							$this->show_admin_notice_content( $notice, 'admin_notice' );
						}
					}
				} else {
					$this->show_admin_notice_content( $notice, 'admin_notice' );
				}
			}
		} // function display_admin_notices


		/**
		 * Show license not activated admin notice content
		 *
		 * @since  1.0.0
		 * @param  string $notice  Message to be displayed.
		 * @param  string $type Notice type.
		 */
		private function show_admin_notice_content( $notice, $type = 'admin_notice' ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
			$notice       = sprintf( $notice, $this->product_name, $this->license_page_url );
			?>
			<div class="error notice <?php echo esc_attr( $this->sw_identifier ); ?>-admin-notice">
				<p><?php echo wp_kses( $notice, $allowed_html ); ?></p>
			</div>
			<?php
		} // function show_admin_notice_content


		/**
		 * Creating cron job that run once in a day to check license status
		 *
		 * @since  1.0.0
		 */
		public function schedule_license_status_check_daily_cron() {
			if ( ! wp_next_scheduled( 'check_license_' . $this->sw_identifier ) ) {
				wp_schedule_event( time(), 'daily', 'check_license_' . $this->sw_identifier );
			}
		} // function schedule_license_status_check_daily_cron


		/**
		 * Checks if a license key is still valid.
		 *
		 * @since  1.0.0
		 */
		public function check_license() {
			/**
			* Apply filter to log license status check event.
			*
			* @since  1.0.0
			*/
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_enable_log_on_helper', false ) ) {
				$this->write_log( '--- check_license event start---' );
				$this->write_log( current_datetime() );
				$this->write_log( '------' );
			}
			$saved_license_data = $this->get_license_data();

			$license_key = isset( $saved_license_data['license_key'] ) ? $saved_license_data['license_key'] : false;
			$domain      = isset( $saved_license_data['domain'] ) ? $saved_license_data['domain'] : $this->domain;

			if ( ! $license_key ) {
				return false;
			}

			if ( $domain !== $this->domain ) {
				return false;
			}

			$api_params = array(
				'edd_action'  => 'check_license',
				'license'     => $license_key,
				'item_id'     => $this->item_id,
				'url'         => $this->domain,
				'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
			);

			// Call the custom API.
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			/**
			* Apply filter to log data on license status check event.
			*
			* @since  1.0.0
			*/
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_enable_log_on_helper', false ) ) {
				$this->write_log( '--- check_license start ---' );
				$this->write_log( 'api_params' );
				$this->write_log( $api_params );
				$this->write_log( 'response' );
				$this->write_log( $response );
				$this->write_log( '------' );
			}

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response_obj = json_decode( wp_remote_retrieve_body( $response ) );

			/**
			* Apply filter to log data on license status check event.
			*
			* @since  1.0.0
			*/
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && apply_filters( 'thedd_enable_log_on_helper', false ) ) {
				$this->write_log( '--- check_license response_obj ---' );
				$this->write_log( $response_obj );
				$this->write_log( '--- check_license response_obj ---' );
			}

			if ( ( 'expired' === $response_obj->license ) || ( 'valid' === $response_obj->license ) ) {

				$new_data = array(
					'status' => $response_obj->license,
					'expiry' => $response_obj->expires,
				);

				$new_license_data = array_replace( $saved_license_data, $new_data );

				if ( $new_license_data !== $saved_license_data ) {
					$this->update_license_data( $new_license_data );
				}
			} else {

				$this->update_license_data( array() );

			}

		} // function check_license


		/**
		 * Display custom update message on plugin listing page.
		 *
		 * @since  1.0.0
		 * @param  array  $plugin_data  An array of plugin metadata.
		 * @param  object $response  An object of metadata about the available plugin update.
		 */
		public function plugin_update_message( $plugin_data, $response ) {
			$home_url       = isset( $plugin_data['homepage'] ) ? $plugin_data['homepage'] : '#';
			$license_data   = $this->get_license_data();
			$license_key    = isset( $license_data['license_key'] ) ? $license_data['license_key'] : false;
			$license_status = isset( $license_data['status'] ) ? $license_data['status'] : false;

			if ( isset( $plugin_data['update'] ) && $plugin_data['update'] && ( ! $license_key ) || ! ( ( 'valid' === $license_status ) || ( 'active' === $license_status ) ) ) {
				ob_start();
				?>
				<br><br>
				<span class="wp-ui-text-notification alert dashicons dashicons-warning"></span>
				<?php
				/* translators: %1$s is the plugin name, %2$s and %3$s are a link. */
				echo sprintf( esc_html__( 'You need an active license key to get updates and receive support for %1$s. We recommend you to %2$srenew your subscription%3$s if you want to update to the latest version.', 'text-domain' ), esc_html( $this->product_name ), '<a href="' . esc_attr( $home_url ) . '" target="_blank">', '</a>' );
 
				$notice = ob_get_clean();
				ob_end_flush();
				/**
				* Apply filter to modify the custom update message.
				*
				* @since  1.0.0
				*/
				$notice = apply_filters( 'thedd_plugin_update_custom_notice', $notice, $this->sw_identifier, $plugin_data, $response );
				echo wp_kses( $notice, wp_kses_allowed_html( 'post' ) );
			}
		} // function plugin_update_message

	}

endif;

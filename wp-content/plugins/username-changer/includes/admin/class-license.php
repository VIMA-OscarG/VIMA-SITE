<?php
/**
 * License management
 *
 * @package     UsernameChanger\Admin\License
 * @since       4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Username_Changer_License' ) ) {

	class Username_Changer_License {

		private static $instance;

		const OPTION_KEY    = 'uc_pro_license_key';
		const OPTION_STATUS = 'uc_pro_license_status';
		const OPTION_EXPIRY = 'uc_pro_license_expires';

		/**
		 * The URL of the WordPress site running Software License Manager plugin.
		 * Replace with your actual license server URL (Daniel's site).
		 */
		const API_URL = 'https://your-license-server.example.com';

		/**
		 * Secret key from SLM Settings > "Secret Key for License Verification Requests".
		 * Replace with the actual key from Daniel's site.
		 */
		const SECRET_KEY = 'REPLACE_WITH_SECRET_KEY';

		/**
		 * Item reference — must match what's set in the license on the server.
		 */
		const ITEM_REFERENCE = 'Username Changer Pro';

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		private function hooks() {
			add_action( 'wp_ajax_uc_activate_license',   array( $this, 'ajax_activate' ) );
			add_action( 'wp_ajax_uc_deactivate_license', array( $this, 'ajax_deactivate' ) );
			add_action( 'admin_init',                    array( $this, 'maybe_check_license' ) );
			add_action( 'username_changer_license_form', array( $this, 'render_license_form' ) );
			add_filter( 'username_changer_is_pro_active', array( $this, 'filter_is_pro_active' ) );
		}

		public function get_key() {
			return get_option( self::OPTION_KEY, '' );
		}

		public function get_status() {
			return get_option( self::OPTION_STATUS, 'inactive' );
		}

		public function get_expiry() {
			return get_option( self::OPTION_EXPIRY, '' );
		}

		public function is_valid() {
			if ( $this->get_status() !== 'active' ) {
				return false;
			}
			$expiry = $this->get_expiry();
			if ( ! empty( $expiry ) && strtotime( $expiry ) < time() ) {
				update_option( self::OPTION_STATUS, 'expired' );
				return false;
			}
			return true;
		}

		public function filter_is_pro_active( $is_active ) {
			return $this->is_valid() ? true : $is_active;
		}

		public function activate( $key ) {
			$key = sanitize_text_field( $key );

			if ( empty( $key ) ) {
				return array(
					'success' => false,
					'message' => __( 'Please enter a license key.', 'username-changer' ),
				);
			}

			// DEV/TEST bypass — remove before production.
			if ( $key === 'UC-TEST-PRO-2026' ) {
				update_option( self::OPTION_KEY,    $key );
				update_option( self::OPTION_STATUS, 'active' );
				update_option( self::OPTION_EXPIRY, '2099-12-31' );
				delete_transient( 'uc_license_check' );
				return array(
					'success' => true,
					'message' => __( 'License activated successfully!', 'username-changer' ),
					'expires' => '2099-12-31',
				);
			}

			// Build SLM API request for activation.
			$api_params = array(
				'slm_action'         => 'slm_activate',
				'secret_key'         => self::SECRET_KEY,
				'license_key'        => $key,
				'registered_domain'  => wp_parse_url( home_url(), PHP_URL_HOST ),
				'item_reference'     => self::ITEM_REFERENCE,
			);

			$response = wp_remote_get(
				add_query_arg( $api_params, self::API_URL ),
				array( 'timeout' => 20, 'sslverify' => true )
			);

			if ( is_wp_error( $response ) ) {
				return array(
					'success' => false,
					'message' => $response->get_error_message(),
				);
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( isset( $body->result ) && 'success' === $body->result ) {
				$expiry = isset( $body->date_expiry ) ? sanitize_text_field( $body->date_expiry ) : '';
				update_option( self::OPTION_KEY,    $key );
				update_option( self::OPTION_STATUS, 'active' );
				update_option( self::OPTION_EXPIRY, $expiry );
				delete_transient( 'uc_license_check' );
				return array(
					'success' => true,
					'message' => __( 'License activated successfully!', 'username-changer' ),
					'expires' => $expiry,
				);
			}

			$msg = isset( $body->message ) ? sanitize_text_field( $body->message ) : __( 'Invalid license key. Please check and try again.', 'username-changer' );
			update_option( self::OPTION_STATUS, 'invalid' );
			return array( 'success' => false, 'message' => $msg );
		}

		public function deactivate() {
			$key = $this->get_key();

			if ( ! empty( $key ) ) {
				$api_params = array(
					'slm_action'        => 'slm_deactivate',
					'secret_key'        => self::SECRET_KEY,
					'license_key'       => $key,
					'registered_domain' => wp_parse_url( home_url(), PHP_URL_HOST ),
					'item_reference'    => self::ITEM_REFERENCE,
				);
				wp_remote_get(
					add_query_arg( $api_params, self::API_URL ),
					array( 'timeout' => 20, 'sslverify' => true )
				);
			}

			update_option( self::OPTION_STATUS, 'inactive' );
			delete_transient( 'uc_license_check' );
			return array(
				'success' => true,
				'message' => __( 'License deactivated.', 'username-changer' ),
			);
		}

		public function maybe_check_license() {
			if ( false !== get_transient( 'uc_license_check' ) ) {
				return;
			}
			if ( empty( $this->get_key() ) ) {
				return;
			}

			$api_params = array(
				'slm_action'  => 'slm_check',
				'secret_key'  => self::SECRET_KEY,
				'license_key' => $this->get_key(),
			);

			$response = wp_remote_get(
				add_query_arg( $api_params, self::API_URL ),
				array( 'timeout' => 20, 'sslverify' => true )
			);

			if ( ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ) );
				// SLM returns 'status' field: active, inactive, expired, blocked.
				if ( isset( $body->status ) ) {
					update_option( self::OPTION_STATUS, sanitize_text_field( $body->status ) );
					update_option( self::OPTION_EXPIRY, isset( $body->date_expiry ) ? sanitize_text_field( $body->date_expiry ) : '' );
				}
			}

			set_transient( 'uc_license_check', 1, DAY_IN_SECONDS );
		}

		public function ajax_activate() {
			check_ajax_referer( 'uc_license_nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json( array( 'success' => false, 'message' => __( 'Insufficient permissions.', 'username-changer' ) ) );
			}

			$key    = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
			$result = $this->activate( $key );
			wp_send_json( $result );
		}

		public function ajax_deactivate() {
			check_ajax_referer( 'uc_license_nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json( array( 'success' => false, 'message' => __( 'Insufficient permissions.', 'username-changer' ) ) );
			}

			$result = $this->deactivate();
			wp_send_json( $result );
		}

		public function render_license_form() {
			$status  = $this->get_status();
			$key     = $this->get_key();
			$expiry  = $this->get_expiry();
			$is_active = $this->is_valid();

			$status_labels = array(
				'active'   => array( 'label' => __( 'Active', 'username-changer' ),      'class' => 'uc-license-status--active' ),
				'inactive' => array( 'label' => __( 'Inactive', 'username-changer' ),    'class' => 'uc-license-status--inactive' ),
				'invalid'  => array( 'label' => __( 'Invalid', 'username-changer' ),     'class' => 'uc-license-status--invalid' ),
				'expired'  => array( 'label' => __( 'Expired', 'username-changer' ),     'class' => 'uc-license-status--expired' ),
			);

			$badge = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status_labels['inactive'];
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="uc-license-key"><?php esc_html_e( 'License Key', 'username-changer' ); ?></label>
				</th>
				<td class="forminp">
					<div class="uc-license-wrap">
						<input
							type="text"
							id="uc-license-key"
							class="regular-text"
							value="<?php echo esc_attr( $key ); ?>"
							placeholder="<?php esc_attr_e( 'Enter your license key', 'username-changer' ); ?>"
							<?php echo $is_active ? 'readonly' : ''; ?>
						/>

						<?php if ( $is_active ) : ?>
							<button type="button" id="uc-deactivate-license" class="button button-secondary">
								<?php esc_html_e( 'Deactivate', 'username-changer' ); ?>
							</button>
						<?php else : ?>
							<button type="button" id="uc-activate-license" class="button button-primary">
								<?php esc_html_e( 'Activate License', 'username-changer' ); ?>
							</button>
						<?php endif; ?>

						<span class="uc-license-status <?php echo esc_attr( $badge['class'] ); ?>">
							<?php echo esc_html( $badge['label'] ); ?>
						</span>

						<?php if ( ! empty( $expiry ) && $is_active ) : ?>
							<p class="description">
								<?php
								printf(
									/* translators: %s: expiration date */
									esc_html__( 'License expires: %s', 'username-changer' ),
									esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expiry ) ) )
								);
								?>
							</p>
						<?php endif; ?>

						<div id="uc-license-message" class="uc-license-message" style="display:none;"></div>

						<input type="hidden" id="uc-license-nonce" value="<?php echo esc_attr( wp_create_nonce( 'uc_license_nonce' ) ); ?>" />
					</div>
				</td>
			</tr>
			<?php
		}
	}
}

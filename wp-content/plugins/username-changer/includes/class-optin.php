<?php
/**
 * Opt-in / telemetry consent for Username Changer.
 *
 * Stores consent in wp_options under the key 'uc_optin'.
 * Value shape:
 *   {
 *     "status"    : "pending" | "yes" | "no",
 *     "timestamp" : <unix int>,
 *     "data"      : { "profile": bool, "diagnostic": bool, "extensions": bool }
 *   }
 *
 * @package UsernameChanger\Optin
 * @since   3.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UC_Optin' ) ) {

	class UC_Optin {

		const OPTION_KEY   = 'uc_optin';
		const ENDPOINT_URL = 'https://admin.trsplugins.com/wp-json/trsplugins/v1/optin';

		private static $instance;

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		private function hooks() {
			add_action( 'admin_notices',      array( $this, 'maybe_show_modal' ) );
			add_action( 'wp_ajax_uc_optin_save', array( $this, 'ajax_save' ) );
		}

		// ── Activation ────────────────────────────────────────────────────

		public function on_activation() {
			$current = get_option( self::OPTION_KEY );
			if ( $current && isset( $current['status'] ) && in_array( $current['status'], array( 'yes', 'no' ), true ) ) {
				return;
			}
			update_option( self::OPTION_KEY, array(
				'status'    => 'pending',
				'timestamp' => time(),
				'data'      => array(
					'profile'    => false,
					'diagnostic' => false,
					'extensions' => false,
				),
			), false );
		}

		// ── Getters ───────────────────────────────────────────────────────

		public function get() {
			return get_option( self::OPTION_KEY, array(
				'status'    => 'pending',
				'timestamp' => 0,
				'data'      => array(
					'profile'    => false,
					'diagnostic' => false,
					'extensions' => false,
				),
			) );
		}

		public function get_status() {
			$opt = $this->get();
			return isset( $opt['status'] ) ? $opt['status'] : 'pending';
		}

		public function is_allowed( $key ) {
			$opt = $this->get();
			return ! empty( $opt['data'][ $key ] );
		}

		// ── Modal ─────────────────────────────────────────────────────────

		public function maybe_show_modal() {
			if ( $this->get_status() !== 'pending' ) {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$this->render_modal();
		}

		private function render_modal() {
			$plugin_name = 'Username Changer';
			$nonce = wp_create_nonce( 'uc_optin_nonce' );
			?>
			<div id="uc-optin-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99999;display:flex;align-items:center;justify-content:center;">
				<div id="uc-optin-modal" style="background:#fff;border-radius:6px;width:500px;max-width:95vw;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.2);">

					<!-- Header -->
					<div style="padding:20px 24px 0;display:flex;justify-content:space-between;align-items:center;">
						<strong style="font-size:15px;letter-spacing:.2px;"><?php echo esc_html( sprintf( 'Opt-In for %s', $plugin_name ) ); ?></strong>
						<button id="uc-optin-close" style="background:none;border:none;cursor:pointer;font-size:20px;color:#999;line-height:1;" aria-label="Close">&times;</button>
					</div>				<p style="margin:10px 24px 0;font-size:13px;color:#555;">Choose which data you'd like to share with us. Opting in is voluntary. See our <a href="https://trsplugins.com/privacy-policy" target="_blank" rel="noopener noreferrer" style="color:#2196f3;text-decoration:none;">Privacy Policy</a>.</p>
					<!-- Body -->
					<div style="padding:16px 24px 0;">

						<!-- COMMUNICATION -->
						<p style="margin:0 0 8px;font-size:12px;font-weight:700;text-transform:uppercase;color:#333;letter-spacing:.5px;">COMMUNICATION</p>
						<div style="border:1px solid #e0e0e0;border-radius:4px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px;">
							<span style="font-size:22px;margin-top:2px;">👤</span>
							<div>
								<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
									<input type="checkbox" id="uc-optin-profile" checked style="width:16px;height:16px;">
									<strong>View Basic Profile Info</strong>
								</label>
								<p style="margin:4px 0 0;font-size:13px;color:#555;">Your WordPress user's: first &amp; last name, and email address</p>
							</div>
						</div>

						<!-- DIAGNOSTIC INFO -->
						<p style="margin:0 0 8px;font-size:12px;font-weight:700;text-transform:uppercase;color:#333;letter-spacing:.5px;">DIAGNOSTIC INFO</p>
						<div style="border:1px solid #e0e0e0;border-radius:4px;margin-bottom:16px;overflow:hidden;">
							<div style="padding:14px 16px;display:flex;align-items:flex-start;gap:12px;">
								<span style="font-size:22px;margin-top:2px;">🔗</span>
								<div>
									<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
										<input type="checkbox" id="uc-optin-diagnostic" checked style="width:16px;height:16px;">
										<strong>View Basic Website Info</strong>
									</label>
									<p style="margin:4px 0 0;font-size:13px;color:#555;">Homepage URL &amp; title, WP &amp; PHP versions, and site language</p>
								</div>
							</div>
							<div style="padding:14px 16px;border-top:1px solid #e0e0e0;display:flex;align-items:flex-start;gap:12px;">
								<span style="font-size:22px;margin-top:2px;">🔌</span>
								<div>
									<strong>View Basic Plugin Info</strong>
									<p style="margin:4px 0 0;font-size:13px;color:#555;">Current plugin &amp; SDK versions, and if active or uninstalled</p>
								</div>
							</div>
						</div>

						<!-- EXTENSIONS -->
						<p style="margin:0 0 8px;font-size:12px;font-weight:700;text-transform:uppercase;color:#333;letter-spacing:.5px;">EXTENSIONS</p>
						<div style="border:1px solid #e0e0e0;border-radius:4px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:flex-start;gap:12px;">
							<span style="font-size:22px;margin-top:2px;">📦</span>
							<div style="flex:1;">
								<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
									<input type="checkbox" id="uc-optin-extensions" style="width:16px;height:16px;">
									<strong>View Plugins &amp; Themes List</strong>
								</label>
								<p style="margin:4px 0 0;font-size:13px;color:#555;">Names, slugs, versions, and if active or not</p>
							</div>
						</div>
					</div>

					<!-- Footer -->
					<div style="padding:0 24px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #f0f0f0;padding-top:16px;">
						<button id="uc-optin-skip" style="padding:8px 16px;background:#d63638;color:#fff;border:1px solid #d63638;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">
							Skip
						</button>
						<button id="uc-optin-allow" style="padding:8px 20px;background:#2e7d32;color:#fff;border:1px solid #2e7d32;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">
							Allow
						</button>
					</div>

				</div>
			</div>

			<script>
			(function(){
				function save(status, data) {
					var fd = new FormData();
					fd.append('action', 'uc_optin_save');
					fd.append('nonce',  '<?php echo esc_js( $nonce ); ?>');
					fd.append('status', status);
					fd.append('profile',    data.profile    ? '1' : '0');
					fd.append('diagnostic', data.diagnostic ? '1' : '0');
					fd.append('extensions', data.extensions ? '1' : '0');
					fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method:'POST', body:fd });
					document.getElementById('uc-optin-overlay').remove();
				}

				document.getElementById('uc-optin-allow').addEventListener('click', function(){
					save('yes', {
						profile:    document.getElementById('uc-optin-profile').checked,
						diagnostic: document.getElementById('uc-optin-diagnostic').checked,
						extensions: document.getElementById('uc-optin-extensions').checked,
					});
				});

				document.getElementById('uc-optin-skip').addEventListener('click', function(){
					save('no', { profile:false, diagnostic:false, extensions:false });
				});

				document.getElementById('uc-optin-close').addEventListener('click', function(){
					save('no', { profile:false, diagnostic:false, extensions:false });
				});
			})();
			</script>
			<?php
		}

		// ── AJAX ──────────────────────────────────────────────────────────

		public function ajax_save() {
			check_ajax_referer( 'uc_optin_nonce', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Unauthorized', 403 );
			}

			$status = isset( $_POST['status'] ) && $_POST['status'] === 'yes' ? 'yes' : 'no';

			$data = array(
				'profile'    => ! empty( $_POST['profile'] )    && '1' === $_POST['profile'],
				'diagnostic' => ! empty( $_POST['diagnostic'] ) && '1' === $_POST['diagnostic'],
				'extensions' => ! empty( $_POST['extensions'] ) && '1' === $_POST['extensions'],
			);

			update_option( self::OPTION_KEY, array(
				'status'    => $status,
				'timestamp' => time(),
				'data'      => $data,
			), false );

			if ( 'yes' === $status ) {
				$this->send_data( $data );
			}

			wp_send_json_success();
		}

		// ── Remote send ───────────────────────────────────────────────────

		private function send_data( $consented ) {
			$payload = array(
				'plugin'     => 'username-changer',
				'version'    => defined( 'USERNAME_CHANGER_VER' ) ? USERNAME_CHANGER_VER : '',
				'profile'    => ! empty( $consented['profile'] ),
				'diagnostic' => ! empty( $consented['diagnostic'] ),
				'extensions' => ! empty( $consented['extensions'] ),
				'created_at' => gmdate( 'c' ),
			);

			if ( ! empty( $consented['profile'] ) ) {
				$user = wp_get_current_user();
				$payload['first_name'] = $user->first_name;
				$payload['last_name']  = $user->last_name;
				$payload['email']      = $user->user_email;
			}

			if ( ! empty( $consented['diagnostic'] ) ) {
				$payload['site_url']      = home_url();
				$payload['site_name']     = get_bloginfo( 'name' );
				$payload['wp_version']    = get_bloginfo( 'version' );
				$payload['php_version']   = PHP_VERSION;
				$payload['site_language'] = get_bloginfo( 'language' );
			}

			if ( ! empty( $consented['extensions'] ) ) {
				if ( ! function_exists( 'get_plugins' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				$all    = get_plugins();
				$active = get_option( 'active_plugins', array() );
				$list   = array();
				foreach ( $all as $file => $info ) {
					$list[] = array(
						'name'    => $info['Name'],
						'slug'    => dirname( $file ),
						'version' => $info['Version'],
						'active'  => in_array( $file, $active, true ),
					);
				}
				$payload['plugins'] = $list;
				$payload['theme']   = array(
					'name'    => wp_get_theme()->get( 'Name' ),
					'version' => wp_get_theme()->get( 'Version' ),
					'active'  => true,
				);
			}

			wp_remote_post( self::ENDPOINT_URL, array(
				'timeout'     => 8,
				'blocking'    => false,
				'body'        => wp_json_encode( $payload ),
				'headers'     => array( 'Content-Type' => 'application/json' ),
				'data_format' => 'body',
			) );
		}
	}
}

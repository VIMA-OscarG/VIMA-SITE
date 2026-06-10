<?php
/**
 * Audit Log
 *
 * @package     UsernameChanger\Admin\AuditLog
 * @since       4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Username_Changer_Audit_Log' ) ) {

	class Username_Changer_Audit_Log {

		private static $instance;

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		private function hooks() {
			add_action( 'username_changer_after_process', array( $this, 'log_change' ), 10, 2 );
			add_action( 'admin_menu',                     array( $this, 'add_menu_page' ) );
			add_action( 'wp_ajax_uc_export_audit_log',    array( $this, 'export_csv' ) );
		}

		public static function get_table_name() {
			global $wpdb;
			return $wpdb->prefix . 'uc_audit_log';
		}

		public static function create_table() {
			global $wpdb;

			$table   = self::get_table_name();
			$charset = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$table} (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				changed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				changed_by_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				changed_by_login varchar(60) NOT NULL DEFAULT '',
				old_username varchar(60) NOT NULL DEFAULT '',
				new_username varchar(60) NOT NULL DEFAULT '',
				ip_address varchar(45) NOT NULL DEFAULT '',
				status varchar(20) NOT NULL DEFAULT 'success',
				PRIMARY KEY (id),
				KEY changed_at (changed_at),
				KEY status (status)
			) {$charset};";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		public function log_change( $old_username, $new_username, $log_status = 'success' ) {
			global $wpdb;

			$current_user = wp_get_current_user();
			$ip           = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				self::get_table_name(),
				array(
					'changed_at'       => current_time( 'mysql' ),
					'changed_by_id'    => (int) $current_user->ID,
					'changed_by_login' => $current_user->user_login,
					'old_username'     => $old_username,
					'new_username'     => $new_username,
					'ip_address'       => $ip,
					'status'           => $log_status,
				),
				array( '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
			);
		}

		public function get_logs( $args = array() ) {
			global $wpdb;

			$defaults = array(
				'per_page' => 20,
				'page'     => 1,
				'search'   => '',
				'orderby'  => 'changed_at',
				'order'    => 'DESC',
			);
			$args = wp_parse_args( $args, $defaults );

			$table  = self::get_table_name();
			$offset = ( absint( $args['page'] ) - 1 ) * absint( $args['per_page'] );

			$allowed_orderby = array( 'changed_at', 'old_username', 'new_username', 'changed_by_login', 'status' );
			$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'changed_at';
			$order           = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

			if ( ! empty( $args['search'] ) ) {
				$s = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
				return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"SELECT * FROM {$table} WHERE old_username LIKE %s OR new_username LIKE %s OR changed_by_login LIKE %s ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$s, $s, $s,
						absint( $args['per_page'] ),
						$offset
					)
				);
			}

			return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $args['per_page'] ),
					$offset
				)
			);
		}

		public function get_total( $search = '' ) {
			global $wpdb;

			$table = self::get_table_name();

			if ( ! empty( $search ) ) {
				$s = '%' . $wpdb->esc_like( sanitize_text_field( $search ) ) . '%';
				return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$table} WHERE old_username LIKE %s OR new_username LIKE %s OR changed_by_login LIKE %s",
						$s, $s, $s
					)
				);
			}

			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		}

		public function add_menu_page() {
			if ( ! username_changer_is_pro_active() ) {
				return;
			}
			add_submenu_page(
				'options-general.php',
				__( 'Username Change Log', 'username-changer' ),
				__( 'Username Change Log', 'username-changer' ),
				'manage_options',
				'uc-audit-log',
				array( $this, 'render_page' )
			);
		}

		public function export_csv() {
			check_ajax_referer( 'uc_export_audit_log', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions.', 'username-changer' ) );
			}

			if ( ! username_changer_is_pro_active() ) {
				wp_die( esc_html__( 'Pro license required.', 'username-changer' ) );
			}

			$logs = $this->get_logs( array( 'per_page' => 9999, 'page' => 1 ) );

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=uc-audit-log-' . gmdate( 'Y-m-d' ) . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			fputcsv( $output, array( 'ID', 'Date', 'Changed By', 'Old Username', 'New Username', 'IP Address', 'Status' ) );

			foreach ( $logs as $log ) {
				fputcsv( $output, array(
					$log->id,
					$log->changed_at,
					$log->changed_by_login,
					$log->old_username,
					$log->new_username,
					$log->ip_address,
					$log->status,
				) );
			}

			fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			exit;
		}

		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'username-changer' ) );
			}

			if ( ! username_changer_is_pro_active() ) {
				wp_die( esc_html__( 'A valid Pro license is required to use this feature.', 'username-changer' ) );
			}

			$search  = isset( $_GET['uc_search'] ) ? sanitize_text_field( wp_unslash( $_GET['uc_search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$paged   = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
			$per_page = 20;

			$logs  = $this->get_logs( array(
				'per_page' => $per_page,
				'page'     => $paged,
				'search'   => $search,
			) );
			$total = $this->get_total( $search );
			$pages = ceil( $total / $per_page );

			$export_nonce = wp_create_nonce( 'uc_export_audit_log' );
			?>
			<div class="wrap uc-audit-log-wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Username Change Log', 'username-changer' ); ?></h1>

				<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=uc_export_audit_log&security=' . $export_nonce ) ); ?>" class="page-title-action">
					<?php esc_html_e( 'Export CSV', 'username-changer' ); ?>
				</a>

				<hr class="wp-header-end">

				<form method="get" action="">
					<input type="hidden" name="page" value="uc-audit-log">
					<p class="search-box">
						<input type="search" name="uc_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search logs...', 'username-changer' ); ?>">
						<button type="submit" class="button"><?php esc_html_e( 'Search', 'username-changer' ); ?></button>
					</p>
				</form>

				<table class="wp-list-table widefat fixed striped uc-audit-log-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'username-changer' ); ?></th>
							<th><?php esc_html_e( 'Changed By', 'username-changer' ); ?></th>
							<th><?php esc_html_e( 'Old Username', 'username-changer' ); ?></th>
							<th><?php esc_html_e( 'New Username', 'username-changer' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'username-changer' ); ?></th>
							<th><?php esc_html_e( 'Status', 'username-changer' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $logs ) ) : ?>
							<?php foreach ( $logs as $log ) : ?>
								<tr>
									<td><?php echo esc_html( $log->changed_at ); ?></td>
									<td><?php echo esc_html( $log->changed_by_login ); ?></td>
									<td><?php echo esc_html( $log->old_username ); ?></td>
									<td><?php echo esc_html( $log->new_username ); ?></td>
									<td><?php echo esc_html( $log->ip_address ); ?></td>
									<td>
										<span class="uc-log-status uc-log-status--<?php echo esc_attr( $log->status ); ?>">
											<?php echo esc_html( ucfirst( $log->status ) ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="6"><?php esc_html_e( 'No log entries found.', 'username-changer' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $pages > 1 ) : ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<?php
							echo wp_kses_post( paginate_links( array(
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '',
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
								'total'   => $pages,
								'current' => $paged,
							) ) );
							?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}

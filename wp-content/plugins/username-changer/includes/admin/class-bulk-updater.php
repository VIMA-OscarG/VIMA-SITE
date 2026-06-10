<?php
/**
 * Bulk Username Updater
 *
 * @package     UsernameChanger\Admin\BulkUpdater
 * @since       4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Username_Changer_Bulk_Updater' ) ) {

	class Username_Changer_Bulk_Updater {

		private static $instance;

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		private function hooks() {
			add_action( 'admin_menu',                      array( $this, 'add_menu_page' ) );
			add_action( 'wp_ajax_uc_bulk_update',          array( $this, 'ajax_bulk_update' ) );
			add_action( 'wp_ajax_uc_export_users_csv',     array( $this, 'export_users_csv' ) );
		}

		public function add_menu_page() {
			if ( ! username_changer_is_pro_active() ) {
				return;
			}
			add_submenu_page(
				'options-general.php',
				__( 'Bulk Username Updater', 'username-changer' ),
				__( 'Bulk Username Updater', 'username-changer' ),
				'manage_options',
				'uc-bulk-updater',
				array( $this, 'render_page' )
			);
		}

		public function ajax_bulk_update() {
			check_ajax_referer( 'uc_bulk_update', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Insufficient permissions.', 'username-changer' ) );
			}

			if ( ! username_changer_is_pro_active() ) {
				wp_send_json_error( __( 'Pro license required.', 'username-changer' ) );
			}

			$raw_updates = isset( $_POST['updates'] ) ? wp_unslash( $_POST['updates'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			if ( ! is_array( $raw_updates ) || empty( $raw_updates ) ) {
				wp_send_json_error( __( 'No updates provided.', 'username-changer' ) );
			}

			// Enforce max 100 per request.
			$raw_updates = array_slice( $raw_updates, 0, 100, true );

			$results = array();

			foreach ( $raw_updates as $old => $new ) {
				$old = sanitize_user( (string) $old );
				$new = sanitize_user( (string) $new );

				if ( empty( $new ) || $old === $new ) {
					continue;
				}

				if ( ! validate_username( $new ) ) {
					$results[] = array(
						'old'     => $old,
						'new'     => $new,
						'success' => false,
						'message' => __( 'Invalid characters in username.', 'username-changer' ),
					);
					continue;
				}

				$illegal = array_map( 'strtolower', (array) apply_filters( 'illegal_user_logins', array() ) );
				if ( in_array( strtolower( $new ), $illegal, true ) ) {
					$results[] = array(
						'old'     => $old,
						'new'     => $new,
						'success' => false,
						'message' => __( 'That username is not allowed.', 'username-changer' ),
					);
					continue;
				}

				if ( username_exists( $new ) ) {
					$results[] = array(
						'old'     => $old,
						'new'     => $new,
						'success' => false,
						'message' => __( 'Username already exists.', 'username-changer' ),
					);
					continue;
				}

				$success   = username_changer_process( $old, $new );
				$results[] = array(
					'old'     => $old,
					'new'     => $new,
					'success' => $success,
					'message' => $success ? '' : __( 'Failed to update username.', 'username-changer' ),
				);
			}

			wp_send_json_success( $results );
		}

		public function export_users_csv() {
			check_ajax_referer( 'uc_export_users', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions.', 'username-changer' ) );
			}

			if ( ! username_changer_is_pro_active() ) {
				wp_die( esc_html__( 'Pro license required.', 'username-changer' ) );
			}

			$users = get_users( array( 'number' => 9999 ) );

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=users-' . gmdate( 'Y-m-d' ) . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			fputcsv( $output, array( 'old_username', 'new_username' ) );

			foreach ( $users as $user ) {
				fputcsv( $output, array( $user->user_login, '' ) );
			}

			fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			exit;
		}

		public function handle_csv_import( $file ) {
			if ( ! isset( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
				return new WP_Error( 'invalid_file', __( 'Invalid file upload.', 'username-changer' ) );
			}

			$handle = fopen( $file['tmp_name'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( ! $handle ) {
				return new WP_Error( 'read_error', __( 'Could not read uploaded file.', 'username-changer' ) );
			}

			$updates = array();
			$row_num = 0;

			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				$row_num++;

				// Skip header row.
				if ( 1 === $row_num && isset( $row[0] ) && strtolower( trim( $row[0] ) ) === 'old_username' ) {
					continue;
				}

				if ( empty( $row[0] ) || empty( $row[1] ) ) {
					continue;
				}

				$old = sanitize_user( trim( $row[0] ) );
				$new = sanitize_user( trim( $row[1] ) );

				if ( ! empty( $old ) && ! empty( $new ) ) {
					$updates[ $old ] = $new;
				}
			}

			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

			return $updates;
		}

		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'username-changer' ) );
			}

			if ( ! username_changer_is_pro_active() ) {
				wp_die( esc_html__( 'A valid Pro license is required to use this feature.', 'username-changer' ) );
			}

			// Handle CSV import form submission.
			$import_results = array();
			if ( isset( $_POST['uc_import_csv_nonce'] ) ) {
				check_admin_referer( 'uc_import_csv', 'uc_import_csv_nonce' );

				if ( ! empty( $_FILES['uc_csv_file']['name'] ) ) {
					$updates = $this->handle_csv_import( $_FILES['uc_csv_file'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

					if ( is_wp_error( $updates ) ) {
						$import_results['error'] = $updates->get_error_message();
					} else {
						foreach ( $updates as $old => $new ) {
							$old = sanitize_user( $old );
							$new = sanitize_user( $new );

							if ( ! validate_username( $new ) ) {
								$import_results[] = array( 'old' => $old, 'new' => $new, 'success' => false, 'message' => __( 'Invalid characters.', 'username-changer' ) );
								continue;
							}

							if ( username_exists( $new ) ) {
								$import_results[] = array( 'old' => $old, 'new' => $new, 'success' => false, 'message' => __( 'Already exists.', 'username-changer' ) );
								continue;
							}

							$success          = username_changer_process( $old, $new );
							$import_results[] = array( 'old' => $old, 'new' => $new, 'success' => $success, 'message' => $success ? __( 'Updated.', 'username-changer' ) : __( 'Failed.', 'username-changer' ) );
						}
					}
				}
			}

			$paged    = isset( $_GET['ucbu_page'] ) ? absint( $_GET['ucbu_page'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
			$per_page = 50;
			$users    = get_users( array(
				'number' => $per_page,
				'offset' => ( $paged - 1 ) * $per_page,
			) );
			$total    = (int) count_users()['total_users'];
			$pages    = ceil( $total / $per_page );

			$export_nonce = wp_create_nonce( 'uc_export_users' );
			$bulk_nonce   = wp_create_nonce( 'uc_bulk_update' );
			?>
			<div class="wrap uc-bulk-updater-wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Bulk Username Updater', 'username-changer' ); ?></h1>
				<hr class="wp-header-end">

				<?php if ( ! empty( $import_results ) && ! isset( $import_results['error'] ) ) : ?>
					<div class="notice notice-success is-dismissible">
						<p><?php
							$success_count = count( array_filter( $import_results, function( $r ) { return $r['success']; } ) );
							$fail_count    = count( $import_results ) - $success_count;
							printf(
								/* translators: 1: number of successes, 2: number of failures */
								esc_html__( 'CSV import complete: %1$d updated, %2$d failed.', 'username-changer' ),
								(int) $success_count,
								(int) $fail_count
							);
						?></p>
					</div>
				<?php elseif ( isset( $import_results['error'] ) ) : ?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo esc_html( $import_results['error'] ); ?></p>
					</div>
				<?php endif; ?>

				<!-- CSV Import -->
				<div class="uc-card">
					<h2><?php esc_html_e( 'Import from CSV', 'username-changer' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Upload a CSV file with columns: old_username, new_username', 'username-changer' ); ?></p>
					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'uc_import_csv', 'uc_import_csv_nonce' ); ?>
						<input type="file" name="uc_csv_file" accept=".csv" required>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Import CSV', 'username-changer' ); ?></button>
						<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=uc_export_users_csv&security=' . $export_nonce ) ); ?>" class="button">
							<?php esc_html_e( 'Download User List (CSV)', 'username-changer' ); ?>
						</a>
					</form>
				</div>

				<!-- CSV Import Results -->
				<?php if ( ! empty( $import_results ) && ! isset( $import_results['error'] ) ) : ?>
					<div class="uc-card">
						<h3><?php esc_html_e( 'Import Results', 'username-changer' ); ?></h3>
						<table class="wp-list-table widefat fixed striped" style="max-width:600px">
							<thead><tr><th><?php esc_html_e( 'Old', 'username-changer' ); ?></th><th><?php esc_html_e( 'New', 'username-changer' ); ?></th><th><?php esc_html_e( 'Result', 'username-changer' ); ?></th></tr></thead>
							<tbody>
								<?php foreach ( $import_results as $r ) : ?>
									<tr>
										<td><?php echo esc_html( $r['old'] ); ?></td>
										<td><?php echo esc_html( $r['new'] ); ?></td>
										<td class="<?php echo $r['success'] ? 'uc-result-success' : 'uc-result-fail'; ?>">
											<?php echo $r['success'] ? '&#10003; ' : '&#10007; '; echo esc_html( $r['message'] ); ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<!-- Inline Bulk Edit -->
				<div class="uc-card">
					<h2><?php esc_html_e( 'Inline Bulk Edit', 'username-changer' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Enter new usernames for selected users and click "Update Selected". Leave blank to skip.', 'username-changer' ); ?></p>

					<div id="uc-bulk-results"></div>

					<form id="uc-bulk-update-form">
						<input type="hidden" id="uc-bulk-nonce" value="<?php echo esc_attr( $bulk_nonce ); ?>">

						<div style="margin-bottom:10px;">
							<label>
								<input type="checkbox" id="uc-select-all">
								<?php esc_html_e( 'Select All', 'username-changer' ); ?>
							</label>
						</div>

						<table class="wp-list-table widefat fixed striped uc-bulk-table">
							<thead>
								<tr>
									<th style="width:30px;"></th>
									<th><?php esc_html_e( 'Current Username', 'username-changer' ); ?></th>
									<th><?php esc_html_e( 'Display Name', 'username-changer' ); ?></th>
									<th><?php esc_html_e( 'Role', 'username-changer' ); ?></th>
									<th><?php esc_html_e( 'New Username', 'username-changer' ); ?></th>
									<th><?php esc_html_e( 'Status', 'username-changer' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $users as $user ) : ?>
									<tr data-user-login="<?php echo esc_attr( $user->user_login ); ?>">
										<td><input type="checkbox" class="uc-user-check" value="<?php echo esc_attr( $user->user_login ); ?>"></td>
										<td class="uc-current-username"><?php echo esc_html( $user->user_login ); ?></td>
										<td><?php echo esc_html( $user->display_name ); ?></td>
										<td><?php echo esc_html( implode( ', ', $user->roles ) ); ?></td>
										<td>
											<input
												type="text"
												class="uc-new-username regular-text"
												data-old="<?php echo esc_attr( $user->user_login ); ?>"
												placeholder="<?php echo esc_attr( $user->user_login ); ?>"
												autocomplete="off"
											>
										</td>
										<td class="uc-row-status"></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<?php if ( $pages > 1 ) : ?>
							<div style="margin-top:10px;">
								<?php
								echo wp_kses_post( paginate_links( array(
									'base'      => add_query_arg( 'ucbu_page', '%#%' ),
									'format'    => '',
									'prev_text' => '&laquo;',
									'next_text' => '&raquo;',
									'total'     => $pages,
									'current'   => $paged,
								) ) );
								?>
							</div>
						<?php endif; ?>

						<p style="margin-top:15px;">
							<button type="button" id="uc-bulk-update-btn" class="button button-primary">
								<?php esc_html_e( 'Update Selected Usernames', 'username-changer' ); ?>
							</button>
						</p>
					</form>
				</div>
			</div>
			<?php
		}
	}
}

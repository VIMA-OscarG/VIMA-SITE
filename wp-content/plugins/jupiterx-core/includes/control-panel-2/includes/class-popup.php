<?php
defined( 'ABSPATH' ) || die();
/**
 * Handles popup functionality in control panel.
 *
 * @package JupiterX_Core\Control_Panel_2\Popup
 *
 * @since 3.7.0
 */
use Elementor\Plugin;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class JupiterX_Core_Control_Panel_Popup {
	private static $instance = null;

	/**
	 * Instance of class
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_jupiterx_add_new_popup', [ $this, 'ajax_handler' ] );
		add_action( 'wp_ajax_jupiterx_popups', [ $this, 'handle_ajax' ] );
		add_action( 'wp_ajax_jupiterx_popups_get_posts', [ $this, 'get_posts' ] );
		add_action( 'wp_ajax_jupiterx_get_import_form', [ $this, 'import_form' ] );
		add_action( 'admin_action_import_popup_action', [ $this, 'import_popup_templates' ] );
		add_action( 'wp_ajax_enable_unfiltered_files_upload', [ $this, 'enable_unfiltered_files_upload' ] );

		// Conditions ajax requests.
		add_action( 'wp_ajax_jupiterx_popup_get_options', [ $this, 'get_options' ] );
		add_action( 'wp_ajax_jupiterx_popup_save_conditions_triggers', [ $this, 'save_conditions_triggers' ] );
		add_action( 'wp_ajax_jupiterx_popup_get_conditions', [ $this, 'get_popup_conditions' ] );
		add_action( 'wp_ajax_jupiterx_popup_get_triggers', [ $this, 'get_popup_triggers' ] );
	}

	/**
	 * Handle generic popup ajax requests.
	 *
	 * @return void
	 */
	public function handle_ajax() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$action = filter_input( INPUT_POST, 'sub_action', FILTER_UNSAFE_RAW );

		if ( ! empty( $action ) && method_exists( $this, $action ) ) {
			call_user_func( [ $this, $action ] );
		}
	}

	/**
	 * Get popup posts for the control panel list.
	 *
	 * @return void
	 */
	public function get_posts() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );
		$paged = $paged ? (int) $paged : 1;

		$filter_value = isset( $_GET['filter_value'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_value'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! in_array( $filter_value, [ 'all', 'publish', 'draft', 'trash' ], true ) ) {
			$filter_value = 'all';
		}

		switch ( $filter_value ) {
			case 'publish':
				$post_statuses = [ 'publish' ];
				break;
			case 'draft':
				$post_statuses = [ 'draft', 'private' ];
				break;
			case 'trash':
				$post_statuses = 'trash';
				break;
			default:
				$post_statuses = [ 'publish', 'draft', 'private' ];
				break;
		}

		$query = new \WP_Query(
			[
				'post_type'      => 'jupiterx-popups',
				'post_status'    => $post_statuses,
				'paged'          => $paged,
				'posts_per_page' => 20,
			]
		);

		$trash_count_query = new \WP_Query(
			[
				'post_type'      => 'jupiterx-popups',
				'post_status'    => 'trash',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => false,
			]
		);
		$trash_total = (int) $trash_count_query->found_posts;

		$posts = $query->posts;

		$columns = [
			'labels' => [
				esc_html__( 'Author', 'jupiterx-core' ),
				esc_html__( 'Status', 'jupiterx-core' ),
				esc_html__( 'Created on', 'jupiterx-core' ),
			],
			'values' => [ '' ],
		];

		$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		foreach ( $posts as $post ) {
			$conditions = get_post_meta( $post->ID, '_jupiterx_popup_conditions', true );
			$triggers   = get_post_meta( $post->ID, '_jupiterx_popup_triggers', true );
			$document   = Plugin::$instance->documents->get( $post->ID );

			$columns['values'][ "post_{$post->ID}" ] = [
				get_the_author_meta( 'user_login', (int) get_post_field( 'post_author', $post->ID ) ),
				'trash' === $post->post_status ? 'trash' : ucfirst( $post->post_status ),
				get_the_time( 'Y-m-d', $post->ID ),
			];

			$post->user_url = get_edit_user_link( (int) get_the_author_meta( 'ID', (int) get_post_field( 'post_author', $post->ID ) ) );
			if ( $document ) {
				$post->edit_url = $document->get_edit_url();
			} else {
				$post->edit_url = add_query_arg(
					[
						'post'   => $post->ID,
						'action' => 'elementor',
					],
					admin_url( 'post.php' )
				);
			}
			$preview_url = get_preview_post_link( $post );
			$preview_url = $preview_url ? $preview_url : get_permalink( $post->ID );
			$post->preview_url = add_query_arg(
				[
					'preview-id' => $post->ID,
				],
				$preview_url
			);
			$post->author_name = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post->ID ) );
			$post->custom_date = get_the_date( 'M d, Y', $post->ID );
			$post->custom_modified_date = get_the_modified_date( 'M d, Y', $post->ID );
			$post->custom_date_with_time  = mysql2date( $date_time_format, $post->post_date );
			$post->custom_modified_date_with_time = mysql2date( $date_time_format, $post->post_modified );
			$post->condition_items = $this->get_condition_items( $conditions );
			$post->trigger_items   = $this->get_trigger_items( $triggers );
			$post->conditions_count = is_array( $conditions ) ? count( array_filter( $conditions ) ) : 0;
			$post->triggers_count   = is_array( $triggers ) ? count( array_filter( $triggers ) ) : 0;
			$post->conditions_label = 0 < $post->conditions_count
				? sprintf(
					/* translators: %d: number of conditions */
					_n( '%d condition', '%d conditions', $post->conditions_count, 'jupiterx-core' ),
					$post->conditions_count
				)
				: esc_html__( 'No display conditions', 'jupiterx-core' );
			$post->triggers_label = 0 < $post->triggers_count
				? sprintf(
					/* translators: %d: number of triggers */
					_n( '%d trigger', '%d triggers', $post->triggers_count, 'jupiterx-core' ),
					$post->triggers_count
				)
				: esc_html__( 'No triggers', 'jupiterx-core' );
			$post->export_url = add_query_arg(
				[
					'action'      => 'jupiterx_export_popup',
					'template_id' => $post->ID,
					'nonce'       => wp_create_nonce( 'jupiterx_export_popup' ),
				],
				admin_url( 'admin.php' )
			);
		}

		wp_send_json_success(
			[
				'posts'         => $posts,
				'max_num_pages' => (int) $query->max_num_pages,
				'columns'       => $columns,
				'counts'        => [
					'trash' => $trash_total,
				],
			]
		);
	}

	/**
	 * Handle unfiltered files upload.
	 *
	 * @return void
	 * @since 3.7.0
	 */
	public function enable_unfiltered_files_upload() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		update_option( 'elementor_unfiltered_files_upload', 1 );

		wp_send_json_success();
	}

	/**
	 * Build friendly condition labels for the popup tooltip.
	 *
	 * @param mixed $conditions Popup conditions.
	 * @return array
	 */
	private function get_condition_items( $conditions ) {
		if ( ! is_array( $conditions ) || ! class_exists( 'JupiterX_Popups_Conditions_Manager' ) ) {
			return [];
		}

		$items = [];

		foreach ( $conditions as $condition ) {
			if ( ! is_array( $condition ) || empty( $condition['name'] ) || 'exclude' === ( $condition['type'] ?? '' ) ) {
				continue;
			}

			$condition_name = ! empty( $condition['sub_name'] ) ? $condition['sub_name'] : $condition['name'];
			$condition_obj  = JupiterX_Popups_Conditions_Manager::$conditions[ $condition_name ] ?? null;

			if ( empty( $condition_obj ) ) {
				$items[] = $this->humanize_key( $condition_name );
				continue;
			}

			if ( isset( $condition['sub_id'] ) && is_array( $condition['sub_id'] ) ) {
				$items[] = sprintf(
					'%1$s: %2$s',
					$condition_obj->get_label(),
					$condition['sub_id']['label'] ?? $condition['sub_id']['name'] ?? $condition['sub_id']['value'] ?? ''
				);
				continue;
			}

			if ( isset( $condition['sub_id'] ) && 'all' === $condition['sub_id'] ) {
				$items[] = $condition_obj->get_label();
				continue;
			}

			$items[] = $condition_obj->get_all_label();
		}

		return array_values( array_filter( array_unique( $items ) ) );
	}

	/**
	 * Build friendly trigger labels for the popup tooltip.
	 *
	 * @param mixed $triggers Popup triggers.
	 * @return array
	 */
	private function get_trigger_items( $triggers ) {
		if ( ! is_array( $triggers ) || ! class_exists( 'JupiterX_Popups_Triggers_Manager' ) ) {
			return [];
		}

		$rule_count = 0;

		foreach ( $triggers as $trigger ) {
			if ( is_array( $trigger ) && ! empty( $trigger['name'] ) && 'relation_logic' !== $trigger['name'] ) {
				$rule_count++;
			}
		}

		$items = [];

		foreach ( $triggers as $trigger ) {
			if ( ! is_array( $trigger ) || empty( $trigger['name'] ) ) {
				continue;
			}

			if ( 'relation_logic' === $trigger['name'] ) {
				if ( $rule_count < 2 ) {
					continue;
				}

				$items[] = 'and' === ( $trigger['control'] ?? '' )
					? esc_html__( 'All trigger rules must match', 'jupiterx-core' )
					: esc_html__( 'Any trigger rule can match', 'jupiterx-core' );
				continue;
			}

			$trigger_obj   = JupiterX_Popups_Triggers_Manager::$triggers[ $trigger['name'] ] ?? null;
			$trigger_label = $trigger_obj ? $trigger_obj->get_label() : $this->humanize_key( $trigger['name'] );
			$control_type  = $this->get_trigger_control_type( $trigger_obj );
			$operator      = '';
			$value         = $this->format_trigger_value( $trigger['control'] ?? null, $control_type );

			if ( ! empty( $trigger['operator'] ) && ! empty( JupiterX_Popups_Triggers_Manager::$control_panel['operators'][ $trigger['operator'] ] ) ) {
				$operator = strtolower( JupiterX_Popups_Triggers_Manager::$control_panel['operators'][ $trigger['operator'] ] );
			}

			if ( 'on_page_load' === $trigger['name'] ) {
				$items[] = empty( $value ) || '0' === $value
					? esc_html__( 'Show on page load immediately', 'jupiterx-core' )
					: sprintf(
						/* translators: %s: trigger delay */
						esc_html__( 'Show on page load after %s seconds', 'jupiterx-core' ),
						$value
					);
				continue;
			}

			$line = $trigger_label;

			if ( ! empty( $operator ) ) {
				$line .= ' ' . $operator;
			}

			if ( ! empty( $value ) ) {
				$line .= ' ' . $value;
			}

			$items[] = trim( $line );
		}

		return array_values( array_filter( array_unique( $items ) ) );
	}

	/**
	 * Retrieve the control type declared for a trigger.
	 *
	 * @param object|null $trigger_obj Trigger instance.
	 * @return string
	 */
	private function get_trigger_control_type( $trigger_obj ) {
		if ( ! is_object( $trigger_obj ) || ! method_exists( $trigger_obj, 'add_control' ) ) {
			return '';
		}

		$control = $trigger_obj->add_control();

		if ( is_array( $control ) && ! empty( $control['type'] ) ) {
			return (string) $control['type'];
		}

		return '';
	}

	/**
	 * Convert trigger values to readable labels.
	 *
	 * @param mixed  $value        Trigger control value.
	 * @param string $control_type Optional trigger control type (e.g. 'date', 'date-range').
	 * @return string
	 */
	private function format_trigger_value( $value, $control_type = '' ) {
		if ( 'date-range' === $control_type && is_array( $value ) ) {
			$start = isset( $value['start_date'] ) ? $this->format_date_value( $value['start_date'] ) : '';
			$end   = isset( $value['end_date'] ) ? $this->format_date_value( $value['end_date'] ) : '';

			if ( '' !== $start && '' !== $end ) {
				return sprintf(
					/* translators: 1: start date, 2: end date */
					esc_html__( '%1$s to %2$s', 'jupiterx-core' ),
					$start,
					$end
				);
			}

			return trim( $start . ' ' . $end );
		}

		if ( 'date' === $control_type ) {
			return $this->format_date_value( $value );
		}

		if ( is_array( $value ) ) {
			$items = [];

			foreach ( $value as $item ) {
				$formatted = $this->format_trigger_value( $item );

				if ( ! empty( $formatted ) ) {
					$items[] = $formatted;
				}
			}

			return implode( ', ', array_unique( $items ) );
		}

		if ( is_object( $value ) ) {
			$value = (array) $value;
		}

		if ( is_array( $value ) ) {
			foreach ( [ 'label', 'name', 'title', 'value', 'id' ] as $key ) {
				if ( ! empty( $value[ $key ] ) ) {
					return $this->humanize_key( (string) $value[ $key ] );
				}
			}

			return '';
		}

		if ( is_bool( $value ) ) {
			return $value ? esc_html__( 'Yes', 'jupiterx-core' ) : esc_html__( 'No', 'jupiterx-core' );
		}

		return $this->humanize_key( (string) $value );
	}

	/**
	 * Format a unix timestamp into a readable date using the site date format.
	 *
	 * @param mixed $value Raw value (unix timestamp).
	 * @return string
	 */
	private function format_date_value( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}

		if ( '' === $value || null === $value ) {
			return '';
		}

		if ( ! is_numeric( $value ) ) {
			return (string) $value;
		}

		$timestamp = (int) $value;

		if ( $timestamp <= 0 ) {
			return '';
		}

		$format = get_option( 'date_format' );

		if ( empty( $format ) ) {
			$format = 'M d, Y';
		}

		return wp_date( $format, $timestamp );
	}

	/**
	 * Convert raw keys into nicer labels.
	 *
	 * @param string $value Raw key.
	 * @return string
	 */
	private function humanize_key( $value ) {
		if ( '' === $value ) {
			return '';
		}

		$value = str_replace( [ '_', '-' ], ' ', $value );

		return ucwords( trim( $value ) );
	}

	/**
	 * Handle ajax requests.
	 *
	 * @return void
	 * @since 3.7.0
	 */
	public function ajax_handler() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$title = ! empty( $_POST[ 'title' ] ) ? htmlspecialchars( $_POST[ 'title' ] ) : ''; //phpcs:ignore

		$args = [
			'post_type'   => 'jupiterx-popups',
			'post_title'  => $title,
			'post_status' => 'draft',
			'meta_input'  => [
				'_elementor_template_type' => 'jupiterx-popups',
				'_elementor_edit_mode' => 'builder',
			],
		];

		$post_id    = wp_insert_post( $args );
		$editor_url = Plugin::$instance->documents->get( $post_id )->get_edit_url();

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( $post_id->get_error_message() );
		}

		wp_send_json_success( [
			'url' => $editor_url,
		] );
	}

	/**
	 * Remove popup by ajax.
	 *
	 * @return void
	 */
	public function remove_post() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $post_id ) ) {
			wp_send_json_error();
		}

		$post = get_post( $post_id );

		if ( ! $post || 'trash' === $post->post_status ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error();
		}

		$result = wp_trash_post( $post_id );

		if ( ! $result ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Permanently delete a trashed popup.
	 *
	 * @since 4.16.0
	 * @return void
	 */
	public function delete_post_permanently() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $post_id ) ) {
			wp_send_json_error();
		}

		$post = get_post( $post_id );

		if ( ! $post || 'trash' !== $post->post_status ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error();
		}

		$result = wp_delete_post( $post_id, true );

		if ( empty( $result ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Restore a trashed popup.
	 *
	 * @since 4.16.0
	 * @return void
	 */
	public function restore_post() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $post_id ) ) {
			wp_send_json_error();
		}

		$post = get_post( $post_id );

		if ( ! $post || 'trash' !== $post->post_status ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		$result = wp_untrash_post( $post_id );

		if ( ! $result ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Change popup status (publish/draft).
	 *
	 * @since 4.16.0
	 * @return void
	 */
	public function change_post_status() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$status  = isset( $_POST['post_status'] ) ? sanitize_text_field( wp_unslash( $_POST['post_status'] ) ) : '';

		if ( empty( $post_id ) || ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
			wp_send_json_error();
		}

		$post = get_post( $post_id );

		if ( ! $post || 'trash' === $post->post_status ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		$result = wp_update_post(
			[
				'ID'          => (int) $post_id,
				'post_status' => $status,
			],
			true
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_messages() );
		}

		wp_send_json_success();
	}

	/**
	 * Rename popup title.
	 *
	 * @return void
	 */
	public function rename_post() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$title   = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$result = wp_update_post(
			[
				'ID'         => $post_id,
				'post_title' => $title,
			],
			true
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_messages() );
		}

		wp_send_json_success();
	}

	/**
	 * Get import template form.
	 *
	 * @return void
	 * @since 3.7.0
	 */
	public function import_form() {
		ob_start();

		$action = add_query_arg(
			[
				'action' => 'import_popup_action',
			],
			esc_url( admin_url( 'admin.php' ) )
		);

		?>
		<div id="jupiterx-import-template-area">
				<div id="jupiterx-import-template-title"><?php echo esc_html__( 'Choose an Elementor template JSON file or a .zip archive of Elementor templates, and add them to the list of templates available in your library.', 'jupiterx-core' ); ?></div>
				<form id="jupiterx-import-template-form" method="post" action="<?php echo esc_url( $action ); ?>" enctype="multipart/form-data">
					<?php wp_nonce_field( 'jupiterx_import_popup_action', 'jupiterx_import_popup_nonce' ); ?>
					<fieldset id="jupiterx-import-template-form-inputs">
						<input type="file" name="file" accept=".json,application/json,.zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed" required>
						<input id="jupiterx-import-template-action" type="submit" class="button" value="<?php echo esc_attr__( 'Import Now', 'jupiterx-core' ); ?>">
					</fieldset>
				</form>
			</div>
		<?php

		$content = ob_get_clean();

		wp_send_json_success( [
			'content' => $content,
		] );
	}

	/**
     * Import popup template base on file type.
     *
     * @return void
     * @since 3.7.0
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function import_popup_templates() {
        // --- SECURITY PATCH: Verify permissions and nonce ---
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to import popups.', 'jupiterx-core' ) );
        }

        if ( ! isset( $_POST['jupiterx_import_popup_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jupiterx_import_popup_nonce'] ) ), 'jupiterx_import_popup_action' ) ) {
            wp_die( esc_html__( 'Security check failed. Please refresh the page and try again.', 'jupiterx-core' ) );
        }
        // --- END SECURITY PATCH ---

        $file = filter_var_array( $_FILES, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

        if ( empty( $file ) ) {
            wp_die( esc_html__( 'Empty file.', 'jupiterx-core' ) );
        }

        $file = $file['file'];

        if ( 'application/zip' !== $file['type'] && 'application/json' !== $file['type'] ) {
            wp_die( esc_html__( 'Format not allowed', 'jupiterx-core' ) );
        }

        $path = $file['tmp_name'];

        $templates = [];

        if ( 'application/zip' === $file['type'] ) {
            $extracted_files = Elementor\Plugin::instance()->uploads_manager->extract_and_validate_zip( $path, [ 'json' ] );

            if ( is_wp_error( $extracted_files ) ) {
                wp_die( esc_html( $extracted_files->get_error_message() ) );
            }

            foreach ( $extracted_files['files'] as $file_path ) {
                $import_result = $this->import_popup_template( $file_path );

                if ( empty( $import_result ) ) {
                    Elementor\Plugin::instance()->uploads_manager->remove_file_or_dir( $extracted_files['extraction_directory'] );

                    wp_die( esc_html__( 'Unable to import popup template.', 'jupiterx-core' ) );
                }

                $templates[] = $import_result;
            }

            Elementor\Plugin::instance()->uploads_manager->remove_file_or_dir( $extracted_files['extraction_directory'] );
        }

        if ( 'application/json' === $file['type'] ) {
            $templates = $this->import_popup_template( $path );
        }

        if ( ! empty( $templates ) && is_array( $templates ) ) {
            $popups = add_query_arg(
                [
                    'post_type' => 'jupiterx-popups',
                ],
                admin_url( 'edit.php' )
            );

            wp_safe_redirect( $popups );

            die();
        }

        if ( ! empty( $templates ) && ! is_array( $templates ) ) {
            $edit = add_query_arg(
                [
                    'post' => $templates,
                    'action' => 'elementor',
                ],
                admin_url( 'post.php' )
            );

            wp_safe_redirect( $edit );

            die();
        }
    }

	/**
	 * Import popup template functionality.
	 *
	 * @param string $file template file path.
	 * @return int
	 * @since 3.7.0
	 */
	private function import_popup_template( $file ) {
		$content = file_get_contents( $file ); // phpcs:ignore
		$content = json_decode( $content, true );

		if ( ! $content ) {
			wp_die( esc_html__( 'No data found in file', 'jupiterx-core' ) );
		}

		$documents = Plugin::instance()->documents;
		$doc_type  = $documents->get_document_type( 'jupiterx-popups' );

		$popup_content    = $content['content'];
		$popup_conditions = ! empty( $content['popup_conditions'] ) ? $content['popup_conditions'] : [];
		$popup_triggers   = ! empty( $content['popup_triggers'] ) ? $content['popup_triggers'] : [];
		$popup_settings   = ! empty( $content['page_settings'] ) ? $content['page_settings'] : [];
		$popup_content    = $this->get_imported_template_content( $popup_content );

		$post_data = [
			'post_type'  => 'jupiterx-popups',
			'meta_input' => [
				'_elementor_edit_mode'     => 'builder',
				$doc_type::TYPE_META_KEY   => 'jupiterx-popups',
				'_elementor_data'          => wp_slash( wp_json_encode( $popup_content ) ),
				'_elementor_page_settings' => $popup_settings,
				'_jupiterx_popup_conditions' => $popup_conditions,
				'_jupiterx_popup_triggers' => $popup_triggers,
			],
		];

		$post_data['post_title'] = ! empty( $content['title'] ) ? $content['title'] : esc_html__( 'New Popup', 'jupiterx-core' );

		$popup_id = wp_insert_post( $post_data );

		if ( ! $popup_id ) {
			wp_die( esc_html__( 'Can\'t create popup.', 'jupiterx-core' ) );
		}

		return $popup_id;
	}

	/**
	 * Get import content.
	 *
	 * @param array $content template content.
	 * @return array|null
	 * @since 3.7.0
	 */
	private function get_imported_template_content( $content ) {
		$import = 'on_import';

		$data = Plugin::$instance->db->iterate_data(
			$content, function( $element_data ) use ( $import ) {
				$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

				if ( ! $element ) {
					return null;
				}

				$element_data = $element->get_data();

				if ( method_exists( $element, $import ) ) {
					$element_data = $element->{$import}( $element_data );
				}

				foreach ( $element->get_controls() as $control ) {
					$control_class = Plugin::$instance->controls_manager->get_control( $control['type'] );

					if ( ! $control_class ) {
						return $element_data;
					}

					if ( method_exists( $control_class, $import ) ) {
						$element_data['settings'][ $control['name'] ] = $control_class->{$import}( $element->get_settings( $control['name'] ), $control );
					}
				}

				return $element_data;
			}
		);

		return $data;
	}

	/**
	 * Get conditions options list.
	 *
	 * @return void
	 * @since 3.7.0
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function get_options() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$type = ! empty( $_GET[ 'type' ] ) ? htmlspecialchars( $_GET[ 'type' ] ) : ''; //phpcs:ignore

		$condition_name = ! empty( $_GET[ 'condition_name' ] ) ? htmlspecialchars( $_GET[ 'condition_name' ] ) : ''; //phpcs:ignore
		$search_value   = ! empty( $_GET[ 'search_value' ] ) ? htmlspecialchars( $_GET[ 'search_value' ] ) : ''; //phpcs:ignore

		if ( empty( $condition_name ) || ( 'condition' === $type && empty( $search_value ) ) ) {
			wp_send_json_error( esc_html__( 'There is something wrong while passing data.', 'jupiterx-core' ) );
		}

		$options = [];

		if ( class_exists( 'JupiterX_Popups_Conditions_Manager' ) && 'condition' === $type ) {
			$options = call_user_func( [ JupiterX_Popups_Conditions_Manager::$conditions[ $condition_name ], 'get_options' ], $search_value );
		}

		if ( class_exists( 'JupiterX_Popups_Triggers_Manager' ) && 'trigger' === $type ) {
			$options = call_user_func( [ JupiterX_Popups_Triggers_Manager::$triggers[ $condition_name ], 'get_options' ], $search_value );
		}

		wp_send_json_success( $options );
	}

	/**
	 * Save triggers and conditions.
	 *
	 * @return void
	 * @since 3.7.0
	 */
	public function save_conditions_triggers() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$popup_id   = ! empty( $_POST[ 'popup_id' ] ) ? (int) htmlspecialchars( $_POST[ 'popup_id' ] ) : ''; //phpcs:ignore
		$conditions = filter_input( INPUT_POST, 'conditions', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$triggers   = filter_input( INPUT_POST, 'triggers', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( empty( $popup_id ) ) {
			return;
		}

		update_post_meta( $popup_id, '_jupiterx_popup_conditions', $conditions );
		update_post_meta( $popup_id, '_jupiterx_popup_triggers', $triggers );

		wp_send_json_success();
	}

	/**
	 * Get current popup conditions
	 *
	 * @return void
	 * @since 3.7.0
	 */
	public function get_popup_conditions() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$popup_id = ! empty( $_POST[ 'popup_id' ] ) ? (int) htmlspecialchars( $_POST[ 'popup_id' ] ) : ''; //phpcs:ignore

		$conditions = get_post_meta( $popup_id, '_jupiterx_popup_conditions', true );

		wp_send_json_success( $conditions );
	}

	/**
	 * Get current popup triggers.
	 *
	 * @return void
	 * @since 3.7.0
	 */
	public function get_popup_triggers() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have access to this section.', 'jupiterx-core' ) );
		}

		$popup_id = ! empty( $_POST[ 'popup_id' ] ) ? (int) htmlspecialchars( $_POST[ 'popup_id' ] ) : ''; //phpcs:ignore

		$conditions = get_post_meta( $popup_id, '_jupiterx_popup_triggers', true );

		wp_send_json_success( $conditions );
	}
}

JupiterX_Core_Control_Panel_Popup::get_instance();

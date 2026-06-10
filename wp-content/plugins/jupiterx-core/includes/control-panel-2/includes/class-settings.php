<?php
defined( 'ABSPATH' ) || die();
/**
 * Settings API: JupiterX_Core_Control_Panel_Settings base class
 *
 * @package JupiterX_Core\Framework\Control_Panel\Settings
 *
 * @since 1.18.0
 */

if ( ! class_exists( 'JupiterX_Core_Control_Panel_Settings' ) ) {
	/**
	 * Settings.
	 *
	 * @since 1.18.0
	 */
	class JupiterX_Core_Control_Panel_Settings {

		/**
		 * Constructor.
		 *
		 * @since 1.18.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_jupiterx_core_cp_settings', [ $this, 'ajax_handler' ] );
			add_action( 'wp_ajax_jupiterx_control_panel_welcome_box', [ $this, 'welcome_box' ] );
		}

		/**
		 * Map the requests to proper methods.
		 *
		 * @since 1.18.0
		 */
		public function ajax_handler() {
			check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You do not have access to this section.', 'jupiterx-core' );
			}

			jupiterx_log(
				"[Control Panel > Settings] To handle the request, the following data is expected to be an array consisting of 'action', '_wpnonce', 'type' and 'fields' for save type.",
				$_POST
			);

			$type = jupiterx_post( 'type' );

			if ( ! $type ) {
				wp_send_json_error(
					__( 'Type param is missing.', 'jupiterx-core' )
				);
			}

			if ( 'save' === $type ) {
				$this->save();
			}

			wp_send_json_error(
				/* translators: Function request type to initialize. */
				sprintf( esc_html__( 'Type param (%s) is not valid.', 'jupiterx-core' ), $type )
			);
		}

		/**
		 * Save settings.
		 *
		 * @since 1.18.0
		 */
		public function save() {
			$fields = jupiterx_post( 'fields' );

			if ( ! $fields || ! is_array( $fields ) ) {
				wp_send_json_error( esc_html__( 'Fields param is missing.', 'jupiterx-core' ) );
			}

			$fields = (array) $fields;

			if ( ! jupiterx_is_pro() ) {
				$pro_fields = [
					'jupiterx_adobe_fonts_project_id',
					'jupiterx_tracking_codes_after_head',
					'jupiterx_tracking_codes_before_head',
					'jupiterx_tracking_codes_after_body',
					'jupiterx_tracking_codes_before_body',
				];

				foreach ( $pro_fields as $name ) {
					unset( $fields[ $name ] );
				}
			}

			// Elementor settings below are stored as WordPress options, not JupiterX theme options.
			if ( class_exists( '\Elementor\Plugin' ) ) {
				$elementor_bridge = JupiterX_Core_Control_Panel_Elementor_Settings_Bridge::get_instance();

				if ( isset( $fields['elementor_cpt_support'] ) ) {
					$cpt_raw = $fields['elementor_cpt_support'];
					unset( $fields['elementor_cpt_support'] );
					if ( ! is_array( $cpt_raw ) ) {
						$cpt_raw = [];
					}
					$elementor_bridge->persist_elementor_cpt_support( $cpt_raw );
				}

				$elementor_general_fields = [];
				foreach (
					[
						'elementor_disable_color_schemes',
						'elementor_disable_typography_schemes',
						'elementor_editor_break_lines',
						'elementor_unfiltered_files_upload',
						'elementor_google_font',
						'elementor_font_display',
						'elementor_load_fa4_shim',
						'elementor_meta_generator_tag',
					] as $field_name
				) {
					if ( ! array_key_exists( $field_name, $fields ) ) {
						continue;
					}

					$elementor_general_fields[ $field_name ] = $fields[ $field_name ];
					unset( $fields[ $field_name ] );
				}

				if ( ! empty( $elementor_general_fields ) ) {
					$elementor_bridge->persist_elementor_general_settings( $elementor_general_fields );
				}
			}

			foreach ( $fields as $name => $value ) {
				$name = preg_replace( '/(jupiterx|artbees)_/', '', $name, 1 );
				$this->handle_simplicity_mode( $name, $value );
				jupiterx_update_option( $name, $value );
			}

			wp_send_json_success( esc_html__( 'Settings saved successfully.', 'jupiterx-core' ) );
		}

		/**
		 * Save last value of simplicity mode.
		 *
		 * @param string $name  Field name.
		 * @param int    $value Field value.
		 * @since 3.8.0
		 */
		private function handle_simplicity_mode( $name, $value ) {
			if ( 'disable_theme_default_settings' !== $name ) {
				return;
			}

			$default = ! is_array( jupiterx_get_option( 'elements' ) ) ? '1' : '0';

			$simplicity_mode = get_option( 'jupiterx_disable_theme_default_settings', $default );

			if ( $simplicity_mode !== $value ) {
				jupiterx_core_flush_cache();
			}

			if ( 'disable_theme_default_settings' === $name ) {
				update_option( 'jupiterx_disable_theme_default_settings', $value );
			}

			delete_transient( 'jupiterx_inactive_required_plugins_list' );
		}

		/**
		 * Dismiss jupiterx control panel welcome box.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function welcome_box() {
			check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You do not have access to this section.', 'jupiterx-core' );
			}

			update_option( 'jupiterx_dashboard_welcome_box', 'false' );

			wp_send_json_success();
		}

	}

	new JupiterX_Core_Control_Panel_Settings();
}

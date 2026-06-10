<?php
defined( 'ABSPATH' ) || die();
/**
 * This class handles admin notices.
 *
 * @package JupiterX_Core\Admin
 *
 * @since 1.18.0
 */

/**
 * Handle admin notices.
 *
 * @package JupiterX_Core\Admin
 *
 * @since 1.18.0
 */
class JupiterX_Core_Admin_Notices {

	/**
	 * Notice hooks that can inject admin banners and promos.
	 *
	 * @since 4.14.2
	 *
	 * @var string[]
	 */
	const NOTICE_HOOKS = [
		'admin_notices',
		'all_admin_notices',
		'network_admin_notices',
		'user_admin_notices',
	];

	/**
	 * Constructor.
	 *
	 * @since 1.18.0
	 */
	public function __construct() {
		add_filter( 'jet-dashboard/js-page-config', [ $this, 'remove_croco_license_notice' ], 10, 1 );
		add_action( 'current_screen', [ $this, 'suppress_third_party_notices' ], 100 );
	}

	/**
	 * Remove Croco notice.
	 *
	 * @param $notices
	 * @return void|array
	 * @since 1.20.0
	 */
	public function remove_croco_license_notice( $notices ) {
		if ( empty( $notices['noticeList'] ) ) {
			return $notices;
		}

		foreach ( $notices['noticeList'] as $key => $notice ) {
			if ( empty( $notice['id'] ) || '30days-to-license-expire' !== $notice['id'] ) {
				continue;
			}

			unset( $notices['noticeList'][ $key ] );
		}

		// Reindex array after unset
		$notices['noticeList'] = array_values( $notices['noticeList'] );

		return $notices;
	}

	/**
	 * Remove third-party admin notices from JupiterX control panel screens.
	 *
	 * Plugins like WooCommerce and Rank Math do this by filtering notice hooks
	 * only on their own pages, so external promos do not break the product UI.
	 *
	 * @since 4.14.2
	 *
	 * @return void
	 */
	public function suppress_third_party_notices() {
		if ( ! $this->is_jupiterx_screen() ) {
			return;
		}

		global $wp_filter;

		foreach ( self::NOTICE_HOOKS as $hook_name ) {
			if ( empty( $wp_filter[ $hook_name ] ) || ! $wp_filter[ $hook_name ] instanceof WP_Hook ) {
				continue;
			}

			foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					if ( empty( $callback['function'] ) ) {
						continue;
					}

					if ( $this->should_keep_notice_callback( $callback['function'] ) ) {
						continue;
					}

					remove_action( $hook_name, $callback['function'], $priority );
				}
			}
		}
	}

	/**
	 * Determine whether the current screen belongs to JupiterX.
	 *
	 * @since 4.14.2
	 *
	 * @return bool
	 */
	private function is_jupiterx_screen() {
		if ( ! is_admin() ) {
			return false;
		}

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			if ( $screen ) {
				$screen_id = isset( $screen->id ) ? (string) $screen->id : '';
				$screen_base = isset( $screen->base ) ? (string) $screen->base : '';
				$post_type = isset( $screen->post_type ) ? (string) $screen->post_type : '';

				if (
					0 === strpos( $screen_id, 'jupiterx' ) ||
					false !== strpos( $screen_id, 'page_jupiterx' ) ||
					0 === strpos( $screen_base, 'jupiterx' ) ||
					'jupiterx-popups' === $post_type
				) {
					return true;
				}
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

		return 0 === strpos( $page, 'jupiterx' ) || 'jupiterx-popups' === $post_type;
	}

	/**
	 * Decide whether a notice callback should remain visible.
	 *
	 * JupiterX notices stay visible, and WordPress core notices are preserved.
	 * Third-party plugin callbacks are removed on JupiterX screens.
	 *
	 * @since 4.14.2
	 *
	 * @param callable $callback Notice callback.
	 *
	 * @return bool
	 */
	private function should_keep_notice_callback( $callback ) {
		$callback_file = $this->get_callback_file( $callback );

		if ( empty( $callback_file ) ) {
			return $this->is_jupiterx_callback_name( $callback );
		}

		$callback_file = wp_normalize_path( $callback_file );
		$plugin_dir    = wp_normalize_path( jupiterx_core()->plugin_dir() );
		$admin_dir     = wp_normalize_path( ABSPATH . 'wp-admin/' );
		$includes_dir  = wp_normalize_path( ABSPATH . WPINC . '/' );

		return 0 === strpos( $callback_file, $plugin_dir ) ||
			0 === strpos( $callback_file, $admin_dir ) ||
			0 === strpos( $callback_file, $includes_dir );
	}

	/**
	 * Resolve the file path that registered a callback.
	 *
	 * @since 4.14.2
	 *
	 * @param callable $callback Notice callback.
	 *
	 * @return string
	 */
	private function get_callback_file( $callback ) {
		try {
			if ( is_string( $callback ) && function_exists( $callback ) ) {
				$reflection = new ReflectionFunction( $callback );

				return (string) $reflection->getFileName();
			}

			if ( $callback instanceof Closure ) {
				$reflection = new ReflectionFunction( $callback );

				return (string) $reflection->getFileName();
			}

			if ( is_array( $callback ) && 2 === count( $callback ) ) {
				$reflection = new ReflectionMethod( $callback[0], $callback[1] );

				return (string) $reflection->getFileName();
			}
		} catch ( ReflectionException $exception ) {
			return '';
		}

		return '';
	}

	/**
	 * Check callback names for JupiterX-owned plain functions.
	 *
	 * @since 4.14.2
	 *
	 * @param callable $callback Notice callback.
	 *
	 * @return bool
	 */
	private function is_jupiterx_callback_name( $callback ) {
		if ( is_string( $callback ) ) {
			return 0 === strpos( $callback, 'jupiterx_' );
		}

		if ( is_array( $callback ) && isset( $callback[0] ) ) {
			if ( is_object( $callback[0] ) ) {
				return 0 === strpos( get_class( $callback[0] ), 'JupiterX_' );
			}

			if ( is_string( $callback[0] ) ) {
				return 0 === strpos( $callback[0], 'JupiterX_' );
			}
		}

		return false;
	}
}

new JupiterX_Core_Admin_Notices();

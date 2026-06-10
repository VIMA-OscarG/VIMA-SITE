<?php
defined( 'ABSPATH' ) || die();
/**
 * JupiterX_Core General Utilities
 *
 * @package JupiterX_Core\Utilities
 *
 * @since 1.20.0
 */

if ( ! function_exists( 'jupiterx_log' ) ) {
	/**
	 * Add log in WordPress default debug file.
	 *
	 * @since 1.20.0
	 *
	 * @param string $message The log message.
	 * @param array $data The log data.
	 *
	 * @return void
	 */
	function jupiterx_log( $message, $data = null ) {
		if ( ! jupiterx_is_debug_log() || empty( $message ) || ! is_string( $message ) ) {
			return;
		}

		// Check JUPITERX_LOG.
		if ( ! defined( 'JUPITERX_LOG' ) || empty( JUPITERX_LOG ) ) {
			return false;
		}

		// Add message.
		$log = '[Jupiter X] ' . $message;

		// phpcs:disable
		// Add data.
		if ( ! empty( $data ) ) {
			$log .= "\n" . print_r( $data, true );
		}

		// Add stack trace.
		$backtrace = debug_backtrace();

		if ( ! empty( $backtrace ) ) {
			$backtrace = reset( $backtrace );
		}

		if ( ! empty( $backtrace['file'] ) || ! empty( $backtrace['line'] ) ) {
			$log .= "\nStack trace:\n#0 {$backtrace['file']}({$backtrace['line']})";
		}

		// Log.
		error_log( $log );
		// phpcs:enable
	}
}

if ( ! function_exists( 'jupiterx_is_debug_log' ) ) {
	/**
	 * Check if debug log is enabled.
	 *
	 * @since 1.20.0
	 *
	 * @return boolean
	 */
	function jupiterx_is_debug_log() {
		// Check WP_DEBUG.
		if ( defined( 'WP_DEBUG' ) && false === WP_DEBUG ) {
			return false;
		}

		// Check WP_DEBUG_LOG.
		if ( defined( 'WP_DEBUG_LOG' ) && false === WP_DEBUG_LOG ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'jupiterx_core_get_integrations_settings_url' ) ) {
	/**
	 * Admin URL for JupiterX Control Panel → Settings → Integrations.
	 *
	 * @since 4.15.0
	 *
	 * @return string Unescaped URL; use esc_url() when printing.
	 */
	function jupiterx_core_get_integrations_settings_url() {
		return admin_url( 'admin.php?page=jupiterx' ) . '#/settings#integrations';
	}
}

if ( ! function_exists( 'jupiterx_core_get_portfolio_settings' ) ) {
	/**
	 * Get portfolio settings from Jupiter option storage.
	 *
	 * @since 4.15.0
	 *
	 * @return array
	 */
	function jupiterx_core_get_portfolio_settings() {
		$options = get_option( 'jupiterx', [] );

		$mode = strtolower( trim( (string) ( $options['portfolio_post_type_mode'] ?? 'enabled' ) ) );

		if ( ! in_array( $mode, [ 'enabled', 'hidden' ], true ) ) {
			$mode = 'enabled';
		}

		$slug = sanitize_title( (string) ( $options['portfolio_post_type_slug'] ?? 'portfolio' ) );

		if ( empty( $slug ) ) {
			$slug = 'portfolio';
		}

		$singular_label = sanitize_text_field( (string) ( $options['portfolio_singular_label'] ?? 'Portfolio' ) );
		$plural_label   = sanitize_text_field( (string) ( $options['portfolio_plural_label'] ?? 'Portfolios' ) );

		if ( empty( $singular_label ) ) {
			$singular_label = 'Portfolio';
		}

		if ( empty( $plural_label ) ) {
			$plural_label = 'Portfolios';
		}

		return [
			'mode'           => $mode,
			'slug'           => $slug,
			'singular_label' => $singular_label,
			'plural_label'   => $plural_label,
		];
	}
}

if ( ! function_exists( 'jupiterx_core_get_portfolio_label' ) ) {
	/**
	 * Get a portfolio label for a specific context.
	 *
	 * @since 4.15.0
	 *
	 * @param string $context Label context.
	 *
	 * @return string
	 */
	function jupiterx_core_get_portfolio_label( $context = 'plural' ) {
		$settings = jupiterx_core_get_portfolio_settings();

		switch ( $context ) {
			case 'singular':
				return $settings['singular_label'];
			case 'archive':
				return sprintf( esc_html__( '%s Archive', 'jupiterx-core' ), $settings['plural_label'] );
			case 'single':
				return sprintf( esc_html__( '%s Single', 'jupiterx-core' ), $settings['singular_label'] );
			case 'categories':
				return sprintf( esc_html__( '%s Categories', 'jupiterx-core' ), $settings['plural_label'] );
			case 'tags':
				return sprintf( esc_html__( '%s Tags', 'jupiterx-core' ), $settings['plural_label'] );
			case 'plural':
			default:
				return $settings['plural_label'];
		}
	}
}

<?php
/**
 * Plugin Name: Frontend Reset Password
 * Description: Let your users reset their forgotten passwords from the frontend of your website.
 * Version: 1.3.3
 * Author: WP Enhanced
 * Author URI: https://wpenhanced.com
 * Requires at least: 4.4
 * Tested up to: 6.9
 *
 * Text Domain: frontend-reset-password
 * Domain Path: /i18n/languages
 *
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Donate link: https://www.paypal.com/donate/?hosted_button_id=VAYF6G99MCMHU
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'SOMFRP_FILE', __FILE__ );
define( 'SOMFRP_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOMFRP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require main plugin loader
require_once( SOMFRP_PATH . 'somfrp-loader.php' );

/**
 * Add Settings link to the plugins page
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function somfrp_plugin_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wp-enhanced' ) ) . '">' . esc_html__( 'Settings', 'frontend-reset-password' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . SOMFRP_PLUGIN_BASENAME, 'somfrp_plugin_action_links' );

/**
 * Add legacy settings menu item under Settings for backwards compatibility.
 * This redirects to the new WP Enhanced settings page.
 */
function somfrp_add_legacy_settings_menu() {
	add_options_page(
		__( 'Frontend Reset Password', 'frontend-reset-password' ),
		__( 'Frontend Reset Password', 'frontend-reset-password' ),
		'manage_options',
		'somfrp_options_page',
		'somfrp_legacy_settings_redirect'
	);
}
add_action( 'admin_menu', 'somfrp_add_legacy_settings_menu' );

/**
 * Redirect legacy settings page to the new WP Enhanced settings page.
 */
function somfrp_legacy_settings_redirect() {
	wp_safe_redirect( admin_url( 'admin.php?page=wp-enhanced' ) );
	exit;
}

/**
 * Handle redirect early if accessing the legacy settings page directly.
 */
function somfrp_maybe_redirect_legacy_settings() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && $_GET['page'] === 'somfrp_options_page' ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wp-enhanced' ) );
		exit;
	}
}
add_action( 'admin_init', 'somfrp_maybe_redirect_legacy_settings' );
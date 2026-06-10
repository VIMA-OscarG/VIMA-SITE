<?php
/**
 * Update plugins functionality.
 *
 * @package JupiterX_Core\Updater
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'pre_current_active_plugins', 'jupiterx_plugin_update_warning' );
/**
 * Render Update conflict warning on WordPress plugin page.
 *
 * @since 1.3.0
 *
 * @return void
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function jupiterx_plugin_update_warning() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$wp_updated_plugins = get_site_transient( 'update_plugins' );

	$plugins = jupiterx_get_update_plugins( false );

	foreach ( $plugins as &$plugin ) {
		$plugin = (array) $plugin;
	}

	foreach ( $plugins as $plugin ) {
		// translators: 1. Heads up title.
		$message = sprintf( esc_html__( '%1$s We have found conflicts on updating this plugin. Please resolve following issues before you continue otherwise it may cause unknown issues.', 'jupiterx-core' ), '<b>' . esc_html__( 'Heads up!', 'jupiterx-core' ) . '</b>' );

		add_action(
			'in_plugin_update_message-' . $plugin['basename'],
			function ( $plugin_data, $response ) use ( $plugin, $message, $wp_updated_plugins ) {

				if ( 'wp-repo' === $plugin['version'] ) {
					if (
						empty( $wp_updated_plugins ) &&
						empty( $wp_updated_plugins->response[ $plugin['basename'] ] )
					) {
						return;
					}

					$plugin['version'] = $wp_updated_plugins
						->response[ $plugin['basename'] ]
						->new_version;
				}

				if ( version_compare( $response->new_version, $plugin['version'] ) !== 0 ) {
					return;
				}

				$conflicts = jupiterx_get_plugin_conflicts( $plugin, get_plugins() );

				if ( empty( $conflicts['plugins'] ) && empty( $conflicts['themes'] ) ) {
					return;
				}

				ob_start();
				include 'views/html-notice-update-extensions-themes-inline.php';
				echo wp_kses_post( ob_get_clean() );
				?>
				<?php
			},
			10,
			2
		);
	}
}

add_action( 'upgrader_process_complete', 'jupiterx_upgrader_process_complete', 10, 2 );
/**
 * Run actions after WordPress upgrader process complete.
 *
 * @since 1.3.0
 *
 * @param object $upgrader_object WP_Upgrader instance.
 * @param array  $options         Update data.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function jupiterx_upgrader_process_complete( $upgrader_object, $options ) {

	if ( 'update' !== $options['action'] ) {
		return;
	}

	if ( 'theme' === $options['type'] ) {
		jupiterx_core_flush_cache_all_sites();
		return;
	}

	if ( 'plugin' !== $options['type'] ) {
		return;
	}

	$plugins_list = [];

	if ( ! empty( $options['plugins'] ) ) {
		$plugins_list = $options['plugins'];
	}

	if ( ! empty( $options['plugin'] ) ) {
		$plugins_list[] = $options['plugin'];
	}

	if ( empty( $plugins_list ) ) {
		return;
	}

	$watched_plugins = jupiterx_get_watched_plugins();

	if ( empty( array_intersect( $watched_plugins, $plugins_list ) ) ) {
		return;
	}

	jupiterx_core_flush_cache_all_sites();
}

/**
 * Get the list of plugins whose updates should trigger a cache flush.
 *
 * @since 4.8.0
 *
 * @return array
 */
function jupiterx_get_watched_plugins() {
	$plugins = [
		'jupiterx-core/jupiterx-core.php',
		'elementor/elementor.php',
		'elementor-pro/elementor-pro.php',
		'raven/raven.php',
		'jet-engine/jet-engine.php',
		'jet-elements/jet-elements.php',
		'jet-menu/jet-menu.php',
		'jet-tabs/jet-tabs.php',
		'jet-tricks/jet-tricks.php',
		'jet-blocks/jet-blocks.php',
		'jet-blog/jet-blog.php',
		'jet-woo-builder/jet-woo-builder.php',
		'jet-popup/jet-popup.php',
		'jet-smart-filters/jet-smart-filters.php',
		'jet-theme-core/jet-theme-core.php',
	];

	/**
	 * Filter the list of plugins that trigger a full cache flush on update.
	 *
	 * @since 4.8.0
	 *
	 * @param array $plugins Plugin basenames.
	 */
	return apply_filters( 'jupiterx_watched_plugins_for_flush', $plugins );
}

/**
 * Flush cache across all sites in a multisite network, or the current site.
 *
 * @since 4.8.0
 */
function jupiterx_core_flush_cache_all_sites() {
	if ( ! is_multisite() ) {
		jupiterx_core_flush_cache();
		return;
	}

	$sites = get_sites( [
		'fields' => 'ids',
		'number' => 0,
	] );

	foreach ( $sites as $blog_id ) {
		switch_to_blog( $blog_id );
		jupiterx_core_flush_cache();
		restore_current_blog();
	}
}

/**
 * Wrapper function for flush cache functions.
 *
 * Clears JupiterX compiler dirs, Elementor generated CSS, Crocoblock/Jet
 * cached assets, and page cache plugins for the current site.
 *
 * @since 1.2.0
 */
function jupiterx_core_flush_cache() {
	if ( function_exists( 'jupiterx_remove_dir' ) && function_exists( 'jupiterx_get_compiler_dir' ) ) {
		jupiterx_remove_dir( jupiterx_get_compiler_dir() );
		jupiterx_remove_dir( jupiterx_get_compiler_dir( true ) );
		jupiterx_remove_dir( jupiterx_get_images_dir() );
	}

	if ( function_exists( 'jupiterx_elementor_flush_cache' ) ) {
		jupiterx_elementor_flush_cache();
	}

	jupiterx_flush_crocoblock_cache();

	if ( function_exists( 'jupiterx_flush_cache_plugins' ) ) {
		jupiterx_flush_cache_plugins();
	}
}

/**
 * Clear Crocoblock / Jet plugin cached CSS and style data.
 *
 * Jet plugins (JetEngine, JetMenu, JetElements, etc.) store compiled CSS in
 * upload subdirectories and transients. Jet Style Manager aggregates styles
 * into its own upload folder. This function removes all of those.
 *
 * @since 4.8.0
 */
function jupiterx_flush_crocoblock_cache() {
	if ( ! function_exists( 'jupiterx_remove_dir' ) ) {
		return;
	}

	$wp_upload_dir = wp_upload_dir();
	$base          = trailingslashit( $wp_upload_dir['basedir'] );

	$jet_dirs = [
		'jet-engine',
		'jet-elements',
		'jet-menu',
		'jet-blog',
		'jet-blocks',
		'jet-tabs',
		'jet-tricks',
		'jet-woo-builder',
		'jet-popup',
		'jet-smart-filters',
		'jet-theme-core',
		'jet-style-manager',
	];

	foreach ( $jet_dirs as $slug ) {
		$dir = $base . $slug . '/';
		if ( is_dir( $dir ) ) {
			jupiterx_remove_dir( $dir );
		}
	}

	$jet_transients = [
		'jet_engine_listing_css',
		'jet_menu_cache_css',
		'jet_popup_css',
	];

	foreach ( $jet_transients as $transient ) {
		delete_transient( $transient );
	}

	do_action( 'jet-styles-manager/clear-cache' );
}

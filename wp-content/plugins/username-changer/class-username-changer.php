<?php
/**
 * Plugin Name: Username Changer
 * Plugin URI: https://wpusernamechange.com
 * Description: Change usernames easily
 * Author: TRS Plugins
 * Author URI: https://trsplugins.com/
 * Version: 3.2.8
 * Text Domain: username-changer
 * Domain Path: languages
 * Tested up to: 6.9
 * Requires at least: 3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Username_Changer' ) ) {


	/**
	 * Main Username_Changer class
	 *
	 * @access      public
	 * @since       2.0.0
	 */
	final class Username_Changer {


		/**
		 * The one true Username_Changer
		 *
		 * @access      private
		 * @since       2.0.0
		 * @var         Username_Changer $instance The one true Username_Changer
		 */
		private static $instance;


		/**
		 * The settings object
		 *
		 * @access      public
		 * @since       3.0.0
		 * @var         object $settings The settings object
		 */
		public $settings;


		/**
		 * The template tags object
		 *
		 * @access      public
		 * @since       3.0.0
		 * @var         object $template_tags The template tags object
		 */
		public $template_tags;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       2.0.0
		 * @static
		 * @return      object self::$instance The one true Username_Changer
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Username_Changer ) ) {
				self::$instance = new Username_Changer();
				self::$instance->setup_constants();
				self::$instance->hooks();
				self::$instance->includes();
				self::$instance->template_tags = new Username_Changer_Template_Tags();
			}

			return self::$instance;
		}


		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is
		 * a single object. Therefore, we don't want the object to be cloned.
		 *
		 * @access      protected
		 * @since       1.0.0
		 * @return      void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'username-changer' ), '1.0.0' );
		}


		/**
		 * Disable unserializing of the class
		 *
		 * @access      protected
		 * @since       1.0.0
		 * @return      void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'username-changer' ), '1.0.0' );
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       2.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version.
			if ( ! defined( 'USERNAME_CHANGER_VER' ) ) {
			define( 'USERNAME_CHANGER_VER', '3.2.7' );
			}

			// Plugin path.
			if ( ! defined( 'USERNAME_CHANGER_DIR' ) ) {
				define( 'USERNAME_CHANGER_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin URL.
			if ( ! defined( 'USERNAME_CHANGER_URL' ) ) {
				define( 'USERNAME_CHANGER_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin file.
			if ( ! defined( 'USERNAME_CHANGER_FILE' ) ) {
				define( 'USERNAME_CHANGER_FILE', __FILE__ );
			}
		}


		/**
		 * Run plugin base hooks
		 *
		 * @access      private
		 * @since       3.2.0
		 * @return      void
		 */
		private function hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			global $username_changer_options;

			// Load settings handler if necessary.
			if ( ! class_exists( 'Simple_Settings' ) ) {
				require_once USERNAME_CHANGER_DIR . 'vendor/widgitlabs/simple-settings/class-simple-settings.php';
			}

			require_once USERNAME_CHANGER_DIR . 'includes/admin/settings/register-settings.php';

			self::$instance->settings = new Simple_Settings( 'username_changer', 'settings' );
			$username_changer_options = self::$instance->settings->get_settings();

			require_once USERNAME_CHANGER_DIR . 'includes/misc-functions.php';
			require_once USERNAME_CHANGER_DIR . 'includes/scripts.php';
			require_once USERNAME_CHANGER_DIR . 'includes/class-username-changer-template-tags.php';

			if ( is_admin() ) {
				require_once USERNAME_CHANGER_DIR . 'includes/admin/actions.php';
			}
		}


		/**
		 * Load plugin language files
		 *
		 * @access      public
		 * @since       2.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory.
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'username_changer_languages_directory', $lang_dir );

			// WordPress plugin locale filter.
			$locale = apply_filters( 'plugin_locale', get_locale(), 'username-changer' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'username-changer', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/username-changer/' . $mofile;
			$mofile_core   = WP_LANG_DIR . '/plugins/username-changer/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/username-changer folder.
				load_textdomain( 'username-changer', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/username-changer/languages/ folder.
				load_textdomain( 'username-changer', $mofile_local );
			} elseif ( file_exists( $mofile_core ) ) {
				// Look in core /wp-content/languages/plugins/username-changer/ folder.
				load_textdomain( 'username-changer', $mofile_core );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'username-changer', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the one true Username_Changer
 * instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without
 * needing to declare the global.
 *
 * Example: <?php $username_changer = Username_Changer(); ?>
 *
 * @since       2.0.0
 * @return      Username_Changer The one true Username_Changer
 */
function username_changer() {
	return Username_Changer::instance();
}


/**
 * Check if the Pro license is active.
 *
 * @since  4.0.0
 * @return bool
 */
function username_changer_is_pro_active() {
	return (bool) apply_filters( 'username_changer_is_pro_active', false );
}


// Get things started.
Username_Changer();

/* Opt-in */
require_once dirname( __FILE__ ) . '/includes/class-optin.php';
add_action( 'plugins_loaded', function() {
	UC_Optin::instance();
} );
register_activation_hook( __FILE__, function() {
	require_once dirname( __FILE__ ) . '/includes/class-optin.php';
	UC_Optin::instance()->on_activation();
} );
add_action( 'upgrader_process_complete', 'uc_optin_on_upgrade', 10, 2 );

function uc_optin_on_upgrade( $upgrader, $hook_extra ) {
	if ( empty( $hook_extra['action'] ) || 'update' !== $hook_extra['action'] ) {
		return;
	}

	if ( empty( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
		return;
	}

	$updated_plugins = array();
	if ( ! empty( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] ) ) {
		$updated_plugins = $hook_extra['plugins'];
	} elseif ( ! empty( $hook_extra['plugin'] ) ) {
		$updated_plugins = array( $hook_extra['plugin'] );
	}

	if ( in_array( plugin_basename( __FILE__ ), $updated_plugins, true ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-optin.php';
		UC_Optin::instance()->on_activation();
	}
}

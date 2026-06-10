<?php
/**
 * Plugin Name: WP All Export - User Export Add-On
 * Plugin URI: http://www.wpallimport.com/tour/export-wordpress-users/?utm_source=export-users-addon-free&utm_medium=wp-plugins-page&utm_campaign=upgrade-to-pro
 * Description: Export Users from WordPress. Requires WP All Export.
 * Version: 1.0.2
 * Author: Soflyy
 * Text Domain: export-wp-users-xml-csv
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Plugin root dir with forward slashes as directory separator regardless of actuall DIRECTORY_SEPARATOR value
 * @var string
 */
define('PMUE_ROOT_DIR', str_replace('\\', '/', dirname(__FILE__)));
/**
 * Plugin root url for referencing static content
 * @var string
 */
define('PMUE_ROOT_URL', rtrim(plugin_dir_url(__FILE__), '/'));
/**
 * Plugin prefix for making names unique (be aware that this variable is used in conjuction with naming convention,
 * i.e. in order to change it one must not only modify this constant but also rename all constants, classes and functions which
 * names composed using this prefix)
 * @var string
 */
define('PMUE_PREFIX', 'pmue_');

define('PMUE_VERSION', '1.0.2');

if ( class_exists('PMUE_Plugin') and PMUE_EDITION != "free"){

	function pmue_notice(){
		
		?>
		<div class="error"><p>
			<?php printf(esc_html__('Please de-activate and remove the free version of the User Add-On before activating the paid version.', 'export-wp-users-xml-csv'));
			?>
		</p></div>
		<?php				

		deactivate_plugins(PMUE_ROOT_DIR . '/plugin.php');

	}

	add_action('admin_notices', 'pmue_notice');

}
else {

	define('PMUE_EDITION', 'free');

	/**
	 * Main plugin file, Introduces MVC pattern
	 *
	 * @singletone
	 * @author Maksym Tsypliakov <maksym.tsypliakov@gmail.com>
	 */

	final class PMUE_Plugin {
		/**
		 * Singletone instance
		 * @var PMUE_Plugin
		 */
		protected static $instance;

		/**
		 * Plugin root dir
		 * @var string
		 */
		const ROOT_DIR = PMUE_ROOT_DIR;
		/**
		 * Plugin root URL
		 * @var string
		 */
		const ROOT_URL = PMUE_ROOT_URL;
		/**
		 * Prefix used for names of shortcodes, action handlers, filter functions etc.
		 * @var string
		 */
		const PREFIX = PMUE_PREFIX;
		/**
		 * Plugin file path
		 * @var string
		 */
		const FILE = __FILE__;	

		/**
		 * Return singletone instance
		 * @return PMUE_Plugin
		 */
		static public function getInstance() {
			if (self::$instance == NULL) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		static public function getEddName(){
			return 'User Export Add-On';
		}

		/**
		 * Common logic for requestin plugin info fields
		 */
		public function __call($method, $args) {
			if (preg_match('%^get(.+)%i', $method, $mtch)) {
				$info = get_plugin_data(self::FILE);
				if (isset($info[$mtch[1]])) {
					return $info[$mtch[1]];
				}
			}
			throw new Exception( esc_html( "Requested method " . get_class($this) . "::$method doesn't exist." ) );
		}

		/**
		 * Get path to plagin dir relative to wordpress root
		 * @param bool[optional] $noForwardSlash Whether path should be returned withot forwarding slash
		 * @return string
		 */
		public function getRelativePath($noForwardSlash = false) {
			$wp_root = str_replace('\\', '/', ABSPATH);
			return ($noForwardSlash ? '' : '/') . str_replace($wp_root, '', self::ROOT_DIR);
		}

		/**
		 * Check whether plugin is activated as network one
		 * @return bool
		 */
		public function isNetwork() {
			if ( !is_multisite() )
			return false;

			$plugins = get_site_option('active_sitewide_plugins');
			if (isset($plugins[plugin_basename(self::FILE)]))
				return true;

			return false;
		}

		/**
		 * Class constructor containing dispatching logic
		 * @param string $rootDir Plugin root dir
		 * @param string $pluginFilePath Plugin main file
		 */
		protected function __construct() {

		    include_once 'src'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'Bootstrap'.DIRECTORY_SEPARATOR.'Autoloader.php';
		    $autoloader = new \Pmue\Common\Bootstrap\Autoloader(self::ROOT_DIR, self::PREFIX);
			// create/update required database tables

			// register autoloading method
			spl_autoload_register(array($autoloader, 'autoload'));

			register_activation_hook(self::FILE, array($this, 'activation'));

			$autoloader->init();

			// register admin page pre-dispatcher
			add_action('init', array($this, 'init'));

		}

		public function init(){
			// Translations are automatically loaded by WordPress.org for hosted plugins
		}

		/**
		 * Dispatch shorttag: create corresponding controller instance and call its index method
		 * @param array $args Shortcode tag attributes
		 * @param string $content Shortcode tag content
		 * @param string $tag Shortcode tag name which is being dispatched
		 * @return string
		 */
		public function shortcodeDispatcher($args, $content, $tag) {

			$controllerName = self::PREFIX . preg_replace_callback('%(^|_).%', array($this, "replace_callback"), $tag);// capitalize first letters of class name parts and add prefix
			$controller = new $controllerName();
			if ( ! $controller instanceof PMUE_Controller) {
					throw new Exception( esc_html( "Shortcode `$tag` matches to a wrong controller type." ) );
			}
			ob_start();
			$controller->index($args, $content);
			return ob_get_clean();
		}

		public function replace_callback($matches){
			return strtoupper($matches[0]);
		}

		/**
		 * Plugin activation logic
		 */
		public function activation() {
			// Uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error, WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional fatal error for activation failures
			set_exception_handler(function($e){trigger_error($e->getMessage(), E_USER_ERROR);});
		}

		/**
		 * Method returns default import options, main utility of the method is to avoid warnings when new
		 * option is introduced but already registered imports don't have it
		 */
		public static function get_default_import_options()
        {
            $importOptions = new \Pmue\Common\Bootstrap\DefaultImportOptions();
            return $importOptions->getDefaultImportOptions();
        }
	}

	PMUE_Plugin::getInstance();

    add_action('admin_notices', function(){
        // notify user if history folder is not writable
        if ( ! class_exists( 'PMXE_Plugin' )) {
            ?>
            <div class="error"><p>
                    <?php
                    echo wp_kses_post( sprintf(
                        // translators: %s is the plugin name
                        __('<b>%s Plugin</b>: WP All Export must be installed and activated. You can download it here <a href="https://wordpress.org/plugins/wp-all-export/" target="_blank">https://wordpress.org/plugins/wp-all-export/</a>', 'export-wp-users-xml-csv'),
                        esc_html( PMUE_Plugin::getInstance()->getName() )
                    ) );
                    ?>
                </p></div>
            <?php

            deactivate_plugins(PMUE_ROOT_DIR . '/plugin.php');

        }



        if ( class_exists( 'PMXE_Plugin' ) && ( version_compare(PMXE_VERSION, '1.2.4') < 0 && PMXE_EDITION == 'free') ) {
            ?>
            <div class="error"><p>
                    <?php
                    echo wp_kses_post( sprintf(
                        // translators: %s is the plugin name
                        __('<b>%s Plugin</b>: Please update WP All Export to the latest version', 'export-wp-users-xml-csv'),
                        esc_html( PMUE_Plugin::getInstance()->getName() )
                    ) );
                    ?>
                </p></div>
            <?php

            deactivate_plugins(PMUE_ROOT_DIR . '/plugin.php');
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice display
        $messages = isset($_GET['pmue_nt']) ? map_deep(wp_unslash($_GET['pmue_nt']), 'sanitize_text_field') : array();
        if ($messages) {
            is_array($messages) or $messages = array($messages);
            foreach ($messages as $type => $m) {
                in_array((string)$type, array('updated', 'error')) or $type = 'updated';
                ?>
                <div class="<?php echo esc_attr($type) ?>"><p><?php echo esc_html($m) ?></p></div>
                <?php
            }
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for menu highlighting, no data modification
        if ( ! empty($_GET['type']) and $_GET['type'] == 'user'){
            ?>
            <script type="text/javascript">
                (function($){$(function () {
                    $('#toplevel_page_pmxi-admin-home').find('.wp-submenu').find('li').removeClass('current');
                    $('#toplevel_page_pmxi-admin-home').find('.wp-submenu').find('a').removeClass('current');
                    $('#toplevel_page_pmxi-admin-home').find('.wp-submenu').find('li').eq(2).addClass('current').find('a').addClass('current');
                });})(jQuery);
            </script>
            <?php
        }

    });
	
}
<?php
defined('ABSPATH') || die();
/**
 * This class adds new control panel.
 *
 * @package JupiterX_Core\Control_Panel_2
 *
 * @since 1.18.0
 */

use JupiterX_Core\Raven\Plugin;
use Elementor\Plugin as Elementor;

/**
 * New control panel.
 *
 * @package JupiterX_Core\Control_Panel_2
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 1.18.0
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class JupiterX_Control_Panel_2
{

	const SCREEN_ID = 'jupiterx';

	/**
	 * Components store.
	 *
	 * @since 1.18.0
	 *
	 * @var array
	 */
	private $components = [];

	/**
	 * Components store.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	private static $referrer = null;

	/**
	 * Constructor.
	 *
	 * @since 1.18.0
	 */
	public function __construct()
	{
		jupiterx_core()->load_files([
			'control-panel-2/includes/class-promotion-banner',
		]);

		add_action('admin_init', [$this, 'init']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action('admin_menu', [$this, 'register_admin_menu']);
		add_action('in_admin_header', [$this, 'register_popups_callback']);
		add_action('parent_file', [$this, 'keep_menu_open_popups']);
	}

	/**
	 * Initialize.
	 *
	 * @since 1.18.0
	 */
	public function init()
	{
		jupiterx_core()->load_files([
			'control-panel-2/includes/logic-messages',
			'control-panel-2/includes/class-helpers',
			'control-panel-2/includes/class-filesystem',
			'control-panel-2/includes/class-db-php-manager',
			'control-panel-2/includes/class-export-import-content',
			'control-panel-2/includes/class-install-template',
			'control-panel-2/includes/class-license',
			'control-panel-2/includes/class-theme-upgrades-downgrades',
			'control-panel-2/includes/class-install-plugins',
			'control-panel-2/includes/class-updates-manager',
			'control-panel-2/includes/class-templates',
			'control-panel-2/includes/class-settings',
			'control-panel-2/includes/class-version-control',
			'control-panel-2/includes/class-image-sizes',
			'control-panel-2/includes/class-logs',
			'control-panel-2/includes/class-layout-builder',
			'control-panel-2/includes/class-custom-snippets',
			'control-panel-2/includes/class-custom-fonts',
			'control-panel-2/includes/custom-icons/class-custom-icons',
			'control-panel-2/includes/custom-icons/icon-sets/icon-set-base',
			'control-panel-2/includes/class-sellkit-box',
			'control-panel-2/includes/class-popup',
			'control-panel-2/includes/class-floating-elements',
			'control-panel-2/includes/class-integrations',
			'control-panel-2/includes/class-home',
			'control-panel-2/includes/class-enable-widgets',
			'control-panel-2/includes/setup-wizard/class-condition-generator',
			'control-panel-2/includes/setup-wizard/class-setup-wizard',
			'control-panel-2/includes/class-theme-update',
			'control-panel-2/includes/class-multisite-maintenance',
			'control-panel-2/includes/class-elementor-role-manager',
			'control-panel-2/includes/class-elementor-settings-bridge',
		]);

		JupiterX_Core_Control_Panel_Elementor_Role_Manager::get_instance();
		JupiterX_Core_Control_Panel_Elementor_Settings_Bridge::get_instance();

		$this->components['license']   = JupiterX_Core_Control_Panel_License::get_instance();
		$this->components['templates'] = JupiterX_Core_Control_Panel_Templates::get_instance();
		$this->components['logs']      = JupiterX_Core_Control_Panel_logs::get_instance();

		if ($this->is_current_screen()) {
			$this->back_compat();
		}
	}

	/**
	 * Run backward compatibility actions.
	 */
	private function back_compat()
	{
		$this->components['license']->retry_api_key();
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.18.0
	 */
	public function enqueue_admin_scripts()
	{
		if (! $this->is_current_screen()) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'jupiterx-control-panel-2',
			jupiterx_core()->plugin_url() . 'includes/control-panel-2/dist/control-panel.js',
			['lodash', 'wp-element', 'wp-i18n', 'wp-util'],
			jupiterx_core()->version(),
			true
		);

		wp_localize_script(
			'jupiterx-control-panel-2',
			'jupiterxControlPanel2',
			$this->get_localize_data()
		);

		wp_enqueue_style(
			'jupiterx-control-panel-2',
			jupiterx_core()->plugin_url() . 'includes/control-panel-2/dist/control-panel.css',
			[],
			jupiterx_core()->version()
		);

		wp_set_script_translations('jupiterx-control-panel-2', 'jupiterx-core', jupiterx_core()->plugin_dir() . 'languages');
	}

	/**
	 * Register admin menu.
	 *
	 * @since 1.18.0
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function register_admin_menu()
	{
		if (! defined('JUPITERX_NAME')) {
			return;
		}

		$menu_icon = 'dashicons-jx-dashboard';

		if (function_exists('jupiterx_is_white_label')) {
			if (jupiterx_is_white_label() && jupiterx_get_option('white_label_menu_icon')) {
				$menu_icon = jupiterx_get_option('white_label_menu_icon');
			}
		}

		$menu_name = JUPITERX_NAME;

		if (function_exists('jupiterx_is_white_label')) {
			if (jupiterx_is_white_label() && jupiterx_get_option('white_label_text_occurence')) {
				$menu_name = esc_html(jupiterx_get_option('white_label_text_occurence'));
			}
		}

		add_menu_page(
			$menu_name,
			$menu_name,
			'manage_options',
			self::SCREEN_ID,
			[$this, 'register_admin_menu_callback'],
			$menu_icon,
			'3.5'
		);

		if ($this->is_white_label_cpanel_item('home')) {
			add_submenu_page(
				self::SCREEN_ID,
				__('Home', 'jupiterx-core'),
				__('Home', 'jupiterx-core') . $this->warning_badge(),
				'edit_theme_options',
				self::SCREEN_ID,
				[$this, 'register_admin_menu_callback']
			);
		} else {
			add_action('admin_menu', [$this, 'remove_jupiterx_home_submenu'], 999);
		}

		if (jupiterx_is_premium()) {
			if ($this->layout_builder_wp_admin_visible()) {
				$lb_first_slug = $this->get_first_visible_layout_builder_template_slug();
				add_submenu_page(
					self::SCREEN_ID,
					esc_html__('Layout Builder', 'jupiterx-core'),
					esc_html__('Layout Builder', 'jupiterx-core'),
					'edit_theme_options',
					'jupiterx#/layout-builder/' . $lb_first_slug,
					[$this, 'register_admin_menu_callback']
				);
			}

			if ($this->is_white_label_cpanel_item('custom-elements::custom-snippets')) {
				add_submenu_page(
					self::SCREEN_ID,
					esc_html__('Custom Snippets', 'jupiterx-core'),
					esc_html__('Custom Snippets', 'jupiterx-core'),
					'edit_theme_options',
					'jupiterx#/custom-snippets',
					[$this, 'register_admin_menu_callback']
				);
			}
		}

		if ($this->is_white_label_cpanel_item('custom-elements::custom-fonts')) {
			add_submenu_page(
				self::SCREEN_ID,
				esc_html__('Custom Fonts', 'jupiterx-core'),
				esc_html__('Custom Fonts', 'jupiterx-core'),
				'edit_theme_options',
				'jupiterx#/custom-fonts',
				[$this, 'register_admin_menu_callback']
			);
		}

		if ($this->is_white_label_cpanel_item('custom-elements::custom-icons')) {
			add_submenu_page(
				self::SCREEN_ID,
				esc_html__('Custom Icons', 'jupiterx-core'),
				esc_html__('Custom Icons', 'jupiterx-core'),
				'edit_theme_options',
				'jupiterx#/custom-icons',
				[$this, 'register_admin_menu_callback']
			);
		}

		if (class_exists('Elementor\Plugin')) {
			if ($this->is_white_label_cpanel_item('templates::popups')) {
				add_submenu_page(
					self::SCREEN_ID,
					esc_html__('Popups', 'jupiterx-core'),
					esc_html__('Popups', 'jupiterx-core'),
					'publish_posts',
					'jupiterx#/popups',
					'__return_null'
				);
			}

			if (
				$this->is_elementor_floating_elements_available()
				&& $this->is_white_label_cpanel_item('templates::floating-elements')
			) {
				add_submenu_page(
					self::SCREEN_ID,
					esc_html__('Floating Elements', 'jupiterx-core'),
					esc_html__('Floating Elements', 'jupiterx-core'),
					'publish_posts',
					'jupiterx#/floating-elements',
					'__return_null'
				);
			}
		}

		$settings_panel_enabled = ! defined('JUPITERX_CONTROL_PANEL_SETTINGS') || constant('JUPITERX_CONTROL_PANEL_SETTINGS');

		if ($settings_panel_enabled && $this->is_white_label_cpanel_parent_visible('settings')) {
			add_submenu_page(
				self::SCREEN_ID,
				esc_html__('Settings', 'jupiterx-core'),
				esc_html__('Settings', 'jupiterx-core'),
				'edit_theme_options',
				'jupiterx#/settings#general',
				[$this, 'register_admin_menu_callback']
			);
		}

		if (jupiterx_core()->jupiterx_check_setup_wizard()) {
			add_submenu_page(
				self::SCREEN_ID,
				esc_html__('Setup Wizard', 'jupiterx-core'),
				esc_html__('Setup Wizard', 'jupiterx-core'),
				'edit_theme_options',
				'jupiterx-setup-wizard',
				[$this, 'setup_wizard_root']
			);
		}

		if ($this->is_white_label_cpanel_item('maintenance::license')) {
			add_submenu_page(
				self::SCREEN_ID,
				esc_html__('License', 'jupiterx-core'),
				esc_html__('License', 'jupiterx-core'),
				'edit_theme_options',
				'jupiterx#/license',
				[$this, 'register_admin_menu_callback']
			);
		}

		if (function_exists('jupiterx_is_white_label')) {
			if (! jupiterx_is_white_label() || (jupiterx_is_white_label() && jupiterx_get_option('white_label_menu_help', true))) {
				add_submenu_page(
					self::SCREEN_ID,
					__('Help', 'jupiterx-core'),
					__('Help', 'jupiterx-core'),
					'edit_theme_options',
					'jupiterx_help',
					[$this, 'redirect_page']
				);
			}
		}

		if (function_exists('jupiterx_is_pro') && ! jupiterx_is_pro() && ! jupiterx_is_premium()) {
			add_submenu_page(
				self::SCREEN_ID,
				__('Upgrade', 'jupiterx-core'),
				'<i class="jupiterx-icon-pro"></i>' . __('Upgrade', 'jupiterx-core'),
				'edit_theme_options',
				'jupiterx_upgrade',
				[$this, 'redirect_page']
			);
		}

		remove_submenu_page('themes.php', self::SCREEN_ID);
	}

	/**
	 * Remove the default first Jupiter X submenu (same slug as the parent) when Home is hidden in white label.
	 *
	 * Must be public: registered on `admin_menu` and invoked by WordPress outside this class.
	 *
	 * @since 4.10.0
	 */
	public function remove_jupiterx_home_submenu()
	{
		if (! function_exists('jupiterx_is_white_label') || ! jupiterx_is_white_label()) {
			return;
		}

		remove_submenu_page(self::SCREEN_ID, self::SCREEN_ID);
	}

	/**
	 * Get warining badge for premium users.
	 *
	 * @since 1.18.0
	 *
	 * @return string
	 */
	private function warning_badge()
	{
		if (
			! function_exists('jupiterx_is_registered') ||
			! function_exists('jupiterx_is_premium')
		) {
			return '';
		}

		if (! jupiterx_is_premium()) {
			return '';
		}

		if (jupiterx_is_registered()) {
			return '';
		}

		return sprintf(
			' <img class="jupiterx-premium-warning-badge" src="%1$s" alt="%2$s" width="16" height="16">',
			trailingslashit(jupiterx_core()->plugin_assets_url()) . 'images/warning-badge.svg',
			esc_html__('Activate Product', 'jupiterx-core')
		);
	}

	/**
	 * Redirect an admin page.
	 *
	 * @since 1.18.0
	 */
	public function redirect_page()
	{
		if (empty(jupiterx_get('page'))) {
			return;
		}

		if ('customize_theme' === jupiterx_get('page')) {
			wp_safe_redirect(admin_url('customize.php'));
			exit;
		}

		if ('jupiterx_upgrade' === jupiterx_get('page')) {
			wp_safe_redirect(admin_url());
			exit;
		}

		if ('jupiterx_help' === jupiterx_get('page')) {
			wp_safe_redirect('https://help.jupiterx.com/');
			exit;
		}
	}

	/**
	 * Register admin menu callback.
	 *
	 * @since 1.18.0
	 */
	public function register_admin_menu_callback()
	{
?>
		<div id="wrap" class="wrap">
			<h1></h1>
			<div id="jx-cp-root" class="jx-cp"></div>
		</div>
	<?php
	}

	/**
	 * Register admin popup menu callback.
	 *
	 * @since 3.7.0
	 */
	public function register_popups_callback()
	{
		$popup = ! empty($_GET['post_type']) ? htmlspecialchars($_GET['post_type']) : ''; // phpcs:ignore

		if ('jupiterx-popups' !== $popup) {
			return;
		}
	?>
		<div id="jx-popup-root" class="jx-popup"></div>
	<?php
	}

	/**
	 * Add enable widget modal root.
	 *
	 * @since 2.5.0
	 */
	public function enable_widget_root()
	{
	?>
		<div id="jx-enable-widget-root" class="jx-cp"></div>
	<?php
	}

	/**
	 * Add theme update modal root.
	 *
	 * @since 4.0.0
	 */
	public function add_theme_update_modal()
	{
	?>
		<div id="jx-theme-update-root" class="jx-cp"></div>
	<?php
	}

	/**
	 * Add enable widget modal root.
	 *
	 * @since 4.0.0
	 */
	public function setup_wizard_root()
	{
	?>
		<div id="jx-setup-wizard-root" class="jx-cp"></div>
<?php
	}

	/**
	 * Add Popups submenu to Jupiter X menu for keeping menu open.
	 *
	 * @since 3.7.0
	 */
	public function keep_menu_open_popups($parent_file)
	{
		global $current_screen;
		$post_type = $current_screen->post_type;

		if ('jupiterx-popups' === $post_type || 'e-floating-buttons' === $post_type) {
			$parent_file = 'jupiterx';
		}

		return $parent_file;
	}

	/**
	 * Whether Elementor Floating Elements (e-floating-buttons) is available.
	 *
	 * @since 4.16.0
	 *
	 * @return bool
	 */
	private function is_elementor_floating_elements_available()
	{
		return class_exists('Elementor\Plugin') && post_type_exists('e-floating-buttons');
	}

	/**
	 * Get localize data.
	 *
	 * @since 1.18.0
	 */
	private function get_localize_data()
	{
		$jx_settings = get_option('jupiterx', []);
		$version     = wp_get_theme()->get('Version');

		if (is_a(wp_get_theme()->parent(), '\WP_Theme')) {
			$version = wp_get_theme()->parent()->get('Version');
		}

		$data = [
			'nonce' => wp_create_nonce('jupiterx_control_panel'),
			'themeVersion' => $this->get_theme_data('Version'),
			'jupiterxVersion' => JUPITERX_VERSION,
			'urls' => [
				'customize' => admin_url('customize.php'),
				'upgrade' => jupiterx_upgrade_link(),
				'upgradeBanner' => jupiterx_upgrade_link('banner'),
				'upgradeComparison' => jupiterx_upgrade_link('comparison'),
				'siteHealth' => esc_url(admin_url('site-health.php')),
				'controlPanel' => jupiterx_core()->plugin_url() . 'includes/control-panel-2/',
				'controlPanelUrl' => admin_url('admin.php?page=jupiterx'),
				'imgUrl' => jupiterx_core()->plugin_url() . 'includes/control-panel-2/img',
				'siteUrl' => site_url(),
			],
			'installedPlugins' => array_keys(get_plugins()),
			'activePlugins' => array_values(get_option('active_plugins')),
			'options' => get_option('jupiterx', []),
			'postTypes' => array_values(jupiterx_get_custom_post_types('objects')),
			'themeLicense' => $this->components['license']->get_details(),
			'isPremium' => jupiterx_is_premium(),
			'isPro' => jupiterx_is_pro(),
			'searchFilters' => $this->components['templates']->get_filters(),
			'adminAjaxURL' => admin_url('admin-ajax.php'),
			'siteName' => get_bloginfo('name'),
			'debug' => $this->components['logs']->get_info(),
			'tabs' => $this->get_tabs(),
			'isMultilingual' => (function_exists('pll_current_language') || class_exists('SitePress')),
			'layoutTemplates' => JupiterX_Core_Control_Panel_Layout_Builder::layout_templates(),
			'customSnippetsLocations' => JupiterX_Core_Control_Panel_Custom_Snippets::snippet_locations(),
			'elements' => Plugin::get_modules(true),
			'sellkitProActive' => class_exists('Sellkit_Pro'),
			'sellkitFreeActive' => class_exists('Sellkit'),
			'woocommerceActive' => class_exists('Woocommerce'),
			'elementorActive' => class_exists('Elementor\Plugin'),
			'wpmlActive' => defined('ICL_SITEPRESS_VERSION'),
			'welcomeBox' => get_option('jupiterx_dashboard_welcome_box'),
			'sellkitDismiss' => get_user_meta(get_current_user_id(), 'jupiterx_dismiss_sellkit_box', true),
			'popupConditions' => $this->get_popup_conditions(),
			'popupTriggers' => $this->get_popup_triggers(),
			'timezones' => timezone_identifiers_list(),
			'topBar' => class_exists('Elementor\Plugin') ? $this->check_elementor_top_bar() : '',
			'elementorImportSecurity' => get_option('elementor_unfiltered_files_upload', 0),
			'isRequiredPluginsActivated' => defined('ELEMENTOR_VERSION') && class_exists('ACF'),
			'isSetupWizard' => get_option('jupiterx_setup_wizard_done', false),
			'checkSimplicityMode' => jupiterx_core()->check_default_settings(),
			'SimplicityVersion' => version_compare($version, '3.8.0', '<') ? false : true,
			'freshInstall' => ! isset($jx_settings['disable_theme_default_settings']) || version_compare($version, '3.8.0', '<') ? '' : 1,
			'imageSizes' => get_option(JUPITERX_IMAGE_SIZE_OPTION, []),
			'isMultisite' => is_multisite(),
			'canAccessSiteMaintenance' => $this->can_access_site_maintenance(),
		];

		jupiterx_log(
			"[Control Panel] To view Control Panel, the following data is expected to be an array consisting of 'nonce', 'themeVersion', 'urls', ...  'tabs'.",
			$data
		);

		return $data;
	}

	/**
	 * Whether any tracking code fields are saved (non-whitespace).
	 *
	 * Used to keep the Tracking Codes settings subsection visible for sites that
	 * already use snippets while Simplicity Mode hides it for empty installs.
	 *
	 * @since 4.18.0
	 *
	 * @return bool
	 */
	private function has_saved_tracking_codes()
	{
		$jx_settings = get_option('jupiterx', []);

		if (! is_array($jx_settings)) {
			return false;
		}

		$keys = [
			'tracking_codes_after_head',
			'tracking_codes_before_head',
			'tracking_codes_after_body',
			'tracking_codes_before_body',
		];

		foreach ($keys as $key) {
			if (! isset($jx_settings[$key])) {
				continue;
			}

			$value = $jx_settings[$key];

			if (! is_scalar($value)) {
				continue;
			}

			if ('' !== trim(stripslashes((string) $value))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get control panel tabs.
	 *
	 * @since 1.18.0
	 *
	 * @return array
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	private function get_tabs()
	{
		$custom_elements_enabled = $this->is_white_label_cpanel_parent_visible('custom-elements');

		$templates_enabled = $this->is_white_label_cpanel_parent_visible('templates');

		$tabs = [
			'home' => [
				'id' => 'home',
				'href' => '/',
				'label' => __('Home', 'jupiterx-core'),
				'help' =>  'https://help.jupiterx.com/',
				'whiteLabel' => true,
				'subMenu' => false,
				'whiteLabelEnabled' => $this->is_white_label_cpanel_item('home'),
			],
			'templates' => [
				'id' => 'templates',
				'href' => '/layout-builder/' . $this->get_first_visible_layout_builder_template_slug(),
				'label' => __('Layout Builder', 'jupiterx-core'),
				'help' => 'https://help.jupiterx.com/',
				'whiteLabel' => true,
				'subMenu' => true,
				'whiteLabelEnabled' => $templates_enabled,
				'subTabs' => 					array_merge(
					// Layout builder template types become direct sub-tabs.
					array_map(
						function ($key, $label) {
							$sub_id = 'layout-builder-' . $key;

							return [
								'id' => $sub_id,
								'href' => '/layout-builder/' . $key,
								'label' => $label,
								'whiteLabel' => true,
								'whiteLabelKey' => 'templates::' . $sub_id,
								'whiteLabelEnabled' => $this->is_white_label_cpanel_item('templates::' . $sub_id),
							];
						},
						array_keys(JupiterX_Core_Control_Panel_Layout_Builder::layout_templates()),
						array_values(JupiterX_Core_Control_Panel_Layout_Builder::layout_templates())
					),
					array_merge(
						[
							[
								'id' => 'popups',
								'href' => '/popups',
								'label' => __('Popups', 'jupiterx-core'),
								'whiteLabel' => true,
								'whiteLabelKey' => 'templates::popups',
								'whiteLabelEnabled' => $this->is_white_label_cpanel_item('templates::popups'),
							],
						],
						$this->is_elementor_floating_elements_available()
							? [
								[
									'id' => 'floating-elements',
									'href' => '/floating-elements',
									'label' => __('Floating Elements', 'jupiterx-core'),
									'whiteLabel' => true,
									'whiteLabelKey' => 'templates::floating-elements',
									'whiteLabelEnabled' => $this->is_white_label_cpanel_item('templates::floating-elements'),
								],
							]
							: [],
						[
							[
								'id' => 'saved-templates',
								'href' => '/saved-templates',
								'label' => __('Saved Templates', 'jupiterx-core'),
								'whiteLabel' => true,
								'whiteLabelKey' => 'templates::saved-templates',
								'whiteLabelEnabled' => $this->is_white_label_cpanel_item('templates::saved-templates'),
							],
							[
								'id' => 'website-templates',
								'href' => '/website-templates',
								'label' => __('Website Templates', 'jupiterx-core'),
								'whiteLabel' => true,
								'whiteLabelKey' => 'templates::website-templates',
								'whiteLabelEnabled' => $this->is_white_label_cpanel_item('templates::website-templates'),
							],
						]
					)
				),
			],
			'custom-elements' => [
				'id' => 'custom-elements',
				'href' => '/custom-snippets',
				'label' => __('Custom Elements', 'jupiterx-core'),
				'help' => 'https://help.jupiterx.com/',
				'whiteLabel' => true,
				'subMenu' => true,
				'whiteLabelEnabled' => $custom_elements_enabled,
				'subTabs' => [
					'custom-snippets' => [
						'id' => 'custom-snippets',
						'href' => '/custom-snippets',
						'label' => __('Snippets', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'custom-elements::custom-snippets',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('custom-elements::custom-snippets'),
					],
					'custom-icons' => [
						'id' => 'custom-icons',
						'href' => '/custom-icons',
						'label' => __('Icons', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'custom-elements::custom-icons',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('custom-elements::custom-icons'),
					],
					'custom-fonts' => [
						'id' => 'custom-fonts',
						'href' => '/custom-fonts',
						'label' => __('Fonts', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'custom-elements::custom-fonts',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('custom-elements::custom-fonts'),
					],
				],
			],
			'settings' => [
				'id' => 'settings',
				'href' => '/settings',
				'label' => __('Settings', 'jupiterx-core'),
				'help' => 'https://help.jupiterx.com/',
				'whiteLabel' => true,
				'subMenu' => true,
				'whiteLabelEnabled' => $this->is_white_label_cpanel_parent_visible('settings'),
				'subTabs' => [
					'general' => [
						'id' => 'general',
						'href' => '/settings#general',
						'label' => __('Site Settings', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::general',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::general'),
					],
					'elementor' => [
						'id' => 'elementor',
						'href' => '/settings#elementor',
						'label' => __('Elementor', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::elementor',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::elementor'),
					],
					'manage-elements' => [
						'id' => 'manage-elements',
						'href' => '/settings#manage-elements',
						'label' => __('Manage Elements', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::manage-elements',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::manage-elements'),
					],
					'post-types' => [
						'id' => 'post-types',
						'href' => '/settings#post-types',
						'label' => __('Custom Post Types', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::post-types',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::post-types'),
					],
					'white-label' => [
						'id' => 'white-label',
						'href' => '/settings#white-label',
						'label' => __('White Label', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::white-label',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::white-label'),
					],
					'woocommerce' => [
						'id' => 'woocommerce',
						'href' => '/settings#woocommerce',
						'label' => __('WooCommerce', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::woocommerce',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::woocommerce'),
					],
					'integrations' => [
						'id' => 'integrations',
						'href' => '/settings#integrations',
						'label' => __('Integrations', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::integrations',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::integrations'),
					],
					'role-manager' => [
						'id' => 'role-manager',
						'href' => '/settings#role-manager',
						'label' => __('Role Manager', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::role-manager',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::role-manager'),
					],
					'third-party-integration' => [
						'id' => 'third-party-integration',
						'href' => '/settings#third-party-integration',
						'label' => __('Third-Party Integration', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::third-party-integration',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::third-party-integration'),
					],
					'tracking-codes' => [
						'id' => 'tracking-codes',
						'href' => '/settings#tracking-codes',
						'label' => __('Tracking Codes', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::tracking-codes',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::tracking-codes'),
					],
					'image-sizes' => [
						'id' => 'image-sizes',
						'href' => '/settings#image-sizes',
						'label' => __('Image Sizes', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'settings::image-sizes',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('settings::image-sizes'),
					],

				],
			],
			'maintenance' => [
				'id' => 'maintenance',
				'href' => '/maintenance',
				'label' => __('Maintenance', 'jupiterx-core'),
				'help' => 'https://help.jupiterx.com/',
				'whiteLabel' => true,
				'subMenu' => true,
				'whiteLabelEnabled' => $this->is_white_label_cpanel_parent_visible('maintenance'),
				'subTabs' => [
					'site-maintenance' => [
						'id' => 'site-maintenance',
						'href' => '/site-maintenance',
						'label' => __('Cache', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::site-maintenance',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::site-maintenance'),
					],
					'elementor-performance' => [
						'id' => 'elementor-performance',
						'href' => '/elementor-performance',
						'label' => __('Performance', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::elementor-performance',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::elementor-performance'),
					],
					'elementor-maintenance' => [
						'id' => 'elementor-maintenance',
						'href' => '/elementor-maintenance',
						'label' => __('Maintenance mode', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::elementor-maintenance',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::elementor-maintenance'),
					],
					'elementor-replace-url' => [
						'id' => 'elementor-replace-url',
						'href' => '/elementor-replace-url',
						'label' => __('Replace URL', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::elementor-replace-url',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::elementor-replace-url'),
					],
					'license' => [
						'id' => 'license',
						'href' => '/license',
						'label' => __('License', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::license',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::license'),
					],
					'updates' => [
						'id' => 'updates',
						'href' => '/updates',
						'label' => __('Updates', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::updates',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::updates'),
					],
					'version-rollback' => [
						'id' => 'version-rollback',
						'href' => '/version-rollback',
						'label' => __('Version Rollback', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::version-rollback',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::version-rollback'),
					],
					'logs' => [
						'id' => 'logs',
						'href' => '/logs',
						'label' => __('Logs', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::logs',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::logs'),
					],
					'export' => [
						'id' => 'export',
						'href' => '/export',
						'label' => __('Export', 'jupiterx-core'),
						'whiteLabel' => true,
						'whiteLabelKey' => 'maintenance::export',
						'whiteLabelEnabled' => $this->is_white_label_cpanel_item('maintenance::export'),
					],
				],
			],
			'free-vs-pro' => [
				'id' => 'freeVsPro',
				'href' => '/free-vs-pro',
				'label' => __('Free Vs Pro', 'jupiterx-core'),
				'whiteLabel' => true,
				'subMenu' => false,
				'whiteLabelEnabled' => $this->is_white_label_cpanel_item('freeVsPro'),
			],
		];

		// Hide Site Health for WP under 5.2.
		if (version_compare(get_bloginfo('version'), '5.2', '<')) {
			unset($tabs['site-health']);
		}

		// Hide Elementor for now.
		unset($tabs['elementor']);

		// Hide Tools > Export if constant is not defined.
		if (! $this->show_tab('JUPITERX_CONTROL_PANEL_EXPORT_IMPORT')) {
			unset($tabs['maintenance']['subTabs']['export']);
		}

		// Hide Free Vs Pro on premium theme.
		if (jupiterx_is_premium()) {
			unset($tabs['free-vs-pro']);
		}

		// Hide settings > third party integration if constant is not defined.
		if (! is_plugin_active('jupiter-donut/jupiter-donut.php')) {
			unset($tabs['settings']['subTabs']['third-party-integration']);
		}

		unset($tabs['settings']['subTabs']['post-types']);

		// Hide Tracking Codes under Simplicity Mode unless snippets already exist.
		if (jupiterx_core()->check_default_settings() && ! $this->has_saved_tracking_codes()) {
			unset($tabs['settings']['subTabs']['tracking-codes']);
		}

		unset($tabs['settings']['subTabs']['image-sizes']);

		if (! function_exists('WC')) {
			unset($tabs['settings']['subTabs']['woocommerce']);
		}

		if (! class_exists('Elementor\Plugin')) {
			unset($tabs['templates']['subTabs']['saved-templates']);
		}

		if (! class_exists('Elementor\Plugin')) {
			unset($tabs['templates']['subTabs']['popups']);
			unset($tabs['settings']['subTabs']['role-manager']);
			unset($tabs['settings']['subTabs']['elementor']);
			unset($tabs['maintenance']['subTabs']['elementor-performance']);
			unset($tabs['maintenance']['subTabs']['elementor-maintenance']);
			unset($tabs['maintenance']['subTabs']['elementor-replace-url']);
		}

		if (! jupiterx_is_premium()) {
			unset($tabs['templates']['subTabs']['layout-builder']);
			unset($tabs['custom-elements']['subTabs']['custom-snippets']);
			unset($tabs['free-vs-pro']);
		}

		if (empty($tabs['custom-elements']['subTabs'])) {
			unset($tabs['custom-elements']);
		}

		if (empty($tabs['templates']['subTabs'])) {
			unset($tabs['templates']);
		}

		if (defined('JUPITERX_CONTROL_PANEL_SETTINGS') && ! constant('JUPITERX_CONTROL_PANEL_SETTINGS')) {
			unset($tabs['settings']);
		}

		if (! $this->can_access_site_maintenance()) {
			unset($tabs['maintenance']['subTabs']['site-maintenance']);
		}

		return array_values($tabs);
	}

	/**
	 * Whether the current user may use Site / Network Maintenance tools.
	 *
	 * Multisite: super admins only. Single site: administrators with manage_options.
	 *
	 * @since 4.8.0
	 *
	 * @return bool
	 */
	private function can_access_site_maintenance()
	{
		if (is_multisite()) {
			return is_super_admin();
		}

		return current_user_can('manage_options');
	}

	/**
	 * Get current theme data.
	 *
	 * @since 1.18.0
	 *
	 * @param string $data The theme data.
	 */
	private function get_theme_data($data)
	{
		$current_theme = wp_get_theme();

		return $current_theme->get($data);
	}

	/**
	 * Check current screen.
	 *
	 * @since 1.18.0
	 *
	 * @return boolean Control panel screen.
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	private function is_current_screen()
	{
		$current_page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if ('jupiterx-setup-wizard' === $current_page) {
			return true;
		}

		$page  = ! empty($_GET['page']) ? htmlspecialchars($_GET['page']) : ''; // phpcs:ignore
		$popup = ! empty($_GET['post_type']) ? htmlspecialchars($_GET['post_type']) : ''; // phpcs:ignore

		if (! is_admin()) {
			return false;
		}

		if (self::SCREEN_ID === $page || 'jupiterx-popups' === $popup) {
			return true;
		}

		$post = ! empty($_GET['post']) && ! is_array($_GET['post']) ? htmlspecialchars($_GET['post']) : ''; // phpcs:ignore

		if (empty(self::$referrer)) {
			self::$referrer = $post;
		}

		$post_type = get_post_type(self::$referrer);

		if ('jupiterx-popups' === $post_type) {
			return true;
		}

		if (! is_array(jupiterx_get_option('elements'))) {
			jupiterx_update_option('first_installation_after_250', true);
		}

		if (
			function_exists('jupiterx_get_option') &&
			'deleted' !== jupiterx_get_option('enable_widgets_reminder') &&
			time() > jupiterx_get_option('enable_widgets_reminder') &&
			! jupiterx_get_option('first_installation_after_250')
		) {
			add_action('admin_footer', [$this, 'enable_widget_root']);

			return true;
		}

		if ('show' === get_option('jupiterx_theme_update_modal', '')) {
			add_action('admin_footer', [$this, 'add_theme_update_modal']);

			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.NoNonceVerification
		return is_admin() && isset($_GET['page']) && self::SCREEN_ID === $_GET['page'];
	}

	/**
	 * Get show tab.
	 *
	 * @param string $constant Constant name.
	 *
	 * @return boolean Tab show.
	 */
	private function show_tab($constant)
	{
		return defined($constant) && constant($constant);
	}


	/**
	 * Get popup conditions.
	 *
	 * @since 3.7.0
	 */
	private function get_popup_conditions()
	{
		if (! class_exists('Elementor\Plugin')) {
			return;
		}

		$post_type = (new JupiterX_Popups())->get_post_type_name();

		if ('jupiterx-popups' !== $post_type || ! class_exists('JupiterX_Popups_Conditions_Manager')) {
			return;
		}

		return JupiterX_Popups_Conditions_Manager::$control_panel;
	}

	/**
	 * Get popup triggers.
	 *
	 * @since 3.7.0
	 */
	private function get_popup_triggers()
	{
		if (! class_exists('Elementor\Plugin')) {
			return;
		}

		$post_type = (new JupiterX_Popups())->get_post_type_name();

		if ('jupiterx-popups' !== $post_type || ! class_exists('JupiterX_Popups_Triggers_Manager')) {
			return;
		}

		return JupiterX_Popups_Triggers_Manager::$control_panel;
	}

	/**
	 * Whether Jupiter X white label mode is enabled (theme option).
	 *
	 * @return bool
	 */
	private function is_white_label_active()
	{
		return function_exists('jupiterx_is_white_label') && jupiterx_is_white_label();
	}

	/**
	 * Raw saved list of visible control panel areas (legacy top-level ids and/or granular keys).
	 *
	 * @return array<int, string>|null Null when the option has never been saved as an array.
	 */
	private function get_white_label_cpanel_pages_raw()
	{
		$pages = jupiterx_get_option('white_label_cpanel_pages');

		return is_array($pages) ? $pages : null;
	}

	/**
	 * Whether saved white label pages use granular `parent::child` keys.
	 *
	 * @param array<int, string> $pages Pages list.
	 * @return bool
	 */
	private function uses_granular_white_label_keys(array $pages)
	{
		foreach ($pages as $p) {
			if (is_string($p) && strpos($p, '::') !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Show Layout Builder in WP admin submenu if any layout template type is allowed.
	 *
	 * @return bool
	 */
	private function layout_builder_wp_admin_visible()
	{
		if (! $this->is_white_label_active()) {
			return true;
		}

		$pages = $this->get_white_label_cpanel_pages_raw();

		if (! is_array($pages)) {
			return true;
		}

		if (! $pages) {
			return false;
		}

		if ($this->uses_granular_white_label_keys($pages)) {
			foreach ($pages as $p) {
				if (is_string($p) && strpos($p, 'templates::layout-builder-') === 0) {
					return true;
				}
			}

			return false;
		}

		return in_array('templates', $pages, true);
	}

	/**
	 * Load layout builder when needed before admin_init.
	 *
	 * WordPress builds the admin menu (admin_menu) while loading menu.php, which runs
	 * before admin_init. Our init() only loads class-layout-builder on admin_init, so
	 * register_admin_menu must load this dependency explicitly.
	 *
	 * @return void
	 */
	private function ensure_layout_builder_class_loaded()
	{
		if (class_exists('JupiterX_Core_Control_Panel_Layout_Builder', false)) {
			return;
		}

		jupiterx_core()->load_files([
			'control-panel-2/includes/class-layout-builder',
		]);
	}

	/**
	 * First layout template slug visible under white label (same order as layout_templates()).
	 *
	 * Used for the Layout Builder WP admin link and the templates tab default href so users do not land on a disabled type (e.g. Header).
	 *
	 * @return string Slug such as header, footer, page-title-bar.
	 */
	private function get_first_visible_layout_builder_template_slug()
	{
		$this->ensure_layout_builder_class_loaded();

		$sections = JupiterX_Core_Control_Panel_Layout_Builder::layout_templates();

		foreach (array_keys($sections) as $key) {
			if ($this->is_white_label_cpanel_item('templates::layout-builder-' . $key)) {
				return $key;
			}
		}

		$keys = array_keys($sections);

		return ! empty($keys) ? $keys[0] : 'header';
	}

	/**
	 * Whether a top-level sidebar group (e.g. templates, settings) should appear.
	 *
	 * @param string $parent_id Tab id: templates, settings, maintenance, custom-elements, etc.
	 * @return bool
	 */
	private function is_white_label_cpanel_parent_visible($parent_id)
	{
		if (! $this->is_white_label_active()) {
			return true;
		}

		$pages = $this->get_white_label_cpanel_pages_raw();

		if (! is_array($pages)) {
			return true;
		}

		if (! $pages) {
			return false;
		}

		if ($this->uses_granular_white_label_keys($pages)) {
			if (in_array($parent_id, $pages, true)) {
				return true;
			}

			foreach ($pages as $p) {
				if (is_string($p) && strpos($p, $parent_id . '::') === 0) {
					return true;
				}
			}

			return false;
		}

		// Legacy top-level list only: parent must be explicitly allowed (no Settings/License bypass).
		return in_array($parent_id, $pages, true);
	}

	/**
	 * Whether a control panel route is visible under white label (granular key or legacy id).
	 *
	 * Keys use `parent::child` (e.g. `settings::general`, `templates::popups`) or single ids (`home`, `freeVsPro`).
	 *
	 * @param string $key Item key.
	 * @return bool
	 */
	private function is_white_label_cpanel_item($key)
	{
		if (! $this->is_white_label_active()) {
			return true;
		}

		$pages = $this->get_white_label_cpanel_pages_raw();

		// Not saved yet: show everything (matches SPA default).
		if (! is_array($pages)) {
			return true;
		}

		if (! $pages) {
			return false;
		}

		if ($this->uses_granular_white_label_keys($pages)) {
			if (in_array($key, $pages, true)) {
				return true;
			}

			$parts = explode('::', $key, 2);

			if (count($parts) === 2) {
				return in_array($parts[0], $pages, true);
			}

			return false;
		}

		return $this->legacy_white_label_cpanel_item_visible($key, $pages);
	}

	/**
	 * Legacy white label list only stored top-level tab ids (home, templates, custom-elements, maintenance).
	 *
	 * @param string               $key   Item key.
	 * @param array<int, string> $pages Saved list.
	 * @return bool
	 */
	private function legacy_white_label_cpanel_item_visible($key, $pages)
	{
		// Free vs Pro was not in the legacy UI; keep visible unless granular save adds it.
		if ('freeVsPro' === $key) {
			return true;
		}

		// Legacy: settings subtabs follow the "settings" top-level id (same as templates::*, etc.).
		if (0 === strpos($key, 'settings::')) {
			return in_array('settings', $pages, true);
		}

		if ('home' === $key) {
			return in_array('home', $pages, true) || in_array('dashboard', $pages, true);
		}

		if ('maintenance::license' === $key) {
			return in_array('maintenance', $pages, true)
				|| in_array('dashboard', $pages, true);
		}

		if (false !== strpos($key, '::')) {
			[$parent] = explode('::', $key, 2);

			if ('templates' === $parent) {
				return in_array('templates', $pages, true);
			}

			if ('custom-elements' === $parent) {
				return in_array('custom-elements', $pages, true);
			}

			if ('maintenance' === $parent) {
				return in_array('maintenance', $pages, true);
			}

			return true;
		}

		return in_array($key, $pages, true);
	}

	/**
	 * Check if Elementor top bar is active.
	 *
	 * @return bool Whether the top bar feature is active.
	 */
	private function check_elementor_top_bar()
	{
		// Ensure Elementor is loaded
		if (! defined('ELEMENTOR_VERSION')) {
			return false;
		}

		// Top bar was merged into core and enabled by default in v3.30+
		if (version_compare(ELEMENTOR_VERSION, '3.30', '>=')) {
			return true;
		}

		// For versions before 3.30, check if the experiment is active
		if (isset(Elementor::$instance->experiments)) {
			$experiments = Elementor::$instance->experiments;

			// Check for top bar experiment
			if (
				method_exists($experiments, 'is_feature_active')
				&& $experiments->is_feature_active('editor_v2')
			) {
				return true;
			}
		}

		return false;
	}
}

new JupiterX_Control_Panel_2();

<?php

namespace JupiterX_Core\Raven\Modules\Elementor_Ads;

defined('ABSPATH') || die();

use JupiterX_Core\Raven\Base\Module_base;
use Elementor\Plugin;
use Elementor\Utils as ElementorUtils;
use Elementor\Core\Kits\Documents\Kit;
use JupiterX_Core\Raven\Plugin as Raven;

/**
 * Handle elementor ads and hide or disable them.
 * Some of features will be disabled if Elementor pro is activated.
 *
 * @since 2.5.0
 */
class Module extends Module_Base
{
	/**
	 * Class construct.
	 *
	 * @since 2.5.0
	 */
	public function __construct()
	{
		$this->actions();
	}

	/**
	 * Whether Elementor Pro is active — all ads-suppression must stay off when true.
	 *
	 * @since 4.7.0
	 */
	private static function is_elementor_pro_active(): bool
	{
		return class_exists('\ElementorPro\Plugin') || function_exists('elementor_pro_load_plugin');
	}

	/**
	 * Actions.
	 * Runs only when Elementor Pro is not active (see `is_elementor_pro_active()`).
	 *
	 * @since 2.5.0
	 */
	private function actions()
	{
		if (self::is_elementor_pro_active()) {
			return;
		}

		add_action('elementor/editor/after_enqueue_scripts', [$this, 'elementor_editor_assets']);
		add_action('elementor/editor/after_enqueue_scripts', [$this, 'dequeue_promotion_scripts'], 999);
		add_action('elementor/preview/enqueue_scripts', [$this, 'preview_scripts']);
		add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
		add_action('elementor/app/init', [$this, 'admin_assets'], 9999);
		add_action('elementor/admin/menu/after_register', [$this, 'remove_elementor_pro_related_menu_items'], 10, 1);
		add_action('admin_menu', [$this, 'remove_upgrade_admin_submenu'], 10005);
		add_action('admin_menu', [$this, 'restructure_elementor_wordpress_menu'], 9990);
		add_filter('custom_menu_order', '__return_true');
		add_filter('menu_order', [$this, 'move_elementor_menu_after_plugins'], 99);
		add_filter('plugin_action_links_' . ELEMENTOR_PLUGIN_BASE, [$this, 'remove_go_pro_from_plugin_page'], 999, 1);
		add_filter('elementor/frontend/admin_bar/settings', [$this, 'remove_theme_builder_from_admin_bar'], 99);
		add_action('elementor/admin-top-bar/init', [$this, 'elementor_admin_top_bar'], 10, 1);
		add_action('admin_print_scripts-toplevel_page_elementor', [$this, 'elementor_homepage']);

		// Remove the Components tab from the editor elements panel (it's a Pro upsell).
		add_filter('elementor/editor/v2/packages', [$this, 'remove_components_package'], 99);

		// Intercept editorOneSidebarConfig before React mounts to suppress upgrade CTA.
		add_action('admin_head', [$this, 'suppress_sidebar_upgrade_cta'], 1);

		// Suppress the editor upgrade notice bar by keeping the dismissed option current.
		add_action('admin_init', [$this, 'suppress_upgrade_notice_bar']);

		// Remove v4 promotions and promotion widgets from editor localized settings.
		add_filter('elementor/editor/localize_settings', [$this, 'remove_promotions_from_localized_settings'], 99);

		// Blank out the sidebar "Upgrade plan" link (Editor One).
		add_filter('elementor/sidebar/promotion', [$this, 'blank_promotion_url'], 99);

		// Blank out the site editor (Theme Builder) promo upgrade URL.
		add_filter('elementor/site-editor/promotion', [$this, 'blank_promotion_url'], 99);

		// Remove Pro-feature capability cards from the Elementor home screen.
		add_filter('elementor/core/admin/homescreen', [$this, 'filter_home_screen_data'], 99);

		// MutationObserver to strip dynamically-rendered capability/upgrade cards.
		add_action('admin_head', [$this, 'suppress_home_screen_capability_cards'], 1);

		// Replace Pro-only links in the Editor-One sidebar navigation with JupiterX equivalents.
		add_action('admin_head', [$this, 'replace_editor_sidebar_pro_links'], 1);

		// Disable Elementor AI for all users (priority 0 → fires before the AI module reads it).
		add_filter('get_user_option_elementor_enable_ai', '__return_false', 0);

		// Treat the "Connect account" alert as permanently dismissed — no DB write needed.
		add_filter('pre_option_elementor_one_dismiss_connect_alert', '__return_true');

		// Dequeue AI scripts after the editor enqueues them.
		add_action('elementor/editor/after_enqueue_scripts', [$this, 'dequeue_ai_scripts'], 9999);
		add_action('admin_enqueue_scripts', [$this, 'dequeue_admin_ai_scripts'], 9999);
	}

	/**
	 * Elementor homepage.
	 *
	 * @since 4.5.0
	 * @return void
	 */
	public function elementor_homepage()
	{
		if (! current_user_can('manage_options') || ! Plugin::$instance->experiments->is_feature_active('home_screen')) {
			return;
		}

		$suffix = ElementorUtils::is_script_debug() ? '' : '.min';

		wp_enqueue_style(
			'jx-free-elementor-integrate-style',
			jupiterx_core()->plugin_url() . 'includes/extensions/raven/assets/css/elementor-ads' . $suffix . '.css',
			[],
			jupiterx_core()->version()
		);
	}

	/**
	 * Remove theme builder link from admin bar.
	 *
	 * @since 2.5.0
	 */
	public function remove_theme_builder_from_admin_bar($config)
	{
		foreach ($config['elementor_edit_page']['children'] as $key => $value) {
			if ('elementor_app_site_editor' === $value['id']) {
				unset($config['elementor_edit_page']['children'][$key]);
			}
		}

		return $config;
	}

	/**
	 * Remove Elementor pro menus.
	 *
	 * @since 2.5.0
	 */
	public function remove_elementor_pro_related_menu_items($class)
	{
		$to_delete = [
			'go_elementor_pro',
			'elementor_custom_custom_code',
			'elementor_custom_icons',
			'elementor_custom_fonts',
			'e-form-submissions',
			'popup_templates',
			'go_knowledge_base_site',
			'elementor_custom_code',
			'elementor-getting-started',
		];

		foreach ($class->get_all() as $item_slug => $item) {
			if (in_array($item_slug, $to_delete, true)) {
				remove_submenu_page($item->get_parent_slug(), $item_slug);
			}
		}
	}

	/**
	 * Required editor assets.
	 *
	 * @since 2.5.0
	 */
	public function elementor_editor_assets()
	{
		$suffix = ElementorUtils::is_script_debug() ? '' : '.min';

		wp_enqueue_style(
			'jx-free-elementor-integrate-style',
			jupiterx_core()->plugin_url() . 'includes/extensions/raven/assets/css/elementor-ads' . $suffix . '.css',
			[],
			jupiterx_core()->version()
		);

		wp_enqueue_script(
			'jx-free-elementor-integrate-editor-js',
			jupiterx_core()->plugin_url() . 'includes/extensions/raven/assets/js/elementor-ads' . $suffix . '.js',
			[ 'jquery' ],
			jupiterx_core()->version(),
			true
		);

		$option = Raven::get_modules();

		if (in_array('global-widget', $option, true)) {
			return;
		}

		wp_add_inline_style('jx-free-elementor-integrate-style', '.elementor-panel-navigation > [data-tab="global"] { display:none !important; }');
	}

	/**
	 * Preview script.
	 *
	 * @since 2.5.0
	 */
	public function preview_scripts()
	{
		$suffix = ElementorUtils::is_script_debug() ? '' : '.min';

		wp_enqueue_script(
			'jx-free-elementor-integrate-preview-js',
			jupiterx_core()->plugin_url() . 'includes/extensions/raven/assets/js/elementor-ads' . $suffix . '.js',
			['jquery'],
			jupiterx_core()->version(),
			true
		);
	}

	/**
	 * Admin assets.
	 *
	 * @since 2.5.0
	 */
	public function admin_assets()
	{
		$suffix = ElementorUtils::is_script_debug() ? '' : '.min';

		wp_enqueue_script(
			'jx-free-elementor-integrate-admin-js',
			jupiterx_core()->plugin_url() . 'includes/extensions/raven/assets/js/elementor-ads' . $suffix . '.js',
			['jquery'],
			jupiterx_core()->version(),
			true
		);

		wp_enqueue_style(
			'jx-free-elementor-integrate-style-admin',
			jupiterx_core()->plugin_url() . 'includes/extensions/raven/assets/css/elementor-ads' . $suffix . '.css',
			[],
			jupiterx_core()->version()
		);
	}

	/**
	 * Unset go pro link.
	 *
	 * @param array $links
	 * @since 2.5.0
	 * @return array
	 */
	public function remove_go_pro_from_plugin_page($links)
	{
		unset($links['go_pro']);
		return $links;
	}

	/**
	 * Admin top bar.
	 *
	 * @param object $admin_top_bar top bar object.
	 * @since 4.3.0
	 */
	public function elementor_admin_top_bar($admin_top_bar)
	{
		$settings = $admin_top_bar->get_settings();

		unset($settings['apps_url']);
		unset($settings['connect_url']);
		unset($settings['promotion']);

		$admin_top_bar->set_settings($settings);
	}

	/**
	 * Keep the editor upgrade notice bar dismissed indefinitely.
	 *
	 * @since 4.7.0
	 */
	public function suppress_upgrade_notice_bar()
	{
		$option_key  = '_elementor_editor_upgrade_notice_dismissed';
		$future_time = strtotime('+365 day');
		$stored      = (int) get_option($option_key, 0);

		// Only write when the stored value is about to expire (less than 30 days away).
		if ($stored < strtotime('+30 day')) {
			update_option($option_key, $future_time);
		}
	}

	/**
	 * Remove v4 promotions data from editor localized settings.
	 *
	 * @param array $settings Elementor editor settings.
	 * @since 4.7.0
	 * @return array
	 */
	public function remove_promotions_from_localized_settings(array $settings): array
	{
		unset($settings['v4Promotions']);
		unset($settings['promotionWidgets']);
		unset($settings['promotion']);

		return $settings;
	}

	/**
	 * Dequeue Elementor's React-based promotion scripts and the Components tab bundle.
	 *
	 * @since 4.7.0
	 */
	public function dequeue_promotion_scripts()
	{
		wp_dequeue_script('e-react-promotions');
		wp_dequeue_script('editor-v4-opt-in-alphachip');
		// Components tab is a Pro-upsell; dequeue so the tab never registers.
		wp_dequeue_script('editor-components');
	}

	/**
	 * Remove the 'editor-components' package from Elementor V2 packages list.
	 * This prevents the Components tab from appearing in the editor elements panel.
	 *
	 * @param array $packages Array of editor V2 package handles.
	 * @since 4.7.0
	 * @return array
	 */
	public function remove_components_package(array $packages): array
	{
		return array_values(array_filter($packages, fn($p) => $p !== 'editor-components'));
	}

	/**
	 * Output a <script> in <head> (priority 1 — before any wp_localize_script data)
	 * that intercepts the editorOneSidebarConfig assignment via Object.defineProperty.
	 * This guarantees the "Upgrade plan" CTA never renders, regardless of WP script
	 * queue ordering.
	 *
	 * @since 4.7.0
	 */
	public function suppress_sidebar_upgrade_cta()
	{
?>
		<script>
			(function() {
				var _jxSidebarCfg;
				Object.defineProperty(window, 'editorOneSidebarConfig', {
					configurable: true,
					enumerable: true,
					set: function(v) {
						if (v && typeof v === 'object') {
							v.hasPro = true;
							v.upgradeUrl = '';
							v.upgradeText = '';
						}
						_jxSidebarCfg = v;
					},
					get: function() {
						return _jxSidebarCfg;
					},
				});
			})();
		</script>
	<?php
	}

	/**
	 * Inject a MutationObserver via <head> that removes capability/upgrade cards
	 * rendered by the Elementor home-screen React app.
	 *
	 * Strategy: start from the "Upgrade" button itself and walk UP the DOM tree
	 * to hide only its enclosing grid column — nothing else is touched.
	 *
	 * Two independent signals are used so the removal fires whichever one appears
	 * in the DOM first:
	 *   1. A <button> with MUI's Promotion colour variant (the crown "Upgrade" button).
	 *   2. A card element whose data-test attribute starts with "one-capabilities-card-".
	 *
	 * @since 4.7.0
	 */
	public function suppress_home_screen_capability_cards()
	{
	?>
		<script>
			(function() {
				function jxHideUpgradeCards(root) {
					if (!root || !root.querySelectorAll) {
						return;
					}

					/*
					 * Target only cards that carry the MUI "Promotion" colour variant —
					 * that is the crown "Upgrade" button Elementor uses exclusively for
					 * Pro-gated features.  Free/secondary buttons use different variants
					 * (e.g. MuiButton-colorSecondary) and must NOT be touched.
					 */
					var upgradeBtns = root.querySelectorAll(
						'button.MuiButton-colorPromotion,' +
						'button.MuiButton-outlinedPromotion,' +
						'button.MuiButton-containedPromotion'
					);
					Array.prototype.forEach.call(upgradeBtns, function(btn) {
						var col = btn.closest('.MuiGrid-item');
						if (col) {
							col.style.setProperty('display', 'none', 'important');
						}
					});

					/* "Get Hello Elementor" theme-switch prompt in the home-screen header */
					var helloBtn = root.querySelector('[data-test="one-home-get-hello-elementor-button"]');
					if (helloBtn) {
						helloBtn.style.setProperty('display', 'none', 'important');
					}

					/* AI site-creation section ("Create and launch your site faster with AI") */
					var aiInputs = root.querySelectorAll('input[placeholder*="site you want to create"]');
					Array.prototype.forEach.call(aiInputs, function(input) {
						var paper = input.closest('.MuiPaper-root');
						if (paper) {
							paper.style.setProperty('display', 'none', 'important');
						}
					});

					/*
					 * Home-screen sidebar links: replace Elementor URLs with JupiterX/Artbees
					 * equivalents, and remove the Facebook Community entry entirely.
					 */
					var homeLinkMap = {
						'https://elementor.com/help/': 'https://help.jupiterx.com',
						'https://www.youtube.com/@Elementor': 'https://www.youtube.com/@artbees-themes',
						'https://elementor.com/blog/': 'https://artbees.net/blog/',
					};
					var removeHrefs = ['https://www.facebook.com/groups/Elementors'];

					var sidebarLinks = root.querySelectorAll('a[href]');
					Array.prototype.forEach.call(sidebarLinks, function(a) {
						var href = (a.getAttribute('href') || '').replace(/\/$/, '');

						/* Replace known URLs */
						for (var original in homeLinkMap) {
							if (href === original.replace(/\/$/, '')) {
								a.setAttribute('href', homeLinkMap[original]);
								return;
							}
						}

						/* Hide entries we don't want at all */
						for (var r = 0; r < removeHrefs.length; r++) {
							if (href === removeHrefs[r].replace(/\/$/, '')) {
								var box = a.closest('.MuiBox-root');
								if (box) {
									box.style.setProperty('display', 'none', 'important');
								} else {
									a.style.setProperty('display', 'none', 'important');
								}
								return;
							}
						}
					});

					/* Sidebar upgrade banner (go-pro-home-sidebar-upgrade) */
					var promoLinks = root.querySelectorAll('a[href*="go-pro-home-sidebar-upgrade"]');
					Array.prototype.forEach.call(promoLinks, function(link) {
						/* Use the innermost xs container to avoid hiding ancestor wrappers */
						var container = link.closest('.MuiContainer-maxWidthXs') || link.closest('.MuiPaper-root');
						if (container) {
							container.style.setProperty('display', 'none', 'important');
						}
					});
				}

				function jxStartObserver() {
					jxHideUpgradeCards(document);

					var pending = false;
					var observer = new MutationObserver(function(mutations) {
						if (pending) {
							return;
						}
						pending = true;
						requestAnimationFrame(function() {
							pending = false;
							for (var i = 0; i < mutations.length; i++) {
								var added = mutations[i].addedNodes;
								for (var j = 0; j < added.length; j++) {
									if (added[j].nodeType === 1) {
										jxHideUpgradeCards(added[j]);
									}
								}
							}
						});
					});

					observer.observe(document.body, {
						childList: true,
						subtree: true
					});
				}

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', jxStartObserver);
				} else {
					jxStartObserver();
				}

				/*
				 * Static WP admin sidebar – hide the "Upgrade" <li> inside the
				 * Elementor menu.  The admin menu is server-rendered HTML so a simple
				 * DOMContentLoaded scan is enough; no MutationObserver needed here.
				 *
				 * Strategy: find every <a> inside the Elementor top-level menu items,
				 * check whether its trimmed text is exactly "Upgrade" (case-insensitive)
				 * or its href points to a known upgrade slug, then hide the parent <li>.
				 */
				function jxRemoveUpgradeMenuItems() {
					var elMenuIds = [
						'toplevel_page_elementor-settings',
						'toplevel_page_elementor-home',
						'toplevel_page_elementor',
					];

					elMenuIds.forEach(function(menuId) {
						var menuEl = document.getElementById(menuId);
						if (!menuEl) {
							return;
						}

						var links = menuEl.querySelectorAll('.wp-submenu a, .elementor-submenu-flyout a');
						Array.prototype.forEach.call(links, function(link) {
							var text = (link.textContent || '').trim().toLowerCase();
							var href = (link.getAttribute('href') || '').toLowerCase();
							var isUpgrade =
								text === 'upgrade' ||
								href.indexOf('elementor-one-upgrade') !== -1 ||
								href.indexOf('go_elementor_pro') !== -1 ||
								href.indexOf('admin_menu_promo') !== -1;
							/* Match href only — label "Submissions" is too easy to false-match */
							var isSubmissions = href.indexOf('e-form-submissions') !== -1;

							if (isUpgrade || isSubmissions) {
								/* Only the <li> that directly wraps this <a>, not a flyout parent */
								var li = link.parentElement;
								if (li && li.tagName === 'LI') {
									li.style.setProperty('display', 'none', 'important');
								}
							}
						});
					});
				}

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', jxRemoveUpgradeMenuItems);
				} else {
					jxRemoveUpgradeMenuItems();
				}
			})();
		</script>
	<?php
	}

	/**
	 * Remove the "Upgrade" and Pro-only "Submissions" submenu pages from Elementor menus.
	 * Targets both the legacy 'elementor' parent and the new 'elementor-home' parent.
	 *
	 * @since 4.7.0
	 */
	public function remove_upgrade_admin_submenu()
	{
		$upgrade_slugs  = ['go_elementor_pro', 'admin_menu_promo', 'elementor-one-upgrade'];
		$parent_slugs   = ['elementor', 'elementor-home'];

		foreach ($parent_slugs as $parent) {
			foreach ($upgrade_slugs as $slug) {
				remove_submenu_page($parent, $slug);
			}
			/* Pro-only form submissions screen (Elementor One flyout). */
			remove_submenu_page($parent, 'e-form-submissions');
		}
	}

	/**
	 * Replace Elementor’s default submenu with a flat list (Settings, Tools only)
	 * and make the top-level menu open Settings (first item).
	 *
	 * The parent slug stays `elementor-home` so WordPress can resolve
	 * `get_plugin_page_hook( $slug, 'elementor-home' )` and build correct
	 * `admin.php?page=…` URLs. Renaming the parent to `elementor-settings` breaks that
	 * and produces broken hrefs like `/wp-admin/elementor-tools`.
	 *
	 * @since 4.8.0
	 * @return void
	 */
	public function restructure_elementor_wordpress_menu()
	{
		global $submenu;

		if ( ! isset( $submenu['elementor-home'] ) ) {
			return;
		}

		$cap_manage = 'manage_options';

		// Same shape as add_submenu_page(): [ menu_title, capability, menu_slug, page_title ].
		// Index 3 is required — get_admin_page_title() reads $submenu_array[3] (wp-admin/includes/plugin.php).
		$submenu['elementor-home'] = [
			[
				esc_html__( 'Settings', 'jupiterx-core' ),
				$cap_manage,
				'elementor-settings',
				esc_html__( 'Settings', 'jupiterx-core' ),
			],
			[
				esc_html__( 'Tools', 'jupiterx-core' ),
				$cap_manage,
				'elementor-tools',
				esc_html__( 'Tools', 'jupiterx-core' ),
			],
		];
	}

	/**
	 * Return a data array with a blank URL so that Filtered_Promotions_Manager
	 * discards the promotion URL (it only accepts elementor.com hosts).
	 *
	 * @param array $data Promotion data.
	 * @since 4.7.0
	 * @return array
	 */
	public function blank_promotion_url(array $data): array
	{
		$data['url'] = '';

		return $data;
	}

	/**
	 * Strip Pro-feature capability cards from the Elementor home-screen data.
	 *
	 * The home-screen JSON is fetched from a remote URL and cached.  The
	 * `get_started` section contains a `repeater` array for each license tier
	 * (free/pro/one); entries for Theme Builder, Popups, Custom Icons and
	 * Custom Fonts link to Pro or confusing upsell pages, so we remove them.
	 * The `add_ons` section is also cleared to prevent any add-on promotions.
	 *
	 * @param array $data Raw home-screen data.
	 * @since 4.7.0
	 * @return array
	 */
	public function filter_home_screen_data(array $data): array
	{
		$titles_to_remove = [
			'Theme Builder',
			'Popups',
			'Custom Icons',
			'Custom Fonts',
			'Custom Code',
			'Submissions',
		];

		if (isset($data['get_started']) && is_array($data['get_started'])) {
			foreach ($data['get_started'] as &$license_group) {
				if (! isset($license_group['repeater']) || ! is_array($license_group['repeater'])) {
					continue;
				}
				$license_group['repeater'] = array_values(
					array_filter(
						$license_group['repeater'],
						function ($item) use ($titles_to_remove) {
							return ! in_array($item['title'] ?? '', $titles_to_remove, true);
						}
					)
				);
			}
			unset($license_group);
		}

		// Remove the add-ons promotional section entirely.
		unset($data['add_ons']);

		return $data;
	}

	/**
	 * Dequeue Elementor AI scripts from the editor.
	 *
	 * @since 4.7.0
	 */
	public function dequeue_ai_scripts()
	{
		$ai_handles = [
			'elementor-ai',
			'elementor-ai-editor',
			'elementor-ai-layout',
			'elementor-ai-layout-preview',
			'elementor-ai-media-library',
		];

		foreach ($ai_handles as $handle) {
			wp_dequeue_script($handle);
			wp_deregister_script($handle);
		}
	}

	/**
	 * Replace Pro-only links in the Editor-One sidebar navigation with JupiterX equivalents.
	 * Uses a MutationObserver so the swap happens as soon as React renders the nav.
	 *
	 * @since 4.7.0
	 */
	public function replace_editor_sidebar_pro_links()
	{
		$links = [
			'elementor_custom_fonts' => admin_url('admin.php?page=jupiterx#/custom-fonts'),
			'elementor_custom_icons' => admin_url('admin.php?page=jupiterx#/custom-icons'),
			'popup_templates'        => admin_url('admin.php?page=jupiterx#/popups'),
			'elementor_custom_code'  => admin_url('admin.php?page=jupiterx#/custom-snippets'),
		];
	?>
		<script>
			(function() {
				var jxLinkMap = <?php echo wp_json_encode($links); ?>;

				/*
				 * Always scan the full document so React re-renders that revert hrefs
				 * are immediately corrected on the next animation frame.
				 */
				function jxHideEditorOneSidebarNavItems() {
					var navRoot = document.getElementById('editor-one-sidebar-navigation');
					if (!navRoot) {
						return;
					}

					/*
					 * Main nav + Templates flyout: match label text then hide the <li>.
					 * (Class names are unstable; text is the stable anchor.)
					 */
					var hideLabels = [
						'Submissions',
						'Quick Start',
						'Role Manager',
						'Custom Elements',
						'Theme Builder',
						'Popups',
						'Website Templates',
					];

					var navItems = navRoot.querySelectorAll('.MuiListItem-root');
					Array.prototype.forEach.call(navItems, function(li) {
						var label = li.querySelector('.MuiListItemText-primary');
						var t = label ? (label.textContent || '').trim() : '';
						if (t && hideLabels.indexOf(t) !== -1) {
							li.style.setProperty('display', 'none', 'important');
						}
					});

					/*
					 * After "Custom Elements", Elementor nests Fonts / Icons / Code in their own <ul>.
					 * Hide that entire list when it contains exactly those three items.
					 */
					var uls = navRoot.querySelectorAll('ul');
					Array.prototype.forEach.call(uls, function(ul) {
						var texts = [];
						var ch = ul.children;
						for (var c = 0; c < ch.length; c++) {
							if (ch[c].tagName !== 'LI') {
								continue;
							}
							var lab = ch[c].querySelector('.MuiListItemText-primary');
							if (lab) {
								texts.push((lab.textContent || '').trim());
							}
						}
						if (texts.length !== 3) {
							return;
						}
						texts.sort();
						if (texts[0] === 'Code' && texts[1] === 'Fonts' && texts[2] === 'Icons') {
							ul.style.setProperty('display', 'none', 'important');
						}
					});
				}

				function jxReplaceSidebarLinks() {
					/* ── Anchor replacements by known page= slugs ── */
					var anchors = document.querySelectorAll('a[href]');
					Array.prototype.forEach.call(anchors, function(a) {
						var href = a.getAttribute('href') || '';
						for (var slug in jxLinkMap) {
							if (href.indexOf('page=' + slug) !== -1) {
								a.setAttribute('href', jxLinkMap[slug]);
								a.removeAttribute('target');
								return;
							}
						}
					});

					jxHideEditorOneSidebarNavItems();
				}

				function jxStartSidebarObserver() {
					jxReplaceSidebarLinks();

					var pending = false;
					var observer = new MutationObserver(function() {
						if (pending) {
							return;
						}
						pending = true;
						requestAnimationFrame(function() {
							pending = false;
							jxReplaceSidebarLinks();
						});
					});

					/*
					 * Watch childList + subtree for node additions/removals,
					 * AND attributes/attributeFilter for React patching href in-place.
					 */
					observer.observe(document.body, {
						childList: true,
						subtree: true,
						attributes: true,
						attributeFilter: ['href'],
					});
				}

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', jxStartSidebarObserver);
				} else {
					jxStartSidebarObserver();
				}
			})();
		</script>
<?php
	}

	/**
	 * Move the Elementor top-level menu item to appear after "Plugins" in the
	 * WordPress admin sidebar instead of floating near the top.
	 *
	 * @since 4.7.0
	 *
	 * @param array $menu_order Current admin menu order.
	 * @return array Reordered menu.
	 */
	public function move_elementor_menu_after_plugins(array $menu_order): array
	{
		$pos = array_search('elementor-home', $menu_order, true);
		if (false === $pos) {
			return $menu_order;
		}

		$slug = 'elementor-home';

		// Pull Elementor out of its current position.
		array_splice($menu_order, $pos, 1);

		// Re-insert immediately after plugins.php.
		$plugins_pos = array_search('plugins.php', $menu_order, true);
		if (false !== $plugins_pos) {
			array_splice($menu_order, $plugins_pos + 1, 0, [ $slug ]);
		} else {
			$menu_order[] = $slug;
		}

		return $menu_order;
	}

	/**
	 * Dequeue Elementor AI scripts from admin pages (Gutenberg, media library, etc.).
	 *
	 * @since 4.7.0
	 */
	public function dequeue_admin_ai_scripts()
	{
		$ai_handles = [
			'elementor-ai-gutenberg',
			'elementor-ai-media-library',
		];

		foreach ($ai_handles as $handle) {
			wp_dequeue_script($handle);
			wp_deregister_script($handle);
		}
	}
}

<?php

/**
 * Handles global admin promotion banner.
 *
 * @package JupiterX_Core\Control_Panel_2\Promotion_Banner
 *
 * @since 4.13.0
 */

defined('ABSPATH') || die();

/**
 * Global admin promotion banner.
 *
 * @since 4.13.0
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class JupiterX_Core_Control_Panel_Promotion_Banner
{
	/**
	 * Option key for local banners.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const OPTION_KEY = 'jupiterx_local_test_promotion_banners';

	/**
	 * Admin page slug.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'jupiterx-local-promotion-banner';

	/**
	 * Default remote JSON endpoint.
	 *
	 * Override order:
	 * 1. Optional `JUPITERX_PROMOTION_BANNER_DEV_URL` in wp-config (non-empty string).
	 * 2. Filter `jupiterx_promotion_banner_remote_endpoint` (receives URL from step 1 or this default).
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const DEFAULT_REMOTE_ENDPOINT = 'https://d34e24far7qrt2.cloudfront.net/jupiterx/api/banners/promo_banners.json';

	/**
	 * Last normalized remote banners (for dismiss validation after async load).
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const REMOTE_BANNERS_SNAPSHOT_OPTION = 'jupiterx_remote_promotion_banners_snapshot';

	/**
	 * Dismiss meta prefix.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const DISMISS_META_PREFIX = 'jupiterx_admin_promotion_dismissed_';

	/**
	 * Constructor.
	 *
	 * @since 4.13.0
	 */
	public function __construct()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
		add_action('wp_ajax_jupiterx_dismiss_admin_promotion', [$this, 'ajax_dismiss']);
		add_action('wp_ajax_jupiterx_fetch_promotion_banners', [$this, 'ajax_fetch_promotion_banners']);
	}

	/**
	 * Register admin page.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function register_admin_page()
	{
		if (! $this->is_local_manager_enabled()) {
			return;
		}

		add_submenu_page(
			'jupiterx',
			esc_html__('Promo Banner', 'jupiterx-core'),
			esc_html__('Promo Banner', 'jupiterx-core'),
			'manage_options',
			self::PAGE_SLUG,
			[$this, 'render_admin_page']
		);
	}

	/**
	 * Render admin page.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function render_admin_page()
	{
		if (! current_user_can('manage_options') || ! $this->is_local_manager_enabled()) {
			return;
		}

		$banner_id = isset($_GET['banner_id']) ? sanitize_key(wp_unslash($_GET['banner_id'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action    = isset($_GET['jx_action']) ? sanitize_key(wp_unslash($_GET['jx_action'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e('JupiterX Promo Banners', 'jupiterx-core'); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url($this->get_admin_page_url(['jx_action' => 'new'])); ?>">
				<?php esc_html_e('Add New', 'jupiterx-core'); ?>
			</a>
			<p><?php esc_html_e('Create and manage local test banners for the JupiterX control panel. This is a local management UI for testing the banner system before connecting it to my.artbees.net.', 'jupiterx-core'); ?></p>

			<?php $this->render_admin_notices(); ?>

			<?php
			if ('new' === $action || ! empty($banner_id)) {
				$this->render_editor_screen($banner_id);
			} else {
				$this->render_list_screen();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render admin notices for actions.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	private function render_admin_notices()
	{
		$messages = [
			'updated' => __('Promo banner saved.', 'jupiterx-core'),
			'deleted' => __('Promo banner deleted.', 'jupiterx-core'),
			'toggled' => __('Promo banner status updated.', 'jupiterx-core'),
			'duplicated' => __('Promo banner duplicated.', 'jupiterx-core'),
		];

		foreach ($messages as $query_var => $message) {
			if (! isset($_GET[$query_var])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				continue;
			}
		?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html($message); ?></p>
			</div>
		<?php
		}
	}

	/**
	 * Render banner list.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	private function render_list_screen()
	{
		$banners = $this->get_local_banners();
		?>
		<table class="widefat striped" style="margin-top:16px;">
			<thead>
				<tr>
					<th><?php esc_html_e('Banner', 'jupiterx-core'); ?></th>
					<th><?php esc_html_e('Preset', 'jupiterx-core'); ?></th>
					<th><?php esc_html_e('Targeting', 'jupiterx-core'); ?></th>
					<th><?php esc_html_e('Schedule', 'jupiterx-core'); ?></th>
					<th><?php esc_html_e('Status', 'jupiterx-core'); ?></th>
					<th><?php esc_html_e('Actions', 'jupiterx-core'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($banners)) : ?>
					<tr>
						<td colspan="6"><?php esc_html_e('No banners created yet.', 'jupiterx-core'); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ($banners as $banner) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html($banner['name']); ?></strong><br />
								<span><?php echo esc_html($banner['heading']); ?></span>
								<?php if (empty($banner['isLocal'])) : ?>
									<br /><span class="description"><?php esc_html_e('Built-in banner', 'jupiterx-core'); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html($this->get_preset_label($banner['preset'])); ?></td>
							<td><?php echo esc_html($this->get_target_summary($banner)); ?></td>
							<td><?php echo esc_html($this->get_schedule_summary($banner)); ?></td>
							<td><?php echo esc_html($this->get_banner_status($banner)); ?></td>
							<td>
								<?php if (! empty($banner['isLocal'])) : ?>
									<a href="<?php echo esc_url($this->get_admin_page_url(['banner_id' => $banner['id']])); ?>">
										<?php esc_html_e('Edit', 'jupiterx-core'); ?>
									</a>
									|
									<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
										<?php wp_nonce_field('jupiterx_toggle_local_promotion_banner'); ?>
										<input type="hidden" name="action" value="jupiterx_toggle_local_promotion_banner" />
										<input type="hidden" name="banner_id" value="<?php echo esc_attr($banner['id']); ?>" />
										<input type="hidden" name="toggle_to" value="<?php echo empty($banner['enabled']) ? 'active' : 'draft'; ?>" />
										<button type="submit" class="button-link">
											<?php echo esc_html(empty($banner['enabled']) ? __('Activate', 'jupiterx-core') : __('Deactivate', 'jupiterx-core')); ?>
										</button>
									</form>
									|
									<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
										<?php wp_nonce_field('jupiterx_delete_local_promotion_banner'); ?>
										<input type="hidden" name="action" value="jupiterx_delete_local_promotion_banner" />
										<input type="hidden" name="banner_id" value="<?php echo esc_attr($banner['id']); ?>" />
										<button type="submit" class="button-link-delete" onclick="return window.confirm('<?php echo esc_js(__('Delete this banner?', 'jupiterx-core')); ?>');">
											<?php esc_html_e('Delete', 'jupiterx-core'); ?>
										</button>
									</form>
									|
									<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
										<?php wp_nonce_field('jupiterx_duplicate_local_promotion_banner'); ?>
										<input type="hidden" name="action" value="jupiterx_duplicate_local_promotion_banner" />
										<input type="hidden" name="banner_id" value="<?php echo esc_attr($banner['id']); ?>" />
										<button type="submit" class="button-link">
											<?php esc_html_e('Duplicate', 'jupiterx-core'); ?>
										</button>
									</form>
								<?php else : ?>
									<span class="description"><?php esc_html_e('Built-in campaign', 'jupiterx-core'); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php
	}

	/**
	 * Render banner editor.
	 *
	 * @since 4.13.0
	 *
	 * @param string $banner_id Banner ID.
	 *
	 * @return void
	 */
	private function render_editor_screen($banner_id)
	{
		wp_enqueue_media();

		$is_new = empty($banner_id);
		$banner = $is_new ? $this->get_default_banner() : $this->get_banner_by_id($banner_id);

		if (empty($banner)) {
			echo '<div class="notice notice-error"><p>' . esc_html__('Banner not found.', 'jupiterx-core') . '</p></div>';
			return;
		}

		$preview_banner = $this->prepare_banner_for_render($banner);
	?>
		<hr class="wp-header-end" />
		<p>
			<a href="<?php echo esc_url($this->get_admin_page_url()); ?>">&larr; <?php esc_html_e('Back to banner list', 'jupiterx-core'); ?></a>
		</p>

		<h2><?php echo esc_html($is_new ? __('New Promo Banner', 'jupiterx-core') : __('Edit Promo Banner', 'jupiterx-core')); ?></h2>
		<style>
			.jx-banner-form-grid tbody {
				display: grid;
				grid-template-columns: repeat(2, minmax(320px, 1fr));
				gap: 16px 24px;
			}

			.jx-banner-form-grid tr {
				display: block;
				margin: 0;
				padding: 16px;
				border: 1px solid #dcdcde;
				background: #fff;
				border-radius: 8px;
			}

			.jx-banner-form-grid tr.jx-banner-section-row {
				grid-column: 1 / -1;
				padding: 0;
				border: 0;
				background: transparent;
			}

			.jx-banner-form-grid th,
			.jx-banner-form-grid td {
				display: block;
				width: auto;
				padding: 0;
				margin: 0;
			}

			.jx-banner-form-grid th {
				margin-bottom: 10px;
			}

			.jx-banner-form-grid input[type="text"],
			.jx-banner-form-grid input[type="url"],
			.jx-banner-form-grid input[type="number"],
			.jx-banner-form-grid textarea,
			.jx-banner-form-grid select {
				width: 100%;
				max-width: 100%;
			}

			.jx-banner-form-grid .small-text {
				width: 90px;
			}

			@media (max-width: 1100px) {
				.jx-banner-form-grid tbody {
					grid-template-columns: 1fr;
				}
			}
		</style>

		<div style="max-width:1600px;">
			<h3><?php esc_html_e('Preview', 'jupiterx-core'); ?></h3>
			<div style="background:#f1f1f1; padding: 1px 0 20px;">
				<?php $this->render_preview_banner($preview_banner); ?>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:20px;">
			<?php wp_nonce_field('jupiterx_save_local_promotion_banner'); ?>
			<input type="hidden" name="action" value="jupiterx_save_local_promotion_banner" />
			<input type="hidden" name="banner_id" value="<?php echo esc_attr($banner['id']); ?>" />

			<table class="form-table jx-banner-form-grid" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="jx-banner-name"><?php esc_html_e('Banner name', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-name" name="name" type="text" class="regular-text" value="<?php echo esc_attr($banner['name']); ?>" />
							<p class="description"><?php esc_html_e('Internal name for the banner manager list.', 'jupiterx-core'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e('Enable banner', 'jupiterx-core'); ?></th>
						<td>
							<label>
								<input type="checkbox" name="enabled" value="1" <?php checked(! empty($banner['enabled'])); ?> />
								<?php esc_html_e('Activate this banner so it can appear when its schedule and targeting match.', 'jupiterx-core'); ?>
							</label>
							<p class="description"><?php esc_html_e('If this is unchecked, the banner stays in Draft mode and will not be displayed.', 'jupiterx-core'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-preset"><?php esc_html_e('Preset', 'jupiterx-core'); ?></label></th>
						<td>
							<select id="jx-banner-preset" name="preset">
								<?php foreach ($this->get_presets() as $preset_key => $preset) : ?>
									<option value="<?php echo esc_attr($preset_key); ?>" <?php selected($banner['preset'], $preset_key); ?>>
										<?php echo esc_html($preset['label']); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e('Presets provide a fast starting point. You can still customize everything after choosing one.', 'jupiterx-core'); ?></p>
						</td>
					</tr>

					<tr class="jx-banner-section-row">
						<th colspan="2">
							<h2><?php esc_html_e('Content', 'jupiterx-core'); ?></h2>
						</th>
					</tr>

					<tr>
						<th scope="row"><label for="jx-banner-heading"><?php esc_html_e('Title', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-heading" name="heading" type="text" class="large-text" value="<?php echo esc_attr($banner['heading']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-description"><?php esc_html_e('Description', 'jupiterx-core'); ?></label></th>
						<td><textarea id="jx-banner-description" name="description" rows="4" class="large-text"><?php echo esc_textarea($banner['description']); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-coupon-code"><?php esc_html_e('Coupon code', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-coupon-code" name="couponCode" type="text" class="regular-text" value="<?php echo esc_attr($banner['couponCode']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-image-url"><?php esc_html_e('Left image URL', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-image-url" name="mainImageURL" type="url" class="large-text jx-media-target" value="<?php echo esc_attr($banner['mainImageURL']); ?>" placeholder="https://example.com/image.png" />
							<p><button type="button" class="button jx-media-upload"><?php esc_html_e('Select image', 'jupiterx-core'); ?></button></p>
						</td>
					</tr>

					<tr class="jx-banner-section-row">
						<th colspan="2">
							<h2><?php esc_html_e('CTA Buttons', 'jupiterx-core'); ?></h2>
						</th>
					</tr>

					<tr>
						<th scope="row"><label for="jx-banner-cta1-text"><?php esc_html_e('CTA 1 label', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-cta1-text" name="cta1Text" type="text" class="regular-text" value="<?php echo esc_attr($banner['cta1Text']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-cta1-url"><?php esc_html_e('CTA 1 URL', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-cta1-url" name="cta1Url" type="url" class="large-text" value="<?php echo esc_attr($banner['cta1Url']); ?>" placeholder="https://example.com" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-cta2-text"><?php esc_html_e('CTA 2 label', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-cta2-text" name="cta2Text" type="text" class="regular-text" value="<?php echo esc_attr($banner['cta2Text']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-cta2-url"><?php esc_html_e('CTA 2 URL', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-cta2-url" name="cta2Url" type="url" class="large-text" value="<?php echo esc_attr($banner['cta2Url']); ?>" placeholder="https://example.com" /></td>
					</tr>

					<tr class="jx-banner-section-row">
						<th colspan="2">
							<h2><?php esc_html_e('Appearance', 'jupiterx-core'); ?></h2>
						</th>
					</tr>

					<tr>
						<th scope="row"><label for="jx-banner-background-color"><?php esc_html_e('Background color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-background-color" name="backgroundColor" type="color" value="<?php echo esc_attr($banner['backgroundColor']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-text-color"><?php esc_html_e('Text color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-text-color" name="textColor" type="color" value="<?php echo esc_attr($banner['textColor']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-border-color"><?php esc_html_e('Border color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-border-color" name="borderColor" type="color" value="<?php echo esc_attr($banner['borderColor']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-background-image"><?php esc_html_e('Background image URL', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-background-image" name="backgroundImage" type="url" class="large-text jx-media-target" value="<?php echo esc_attr($banner['backgroundImage']); ?>" placeholder="https://example.com/background.jpg" />
							<p><button type="button" class="button jx-media-upload"><?php esc_html_e('Select image', 'jupiterx-core'); ?></button></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-overlay-color"><?php esc_html_e('Image overlay color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-overlay-color" name="overlayColor" type="color" value="<?php echo esc_attr($banner['overlayColor']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-overlay-opacity"><?php esc_html_e('Image overlay opacity', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-overlay-opacity" name="overlayOpacity" type="range" min="0" max="100" value="<?php echo esc_attr($banner['overlayOpacity']); ?>" />
							<span><?php echo esc_html((int) $banner['overlayOpacity']); ?>%</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-background-opacity"><?php esc_html_e('Background image opacity', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-background-opacity" name="backgroundImageOpacity" type="range" min="0" max="100" value="<?php echo esc_attr($banner['backgroundImageOpacity']); ?>" />
							<span><?php echo esc_html((int) $banner['backgroundImageOpacity']); ?>%</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e('Linear gradient', 'jupiterx-core'); ?></th>
						<td>
							<input id="jx-banner-gradient-start" name="gradientStart" type="color" value="<?php echo esc_attr($banner['gradientStart']); ?>" />
							<input id="jx-banner-gradient-end" name="gradientEnd" type="color" value="<?php echo esc_attr($banner['gradientEnd']); ?>" />
							<input id="jx-banner-gradient-angle" name="gradientAngle" type="number" class="small-text" min="0" max="360" value="<?php echo esc_attr($banner['gradientAngle']); ?>" />
							<label for="jx-banner-gradient-angle"><?php esc_html_e('Angle', 'jupiterx-core'); ?></label>
							<input id="jx-banner-gradient" name="backgroundGradient" type="hidden" value="<?php echo esc_attr($banner['backgroundGradient']); ?>" />
							<p class="description"><?php esc_html_e('Pick start/end colors and angle. Leave both colors unchanged to avoid using a gradient.', 'jupiterx-core'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-primary-bg"><?php esc_html_e('Primary button color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-primary-bg" name="primaryButtonBg" type="color" value="<?php echo esc_attr($banner['primaryButtonBg']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-primary-text"><?php esc_html_e('Primary button text color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-primary-text" name="primaryButtonText" type="color" value="<?php echo esc_attr($banner['primaryButtonText']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-secondary-bg"><?php esc_html_e('Secondary button color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-secondary-bg" name="secondaryButtonBg" type="color" value="<?php echo esc_attr($banner['secondaryButtonBg']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-secondary-text"><?php esc_html_e('Secondary button text color', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-secondary-text" name="secondaryButtonText" type="color" value="<?php echo esc_attr($banner['secondaryButtonText']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-element-spacing"><?php esc_html_e('Element spacing', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-element-spacing" name="elementSpacing" type="range" min="0" max="48" value="<?php echo esc_attr($banner['elementSpacing']); ?>" />
							<span><?php echo esc_html((int) $banner['elementSpacing']); ?>px</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-title-size"><?php esc_html_e('Title font size', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-title-size" name="titleFontSize" type="number" class="small-text" min="10" max="40" value="<?php echo esc_attr($banner['titleFontSize']); ?>" /> px</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-description-size"><?php esc_html_e('Description font size', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-description-size" name="descriptionFontSize" type="number" class="small-text" min="10" max="32" value="<?php echo esc_attr($banner['descriptionFontSize']); ?>" /> px</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-coupon-size"><?php esc_html_e('Coupon font size', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-coupon-size" name="couponFontSize" type="number" class="small-text" min="10" max="28" value="<?php echo esc_attr($banner['couponFontSize']); ?>" /> px</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-cta-size"><?php esc_html_e('CTA font size', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-cta-size" name="ctaFontSize" type="number" class="small-text" min="10" max="28" value="<?php echo esc_attr($banner['ctaFontSize']); ?>" /> px</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-container-height"><?php esc_html_e('Container height', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-container-height" name="containerHeight" type="number" class="small-text" min="40" max="240" value="<?php echo esc_attr($banner['containerHeight']); ?>" /> px</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-container-width"><?php esc_html_e('Container width', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-container-width" name="containerWidth" type="number" class="small-text" min="20" max="100" value="<?php echo esc_attr($banner['containerWidth']); ?>" /> %
							<p class="description"><?php esc_html_e('Use a percentage of the available admin content width.', 'jupiterx-core'); ?></p>
						</td>
					</tr>

					<tr class="jx-banner-section-row">
						<th colspan="2">
							<h2><?php esc_html_e('Targeting & Schedule', 'jupiterx-core'); ?></h2>
						</th>
					</tr>

					<tr>
						<th scope="row"><label for="jx-banner-target"><?php esc_html_e('Display on', 'jupiterx-core'); ?></label></th>
						<td>
							<select id="jx-banner-target" name="targetScope">
								<option value="jupiterx_only" <?php selected($banner['targetScope'], 'jupiterx_only'); ?>><?php esc_html_e('JupiterX pages only', 'jupiterx-core'); ?></option>
								<option value="jupiterx_and_dashboard" <?php selected($banner['targetScope'], 'jupiterx_and_dashboard'); ?>><?php esc_html_e('JupiterX pages + WordPress Dashboard', 'jupiterx-core'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e('JupiterX pages', 'jupiterx-core'); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e('Choose individual JupiterX pages', 'jupiterx-core'); ?></legend>
								<?php foreach ($this->get_jupiterx_target_pages() as $page_key => $page_label) : ?>
									<label style="display:block;margin-bottom:6px;">
										<input
											type="checkbox"
											name="targetPages[]"
											value="<?php echo esc_attr($page_key); ?>"
											<?php checked(in_array($page_key, $banner['targetPages'], true)); ?> />
										<?php echo esc_html($page_label); ?>
									</label>
								<?php endforeach; ?>
							</fieldset>
							<p class="description"><?php esc_html_e('Leave all unchecked to show on all matching JupiterX pages. The main Control Panel option covers Dashboard, Settings, Help, and other sections that live under the same admin screen.', 'jupiterx-core'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-starts-at"><?php esc_html_e('Start time', 'jupiterx-core'); ?></label></th>
						<td>
							<input id="jx-banner-starts-at" name="startsAt" type="datetime-local" value="<?php echo esc_attr($this->format_datetime_local_value($banner['startsAt'])); ?>" />
							<p class="description"><?php echo esc_html(sprintf(__('Stored using the site timezone: %s', 'jupiterx-core'), wp_timezone_string())); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-expires-at"><?php esc_html_e('Expiry time', 'jupiterx-core'); ?></label></th>
						<td><input id="jx-banner-expires-at" name="expiresAt" type="datetime-local" value="<?php echo esc_attr($this->format_datetime_local_value($banner['expiresAt'])); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e('Scheduling', 'jupiterx-core'); ?></th>
						<td>
							<label>
								<input type="checkbox" name="displayPermanently" value="1" <?php checked(! empty($banner['displayPermanently'])); ?> />
								<?php esc_html_e('Display permanently when no start or expiry time is set.', 'jupiterx-core'); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="jx-banner-dismiss-mode"><?php esc_html_e('After dismissing', 'jupiterx-core'); ?></label></th>
						<td>
							<select id="jx-banner-dismiss-mode" name="dismissMode">
								<?php foreach ($this->get_dismiss_mode_options() as $value => $label) : ?>
									<option value="<?php echo esc_attr($value); ?>" <?php selected($banner['dismissMode'], $value); ?>>
										<?php echo esc_html($label); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e('Banners are always dismissible. This controls when they can appear again for the same admin user.', 'jupiterx-core'); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(__('Save Banner', 'jupiterx-core')); ?>
		</form>
		<script>
			(function() {
				const presets = <?php echo wp_json_encode($this->get_presets()); ?>;
				const presetField = document.getElementById('jx-banner-preset');
				const spacingField = document.getElementById('jx-banner-element-spacing');
				const spacingLabel = spacingField ? spacingField.nextElementSibling : null;
				const gradientStart = document.getElementById('jx-banner-gradient-start');
				const gradientEnd = document.getElementById('jx-banner-gradient-end');
				const gradientAngle = document.getElementById('jx-banner-gradient-angle');
				const gradientHidden = document.getElementById('jx-banner-gradient');
				const appearanceFields = [
					document.getElementById('jx-banner-background-color'),
					document.getElementById('jx-banner-text-color'),
					document.getElementById('jx-banner-border-color'),
					document.getElementById('jx-banner-background-image'),
					document.getElementById('jx-banner-overlay-color'),
					document.getElementById('jx-banner-overlay-opacity'),
					document.getElementById('jx-banner-background-opacity'),
					document.getElementById('jx-banner-gradient-start'),
					document.getElementById('jx-banner-gradient-end'),
					document.getElementById('jx-banner-gradient-angle'),
					document.getElementById('jx-banner-primary-bg'),
					document.getElementById('jx-banner-primary-text'),
					document.getElementById('jx-banner-secondary-bg'),
					document.getElementById('jx-banner-secondary-text'),
					document.getElementById('jx-banner-element-spacing'),
					document.getElementById('jx-banner-title-size'),
					document.getElementById('jx-banner-description-size'),
					document.getElementById('jx-banner-coupon-size'),
					document.getElementById('jx-banner-cta-size'),
					document.getElementById('jx-banner-container-height'),
					document.getElementById('jx-banner-container-width')
				].filter(Boolean);

				const markPresetAsCustom = () => {
					if (!presetField || presetField.value === 'custom') {
						return;
					}

					presetField.value = 'custom';
				};

				const applyPreset = () => {
					const preset = presets[presetField.value];
					if (!preset) return;
					if (presetField.value === 'custom') return;
					document.getElementById('jx-banner-background-color').value = preset.backgroundColor;
					document.getElementById('jx-banner-text-color').value = preset.textColor;
					document.getElementById('jx-banner-border-color').value = preset.borderColor;
					document.getElementById('jx-banner-primary-bg').value = preset.primaryButtonBg;
					document.getElementById('jx-banner-primary-text').value = preset.primaryButtonText;
					document.getElementById('jx-banner-secondary-bg').value = preset.secondaryButtonBg;
					document.getElementById('jx-banner-secondary-text').value = preset.secondaryButtonText;
					if (gradientStart) gradientStart.value = preset.gradientStart;
					if (gradientEnd) gradientEnd.value = preset.gradientEnd;
					if (gradientAngle) gradientAngle.value = preset.gradientAngle || 90;
					syncGradient();
				};

				const syncGradient = () => {
					if (!gradientHidden) return;
					gradientHidden.value = 'linear-gradient(' + (gradientAngle.value || 90) + 'deg, ' + gradientStart.value + ', ' + gradientEnd.value + ')';
				};

				if (presetField) {
					presetField.addEventListener('change', applyPreset);
				}

				appearanceFields.forEach((field) => {
					field.addEventListener('input', markPresetAsCustom);
					field.addEventListener('change', markPresetAsCustom);
				});

				[gradientStart, gradientEnd, gradientAngle].forEach((field) => {
					if (field) field.addEventListener('input', syncGradient);
				});

				if (spacingField && spacingLabel) {
					spacingField.addEventListener('input', () => {
						spacingLabel.textContent = spacingField.value + 'px';
					});
				}

				const overlayField = document.getElementById('jx-banner-overlay-opacity');
				const overlayLabel = overlayField ? overlayField.nextElementSibling : null;
				const backgroundOpacityField = document.getElementById('jx-banner-background-opacity');
				const backgroundOpacityLabel = backgroundOpacityField ? backgroundOpacityField.nextElementSibling : null;

				if (overlayField && overlayLabel) {
					overlayField.addEventListener('input', () => {
						overlayLabel.textContent = overlayField.value + '%';
					});
				}

				if (backgroundOpacityField && backgroundOpacityLabel) {
					backgroundOpacityField.addEventListener('input', () => {
						backgroundOpacityLabel.textContent = backgroundOpacityField.value + '%';
					});
				}

				document.querySelectorAll('.jx-media-upload').forEach((button) => {
					button.addEventListener('click', function() {
						const target = this.closest('td').querySelector('.jx-media-target');
						if (!target || !window.wp || !wp.media) return;
						const frame = wp.media({
							title: 'Select image',
							multiple: false,
							library: {
								type: 'image'
							}
						});
						frame.on('select', function() {
							const attachment = frame.state().get('selection').first().toJSON();
							target.value = attachment.url || '';
						});
						frame.open();
					});
				});
			}());
		</script>
<?php
	}

	/**
	 * Save banner.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function handle_save()
	{
		if (! current_user_can('manage_options') || ! $this->is_local_manager_enabled()) {
			wp_die(esc_html__('You do not have permission to do that.', 'jupiterx-core'));
		}

		check_admin_referer('jupiterx_save_local_promotion_banner');

		$banner_id = isset($_POST['banner_id']) ? sanitize_key(wp_unslash($_POST['banner_id'])) : '';
		$banners   = $this->get_local_banners_raw();
		$banner    = $this->sanitize_banner_input($_POST, $banner_id);

		$banners[$banner['id']] = $banner;

		update_option(self::OPTION_KEY, $banners, false);
		$this->clear_dismiss_meta_if_needed($banner);

		wp_safe_redirect($this->get_admin_page_url(['updated' => 1]));
		exit;
	}

	/**
	 * Delete banner.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function handle_delete()
	{
		if (! current_user_can('manage_options') || ! $this->is_local_manager_enabled()) {
			wp_die(esc_html__('You do not have permission to do that.', 'jupiterx-core'));
		}

		check_admin_referer('jupiterx_delete_local_promotion_banner');

		$banner_id = isset($_POST['banner_id']) ? sanitize_key(wp_unslash($_POST['banner_id'])) : '';
		$banners   = $this->get_local_banners();

		if (! empty($banner_id)) {
			$remaining_banners = [];

			foreach ($banners as $banner) {
				if (empty($banner['isLocal']) || $banner['id'] === $banner_id) {
					continue;
				}

				$remaining_banners[$banner['id']] = $banner;
			}

			update_option(self::OPTION_KEY, $remaining_banners, false);
			delete_metadata('user', 0, self::DISMISS_META_PREFIX . $banner_id, '', true);
		}

		wp_safe_redirect($this->get_admin_page_url(['deleted' => 1]));
		exit;
	}

	/**
	 * Toggle banner enabled status.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function handle_toggle_status()
	{
		if (! current_user_can('manage_options') || ! $this->is_local_manager_enabled()) {
			wp_die(esc_html__('You do not have permission to do that.', 'jupiterx-core'));
		}

		check_admin_referer('jupiterx_toggle_local_promotion_banner');

		$banner_id = isset($_POST['banner_id']) ? sanitize_key(wp_unslash($_POST['banner_id'])) : '';
		$toggle_to = isset($_POST['toggle_to']) ? sanitize_key(wp_unslash($_POST['toggle_to'])) : '';
		$enabled   = 'active' === $toggle_to;
		$banners   = [];

		foreach ($this->get_local_banners() as $banner) {
			if ($banner['id'] === $banner_id) {
				$banner['enabled']   = $enabled;
				$banner['updatedAt'] = gmdate(DATE_ATOM);

				if ($enabled) {
					$this->clear_dismiss_meta_if_needed($banner);
				}
			}

			$banners[$banner['id']] = $banner;
		}

		update_option(self::OPTION_KEY, $banners, false);

		wp_safe_redirect($this->get_admin_page_url(['toggled' => 1]));
		exit;
	}

	/**
	 * Duplicate a local banner.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function handle_duplicate()
	{
		if (! current_user_can('manage_options') || ! $this->is_local_manager_enabled()) {
			wp_die(esc_html__('You do not have permission to do that.', 'jupiterx-core'));
		}

		check_admin_referer('jupiterx_duplicate_local_promotion_banner');

		$banner_id = isset($_POST['banner_id']) ? sanitize_key(wp_unslash($_POST['banner_id'])) : '';
		$banners   = [];
		$source    = null;

		foreach ($this->get_local_banners() as $banner) {
			$banners[$banner['id']] = $banner;

			if ($banner['id'] === $banner_id) {
				$source = $banner;
			}
		}

		if (! empty($source)) {
			$source['id']        = $this->generate_banner_id();
			$source['name']      = sprintf(__('%s Copy', 'jupiterx-core'), $source['name']);
			$source['enabled']   = false;
			$source['updatedAt'] = gmdate(DATE_ATOM);
			$banners[$source['id']] = $source;
			update_option(self::OPTION_KEY, $banners, false);
		}

		wp_safe_redirect($this->get_admin_page_url(['duplicated' => 1]));
		exit;
	}

	/**
	 * Get all configured banners.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_banners()
	{
		return [];
	}

	/**
	 * Check whether the local banner manager should be available.
	 *
	 * @since 4.13.0
	 *
	 * @return bool
	 */
	private function is_local_manager_enabled()
	{
		return false;
	}

	/**
	 * Fetch remote JSON payload (AJAX handler only; no server-side page-load fetch).
	 *
	 * @since 4.13.0
	 *
	 * @return array|null Valid payload with `banners` key, or null when the request or document is unusable.
	 */
	private function fetch_remote_promotion_payload()
	{
		$endpoint = $this->get_remote_endpoint();

		if (empty($endpoint)) {
			return null;
		}

		$response = wp_remote_get(
			$endpoint,
			[
				'timeout' => 8,
				'headers' => $this->get_remote_request_headers(),
			]
		);

		if (is_wp_error($response)) {
			return null;
		}

		$status_code = (int) wp_remote_retrieve_response_code($response);

		if (200 !== $status_code) {
			return null;
		}

		$body = wp_remote_retrieve_body($response);

		if ('' === $body) {
			return null;
		}

		$payload = json_decode($body, true);

		if (! is_array($payload) || ! isset($payload['banners']) || ! is_array($payload['banners'])) {
			return null;
		}

		return $payload;
	}

	/**
	 * Persist last remote banners for dismiss lookups.
	 *
	 * @since 4.13.0
	 *
	 * @param array $normalized_banners Normalized banner rows.
	 *
	 * @return void
	 */
	private function store_remote_banners_snapshot(array $normalized_banners)
	{
		update_option(self::REMOTE_BANNERS_SNAPSHOT_OPTION, $normalized_banners, false);
	}

	/**
	 * Banners from last successful remote fetch (for dismiss validation).
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_remote_banners_snapshot_banners()
	{
		$stored = get_option(self::REMOTE_BANNERS_SNAPSHOT_OPTION, []);

		return is_array($stored) ? $stored : [];
	}

	/**
	 * Banners to consider when validating dismiss (local + last remote snapshot).
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_banners_for_dismiss_lookup()
	{
		$out  = [];
		$seen = [];

		foreach ($this->get_banners() as $banner) {
			if (empty($banner['id'])) {
				continue;
			}

			$seen[$banner['id']] = true;
			$out[]                 = $banner;
		}

		foreach ($this->get_remote_banners_snapshot_banners() as $banner) {
			if (! is_array($banner) || empty($banner['id'])) {
				continue;
			}

			if (isset($seen[$banner['id']])) {
				continue;
			}

			$seen[$banner['id']] = true;
			$out[]                 = $this->normalize_banner($banner);
		}

		return $out;
	}

	/**
	 * Get remote endpoint URL.
	 *
	 * @since 4.13.0
	 *
	 * @return string
	 */
	private function get_remote_endpoint()
	{
		$default = self::DEFAULT_REMOTE_ENDPOINT;

		if (defined('JUPITERX_PROMOTION_BANNER_DEV_URL')) {
			$dev_url = constant('JUPITERX_PROMOTION_BANNER_DEV_URL');

			if (is_string($dev_url)) {
				$dev_url = trim($dev_url);

				if ('' !== $dev_url) {
					$default = $dev_url;
				}
			}
		}

		$endpoint = apply_filters('jupiterx_promotion_banner_remote_endpoint', $default);

		return is_string($endpoint) ? esc_url_raw($endpoint) : '';
	}

	/**
	 * Get remote request headers.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_remote_request_headers()
	{
		$headers = [
			'Accept' => 'application/json',
		];

		$api_key = apply_filters('jupiterx_promotion_banner_remote_api_key', '');

		if (is_string($api_key) && '' !== trim($api_key)) {
			$api_key = trim($api_key);
			$headers['X-JupiterX-Promotion-Banner-Key'] = $api_key;
			$headers['X-Artbees-Shared-Key'] = $api_key;
			$headers['Authorization'] = 'Bearer ' . $api_key;
			$headers['X-API-Key'] = $api_key;
		}

		return $headers;
	}

	/**
	 * Get normalized local banners.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_local_banners()
	{
		$banners = [];

		foreach ($this->get_local_banners_raw() as $banner) {
			$banners[] = $this->normalize_banner($banner);
		}

		return $banners;
	}

	/**
	 * Get raw local banners, with migration from legacy single-banner storage.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_local_banners_raw()
	{
		$banners = get_option(self::OPTION_KEY, []);

		if (! is_array($banners)) {
			return [];
		}

		$normalized_banners = [];

		foreach ($banners as $key => $banner) {
			if (! is_array($banner)) {
				continue;
			}

			$banner = $this->normalize_banner($banner);

			if (empty($banner['id'])) {
				$banner['id'] = is_string($key) ? sanitize_key($key) : $this->generate_banner_id();
			} else {
				$banner['id'] = sanitize_key($banner['id']);
			}

			$normalized_banners[$banner['id']] = $banner;
		}

		return $normalized_banners;
	}

	/**
	 * Get a banner by ID.
	 *
	 * @since 4.13.0
	 *
	 * @param string $banner_id Banner ID.
	 *
	 * @return array|null
	 */
	private function get_banner_by_id($banner_id)
	{
		foreach ($this->get_local_banners() as $banner) {
			if ($banner['id'] === $banner_id) {
				return $banner;
			}
		}

		return null;
	}

	/**
	 * Get the default banner payload.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_default_banner()
	{
		$presets = $this->get_presets();
		$preset  = $presets['neutral'];

		return [
			'id'                  => $this->generate_banner_id(),
			'name'                => __('New banner', 'jupiterx-core'),
			'enabled'             => true,
			'isLocal'             => true,
			'preset'              => 'neutral',
			'heading'             => '',
			'description'         => '',
			'couponCode'          => '',
			'mainImageURL'        => '',
			'backgroundImage'     => '',
			'overlayColor'        => '#111827',
			'overlayOpacity'      => 0,
			'backgroundImageOpacity' => 100,
			'backgroundGradient'  => '',
			'gradientStart'       => '#111827',
			'gradientEnd'         => '#374151',
			'gradientAngle'       => 90,
			'backgroundColor'     => $preset['backgroundColor'],
			'textColor'           => $preset['textColor'],
			'borderColor'         => $preset['borderColor'],
			'cta1Text'            => '',
			'cta1Url'             => '',
			'cta2Text'            => '',
			'cta2Url'             => '',
			'primaryButtonBg'     => $preset['primaryButtonBg'],
			'primaryButtonText'   => $preset['primaryButtonText'],
			'secondaryButtonBg'   => $preset['secondaryButtonBg'],
			'secondaryButtonText' => $preset['secondaryButtonText'],
			'titleFontSize'       => 16,
			'descriptionFontSize' => 12,
			'couponFontSize'      => 11,
			'ctaFontSize'         => 12,
			'containerHeight'     => 56,
			'containerWidth'      => 100,
			'startsAt'            => '',
			'expiresAt'           => '',
			'displayPermanently'  => true,
			'dismissMode'         => '24_hours',
			'targetScope'         => 'jupiterx_only',
			'targetPages'         => [],
			'elementSpacing'      => 12,
			'updatedAt'           => '',
			'customCSS'           => '',
		];
	}

	/**
	 * Banner presets.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_presets()
	{
		return [
			'neutral' => [
				'label'               => __('Neutral', 'jupiterx-core'),
				'backgroundColor'     => '#eef2f7',
				'textColor'           => '#1f2937',
				'borderColor'         => '#6b7280',
				'gradientStart'       => '#f8fafc',
				'gradientEnd'         => '#e5e7eb',
				'gradientAngle'       => 90,
				'primaryButtonBg'     => '#6b7280',
				'primaryButtonText'   => '#ffffff',
				'secondaryButtonBg'   => '#ffffff',
				'secondaryButtonText' => '#1f2937',
			],
			'news' => [
				'label'               => __('News', 'jupiterx-core'),
				'backgroundColor'     => '#ddfce0',
				'textColor'           => '#166534',
				'borderColor'         => '#22c55e',
				'gradientStart'       => '#ecfdf0',
				'gradientEnd'         => '#c7f9cc',
				'gradientAngle'       => 90,
				'primaryButtonBg'     => '#22c55e',
				'primaryButtonText'   => '#14532d',
				'secondaryButtonBg'   => '#ffffff',
				'secondaryButtonText' => '#166534',
			],
			'warning' => [
				'label'               => __('Warning', 'jupiterx-core'),
				'backgroundColor'     => '#ffe1e1',
				'textColor'           => '#7f1d1d',
				'borderColor'         => '#ef4444',
				'gradientStart'       => '#fff1f2',
				'gradientEnd'         => '#fecdd3',
				'gradientAngle'       => 90,
				'primaryButtonBg'     => '#ef4444',
				'primaryButtonText'   => '#7f1d1d',
				'secondaryButtonBg'   => '#ffffff',
				'secondaryButtonText' => '#7f1d1d',
			],
			'important' => [
				'label'               => __('Important', 'jupiterx-core'),
				'backgroundColor'     => '#fff1bf',
				'textColor'           => '#7c4a03',
				'borderColor'         => '#f59e0b',
				'gradientStart'       => '#fff8db',
				'gradientEnd'         => '#fde68a',
				'gradientAngle'       => 90,
				'primaryButtonBg'     => '#f59e0b',
				'primaryButtonText'   => '#78350f',
				'secondaryButtonBg'   => '#ffffff',
				'secondaryButtonText' => '#7c4a03',
			],
			'custom' => [
				'label'               => __('Custom', 'jupiterx-core'),
				'backgroundColor'     => '#eef2f7',
				'textColor'           => '#1f2937',
				'borderColor'         => '#6b7280',
				'gradientStart'       => '#f8fafc',
				'gradientEnd'         => '#e5e7eb',
				'gradientAngle'       => 90,
				'primaryButtonBg'     => '#6b7280',
				'primaryButtonText'   => '#ffffff',
				'secondaryButtonBg'   => '#ffffff',
				'secondaryButtonText' => '#1f2937',
			],
		];
	}

	/**
	 * Normalize banner shape.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return array
	 */
	private function normalize_banner($banner)
	{
		$defaults = $this->get_default_banner();
		$banner   = wp_parse_args(is_array($banner) ? $banner : [], $defaults);
		$banner['id'] = sanitize_key($banner['id']);

		return $banner;
	}

	/**
	 * Normalize a remote banner payload into the local banner shape.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Remote banner payload.
	 *
	 * @return array
	 */
	private function normalize_remote_banner($banner)
	{
		if (! is_array($banner)) {
			return [];
		}

		$id = isset($banner['id']) ? sanitize_key($banner['id']) : '';

		if (empty($id)) {
			return [];
		}

		$status = isset($banner['status']) ? sanitize_key($banner['status']) : 'draft';

		if (! in_array($status, ['active', 'scheduled', 'expired', 'draft'], true)) {
			$status = 'draft';
		}

		$content    = isset($banner['content']) && is_array($banner['content']) ? $banner['content'] : [];
		$appearance = isset($banner['appearance']) && is_array($banner['appearance']) ? $banner['appearance'] : [];
		$targeting  = isset($banner['targeting']) && is_array($banner['targeting']) ? $banner['targeting'] : [];
		$schedule   = isset($banner['schedule']) && is_array($banner['schedule']) ? $banner['schedule'] : [];
		$dismissal  = isset($banner['dismissal']) && is_array($banner['dismissal']) ? $banner['dismissal'] : [];
		$cta_1      = isset($content['cta_1']) && is_array($content['cta_1']) ? $content['cta_1'] : [];
		$cta_2      = isset($content['cta_2']) && is_array($content['cta_2']) ? $content['cta_2'] : [];

		$starts_at_raw   = isset($schedule['starts_at']) ? trim((string) $schedule['starts_at']) : '';
		$expires_at_raw  = isset($schedule['expires_at']) ? trim((string) $schedule['expires_at']) : '';
		$display_perm    = ! empty($schedule['display_permanently']);

		// Payload often sends display_permanently false with empty starts_at/expires_at (no date window).
		// Treat that as "show while active" — same as a permanent schedule for filtering purposes.
		if ('' === $starts_at_raw && '' === $expires_at_raw) {
			$display_perm = true;
		}

		$normalized = [
			'id'                     => $id,
			'name'                   => isset($banner['name']) ? sanitize_text_field($banner['name']) : '',
			'enabled'                => 'active' === $status || 'scheduled' === $status,
			'isLocal'                => false,
			'preset'                 => isset($banner['preset']) ? sanitize_key($banner['preset']) : 'custom',
			'heading'                => isset($content['title']) ? sanitize_text_field($content['title']) : '',
			'description'            => isset($content['description']) ? sanitize_textarea_field($content['description']) : '',
			'couponCode'             => isset($content['coupon_code']) ? sanitize_text_field($content['coupon_code']) : '',
			'mainImageURL'           => isset($content['image_url']) ? esc_url_raw($content['image_url']) : '',
			'backgroundImage'        => isset($appearance['background_image']) ? esc_url_raw($appearance['background_image']) : '',
			'overlayColor'           => isset($appearance['overlay_color']) ? sanitize_text_field($appearance['overlay_color']) : '#111827',
			'overlayOpacity'         => isset($appearance['overlay_opacity']) ? min(100, max(0, absint($appearance['overlay_opacity']))) : 0,
			'backgroundImageOpacity' => isset($appearance['background_image_opacity']) ? min(100, max(0, absint($appearance['background_image_opacity']))) : 100,
			'backgroundGradient'     => '',
			'gradientStart'          => isset($appearance['gradient_start']) ? sanitize_text_field($appearance['gradient_start']) : '',
			'gradientEnd'            => isset($appearance['gradient_end']) ? sanitize_text_field($appearance['gradient_end']) : '',
			'gradientAngle'          => isset($appearance['gradient_angle']) ? min(360, max(0, absint($appearance['gradient_angle']))) : 90,
			'backgroundColor'        => isset($appearance['background_color']) ? sanitize_text_field($appearance['background_color']) : '',
			'textColor'              => isset($appearance['text_color']) ? sanitize_text_field($appearance['text_color']) : '',
			'borderColor'            => isset($appearance['border_color']) ? sanitize_text_field($appearance['border_color']) : '',
			'cta1Text'               => isset($cta_1['label']) ? sanitize_text_field($cta_1['label']) : '',
			'cta1Url'                => isset($cta_1['url']) ? esc_url_raw($cta_1['url']) : '',
			'cta2Text'               => isset($cta_2['label']) ? sanitize_text_field($cta_2['label']) : '',
			'cta2Url'                => isset($cta_2['url']) ? esc_url_raw($cta_2['url']) : '',
			'primaryButtonBg'        => isset($appearance['primary_button_bg']) ? sanitize_text_field($appearance['primary_button_bg']) : '',
			'primaryButtonText'      => isset($appearance['primary_button_text']) ? sanitize_text_field($appearance['primary_button_text']) : '',
			'secondaryButtonBg'      => isset($appearance['secondary_button_bg']) ? sanitize_text_field($appearance['secondary_button_bg']) : '',
			'secondaryButtonText'    => isset($appearance['secondary_button_text']) ? sanitize_text_field($appearance['secondary_button_text']) : '',
			'titleFontSize'          => isset($appearance['title_font_size']) ? min(40, max(10, absint($appearance['title_font_size']))) : 16,
			'descriptionFontSize'    => isset($appearance['description_font_size']) ? min(32, max(10, absint($appearance['description_font_size']))) : 12,
			'couponFontSize'         => isset($appearance['coupon_font_size']) ? min(28, max(10, absint($appearance['coupon_font_size']))) : 11,
			'ctaFontSize'            => isset($appearance['cta_font_size']) ? min(28, max(10, absint($appearance['cta_font_size']))) : 12,
			'containerHeight'        => isset($appearance['container_height']) ? min(240, max(40, absint($appearance['container_height']))) : 56,
			'containerWidth'         => isset($appearance['container_width']) ? min(100, max(20, absint($appearance['container_width']))) : 100,
			'startsAt'               => '' !== $starts_at_raw ? sanitize_text_field($starts_at_raw) : '',
			'expiresAt'              => '' !== $expires_at_raw ? sanitize_text_field($expires_at_raw) : '',
			'displayPermanently'     => $display_perm,
			'dismissMode'            => isset($dismissal['mode']) ? sanitize_key($dismissal['mode']) : '24_hours',
			'targetScope'            => isset($targeting['scope']) ? sanitize_key($targeting['scope']) : 'jupiterx_only',
			'targetPages'            => isset($targeting['jupiterx_pages']) && is_array($targeting['jupiterx_pages']) ? array_map('sanitize_key', $targeting['jupiterx_pages']) : [],
			'elementSpacing'         => isset($appearance['element_spacing']) ? min(48, max(0, absint($appearance['element_spacing']))) : 12,
			'updatedAt'              => isset($banner['signature']) ? sanitize_text_field($banner['signature']) : (isset($banner['generated_at']) ? sanitize_text_field($banner['generated_at']) : gmdate(DATE_ATOM)),
			'customCSS'              => '',
		];

		$normalized = $this->maybe_apply_remote_preset_defaults($normalized);

		if (! empty($normalized['gradientStart']) && ! empty($normalized['gradientEnd'])) {
			$normalized['backgroundGradient'] = sprintf(
				'linear-gradient(%1$ddeg, %2$s, %3$s)',
				$normalized['gradientAngle'],
				$normalized['gradientStart'],
				$normalized['gradientEnd']
			);
		}

		return $this->normalize_banner($normalized);
	}

	/**
	 * Apply selected preset defaults for remote banners when the payload still carries untouched neutral styling.
	 *
	 * This makes JupiterX more resilient when the source plugin stores a non-custom preset label
	 * but does not serialize the matching appearance palette yet.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Normalized remote banner.
	 *
	 * @return array
	 */
	private function maybe_apply_remote_preset_defaults(array $banner)
	{
		$preset_key = isset($banner['preset']) ? $banner['preset'] : 'custom';
		$presets    = $this->get_presets();

		if (empty($presets[$preset_key]) || 'custom' === $preset_key || 'neutral' === $preset_key) {
			return $banner;
		}

		if (! $this->remote_banner_uses_neutral_defaults($banner)) {
			return $banner;
		}

		$preset = $presets[$preset_key];

		$banner['backgroundColor']     = $preset['backgroundColor'];
		$banner['textColor']           = $preset['textColor'];
		$banner['borderColor']         = $preset['borderColor'];
		$banner['gradientStart']       = $preset['gradientStart'];
		$banner['gradientEnd']         = $preset['gradientEnd'];
		$banner['gradientAngle']       = $preset['gradientAngle'];
		$banner['primaryButtonBg']     = $preset['primaryButtonBg'];
		$banner['primaryButtonText']   = $preset['primaryButtonText'];
		$banner['secondaryButtonBg']   = $preset['secondaryButtonBg'];
		$banner['secondaryButtonText'] = $preset['secondaryButtonText'];

		return $banner;
	}

	/**
	 * Check whether a remote banner is still carrying neutral preset defaults.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Normalized remote banner.
	 *
	 * @return bool
	 */
	private function remote_banner_uses_neutral_defaults(array $banner)
	{
		$neutral = $this->get_presets()['neutral'];

		return
			$neutral['backgroundColor'] === ($banner['backgroundColor'] ?? '') &&
			$neutral['textColor'] === ($banner['textColor'] ?? '') &&
			$neutral['borderColor'] === ($banner['borderColor'] ?? '') &&
			$neutral['gradientStart'] === ($banner['gradientStart'] ?? '') &&
			$neutral['gradientEnd'] === ($banner['gradientEnd'] ?? '') &&
			(string) $neutral['gradientAngle'] === (string) ($banner['gradientAngle'] ?? '') &&
			$neutral['primaryButtonBg'] === ($banner['primaryButtonBg'] ?? '') &&
			$neutral['primaryButtonText'] === ($banner['primaryButtonText'] ?? '') &&
			$neutral['secondaryButtonBg'] === ($banner['secondaryButtonBg'] ?? '') &&
			$neutral['secondaryButtonText'] === ($banner['secondaryButtonText'] ?? '');
	}

	/**
	 * Generate a lowercase-safe banner ID.
	 *
	 * @since 4.13.0
	 *
	 * @return string
	 */
	private function generate_banner_id()
	{
		return 'jx-banner-' . strtolower(wp_generate_password(8, false, false));
	}

	/**
	 * Sanitize admin form input.
	 *
	 * @since 4.13.0
	 *
	 * @param array  $input     Raw input.
	 * @param string $banner_id Existing banner ID.
	 *
	 * @return array
	 */
	private function sanitize_banner_input(array $input, $banner_id)
	{
		$defaults = $this->get_default_banner();
		$preset   = isset($input['preset']) ? sanitize_key(wp_unslash($input['preset'])) : 'neutral';
		$presets  = $this->get_presets();

		if (! isset($presets[$preset])) {
			$preset = 'neutral';
		}

		$banner = [
			'id'                  => ! empty($banner_id) ? $banner_id : $defaults['id'],
			'name'                => isset($input['name']) ? sanitize_text_field(wp_unslash($input['name'])) : '',
			'enabled'             => ! empty($input['enabled']),
			'isLocal'             => true,
			'preset'              => $preset,
			'heading'             => isset($input['heading']) ? sanitize_text_field(wp_unslash($input['heading'])) : '',
			'description'         => isset($input['description']) ? sanitize_textarea_field(wp_unslash($input['description'])) : '',
			'couponCode'          => isset($input['couponCode']) ? sanitize_text_field(wp_unslash($input['couponCode'])) : '',
			'mainImageURL'        => isset($input['mainImageURL']) ? esc_url_raw(wp_unslash($input['mainImageURL'])) : '',
			'backgroundImage'     => isset($input['backgroundImage']) ? esc_url_raw(wp_unslash($input['backgroundImage'])) : '',
			'overlayColor'        => isset($input['overlayColor']) ? sanitize_text_field(wp_unslash($input['overlayColor'])) : '#111827',
			'overlayOpacity'      => isset($input['overlayOpacity']) ? min(100, max(0, absint(wp_unslash($input['overlayOpacity'])))) : 0,
			'backgroundImageOpacity' => isset($input['backgroundImageOpacity']) ? min(100, max(0, absint(wp_unslash($input['backgroundImageOpacity'])))) : 100,
			'backgroundGradient'  => '',
			'gradientStart'       => isset($input['gradientStart']) ? sanitize_text_field(wp_unslash($input['gradientStart'])) : '#111827',
			'gradientEnd'         => isset($input['gradientEnd']) ? sanitize_text_field(wp_unslash($input['gradientEnd'])) : '#374151',
			'gradientAngle'       => isset($input['gradientAngle']) ? absint(wp_unslash($input['gradientAngle'])) : 90,
			'backgroundColor'     => isset($input['backgroundColor']) ? sanitize_text_field(wp_unslash($input['backgroundColor'])) : '',
			'textColor'           => isset($input['textColor']) ? sanitize_text_field(wp_unslash($input['textColor'])) : '',
			'borderColor'         => isset($input['borderColor']) ? sanitize_text_field(wp_unslash($input['borderColor'])) : '',
			'cta1Text'            => isset($input['cta1Text']) ? sanitize_text_field(wp_unslash($input['cta1Text'])) : '',
			'cta1Url'             => isset($input['cta1Url']) ? esc_url_raw(wp_unslash($input['cta1Url'])) : '',
			'cta2Text'            => isset($input['cta2Text']) ? sanitize_text_field(wp_unslash($input['cta2Text'])) : '',
			'cta2Url'             => isset($input['cta2Url']) ? esc_url_raw(wp_unslash($input['cta2Url'])) : '',
			'primaryButtonBg'     => isset($input['primaryButtonBg']) ? sanitize_text_field(wp_unslash($input['primaryButtonBg'])) : '',
			'primaryButtonText'   => isset($input['primaryButtonText']) ? sanitize_text_field(wp_unslash($input['primaryButtonText'])) : '',
			'secondaryButtonBg'   => isset($input['secondaryButtonBg']) ? sanitize_text_field(wp_unslash($input['secondaryButtonBg'])) : '',
			'secondaryButtonText' => isset($input['secondaryButtonText']) ? sanitize_text_field(wp_unslash($input['secondaryButtonText'])) : '',
			'titleFontSize'       => isset($input['titleFontSize']) ? min(40, max(10, absint(wp_unslash($input['titleFontSize'])))) : 16,
			'descriptionFontSize' => isset($input['descriptionFontSize']) ? min(32, max(10, absint(wp_unslash($input['descriptionFontSize'])))) : 12,
			'couponFontSize'      => isset($input['couponFontSize']) ? min(28, max(10, absint(wp_unslash($input['couponFontSize'])))) : 11,
			'ctaFontSize'         => isset($input['ctaFontSize']) ? min(28, max(10, absint(wp_unslash($input['ctaFontSize'])))) : 12,
			'containerHeight'     => isset($input['containerHeight']) ? min(240, max(40, absint(wp_unslash($input['containerHeight'])))) : 56,
			'containerWidth'      => isset($input['containerWidth']) ? min(100, max(20, absint(wp_unslash($input['containerWidth'])))) : 100,
			'startsAt'            => $this->normalize_datetime_local_input(isset($input['startsAt']) ? wp_unslash($input['startsAt']) : ''),
			'expiresAt'           => $this->normalize_datetime_local_input(isset($input['expiresAt']) ? wp_unslash($input['expiresAt']) : ''),
			'displayPermanently'  => ! empty($input['displayPermanently']),
			'dismissMode'         => isset($input['dismissMode']) ? sanitize_key(wp_unslash($input['dismissMode'])) : '24_hours',
			'targetScope'         => isset($input['targetScope']) ? sanitize_key(wp_unslash($input['targetScope'])) : 'jupiterx_only',
			'targetPages'         => isset($input['targetPages']) && is_array($input['targetPages']) ? array_map('sanitize_key', wp_unslash($input['targetPages'])) : [],
			'elementSpacing'      => isset($input['elementSpacing']) ? absint(wp_unslash($input['elementSpacing'])) : 12,
			'updatedAt'           => gmdate(DATE_ATOM),
			'customCSS'           => '',
		];

		if (! empty($banner['gradientStart']) && ! empty($banner['gradientEnd'])) {
			$banner['backgroundGradient'] = sprintf(
				'linear-gradient(%1$ddeg, %2$s, %3$s)',
				min(360, $banner['gradientAngle']),
				$banner['gradientStart'],
				$banner['gradientEnd']
			);
		}

		if (empty($banner['name'])) {
			$banner['name'] = ! empty($banner['heading']) ? $banner['heading'] : __('Untitled banner', 'jupiterx-core');
		}

		if (! in_array($banner['targetScope'], ['jupiterx_only', 'jupiterx_and_dashboard'], true)) {
			$banner['targetScope'] = 'jupiterx_only';
		}

		$banner['targetPages'] = array_values(
			array_intersect(
				$banner['targetPages'],
				array_keys($this->get_jupiterx_target_pages())
			)
		);

		if (! isset($this->get_dismiss_mode_options()[$banner['dismissMode']])) {
			$banner['dismissMode'] = '24_hours';
		}

		return $this->normalize_banner($banner);
	}

	/**
	 * Check whether a promotion ID exists.
	 *
	 * @since 4.13.0
	 *
	 * @param string $promotion_id Promotion ID.
	 *
	 * @return bool
	 */
	private function is_valid_promotion_id($promotion_id)
	{
		if (empty($promotion_id)) {
			return false;
		}

		foreach ($this->get_banners_for_dismiss_lookup() as $banner) {
			if (! empty($banner['id']) && $promotion_id === $banner['id']) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if current page is a JupiterX page.
	 *
	 * @since 4.13.0
	 *
	 * @return bool
	 */
	private function is_jupiterx_admin_page()
	{
		$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (! empty($page) && false !== strpos($page, 'jupiterx')) {
			return true;
		}

		$post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return ! empty($post_type) && false !== strpos($post_type, 'jupiterx');
	}

	/**
	 * Get the current JupiterX target page key.
	 *
	 * @since 4.13.0
	 *
	 * @return string
	 */
	private function get_current_jupiterx_target_page()
	{
		$page      = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return $this->resolve_jupiterx_target_page_key($page, $post_type);
	}

	/**
	 * Resolve JupiterX targeting key from admin query parameters.
	 *
	 * @since 4.13.0
	 *
	 * @param string $page      Sanitized `page` query value.
	 * @param string $post_type Sanitized `post_type` query value.
	 *
	 * @return string
	 */
	private function resolve_jupiterx_target_page_key($page, $post_type)
	{
		$page = sanitize_key((string) $page);

		if (! empty($page)) {
			$known_pages = array_keys($this->get_jupiterx_target_pages());

			if (in_array($page, $known_pages, true)) {
				return $page;
			}

			if ('jupiterx' === $page || false !== strpos($page, 'jupiterx')) {
				return 'jupiterx';
			}
		}

		$post_type = sanitize_key((string) $post_type);

		if ('jupiterx-popups' === $post_type) {
			return 'jupiterx-popups';
		}

		return '';
	}

	/**
	 * Screen context for banner targeting (current admin request).
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_banner_screen_context_from_globals()
	{
		return [
			'is_jupiterx_admin_page' => $this->is_jupiterx_admin_page(),
			'is_dashboard_page'      => $this->is_dashboard_page(),
			'current_target_page'    => $this->get_current_jupiterx_target_page(),
		];
	}

	/**
	 * Whether to load remote banners asynchronously on this screen (WP Dashboard or JupiterX admin).
	 *
	 * @since 4.13.0
	 *
	 * @return bool
	 */
	private function should_load_promotion_banners_async()
	{
		$ctx = $this->get_banner_screen_context_from_globals();

		return ! empty($ctx['is_dashboard_page']) || ! empty($ctx['is_jupiterx_admin_page']);
	}

	/**
	 * Parse screen context from the AJAX request (must match the visiting admin screen).
	 *
	 * @since 4.13.0
	 *
	 * @return array|null
	 */
	private function resolve_banner_screen_context_from_ajax_request()
	{
		$jx_pagenow = isset($_POST['jx_pagenow']) ? wp_unslash($_POST['jx_pagenow']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$jx_pagenow = is_string($jx_pagenow) ? basename($jx_pagenow) : '';

		if (! is_string($jx_pagenow) || ! preg_match('/^[a-zA-Z0-9._-]+$/', $jx_pagenow)) {
			$jx_pagenow = '';
		}

		$jx_page      = isset($_POST['jx_page']) ? sanitize_text_field(wp_unslash($_POST['jx_page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$jx_post_type = isset($_POST['jx_post_type']) ? sanitize_key(wp_unslash($_POST['jx_post_type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$is_dashboard = ('index.php' === $jx_pagenow);
		$is_jupiterx  = (! empty($jx_page) && false !== strpos($jx_page, 'jupiterx')) || (! empty($jx_post_type) && false !== strpos($jx_post_type, 'jupiterx'));

		if (! $is_dashboard && ! $is_jupiterx) {
			return null;
		}

		return [
			'is_jupiterx_admin_page' => $is_jupiterx,
			'is_dashboard_page'      => $is_dashboard,
			'current_target_page'    => $this->resolve_jupiterx_target_page_key($jx_page, $jx_post_type),
		];
	}

	/**
	 * Filter remote banners for a screen context and user state.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banners    Normalized banner rows.
	 * @param array $screen_ctx Screen context from get_banner_screen_context_from_globals() or AJAX resolver.
	 *
	 * @return array Banners prepared for rendering.
	 */
	private function filter_active_banners_for_context(array $banners, array $screen_ctx)
	{
		$filtered               = [];
		$now                    = current_time('timestamp');
		$is_jupiterx_admin_page = ! empty($screen_ctx['is_jupiterx_admin_page']);
		$is_dashboard_page      = ! empty($screen_ctx['is_dashboard_page']);
		$current_target_page    = isset($screen_ctx['current_target_page']) ? $screen_ctx['current_target_page'] : '';

		foreach ($banners as $banner) {
			$banner = $this->prepare_banner_for_render($banner);

			if (empty($banner['id']) || empty($banner['enabled'])) {
				continue;
			}

			if (empty($banner['heading']) && empty($banner['description']) && empty($banner['mainImageURL'])) {
				continue;
			}

			if ($this->is_banner_dismissed($banner)) {
				continue;
			}

			if (! empty($banner['startsAt']) && strtotime($banner['startsAt']) > $now) {
				continue;
			}

			if (! empty($banner['expiresAt']) && strtotime($banner['expiresAt']) < $now) {
				continue;
			}

			if (
				empty($banner['displayPermanently']) &&
				empty($banner['startsAt']) &&
				empty($banner['expiresAt'])
			) {
				continue;
			}

			if ('jupiterx_only' === $banner['targetScope'] && ! $is_jupiterx_admin_page) {
				continue;
			}

			if ('jupiterx_and_dashboard' === $banner['targetScope'] && ! $is_jupiterx_admin_page && ! $is_dashboard_page) {
				continue;
			}

			if ($is_jupiterx_admin_page && ! empty($banner['targetPages']) && ! in_array($current_target_page, $banner['targetPages'], true)) {
				continue;
			}

			$filtered[] = $banner;
		}

		return $filtered;
	}

	/**
	 * Check if current page is the Dashboard.
	 *
	 * @since 4.13.0
	 *
	 * @return bool
	 */
	private function is_dashboard_page()
	{
		global $pagenow;

		return 'index.php' === $pagenow;
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function enqueue_assets()
	{
		if (! is_admin() || is_network_admin() || ! current_user_can('manage_options')) {
			return;
		}

		if (! $this->should_load_promotion_banners_async()) {
			return;
		}

		global $pagenow;

		wp_enqueue_style(
			'jupiterx-promotion-banner-font',
			'https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;800&display=swap',
			[],
			'1.0.0'
		);

		wp_enqueue_style(
			'jupiterx-admin-promotion-banner',
			jupiterx_core()->plugin_url() . 'includes/control-panel-2/assets/css/promotion-banner.css',
			['jupiterx-promotion-banner-font'],
			jupiterx_core()->version()
		);

		wp_enqueue_script(
			'jupiterx-admin-promotion-banner',
			jupiterx_core()->plugin_url() . 'includes/control-panel-2/assets/js/promotion-banner.js',
			['jquery'],
			jupiterx_core()->version(),
			true
		);

		wp_localize_script(
			'jupiterx-admin-promotion-banner',
			'jxPromotionBanner',
			[
				'ajaxUrl'    => admin_url('admin-ajax.php'),
				'fetchNonce' => wp_create_nonce('jupiterx_fetch_promotion_banners'),
				'pagenow'    => isset($pagenow) ? $pagenow : '',
			]
		);
	}

	/**
	 * Build inline CSS.
	 *
	 * @since 4.13.0
	 *
	 * @param array $active_banners Active banners.
	 *
	 * @return string
	 */
	private function get_custom_css(array $active_banners)
	{
		$css = '';

		foreach ($active_banners as $banner) {
			if (empty($banner['id']) || empty($banner['customCSS'])) {
				continue;
			}

			$banner_class = 'jx-promotion-banner--' . sanitize_html_class($banner['id']);
			$css         .= "\n/* Custom CSS for {$banner['id']} */\n";

			if (false !== strpos($banner['customCSS'], '{')) {
				$css .= trim($banner['customCSS']) . "\n";
			} else {
				$css .= ".{$banner_class} {\n";
				$css .= "\t" . str_replace("\n", "\n\t", trim($banner['customCSS'])) . "\n";
				$css .= "}\n";
			}
		}

		return $css;
	}

	/**
	 * Prepare a banner for rendering.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return array
	 */
	private function prepare_banner_for_render(array $banner)
	{
		$banner = $this->normalize_banner($banner);

		$banner['promotion_id']   = $banner['id'];
		$banner['nonce']          = wp_create_nonce('jupiterx_dismiss_admin_promotion');
		$banner['image_url']      = $banner['mainImageURL'];
		$banner['coupon_code']    = $banner['couponCode'];
		$banner['has_code']       = ! empty($banner['couponCode']);
		$banner['has_cta']        = ! empty($banner['cta1Text']) && ! empty($banner['cta1Url']);
		$banner['has_second_cta'] = ! empty($banner['cta2Text']) && ! empty($banner['cta2Url']);
		$banner['bg_style']       = $this->build_banner_style($banner);
		$banner['primary_button_style']   = $this->build_button_style($banner['primaryButtonBg'], $banner['primaryButtonText']);
		$banner['secondary_button_style'] = $this->build_button_style($banner['secondaryButtonBg'], $banner['secondaryButtonText'], $banner['borderColor']);
		$banner['banner_id']      = $banner['id'];
		$banner['is_dismissible'] = true;
		$banner['dismiss_signature'] = $this->get_banner_dismiss_signature($banner);

		return $banner;
	}

	/**
	 * Build banner container style.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return string
	 */
	private function build_banner_style(array $banner)
	{
		$styles = [];

		if (! empty($banner['textColor'])) {
			$styles[] = 'color:' . $banner['textColor'];
		}

		if (! empty($banner['borderColor'])) {
			$styles[] = 'border:1px solid ' . $banner['borderColor'];
			$styles[] = '--jx-banner-accent:' . $banner['borderColor'];
		}

		$styles[] = '--jx-banner-gap:' . absint($banner['elementSpacing']) . 'px';
		$styles[] = '--jx-banner-title-size:' . absint($banner['titleFontSize']) . 'px';
		$styles[] = '--jx-banner-description-size:' . absint($banner['descriptionFontSize']) . 'px';
		$styles[] = '--jx-banner-coupon-size:' . absint($banner['couponFontSize']) . 'px';
		$styles[] = '--jx-banner-cta-size:' . absint($banner['ctaFontSize']) . 'px';
		$styles[] = '--jx-banner-overlay-color:' . $banner['overlayColor'];
		$styles[] = '--jx-banner-overlay-opacity:' . (absint($banner['overlayOpacity']) / 100);
		$styles[] = '--jx-banner-image-opacity:' . (absint($banner['backgroundImageOpacity']) / 100);
		$styles[] = 'min-height:' . absint($banner['containerHeight']) . 'px';
		$styles[] = 'height:' . absint($banner['containerHeight']) . 'px';
		$container_width = absint($banner['containerWidth']);

		if ($container_width >= 100) {
			$styles[] = 'width:auto';
		} else {
			$styles[] = 'width:calc(' . $container_width . '% - 22px)';
		}

		$styles[] = 'max-width:100%';

		if (! empty($banner['backgroundColor'])) {
			$styles[] = 'background-color:' . $banner['backgroundColor'];
		}

		if (! empty($banner['backgroundGradient']) && empty($banner['backgroundImage'])) {
			$styles[] = 'background-image:' . $banner['backgroundGradient'];
		}

		if (! empty($banner['backgroundImage'])) {
			$background_image = 'linear-gradient(rgba(255,255,255,' . (1 - (absint($banner['backgroundImageOpacity']) / 100)) . '), rgba(255,255,255,' . (1 - (absint($banner['backgroundImageOpacity']) / 100)) . ')), url(' . esc_url_raw($banner['backgroundImage']) . ')';
			$styles[] = 'background-image:' . $background_image;
		}

		return implode(';', $styles);
	}

	/**
	 * Build button style.
	 *
	 * @since 4.13.0
	 *
	 * @param string $background Background color.
	 * @param string $text       Text color.
	 * @param string $border     Border color.
	 *
	 * @return string
	 */
	private function build_button_style($background, $text, $border = '')
	{
		$styles = [];

		if ('' !== $background) {
			$styles[] = 'background:' . $background;
		}

		if ('' !== $text) {
			$styles[] = 'color:' . $text;
		}

		if ('' !== $border) {
			$styles[] = 'border-color:' . $border;
		}

		return implode(';', $styles);
	}

	/**
	 * Render a preview banner inside the admin editor.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return void
	 */
	private function render_preview_banner(array $banner)
	{
		if (empty($banner['heading'])) {
			$banner['heading'] = __('Banner preview title', 'jupiterx-core');
		}

		if (empty($banner['description'])) {
			$banner['description'] = __('Banner preview description. Save your changes and open the JupiterX dashboard to test the real placement.', 'jupiterx-core');
		}

		if (empty($banner['cta1Text'])) {
			$banner['cta1Text'] = __('Primary action', 'jupiterx-core');
			$banner['cta1Url']  = '#';
			$banner['has_cta']  = true;
		}

		$this->render_banner_template($banner);
	}

	/**
	 * Render a banner via the PHP template.
	 *
	 * @since 4.13.0
	 *
	 * @param array $context Banner context.
	 *
	 * @return void
	 */
	private function render_banner_template(array $context)
	{
		$template = __DIR__ . '/views/promotion-banner.php';

		if (! file_exists($template)) {
			return;
		}

		$promotion_id           = $context['promotion_id'];
		$nonce                  = $context['nonce'];
		$image_url              = $context['image_url'];
		$heading                = $context['heading'];
		$description            = $context['description'];
		$coupon_code            = $context['coupon_code'];
		$has_code               = $context['has_code'];
		$has_cta                = $context['has_cta'];
		$has_second_cta         = $context['has_second_cta'];
		$cta_1_text             = $context['cta1Text'];
		$cta_1_url              = $context['cta1Url'];
		$cta_2_text             = $context['cta2Text'];
		$cta_2_url              = $context['cta2Url'];
		$bg_style               = $context['bg_style'];
		$primary_button_style   = $context['primary_button_style'];
		$secondary_button_style = $context['secondary_button_style'];
		$is_dismissible         = $context['is_dismissible'];
		$banner_id              = $context['banner_id'];

		include $template;
	}

	/**
	 * Capture rendered banner HTML for AJAX responses.
	 *
	 * @since 4.13.0
	 *
	 * @param array $prepared Prepared banner context.
	 *
	 * @return string
	 */
	private function capture_banner_html(array $prepared)
	{
		ob_start();
		$this->render_banner_template($prepared);

		return (string) ob_get_clean();
	}

	/**
	 * Fetch remote banners and return rendered HTML (non-blocking admin AJAX).
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function ajax_fetch_promotion_banners()
	{
		check_ajax_referer('jupiterx_fetch_promotion_banners', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(null, 403);
		}

		$screen_ctx = $this->resolve_banner_screen_context_from_ajax_request();

		if (null === $screen_ctx) {
			wp_send_json_success(
				[
					'html' => '',
					'css'  => '',
				]
			);
		}

		$payload = $this->fetch_remote_promotion_payload();

		if (null === $payload) {
			wp_send_json_success(
				[
					'html' => '',
					'css'  => '',
				]
			);

			return;
		}

		$normalized_list = [];

		foreach ($payload['banners'] as $banner) {
			$normalized = $this->normalize_remote_banner($banner);

			if (! empty($normalized)) {
				$normalized_list[] = $normalized;
			}
		}

		$this->store_remote_banners_snapshot($normalized_list);

		$active = $this->filter_active_banners_for_context($normalized_list, $screen_ctx);

		if (empty($active)) {
			wp_send_json_success(
				[
					'html' => '',
					'css'  => '',
				]
			);
		}

		$html = '';
		$css  = $this->get_custom_css($active);

		foreach ($active as $prepared) {
			$html .= $this->capture_banner_html($prepared);
		}

		wp_send_json_success(
			[
				'html' => $html,
				'css'  => $css,
			]
		);
	}

	/**
	 * Handle dismiss action.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function ajax_dismiss()
	{
		check_ajax_referer('jupiterx_dismiss_admin_promotion', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error();
		}

		$promotion_id = isset($_POST['promotionId']) ? sanitize_text_field(wp_unslash($_POST['promotionId'])) : '';

		if (empty($promotion_id) || ! $this->is_valid_promotion_id($promotion_id)) {
			wp_send_json_error();
		}

		update_user_meta(
			get_current_user_id(),
			self::DISMISS_META_PREFIX . $promotion_id,
			$this->get_dismiss_payload($promotion_id)
		);

		wp_send_json_success();
	}

	/**
	 * Check if a banner is dismissed.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return bool
	 */
	private function is_banner_dismissed(array $banner)
	{
		$dismissed = get_user_meta(get_current_user_id(), self::DISMISS_META_PREFIX . $banner['id'], true);

		if (empty($dismissed) || ! is_array($dismissed)) {
			return false;
		}

		$mode = isset($dismissed['mode']) ? $dismissed['mode'] : 'permanent';

		if ('until_updated' === $mode) {
			return isset($dismissed['signature'], $banner['dismiss_signature']) && $dismissed['signature'] === $banner['dismiss_signature'];
		}

		if (isset($dismissed['until']) && is_numeric($dismissed['until'])) {
			return (int) $dismissed['until'] > current_time('timestamp');
		}

		return true;
	}

	/**
	 * Clear dismiss meta when a banner changes.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return void
	 */
	private function clear_dismiss_meta_if_needed(array $banner)
	{
		delete_metadata('user', 0, self::DISMISS_META_PREFIX . $banner['id'], '', true);
	}

	/**
	 * Build dismiss metadata payload.
	 *
	 * @since 4.13.0
	 *
	 * @param string $promotion_id Promotion ID.
	 *
	 * @return array
	 */
	private function get_dismiss_payload($promotion_id)
	{
		$banner = $this->find_banner_for_dismiss($promotion_id);
		$mode   = ! empty($banner['dismissMode']) ? $banner['dismissMode'] : 'permanent';
		$now    = current_time('timestamp');

		$payload = [
			'mode'         => $mode,
			'dismissed_at' => $now,
		];

		$durations = [
			'24_hours' => DAY_IN_SECONDS,
			'3_days'   => 3 * DAY_IN_SECONDS,
			'7_days'   => 7 * DAY_IN_SECONDS,
		];

		if (isset($durations[$mode])) {
			$payload['until'] = $now + $durations[$mode];
		}

		if ('until_updated' === $mode && ! empty($banner)) {
			$payload['signature'] = $this->get_banner_dismiss_signature($banner);
		}

		return $payload;
	}

	/**
	 * Find banner by ID for dismissal metadata.
	 *
	 * @since 4.13.0
	 *
	 * @param string $promotion_id Promotion ID.
	 *
	 * @return array
	 */
	private function find_banner_for_dismiss($promotion_id)
	{
		foreach ($this->get_banners_for_dismiss_lookup() as $banner) {
			if (! empty($banner['id']) && $promotion_id === $banner['id']) {
				return $this->normalize_banner($banner);
			}
		}

		return [];
	}

	/**
	 * Get dismiss signature for a banner.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return string
	 */
	private function get_banner_dismiss_signature(array $banner)
	{
		return md5(
			wp_json_encode(
				[
					'heading'     => $banner['heading'],
					'description' => $banner['description'],
					'couponCode'  => $banner['couponCode'],
					'cta1Text'    => $banner['cta1Text'],
					'cta1Url'     => $banner['cta1Url'],
					'cta2Text'    => $banner['cta2Text'],
					'cta2Url'     => $banner['cta2Url'],
					'updatedAt'   => isset($banner['updatedAt']) ? $banner['updatedAt'] : '',
				]
			)
		);
	}

	/**
	 * Build an admin page URL.
	 *
	 * @since 4.13.0
	 *
	 * @param array $args Extra query args.
	 *
	 * @return string
	 */
	private function get_admin_page_url(array $args = [])
	{
		$args = array_merge(
			[
				'page' => self::PAGE_SLUG,
			],
			$args
		);

		return add_query_arg($args, admin_url('admin.php'));
	}

	/**
	 * Get preset label.
	 *
	 * @since 4.13.0
	 *
	 * @param string $preset_key Preset key.
	 *
	 * @return string
	 */
	private function get_preset_label($preset_key)
	{
		$presets = $this->get_presets();

		return isset($presets[$preset_key]) ? $presets[$preset_key]['label'] : __('Custom', 'jupiterx-core');
	}

	/**
	 * Get target scope label.
	 *
	 * @since 4.13.0
	 *
	 * @param string $target_scope Scope value.
	 *
	 * @return string
	 */
	private function get_target_scope_label($target_scope)
	{
		if ('jupiterx_and_dashboard' === $target_scope) {
			return __('JupiterX + Dashboard', 'jupiterx-core');
		}

		return __('JupiterX only', 'jupiterx-core');
	}

	/**
	 * Get full target summary for list display.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return string
	 */
	private function get_target_summary(array $banner)
	{
		$summary = $this->get_target_scope_label($banner['targetScope']);

		if (empty($banner['targetPages'])) {
			return $summary;
		}

		$labels = [];
		$pages  = $this->get_jupiterx_target_pages();

		foreach ($banner['targetPages'] as $page_key) {
			if (isset($pages[$page_key])) {
				$labels[] = $pages[$page_key];
			}
		}

		if (empty($labels)) {
			return $summary;
		}

		return $summary . ' - ' . implode(', ', $labels);
	}

	/**
	 * Get available JupiterX page targets.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_jupiterx_target_pages()
	{
		return [
			'jupiterx'               => __('Control Panel (Dashboard / Settings / Help / internal tabs)', 'jupiterx-core'),
			'jupiterx-layout-builder' => __('Layout Builder', 'jupiterx-core'),
			'jupiterx-custom-snippets' => __('Custom Snippets', 'jupiterx-core'),
			'jupiterx-custom-fonts' => __('Custom Fonts', 'jupiterx-core'),
			'jupiterx-custom-icons' => __('Custom Icons', 'jupiterx-core'),
			'jupiterx-popups'       => __('Popups', 'jupiterx-core'),
			'jupiterx-setup-wizard' => __('Setup Wizard', 'jupiterx-core'),
			'jupiterx-help'         => __('Help', 'jupiterx-core'),
			self::PAGE_SLUG         => __('Promo Banner Manager', 'jupiterx-core'),
		];
	}

	/**
	 * Get dismiss mode options.
	 *
	 * @since 4.13.0
	 *
	 * @return array
	 */
	private function get_dismiss_mode_options()
	{
		return [
			'24_hours'    => __('Show again after 24 hours', 'jupiterx-core'),
			'3_days'      => __('Show again after 3 days', 'jupiterx-core'),
			'7_days'      => __('Show again after 7 days', 'jupiterx-core'),
			'until_updated' => __('Show again when the banner is updated', 'jupiterx-core'),
			'permanent'   => __('Hide permanently', 'jupiterx-core'),
		];
	}

	/**
	 * Get banner schedule summary.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return string
	 */
	private function get_schedule_summary(array $banner)
	{
		if (empty($banner['startsAt']) && empty($banner['expiresAt'])) {
			return ! empty($banner['displayPermanently']) ? __('Always on', 'jupiterx-core') : __('No schedule', 'jupiterx-core');
		}

		$parts = [];

		if (! empty($banner['startsAt'])) {
			$parts[] = sprintf(__('Starts %s', 'jupiterx-core'), $this->format_datetime_label($banner['startsAt']));
		}

		if (! empty($banner['expiresAt'])) {
			$parts[] = sprintf(__('Ends %s', 'jupiterx-core'), $this->format_datetime_label($banner['expiresAt']));
		}

		return implode(' / ', $parts);
	}

	/**
	 * Get banner status.
	 *
	 * @since 4.13.0
	 *
	 * @param array $banner Banner data.
	 *
	 * @return string
	 */
	private function get_banner_status(array $banner)
	{
		if (empty($banner['enabled'])) {
			return __('Draft', 'jupiterx-core');
		}

		$now = current_time('timestamp');

		if (! empty($banner['startsAt']) && strtotime($banner['startsAt']) > $now) {
			return __('Scheduled', 'jupiterx-core');
		}

		if (! empty($banner['expiresAt']) && strtotime($banner['expiresAt']) < $now) {
			return __('Expired', 'jupiterx-core');
		}

		return __('Active', 'jupiterx-core');
	}

	/**
	 * Format datetime input into storage.
	 *
	 * @since 4.13.0
	 *
	 * @param string $value Datetime-local value.
	 *
	 * @return string
	 */
	private function normalize_datetime_local_input($value)
	{
		$value = is_string($value) ? trim($value) : '';

		if (empty($value)) {
			return '';
		}

		$timezone = wp_timezone();

		foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s'] as $format) {
			$date = date_create_immutable_from_format($format, $value, $timezone);

			if (false !== $date) {
				return $date->format(DATE_ATOM);
			}
		}

		return '';
	}

	/**
	 * Format stored datetime for input controls.
	 *
	 * @since 4.13.0
	 *
	 * @param string $value Stored datetime.
	 *
	 * @return string
	 */
	private function format_datetime_local_value($value)
	{
		if (empty($value) || ! is_string($value)) {
			return '';
		}

		try {
			$date = new DateTimeImmutable($value);

			return $date->setTimezone(wp_timezone())->format('Y-m-d\TH:i');
		} catch (Exception $exception) {
			return '';
		}
	}

	/**
	 * Format stored datetime for admin labels.
	 *
	 * @since 4.13.0
	 *
	 * @param string $value Stored datetime.
	 *
	 * @return string
	 */
	private function format_datetime_label($value)
	{
		if (empty($value)) {
			return '';
		}

		try {
			$date = new DateTimeImmutable($value);

			return $date->setTimezone(wp_timezone())->format('Y-m-d H:i');
		} catch (Exception $exception) {
			return '';
		}
	}
}

new JupiterX_Core_Control_Panel_Promotion_Banner();

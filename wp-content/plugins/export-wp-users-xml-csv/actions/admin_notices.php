<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pmue_admin_notices() {

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
}

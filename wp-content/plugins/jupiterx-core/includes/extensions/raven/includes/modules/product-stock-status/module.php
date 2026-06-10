<?php

namespace JupiterX_Core\Raven\Modules\Product_Stock_Status;

use JupiterX_Core\Raven\Base\Module_Base;
use JupiterX_Core\Raven\Plugin;

defined( 'ABSPATH' ) || die();

class Module extends Module_Base {

	public static function is_active() {
		return function_exists( 'WC' ) && Plugin::is_active( 'product-stock-status' );
	}

	public function get_widgets() {
		return [ 'product-stock-status' ];
	}
}

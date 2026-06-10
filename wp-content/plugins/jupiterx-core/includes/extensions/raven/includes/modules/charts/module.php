<?php

namespace JupiterX_Core\Raven\Modules\Charts;

defined( 'ABSPATH' ) || die();

use JupiterX_Core\Raven\Base\Module_Base;

class Module extends Module_Base {
	public function get_widgets() {
		return [
			'pie-chart',
			'line-chart',
			'bar-chart',
		];
	}
}

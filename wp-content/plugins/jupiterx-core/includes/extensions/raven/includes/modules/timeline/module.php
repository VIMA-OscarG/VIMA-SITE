<?php

namespace JupiterX_Core\Raven\Modules\Timeline;

defined( 'ABSPATH' ) || die();

use JupiterX_Core\Raven\Base\Module_Base;

class Module extends Module_Base {
	public function get_widgets() {
		return [
			'vertical-timeline',
			'horizontal-timeline',
		];
	}
}

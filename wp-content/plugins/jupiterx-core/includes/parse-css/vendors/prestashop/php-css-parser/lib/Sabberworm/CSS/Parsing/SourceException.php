<?php

namespace Sabberworm\CSS\Parsing;

defined( 'ABSPATH' ) || die();

class SourceException extends \Exception {
	private $iLineNo;
	public function __construct($sMessage, $iLineNo = 0) {
		$this->iLineNo = $iLineNo;
		if (!empty($iLineNo)) {
			$sMessage .= " [line no: $iLineNo]";
		}
		parent::__construct( esc_html( $sMessage ) );
	}

	public function getLineNo() {
		return $this->iLineNo;
	}
}
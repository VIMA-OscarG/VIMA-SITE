<?php

namespace JupiterX_Core\Raven\Core\Compatibility\Wpml\Modules;

defined( 'ABSPATH' ) || die();

class Form extends \WPML_Elementor_Module_With_Items {
	public function get_items_field() {
		return 'fields';
	}

	public function get_fields() {
		return [ 'label', 'placeholder', 'field_options', 'step_previous_button', 'step_next_button' ];
	}

	protected function get_title( $field ) {
		switch ( $field ) {
			case 'label':
				return esc_html__( 'Raven Form: Form field label', 'jupiterx-core' );

			case 'placeholder':
				return esc_html__( 'Raven Form: Form field placeholder', 'jupiterx-core' );

			case 'field_options':
				return esc_html__( 'Raven Form: Field options (select / radio / checkbox)', 'jupiterx-core' );

			case 'step_previous_button':
				return esc_html__( 'Raven Form: Step Previous Button', 'jupiterx-core' );

			case 'step_next_button':
				return esc_html__( 'Raven Form: Step Next Button', 'jupiterx-core' );

			default:
				return '';
		}
	}

	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'label':
				return 'LINE';

			case 'placeholder':
				return 'LINE';

			case 'field_options':
				return 'AREA';

			case 'step_previous_button':
				return 'LINE';

			case 'step_next_button':
				return 'LINE';

			default:
				return '';
		}
	}
}

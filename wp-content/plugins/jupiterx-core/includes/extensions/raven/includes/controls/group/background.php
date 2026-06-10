<?php
/**
 * Adds background control.
 *
 * @package JupiterX_Core\Raven
 * @since 1.0.0
 */

namespace JupiterX_Core\Raven\Controls\Group;

use Elementor\Group_Control_Base;

defined( 'ABSPATH' ) || die();

/**
 * Raven background control.
 *
 * A base control for creating background control. Displays input fields to define
 * the background color, background image, background gradiant or background video.
 *
 * Creating new control in the editor (inside `Widget_Base::_register_controls()`
 * method):
 *
 *    $this->add_group_control(
 *        'raven-background',
 *        [
 *            'name' => 'background',
 *            'types' => [ 'classic', 'gradient', 'video' ],
 *            'selector' => '{{WRAPPER}} .wrapper',
 *            'separator' => 'before',
 *        ]
 *    );
 *
 * @since 1.0.0
 *
 * @param string $name           The field name.
 * @param array  $types          Optional. Define spesific types to use. Available
 *                               types are `classic`, `gradient` and `video`. Default
 *                               is an empty array, including all the types.
 * @param array  $fields_options Optional. An array of arays contaning data that
 *                               overrides control settings. Default is an empty array.
 * @param string $separator      Optional. Set the position of the control separator.
 *                               Available values are 'default', 'before', 'after'
 *                               and 'none'. 'default' will position the separator
 *                               depending on the control type. 'before' / 'after'
 *                               will position the separator before/after the
 *                               control. 'none' will hide the separator. Default
 *                               is 'default'.
 */
class Background extends Group_Control_Base {

	/**
	 * Fields.
	 *
	 * Holds all the background control fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @var array Background control fields.
	 */
	protected static $fields;

	/**
	 * Background Types.
	 *
	 * Holds all the available background types.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $background_types;

	/**
	 * Retrieve type.
	 *
	 * Get background control type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string Control type.
	 */
	public static function get_type() {
		return 'raven-background';
	}

	/**
	 * Retrieve background types.
	 *
	 * Gat available background types.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return array Available background types.
	 */
	public static function get_background_types() {
		if ( null === self::$background_types ) {
			self::$background_types = self::init_background_types();
		}

		return self::$background_types;
	}

	/* TODO: rename to `default_background_types()` */
	/**
	 * Default background types.
	 *
	 * Retrieve background control initial types.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @return array Default background types.
	 */
	private static function init_background_types() {
		return [
			'classic' => [
				'title' => _x( 'Classic', 'Background Control', 'jupiterx-core' ),
				'icon' => 'fa fa-paint-brush',
			],
			'gradient' => [
				'title' => _x( 'Gradient', 'Background Control', 'jupiterx-core' ),
				'icon' => 'fa fa-barcode',
			],
			'video' => [
				'title' => _x( 'Background Video', 'Background Control', 'jupiterx-core' ),
				'icon' => 'fa fa-video-camera',
			],
			'blur' => [
				'title' => _x( 'Blur', 'Background Control', 'jupiterx-core' ),
				'icon' => 'fa fa-adjust',
			],
		];
	}

	/**
	 * Init fields.
	 *
	 * Initialize background control fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Control fields.
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function init_fields() {
		$fields = [];

		$fields['background'] = [
			'label' => _x( 'Background Type', 'Background Control', 'jupiterx-core' ),
			'type' => 'choose',
			'label_block' => false,
			// Use `template` so widgets re-render when the background type changes.
			// This is required for the blur type to take effect live in the editor
			// on widgets without a content_template (e.g. the Button), where the
			// `.jupiterx-blur-background` render-attribute class is added inside
			// the PHP render() method. Without a re-render the class would only
			// show up after a full editor reload.
			'render_type' => 'template',
		];

		$fields['color'] = [
			'label' => _x( 'Color', 'Background Control', 'jupiterx-core' ),
			'type' => 'color',
			'default' => '',
			'title' => _x( 'Background Color', 'Background Control', 'jupiterx-core' ),
			'selectors' => [
				'{{SELECTOR}}' => 'background-color: {{VALUE}}; background-image: none;',
			],
			'condition' => [
				'background' => [ 'classic', 'gradient' ],
			],
		];

		$fields['color_stop'] = [
			'label' => _x( 'Location', 'Background Control', 'jupiterx-core' ),
			'type' => 'slider',
			'size_units' => [ '%' ],
			'default' => [
				'unit' => '%',
				'size' => 0,
			],
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['color_b'] = [
			'label' => _x( 'Second Color', 'Background Control', 'jupiterx-core' ),
			'type' => 'color',
			'default' => '#f2295b',
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['color_b_stop'] = [
			'label' => _x( 'Location', 'Background Control', 'jupiterx-core' ),
			'type' => 'slider',
			'size_units' => [ '%' ],
			'default' => [
				'unit' => '%',
				'size' => 100,
			],
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['gradient_type'] = [
			'label' => _x( 'Type', 'Background Control', 'jupiterx-core' ),
			'type' => 'select',
			'options' => [
				'linear' => _x( 'Linear', 'Background Control', 'jupiterx-core' ),
				'radial' => _x( 'Radial', 'Background Control', 'jupiterx-core' ),
			],
			'default' => 'linear',
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['gradient_angle'] = [
			'label' => _x( 'Angle', 'Background Control', 'jupiterx-core' ),
			'type' => 'slider',
			'size_units' => [ 'deg' ],
			'default' => [
				'unit' => 'deg',
				'size' => 180,
			],
			'range' => [
				'deg' => [
					'step' => 10,
				],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-image: linear-gradient({{SIZE}}{{UNIT}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
			],
			'condition' => [
				'background' => [ 'gradient' ],
				'gradient_type' => 'linear',
			],
			'of_type' => 'gradient',
		];

		$fields['gradient_position'] = [
			'label' => _x( 'Position', 'Background Control', 'jupiterx-core' ),
			'type' => 'select',
			'options' => [
				'center center' => _x( 'Center Center', 'Background Control', 'jupiterx-core' ),
				'center left' => _x( 'Center Left', 'Background Control', 'jupiterx-core' ),
				'center right' => _x( 'Center Right', 'Background Control', 'jupiterx-core' ),
				'top center' => _x( 'Top Center', 'Background Control', 'jupiterx-core' ),
				'top left' => _x( 'Top Left', 'Background Control', 'jupiterx-core' ),
				'top right' => _x( 'Top Right', 'Background Control', 'jupiterx-core' ),
				'bottom center' => _x( 'Bottom Center', 'Background Control', 'jupiterx-core' ),
				'bottom left' => _x( 'Bottom Left', 'Background Control', 'jupiterx-core' ),
				'bottom right' => _x( 'Bottom Right', 'Background Control', 'jupiterx-core' ),
			],
			'default' => 'center center',
			'selectors' => [
				'{{SELECTOR}}' => 'background-image: radial-gradient(at {{VALUE}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
			],
			'condition' => [
				'background' => [ 'gradient' ],
				'gradient_type' => 'radial',
			],
			'of_type' => 'gradient',
		];

		$fields['image'] = [
			'label' => _x( 'Image', 'Background Control', 'jupiterx-core' ),
			'type' => 'media',
			'title' => _x( 'Background Image', 'Background Control', 'jupiterx-core' ),
			'selectors' => [
				'{{SELECTOR}}' => 'background-image: url("{{URL}}");',
			],
			'condition' => [
				'background' => [ 'classic' ],
			],
		];

		$fields['position'] = [
			'label' => _x( 'Position', 'Background Control', 'jupiterx-core' ),
			'type' => 'select',
			'default' => '',
			'options' => [
				'' => _x( 'Default', 'Background Control', 'jupiterx-core' ),
				'top left' => _x( 'Top Left', 'Background Control', 'jupiterx-core' ),
				'top center' => _x( 'Top Center', 'Background Control', 'jupiterx-core' ),
				'top right' => _x( 'Top Right', 'Background Control', 'jupiterx-core' ),
				'center left' => _x( 'Center Left', 'Background Control', 'jupiterx-core' ),
				'center center' => _x( 'Center Center', 'Background Control', 'jupiterx-core' ),
				'center right' => _x( 'Center Right', 'Background Control', 'jupiterx-core' ),
				'bottom left' => _x( 'Bottom Left', 'Background Control', 'jupiterx-core' ),
				'bottom center' => _x( 'Bottom Center', 'Background Control', 'jupiterx-core' ),
				'bottom right' => _x( 'Bottom Right', 'Background Control', 'jupiterx-core' ),
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-position: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['attachment'] = [
			'label' => _x( 'Attachment', 'Background Control', 'jupiterx-core' ),
			'type' => 'select',
			'default' => '',
			'options' => [
				'' => _x( 'Default', 'Background Control', 'jupiterx-core' ),
				'scroll' => _x( 'Scroll', 'Background Control', 'jupiterx-core' ),
				'fixed' => _x( 'Fixed', 'Background Control', 'jupiterx-core' ),
			],
			'selectors' => [
				'(tablet+){{SELECTOR}}' => 'background-attachment: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['repeat'] = [
			'label' => _x( 'Repeat', 'Background Control', 'jupiterx-core' ),
			'type' => 'select',
			'default' => '',
			'options' => [
				'' => _x( 'Default', 'Background Control', 'jupiterx-core' ),
				'no-repeat' => _x( 'No-repeat', 'Background Control', 'jupiterx-core' ),
				'repeat' => _x( 'Repeat', 'Background Control', 'jupiterx-core' ),
				'repeat-x' => _x( 'Repeat-x', 'Background Control', 'jupiterx-core' ),
				'repeat-y' => _x( 'Repeat-y', 'Background Control', 'jupiterx-core' ),
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-repeat: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['size'] = [
			'label' => _x( 'Size', 'Background Control', 'jupiterx-core' ),
			'type' => 'select',
			'default' => '',
			'options' => [
				'' => _x( 'Default', 'Background Control', 'jupiterx-core' ),
				'auto' => _x( 'Auto', 'Background Control', 'jupiterx-core' ),
				'cover' => _x( 'Cover', 'Background Control', 'jupiterx-core' ),
				'contain' => _x( 'Contain', 'Background Control', 'jupiterx-core' ),
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-size: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['video_link'] = [
			'label' => _x( 'Video Link', 'Background Control', 'jupiterx-core' ),
			'type' => 'text',
			'placeholder' => 'https://www.youtube.com/watch?v=9uOETcuFjbE',
			'description' => __( 'YouTube link or video file (mp4 is recommended).', 'jupiterx-core' ),
			'label_block' => true,
			'default' => '',
			'condition' => [
				'background' => [ 'video' ],
			],
			'of_type' => 'video',
		];

		$fields['video_fallback'] = [
			'label' => _x( 'Background Fallback', 'Background Control', 'jupiterx-core' ),
			'description' => __( 'This cover image will replace the background video on mobile and tablet devices.', 'jupiterx-core' ),
			'type' => 'media',
			'label_block' => true,
			'condition' => [
				'background' => [ 'video' ],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background: url("{{URL}}") 50% 50%; background-size: cover;',
			],
			'of_type' => 'video',
		];

		// NOTE on selectors: the `{{SELECTOR}}` passed to this group control by
		// some widgets (e.g. the Button widget) can be a comma-separated list
		// that already contains pseudo-element parts such as `:after`. Appending
		// `::before` / `::after` here would produce invalid selectors like
		// `.x:after::before`, which causes the whole rule to be dropped per the
		// CSS spec. To keep this group control safe for every caller, we only
		// emit the base rule on `{{SELECTOR}}` and rely on the static CSS
		// rules defined in `_blur-background.scss` (targeting the
		// `.jupiterx-blur-background` class) to render the pseudo-elements.
		$fields['blur_enabled'] = [
			'type' => 'hidden',
			'default' => 'yes',
			'condition' => [
				'background' => [ 'blur' ],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background: transparent !important; background-color: transparent !important; background-image: none !important; position: relative; isolation: isolate; --jupiterx-blur-amount: 14; --jupiterx-blur-tint-color: #FFFFFF; --jupiterx-blur-tint-opacity: 18; --jupiterx-blur-fallback-color: rgba(255,255,255,0.90);',
			],
			'of_type' => 'blur',
		];

		$fields['blur_amount'] = [
			'label' => _x( 'Blur Amount', 'Background Control', 'jupiterx-core' ),
			'type' => 'slider',
			'size_units' => [ 'px' ],
			'default' => [
				'unit' => 'px',
				'size' => 14,
			],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 30,
				],
			],
			'selectors' => [
				'{{SELECTOR}}' => '--jupiterx-blur-amount: {{SIZE}};',
			],
			'condition' => [
				'background' => [ 'blur' ],
			],
			'of_type' => 'blur',
		];

		$fields['blur_tint_color'] = [
			'label' => _x( 'Tint Color', 'Background Control', 'jupiterx-core' ),
			'type' => 'color',
			'default' => '#FFFFFF',
			'selectors' => [
				'{{SELECTOR}}' => '--jupiterx-blur-tint-color: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'blur' ],
			],
			'of_type' => 'blur',
		];

		$fields['blur_tint_opacity'] = [
			'label' => _x( 'Tint Opacity', 'Background Control', 'jupiterx-core' ),
			'type' => 'slider',
			'size_units' => [ '%' ],
			'default' => [
				'unit' => '%',
				'size' => 18,
			],
			'range' => [
				'%' => [
					'min' => 0,
					'max' => 60,
				],
			],
			'selectors' => [
				'{{SELECTOR}}' => '--jupiterx-blur-tint-opacity: {{SIZE}};',
			],
			'condition' => [
				'background' => [ 'blur' ],
			],
			'of_type' => 'blur',
		];

		$fields['blur_fallback_color'] = [
			'label' => _x( 'Fallback Color', 'Background Control', 'jupiterx-core' ),
			'type' => 'color',
			'default' => 'rgba(255,255,255,0.90)',
			'selectors' => [
				'{{SELECTOR}}' => '--jupiterx-blur-fallback-color: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'blur' ],
			],
			'of_type' => 'blur',
		];

		// Mobile disable is handled by the static `.jupiterx-blur-background--mobile-disabled`
		// rule in `_blur-background.scss` (the class is added by
		// `Blur_Background_Module::add_render_attributes()`). We intentionally
		// avoid emitting `(mobile){{SELECTOR}}::before` here for the same
		// selector-stacking reason documented above on `blur_enabled`.
		$fields['blur_disable_on_mobile'] = [
			'label' => _x( 'Disable On Mobile', 'Background Control', 'jupiterx-core' ),
			'type' => 'switcher',
			'label_on' => __( 'Yes', 'jupiterx-core' ),
			'label_off' => __( 'No', 'jupiterx-core' ),
			'return_value' => 'yes',
			'default' => '',
			'condition' => [
				'background' => [ 'blur' ],
			],
			'of_type' => 'blur',
		];

		return $fields;
	}

	/**
	 * Retrieve child default args.
	 *
	 * Get the default arguments for all the child controls for a specific group
	 * control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Default arguments for all the child controls.
	 */
	protected function get_child_default_args() {
		return [
			'types' => [ 'classic', 'gradient' ],
		];
	}

	/**
	 * Filter fields.
	 *
	 * Filter which controls to display, using `include`, `exclude`, `condition`
	 * and `of_type` arguments.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control fields.
	 */
	protected function filter_fields() {
		$fields = parent::filter_fields();

		$args = $this->get_args();

		foreach ( $fields as &$field ) {
			if ( isset( $field['of_type'] ) && ! in_array( $field['of_type'], $args['types'], true ) ) {
				unset( $field );
			}
		}

		return $fields;
	}

	/**
	 * Prepare fields.
	 *
	 * Process background control fields before adding them to `add_control()`.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $fields Background control fields.
	 *
	 * @return array Processed fields.
	 */
	protected function prepare_fields( $fields ) {
		$args = $this->get_args();

		$background_types = self::get_background_types();

		$choose_types = [];

		foreach ( $args['types'] as $type ) {
			if ( isset( $background_types[ $type ] ) ) {
				$choose_types[ $type ] = $background_types[ $type ];
			}
		}

		$fields['background']['options'] = $choose_types;

		return parent::prepare_fields( $fields );
	}

	/**
	 * Retrieve default options.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function get_default_options() {
		return [
			'popover' => false,
		];
	}
}

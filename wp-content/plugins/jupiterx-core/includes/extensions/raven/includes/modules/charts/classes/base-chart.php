<?php

namespace JupiterX_Core\Raven\Modules\Charts\Classes;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils as ElementorUtils;
use JupiterX_Core\Raven\Base\Base_Widget;

abstract class Base_Chart extends Base_Widget {
	protected function get_chart_type() {
		return 'bar';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), [ 'chart', 'graph', 'data', 'stats' ] );
	}

	protected function get_title_tag_options() {
		return [
			'h1' => 'H1',
			'h2' => 'H2',
			'h3' => 'H3',
			'h4' => 'H4',
			'h5' => 'H5',
			'h6' => 'H6',
			'div' => 'div',
			'span' => 'span',
			'p' => 'p',
		];
	}

	protected function register_title_controls( $default_title ) {
		$this->add_control(
			'chart_title',
			[
				'label' => esc_html__( 'Chart Title', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'default' => $default_title,
				'dynamic' => [
					'active' => true,
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'chart_title_tag',
			[
				'label' => esc_html__( 'Title HTML Tag', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_title_tag_options(),
				'default' => 'h5',
				'condition' => [
					'chart_title!' => '',
				],
			]
		);

		$this->add_control(
			'chart_title_position',
			[
				'label' => esc_html__( 'Title Position', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'before' => esc_html__( 'Before Chart', 'jupiterx-core' ),
					'after' => esc_html__( 'After Chart', 'jupiterx-core' ),
				],
				'default' => 'after',
				'condition' => [
					'chart_title!' => '',
				],
			]
		);
	}

	protected function register_common_settings_controls() {
		$this->start_controls_section(
			'section_settings',
			[
				'label' => esc_html__( 'Settings', 'jupiterx-core' ),
			]
		);

		$this->add_responsive_control(
			'chart_height',
			[
				'label' => esc_html__( 'Chart Height', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1200,
					],
				],
				'default' => [
					'size' => 400,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-chart-container' => 'height: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'chart_animation_heading',
			[
				'label' => esc_html__( 'Animation', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'chart_animation_duration',
			[
				'label' => esc_html__( 'Duration', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 5000,
						'step' => 100,
					],
				],
				'default' => [
					'size' => 1000,
				],
			]
		);

		$this->add_control(
			'chart_animation_easing',
			[
				'label' => esc_html__( 'Easing', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'easeOutQuart',
				'options' => [
					'linear' => esc_html__( 'Linear', 'jupiterx-core' ),
					'easeInQuad' => esc_html__( 'Ease In Quad', 'jupiterx-core' ),
					'easeOutQuad' => esc_html__( 'Ease Out Quad', 'jupiterx-core' ),
					'easeInOutQuad' => esc_html__( 'Ease In Out Quad', 'jupiterx-core' ),
					'easeOutQuart' => esc_html__( 'Ease Out Quart', 'jupiterx-core' ),
					'easeInOutQuart' => esc_html__( 'Ease In Out Quart', 'jupiterx-core' ),
					'easeOutBounce' => esc_html__( 'Ease Out Bounce', 'jupiterx-core' ),
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_axis_controls() {
		$this->add_control(
			'axis_range',
			[
				'label' => esc_html__( 'Max Scale Axis Range', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 10,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'axis_range_min',
			[
				'label' => esc_html__( 'Min Scale Axis Range', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'step_size',
			[
				'label' => esc_html__( 'Step Size', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
				'step' => 1,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'axis_thousand_separator',
			[
				'label' => esc_html__( 'Axis Value Thousand Separator', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'yes',
			]
		);
	}

	protected function register_legend_style_controls() {
		$this->start_controls_section(
			'section_legend_style',
			[
				'label' => esc_html__( 'Legend', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'chart_legend_display',
			[
				'label' => esc_html__( 'Display', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'chart_legend_position',
			[
				'label' => esc_html__( 'Position', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => [
					'top' => esc_html__( 'Top', 'jupiterx-core' ),
					'left' => esc_html__( 'Left', 'jupiterx-core' ),
					'bottom' => esc_html__( 'Bottom', 'jupiterx-core' ),
					'right' => esc_html__( 'Right', 'jupiterx-core' ),
				],
				'condition' => [
					'chart_legend_display' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_legend_alignment',
			[
				'label' => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => [
					'start' => [
						'title' => esc_html__( 'Start', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-center',
					],
					'end' => [
						'title' => esc_html__( 'End', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'chart_legend_display' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_legend_reverse',
			[
				'label' => esc_html__( 'Reverse', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition' => [
					'chart_legend_display' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_legend_box_width',
			[
				'label' => esc_html__( 'Color Box Width', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 80,
					],
				],
				'condition' => [
					'chart_legend_display' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'chart_legend_typography',
				'selector' => '{{WRAPPER}} .raven-chart-container',
				'exclude' => [ 'text_decoration', 'line_height', 'letter_spacing', 'word_spacing' ],
				'condition' => [
					'chart_legend_display' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_legend_font_color',
			[
				'label' => esc_html__( 'Font Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'chart_legend_display' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_tooltip_style_controls( $external_tooltip = false ) {
		$this->start_controls_section(
			'section_tooltip_style',
			[
				'label' => esc_html__( 'Tooltip', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'chart_tooltip_enabled',
			[
				'label' => esc_html__( 'Display', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'chart_tooltip_prefix',
			[
				'label' => esc_html__( 'Value Prefix', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'chart_tooltip_enabled' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_tooltip_suffix',
			[
				'label' => esc_html__( 'Value Suffix', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'chart_tooltip_enabled' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_tooltip_separator',
			[
				'label' => esc_html__( 'Thousands Separator', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'None', 'jupiterx-core' ),
					',' => esc_html__( 'Comma', 'jupiterx-core' ),
					'.' => esc_html__( 'Dot', 'jupiterx-core' ),
					' ' => esc_html__( 'Space', 'jupiterx-core' ),
				],
				'condition' => [
					'chart_tooltip_enabled' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_tooltip_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => $external_tooltip ? [
					'.raven-chart-tooltip-{{ID}} .raven-chart-tooltip' => 'background-color: {{VALUE}};',
				] : [],
				'condition' => [
					'chart_tooltip_enabled' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'chart_tooltip_typography',
				'selector' => $external_tooltip ? '.raven-chart-tooltip-{{ID}} .raven-chart-tooltip' : '{{WRAPPER}} .raven-chart-container',
				'exclude' => [ 'text_decoration', 'letter_spacing', 'word_spacing' ],
				'condition' => [
					'chart_tooltip_enabled' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_tooltip_font_color',
			[
				'label' => esc_html__( 'Font Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => $external_tooltip ? [
					'.raven-chart-tooltip-{{ID}} .raven-chart-tooltip' => 'color: {{VALUE}};',
				] : [],
				'condition' => [
					'chart_tooltip_enabled' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_axes_style_controls() {
		$this->start_controls_section(
			'section_axes_style',
			[
				'label' => esc_html__( 'Axes & Grid', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'chart_labels_display',
			[
				'label' => esc_html__( 'Axis Labels', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'chart_labels_typography',
				'selector' => '{{WRAPPER}} .raven-chart-container',
				'exclude' => [ 'text_decoration', 'line_height', 'letter_spacing', 'word_spacing' ],
				'condition' => [
					'chart_labels_display' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_labels_font_color',
			[
				'label' => esc_html__( 'Label Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'chart_labels_display' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_grid_display',
			[
				'label' => esc_html__( 'Grid Lines', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'chart_grid_color',
			[
				'label' => esc_html__( 'Grid Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.05)',
				'condition' => [
					'chart_grid_display' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_title_style_controls() {
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'chart_title_align',
			[
				'label' => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'jupiterx-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'jupiterx-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-chart-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'chart_title_typography',
				'selector' => '{{WRAPPER}} .raven-chart-title',
			]
		);

		$this->add_control(
			'chart_title_color',
			[
				'label' => esc_html__( 'Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-chart-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'chart_title_spacing',
			[
				'label' => esc_html__( 'Spacing', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-chart-title-position-before' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-chart-title-position-after' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_chart_font_style_string( $typography_key ) {
		$settings = $this->get_settings_for_display();
		$style    = [];

		foreach ( [ 'font_style', 'font_weight' ] as $suffix ) {
			$key = $typography_key . '_' . $suffix;

			if ( ! empty( $settings[ $key ] ) ) {
				$style[] = $settings[ $key ];
			}
		}

		return implode( ' ', array_unique( $style ) );
	}

	protected function get_chart_typography_options( $typography_key, $color_key = '' ) {
		$settings = $this->get_settings_for_display();
		$options  = [];

		if ( ! empty( $settings[ $typography_key . '_font_family' ] ) ) {
			$options['fontFamily'] = $settings[ $typography_key . '_font_family' ];
		}

		if ( ! empty( $settings[ $typography_key . '_font_size']['size'] ) ) {
			$options['fontSize'] = (int) $settings[ $typography_key . '_font_size']['size'];
		}

		$font_style = $this->get_chart_font_style_string( $typography_key );

		if ( ! empty( $font_style ) ) {
			$options['fontStyle'] = $font_style;
		}

		if ( ! empty( $color_key ) && ! empty( $settings[ $color_key ] ) ) {
			$options['fontColor'] = $settings[ $color_key ];
		}

		return $options;
	}

	protected function get_common_options() {
		$settings = $this->get_settings_for_display();
		$options  = [
			'maintainAspectRatio' => false,
			'responsive' => true,
			'animation' => [
				'duration' => ! empty( $settings['chart_animation_duration']['size'] ) ? (int) $settings['chart_animation_duration']['size'] : 1000,
				'easing' => ! empty( $settings['chart_animation_easing'] ) ? $settings['chart_animation_easing'] : 'easeOutQuart',
			],
			'legend' => [
				'display' => 'yes' === $settings['chart_legend_display'],
				'position' => ! empty( $settings['chart_legend_position'] ) ? $settings['chart_legend_position'] : 'top',
				'reverse' => 'yes' === $settings['chart_legend_reverse'],
				'align' => ! empty( $settings['chart_legend_alignment'] ) ? $settings['chart_legend_alignment'] : 'center',
			],
			'tooltips' => [
				'enabled' => 'yes' === $settings['chart_tooltip_enabled'],
			],
		];

		if ( $options['legend']['display'] ) {
			$legend_labels = $this->get_chart_typography_options( 'chart_legend_typography', 'chart_legend_font_color' );

			if ( ! empty( $settings['chart_legend_box_width']['size'] ) ) {
				$legend_labels['boxWidth'] = (int) $settings['chart_legend_box_width']['size'];
			}

			if ( ! empty( $legend_labels ) ) {
				$options['legend']['labels'] = $legend_labels;
			}
		}

		if ( $options['tooltips']['enabled'] ) {
			if ( ! empty( $settings['chart_tooltip_bg_color'] ) ) {
				$options['tooltips']['backgroundColor'] = $settings['chart_tooltip_bg_color'];
			}

			$tooltip_typography = $this->get_chart_typography_options( 'chart_tooltip_typography', 'chart_tooltip_font_color' );

			foreach ( $tooltip_typography as $key => $value ) {
				$options['tooltips'][ 'body' . ucfirst( $key ) ] = $value;
			}
		}

		return $options;
	}

	protected function get_axes_options( $horizontal = false ) {
		$settings       = $this->get_settings_for_display();
		$labels_display = 'yes' === $settings['chart_labels_display'];
		$grid_display   = 'yes' === $settings['chart_grid_display'];
		$ticks          = [
			'display' => $labels_display,
			'beginAtZero' => empty( $settings['axis_range_min'] ),
			'stepSize' => ! empty( $settings['step_size'] ) ? (float) $settings['step_size'] : 1,
		];

		if ( '' !== $settings['axis_range'] ) {
			$ticks['max'] = (float) $settings['axis_range'];
		}

		if ( '' !== $settings['axis_range_min'] ) {
			$ticks['min'] = (float) $settings['axis_range_min'];
		}

		if ( $labels_display ) {
			$ticks = array_merge( $ticks, $this->get_chart_typography_options( 'chart_labels_typography', 'chart_labels_font_color' ) );
		}

		$value_axis = [
			'ticks' => $ticks,
			'gridLines' => [
				'display' => $grid_display,
				'drawBorder' => false,
				'color' => ! empty( $settings['chart_grid_color'] ) ? $settings['chart_grid_color'] : 'rgba(0,0,0,0.05)',
				'zeroLineColor' => ! empty( $settings['chart_grid_color'] ) ? $settings['chart_grid_color'] : 'rgba(0,0,0,0.05)',
			],
		];

		$category_axis = [
			'ticks' => [
				'display' => $labels_display,
			],
			'gridLines' => [
				'display' => $grid_display,
				'drawBorder' => false,
				'color' => ! empty( $settings['chart_grid_color'] ) ? $settings['chart_grid_color'] : 'rgba(0,0,0,0.05)',
			],
		];

		if ( $labels_display ) {
			$category_axis['ticks'] = array_merge( $category_axis['ticks'], $this->get_chart_typography_options( 'chart_labels_typography', 'chart_labels_font_color' ) );
		}

		return [
			'xAxes' => [ $horizontal ? $value_axis : $category_axis ],
			'yAxes' => [ $horizontal ? $category_axis : $value_axis ],
		];
	}

	protected function parse_csv_list( $value, $numeric = false ) {
		$items = array_map( 'trim', explode( ',', (string) $value ) );
		$items = array_filter( $items, static function( $item ) {
			return '' !== $item;
		} );

		if ( ! $numeric ) {
			return array_values( $items );
		}

		return array_map( 'floatval', array_values( $items ) );
	}

	protected function get_render_settings( $data, $options, $type = '' ) {
		$settings = $this->get_settings_for_display();

		return [
			'type' => ! empty( $type ) ? $type : $this->get_chart_type(),
			'data' => $data,
			'options' => $options,
			'meta' => [
				'tooltipPrefix' => isset( $settings['chart_tooltip_prefix'] ) ? $settings['chart_tooltip_prefix'] : '',
				'tooltipSuffix' => isset( $settings['chart_tooltip_suffix'] ) ? $settings['chart_tooltip_suffix'] : '',
				'tooltipSeparator' => isset( $settings['chart_tooltip_separator'] ) ? $settings['chart_tooltip_separator'] : '',
				'axisSeparator' => isset( $settings['axis_thousand_separator'] ) && 'yes' === $settings['axis_thousand_separator'],
				'labelsLength' => ! empty( $settings['labels_length'] ) ? (int) $settings['labels_length'] : 50,
				'comparison' => isset( $settings['chart_comparison_enabled'] ) && 'yes' === $settings['chart_comparison_enabled'],
				'comparisonLabelType' => isset( $settings['chart_comparison_tooltip_label_type'] ) ? $settings['chart_comparison_tooltip_label_type'] : 'labels',
				'previousLabel' => isset( $settings['chart_comparison_tooltip_previous_label'] ) ? $settings['chart_comparison_tooltip_previous_label'] : esc_html__( 'Previous', 'jupiterx-core' ),
				'currentLabel' => isset( $settings['chart_comparison_tooltip_current_label'] ) ? $settings['chart_comparison_tooltip_current_label'] : esc_html__( 'Current', 'jupiterx-core' ),
			],
		];
	}

	protected function render_chart( array $data, array $options, $type = '' ) {
		$settings = $this->get_settings_for_display();
		$type     = ! empty( $type ) ? $type : $this->get_chart_type();

		$this->add_render_attribute(
			'chart-container',
			[
				'class' => [
					'raven-chart-container',
					'raven-' . $this->get_chart_type() . '-chart-container',
				],
				'data-raven-chart' => wp_json_encode( $this->get_render_settings( $data, $options, $type ) ),
			]
		);

		$this->add_render_attribute(
			'chart-canvas',
			[
				'class' => 'raven-chart-canvas',
				'role' => 'img',
			]
		);

		if ( ! empty( $settings['chart_title'] ) ) {
			$this->add_render_attribute( 'chart-canvas', 'aria-label', $settings['chart_title'] );
		}

		if ( 'before' === $settings['chart_title_position'] ) {
			$this->render_title( 'before' );
		}

		?>
		<div <?php $this->print_render_attribute_string( 'chart-container' ); ?>>
			<canvas <?php $this->print_render_attribute_string( 'chart-canvas' ); ?>></canvas>
		</div>
		<?php

		if ( 'after' === $settings['chart_title_position'] ) {
			$this->render_title( 'after' );
		}
	}

	protected function render_title( $position ) {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['chart_title'] ) ) {
			return;
		}

		$title_tag = ElementorUtils::validate_html_tag( $settings['chart_title_tag'] );

		$this->add_render_attribute(
			'chart-title-' . $position,
			[
				'class' => [
					'raven-chart-title',
					'raven-chart-title-position-' . $position,
				],
			]
		);

		printf(
			'<%1$s %2$s>%3$s</%1$s>',
			esc_attr( $title_tag ),
			$this->get_render_attribute_string( 'chart-title-' . $position ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $settings['chart_title'] )
		);
	}
}

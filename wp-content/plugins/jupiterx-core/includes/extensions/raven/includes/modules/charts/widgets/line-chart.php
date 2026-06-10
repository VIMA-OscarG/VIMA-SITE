<?php

namespace JupiterX_Core\Raven\Modules\Charts\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Repeater;
use JupiterX_Core\Raven\Modules\Charts\Classes\Base_Chart;

class Line_Chart extends Base_Chart {
	public function get_name() {
		return 'raven-line-chart';
	}

	public function get_title() {
		return esc_html__( 'Line Chart', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-line-chart';
	}

	protected function get_chart_type() {
		return 'line';
	}

	protected function register_controls() {
		$this->register_data_controls();
		$this->register_common_settings_controls();
		$this->register_line_style_controls();
		$this->register_title_style_controls();
		$this->register_legend_style_controls();
		$this->register_tooltip_style_controls( true );
		$this->register_axes_style_controls();
	}

	protected function register_data_controls() {
		$this->start_controls_section(
			'section_chart_data',
			[
				'label' => esc_html__( 'Chart Data', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'labels',
			[
				'label' => esc_html__( 'Labels', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'March, April, May', 'jupiterx-core' ),
				'description' => esc_html__( 'Enter labels separated by commas.', 'jupiterx-core' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->register_axis_controls();

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'line_dataset_tabs' );

		$repeater->start_controls_tab(
			'line_dataset_content',
			[
				'label' => esc_html__( 'Content', 'jupiterx-core' ),
			]
		);

		$repeater->add_control(
			'label',
			[
				'label' => esc_html__( 'Label', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'data',
			[
				'label' => esc_html__( 'Data', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter numeric values separated by commas.', 'jupiterx-core' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'line_dataset_style',
			[
				'label' => esc_html__( 'Style', 'jupiterx-core' ),
			]
		);

		$repeater->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
			]
		);

		$repeater->add_control(
			'border_color',
			[
				'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
			]
		);

		$repeater->add_control(
			'point_bg_color',
			[
				'label' => esc_html__( 'Point Background Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
			]
		);

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$this->add_control(
			'chart_data',
			[
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'label' => esc_html__( 'Google', 'jupiterx-core' ),
						'data' => '2, 4, 8',
						'bg_color' => 'rgba(221,75,57,0.4)',
						'border_color' => '#dd4b39',
						'point_bg_color' => '#dd4b39',
					],
					[
						'label' => esc_html__( 'Facebook', 'jupiterx-core' ),
						'data' => '1, 5, 3',
						'bg_color' => 'rgba(59,89,152,0.4)',
						'border_color' => '#3b5998',
						'point_bg_color' => '#3b5998',
					],
					[
						'label' => esc_html__( 'Twitter', 'jupiterx-core' ),
						'data' => '5, 9, 5',
						'bg_color' => 'rgba(85,172,238,0.4)',
						'border_color' => '#55acee',
						'point_bg_color' => '#55acee',
					],
				],
				'title_field' => '{{{ label }}}',
			]
		);

		$this->register_title_controls( esc_html__( 'Line Chart', 'jupiterx-core' ) );

		$this->end_controls_section();

		$this->register_comparison_controls();
	}

	protected function register_comparison_controls() {
		$this->start_controls_section(
			'section_comparison',
			[
				'label' => esc_html__( 'Comparison Tooltip', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'chart_comparison_enabled',
			[
				'label' => esc_html__( 'Enable Comparison', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'chart_comparison_tooltip_label_type',
			[
				'label' => esc_html__( 'Comparison Labels', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'labels',
				'options' => [
					'labels' => esc_html__( 'Use Chart Labels', 'jupiterx-core' ),
					'custom' => esc_html__( 'Custom Labels', 'jupiterx-core' ),
				],
				'condition' => [
					'chart_comparison_enabled' => 'yes',
				],
			]
		);

		$this->add_control(
			'chart_comparison_tooltip_previous_label',
			[
				'label' => esc_html__( 'Previous Label', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Previous', 'jupiterx-core' ),
				'condition' => [
					'chart_comparison_enabled' => 'yes',
					'chart_comparison_tooltip_label_type' => 'custom',
				],
			]
		);

		$this->add_control(
			'chart_comparison_tooltip_current_label',
			[
				'label' => esc_html__( 'Current Label', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Current', 'jupiterx-core' ),
				'condition' => [
					'chart_comparison_enabled' => 'yes',
					'chart_comparison_tooltip_label_type' => 'custom',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_line_style_controls() {
		$this->start_controls_section(
			'section_chart_style',
			[
				'label' => esc_html__( 'Chart', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'chart_border_width',
			[
				'label' => esc_html__( 'Line & Point Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'default' => [
					'size' => 3,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_chart_data() {
		$settings = $this->get_settings_for_display();
		$datasets = [];
		$size     = isset( $settings['chart_border_width']['size'] ) ? (int) $settings['chart_border_width']['size'] : 3;

		foreach ( $settings['chart_data'] as $item ) {
			$point_color = ! empty( $item['point_bg_color'] ) ? $item['point_bg_color'] : '#7a7a7a';

			$datasets[] = [
				'label' => ! empty( $item['label'] ) ? $item['label'] : '',
				'data' => ! empty( $item['data'] ) ? $this->parse_csv_list( $item['data'], true ) : [],
				'backgroundColor' => ! empty( $item['bg_color'] ) ? $item['bg_color'] : 'rgba(206,206,206,0.4)',
				'borderColor' => ! empty( $item['border_color'] ) ? $item['border_color'] : '#7a7a7a',
				'borderWidth' => $size,
				'pointBorderColor' => $point_color,
				'pointBackgroundColor' => $point_color,
				'pointRadius' => $size,
				'pointHoverRadius' => $size + 1,
				'pointBorderWidth' => 0,
			];
		}

		return [
			'labels' => $this->parse_csv_list( $settings['labels'] ),
			'datasets' => $datasets,
		];
	}

	protected function get_chart_options() {
		$options = $this->get_common_options();

		$options['scales'] = $this->get_axes_options();
		$options['hover'] = [
			'mode' => 'nearest',
			'intersect' => true,
		];

		return $options;
	}

	protected function render() {
		$this->render_chart( $this->get_chart_data(), $this->get_chart_options() );
	}
}

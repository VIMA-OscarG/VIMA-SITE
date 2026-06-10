<?php

namespace JupiterX_Core\Raven\Modules\Charts\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Repeater;
use JupiterX_Core\Raven\Modules\Charts\Classes\Base_Chart;

class Pie_Chart extends Base_Chart {
	public function get_name() {
		return 'raven-pie-chart';
	}

	public function get_title() {
		return esc_html__( 'Pie Chart', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-pie-chart';
	}

	protected function get_chart_type() {
		return 'pie';
	}

	protected function register_controls() {
		$this->register_data_controls();
		$this->register_common_settings_controls();
		$this->register_chart_style_controls();
		$this->register_title_style_controls();
		$this->register_legend_style_controls();
		$this->register_tooltip_style_controls();
	}

	protected function register_data_controls() {
		$this->start_controls_section(
			'section_chart_data',
			[
				'label' => esc_html__( 'Chart Data', 'jupiterx-core' ),
			]
		);

		$repeater = new Repeater();

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
			'value',
			[
				'label' => esc_html__( 'Value', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'color',
			[
				'label' => esc_html__( 'Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
			]
		);

		$this->add_control(
			'chart_data',
			[
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'label' => esc_html__( 'Google', 'jupiterx-core' ),
						'value' => 50,
						'color' => '#dd4b39',
					],
					[
						'label' => esc_html__( 'Facebook', 'jupiterx-core' ),
						'value' => 50,
						'color' => '#3b5998',
					],
					[
						'label' => esc_html__( 'Twitter', 'jupiterx-core' ),
						'value' => 50,
						'color' => '#55acee',
					],
				],
				'title_field' => '{{{ label }}}',
			]
		);

		$this->register_title_controls( esc_html__( 'Pie Chart', 'jupiterx-core' ) );

		$this->end_controls_section();
	}

	protected function register_chart_style_controls() {
		$this->start_controls_section(
			'section_chart_style',
			[
				'label' => esc_html__( 'Chart', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'chart_cutout_percentage',
			[
				'label' => esc_html__( 'Cutout Percentage', 'jupiterx-core' ),
				'description' => esc_html__( 'Use a value above 0 to turn the pie chart into a doughnut chart.', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 95,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 0,
				],
			]
		);

		$this->add_control(
			'chart_animate_scale',
			[
				'label' => esc_html__( 'Animate Scale', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'chart_border_width',
			[
				'label' => esc_html__( 'Border Width', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'default' => [
					'size' => 1,
				],
			]
		);

		$this->add_control(
			'chart_border_color',
			[
				'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->end_controls_section();
	}

	protected function get_chart_data() {
		$settings = $this->get_settings_for_display();
		$data     = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'borderWidth' => isset( $settings['chart_border_width']['size'] ) ? (int) $settings['chart_border_width']['size'] : 1,
					'borderColor' => ! empty( $settings['chart_border_color'] ) ? $settings['chart_border_color'] : '#ffffff',
					'hoverBorderColor' => ! empty( $settings['chart_border_color'] ) ? $settings['chart_border_color'] : '#ffffff',
				],
			],
		];

		foreach ( $settings['chart_data'] as $item ) {
			$data['labels'][]                         = ! empty( $item['label'] ) ? $item['label'] : '';
			$data['datasets'][0]['data'][]            = isset( $item['value'] ) ? (float) $item['value'] : 0;
			$data['datasets'][0]['backgroundColor'][] = ! empty( $item['color'] ) ? $item['color'] : '#d8d8d8';
		}

		return $data;
	}

	protected function get_chart_options() {
		$settings = $this->get_settings_for_display();
		$options  = $this->get_common_options();

		$options['animation']['animateScale'] = 'yes' === $settings['chart_animate_scale'];

		if ( ! empty( $settings['chart_cutout_percentage']['size'] ) ) {
			$options['cutoutPercentage'] = (int) $settings['chart_cutout_percentage']['size'];
		}

		return $options;
	}

	protected function render() {
		$this->render_chart( $this->get_chart_data(), $this->get_chart_options() );
	}
}

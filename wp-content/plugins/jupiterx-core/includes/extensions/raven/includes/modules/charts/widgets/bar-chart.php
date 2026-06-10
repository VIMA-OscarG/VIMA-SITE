<?php

namespace JupiterX_Core\Raven\Modules\Charts\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Repeater;
use JupiterX_Core\Raven\Modules\Charts\Classes\Base_Chart;

class Bar_Chart extends Base_Chart {
	public function get_name() {
		return 'raven-bar-chart';
	}

	public function get_title() {
		return esc_html__( 'Bar Chart', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-bar-chart';
	}

	protected function get_chart_type() {
		return 'bar';
	}

	protected function register_controls() {
		$this->register_data_controls();
		$this->register_common_settings_controls();
		$this->register_chart_style_controls();
		$this->register_title_style_controls();
		$this->register_legend_style_controls();
		$this->register_tooltip_style_controls();
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
			'bar_type',
			[
				'label' => esc_html__( 'Bar Type', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'bar',
				'options' => [
					'bar' => esc_html__( 'Vertical Bar', 'jupiterx-core' ),
					'horizontalBar' => esc_html__( 'Horizontal Bar', 'jupiterx-core' ),
				],
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

		$this->add_control(
			'labels_length',
			[
				'label' => esc_html__( 'Label Wrap Length', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 50,
				'min' => 5,
				'step' => 1,
			]
		);

		$this->register_axis_controls();

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'bar_dataset_tabs' );

		$repeater->start_controls_tab(
			'bar_dataset_content',
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
			'bar_dataset_style',
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
			'bg_hover_color',
			[
				'label' => esc_html__( 'Background Hover Color', 'jupiterx-core' ),
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
			'border_hover_color',
			[
				'label' => esc_html__( 'Border Hover Color', 'jupiterx-core' ),
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
						'bg_color' => '#dd4b39',
						'bg_hover_color' => '#c74132',
						'border_color' => '#dd4b39',
						'border_hover_color' => '#c74132',
					],
					[
						'label' => esc_html__( 'Facebook', 'jupiterx-core' ),
						'data' => '1, 5, 3',
						'bg_color' => '#3b5998',
						'bg_hover_color' => '#2f477a',
						'border_color' => '#3b5998',
						'border_hover_color' => '#2f477a',
					],
					[
						'label' => esc_html__( 'Twitter', 'jupiterx-core' ),
						'data' => '5, 9, 5',
						'bg_color' => '#55acee',
						'bg_hover_color' => '#4499d5',
						'border_color' => '#55acee',
						'border_hover_color' => '#4499d5',
					],
				],
				'title_field' => '{{{ label }}}',
			]
		);

		$this->register_title_controls( esc_html__( 'Bar Chart', 'jupiterx-core' ) );

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
			'chart_border_width',
			[
				'label' => esc_html__( 'Dataset Border Width', 'jupiterx-core' ),
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

		$this->end_controls_section();
	}

	protected function get_chart_data() {
		$settings = $this->get_settings_for_display();
		$datasets = [];

		foreach ( $settings['chart_data'] as $item ) {
			$datasets[] = [
				'label' => ! empty( $item['label'] ) ? $item['label'] : '',
				'data' => ! empty( $item['data'] ) ? $this->parse_csv_list( $item['data'], true ) : [],
				'backgroundColor' => ! empty( $item['bg_color'] ) ? $item['bg_color'] : '#cecece',
				'hoverBackgroundColor' => ! empty( $item['bg_hover_color'] ) ? $item['bg_hover_color'] : '#7a7a7a',
				'borderColor' => ! empty( $item['border_color'] ) ? $item['border_color'] : '#7a7a7a',
				'hoverBorderColor' => ! empty( $item['border_hover_color'] ) ? $item['border_hover_color'] : '#7a7a7a',
				'borderWidth' => isset( $settings['chart_border_width']['size'] ) ? (int) $settings['chart_border_width']['size'] : 1,
			];
		}

		return [
			'labels' => $this->parse_csv_list( $settings['labels'] ),
			'datasets' => $datasets,
		];
	}

	protected function get_chart_options() {
		$settings = $this->get_settings_for_display();
		$options  = $this->get_common_options();

		$options['scales'] = $this->get_axes_options( 'horizontalBar' === $settings['bar_type'] );

		return $options;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->render_chart( $this->get_chart_data(), $this->get_chart_options(), $settings['bar_type'] );
	}
}

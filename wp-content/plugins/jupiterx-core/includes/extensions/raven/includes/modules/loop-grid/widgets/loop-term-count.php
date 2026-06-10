<?php
/**
 * Current loop term count widget.
 *
 * @package JupiterX_Core\Raven
 * @since NEXT
 */

namespace JupiterX_Core\Raven\Modules\Loop_Grid\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use JupiterX_Core\Raven\Base\Base_Widget;
use JupiterX_Core\Raven\Core\Document_Types\Type\Jupiterx_Loop_Item_Document;

class Loop_Term_Count extends Base_Widget {

	public function get_name() {
		return 'raven-loop-term-count';
	}

	public function get_title() {
		return esc_html__( 'Loop Term Count', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-loop-term-count';
	}

	public function get_keywords() {
		return [ 'loop', 'term', 'taxonomy', 'count', 'category' ];
	}

	public function get_style_depends() {
		return [ 'jupiterx-core-raven-frontend' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Count', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'before',
			[
				'label'   => esc_html__( 'Before', 'jupiterx-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$this->add_control(
			'after',
			[
				'label'   => esc_html__( 'After', 'jupiterx-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( ' posts', 'jupiterx-core' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Count', 'jupiterx-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'jupiterx-core' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'jupiterx-core' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-term-count-wrap' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color',
			[
				'label'     => esc_html__( 'Color', 'jupiterx-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-term-count' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .jupiterx-loop-term-count',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'background',
				'selector' => '{{WRAPPER}} .jupiterx-loop-term-count-wrap',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'border',
				'selector' => '{{WRAPPER}} .jupiterx-loop-term-count-wrap',
			]
		);

		$this->add_responsive_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Border radius', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-term-count-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label'      => esc_html__( 'Padding', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-term-count-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$term = $this->get_display_term();

		if ( ! $term ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		$count = $term instanceof \WP_Term ? (int) $term->count : (int) $term->count;

		echo '<div class="jupiterx-loop-term-count-wrap"><span class="jupiterx-loop-term-count">';
		echo esc_html( ( $settings['before'] ?? '' ) . $count . ( $settings['after'] ?? '' ) );
		echo '</span></div>';
	}

	/**
	 * @return \WP_Term|object|null
	 */
	protected function get_display_term() {
		$term = Loop_Grid::get_current_loop_term();

		if ( $term instanceof \WP_Term ) {
			return $term;
		}

		$queried_object = get_queried_object();

		if ( $queried_object instanceof \WP_Term ) {
			return $queried_object;
		}

		if ( $this->allow_loop_term_placeholder() ) {
			return (object) [
				'term_id'     => 0,
				'name'        => esc_html__( 'Loop Term Title', 'jupiterx-core' ),
				'taxonomy'    => 'category',
				'description' => '',
				'count'       => 6,
			];
		}

		return null;
	}

	protected function allow_loop_term_placeholder() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$plugin = \Elementor\Plugin::$instance;

		if ( $plugin->editor->is_edit_mode() || $plugin->preview->is_preview_mode() ) {
			return true;
		}

		return Jupiterx_Loop_Item_Document::is_preview_context();
	}
}

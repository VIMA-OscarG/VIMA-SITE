<?php

namespace JupiterX_Core\Raven\Modules\Timeline\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use JupiterX_Core\Raven\Modules\Timeline\Classes\Base_Timeline;

class Horizontal_Timeline extends Base_Timeline {
	public function get_name() {
		return 'raven-horizontal-timeline';
	}

	public function get_title() {
		return esc_html__( 'Horizontal Timeline', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-horizontal-timeline';
	}

	protected function get_timeline_type() {
		return 'horizontal';
	}

	protected function register_controls() {
		$this->register_items_controls();
		$this->register_layout_controls();
		$this->register_style_controls();
		$this->register_arrows_style_controls();
	}

	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'jupiterx-core' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Columns', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 6,
				'default' => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline--horizontal .raven-timeline-item' => 'flex: 0 0 calc(100% / {{VALUE}}); max-width: calc(100% / {{VALUE}});',
				],
				'frontend_available' => true,
				'render_type' => 'template',
			]
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			[
				'label' => esc_html__( 'Slides to Scroll', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'vertical_layout',
			[
				'label' => esc_html__( 'Layout', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'toggle' => false,
				'default' => 'top',
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'jupiterx-core' ),
						'icon' => 'eicon-v-align-top',
					],
					'chess' => [
						'title' => esc_html__( 'Chess', 'jupiterx-core' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'jupiterx-core' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
			]
		);

		$this->add_control(
			'horizontal_alignment',
			[
				'label' => esc_html__( 'Horizontal Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'left',
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-right',
					],
				],
			]
		);

		$this->add_control(
			'navigation_type',
			[
				'label' => esc_html__( 'Navigation Type', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'scroll-bar',
				'options' => [
					'scroll-bar' => esc_html__( 'Scroll Bar', 'jupiterx-core' ),
					'arrows-nav' => esc_html__( 'Arrows Navigation', 'jupiterx-core' ),
				],
			]
		);

		$this->add_control(
			'prev_arrow',
			[
				'label' => esc_html__( 'Prev Arrow Icon', 'jupiterx-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'eicon-chevron-left',
					'library' => 'eicons',
				],
				'condition' => [
					'navigation_type' => 'arrows-nav',
				],
			]
		);

		$this->add_control(
			'next_arrow',
			[
				'label' => esc_html__( 'Next Arrow Icon', 'jupiterx-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'eicon-chevron-right',
					'library' => 'eicons',
				],
				'condition' => [
					'navigation_type' => 'arrows-nav',
				],
			]
		);

		$this->add_responsive_control(
			'items_gap',
			[
				'label' => esc_html__( 'Items Gap', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline--horizontal .raven-timeline-item' => 'padding-left: calc({{SIZE}}{{UNIT}} / 2); padding-right: calc({{SIZE}}{{UNIT}} / 2);',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_arrows_style_controls() {
		$this->start_controls_section(
			'section_arrows_style',
			[
				'label' => esc_html__( 'Arrows', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'navigation_type' => 'arrows-nav',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_arrows_style' );
		$this->start_controls_tab( 'tab_arrows_normal', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control(
			'arrow_color',
			[
				'label' => esc_html__( 'Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_background_color',
			[
				'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_arrows_hover', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control(
			'arrow_hover_color',
			[
				'label' => esc_html__( 'Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow:not(.is-disabled):hover' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'arrow_hover_background_color',
			[
				'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow:not(.is-disabled):hover' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'arrow_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow:not(.is-disabled):hover' => 'border-color: {{VALUE}};',
				],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'arrow_size',
			[
				'label' => esc_html__( 'Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 120,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'arrow_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow i, {{WRAPPER}} .raven-timeline-arrow svg' => 'font-size: {{SIZE}}{{UNIT}}; width: 1em; height: 1em;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'arrow_border',
				'selector' => '{{WRAPPER}} .raven-timeline-arrow',
			]
		);

		$this->add_control(
			'arrow_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'arrow_box_shadow',
				'selector' => '{{WRAPPER}} .raven-timeline-arrow',
			]
		);

		$this->add_control(
			'prev_arrow_position',
			[
				'label' => esc_html__( 'Prev Arrow Position', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'prev_left_position',
			[
				'label' => esc_html__( 'Left Indent', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => -400,
						'max' => 400,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-prev' => 'left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'next_arrow_position',
			[
				'label' => esc_html__( 'Next Arrow Position', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'next_right_position',
			[
				'label' => esc_html__( 'Right Indent', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => -400,
						'max' => 400,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-next' => 'right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$classes  = [
			'raven-timeline',
			'raven-timeline--horizontal',
			'raven-timeline--layout-' . $settings['vertical_layout'],
			'raven-timeline--navigation-' . $settings['navigation_type'],
			'raven-timeline--align-' . $settings['horizontal_alignment'],
		];

		$this->add_render_attribute( 'timeline', [
			'class' => $classes,
			'data-raven-horizontal-timeline' => wp_json_encode( [
				'columns' => [
					'desktop' => ! empty( $settings['columns'] ) ? (int) $settings['columns'] : 3,
					'tablet' => ! empty( $settings['columns_tablet'] ) ? (int) $settings['columns_tablet'] : 2,
					'mobile' => ! empty( $settings['columns_mobile'] ) ? (int) $settings['columns_mobile'] : 1,
				],
				'slidesToScroll' => [
					'desktop' => ! empty( $settings['slides_to_scroll'] ) ? (int) $settings['slides_to_scroll'] : 1,
					'tablet' => ! empty( $settings['slides_to_scroll_tablet'] ) ? (int) $settings['slides_to_scroll_tablet'] : 1,
					'mobile' => ! empty( $settings['slides_to_scroll_mobile'] ) ? (int) $settings['slides_to_scroll_mobile'] : 1,
				],
			] ),
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'timeline' ); ?>>
			<?php if ( 'arrows-nav' === $settings['navigation_type'] ) : ?>
				<button type="button" class="raven-timeline-arrow raven-timeline-prev" aria-label="<?php esc_attr_e( 'Previous', 'jupiterx-core' ); ?>"><?php Icons_Manager::render_icon( $settings['prev_arrow'], [ 'aria-hidden' => 'true' ] ); ?></button>
				<button type="button" class="raven-timeline-arrow raven-timeline-next" aria-label="<?php esc_attr_e( 'Next', 'jupiterx-core' ); ?>"><?php Icons_Manager::render_icon( $settings['next_arrow'], [ 'aria-hidden' => 'true' ] ); ?></button>
			<?php endif; ?>
			<div class="raven-timeline-viewport">
				<div class="raven-timeline-track">
					<div class="raven-timeline-list raven-timeline-list--top">
						<?php foreach ( $settings['cards_list'] as $index => $item ) : ?>
							<?php $this->render_horizontal_item_slot( $item, $index, 'top', $settings['vertical_layout'] ); ?>
						<?php endforeach; ?>
					</div>
					<div class="raven-timeline-list raven-timeline-list--middle">
						<div class="raven-timeline-line"><div class="raven-timeline-line-progress"></div></div>
						<?php foreach ( $settings['cards_list'] as $index => $item ) : ?>
							<div class="<?php echo esc_attr( $this->get_horizontal_item_classes( $item, 'middle', $settings['vertical_layout'] ) ); ?>" data-item-id="<?php echo esc_attr( $item['_id'] ); ?>">
								<?php $this->render_point( $item ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="raven-timeline-list raven-timeline-list--bottom">
						<?php foreach ( $settings['cards_list'] as $index => $item ) : ?>
							<?php $this->render_horizontal_item_slot( $item, $index, 'bottom', $settings['vertical_layout'] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_horizontal_item_slot( $item, $index, $slot, $layout ) {
		?>
		<div class="<?php echo esc_attr( $this->get_horizontal_item_classes( $item, $slot, $layout ) ); ?>" data-item-id="<?php echo esc_attr( $item['_id'] ); ?>">
			<?php $this->render_horizontal_slot_content( $item, $index, $slot, $layout ); ?>
		</div>
		<?php
	}

	private function render_horizontal_slot_content( $item, $index, $slot, $layout ) {
		if ( 'top' === $layout ) {
			'top' === $slot ? $this->render_card( $item, $index, false ) : $this->render_meta( $item );
			return;
		}

		if ( 'bottom' === $layout ) {
			'top' === $slot ? $this->render_meta( $item ) : $this->render_card( $item, $index, false );
			return;
		}

		$is_odd = 1 === $index % 2;

		if ( 'top' === $slot ) {
			$is_odd ? $this->render_meta( $item ) : $this->render_card( $item, $index, false );
			return;
		}

		$is_odd ? $this->render_card( $item, $index, false ) : $this->render_meta( $item );
	}

	private function get_horizontal_item_classes( $item, $slot, $layout ) {
		$classes = [
			'raven-timeline-item',
			'raven-timeline-item--' . $slot,
			'raven-timeline-item--layout-' . $layout,
			'elementor-repeater-item-' . $item['_id'],
		];

		if ( ! empty( $item['is_item_active'] ) && 'yes' === $item['is_item_active'] ) {
			$classes[] = 'is-active';
		}

		return implode( ' ', $classes );
	}
}

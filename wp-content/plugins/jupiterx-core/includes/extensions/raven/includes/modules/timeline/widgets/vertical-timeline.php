<?php

namespace JupiterX_Core\Raven\Modules\Timeline\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use JupiterX_Core\Raven\Modules\Timeline\Classes\Base_Timeline;

class Vertical_Timeline extends Base_Timeline {
	public function get_name() {
		return 'raven-vertical-timeline';
	}

	public function get_title() {
		return esc_html__( 'Vertical Timeline', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-vertical-timeline';
	}

	protected function get_timeline_type() {
		return 'vertical';
	}

	protected function register_controls() {
		$this->register_items_controls();
		$this->register_layout_controls();
		$this->register_style_controls();
	}

	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'animate_cards',
			[
				'label' => esc_html__( 'Animate Cards', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'horizontal_alignment',
			[
				'label' => esc_html__( 'Horizontal Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'center',
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
			'vertical_alignment',
			[
				'label' => esc_html__( 'Vertical Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'middle',
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'jupiterx-core' ),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => esc_html__( 'Middle', 'jupiterx-core' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'jupiterx-core' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
			]
		);

		$this->add_responsive_control(
			'horizontal_space',
			[
				'label' => esc_html__( 'Horizontal Space', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 150,
					],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline--align-center .raven-timeline-point' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-timeline--align-left .raven-timeline-point' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-timeline--align-right .raven-timeline-point' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'vertical_space',
			[
				'label' => esc_html__( 'Vertical Space', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 150,
					],
				],
				'default' => [
					'size' => 30,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline--vertical .raven-timeline-item + .raven-timeline-item' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$classes  = [
			'raven-timeline',
			'raven-timeline--vertical',
			'raven-timeline--align-' . $settings['horizontal_alignment'],
			'raven-timeline--valign-' . $settings['vertical_alignment'],
		];

		$this->add_render_attribute( 'timeline', 'class', $classes );
		?>
		<div <?php $this->print_render_attribute_string( 'timeline' ); ?>>
			<div class="raven-timeline-line"><div class="raven-timeline-line-progress"></div></div>
			<div class="raven-timeline-list">
				<?php foreach ( $settings['cards_list'] as $index => $item ) : ?>
					<?php
					$item_classes = [
						'raven-timeline-item',
						'elementor-repeater-item-' . $item['_id'],
					];

					if ( ! empty( $settings['animate_cards'] ) && 'yes' === $settings['animate_cards'] ) {
						$item_classes[] = 'raven-timeline-item--animated';
					}

					if ( ! empty( $item['is_item_active'] ) && 'yes' === $item['is_item_active'] ) {
						$item_classes[] = 'is-active';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
						<?php if ( 'center' === $settings['horizontal_alignment'] ) : ?>
							<?php $this->render_card( $item, $index, false ); ?>
							<?php $this->render_point( $item ); ?>
							<?php $this->render_meta( $item ); ?>
						<?php elseif ( 'left' === $settings['horizontal_alignment'] ) : ?>
							<?php $this->render_point( $item ); ?>
							<?php $this->render_card( $item, $index ); ?>
						<?php else : ?>
							<?php $this->render_card( $item, $index ); ?>
							<?php $this->render_point( $item ); ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}

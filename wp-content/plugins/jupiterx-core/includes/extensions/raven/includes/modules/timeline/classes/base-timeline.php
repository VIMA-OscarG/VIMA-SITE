<?php

namespace JupiterX_Core\Raven\Modules\Timeline\Classes;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils as ElementorUtils;
use JupiterX_Core\Raven\Base\Base_Widget;

abstract class Base_Timeline extends Base_Widget {
	protected function get_timeline_type() {
		return 'vertical';
	}

	protected function get_default_items() {
		return [
			[
				'is_item_active' => 'yes',
				'item_title' => esc_html__( 'Card #1', 'jupiterx-core' ),
				'item_meta' => esc_html__( 'Thursday, August 31, 2020', 'jupiterx-core' ),
				'item_desc' => esc_html__( 'Lorem ipsum dolor sit amet, mea ei viderer probatus consequuntur, sonet vocibus lobortis has ad. Eos erant indoctum an, dictas invidunt est ex, et sea consulatu torquatos. Nostro aperiam petentium eu nam, mel debet urbanitas ad, idque complectitur eu quo. An sea autem dolore dolores.', 'jupiterx-core' ),
				'item_point_type' => 'icon',
				'item_point_text' => 'A',
			],
			[
				'item_title' => esc_html__( 'Card #2', 'jupiterx-core' ),
				'item_meta' => esc_html__( 'Thursday, August 29, 2020', 'jupiterx-core' ),
				'item_desc' => esc_html__( 'Lorem ipsum dolor sit amet, mea ei viderer probatus consequuntur, sonet vocibus lobortis has ad. Eos erant indoctum an, dictas invidunt est ex, et sea consulatu torquatos. Nostro aperiam petentium eu nam, mel debet urbanitas ad, idque complectitur eu quo. An sea autem dolore dolores.', 'jupiterx-core' ),
				'item_point_type' => 'icon',
				'item_point_text' => 'B',
			],
			[
				'item_title' => esc_html__( 'Card #3', 'jupiterx-core' ),
				'item_meta' => esc_html__( 'Thursday, August 28, 2020', 'jupiterx-core' ),
				'item_desc' => esc_html__( 'Lorem ipsum dolor sit amet, mea ei viderer probatus consequuntur, sonet vocibus lobortis has ad. Eos erant indoctum an, dictas invidunt est ex, et sea consulatu torquatos. Nostro aperiam petentium eu nam, mel debet urbanitas ad, idque complectitur eu quo. An sea autem dolore dolores.', 'jupiterx-core' ),
				'item_point_type' => 'icon',
				'item_point_text' => 'C',
			],
		];
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

	protected function register_items_controls() {
		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Items', 'jupiterx-core' ),
			]
		);

		$repeater = new Repeater();

		if ( 'horizontal' === $this->get_timeline_type() ) {
			$repeater->add_control(
				'is_item_active',
				[
					'label' => esc_html__( 'Active', 'jupiterx-core' ),
					'type' => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
				]
			);
		}

		$repeater->add_control(
			'show_item_image',
			[
				'label' => esc_html__( 'Show Image', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		if ( 'vertical' === $this->get_timeline_type() ) {
			$repeater->add_control(
				'item_image_position',
				[
					'label' => esc_html__( 'Image Position', 'jupiterx-core' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'inside',
					'options' => [
						'inside' => esc_html__( 'Inside Before Text', 'jupiterx-core' ),
						'inside_after' => esc_html__( 'Inside After Text', 'jupiterx-core' ),
						'outside_before' => esc_html__( 'Outside Before Meta', 'jupiterx-core' ),
						'outside_after' => esc_html__( 'Outside After Meta', 'jupiterx-core' ),
					],
					'condition' => [
						'show_item_image' => 'yes',
					],
				]
			);
		}

		$repeater->add_control(
			'item_image',
			[
				'label' => esc_html__( 'Image', 'jupiterx-core' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => ElementorUtils::get_placeholder_image_src(),
				],
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'show_item_image' => 'yes',
				],
			]
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'item_image',
				'default' => 'medium',
				'condition' => [
					'show_item_image' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'item_title',
			[
				'label' => esc_html__( 'Title', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'item_meta',
			[
				'label' => esc_html__( 'Meta', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'item_desc',
			[
				'label' => esc_html__( 'Description', 'jupiterx-core' ),
				'type' => Controls_Manager::WYSIWYG,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'point_heading',
			[
				'label' => esc_html__( 'Point', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_point_type',
			[
				'label' => esc_html__( 'Point Content Type', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon' => esc_html__( 'Icon', 'jupiterx-core' ),
					'text' => esc_html__( 'Text', 'jupiterx-core' ),
				],
			]
		);

		$repeater->add_control(
			'item_point_icon',
			[
				'label' => esc_html__( 'Point Icon', 'jupiterx-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-calendar-alt',
					'library' => 'fa-solid',
				],
				'condition' => [
					'item_point_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'item_point_text',
			[
				'label' => esc_html__( 'Point Text', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'A',
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'item_point_type' => 'text',
				],
			]
		);

		$repeater->add_control(
			'button_heading',
			[
				'label' => esc_html__( 'Button', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_btn_text',
			[
				'label' => esc_html__( 'Text', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'item_btn_url',
			[
				'label' => esc_html__( 'Link', 'jupiterx-core' ),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'cards_list',
			[
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => $this->get_default_items(),
				'title_field' => '{{{ item_title }}}',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Title HTML Tag', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_title_tag_options(),
				'default' => 'h5',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_controls() {
		$this->register_card_style_controls();
		$this->register_image_style_controls();
		$this->register_meta_style_controls();
		$this->register_content_style_controls();
		$this->register_point_style_controls();
		$this->register_line_style_controls();
		$this->register_button_style_controls();
	}

	protected function register_card_style_controls() {
		$this->start_controls_section(
			'section_card_style',
			[
				'label' => esc_html__( 'Cards', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'cards_border',
				'label' => esc_html__( 'Border', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-timeline-card',
			]
		);

		$this->add_responsive_control(
			'cards_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .raven-timeline-card-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'cards_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'cards_style_tabs' );
		$this->start_controls_tab( 'cards_normal_styles', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control(
			'cards_background_normal',
			[
				'label' => esc_html__( 'Background', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card, {{WRAPPER}} .raven-timeline-card-inner' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'cards_box_shadow_normal',
				'selector' => '{{WRAPPER}} .raven-timeline-card',
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'cards_hover_styles', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control(
			'cards_background_hover',
			[
				'label' => esc_html__( 'Background', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card, {{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-inner, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-inner' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'cards_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card' => 'border-color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'cards_box_shadow_hover',
				'selector' => '{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card',
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'cards_active_styles', [ 'label' => esc_html__( 'Active', 'jupiterx-core' ) ] );
		$this->add_control(
			'cards_background_active',
			[
				'label' => esc_html__( 'Background', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card, {{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-inner' => 'background-color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'cards_border_color_active',
			[
				'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card' => 'border-color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'cards_box_shadow_active',
				'selector' => '{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card',
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_image_style_controls() {
		$this->start_controls_section(
			'section_image_style',
			[
				'label' => esc_html__( 'Image', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_spacing',
			[
				'label' => esc_html__( 'Spacing', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -50,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-image-before' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-timeline-image-after' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_meta_style_controls() {
		$this->start_controls_section(
			'section_meta_style',
			[
				'label' => esc_html__( 'Meta', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'meta_border',
				'label' => esc_html__( 'Border', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-timeline-meta, {{WRAPPER}} .raven-timeline-card-meta',
			]
		);

		$this->add_control(
			'meta_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-meta, {{WRAPPER}} .raven-timeline-card-meta' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'meta_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-meta, {{WRAPPER}} .raven-timeline-card-meta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_margin',
			[
				'label' => esc_html__( 'Margin', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-meta, {{WRAPPER}} .raven-timeline-card-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_typography',
				'selector' => '{{WRAPPER}} .raven-timeline-meta-content, {{WRAPPER}} .raven-timeline-card-meta',
			]
		);

		$this->start_controls_tabs( 'meta_style_tabs' );
		$this->start_controls_tab( 'meta_normal_styles', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control( 'meta_normal_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-meta-content, {{WRAPPER}} .raven-timeline-card-meta' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'meta_normal_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-meta, {{WRAPPER}} .raven-timeline-card-meta' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'meta_normal_box_shadow',
			'selector' => '{{WRAPPER}} .raven-timeline-meta, {{WRAPPER}} .raven-timeline-card-meta',
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'meta_hover_styles', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control( 'meta_hover_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-meta-content, {{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-meta-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-meta' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'meta_hover_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-meta' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'meta_hover_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-meta' => 'border-color: {{VALUE}};',
			],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'meta_hover_box_shadow',
			'selector' => '{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-meta',
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'meta_active_styles', [ 'label' => esc_html__( 'Active', 'jupiterx-core' ) ] );
		$this->add_control( 'meta_active_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-meta-content, {{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-meta' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'meta_active_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-meta' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'meta_active_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-meta' => 'border-color: {{VALUE}};',
			],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'meta_active_box_shadow',
			'selector' => '{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-meta, {{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-meta',
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_content_style_controls() {
		$this->start_controls_section(
			'section_content_style',
			[
				'label' => esc_html__( 'Content', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_content_border',
				'label' => esc_html__( 'Border', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-timeline-card-content',
			]
		);

		$this->add_control(
			'card_content_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'card_content_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'card_content_style_tabs' );
		$this->start_controls_tab( 'card_content_normal_styles', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control( 'card_content_normal_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-content' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'card_content_normal_shadow',
			'selector' => '{{WRAPPER}} .raven-timeline-card-content',
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'card_content_hover_styles', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control( 'card_content_hover_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-content' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'card_content_hover_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-content' => 'border-color: {{VALUE}};',
			],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'card_content_hover_shadow',
			'selector' => '{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-content',
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'card_content_active_styles', [ 'label' => esc_html__( 'Active', 'jupiterx-core' ) ] );
		$this->add_control( 'card_content_active_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-content' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'card_content_active_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-content' => 'border-color: {{VALUE}};',
			],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'card_content_active_shadow',
			'selector' => '{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-content',
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'card_content_align',
			[
				'label' => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
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
					'justify' => [
						'title' => esc_html__( 'Justified', 'jupiterx-core' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_heading',
			[
				'label' => esc_html__( 'Title', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Title Typography', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-timeline-card-title',
			]
		);

		$this->start_controls_tabs( 'card_title_style_tabs' );
		$this->start_controls_tab( 'card_title_normal_styles', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control(
			'card_title_normal_color',
			[
				'label' => esc_html__( 'Title Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-title' => 'color: {{VALUE}};',
				],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'card_title_hover_styles', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control( 'card_title_hover_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-title, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-title' => 'color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'card_title_active_styles', [ 'label' => esc_html__( 'Active', 'jupiterx-core' ) ] );
		$this->add_control( 'card_title_active_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-title' => 'color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__( 'Title Margin', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'desc_heading',
			[
				'label' => esc_html__( 'Description', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'label' => esc_html__( 'Description Typography', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-timeline-card-desc',
			]
		);

		$this->start_controls_tabs( 'card_desc_style_tabs' );
		$this->start_controls_tab( 'card_desc_normal_styles', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control(
			'card_desc_normal_color',
			[
				'label' => esc_html__( 'Description Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-desc' => 'color: {{VALUE}};',
				],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'card_desc_hover_styles', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control( 'card_desc_hover_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-card-desc, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-card-desc' => 'color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'card_desc_active_styles', [ 'label' => esc_html__( 'Active', 'jupiterx-core' ) ] );
		$this->add_control( 'card_desc_active_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-card-desc' => 'color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'orders_heading',
			[
				'label' => esc_html__( 'Orders', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control( 'image_order', [
			'label' => esc_html__( 'Image Order', 'jupiterx-core' ),
			'type' => Controls_Manager::NUMBER,
			'min' => 0,
			'max' => 10,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-img' => 'order: {{VALUE}};',
			],
		] );
		$this->add_control( 'title_order', [
			'label' => esc_html__( 'Title Order', 'jupiterx-core' ),
			'type' => Controls_Manager::NUMBER,
			'min' => 0,
			'max' => 10,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-title' => 'order: {{VALUE}};',
			],
		] );
		$this->add_control( 'desc_order', [
			'label' => esc_html__( 'Description Order', 'jupiterx-core' ),
			'type' => Controls_Manager::NUMBER,
			'min' => 0,
			'max' => 10,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-desc' => 'order: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function register_point_style_controls() {
		$this->start_controls_section(
			'section_point_style',
			[
				'label' => esc_html__( 'Point', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'point_type_style_tabs' );
		$this->start_controls_tab( 'point_type_text_styles', [ 'label' => esc_html__( 'Text', 'jupiterx-core' ) ] );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'point_text_typography',
				'selector' => '{{WRAPPER}} .raven-timeline-point-content--text',
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'point_type_icon_styles', [ 'label' => esc_html__( 'Icon', 'jupiterx-core' ) ] );
		$this->add_responsive_control(
			'point_type_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 16,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-point-content--icon i, {{WRAPPER}} .raven-timeline-point-content--icon svg' => 'font-size: {{SIZE}}{{UNIT}}; width: 1em; height: 1em;',
				],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'point_size',
			[
				'label' => esc_html__( 'Point Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 120,
					],
				],
				'default' => [
					'size' => 46,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-point-content' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'point_border',
				'label' => esc_html__( 'Border', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-timeline-point-content',
			]
		);

		$this->add_control(
			'point_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-point-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'point_style_tabs' );
		$this->start_controls_tab( 'point_normal_styles', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control( 'point_normal_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'default' => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-point-content' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'point_normal_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'default' => '#e8e8f6',
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-point-content' => 'background-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'point_hover_styles', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control( 'point_hover_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-point-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-point-content' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'point_hover_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-point-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-point-content' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'point_hover_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item:hover .raven-timeline-point-content, {{WRAPPER}} .raven-timeline-item.is-hover .raven-timeline-point-content' => 'border-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'point_active_styles', [ 'label' => esc_html__( 'Active', 'jupiterx-core' ) ] );
		$this->add_control( 'point_active_color', [
			'label' => esc_html__( 'Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-point-content' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'point_active_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'default' => '#0077ff',
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-point-content' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'point_active_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-item.is-active .raven-timeline-point-content' => 'border-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_line_style_controls() {
		$this->start_controls_section(
			'section_line_style',
			[
				'label' => esc_html__( 'Line', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'line_background_color',
			[
				'label' => esc_html__( 'Line Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#e8e8f6',
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-line' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'progress_background_color',
			[
				'label' => esc_html__( 'Progress Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#0077ff',
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-line-progress' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'line_width',
			[
				'label' => esc_html__( 'Line Width', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 12,
					],
				],
				'default' => [
					'size' => 2,
				],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline--vertical .raven-timeline-line' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-timeline--horizontal .raven-timeline-line' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_button_style_controls() {
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .raven-timeline-card-button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );
		$this->start_controls_tab( 'tab_button_normal', [ 'label' => esc_html__( 'Normal', 'jupiterx-core' ) ] );
		$this->add_control( 'button_color', [
			'label' => esc_html__( 'Text Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-button' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'button_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-button' => 'background-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_button_hover', [ 'label' => esc_html__( 'Hover', 'jupiterx-core' ) ] );
		$this->add_control( 'button_hover_color', [
			'label' => esc_html__( 'Text Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-button:hover' => 'color: {{VALUE}};',
			],
		] );
		$this->add_control( 'button_hover_background_color', [
			'label' => esc_html__( 'Background Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-button:hover' => 'background-color: {{VALUE}};',
			],
		] );
		$this->add_control( 'button_hover_border_color', [
			'label' => esc_html__( 'Border Color', 'jupiterx-core' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .raven-timeline-card-button:hover' => 'border-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .raven-timeline-card-button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .raven-timeline-card-button',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label' => esc_html__( 'Margin', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-timeline-card-btn-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render_item_image( $item, $class = '' ) {
		if ( empty( $item['show_item_image'] ) || 'yes' !== $item['show_item_image'] || empty( $item['item_image']['url'] ) ) {
			return;
		}

		$image_html = Group_Control_Image_Size::get_attachment_image_html( $item, 'item_image', 'item_image' );

		if ( empty( $image_html ) ) {
			$image_html = '<img src="' . esc_url( $item['item_image']['url'] ) . '" alt="">';
		}

		printf(
			'<div class="raven-timeline-card-img %1$s">%2$s</div>',
			esc_attr( $class ),
			$image_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	protected function render_point( $item ) {
		$point_type = ! empty( $item['item_point_type'] ) ? $item['item_point_type'] : 'icon';
		?>
		<div class="raven-timeline-point">
			<div class="raven-timeline-point-content raven-timeline-point-content--<?php echo esc_attr( $point_type ); ?>">
				<?php if ( 'text' === $point_type ) : ?>
					<span><?php echo esc_html( ! empty( $item['item_point_text'] ) ? $item['item_point_text'] : '' ); ?></span>
				<?php else : ?>
					<?php Icons_Manager::render_icon( ! empty( $item['item_point_icon'] ) ? $item['item_point_icon'] : [ 'value' => 'fas fa-calendar-alt', 'library' => 'fa-solid' ], [ 'aria-hidden' => 'true' ] ); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	protected function render_meta( $item ) {
		?>
		<div class="raven-timeline-meta">
			<?php if ( ! empty( $item['item_meta'] ) ) : ?>
				<span class="raven-timeline-meta-content"><?php echo esc_html( $item['item_meta'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function render_card( $item, $index, $show_meta = true ) {
		$title_tag = ElementorUtils::validate_html_tag( $this->get_settings_for_display( 'title_tag' ) );
		$image_position = ! empty( $item['item_image_position'] ) ? $item['item_image_position'] : 'inside';

		$this->add_render_attribute( 'timeline-button-' . $index, 'class', 'raven-timeline-card-button' );

		if ( ! empty( $item['item_btn_url']['url'] ) ) {
			$this->add_link_attributes( 'timeline-button-' . $index, $item['item_btn_url'] );
		}

		?>
		<div class="raven-timeline-card">
			<div class="raven-timeline-card-inner">
				<?php
				if ( in_array( $image_position, [ 'inside', 'outside_before' ], true ) ) {
					$this->render_item_image( $item, 'raven-timeline-image-before' );
				}
				?>
				<div class="raven-timeline-card-content">
					<?php if ( $show_meta && ! empty( $item['item_meta'] ) ) : ?>
						<div class="raven-timeline-card-meta"><?php echo esc_html( $item['item_meta'] ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $item['item_title'] ) ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="raven-timeline-card-title"><?php echo esc_html( $item['item_title'] ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $item['item_desc'] ) ) : ?>
						<div class="raven-timeline-card-desc"><?php echo wp_kses_post( $item['item_desc'] ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $item['item_btn_text'] ) ) : ?>
						<div class="raven-timeline-card-btn-wrap">
							<a <?php $this->print_render_attribute_string( 'timeline-button-' . $index ); ?>><?php echo esc_html( $item['item_btn_text'] ); ?></a>
						</div>
					<?php endif; ?>
				</div>
				<?php
				if ( in_array( $image_position, [ 'inside_after', 'outside_after' ], true ) ) {
					$this->render_item_image( $item, 'raven-timeline-image-after' );
				}
				?>
			</div>
		</div>
		<?php
	}
}

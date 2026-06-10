<?php

namespace JupiterX_Core\Raven\Modules\Instagram_Feed\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use JupiterX_Core\Raven\Base\Base_Widget;

class Instagram_Feed extends Base_Widget {
	public function get_name() {
		return 'raven-instagram-feed';
	}

	public function get_title() {
		return esc_html__( 'Instagram Feed', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-instagram-feed';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), [ 'instagram', 'feed', 'gallery', 'social' ] );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_item_style_controls();
		$this->register_overlay_style_controls();
		$this->register_caption_style_controls();
		$this->register_meta_style_controls();
	}

	protected function register_content_controls() {
		$this->start_controls_section(
			'section_instagram_settings',
			[
				'label' => esc_html__( 'Instagram Settings', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'endpoint',
			[
				'label' => esc_html__( 'What to Display', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'self',
				'options' => [
					'self' => esc_html__( 'My Photos', 'jupiterx-core' ),
					'hashtag' => esc_html__( 'Tagged Photos', 'jupiterx-core' ),
				],
			]
		);

		$this->add_control(
			'access_token',
			[
				'label' => esc_html__( 'Access Token', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Use an Instagram Graph API access token. Hashtag feeds require a Business/Creator account token.', 'jupiterx-core' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'api_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'This native widget uses the official Instagram Graph API. Crocoblock proxy/license requests are intentionally not used.', 'jupiterx-core' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'business_account_id',
			[
				'label' => esc_html__( 'Instagram Business Account ID', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'condition' => [
					'endpoint' => 'hashtag',
				],
			]
		);

		$this->add_control(
			'hashtag',
			[
				'label' => esc_html__( 'Hashtag', 'jupiterx-core' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => esc_html__( 'Enter without the # symbol.', 'jupiterx-core' ),
				'condition' => [
					'endpoint' => 'hashtag',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'order_by',
			[
				'label' => esc_html__( 'Order By', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'recent_media',
				'options' => [
					'recent_media' => esc_html__( 'Recent Media', 'jupiterx-core' ),
					'top_media' => esc_html__( 'Top Media', 'jupiterx-core' ),
				],
				'condition' => [
					'endpoint' => 'hashtag',
				],
			]
		);

		$this->add_control(
			'cache_timeout',
			[
				'label' => esc_html__( 'Cache Timeout', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'hour',
				'options' => [
					'none' => esc_html__( 'None', 'jupiterx-core' ),
					'minute' => esc_html__( 'Minute', 'jupiterx-core' ),
					'hour' => esc_html__( 'Hour', 'jupiterx-core' ),
					'day' => esc_html__( 'Day', 'jupiterx-core' ),
					'week' => esc_html__( 'Week', 'jupiterx-core' ),
				],
			]
		);

		$this->add_control(
			'posts_counter',
			[
				'label' => esc_html__( 'Number of Instagram Posts', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 50,
				'step' => 1,
			]
		);

		$this->add_control(
			'photo_size',
			[
				'label' => esc_html__( 'Photo Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'high',
				'options' => [
					'low' => esc_html__( 'Low', 'jupiterx-core' ),
					'standard' => esc_html__( 'Standard', 'jupiterx-core' ),
					'high' => esc_html__( 'High', 'jupiterx-core' ),
				],
				'description' => esc_html__( 'Official Graph API returns the best available media URL; this setting is kept for Jet-equivalent editor UX.', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'post_link',
			[
				'label' => esc_html__( 'Enable Linking Photos', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'post_link_type',
			[
				'label' => esc_html__( 'Link Type', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => [
					'post' => esc_html__( 'Post Link', 'jupiterx-core' ),
					'lightbox' => esc_html__( 'Lightbox', 'jupiterx-core' ),
					'none' => esc_html__( 'None', 'jupiterx-core' ),
				],
				'condition' => [
					'post_link' => 'yes',
				],
			]
		);

		$this->add_control(
			'post_link_target',
			[
				'label' => esc_html__( 'Open in New Window', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'condition' => [
					'post_link' => 'yes',
					'post_link_type' => 'post',
				],
			]
		);

		$this->add_control(
			'post_caption',
			[
				'label' => esc_html__( 'Enable Caption', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'post_caption_length',
			[
				'label' => esc_html__( 'Caption Length', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 50,
				'min' => 1,
				'max' => 300,
				'condition' => [
					'post_caption' => 'yes',
				],
			]
		);

		$this->add_control(
			'post_likes_count',
			[
				'label' => esc_html__( 'Enable Likes Count', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'condition' => [
					'endpoint' => 'hashtag',
				],
			]
		);

		$this->add_control(
			'post_comments_count',
			[
				'label' => esc_html__( 'Enable Comments Count', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'condition' => [
					'endpoint' => 'hashtag',
				],
			]
		);

		$this->add_control(
			'content_position',
			[
				'label' => esc_html__( 'Content Position', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'overlay',
				'options' => [
					'overlay' => esc_html__( 'On Image', 'jupiterx-core' ),
					'below' => esc_html__( 'Below Image', 'jupiterx-core' ),
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout Settings', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'layout_type',
			[
				'label' => esc_html__( 'Layout Type', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'masonry',
				'options' => [
					'masonry' => esc_html__( 'Masonry', 'jupiterx-core' ),
					'grid' => esc_html__( 'Grid', 'jupiterx-core' ),
					'list' => esc_html__( 'List', 'jupiterx-core' ),
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Columns', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'condition' => [
					'layout_type!' => 'list',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed--grid .raven-instagram-feed-item' => 'flex-basis: calc(100% / {{VALUE}}); max-width: calc(100% / {{VALUE}});',
					'{{WRAPPER}} .raven-instagram-feed--masonry' => 'column-count: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_height',
			[
				'label' => esc_html__( 'Item Height', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
				],
				'default' => [
					'size' => 300,
					'unit' => 'px',
				],
				'condition' => [
					'layout_type' => 'grid',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed--grid .raven-instagram-feed-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_margin',
			[
				'label' => esc_html__( 'Items Gap', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'size' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-inner' => 'margin: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-instagram-feed' => 'margin: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-instagram-feed--masonry' => 'column-gap: calc({{SIZE}}{{UNIT}} * 2);',
				],
			]
		);

		$this->add_control(
			'show_on_hover',
			[
				'label' => esc_html__( 'Show on Hover', 'jupiterx-core' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_item_style_controls() {
		$this->start_controls_section(
			'section_item_style',
			[
				'label' => esc_html__( 'Item', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_vertical_alignment',
			[
				'label' => esc_html__( 'Content Vertical Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'flex-start' => esc_html__( 'Top', 'jupiterx-core' ),
					'center' => esc_html__( 'Center', 'jupiterx-core' ),
					'flex-end' => esc_html__( 'Bottom', 'jupiterx-core' ),
					'space-between' => esc_html__( 'Space Between', 'jupiterx-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'label' => esc_html__( 'Border', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-instagram-feed-inner',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_shadow',
				'selector' => '{{WRAPPER}} .raven-instagram-feed-inner',
			]
		);

		$this->add_control(
			'item_order_heading',
			[
				'label' => esc_html__( 'Order', 'jupiterx-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'caption_order',
			[
				'label' => esc_html__( 'Caption Order', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 4,
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'order: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_order',
			[
				'label' => esc_html__( 'Meta Order', 'jupiterx-core' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 2,
				'min' => 1,
				'max' => 4,
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta' => 'order: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_overlay_style_controls() {
		$this->start_controls_section(
			'section_overlay_style',
			[
				'label' => esc_html__( 'Overlay', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'overlay_background',
				'selector' => '{{WRAPPER}} .raven-instagram-feed-content',
			]
		);

		$this->add_responsive_control(
			'overlay_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'overlay_opacity',
			[
				'label' => esc_html__( 'Opacity', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-content:before' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_caption_style_controls() {
		$this->start_controls_section(
			'section_caption_style',
			[
				'label' => esc_html__( 'Caption', 'jupiterx-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'caption_typography',
				'selector' => '{{WRAPPER}} .raven-instagram-feed-caption',
			]
		);

		$this->add_responsive_control(
			'caption_alignment',
			[
				'label' => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'caption_text_alignment',
			[
				'label' => esc_html__( 'Text Alignment', 'jupiterx-core' ),
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
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'caption_width',
			[
				'label' => esc_html__( 'Caption Width', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 1000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'caption_color',
			[
				'label' => esc_html__( 'Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'caption_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'caption_margin',
			[
				'label' => esc_html__( 'Margin', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

		$this->add_responsive_control(
			'meta_alignment',
			[
				'label' => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'jupiterx-core' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta' => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'comments_icon',
			[
				'label' => esc_html__( 'Comments Icon', 'jupiterx-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'far fa-comment',
					'library' => 'fa-regular',
				],
			]
		);

		$this->add_control(
			'likes_icon',
			[
				'label' => esc_html__( 'Likes Icon', 'jupiterx-core' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'far fa-heart',
					'library' => 'fa-regular',
				],
			]
		);

		$this->add_control(
			'meta_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'jupiterx-core' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta-icon i, {{WRAPPER}} .raven-instagram-feed-meta-icon svg' => 'font-size: {{SIZE}}{{UNIT}}; width: 1em; height: 1em;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_typography',
				'selector' => '{{WRAPPER}} .raven-instagram-feed-meta',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => esc_html__( 'Color', 'jupiterx-core' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'meta_background',
				'selector' => '{{WRAPPER}} .raven-instagram-feed-meta',
			]
		);

		$this->add_responsive_control(
			'meta_padding',
			[
				'label' => esc_html__( 'Padding', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_margin',
			[
				'label' => esc_html__( 'Margin', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_item_margin',
			[
				'label' => esc_html__( 'Item Margin', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'meta_border',
				'label' => esc_html__( 'Border', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} .raven-instagram-feed-meta',
			]
		);

		$this->add_responsive_control(
			'meta_radius',
			[
				'label' => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .raven-instagram-feed-meta' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'meta_shadow',
				'selector' => '{{WRAPPER}} .raven-instagram-feed-meta',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$posts    = $this->get_posts( $settings );

		$this->add_render_attribute( 'feed', 'class', [
			'raven-instagram-feed',
			'raven-instagram-feed--' . $settings['layout_type'],
			'yes' === $settings['show_on_hover'] ? 'raven-instagram-feed--hover' : '',
			'raven-instagram-feed--content-' . $settings['content_position'],
		] );

		if ( is_wp_error( $posts ) ) {
			printf( '<div class="raven-instagram-feed-notice">%s</div>', esc_html( $posts->get_error_message() ) );
			return;
		}

		if ( empty( $posts ) ) {
			printf( '<div class="raven-instagram-feed-notice">%s</div>', esc_html__( 'No Instagram posts found.', 'jupiterx-core' ) );
			return;
		}

		?>
		<div <?php $this->print_render_attribute_string( 'feed' ); ?>>
			<?php foreach ( $posts as $post ) : ?>
				<?php $this->render_post( $post, $settings ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	protected function render_post( $post, $settings ) {
		$image_url = $this->get_post_image_url( $post );

		if ( empty( $image_url ) ) {
			return;
		}

		$caption = ! empty( $post['caption'] ) ? wp_strip_all_tags( $post['caption'] ) : '';
		$caption = $this->trim_caption( $caption, absint( $settings['post_caption_length'] ) );
		$link    = $this->get_post_link( $post, $settings, $image_url );
		$content = $this->get_post_content( $post, $caption, $settings );
		$link_attrs = $this->get_link_attributes( $post, $settings, $link );
		?>
		<div class="raven-instagram-feed-item">
			<div class="raven-instagram-feed-inner">
				<?php if ( ! empty( $link ) ) : ?>
					<a <?php echo $link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php endif; ?>

				<div class="raven-instagram-feed-image">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $caption ); ?>" loading="lazy">
				</div>
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php if ( ! empty( $link ) ) : ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	protected function get_post_content( $post, $caption, $settings ) {
		ob_start();
		?>
		<div class="raven-instagram-feed-content">
			<?php if ( 'yes' === $settings['post_caption'] && ! empty( $caption ) ) : ?>
				<div class="raven-instagram-feed-caption"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>

			<?php if ( 'hashtag' === $settings['endpoint'] && ( 'yes' === $settings['post_likes_count'] || 'yes' === $settings['post_comments_count'] ) ) : ?>
				<div class="raven-instagram-feed-meta">
					<?php if ( 'yes' === $settings['post_likes_count'] && isset( $post['like_count'] ) ) : ?>
						<span class="raven-instagram-feed-meta-item">
							<span class="raven-instagram-feed-meta-icon"><?php Icons_Manager::render_icon( $settings['likes_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>
							<span class="raven-instagram-feed-meta-label"><?php echo esc_html( $post['like_count'] ); ?></span>
						</span>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['post_comments_count'] && isset( $post['comments_count'] ) ) : ?>
						<span class="raven-instagram-feed-meta-item">
							<span class="raven-instagram-feed-meta-icon"><?php Icons_Manager::render_icon( $settings['comments_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>
							<span class="raven-instagram-feed-meta-label"><?php echo esc_html( $post['comments_count'] ); ?></span>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function get_post_link( $post, $settings, $image_url ) {
		$link_type = ! empty( $settings['post_link_type'] ) ? $settings['post_link_type'] : 'post';

		if ( empty( $settings['post_link'] ) || 'yes' !== $settings['post_link'] || 'none' === $link_type ) {
			return '';
		}

		if ( 'lightbox' === $link_type ) {
			return $image_url;
		}

		return ! empty( $post['permalink'] ) ? $post['permalink'] : '';
	}

	protected function get_link_attributes( $post, $settings, $link ) {
		$link_type = ! empty( $settings['post_link_type'] ) ? $settings['post_link_type'] : 'post';
		$attrs = [
			'class' => 'raven-instagram-feed-link',
			'href' => esc_url( $link ),
			'rel' => 'nofollow noopener',
		];

		if ( 'lightbox' === $link_type ) {
			$attrs['data-elementor-open-lightbox'] = 'yes';
			$attrs['data-elementor-lightbox-slideshow'] = 'raven-instagram-feed-' . $this->get_id();

			if ( ! empty( $post['caption'] ) ) {
				$attrs['data-elementor-lightbox-title'] = esc_attr( wp_strip_all_tags( $post['caption'] ) );
			}
		} elseif ( ! empty( $settings['post_link_target'] ) && 'yes' === $settings['post_link_target'] ) {
			$attrs['target'] = '_blank';
		}

		$output = '';

		foreach ( $attrs as $name => $value ) {
			$output .= sprintf( ' %1$s="%2$s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return trim( $output );
	}

	protected function get_posts( $settings ) {
		if ( empty( $settings['access_token'] ) ) {
			return new \WP_Error( 'missing_token', esc_html__( 'Please add an Instagram access token.', 'jupiterx-core' ) );
		}

		$cache_key = $this->get_cache_key( $settings );

		if ( 'none' !== $settings['cache_timeout'] ) {
			$cached = get_transient( $cache_key );

			if ( false !== $cached ) {
				return $cached;
			}
		}

		$response = 'hashtag' === $settings['endpoint'] ? $this->request_hashtag_posts( $settings ) : $this->request_self_posts( $settings );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$posts = array_slice( $response, 0, absint( $settings['posts_counter'] ) );

		if ( 'none' !== $settings['cache_timeout'] ) {
			set_transient( $cache_key, $posts, $this->get_cache_duration( $settings['cache_timeout'] ) );
		}

		return $posts;
	}

	protected function request_self_posts( $settings ) {
		$url = add_query_arg(
			[
				'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,username',
				'limit' => min( 50, absint( $settings['posts_counter'] ) ),
				'access_token' => $settings['access_token'],
			],
			'https://graph.instagram.com/me/media'
		);

		return $this->request_instagram_data( $url );
	}

	protected function request_hashtag_posts( $settings ) {
		if ( empty( $settings['business_account_id'] ) || empty( $settings['hashtag'] ) ) {
			return new \WP_Error( 'missing_hashtag_config', esc_html__( 'Hashtag feeds require a hashtag and Instagram Business Account ID.', 'jupiterx-core' ) );
		}

		$search_url = add_query_arg(
			[
				'user_id' => $settings['business_account_id'],
				'q' => ltrim( $settings['hashtag'], '#' ),
				'access_token' => $settings['access_token'],
			],
			'https://graph.facebook.com/v19.0/ig_hashtag_search'
		);

		$search_response = $this->request_instagram_data( $search_url );

		if ( is_wp_error( $search_response ) ) {
			return $search_response;
		}

		if ( empty( $search_response[0]['id'] ) ) {
			return [];
		}

		$endpoint = 'top_media' === $settings['order_by'] ? 'top_media' : 'recent_media';
		$media_url = add_query_arg(
			[
				'user_id' => $settings['business_account_id'],
				'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,like_count,comments_count,timestamp',
				'limit' => min( 50, absint( $settings['posts_counter'] ) ),
				'access_token' => $settings['access_token'],
			],
			'https://graph.facebook.com/v19.0/' . rawurlencode( $search_response[0]['id'] ) . '/' . $endpoint
		);

		return $this->request_instagram_data( $media_url );
	}

	protected function request_instagram_data( $url ) {
		$response = wp_remote_get(
			$url,
			[
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! empty( $body['error']['message'] ) ) {
			return new \WP_Error( 'instagram_error', $body['error']['message'] );
		}

		if ( empty( $body['data'] ) || ! is_array( $body['data'] ) ) {
			return [];
		}

		return $body['data'];
	}

	protected function get_post_image_url( $post ) {
		if ( ! empty( $post['thumbnail_url'] ) ) {
			return $post['thumbnail_url'];
		}

		if ( ! empty( $post['media_url'] ) ) {
			return $post['media_url'];
		}

		return '';
	}

	protected function trim_caption( $caption, $length ) {
		if ( empty( $length ) || strlen( $caption ) <= $length ) {
			return $caption;
		}

		return rtrim( substr( $caption, 0, $length ) ) . '...';
	}

	protected function get_cache_key( $settings ) {
		return 'raven_instagram_feed_' . md5( wp_json_encode( [
			$settings['endpoint'],
			$settings['access_token'],
			$settings['business_account_id'],
			$settings['hashtag'],
			$settings['order_by'],
			$settings['posts_counter'],
		] ) );
	}

	protected function get_cache_duration( $timeout ) {
		$durations = [
			'minute' => MINUTE_IN_SECONDS,
			'hour' => HOUR_IN_SECONDS,
			'day' => DAY_IN_SECONDS,
			'week' => WEEK_IN_SECONDS,
		];

		return isset( $durations[ $timeout ] ) ? $durations[ $timeout ] : HOUR_IN_SECONDS;
	}
}

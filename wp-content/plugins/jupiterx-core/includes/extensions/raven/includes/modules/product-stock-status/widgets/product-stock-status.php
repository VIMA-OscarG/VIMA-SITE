<?php

namespace JupiterX_Core\Raven\Modules\Product_Stock_Status\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use JupiterX_Core\Raven\Base\Base_Widget;
use JupiterX_Core\Raven\Plugin;
use JupiterX_Core\Raven\Utils;

/**
 * WooCommerce product stock badge for single product templates.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Product_Stock_Status extends Base_Widget {

	public static function is_active() {
		return function_exists( 'WC' ) && Plugin::is_active( 'product-stock-status' );
	}

	public function get_name() {
		return 'raven-product-stock-status';
	}

	public function get_title() {
		return esc_html__( 'Stock Status', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-product-stock-status';
	}

	public function get_categories() {
		return [ 'jupiterx-core-raven-woo-elements' ];
	}

	public function get_keywords() {
		return array_merge(
			parent::get_keywords(),
			[
				'woocommerce',
				'product',
				'stock',
				'availability',
				'in stock',
				'out of stock',
			]
		);
	}

	protected function register_controls() {
		$this->register_content_section();
		$this->register_icon_section();

		$this->register_style_badge_general();
		$this->register_style_in_stock();
		$this->register_style_out_of_stock();
	}

	/**
	 * Content: visibility & labels.
	 */
	private function register_content_section() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Stock Status', 'jupiterx-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'visibility_mode',
			[
				'label'   => esc_html__( 'Display', 'jupiterx-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'both',
				'options' => [
					'both'                => esc_html__( 'Always (switch by product stock)', 'jupiterx-core' ),
					'in_stock_only'       => esc_html__( 'Only when in stock', 'jupiterx-core' ),
					'out_of_stock_only'   => esc_html__( 'Only when out of stock', 'jupiterx-core' ),
				],
			]
		);

		$this->add_control(
			'editor_preview_state',
			[
				'label'              => esc_html__( 'Editor preview', 'jupiterx-core' ),
				'description'        => esc_html__( 'Pick which badge to show in the editor when “Always” is selected. The live page still switches by real stock.', 'jupiterx-core' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'in_stock',
				'options'            => [
					'in_stock'     => esc_html__( 'In stock badge', 'jupiterx-core' ),
					'out_of_stock' => esc_html__( 'Out of stock badge', 'jupiterx-core' ),
				],
				'render_type'        => 'template',
				'frontend_available' => true,
				'condition'          => [
					'visibility_mode' => 'both',
				],
			]
		);

		$this->add_control(
			'label_in_stock',
			[
				'label'       => esc_html__( 'In stock label', 'jupiterx-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'In stock', 'jupiterx-core' ),
				'dynamic'     => [
					'active' => true,
				],
				'label_block' => true,
				'condition'   => [
					'visibility_mode' => [ 'both', 'in_stock_only' ],
				],
			]
		);

		$this->add_control(
			'label_out_of_stock',
			[
				'label'       => esc_html__( 'Out of stock label', 'jupiterx-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Out of stock', 'jupiterx-core' ),
				'dynamic'     => [
					'active' => true,
				],
				'label_block' => true,
				'condition'   => [
					'visibility_mode' => [ 'both', 'out_of_stock_only' ],
				],
			]
		);

		$this->add_control(
			'use_wc_fallback_text',
			[
				'label'       => esc_html__( 'Use WooCommerce availability text when available', 'jupiterx-core' ),
				'description' => esc_html__( 'Falls back to the labels above when WooCommerce returns no text.', 'jupiterx-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'jupiterx-core' ),
				'label_off'   => esc_html__( 'No', 'jupiterx-core' ),
				'return_value' => 'yes',
				'default'     => '',
			]
		);

		$this->add_control(
			'badge_html_tag',
			[
				'label'   => esc_html__( 'HTML tag', 'jupiterx-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'div',
				'options' => [
					'div' => 'div',
					'span'=> 'span',
					'p'   => 'p',
				],
			]
		);

		$this->add_responsive_control(
			'badge_align',
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
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .raven-product-stock-status__inner' => 'justify-content: {{VALUE}};',
				],
				'toggle'    => true,
				// Map choose values to flex justify-content.
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Optional icons.
	 */
	private function register_icon_section() {
		$this->start_controls_section(
			'section_icon',
			[
				'label' => esc_html__( 'Icon', 'jupiterx-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label'        => esc_html__( 'Show icon', 'jupiterx-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jupiterx-core' ),
				'label_off'    => esc_html__( 'No', 'jupiterx-core' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'icon_in_stock',
			[
				'label'       => esc_html__( 'Icon (in stock)', 'jupiterx-core' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'default'     => [
					'value'   => 'fas fa-check-circle',
					'library' => 'fa-solid',
				],
				'condition'   => [
					'show_icon' => 'yes',
					'visibility_mode' => [ 'both', 'in_stock_only' ],
				],
			]
		);

		$this->add_control(
			'icon_out_of_stock',
			[
				'label'       => esc_html__( 'Icon (out of stock)', 'jupiterx-core' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'default'     => [
					'value'   => 'fas fa-times-circle',
					'library' => 'fa-solid',
				],
				'condition'   => [
					'show_icon' => 'yes',
					'visibility_mode' => [ 'both', 'out_of_stock_only' ],
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Icon size', 'jupiterx-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 8, 'max' => 80 ],
					'em' => [ 'min' => 0.2, 'max' => 4, 'step' => 0.05 ],
				],
				'default'    => [
					'size' => 18,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .raven-product-stock-status__icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .raven-product-stock-status__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_icon' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_gap',
			[
				'label'      => esc_html__( 'Space between icon and text', 'jupiterx-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'max' => 50 ],
				],
				'default'    => [
					'size' => 8,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .raven-product-stock-status__badge' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_position',
			[
				'label'     => esc_html__( 'Icon position', 'jupiterx-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'before' => [
						'title' => esc_html__( 'Before', 'jupiterx-core' ),
						'icon'  => 'eicon-h-align-left',
					],
					'after'  => [
						'title' => esc_html__( 'After', 'jupiterx-core' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'      => 'before',
				'toggle'       => false,
				'render_type'  => 'ui',
				'prefix_class' => 'elementor-raven-stock-status-icon-pos--',
				'condition'    => [
					'show_icon' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_badge_general() {
		$this->start_controls_section(
			'section_style_general',
			[
				'label' => esc_html__( 'Layout', 'jupiterx-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'badge_width',
			[
				'label'      => esc_html__( 'Badge width', 'jupiterx-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range'      => [
					'px' => [ 'max' => 800 ],
					'%'  => [ 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .raven-product-stock-status__badge' => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style controls per stock state (typography, colors, badge chrome).
	 *
	 * @param string $suffix             Section id suffix.
	 * @param string $label              Section heading.
	 * @param string $selector           Root selector fragment for one badge state.
	 * @param array  $section_condition  Visibility conditions for the section tab.
	 * @param array  $d                  Defaults: text_color, icon_color, bg, border_color.
	 */
	private function register_stock_state_style_controls( $suffix, $label, $selector, array $section_condition, array $d ) {
		$this->start_controls_section(
			'section_style_' . $suffix,
			[
				'label'     => $label,
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => $section_condition,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography_' . $suffix,
				'label'    => esc_html__( 'Typography', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} ' . $selector . ' .raven-product-stock-status__label',
				'fields_options' => [
					'typography'  => [
						'default' => 'custom',
					],
					'font_size'   => [
						'default' => [
							'size' => 13,
							'unit' => 'px',
						],
					],
					'font_weight' => [
						'default' => '600',
					],
				],
			]
		);

		$this->add_control(
			'text_color_' . $suffix,
			[
				'label'     => esc_html__( 'Text color', 'jupiterx-core' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => $d['text_color'],
				'selectors' => [
					'{{WRAPPER}} ' . $selector . ' .raven-product-stock-status__label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'background_' . $suffix,
				'label'    => esc_html__( 'Background', 'jupiterx-core' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} ' . $selector,
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color'      => [
						'default' => $d['bg'],
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'border_' . $suffix,
				'label'     => esc_html__( 'Border', 'jupiterx-core' ),
				'selector'  => '{{WRAPPER}} ' . $selector,
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'    => '1',
							'right'  => '1',
							'bottom' => '1',
							'left'   => '1',
							'unit'   => 'px',
						],
					],
					'color'  => [
						'default' => $d['border_color'],
					],
				],
			]
		);

		$this->add_responsive_control(
			'border_radius_' . $suffix,
			[
				'label'      => esc_html__( 'Border radius', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '6',
					'right'  => '6',
					'bottom' => '6',
					'left'   => '6',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_padding_' . $suffix,
			[
				'label'      => esc_html__( 'Padding', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'    => '7',
					'right'  => '14',
					'bottom' => '7',
					'left'   => '14',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow_' . $suffix,
				'label'    => esc_html__( 'Box shadow', 'jupiterx-core' ),
				'selector' => '{{WRAPPER}} ' . $selector,
			]
		);

		$this->add_control(
			'icon_color_' . $suffix,
			[
				'label'     => esc_html__( 'Icon color', 'jupiterx-core' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => $d['icon_color'],
				'selectors' => [
					'{{WRAPPER}} ' . $selector . ' .raven-product-stock-status__icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} ' . $selector . ' .raven-product-stock-status__icon svg' => 'fill: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	private function register_style_in_stock() {
		$this->register_stock_state_style_controls(
			'in_stock',
			esc_html__( 'In stock', 'jupiterx-core' ),
			'.raven-product-stock-status__badge--in-stock',
			[
				'visibility_mode' => [ 'both', 'in_stock_only' ],
			],
			[
				'text_color'    => '#0f5132',
				'icon_color'    => '#198754',
				'bg'            => '#d1e7dd',
				'border_color'  => '#a3cfbb',
			]
		);
	}

	private function register_style_out_of_stock() {
		$this->register_stock_state_style_controls(
			'out_of_stock',
			esc_html__( 'Out of stock', 'jupiterx-core' ),
			'.raven-product-stock-status__badge--out-of-stock',
			[
				'visibility_mode' => [ 'both', 'out_of_stock_only' ],
			],
			[
				'text_color'    => '#842029',
				'icon_color'    => '#dc3545',
				'bg'            => '#f8d7da',
				'border_color'  => '#f1aeb5',
			]
		);
	}

	/**
	 * Resolve label text.
	 *
	 * @param \WC_Product $product Product.
	 * @param bool $in_stock In stock flag.
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_stock_label_text( $product, $in_stock, $settings ) {
		if ( 'yes' === ( $settings['use_wc_fallback_text'] ?? '' ) ) {
			$avail = $product->get_availability();
			if ( ! empty( $avail['availability'] ) ) {
				return wp_strip_all_tags( wc_clean( $avail['availability'] ) );
			}
		}

		if ( $in_stock ) {
			return isset( $settings['label_in_stock'] ) ? $settings['label_in_stock'] : esc_html__( 'In stock', 'jupiterx-core' );
		}

		return isset( $settings['label_out_of_stock'] ) ? $settings['label_out_of_stock'] : esc_html__( 'Out of stock', 'jupiterx-core' );
	}

	private function print_stock_badge( $tag, $in_stock, array $settings, $show_icon, $product ) {
		$state_class = $in_stock ? 'raven-product-stock-status__badge--in-stock' : 'raven-product-stock-status__badge--out-of-stock';
		$icon_key    = $in_stock ? 'icon_in_stock' : 'icon_out_of_stock';

		if ( $product instanceof \WC_Product ) {
			$label_html = $this->get_stock_label_text( $product, $in_stock, $settings );
		} else {
			$label_html = $in_stock
				? ( $settings['label_in_stock'] ?? esc_html__( 'In stock', 'jupiterx-core' ) )
				: ( $settings['label_out_of_stock'] ?? esc_html__( 'Out of stock', 'jupiterx-core' ) );
		}

		$class_string = 'raven-product-stock-status__badge raven-stock-status-badge ' . $state_class;

		printf(
			'<%1$s class="%2$s">',
			esc_attr( $tag ),
			esc_attr( $class_string )
		);

		if ( $show_icon ) {
			$icon_settings = isset( $settings[ $icon_key ] ) ? $settings[ $icon_key ] : [];
			if ( ! empty( $icon_settings['value'] ) ) {
				echo '<span class="raven-product-stock-status__icon" aria-hidden="true">';
				Icons_Manager::render_icon( $icon_settings, [ 'aria-hidden' => 'true' ] );
				echo '</span>';
			}
		}

		echo '<span class="raven-product-stock-status__label">' . esc_html( $label_html ) . '</span>';
		echo '</' . esc_attr( $tag ) . '>';
	}

	/**
	 * Whether the widget is rendering inside the Elementor editor canvas.
	 */
	private function is_elementor_editor_preview() {
		return \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	protected function render() {
		$settings      = $this->get_settings_for_display();
		$visibility    = isset( $settings['visibility_mode'] ) ? $settings['visibility_mode'] : 'both';
		$show_icon     = 'yes' === ( $settings['show_icon'] ?? '' );
		$tag           = isset( $settings['badge_html_tag'] ) ? $settings['badge_html_tag'] : 'div';
		$allowed_tags  = [ 'div', 'span', 'p' ];

		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'div';
		}

		Utils::get_product();

		global $product;

		$wc_product = ( ! empty( $product ) && is_a( $product, \WC_Product::class ) ) ? $product : null;

		// Editor + "Always": output both badges so each style tab has a live target (visibility handled by `.elementor-editor-active` CSS + prefix class).
		if ( $this->is_elementor_editor_preview() && 'both' === $visibility ) {
			$preview_state = isset( $settings['editor_preview_state'] ) ? $settings['editor_preview_state'] : 'in_stock';
			$this->add_render_attribute(
				'wrapper',
				[
					'class'                     => [ 'woocommerce', 'raven-product-stock-status', 'raven-product-stock-status--editor-dual' ],
					'role'                      => 'note',
					'aria-label'                => esc_attr__( 'Stock status preview', 'jupiterx-core' ),
					'data-raven-editor-preview' => esc_attr( $preview_state ),
				]
			);

			echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="raven-product-stock-status__inner">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->print_stock_badge( $tag, true, $settings, $show_icon, $wc_product );
			$this->print_stock_badge( $tag, false, $settings, $show_icon, $wc_product );
			echo '</div></div>';
			return;
		}

		if ( empty( $wc_product ) ) {
			return;
		}

		$in_stock = $wc_product->is_in_stock();

		if ( 'in_stock_only' === $visibility && ! $in_stock ) {
			return;
		}

		if ( 'out_of_stock_only' === $visibility && $in_stock ) {
			return;
		}

		$label_html = $this->get_stock_label_text( $wc_product, $in_stock, $settings );

		$this->add_render_attribute(
			'wrapper',
			[
				'class'       => [ 'woocommerce', 'raven-product-stock-status' ],
				'role'        => 'status',
				'aria-live'   => 'polite',
				'aria-label'  => wp_strip_all_tags( $label_html ),
			]
		);

		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="raven-product-stock-status__inner">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$this->print_stock_badge( $tag, $in_stock, $settings, $show_icon, $wc_product );

		echo '</div></div>';
	}

	protected function content_template() {
		?>
		<#
		var mode = settings.visibility_mode || 'both';
		var preview = settings.editor_preview_state || 'in_stock';
		var defIn = <?php echo wp_json_encode( esc_html__( 'In stock', 'jupiterx-core' ) ); ?>;
		var defOut = <?php echo wp_json_encode( esc_html__( 'Out of stock', 'jupiterx-core' ) ); ?>;
		var rootExtraClass = ('both' === mode) ? ' raven-product-stock-status--editor-dual' : '';
		var previewAttr = ('both' === mode) ? ' data-raven-editor-preview="' + preview + '"' : '';

		function badgeHtml( label, stateClass, iconSetting ) {
			var allowedTags = ['div','span','p'];
			var tag = ( settings.badge_html_tag && _.contains(allowedTags, settings.badge_html_tag ) ) ? settings.badge_html_tag : 'div';
			var html = '<' + tag + ' class="raven-product-stock-status__badge ' + stateClass + '">';
			if ( settings.show_icon === 'yes' && iconSetting && iconSetting.library && iconSetting.value ) {
				var iconObj = elementor.helpers.renderIcon( view, iconSetting, { 'aria-hidden': true }, 'i', 'object' );
				if ( iconObj && iconObj.rendered && iconObj.value ) {
					html += '<span class="raven-product-stock-status__icon">' + iconObj.value + '</span>';
				}
			}
			html += '<span class="raven-product-stock-status__label">' + _.escape( label ) + '</span></' + tag + '>';
			return html;
		}
		#>
		<div class="woocommerce raven-product-stock-status<# print( rootExtraClass ); #>" role="status"<# print( previewAttr ); #>>
			<div class="raven-product-stock-status__inner">
				<# if ( 'both' === mode ) {
					print( badgeHtml( settings.label_in_stock || defIn, 'raven-product-stock-status__badge--in-stock', settings.icon_in_stock ) );
					print( badgeHtml( settings.label_out_of_stock || defOut, 'raven-product-stock-status__badge--out-of-stock', settings.icon_out_of_stock ) );
				} else if ( 'in_stock_only' === mode ) {
					print( badgeHtml( settings.label_in_stock || defIn, 'raven-product-stock-status__badge--in-stock', settings.icon_in_stock ) );
				} else {
					print( badgeHtml( settings.label_out_of_stock || defOut, 'raven-product-stock-status__badge--out-of-stock', settings.icon_out_of_stock ) );
				} #>
			</div>
		</div>
		<?php
	}
}

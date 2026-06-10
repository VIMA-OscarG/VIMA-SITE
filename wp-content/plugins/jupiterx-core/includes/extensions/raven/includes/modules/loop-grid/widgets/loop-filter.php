<?php
/**
 * Loop Filter widget.
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
use JupiterX_Core\Raven\Modules\Loop_Grid\Module as Loop_Grid_Module;

class Loop_Filter extends Base_Widget {

	public function get_name() {
		return 'raven-loop-filter';
	}

	public function get_title() {
		return esc_html__( 'Loop Filter', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-loop-filter';
	}

	public function get_keywords() {
		return array_merge(
			parent::get_keywords(),
			[
				'loop',
				'filter',
				'taxonomy',
				'terms',
				'category',
			]
		);
	}

	protected function register_controls() {
		$this->register_filter_controls();
		$this->register_filter_style_controls();
	}

	protected function register_filter_controls() {
		$this->start_controls_section(
			'section_filter',
			[
				'label' => esc_html__( 'Layout', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'jupiterx-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'category',
				'options' => $this->get_taxonomy_options(),
			]
		);

		$this->add_control(
			'target_selector',
			[
				'label'              => esc_html__( 'Target Loop Grid selector', 'jupiterx-core' ),
				'type'               => Controls_Manager::TEXT,
				'description'        => esc_html__( 'Optional. Enter the Loop Grid element ID from the Elementor navigator (e.g. abc123), or a CSS selector such as .elementor-element-abc123. Leave empty to refresh the nearest Loop Grid.', 'jupiterx-core' ),
				'placeholder'        => '.elementor-element-abc123',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'all_label',
			[
				'label'   => esc_html__( 'All label', 'jupiterx-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'All', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'hide_empty',
			[
				'label'        => esc_html__( 'Hide empty terms', 'jupiterx-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_hierarchy',
			[
				'label'        => esc_html__( 'Show hierarchy', 'jupiterx-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Indent child terms for hierarchical taxonomies.', 'jupiterx-core' ),
				'condition'    => [
					'direction' => 'vertical',
				],
			]
		);

		$this->add_responsive_control(
			'direction',
			[
				'label'                => esc_html__( 'Direction', 'jupiterx-core' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'horizontal',
				'tablet_default'       => 'horizontal',
				'mobile_default'       => 'horizontal',
				'options'              => [
					'horizontal' => esc_html__( 'Horizontal', 'jupiterx-core' ),
					'vertical'   => esc_html__( 'Vertical', 'jupiterx-core' ),
				],
				'selectors_dictionary' => [
					'horizontal' => 'flex-direction: row;',
					'vertical'   => 'flex-direction: column;',
				],
				'selectors'            => [
					'{{WRAPPER}} .jupiterx-loop-filter' => '{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label'                => esc_html__( 'Alignment', 'jupiterx-core' ),
				'type'                 => Controls_Manager::CHOOSE,
				'default'              => 'flex-start',
				'options'              => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'jupiterx-core' ),
						'icon'  => 'eicon-align-start-h',
					],
					'center'     => [
						'title' => esc_html__( 'Center', 'jupiterx-core' ),
						'icon'  => 'eicon-align-center-h',
					],
					'flex-end'   => [
						'title' => esc_html__( 'End', 'jupiterx-core' ),
						'icon'  => 'eicon-align-end-h',
					],
					'stretch'    => [
						'title' => esc_html__( 'Stretch', 'jupiterx-core' ),
						'icon'  => 'eicon-align-stretch-h',
					],
				],
				'selectors_dictionary' => [
					'flex-start' => 'justify-content: flex-start; align-items: flex-start; --jx-loop-filter-item-flex: 0 0 auto; --jx-loop-filter-text-align: left;',
					'center'     => 'justify-content: center; align-items: center; --jx-loop-filter-item-flex: 0 0 auto; --jx-loop-filter-text-align: center;',
					'flex-end'   => 'justify-content: flex-end; align-items: flex-end; --jx-loop-filter-item-flex: 0 0 auto; --jx-loop-filter-text-align: right;',
					'stretch'    => 'justify-content: stretch; align-items: stretch; --jx-loop-filter-item-flex: 1 1 auto; --jx-loop-filter-text-align: left;',
				],
				'selectors'            => [
					'{{WRAPPER}} .jupiterx-loop-filter' => '{{VALUE}}',
				],
				'conditions'           => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_hierarchy',
							'operator' => '!==',
							'value'    => 'yes',
						],
						[
							'name'     => 'direction',
							'operator' => '!==',
							'value'    => 'vertical',
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_filter_style_controls() {
		$this->start_controls_section(
			'section_filter_style',
			[
				'label' => esc_html__( 'Items', 'jupiterx-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label'      => esc_html__( 'Gap', 'jupiterx-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-filter' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-filter__item' => '--jx-loop-filter-padding-top: {{TOP}}{{UNIT}}; --jx-loop-filter-padding-right: {{RIGHT}}{{UNIT}}; --jx-loop-filter-padding-bottom: {{BOTTOM}}{{UNIT}}; --jx-loop-filter-padding-left: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'item_typography',
				'selector' => '{{WRAPPER}} .jupiterx-loop-filter__item',
			]
		);

		$this->start_controls_tabs( 'item_style_tabs' );

		$this->start_controls_tab(
			'item_style_normal',
			[
				'label' => esc_html__( 'Normal', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'item_color',
			[
				'label'     => esc_html__( 'Text Color', 'jupiterx-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-filter__item' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_background',
				'selector' => '{{WRAPPER}} .jupiterx-loop-filter__item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'item_style_active',
			[
				'label' => esc_html__( 'Active', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'item_active_color',
			[
				'label'     => esc_html__( 'Text Color', 'jupiterx-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-filter__item.is-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_active_background',
				'selector' => '{{WRAPPER}} .jupiterx-loop-filter__item.is-active',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .jupiterx-loop-filter__item',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-filter__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_taxonomy_options() {
		$options    = [];
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
	}

	/**
	 * Order terms parent-first for hierarchical filter lists.
	 *
	 * @param \WP_Term[] $terms    Terms to order.
	 * @param string     $taxonomy Taxonomy slug.
	 * @return \WP_Term[]
	 */
	protected function order_terms_hierarchically( array $terms, $taxonomy ) {
		$by_parent = [];

		foreach ( $terms as $term ) {
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}

			$by_parent[ (int) $term->parent ][] = $term;
		}

		$ordered = [];
		$this->append_child_terms( $by_parent, 0, $ordered );

		return ! empty( $ordered ) ? $ordered : $terms;
	}

	/**
	 * @param array<int, \WP_Term[]> $by_parent Terms grouped by parent ID.
	 * @param int                    $parent_id Parent term ID.
	 * @param \WP_Term[]             $ordered   Ordered output list.
	 */
	protected function append_child_terms( array $by_parent, $parent_id, array &$ordered ) {
		if ( empty( $by_parent[ $parent_id ] ) ) {
			return;
		}

		foreach ( $by_parent[ $parent_id ] as $term ) {
			$ordered[] = $term;
			$this->append_child_terms( $by_parent, (int) $term->term_id, $ordered );
		}
	}

	/**
	 * Resolve the target Loop Grid widget element ID from saved selector settings.
	 *
	 * @param string $target_selector Raw selector or element ID from widget settings.
	 * @return string
	 */
	protected function parse_target_widget_id( $target_selector ) {
		$target_selector = trim( (string) $target_selector );

		if ( '' === $target_selector ) {
			return '';
		}

		if ( preg_match( '/elementor-element-([a-z0-9]+)/i', $target_selector, $matches ) ) {
			return sanitize_key( $matches[1] );
		}

		if ( preg_match( '/\[data-id=["\']([a-z0-9]+)["\']\]/i', $target_selector, $matches ) ) {
			return sanitize_key( $matches[1] );
		}

		if ( preg_match( '/^[a-z0-9]+$/i', $target_selector ) ) {
			return sanitize_key( $target_selector );
		}

		return '';
	}

	protected function render() {
		if ( Loop_Grid_Module::should_suppress_nested_loop_widgets() ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$taxonomy = ! empty( $settings['taxonomy'] ) ? sanitize_key( $settings['taxonomy'] ) : 'category';
		$target_selector = $settings['target_selector'] ?? '';
		$target_widget_id = $this->parse_target_widget_id( $target_selector );
		$direction = ! empty( $settings['direction'] ) ? $settings['direction'] : 'horizontal';
		$has_hierarchy = 'vertical' === $direction && ! empty( $settings['show_hierarchy'] ) && 'yes' === $settings['show_hierarchy'] && is_taxonomy_hierarchical( $taxonomy );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => ! empty( $settings['hide_empty'] ) && 'yes' === $settings['hide_empty'],
			]
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				return;
			}

			$terms = [
				(object) [
					'term_id' => 1,
					'name'    => esc_html__( 'Category One', 'jupiterx-core' ),
				],
				(object) [
					'term_id' => 2,
					'name'    => esc_html__( 'Category Two', 'jupiterx-core' ),
				],
				(object) [
					'term_id' => 3,
					'name'    => esc_html__( 'Category Three', 'jupiterx-core' ),
				],
			];
		}

		if ( $has_hierarchy ) {
			$terms = $this->order_terms_hierarchically( $terms, $taxonomy );
		}

		?>
		<div
			class="<?php echo esc_attr( $has_hierarchy ? 'jupiterx-loop-filter jupiterx-loop-filter--hierarchy' : 'jupiterx-loop-filter' ); ?>"
			data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
			data-target-selector="<?php echo esc_attr( $target_selector ); ?>"
			data-target-widget-id="<?php echo esc_attr( $target_widget_id ); ?>"
		>
			<?php if ( ! empty( $settings['all_label'] ) ) : ?>
				<button type="button" class="jupiterx-loop-filter__item is-active" data-term-id="0">
					<?php echo esc_html( $settings['all_label'] ); ?>
				</button>
			<?php endif; ?>

			<?php
			foreach ( $terms as $term ) {
				$this->render_term_button( $term, $taxonomy, $settings, $has_hierarchy );
			}
			?>
		</div>
		<?php
	}

	protected function render_term_button( $term, $taxonomy, array $settings, $has_hierarchy = false ) {
		$depth = 0;

		if ( $has_hierarchy ) {
			$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
			$depth     = count( $ancestors );
		}

		?>
		<button
			type="button"
			class="jupiterx-loop-filter__item"
			data-term-id="<?php echo esc_attr( (string) $term->term_id ); ?>"
			data-depth="<?php echo esc_attr( (string) $depth ); ?>"
			style="--jx-loop-filter-depth: <?php echo esc_attr( (string) $depth ); ?>;"
		>
			<?php echo esc_html( $term->name ); ?>
		</button>
		<?php
	}
}

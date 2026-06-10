<?php
/**
 * Loop Carousel widget.
 *
 * @package JupiterX_Core\Raven
 * @since NEXT
 */

namespace JupiterX_Core\Raven\Modules\Loop_Grid\Widgets;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use JupiterX_Core\Raven\Controls\Query as Control_Query;
use JupiterX_Core\Raven\Core\Document_Types\Type\Jupiterx_Loop_Item_Document;
use JupiterX_Core\Raven\Modules\Loop_Grid\Module as Loop_Grid_Module;

class Loop_Carousel extends Loop_Grid {

	public function get_name() {
		return 'raven-loop-carousel';
	}

	public function get_title() {
		return esc_html__( 'Loop Carousel', 'jupiterx-core' );
	}

	public function get_icon() {
		return 'raven-element-icon raven-element-icon-loop-carousel';
	}

	public function get_keywords() {
		return array_merge(
			parent::get_keywords(),
			[
				'carousel',
				'slider',
				'slides',
				'swiper',
			]
		);
	}

	public function get_style_depends() {
		return [ 'e-swiper', 'swiper', 'jupiterx-core-raven-frontend' ];
	}

	protected function register_controls() {
		$this->register_carousel_layout_controls();
		$this->register_query_controls();
		$this->register_query_advanced_controls();
		$this->register_sort_controls();
		$this->register_carousel_controls();
		$this->register_loop_item_style_controls();
		$this->register_carousel_style_controls();
	}

	protected function register_carousel_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'jupiterx-core' ),
			]
		);

		$this->add_control(
			'loop_template_id',
			[
				'label'       => esc_html__( 'Loop item template', 'jupiterx-core' ),
				'description' => esc_html__( 'Create a JupiterX Loop Item template, then select it here.', 'jupiterx-core' ),
				'type'        => 'raven_query',
				'label_block' => true,
				'query'       => [
					'source'        => Control_Query::QUERY_SOURCE_TEMPLATE,
					'template_types' => [ Jupiterx_Loop_Item_Document::SLUG ],
				],
			]
		);

		$this->add_control(
			'create_loop_template',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => $this->get_create_loop_template_button_html(),
				'content_classes' => 'jupiterx-loop-template-create-control',
			]
		);

		$this->add_responsive_control(
			'slides_per_view',
			[
				'label'              => esc_html__( 'Slides per view', 'jupiterx-core' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 3,
				'tablet_default'     => 2,
				'mobile_default'     => 1,
				'min'                => 1,
				'max'                => 12,
				'frontend_available' => true,
				'render_type'        => 'template',
				'condition'          => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			[
				'label'              => esc_html__( 'Slides to scroll', 'jupiterx-core' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 1,
				'min'                => 1,
				'max'                => 12,
				'frontend_available' => true,
				'condition'          => [
					'loop_template_id!' => '',
					'effect'            => 'slide',
				],
			]
		);

		$this->add_responsive_control(
			'space_between',
			[
				'label'              => esc_html__( 'Space between', 'jupiterx-core' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'px' ],
				'range'              => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'            => [
					'size' => 24,
					'unit' => 'px',
				],
				'frontend_available' => true,
				'condition'          => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_carousel_controls() {
		$this->start_controls_section(
			'section_carousel',
			[
				'label'     => esc_html__( 'Carousel', 'jupiterx-core' ),
				'condition' => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_control(
			'effect',
			[
				'label'              => esc_html__( 'Effect', 'jupiterx-core' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'slide',
				'options'            => [
					'slide' => esc_html__( 'Slide', 'jupiterx-core' ),
					'fade'  => esc_html__( 'Fade', 'jupiterx-core' ),
				],
				'description'        => esc_html__( 'Fade transitions one page at a time. Slides per view sets how many loop items appear in each fade page.', 'jupiterx-core' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'show_arrows',
			[
				'label'              => esc_html__( 'Arrows', 'jupiterx-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_off'          => esc_html__( 'Hide', 'jupiterx-core' ),
				'label_on'           => esc_html__( 'Show', 'jupiterx-core' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'pagination',
			[
				'label'              => esc_html__( 'Pagination', 'jupiterx-core' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'bullets',
				'options'            => [
					''            => esc_html__( 'None', 'jupiterx-core' ),
					'bullets'     => esc_html__( 'Dots', 'jupiterx-core' ),
					'fraction'    => esc_html__( 'Fraction', 'jupiterx-core' ),
					'progressbar' => esc_html__( 'Progress', 'jupiterx-core' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'loop',
			[
				'label'              => esc_html__( 'Loop', 'jupiterx-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'              => esc_html__( 'Autoplay', 'jupiterx-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label'              => esc_html__( 'Autoplay speed', 'jupiterx-core' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 5000,
				'frontend_available' => true,
				'condition'          => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'              => esc_html__( 'Pause on hover', 'jupiterx-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'frontend_available' => true,
				'condition'          => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'pause_on_interaction',
			[
				'label'              => esc_html__( 'Pause on interaction', 'jupiterx-core' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'description'        => esc_html__( 'Stops autoplay permanently after the visitor swipes, uses arrows or pagination, or clicks a slide. Pause on hover only pauses while the pointer is over the carousel.', 'jupiterx-core' ),
				'frontend_available' => true,
				'condition'          => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label'              => esc_html__( 'Transition duration', 'jupiterx-core' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 500,
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_carousel_style_controls() {
		$this->start_controls_section(
			'section_carousel_style',
			[
				'label'     => esc_html__( 'Carousel Container', 'jupiterx-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'carousel_background',
				'selector' => '{{WRAPPER}} .jupiterx-loop-carousel',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'carousel_border',
				'selector' => '{{WRAPPER}} .jupiterx-loop-carousel',
			]
		);

		$this->add_responsive_control(
			'carousel_border_radius',
			[
				'label'      => esc_html__( 'Border radius', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-carousel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'carousel_padding',
			[
				'label'      => esc_html__( 'Padding', 'jupiterx-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-carousel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_box_shadow',
				'selector' => '{{WRAPPER}} .jupiterx-loop-carousel',
			]
		);

		$this->end_controls_section();
	}

	protected function get_carousel_query_settings() {
		$query_settings = $this->get_query_settings();

		// Carousels render a single slide set; ignore archive/page pagination in the query.
		$query_settings['paged'] = 1;

		return $query_settings;
	}

	/**
	 * @param array<string, mixed> $settings Widget settings.
	 */
	protected function get_fade_items_per_slide( array $settings ) {
		if ( empty( $settings['effect'] ) || 'fade' !== $settings['effect'] ) {
			return 1;
		}

		return max( 1, (int) ( $settings['slides_per_view'] ?? 1 ) );
	}

	/**
	 * @param object               $query            Terms query result or WP_Query.
	 * @param array<string, mixed> $settings         Widget settings.
	 * @param bool                 $is_terms_source  Whether the source is terms.
	 */
	protected function get_carousel_slide_count( $query, array $settings, $is_terms_source ) {
		$total = $is_terms_source ? count( (array) $query->items ) : (int) $query->post_count;

		if ( $total <= 0 ) {
			return 0;
		}

		$items_per_slide = $this->get_fade_items_per_slide( $settings );

		if ( $items_per_slide > 1 ) {
			return (int) ceil( $total / $items_per_slide );
		}

		return $total;
	}

	/**
	 * @param \WP_Query            $query       Query.
	 * @param array<string, mixed> $settings    Widget settings.
	 * @param int                  $template_id Loop item template ID.
	 * @param int                  $items_per_slide Items grouped into each fade slide.
	 */
	protected function render_grouped_loop_items( \WP_Query $query, array $settings, $template_id, $items_per_slide ) {
		$posts = is_array( $query->posts ) ? $query->posts : [];

		if ( empty( $posts ) ) {
			return;
		}

		$gap = isset( $settings['space_between']['size'] ) ? (int) $settings['space_between']['size'] : 24;

		foreach ( array_chunk( $posts, $items_per_slide ) as $chunk ) {
			$chunk_ids = wp_list_pluck( $chunk, 'ID' );

			echo '<div class="swiper-slide">';
			echo '<div class="jupiterx-loop-carousel__group" style="--jx-carousel-group-columns:' . esc_attr( (string) $items_per_slide ) . ';--jx-carousel-group-gap:' . esc_attr( (string) $gap ) . 'px;">';

			$chunk_query = new \WP_Query(
				[
					'post_type'      => $query->get( 'post_type' ) ?: 'post',
					'post__in'       => $chunk_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => count( $chunk_ids ),
					'post_status'    => $query->get( 'post_status' ) ?: 'publish',
				]
			);

			$this->render_loop_items( $chunk_query, $settings, $template_id, '', '' );
			wp_reset_postdata();

			echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * @param object               $term_result Terms query result.
	 * @param array<string, mixed> $settings    Widget settings.
	 * @param int                  $template_id Loop item template ID.
	 * @param int                  $items_per_slide Items grouped into each fade slide.
	 */
	protected function render_grouped_loop_terms( $term_result, array $settings, $template_id, $items_per_slide ) {
		$terms = is_array( $term_result->items ?? null ) ? $term_result->items : [];

		if ( empty( $terms ) ) {
			return;
		}

		$gap = isset( $settings['space_between']['size'] ) ? (int) $settings['space_between']['size'] : 24;

		foreach ( array_chunk( $terms, $items_per_slide ) as $chunk ) {
			echo '<div class="swiper-slide">';
			echo '<div class="jupiterx-loop-carousel__group" style="--jx-carousel-group-columns:' . esc_attr( (string) $items_per_slide ) . ';--jx-carousel-group-gap:' . esc_attr( (string) $gap ) . 'px;">';

			$chunk_result = (object) [
				'items' => $chunk,
			];

			$this->render_loop_terms( $chunk_result, $settings, $template_id, '', '' );

			echo '</div>';
			echo '</div>';
		}
	}

	protected function render_carousel_slides( $query, array $settings, $template_id, $is_terms_source ) {
		$items_per_slide = $this->get_fade_items_per_slide( $settings );
		$slide_open      = '<div class="swiper-slide">';
		$slide_close     = '</div>';

		if ( $items_per_slide > 1 ) {
			if ( $is_terms_source ) {
				$this->render_grouped_loop_terms( $query, $settings, $template_id, $items_per_slide );
			} else {
				$this->render_grouped_loop_items( $query, $settings, $template_id, $items_per_slide );
			}

			return;
		}

		if ( $is_terms_source ) {
			$this->render_loop_terms( $query, $settings, $template_id, $slide_open, $slide_close );
			return;
		}

		$this->render_loop_items( $query, $settings, $template_id, $slide_open, $slide_close );
	}

	protected function render() {
		if ( Loop_Grid_Module::should_suppress_nested_loop_widgets() ) {
			return;
		}

		$settings        = $this->get_settings_for_display();
		$query_settings  = $this->get_carousel_query_settings();
		$template_id     = $this->get_loop_template_id( $settings );
		$is_terms_source = $this->is_terms_source( $settings );

		if ( ! $template_id || ! $this->is_loop_template( $template_id ) ) {
			if ( $this->is_editor_preview() ) {
				$this->render_editor_placeholder_carousel();
				return;
			}

			if ( current_user_can( 'edit_posts' ) ) {
				echo '<div class="elementor-alert elementor-alert-info jupiterx-loop-grid__empty">';
				echo esc_html__( 'Select a published JupiterX Loop Item template.', 'jupiterx-core' );
				echo '</div>';
			}
			return;
		}

		$query = $is_terms_source ? $this->get_terms_query_result( $query_settings ) : $this->get_posts_query();
		$has_items = $is_terms_source ? ! empty( $query->items ) : $query->post_count > 0;
		$slide_count = $this->get_carousel_slide_count( $query, $settings, $is_terms_source );

		if ( ! $has_items ) {
			if ( $this->is_editor_preview() ) {
				$this->render_editor_placeholder_carousel();
				return;
			}

			if ( ! empty( $settings['enable_empty_message'] ) && 'yes' === $settings['enable_empty_message'] && ! empty( $settings['empty_message'] ) ) {
				$empty_tag = $this->get_safe_empty_message_tag( $settings['empty_message_html_tag'] ?? 'div' );
				echo '<' . tag_escape( $empty_tag ) . ' class="jupiterx-loop-grid__empty">';
				echo esc_html( $settings['empty_message'] );
				echo '</' . tag_escape( $empty_tag ) . '>';
			}
			return;
		}

		$carousel_classes = [
			'jupiterx-loop-carousel',
			'raven-swiper',
			'jupiterx-loop-carousel--effect-' . sanitize_html_class( ! empty( $settings['effect'] ) ? $settings['effect'] : 'slide' ),
		];
		?>
		<div class="<?php echo esc_attr( implode( ' ', $carousel_classes ) ); ?>">
			<div class="raven-main-swiper swiper">
				<div class="swiper-wrapper">
					<?php $this->render_carousel_slides( $query, $settings, $template_id, $is_terms_source ); ?>
				</div>
				<?php if ( $slide_count > 1 ) : ?>
					<?php if ( ! empty( $settings['pagination'] ) ) : ?>
						<div class="swiper-pagination"></div>
					<?php endif; ?>
					<?php if ( ! empty( $settings['show_arrows'] ) && 'yes' === $settings['show_arrows'] ) : ?>
						<div class="elementor-swiper-button elementor-swiper-button-prev">
							<i class="eicon-chevron-left" aria-hidden="true"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Previous', 'jupiterx-core' ); ?></span>
						</div>
						<div class="elementor-swiper-button elementor-swiper-button-next">
							<i class="eicon-chevron-right" aria-hidden="true"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Next', 'jupiterx-core' ); ?></span>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

<?php
/**
 * Loop Grid module.
 *
 * @package JupiterX_Core\Raven
 * @since NEXT
 */

namespace JupiterX_Core\Raven\Modules\Loop_Grid;

defined( 'ABSPATH' ) || die();

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Core\Files\CSS\Post_Preview;
use Elementor\Plugin as Elementor_Plugin;
use Elementor\TemplateLibrary\Source_Local;
use JupiterX_Core\Raven\Base\Module_Base;
use JupiterX_Core\Raven\Core\Document_Types\Type\Jupiterx_Loop_Item_CSS;
use JupiterX_Core\Raven\Core\Document_Types\Type\Jupiterx_Loop_Item_Document;
use JupiterX_Core\Raven\Utils;

/**
 * Registers the Loop Grid widget and query offset fixes for pagination.
 */
class Module extends Module_Base {

	/**
	 * Nesting depth when a Loop Item template is rendered inside a Loop Grid/Carousel.
	 * Used to suppress duplicate Elementor post CSS only for embedded renders.
	 *
	 * @var int
	 */
	private static $embedded_loop_item_render_depth = 0;

	public function __construct() {
		parent::__construct();

		add_action( 'pre_get_posts', [ $this, 'fix_query_offset' ], 1 );
		add_filter( 'found_posts', [ $this, 'fix_query_found_posts' ], 1, 2 );
		add_filter( 'elementor/frontend/builder_content_data', [ $this, 'filter_empty_loop_item_content_data' ], 10, 2 );
		add_filter( 'elementor/finder/categories', [ $this, 'add_finder_items' ] );
		add_action( 'elementor/template-library/create_new_dialog_fields', [ $this, 'add_source_type_to_template_popup' ], 10 );
		add_filter( 'elementor/css-file/dynamic/should_enqueue', [ $this, 'prevent_loop_item_dynamic_css_enqueue' ], 10, 2 );
		add_filter( 'elementor/frontend/builder_content/before_enqueue_css_file', [ $this, 'filter_loop_item_builder_css_file' ], 10, 1 );
		add_action( 'elementor/css-file/post/enqueue', [ $this, 'dequeue_loop_item_post_css' ] );
		add_action( 'manage_' . Source_Local::CPT . '_posts_columns', [ $this, 'manage_loop_item_posts_columns' ] );
		add_action( 'wp_ajax_raven_loop_grid_render', [ $this, 'ajax_render_loop_grid' ] );
		add_action( 'wp_ajax_nopriv_raven_loop_grid_render', [ $this, 'ajax_render_loop_grid' ] );
		add_filter( 'elementor/document/config', [ $this, 'filter_loop_item_document_config' ], 30, 2 );
		add_filter( 'template_include', [ $this, 'filter_loop_item_template_to_canvas' ], 999 );
		add_filter( 'body_class', [ $this, 'filter_loop_item_preview_body_class' ] );
		add_filter( 'elementor/frontend/widget/should_render', [ $this, 'suppress_nested_loop_widgets' ], 10, 2 );
	}

	/**
	 * Begin suppressing standalone Loop Item document CSS (embedded inside a grid/carousel).
	 */
	public static function enter_embedded_loop_item_render() {
		++self::$embedded_loop_item_render_depth;
	}

	/**
	 * End embedded Loop Item render scope.
	 */
	public static function leave_embedded_loop_item_render() {
		self::$embedded_loop_item_render_depth = max( 0, self::$embedded_loop_item_render_depth - 1 );
	}

	/**
	 * Whether Loop Item post CSS / dynamic CSS should be skipped (embedded render only).
	 */
	public static function should_suppress_loop_item_document_css() {
		return self::$embedded_loop_item_render_depth > 0;
	}

	/**
	 * Whether nested Loop Grid/Carousel/Filter widgets should be skipped.
	 */
	public static function should_suppress_nested_loop_widgets() {
		return self::should_suppress_loop_item_document_css();
	}

	/**
	 * Widget names that must not render inside an embedded Loop Item.
	 *
	 * @return string[]
	 */
	public static function get_nested_loop_widget_names() {
		$widgets = [
			'raven-loop-grid',
			'raven-loop-carousel',
			'raven-loop-filter',
		];

		if ( class_exists( '\ElementorPro\Plugin' ) ) {
			$widgets = array_merge( $widgets, [ 'loop-grid', 'loop-carousel', 'taxonomy-filter' ] );
		}

		return $widgets;
	}

	/**
	 * Prevent loop widgets from rendering inside a Loop Item slide/card.
	 *
	 * @param bool              $should_render Whether the widget should render.
	 * @param \Elementor\Widget_Base $widget   Widget instance.
	 * @return bool
	 */
	public function suppress_nested_loop_widgets( $should_render, $widget ) {
		if ( ! $should_render || ! self::should_suppress_nested_loop_widgets() ) {
			return $should_render;
		}

		if ( in_array( $widget->get_name(), self::get_nested_loop_widget_names(), true ) ) {
			return false;
		}

		return $should_render;
	}

	public function get_widgets() {
		return [ 'loop-grid', 'loop-carousel', 'loop-filter', 'loop-term-title', 'loop-term-description', 'loop-term-count' ];
	}

	public function add_finder_items( array $categories ) {
		if ( empty( $categories['create']['items'] ) ) {
			return $categories;
		}

		$categories['create']['items']['jupiterx-loop-item'] = [
			'title'    => esc_html__( 'Add New JupiterX Loop Item', 'jupiterx-core' ),
			'icon'     => 'plus-circle-o',
			'url'      => $this->get_admin_templates_url() . '#add_new',
			'keywords' => [ 'template', 'theme', 'new', 'create', 'loop', 'dynamic', 'listing', 'archive', 'repeater', 'jupiterx' ],
		];

		return $categories;
	}

	public function add_source_type_to_template_popup( $form ) {
		if ( empty( $form ) || ! method_exists( $form, 'add_control' ) ) {
			return;
		}

		$form->add_control(
			'_elementor_source',
			[
				'type'       => Controls_Manager::SELECT,
				'label'      => esc_html__( 'Choose source type', 'jupiterx-core' ),
				'options'    => [
					'post'          => esc_html__( 'Posts', 'jupiterx-core' ),
					'post_taxonomy' => esc_html__( 'Post Taxonomy', 'jupiterx-core' ),
				],
				'default'    => 'post',
				'section'    => 'main',
				'required'   => true,
				'conditions' => [
					'template-type' => Jupiterx_Loop_Item_Document::SLUG,
				],
			]
		);
	}

	public function prevent_loop_item_dynamic_css_enqueue( $should_enqueue, $post_id ) {
		if ( $this->is_loop_item_document( $post_id ) ) {
			return false;
		}

		return $should_enqueue;
	}

	/**
	 * Use inline-only Loop Item CSS when builder content loads a loop template.
	 *
	 * @param Post_CSS|Post_Preview $css_file Elementor CSS file instance.
	 * @return Post_CSS|Post_Preview|Jupiterx_Loop_Item_CSS
	 */
	public function filter_loop_item_builder_css_file( $css_file ) {
		if ( ! is_object( $css_file ) || ! method_exists( $css_file, 'get_post_id' ) ) {
			return $css_file;
		}

		$post_id = $css_file->get_post_id();

		if ( ! $this->is_loop_item_document( $post_id ) ) {
			return $css_file;
		}

		if ( $css_file instanceof Jupiterx_Loop_Item_CSS ) {
			return $css_file;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return new Jupiterx_Loop_Item_CSS( $post_id );
		}

		return Jupiterx_Loop_Item_CSS::create( $post_id );
	}

	public function dequeue_loop_item_post_css( $css_file ) {
		if ( ! is_object( $css_file ) || ! method_exists( $css_file, 'get_post_id' ) ) {
			return;
		}

		$post_id     = $css_file->get_post_id();
		$file_handle = 'elementor-post-' . $post_id;

		if ( $this->is_loop_item_document( $post_id ) && wp_style_is( $file_handle, 'enqueued' ) ) {
			wp_dequeue_style( $file_handle );
		}
	}

	/**
	 * Prevent theme-builder / display-conditions UI from treating Loop Items like location-based templates.
	 *
	 * @param array $config    Additional document config.
	 * @param int   $post_id Post ID.
	 * @return array
	 */
	public function filter_loop_item_document_config( $config, $post_id ) {
		$template_type = get_post_meta( $post_id, Document::TYPE_META_KEY, true );

		// JupiterX Loop Item + Elementor Pro "Loop Item" (avoid theme_builder / display-conditions bindings).
		if ( ! in_array( $template_type, [ Jupiterx_Loop_Item_Document::SLUG, 'loop-item' ], true ) ) {
			return $config;
		}

		unset( $config['theme_builder'] );

		return $config;
	}

	/**
	 * Use Elementor canvas when previewing a Loop Item template on the frontend.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function filter_loop_item_template_to_canvas( $template ) {
		if ( ! Jupiterx_Loop_Item_Document::is_preview_context() ) {
			return $template;
		}

		$page_templates = Elementor_Plugin::$instance->modules_manager->get_modules( 'page-templates' );

		if ( ! $page_templates || ! method_exists( $page_templates, 'get_template_path' ) ) {
			return $template;
		}

		$canvas = $page_templates->get_template_path( \Elementor\Modules\PageTemplates\Module::TEMPLATE_CANVAS );

		return ( ! empty( $canvas ) && is_readable( $canvas ) ) ? $canvas : $template;
	}

	/**
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function filter_loop_item_preview_body_class( $classes ) {
		if ( Jupiterx_Loop_Item_Document::is_preview_context() ) {
			$classes[] = 'jupiterx-loop-template-canvas';
		}

		return $classes;
	}

	public function manage_loop_item_posts_columns( $columns ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list-table context.
		$template_type = isset( $_REQUEST[ Source_Local::TAXONOMY_TYPE_SLUG ] ) ? sanitize_key( wp_unslash( $_REQUEST[ Source_Local::TAXONOMY_TYPE_SLUG ] ) ) : '';

		if ( Jupiterx_Loop_Item_Document::SLUG === $template_type ) {
			unset( $columns['instances'] );
		}

		return $columns;
	}

	private function get_admin_templates_url() {
		return add_query_arg(
			[
				'tabs_group'             => 'theme',
				'elementor_library_type' => Jupiterx_Loop_Item_Document::SLUG,
			],
			admin_url( Source_Local::ADMIN_MENU_SLUG )
		);
	}

	private function is_loop_item_document( $post_id ) {
		return Jupiterx_Loop_Item_Document::SLUG === get_post_meta( $post_id, Document::TYPE_META_KEY, true );
	}

	/**
	 * Keep Elementor's editor preview path alive for empty Loop Item templates.
	 *
	 * @param array $data    Builder content data.
	 * @param int   $post_id Template post ID.
	 * @return array
	 */
	public function filter_empty_loop_item_content_data( $data, $post_id ) {
		if (
			empty( $data ) &&
			wp_doing_ajax() &&
			Jupiterx_Loop_Item_Document::SLUG === get_post_meta( $post_id, Document::TYPE_META_KEY, true )
		) {
			$data['empty_jupiterx_loop_template']    = true;
			$data['empty_jupiterx_loop_template_id'] = $post_id;
		}

		return $data;
	}

	public function ajax_render_loop_grid() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public pagination AJAX.
		$post_id  = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public pagination AJAX.
		$model_id = isset( $_POST['model_id'] ) ? sanitize_text_field( wp_unslash( $_POST['model_id'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public pagination AJAX.
		$paged    = isset( $_POST['paged'] ) ? max( 1, absint( wp_unslash( $_POST['paged'] ) ) ) : 1;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public pagination AJAX.
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public pagination AJAX.
		$term_id  = isset( $_POST['term_id'] ) ? absint( wp_unslash( $_POST['term_id'] ) ) : 0;

		if ( empty( $post_id ) || empty( $model_id ) ) {
			wp_send_json_error();
		}

		// Match Elementor Pro posts-widget AJAX: admin-ajax has no main query paged var.
		set_query_var( 'paged', $paged );

		$document = Elementor_Plugin::$instance->documents->get( $post_id );

		if ( ! $document ) {
			wp_send_json_error();
		}

		$element_data = Utils::find_element_recursive( $document->get_elements_data(), $model_id );

		if ( empty( $element_data ) ) {
			wp_send_json_error();
		}

		$element_data['settings']['paged'] = $paged;

		if ( ! empty( $taxonomy ) && ! empty( $term_id ) && taxonomy_exists( $taxonomy ) ) {
			$element_data['settings']['ajax_filter_taxonomy'] = $taxonomy;
			$element_data['settings']['ajax_filter_term_id']  = $term_id;
		}

		$widget = Elementor_Plugin::$instance->elements_manager->create_element_instance( $element_data );

		if ( ! $widget instanceof Widgets\Loop_Grid ) {
			wp_send_json_error();
		}

		$widget->set_settings( 'paged', $paged );

		$payload = $widget->render_ajax_items();

		wp_send_json_success( $payload );
	}

	/**
	 * Mirrors Raven Posts offset handling so pagination stays correct when offset is set.
	 *
	 * @param \WP_Query $query Query instance.
	 */
	public function fix_query_offset( &$query ) {
		if ( ! empty( $query->query_vars['offset_proper'] ) ) {
			if ( $query->is_paged ) {
				$query->set( 'offset', $query->query_vars['offset_proper'] + ( ( $query->query_vars['paged'] - 1 ) * $query->query_vars['posts_per_page'] ) );
				$query->set( 'jx_loop_grid_fix_pagination_offset', $query->query_vars['offset_proper'] );
				return;
			}

			$query->set( 'offset', $query->query_vars['offset_proper'] );
			$query->set( 'jx_loop_grid_fix_pagination_offset', $query->query_vars['offset_proper'] );
		}
	}

	/**
	 * @param int       $found_posts Found posts count.
	 * @param \WP_Query $query       Query.
	 * @return int
	 */
	public function fix_query_found_posts( $found_posts, $query ) {
		$offset_proper = $query->get( 'jx_loop_grid_fix_pagination_offset' );

		if ( $offset_proper ) {
			$found_posts -= $offset_proper;
		}

		return $found_posts;
	}
}

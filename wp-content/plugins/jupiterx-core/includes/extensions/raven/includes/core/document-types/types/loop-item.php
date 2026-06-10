<?php
/**
 * Loop Item library document (template used by Loop Grid).
 *
 * @package JupiterX_Core\Raven
 * @since NEXT
 */

namespace JupiterX_Core\Raven\Core\Document_Types\Type;

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Modules\Library\Documents\Page;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Local;
use JupiterX_Core\Raven\Modules\Loop_Grid\Module as Loop_Grid_Module;

defined( 'ABSPATH' ) || die();

/**
 * Library document for a single “loop item” design.
 *
 * Stored as an Elementor library template; the Loop Grid widget repeats this
 * template for each post in the query.
 */
class Jupiterx_Loop_Item_Document extends Page {

	private const SOURCE_META_KEY = '_elementor_source';

	/**
	 * Tracks whether Loop Item CSS was already printed in this request.
	 *
	 * @var array<int, bool>
	 */
	private static $printed_loop_item_css = [];

	/**
	 * Document type slug (meta + template library type).
	 */
	public const SLUG = 'jupiterx-loop-item';

	public function get_name() {
		return self::SLUG;
	}

	public static function get_title() {
		return esc_html__( 'JupiterX Loop Item', 'jupiterx-core' );
	}

	public static function get_plural_title() {
		return esc_html__( 'JupiterX Loop Items', 'jupiterx-core' );
	}

	public static function get_add_new_title() {
		return esc_html__( 'Add New Loop Item', 'jupiterx-core' );
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['library_view']        = 'list';
		$properties['group']               = 'blocks';
		$properties['support_conditions']  = false;
		$properties['support_kit']         = true;
		$properties['support_site_editor'] = false;
		$properties['show_in_finder']      = true;

		return $properties;
	}

	public function save( $data ) {
		if ( isset( $data['settings']['source'] ) ) {
			update_post_meta( $this->get_main_id(), self::SOURCE_META_KEY, sanitize_key( $data['settings']['source'] ) );
		}

		return parent::save( $data );
	}

	public function get_container_attributes() {
		$attributes = parent::get_container_attributes();
		$data_id    = get_the_ID() ? get_the_ID() : $this->get_main_id();

		$attributes['class'] .= ' jupiterx-loop-item';
		$attributes['class'] .= ' jupiterx-loop-item-' . $this->get_main_id();
		$attributes['class'] .= ' ' . esc_attr( implode( ' ', get_post_class( [], $data_id ) ) );

		$attributes['data-custom-edit-handle'] = true;

		return $attributes;
	}

	public function get_css_wrapper_selector() {
		return '.jupiterx-loop-item-' . $this->get_main_id();
	}

	public function get_wrapper_tags() {
		return false;
	}

	public function get_initial_config() {
		$config = parent::get_initial_config();

		if ( empty( $config['panel']['widgets_settings'] ) || ! is_array( $config['panel']['widgets_settings'] ) ) {
			$config['panel']['widgets_settings'] = [];
		}

		$config['panel']['widgets_settings']['raven-loop-grid'] = [
			'show_in_panel' => false,
		];
		$config['panel']['widgets_settings']['raven-loop-carousel'] = [
			'show_in_panel' => false,
		];
		$config['panel']['widgets_settings']['raven-loop-filter'] = [
			'show_in_panel' => false,
		];

		foreach ( [ 'raven-post-title', 'raven-post-content', 'raven-post-terms', 'raven-post-meta', 'raven-loop-term-title', 'raven-loop-term-description', 'raven-loop-term-count' ] as $recommended_widget ) {
			$config['panel']['widgets_settings'][ $recommended_widget ] = [
				'categories'    => [ 'recommended' ],
				'show_in_panel' => true,
			];
		}

		$config['container_attributes'] = $this->get_container_attributes();

		if ( class_exists( '\ElementorPro\Plugin' ) ) {
			foreach ( [ 'loop-grid', 'loop-carousel', 'taxonomy-filter' ] as $pro_loop_widget ) {
				$config['panel']['widgets_settings'][ $pro_loop_widget ] = [
					'show_in_panel' => false,
				];
			}
		}

		return $config;
	}

	public static function get_preview_as_options() {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$options    = [];

		foreach ( $post_types as $post_type => $post_type_object ) {
			$options[ 'single/' . $post_type ] = $post_type_object->labels->singular_name;
		}

		return [
			'single' => [
				'label'   => esc_html__( 'Single', 'jupiterx-core' ),
				'options' => $options,
			],
		];
	}

	protected static function get_editor_panel_categories() {
		$categories = parent::get_editor_panel_categories();
		$recommended = [
			'recommended' => [
				'title' => esc_html__( 'Recommended', 'jupiterx-core' ),
			],
		];

		return $recommended + $categories;
	}

	protected function register_controls() {
		parent::register_controls();

		$this->start_controls_section(
			'_section_query',
			[
				'label' => esc_html__( 'Query', 'jupiterx-core' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'source',
			[
				'label'       => esc_html__( 'Source Type', 'jupiterx-core' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'post'          => esc_html__( 'Posts', 'jupiterx-core' ),
					'post_taxonomy' => esc_html__( 'Post Taxonomy', 'jupiterx-core' ),
				],
				'default'     => get_post_meta( $this->get_main_id(), self::SOURCE_META_KEY, true ) ?: 'post',
				'description' => esc_html__( 'This affects the recommended widgets and future taxonomy loop behavior for this item.', 'jupiterx-core' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Whether the current request is previewing a JupiterX Loop Item template.
	 */
	public static function is_preview_context() {
		if ( is_admin() || ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$plugin = Plugin::$instance;

		if ( $plugin->editor->is_edit_mode() ) {
			return true;
		}

		$post_id = self::resolve_preview_post_id();

		if ( ! $post_id ) {
			return false;
		}

		return self::SLUG === get_post_meta( $post_id, Document::TYPE_META_KEY, true );
	}

	/**
	 * Resolve the Loop Item post ID for standalone preview requests.
	 *
	 * @return int
	 */
	private static function resolve_preview_post_id() {
		$plugin = Plugin::$instance;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$preview_id = isset( $_GET['elementor-preview'] ) ? absint( wp_unslash( $_GET['elementor-preview'] ) ) : 0;

		if ( $preview_id && $plugin->preview->is_preview_mode( $preview_id ) ) {
			return $preview_id;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$library_type = isset( $_GET['elementor_library'] ) ? sanitize_key( wp_unslash( $_GET['elementor_library'] ) ) : '';

		if ( 'elementor-' . self::SLUG === $library_type ) {
			return get_the_ID() ?: $preview_id;
		}

		if (
			! empty( $library_type ) &&
			is_singular( Source_Local::CPT )
		) {
			$post = get_queried_object();

			if ( $post instanceof \WP_Post && $library_type === $post->post_name ) {
				return (int) $post->ID;
			}
		}

		if ( function_exists( 'jupiterx_cp_elementor_library_quick_view_is_active' ) && jupiterx_cp_elementor_library_quick_view_is_active() ) {
			return function_exists( 'jupiterx_cp_elementor_library_quick_view_resolve_post_id' )
				? jupiterx_cp_elementor_library_quick_view_resolve_post_id()
				: get_the_ID();
		}

		if ( is_singular( Source_Local::CPT ) ) {
			return get_queried_object_id();
		}

		return 0;
	}

	/**
	 * Print Loop Item CSS before builder output (standalone preview + editor parity).
	 *
	 * @param bool $with_css Passed through to parent::get_content().
	 * @return string
	 */
	public function get_content( $with_css = false ) {
		$edit_mode              = Plugin::$instance->editor->is_edit_mode();
		$document               = Plugin::$instance->documents->get_current();
		$should_switch_document = $document && $document->get_main_id() !== $this->get_main_id();

		if ( $should_switch_document ) {
			Plugin::$instance->documents->switch_to_document( $this );
		}

		add_filter( 'elementor/frontend/builder_content/before_print_css', [ $this, 'prevent_duplicate_inline_css' ] );

		$this->print_loop_item_css();

		Plugin::$instance->editor->set_edit_mode( false );

		$content = parent::get_content( $with_css );

		remove_filter( 'elementor/frontend/builder_content/before_print_css', [ $this, 'prevent_duplicate_inline_css' ] );

		Plugin::$instance->editor->set_edit_mode( $edit_mode );

		if ( $should_switch_document ) {
			Plugin::$instance->documents->restore_document();
		}

		return $content;
	}

	/**
	 * Parent get_content() already prints CSS via print_loop_item_css().
	 *
	 * @return false
	 */
	public function prevent_duplicate_inline_css() {
		return false;
	}

	/**
	 * Enqueue and inline-print Elementor post CSS for standalone Loop Item views.
	 */
	private function print_loop_item_css() {
		if ( Loop_Grid_Module::should_suppress_loop_item_document_css() ) {
			return;
		}

		$post_id = $this->get_main_id();

		if ( ! empty( self::$printed_loop_item_css[ $post_id ] ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			$css_file = new Jupiterx_Loop_Item_CSS( $post_id );
		} else {
			$css_file = Jupiterx_Loop_Item_CSS::create( $post_id );
		}

		$css_file->update();
		$css_file->print_css();

		self::$printed_loop_item_css[ $post_id ] = true;
	}

	public function print_content() {
		$plugin = Plugin::instance();

		if ( $plugin->preview->is_preview_mode( $this->get_main_id() ) ) {
			$this->print_loop_item_css();
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $plugin->preview->builder_wrapper( '' );
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_content( true );
		}
	}

	public function print_elements_with_wrapper( $elements_data = null ) {
		if ( is_array( $elements_data ) && ! empty( $elements_data['empty_jupiterx_loop_template'] ) ) {
			$post_id = ! empty( $elements_data['empty_jupiterx_loop_template_id'] ) ? absint( $elements_data['empty_jupiterx_loop_template_id'] ) : $this->get_main_id();
			?>
			<div
				data-elementor-type="<?php echo esc_attr( self::SLUG ); ?>"
				data-elementor-post-type="<?php echo esc_attr( $this->get_post()->post_type ); ?>"
				data-elementor-id="<?php echo esc_attr( (string) $post_id ); ?>"
				class="elementor elementor-<?php echo esc_attr( (string) $post_id ); ?> elementor-edit-area elementor-edit-mode elementor-edit-area-active jupiterx-loop-first-edit"
				data-elementor-title="<?php echo esc_attr__( 'JupiterX Loop Item', 'jupiterx-core' ); ?>"
			>
				<div class="elementor-section-wrap ui-sortable"></div>
			</div>
			<?php
			return;
		}

		parent::print_elements_with_wrapper( $elements_data );
	}
}

<?php

/**
 * Loop Grid widget.
 *
 * @package JupiterX_Core\Raven
 * @since NEXT
 */

namespace JupiterX_Core\Raven\Modules\Loop_Grid\Widgets;

defined('ABSPATH') || die();

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Core\Documents_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use JupiterX_Core\Raven\Base\Base_Widget;
use JupiterX_Core\Raven\Controls\Query as Control_Query;
use JupiterX_Core\Raven\Core\Document_Types\Type\Jupiterx_Loop_Item_Document;
use JupiterX_Core\Raven\Modules\Loop_Grid\Module as Loop_Grid_Module;
use JupiterX_Core\Raven\Utils;

/**
 * Repeats an Elementor “Loop Item” template for each post in a custom query.
 */
class Loop_Grid extends Base_Widget
{

	/**
	 * Current term while rendering a taxonomy loop item.
	 *
	 * @var \WP_Term|null
	 */
	protected static $current_loop_term = null;

	/**
	 * Cached sticky pagination metadata for the current query.
	 *
	 * @var array<string, mixed>|null
	 */
	protected $sticky_pagination_cache = null;

	public function get_name()
	{
		return 'raven-loop-grid';
	}

	public function get_title()
	{
		return esc_html__('Loop Grid', 'jupiterx-core');
	}

	public function get_icon()
	{
		return 'raven-element-icon raven-element-icon-loop-grid';
	}

	public function get_keywords()
	{
		return array_merge(
			parent::get_keywords(),
			[
				'loop',
				'grid',
				'query',
				'archive',
				'dynamic',
				'template',
				'listing',
				'custom post type',
				'acf',
			]
		);
	}

	public function get_style_depends()
	{
		return ['jupiterx-core-raven-frontend'];
	}

	public static function get_current_loop_term()
	{
		return self::$current_loop_term;
	}

	/**
	 * Normalize the selected Loop Item template ID from widget settings.
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @return int
	 */
	protected function get_loop_template_id( array $settings ) {
		$template_id = $settings['loop_template_id'] ?? 0;

		if ( is_array( $template_id ) ) {
			$template_id = reset( $template_id );
		}

		return absint( $template_id );
	}

	public static function on_import_update_dynamic_content(array $element_config, array $data, $controls = null): array
	{
		if (empty($data['post_ids']) || empty($element_config['settings'])) {
			return $element_config;
		}

		if (! empty($element_config['settings']['loop_template_id']) && isset($data['post_ids'][$element_config['settings']['loop_template_id']])) {
			$element_config['settings']['loop_template_id'] = $data['post_ids'][$element_config['settings']['loop_template_id']];
		}

		if (! empty($element_config['settings']['alternate_templates']) && is_array($element_config['settings']['alternate_templates'])) {
			foreach ($element_config['settings']['alternate_templates'] as &$alternate_template) {
				if (! empty($alternate_template['template_id']) && isset($data['post_ids'][$alternate_template['template_id']])) {
					$alternate_template['template_id'] = $data['post_ids'][$alternate_template['template_id']];
				}
			}
			unset($alternate_template);
		}

		return $element_config;
	}

	protected function register_controls()
	{
		$this->register_layout_controls();
		$this->register_query_controls();
		$this->register_query_advanced_controls();
		$this->register_sort_controls();
		$this->register_pagination_controls();
		$this->register_loop_item_style_controls();
		$this->register_empty_message_style_controls();
		$this->register_pagination_style_controls();
	}

	protected function register_layout_controls()
	{
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__('Layout', 'jupiterx-core'),
			]
		);

		$this->add_control(
			'loop_template_id',
			[
				'label'       => esc_html__('Loop item template', 'jupiterx-core'),
				'description' => esc_html__('Create a “JupiterX Loop Item” in Templates → Saved Templates, design one card, then select it here.', 'jupiterx-core'),
				'type'        => 'raven_query',
				'label_block' => true,
				'query'       => [
					'source'        => Control_Query::QUERY_SOURCE_TEMPLATE,
					'template_types' => [Jupiterx_Loop_Item_Document::SLUG],
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
			'columns',
			[
				'label'              => esc_html__('Columns', 'jupiterx-core'),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 3,
				'tablet_default'     => 2,
				'mobile_default'     => 1,
				'min'                => 1,
				'max'                => 12,
				'frontend_available' => true,
				'condition'          => [
					'loop_template_id!' => '',
				],
				'selectors'          => [
					'{{WRAPPER}} .jupiterx-loop-grid' => '--jx-loop-columns: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'      => esc_html__('Column gap', 'jupiterx-core'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid' => '--jx-loop-column-gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label'      => esc_html__('Row gap', 'jupiterx-core'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid' => '--jx-loop-row-gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_control(
			'masonry',
			[
				'label'              => esc_html__('Masonry', 'jupiterx-core'),
				'type'               => Controls_Manager::SWITCHER,
				'label_off'          => esc_html__('Off', 'jupiterx-core'),
				'label_on'           => esc_html__('On', 'jupiterx-core'),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'condition'          => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_control(
			'equal_height',
			[
				'label'        => esc_html__('Equal height', 'jupiterx-core'),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => esc_html__('Off', 'jupiterx-core'),
				'label_on'     => esc_html__('On', 'jupiterx-core'),
				'return_value' => 'yes',
				'condition'    => [
					'loop_template_id!' => '',
					'masonry'           => '',
				],
				'selectors'    => [
					'{{WRAPPER}} .jupiterx-loop-grid' => 'grid-auto-rows: 1fr',
					'{{WRAPPER}} .jupiterx-loop-grid__item' => 'display: flex; flex-direction: column; height: 100%; min-height: 0;',
					'{{WRAPPER}} .jupiterx-loop-grid__item > .elementor' => 'display: flex; flex: 1 1 auto; flex-direction: column; width: 100%; min-height: 100%;',
					'{{WRAPPER}} .jupiterx-loop-grid__item > .elementor > .e-con:only-child, {{WRAPPER}} .jupiterx-loop-grid__item > .elementor > .elementor-section:only-child, {{WRAPPER}} .jupiterx-loop-grid__item > .elementor > .elementor-section:only-child > .elementor-container' => 'flex: 1 1 auto; min-height: 100%;',
				],
			]
		);

		$this->add_control(
			'alternate_template',
			[
				'label'              => esc_html__('Apply alternate templates', 'jupiterx-core'),
				'type'               => Controls_Manager::SELECT,
				'default'            => '',
				'options'            => [
					''    => esc_html__('No', 'jupiterx-core'),
					'yes' => esc_html__('Yes', 'jupiterx-core'),
				],
				'frontend_available' => true,
				'separator'          => 'before',
				'condition'          => [
					'loop_template_id!' => '',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'template_id',
			[
				'label'       => esc_html__('Loop item template', 'jupiterx-core'),
				'type'        => 'raven_query',
				'label_block' => true,
				'query'       => [
					'source'        => Control_Query::QUERY_SOURCE_TEMPLATE,
					'template_types' => [Jupiterx_Loop_Item_Document::SLUG],
				],
			]
		);

		$repeater->add_control(
			'repeat_template',
			[
				'label'       => esc_html__('Position in grid', 'jupiterx-core'),
				'description' => esc_html__('Repeat this template every chosen number of items. For example, 3 applies it to item 3, 6, 9, and so on.', 'jupiterx-core'),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'default'     => 3,
				'condition'   => [
					'template_id!' => '',
				],
			]
		);

		$repeater->add_control(
			'show_once',
			[
				'label'        => esc_html__('Apply once', 'jupiterx-core'),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_off'    => esc_html__('No', 'jupiterx-core'),
				'label_on'     => esc_html__('Yes', 'jupiterx-core'),
				'return_value' => 'yes',
				'condition'    => [
					'template_id!' => '',
				],
			]
		);

		$repeater->add_responsive_control(
			'column_span',
			[
				'label'     => esc_html__('Column span', 'jupiterx-core'),
				'type'      => Controls_Manager::SELECT,
				'default'   => '1',
				'options'   => [
					'1'  => '1',
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'5'  => '5',
					'6'  => '6',
					'7'  => '7',
					'8'  => '8',
					'9'  => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
				],
				'condition' => [
					'template_id!' => '',
				],
			]
		);

		$repeater->add_control(
			'static_position',
			[
				'label'        => esc_html__('Static item position', 'jupiterx-core'),
				'description'  => esc_html__('Static items stay in the selected position instead of repeating.', 'jupiterx-core'),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => esc_html__('Off', 'jupiterx-core'),
				'label_on'     => esc_html__('On', 'jupiterx-core'),
				'return_value' => 'yes',
				'condition'    => [
					'template_id!' => '',
				],
			]
		);

		$this->add_control(
			'alternate_templates',
			[
				'label'       => esc_html__('Alternate templates', 'jupiterx-core'),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => esc_html__('Alternate Template', 'jupiterx-core'),
				'condition'   => [
					'alternate_template' => 'yes',
					'loop_template_id!'  => '',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_query_controls()
	{
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__('Query', 'jupiterx-core'),
			]
		);

		$this->add_control(
			'loop_source',
			[
				'label'              => esc_html__('Content', 'jupiterx-core'),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'posts',
				'options'            => [
					'posts' => esc_html__('Posts', 'jupiterx-core'),
					'terms' => esc_html__('Terms', 'jupiterx-core'),
				],
				'frontend_available' => true,
				'condition'          => [
					'is_archive_template!' => 'true',
				],
			]
		);

		$this->add_control(
			'is_archive_template',
			[
				'label'              => esc_html__('Use current archive query', 'jupiterx-core'),
				'description'        => esc_html__('When enabled, the global archive query is used (category, search, author, etc.). Other query options below are skipped.', 'jupiterx-core'),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__('Yes', 'jupiterx-core'),
				'label_off'          => esc_html__('No', 'jupiterx-core'),
				'return_value'       => 'true',
				'default'            => '',
				'frontend_available' => true,
				'condition'          => [
					'loop_source' => 'posts',
				],
			]
		);

		$this->add_control(
			'terms_taxonomy',
			[
				'label'              => esc_html__('Taxonomy', 'jupiterx-core'),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'category',
				'options'            => $this->get_taxonomy_options(),
				'frontend_available' => true,
				'condition'          => [
					'loop_source' => 'terms',
				],
			]
		);

		$this->add_control(
			'terms_per_page',
			[
				'label'              => esc_html__('Terms per page', 'jupiterx-core'),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 6,
				'min'                => -1,
				'max'                => 100,
				'frontend_available' => true,
				'condition'          => [
					'loop_source' => 'terms',
				],
			]
		);

		$this->add_control(
			'terms_hide_empty',
			[
				'label'        => esc_html__('Hide empty terms', 'jupiterx-core'),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [
					'loop_source' => 'terms',
				],
			]
		);

		$this->add_control(
			'terms_parent',
			[
				'label'       => esc_html__('Parent term ID', 'jupiterx-core'),
				'description' => esc_html__('Leave empty to include all levels. Use 0 for top-level terms only.', 'jupiterx-core'),
				'type'        => Controls_Manager::NUMBER,
				'condition'   => [
					'loop_source' => 'terms',
				],
			]
		);

		$this->add_control(
			'terms_include',
			[
				'label'       => esc_html__('Include terms', 'jupiterx-core'),
				'type'        => 'raven_query',
				'label_block' => true,
				'multiple'    => true,
				'condition'   => [
					'loop_source' => 'terms',
				],
				'query'       => [
					'source' => Control_Query::QUERY_SOURCE_TAX,
				],
			]
		);

		$this->add_control(
			'terms_exclude',
			[
				'label'       => esc_html__('Exclude terms', 'jupiterx-core'),
				'type'        => 'raven_query',
				'label_block' => true,
				'multiple'    => true,
				'condition'   => [
					'loop_source' => 'terms',
				],
				'query'       => [
					'source' => Control_Query::QUERY_SOURCE_TAX,
				],
			]
		);

		$this->add_control(
			'query_posts_per_page',
			[
				'label'              => esc_html__('Posts per page', 'jupiterx-core'),
				'description'        => esc_html__('Use -1 to show all posts. When “Use current archive query” is on, this overrides the archive count unless empty.', 'jupiterx-core'),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 6,
				'min'                => -1,
				'max'                => 100,
				'frontend_available' => true,
				'condition'          => [
					'is_archive_template' => '',
					'loop_source'         => 'posts',
				],
			]
		);

		$this->add_control(
			'archive_posts_per_page',
			[
				'label'              => esc_html__('Archive posts per page', 'jupiterx-core'),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 6,
				'min'                => -1,
				'max'                => 100,
				'frontend_available' => true,
				'condition'          => [
					'is_archive_template' => 'true',
					'loop_source'         => 'posts',
				],
			]
		);

		$this->add_group_control(
			'raven-posts',
			[
				'name'      => 'query',
				'post_type' => $this->get_loop_post_type_options(),
				'condition' => [
					'is_archive_template' => '',
					'loop_source'         => 'posts',
				],
			]
		);

		$this->add_control(
			'enable_empty_message',
			[
				'label'        => esc_html__('No results message', 'jupiterx-core'),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => esc_html__('Off', 'jupiterx-core'),
				'label_on'     => esc_html__('On', 'jupiterx-core'),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'empty_message',
			[
				'label'       => esc_html__('No results message', 'jupiterx-core'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('No posts found.', 'jupiterx-core'),
				'label_block' => true,
				'dynamic'     => [
					'active' => true,
				],
				'condition'   => [
					'enable_empty_message' => 'yes',
				],
			]
		);

		$this->add_control(
			'empty_message_html_tag',
			[
				'label'     => esc_html__('HTML tag', 'jupiterx-core'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'div',
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'condition' => [
					'enable_empty_message' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_query_advanced_controls()
	{
		$this->start_controls_section(
			'section_query_advanced',
			[
				'label' => esc_html__('Advanced', 'jupiterx-core'),
			]
		);

		$this->add_control(
			'query_id',
			[
				'label'       => esc_html__('Query ID', 'jupiterx-core'),
				'description' => esc_html__('Optional developer hook for modifying this widget query from PHP. Example: enter “my_loop”, then use add_action( “elementor/query/my_loop”, function( $query ) { $query->set( “posts_per_page”, 1 ); } ). To change query args before the query runs, use add_filter( “elementor/query/query_args”, ... ). Leave empty for normal usage.', 'jupiterx-core'),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function register_sort_controls()
	{
		$this->start_controls_section(
			'section_sort',
			[
				'label'     => esc_html__('Sort', 'jupiterx-core'),
				'condition' => [
					'is_archive_template' => '',
				],
			]
		);

		$this->add_control(
			'query_orderby',
			[
				'label'     => esc_html__('Order by', 'jupiterx-core'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'date'           => esc_html__('Date', 'jupiterx-core'),
					'title'          => esc_html__('Title', 'jupiterx-core'),
					'name'           => esc_html__('Slug', 'jupiterx-core'),
					'menu_order'     => esc_html__('Menu order', 'jupiterx-core'),
					'modified'       => esc_html__('Last modified', 'jupiterx-core'),
					'rand'           => esc_html__('Random', 'jupiterx-core'),
					'meta_value'     => esc_html__('Custom field (alphabetical)', 'jupiterx-core'),
					'meta_value_num' => esc_html__('Custom field (numeric)', 'jupiterx-core'),
				],
				'condition' => [
					'loop_source!' => 'terms',
				],
			]
		);

		$this->add_control(
			'terms_query_orderby',
			[
				'label'     => esc_html__('Order by', 'jupiterx-core'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'name',
				'options'   => [
					'name'        => esc_html__('Name', 'jupiterx-core'),
					'slug'        => esc_html__('Slug', 'jupiterx-core'),
					'count'       => esc_html__('Count', 'jupiterx-core'),
					'term_id'     => esc_html__('Term ID', 'jupiterx-core'),
					'description' => esc_html__('Description', 'jupiterx-core'),
				],
				'condition' => [
					'loop_source' => 'terms',
				],
			]
		);

		$this->add_control(
			'query_meta_key',
			[
				'label'       => esc_html__('Meta key', 'jupiterx-core'),
				'description' => esc_html__('ACF and other plugins usually store custom fields as post meta. Enter the field key or meta key used in the database.', 'jupiterx-core'),
				'type'        => Controls_Manager::TEXT,
				'condition'   => [
					'query_orderby' => ['meta_value', 'meta_value_num'],
				],
			]
		);

		$this->add_control(
			'query_order',
			[
				'label'   => esc_html__('Order', 'jupiterx-core'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC'  => esc_html__('Ascending', 'jupiterx-core'),
					'DESC' => esc_html__('Descending', 'jupiterx-core'),
				],
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'loop_source',
							'operator' => '===',
							'value'    => 'terms',
						],
						[
							'name'     => 'query_orderby',
							'operator' => '!==',
							'value'    => 'rand',
						],
					],
				],
			]
		);

		$this->add_control(
			'query_offset',
			[
				'label'       => esc_html__('Offset', 'jupiterx-core'),
				'description' => esc_html__('Skip this many posts before collecting results.', 'jupiterx-core'),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 100,
			]
		);

		$this->add_control(
			'query_excludes',
			[
				'label'       => esc_html__('Exclude', 'jupiterx-core'),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => ['current_post'],
				'options'     => [
					'current_post'     => esc_html__('Current post', 'jupiterx-core'),
					'manual_selection' => esc_html__('Manual selection', 'jupiterx-core'),
				],
			]
		);

		$this->add_control(
			'query_excludes_ids',
			[
				'label'       => esc_html__('Posts to exclude', 'jupiterx-core'),
				'type'        => 'raven_query',
				'label_block' => true,
				'multiple'    => true,
				'condition'   => [
					'query_excludes' => 'manual_selection',
				],
				'query'       => [
					'source'        => Control_Query::QUERY_SOURCE_POST,
					'control_query' => [
						'post_type' => 'query_post_type',
					],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_pagination_controls()
	{
		$this->start_controls_section(
			'section_pagination',
			[
				'label'     => esc_html__('Pagination', 'jupiterx-core'),
				'condition' => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label'   => esc_html__('Type', 'jupiterx-core'),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''                      => esc_html__('None', 'jupiterx-core'),
					'numbers'               => esc_html__('Numbers', 'jupiterx-core'),
					'prev_next'             => esc_html__('Previous/Next', 'jupiterx-core'),
					'numbers_and_prev_next' => esc_html__('Numbers + Previous/Next', 'jupiterx-core'),
					'load_more'             => esc_html__('Load More', 'jupiterx-core'),
					'infinite_scroll'       => esc_html__('Infinite Scroll', 'jupiterx-core'),
				],
			]
		);

		$this->add_control(
			'load_more_text',
			[
				'label'     => esc_html__('Button text', 'jupiterx-core'),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__('Load More', 'jupiterx-core'),
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_control(
			'pagination_pages_visible',
			[
				'label'     => esc_html__('Pages Visible', 'jupiterx-core'),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 7,
				'min'       => 1,
				'max'       => 20,
				'condition' => [
					'pagination_type' => ['numbers', 'numbers_and_prev_next'],
				],
			]
		);

		$this->add_control(
			'pagination_prev_label',
			[
				'label'     => esc_html__('Previous Label', 'jupiterx-core'),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__('Previous', 'jupiterx-core'),
				'condition' => [
					'pagination_type' => ['prev_next', 'numbers_and_prev_next'],
				],
			]
		);

		$this->add_control(
			'pagination_next_label',
			[
				'label'     => esc_html__('Next Label', 'jupiterx-core'),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__('Next', 'jupiterx-core'),
				'condition' => [
					'pagination_type' => ['prev_next', 'numbers_and_prev_next'],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_loop_item_style_controls()
	{
		$this->start_controls_section(
			'section_loop_item_style',
			[
				'label'     => esc_html__('Loop Item', 'jupiterx-core'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_template_id!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'loop_item_background',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'loop_item_border',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__item',
			]
		);

		$this->add_responsive_control(
			'loop_item_border_radius',
			[
				'label'      => esc_html__('Border radius', 'jupiterx-core'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'loop_item_padding',
			[
				'label'      => esc_html__('Padding', 'jupiterx-core'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'loop_item_box_shadow',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__item',
			]
		);

		$this->end_controls_section();
	}

	protected function register_empty_message_style_controls()
	{
		$this->start_controls_section(
			'section_empty_message_style',
			[
				'label'     => esc_html__('No Results Message', 'jupiterx-core'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_empty_message' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'empty_message_alignment',
			[
				'label'     => esc_html__('Alignment', 'jupiterx-core'),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => [
					'left'   => [
						'title' => esc_html__('Left', 'jupiterx-core'),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__('Center', 'jupiterx-core'),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__('Right', 'jupiterx-core'),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-grid__empty' => 'display: block; width: 100%; text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'empty_message_typography',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__empty',
			]
		);

		$this->add_control(
			'empty_message_color',
			[
				'label'     => esc_html__('Color', 'jupiterx-core'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-grid__empty' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'empty_message_spacing',
			[
				'label'      => esc_html__('Spacing', 'jupiterx-core'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__empty' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_pagination_style_controls()
	{
		$this->start_controls_section(
			'section_pagination_style',
			[
				'label'     => esc_html__('Pagination', 'jupiterx-core'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'pagination_type!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_alignment',
			[
				'label'     => esc_html__('Alignment', 'jupiterx-core'),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => [
					'flex-start' => [
						'title' => esc_html__('Start', 'jupiterx-core'),
						'icon'  => 'eicon-text-align-left',
					],
					'center'     => [
						'title' => esc_html__('Center', 'jupiterx-core'),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end'   => [
						'title' => esc_html__('End', 'jupiterx-core'),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination, {{WRAPPER}} .jupiterx-loop-grid__load-more' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button',
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
						'default' => '500',
					],
					'line_height' => [
						'default' => [
							'size' => 1.2,
							'unit' => 'em',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_space_between',
			[
				'label'      => esc_html__('Space Between', 'jupiterx-core'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'default'    => [
					'size' => 6,
					'unit' => 'px',
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_item_padding',
			[
				'label'      => esc_html__('Padding', 'jupiterx-core'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', 'rem'],
				'default'    => [
					'top'      => 7,
					'right'    => 12,
					'bottom'   => 7,
					'left'     => 12,
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs('pagination_style_tabs');

		$this->start_controls_tab(
			'pagination_style_normal',
			[
				'label' => esc_html__('Normal', 'jupiterx-core'),
			]
		);

		$this->add_control(
			'pagination_color',
			[
				'label'     => esc_html__('Color', 'jupiterx-core'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#374151',
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pagination_background',
				'types'    => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button',
				'fields_options' => [
					'background' => [
						'label'   => esc_html__('Background Color Type', 'jupiterx-core'),
						'default' => 'classic',
					],
					'color'      => [
						'label'   => esc_html__('Background Color', 'jupiterx-core'),
						'default' => '#ffffff',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pagination_border',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => 1,
							'right'    => 1,
							'bottom'   => 1,
							'left'     => 1,
							'unit'     => 'px',
							'isLinked' => true,
						],
					],
					'color'  => [
						'default' => '#e5e7eb',
					],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'jupiterx-core'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'default'    => [
					'top'      => 6,
					'right'    => 6,
					'bottom'   => 6,
					'left'     => 6,
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pagination_box_shadow',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers, {{WRAPPER}} .jupiterx-loop-grid__load-more-button',
				'fields_options' => [
					'box_shadow_type' => [
						'default' => 'yes',
					],
					'box_shadow'      => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 2,
							'blur'       => 8,
							'spread'     => 0,
							'color'      => 'rgba(15, 23, 42, 0.08)',
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_style_active',
			[
				'label' => esc_html__('Active', 'jupiterx-core'),
			]
		);

		$this->add_control(
			'pagination_active_color',
			[
				'label'     => esc_html__('Color', 'jupiterx-core'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers.current' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pagination_active_background',
				'types'    => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers.current',
				'fields_options' => [
					'background' => [
						'label'   => esc_html__('Background Color Type', 'jupiterx-core'),
						'default' => 'classic',
					],
					'color'      => [
						'label'   => esc_html__('Background Color', 'jupiterx-core'),
						'default' => '#333333',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pagination_active_border',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers.current',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => 1,
							'right'    => 1,
							'bottom'   => 1,
							'left'     => 1,
							'unit'     => 'px',
							'isLinked' => true,
						],
					],
					'color'  => [
						'default' => '#333333',
					],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_active_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'jupiterx-core'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers.current' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pagination_active_box_shadow',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination span.page-numbers.current',
				'fields_options' => [
					'box_shadow_type' => [
						'default' => 'yes',
					],
					'box_shadow'      => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 3,
							'blur'       => 10,
							'spread'     => 0,
							'color'      => 'rgba(15, 23, 42, 0.12)',
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_style_hover',
			[
				'label' => esc_html__('Hover', 'jupiterx-core'),
			]
		);

		$this->add_control(
			'pagination_hover_color',
			[
				'label'     => esc_html__('Color', 'jupiterx-core'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pagination_hover_background',
				'types'    => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:focus',
				'fields_options' => [
					'background' => [
						'label'   => esc_html__('Background Color Type', 'jupiterx-core'),
						'default' => 'classic',
					],
					'color'      => [
						'label'   => esc_html__('Background Color', 'jupiterx-core'),
						'default' => '#f8fafc',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pagination_hover_border',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:focus',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => 1,
							'right'    => 1,
							'bottom'   => 1,
							'left'     => 1,
							'unit'     => 'px',
							'isLinked' => true,
						],
					],
					'color'  => [
						'default' => '#cbd5e1',
					],
				],
			]
		);

		$this->add_responsive_control(
			'pagination_hover_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'jupiterx-core'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:focus' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pagination_hover_box_shadow',
				'selector' => '{{WRAPPER}} .jupiterx-loop-grid__pagination a.page-numbers:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:hover, {{WRAPPER}} .jupiterx-loop-grid__load-more-button:focus',
				'fields_options' => [
					'box_shadow_type' => [
						'default' => 'yes',
					],
					'box_shadow'      => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 4,
							'blur'       => 12,
							'spread'     => 0,
							'color'      => 'rgba(15, 23, 42, 0.12)',
						],
					],
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pagination_spacing',
			[
				'label'      => esc_html__('Spacing', 'jupiterx-core'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .jupiterx-loop-grid__pagination, {{WRAPPER}} .jupiterx-loop-grid__load-more, {{WRAPPER}} .jupiterx-loop-grid__infinite-scroll' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_loop_post_type_options()
	{
		$post_types = get_post_types(['show_in_nav_menus' => true], 'objects');
		$excluded   = [
			'product',
			'jupiterx-popups',
			'elementor_library',
			'jupiterx-codes',
			'jupiterx-fonts',
			'jupiterx-icons',
			'sellkit_step',
		];
		$options    = [];

		foreach ($post_types as $post_type => $object) {
			if (in_array($post_type, $excluded, true)) {
				continue;
			}

			$options[$post_type] = $object->label;
		}

		$ordered = [];

		foreach (['post', 'page'] as $preferred) {
			if (isset($options[$preferred])) {
				$ordered[$preferred] = $options[$preferred];
				unset($options[$preferred]);
			}
		}

		$options = $ordered + $options;

		return ! empty($options) ? $options : ['post' => esc_html__('Posts', 'jupiterx-core')];
	}

	protected function get_create_loop_template_url()
	{
		if (! class_exists(Documents_Manager::class)) {
			return '';
		}

		return Documents_Manager::get_create_new_post_url('elementor_library', Jupiterx_Loop_Item_Document::SLUG);
	}

	protected function get_create_loop_template_button_html()
	{
		$url = $this->get_create_loop_template_url();

		if (empty($url)) {
			return '';
		}

		return sprintf(
			'<a class="elementor-button elementor-button-default" href="%1$s" target="_blank" rel="noopener">%2$s</a><p class="elementor-control-field-description">%3$s</p>',
			esc_url($url),
			esc_html__('Create Loop Item Template', 'jupiterx-core'),
			esc_html__('Opens a new tab. After creating and publishing the template, return here and search/select it.', 'jupiterx-core')
		);
	}

	/**
	 * Whether the selected library item is a JupiterX loop template.
	 *
	 * @param int $post_id Template post ID.
	 * @return bool
	 */
	protected function is_loop_template($post_id)
	{
		$post_id = absint($post_id);
		if (! $post_id) {
			return false;
		}

		$type = get_post_meta($post_id, Document::TYPE_META_KEY, true);

		return Jupiterx_Loop_Item_Document::SLUG === $type;
	}

	protected function get_taxonomy_options()
	{
		$options    = [];
		$taxonomies = get_taxonomies(['public' => true], 'objects');

		foreach ($taxonomies as $taxonomy) {
			$options[$taxonomy->name] = $taxonomy->label;
		}

		return $options;
	}

	/**
	 * Build WP_Query arguments for Loop Grid/Carousel post sources.
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @return array<string, mixed>
	 */
	protected function get_loop_posts_query_args(array $settings)
	{
		$post_type = ! empty($settings['query_post_type']) ? sanitize_key($settings['query_post_type']) : 'post';
		$per_page  = isset($settings['query_posts_per_page']) ? (int) $settings['query_posts_per_page'] : 6;

		$args = [
			'post_type'           => $post_type,
			'posts_per_page'      => 0 !== $per_page ? $per_page : -1,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => ! empty($settings['query_ignore_sticky_posts']) ? 1 : 0,
			'paged'               => $this->get_current_page($settings),
		];

		$includes_key = 'query_' . $post_type . '_includes';
		$includes     = Utils::normalize_query_ids($settings[$includes_key] ?? []);

		if (! empty($includes)) {
			$args['post__in'] = $includes;
			$args['orderby']  = 'post__in';

			return $args;
		}

		$authors = Utils::normalize_query_ids($settings['query_authors'] ?? []);

		if (! empty($authors)) {
			$args['author__in'] = $authors;
		}

		if (! empty($settings['query_excludes'])) {
			$excludes = (array) $settings['query_excludes'];
			$current_post_key = array_search('current_post', $excludes, true);

			if (false !== $current_post_key) {
				$excludes[$current_post_key] = get_the_ID();
			}

			$not_in = Utils::normalize_query_ids($excludes);

			if (! empty($settings['query_excludes_ids'])) {
				$not_in = array_merge($not_in, Utils::normalize_query_ids($settings['query_excludes_ids']));
			}

			if (! empty($not_in)) {
				$args['post__not_in'] = array_values(array_unique($not_in));
			}
		}

		$taxonomies = get_object_taxonomies($post_type, 'names');
		$tax_query  = [];

		foreach ($taxonomies as $taxonomy) {
			$terms = Utils::normalize_query_ids($settings['query_' . $taxonomy . '_ids'] ?? []);

			if (empty($terms)) {
				continue;
			}

			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $terms,
			];
		}

		if (count($tax_query) > 1) {
			$tax_query['relation'] = 'AND';
		}

		if (! empty($tax_query)) {
			$args['tax_query'] = $tax_query;
		}

		return $args;
	}

	/**
	 * @return \WP_Query
	 */
	protected function get_posts_query()
	{
		// Use raw settings for query args. Taxonomy controls rely on saved values and
		// must not go through get_settings_for_display() active-settings filtering.
		$settings = $this->get_query_settings();

		if (! empty($settings['is_archive_template']) && 'true' === $settings['is_archive_template']) {
			global $wp_query;

			$args = $this->apply_ajax_taxonomy_filter($wp_query->query_vars, $settings);

			$ppp = isset($settings['archive_posts_per_page']) ? (int) $settings['archive_posts_per_page'] : 0;
			if ($ppp) {
				$args['posts_per_page'] = $ppp;
			}

			$args['ignore_sticky_posts'] = true;

			if (! empty($settings['paged'])) {
				$args['paged'] = max(1, absint($settings['paged']));
			}

			$args = apply_filters('jupiterx_core_raven_loop_grid_query_args', $args, $settings, $this);
			$args = apply_filters('elementor/query/query_args', $args, $this);

			return $this->create_posts_query($args, $settings);
		}

		/** @var array<string, mixed> $args */
		$args = $this->get_loop_posts_query_args($settings);
		$args = $this->apply_ajax_taxonomy_filter($args, $settings);
		$args = $this->apply_posts_sort_args($args, $settings);

		$args = apply_filters('jupiterx_core_raven_loop_grid_query_args', $args, $settings, $this);
		$args = apply_filters('elementor/query/query_args', $args, $this);

		return $this->create_posts_query($args, $settings);
	}

	/**
	 * Apply Loop Grid post sort settings to WP_Query arguments.
	 *
	 * @param array<string, mixed> $args     Query arguments.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return array<string, mixed>
	 */
	protected function apply_posts_sort_args(array $args, array $settings)
	{
		$orderby = ! empty($settings['query_orderby']) ? sanitize_key($settings['query_orderby']) : 'date';
		$order   = ! empty($settings['query_order']) && 'ASC' === strtoupper($settings['query_order']) ? 'ASC' : 'DESC';

		$allowed_orderby = [
			'date',
			'title',
			'name',
			'modified',
			'menu_order',
			'rand',
			'meta_value',
			'meta_value_num',
			'ID',
			'comment_count',
			'post__in',
		];

		$orderby_map = [
			'slug' => 'name',
		];

		if (isset($orderby_map[$orderby])) {
			$orderby = $orderby_map[$orderby];
		}

		if (in_array($orderby, ['meta_value', 'meta_value_num'], true)) {
			if (! empty($settings['query_meta_key'])) {
				$args['orderby']  = $orderby;
				$args['meta_key'] = sanitize_key($settings['query_meta_key']);
			} else {
				$args['orderby'] = 'date';
				unset($args['meta_key']);
			}
		} elseif (in_array($orderby, $allowed_orderby, true)) {
			$args['orderby'] = $orderby;
			unset($args['meta_key']);
		} elseif (! empty($args['post__in'])) {
			$args['orderby'] = 'post__in';
			unset($args['meta_key']);
		} else {
			$args['orderby'] = 'date';
			unset($args['meta_key']);
		}

		if ('rand' !== $orderby) {
			$args['order'] = $order;
		}

		$offset = isset($settings['query_offset']) ? max(0, (int) $settings['query_offset']) : 0;

		unset($args['offset']);

		if ($offset > 0) {
			$args['offset_proper'] = $offset;
		} else {
			unset($args['offset_proper']);
		}

		return $args;
	}

	/**
	 * Run the custom Query ID action during pre_get_posts so $query->set() works.
	 *
	 * @param \WP_Query $query Query instance being built.
	 */
	public function pre_get_posts_query_action($query)
	{
		$settings = $this->get_settings();

		if (empty($settings['query_id'])) {
			return;
		}

		/**
		 * Elementor-compatible query action.
		 *
		 * Fires before the Loop Grid query runs. Use $query->set() to alter query vars.
		 *
		 * @param \WP_Query $query  Query instance.
		 * @param self      $widget Widget instance.
		 */
		do_action('elementor/query/' . sanitize_key($settings['query_id']), $query, $this);
	}

	/**
	 * Create a WP_Query and trigger Elementor-compatible query hooks.
	 *
	 * @param array<string, mixed> $args     Query arguments.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return \WP_Query
	 */
	protected function create_posts_query(array $args, array $settings)
	{
		if (! empty($settings['query_id'])) {
			add_action('pre_get_posts', [$this, 'pre_get_posts_query_action']);
		}

		$args  = $this->apply_sticky_posts_query_args($args, $settings);
		$query = new \WP_Query($args);

		if (! empty($settings['query_id'])) {
			remove_action('pre_get_posts', [$this, 'pre_get_posts_query_action']);
		}

		$this->apply_sticky_posts_order($query, $args, $settings);
		$this->apply_sticky_pagination_meta($query, $settings);
		$this->run_query_actions($query, $settings);

		return $query;
	}

	/**
	 * Adjust query args for sticky post behavior.
	 *
	 * When ignore sticky is enabled, matching sticky posts are excluded on all pages.
	 * When sticky ordering is enabled, sticky posts are excluded from page 2+ only.
	 *
	 * @param array<string, mixed> $args     Query arguments.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return array<string, mixed>
	 */
	protected function apply_sticky_posts_query_args(array $args, array $settings)
	{
		if ('post' !== ($args['post_type'] ?? 'post')) {
			return $args;
		}

		// Manual includes define an explicit list; do not alter pagination for stickies.
		if (! empty($args['post__in'])) {
			return $args;
		}

		$sticky_ids = $this->get_sticky_post_ids($args);

		if (empty($sticky_ids)) {
			return $args;
		}

		if (! empty($settings['query_ignore_sticky_posts'])) {
			// ignore_sticky_posts only stops front-page reordering; it does not remove stickies from results.
			$matching_sticky_ids = wp_list_pluck($this->get_matching_sticky_posts($sticky_ids, $args), 'ID');

			if (empty($matching_sticky_ids)) {
				return $args;
			}

			$not_in               = isset($args['post__not_in']) ? (array) $args['post__not_in'] : [];
			$args['post__not_in'] = array_values(array_unique(array_merge($not_in, $matching_sticky_ids)));

			return $args;
		}

		$pagination = $this->get_sticky_pagination_data($args, $settings);

		if (null === $pagination) {
			return $args;
		}

		$paged = max(1, (int) ($args['paged'] ?? 1));

		$args['jx_loop_grid_sticky_max_pages'] = $pagination['max_num_pages'];

		if ($paged > 1) {
			$not_in               = isset($args['post__not_in']) ? (array) $args['post__not_in'] : [];
			$args['post__not_in'] = array_values(array_unique(array_merge($not_in, $pagination['matching_sticky_ids'])));

			$base_offset = max(0, (int) ($args['offset_proper'] ?? 0));
			unset($args['offset_proper']);

			$sticky_offset = $pagination['regular_on_page1'] + ( $paged - 2 ) * $pagination['per_page'];
			$args['offset']  = (int) ( $args['offset'] ?? 0 ) + $base_offset + $sticky_offset;
			$args['paged']   = 1;

			if ($base_offset > 0) {
				$args['jx_loop_grid_fix_pagination_offset'] = $base_offset;
			}
		}

		return $args;
	}

	/**
	 * Calculate sticky pagination metadata for the current query.
	 *
	 * @param array<string, mixed> $args     Query arguments.
	 * @param array<string, mixed> $settings Widget settings.
	 * @return array<string, mixed>|null
	 */
	protected function get_sticky_pagination_data(array $args, array $settings)
	{
		if (null !== $this->sticky_pagination_cache) {
			return $this->sticky_pagination_cache;
		}

		if (! empty($settings['query_ignore_sticky_posts'])) {
			return null;
		}

		$sticky_ids = $this->get_sticky_post_ids($args);

		if (empty($sticky_ids)) {
			return null;
		}

		$matching_sticky     = $this->get_matching_sticky_posts($sticky_ids, $args);
		$matching_sticky_ids = wp_list_pluck($matching_sticky, 'ID');
		$sticky_count        = count($matching_sticky_ids);
		$per_page            = isset($args['posts_per_page']) ? (int) $args['posts_per_page'] : 0;

		if ($per_page <= 0) {
			return null;
		}

		$count_args = $args;
		$not_in     = isset($count_args['post__not_in']) ? (array) $count_args['post__not_in'] : [];

		$count_args['post__not_in']       = array_values(array_unique(array_merge($not_in, $matching_sticky_ids)));
		$count_args['fields']             = 'ids';
		$count_args['posts_per_page']     = 1;
		$count_args['paged']              = 1;
		$count_args['ignore_sticky_posts'] = 1;
		unset($count_args['offset'], $count_args['offset_proper']);

		$count_query   = new \WP_Query($count_args);
		$regular_total = (int) $count_query->found_posts;

		$regular_on_page1 = min($regular_total, max(0, $per_page - $sticky_count));
		$remaining        = max(0, $regular_total - $regular_on_page1);
		$max_pages        = $remaining > 0 ? 1 + (int) ceil($remaining / $per_page) : ( $sticky_count > 0 || $regular_on_page1 > 0 ? 1 : 0 );

		return $this->sticky_pagination_cache = [
			'sticky_count'        => $sticky_count,
			'matching_sticky_ids' => $matching_sticky_ids,
			'regular_total'       => $regular_total,
			'regular_on_page1'    => $regular_on_page1,
			'max_num_pages'       => max(1, $max_pages),
			'per_page'            => $per_page,
		];
	}

	/**
	 * @param \WP_Query            $query    Query instance.
	 * @param array<string, mixed> $settings Widget settings.
	 */
	protected function apply_sticky_pagination_meta(\WP_Query $query, array $settings)
	{
		$pagination = $this->get_sticky_pagination_data($query->query_vars, $settings);

		if (null === $pagination) {
			return;
		}

		$query->max_num_pages = $pagination['max_num_pages'];
	}

	/**
	 * @param array<string, mixed> $args Query arguments.
	 * @return int[]
	 */
	protected function get_sticky_post_ids(array $args)
	{
		$sticky_ids = array_filter(array_map('absint', (array) get_option('sticky_posts', [])));

		if (empty($sticky_ids)) {
			return [];
		}

		if (! empty($args['post__not_in'])) {
			$sticky_ids = array_diff($sticky_ids, array_map('absint', (array) $args['post__not_in']));
		}

		return array_values($sticky_ids);
	}

	/**
	 * Fetch sticky posts that match the current Loop Grid query constraints.
	 *
	 * @param int[]                $sticky_ids Sticky post IDs.
	 * @param array<string, mixed> $args       Base query arguments.
	 * @return \WP_Post[]
	 */
	protected function get_matching_sticky_posts(array $sticky_ids, array $args)
	{
		if (empty($sticky_ids)) {
			return [];
		}

		$sticky_args = $args;

		$sticky_args['post__in']            = $sticky_ids;
		$sticky_args['orderby']             = 'post__in';
		$sticky_args['posts_per_page']      = count($sticky_ids);
		$sticky_args['paged']               = 1;
		$sticky_args['ignore_sticky_posts'] = 1;

		unset($sticky_args['offset'], $sticky_args['offset_proper']);

		$sticky_query = new \WP_Query($sticky_args);

		return $sticky_query->posts;
	}

	/**
	 * Put sticky posts first on page 1 only, without exceeding posts_per_page.
	 *
	 * @param \WP_Query            $query    Query instance.
	 * @param array<string, mixed> $args     Query arguments.
	 * @param array<string, mixed> $settings Widget settings.
	 */
	protected function apply_sticky_posts_order(\WP_Query $query, array $args, array $settings)
	{
		if (! empty($settings['query_ignore_sticky_posts'])) {
			return;
		}

		if ('post' !== ($args['post_type'] ?? 'post')) {
			return;
		}

		if (! empty($args['post__in'])) {
			return;
		}

		$per_page = isset($args['posts_per_page']) ? (int) $args['posts_per_page'] : 0;

		if ($per_page <= 0) {
			return;
		}

		$paged      = max(1, (int) ($args['paged'] ?? 1));
		$sticky_ids = $this->get_sticky_post_ids($args);

		if ($paged > 1) {
			if (! empty($sticky_ids)) {
				$query->posts = array_values(array_filter(
					$query->posts,
					static function ($post) use ($sticky_ids) {
						return ! in_array((int) $post->ID, $sticky_ids, true);
					}
				));
				$query->post_count = count($query->posts);
				$query->rewind_posts();
			}

			return;
		}

		$sticky_posts = $this->get_matching_sticky_posts($sticky_ids, $args);
		$sticky_ids   = wp_list_pluck($sticky_posts, 'ID');

		if (count($sticky_posts) > $per_page) {
			$sticky_posts = array_slice($sticky_posts, 0, $per_page);
			$sticky_ids   = wp_list_pluck($sticky_posts, 'ID');
		}

		$regular = [];

		foreach ($query->posts as $post) {
			if (! in_array((int) $post->ID, $sticky_ids, true)) {
				$regular[] = $post;
			}
		}

		$slots_for_regular = max(0, $per_page - count($sticky_posts));
		$regular           = array_slice($regular, 0, $slots_for_regular);

		$query->posts      = array_merge($sticky_posts, $regular);
		$query->post_count = count($query->posts);
		$query->rewind_posts();
	}

	/**
	 * Trigger Elementor-compatible query hooks after the WP_Query is created.
	 *
	 * @param \WP_Query $query    Query.
	 * @param array     $settings Widget settings.
	 */
	protected function run_query_actions($query, $settings)
	{
		/**
		 * Fires after the Loop Grid query runs.
		 *
		 * @param \WP_Query $query  Query instance.
		 * @param self      $widget Widget instance.
		 */
		do_action('elementor/query/query_results', $query, $this);
	}

	protected function apply_ajax_taxonomy_filter(array $args, array $settings)
	{
		if (empty($settings['ajax_filter_taxonomy']) || empty($settings['ajax_filter_term_id'])) {
			return $args;
		}

		$taxonomy = sanitize_key($settings['ajax_filter_taxonomy']);

		if (! taxonomy_exists($taxonomy)) {
			return $args;
		}

		$args['tax_query']   = isset($args['tax_query']) && is_array($args['tax_query']) ? $args['tax_query'] : [];
		$args['tax_query'][] = [
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => [absint($settings['ajax_filter_term_id'])],
		];

		return $args;
	}

	protected function is_terms_source(array $settings)
	{
		return ! empty($settings['loop_source']) && 'terms' === $settings['loop_source'];
	}

	protected function get_current_page(array $settings)
	{
		if (! empty($settings['paged'])) {
			return max(1, absint($settings['paged']));
		}

		return max(1, get_query_var('paged'), get_query_var('page'));
	}

	/**
	 * Settings used for frontend/AJAX queries.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_query_settings()
	{
		$settings = $this->get_settings();

		if (empty($settings['paged'])) {
			$settings['paged'] = $this->get_current_page($settings);
		}

		return $settings;
	}

	protected function get_terms_query_result(array $settings)
	{
		$taxonomy = ! empty($settings['terms_taxonomy']) ? sanitize_key($settings['terms_taxonomy']) : 'category';

		if (! taxonomy_exists($taxonomy)) {
			return (object) [
				'items'         => [],
				'total'         => 0,
				'max_num_pages' => 0,
			];
		}

		$per_page = isset($settings['terms_per_page']) ? (int) $settings['terms_per_page'] : 6;
		$page     = $this->get_current_page($settings);
		$offset   = $this->get_terms_query_offset($settings);
		$args     = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => ! empty($settings['terms_hide_empty']) && 'yes' === $settings['terms_hide_empty'],
			'orderby'    => $this->get_terms_orderby($settings),
			'order'      => ! empty($settings['query_order']) && 'ASC' === strtoupper($settings['query_order']) ? 'ASC' : 'DESC',
		];

		if ('' !== (string) ($settings['terms_parent'] ?? '')) {
			$args['parent'] = absint($settings['terms_parent']);
		}

		foreach (['terms_include' => 'include', 'terms_exclude' => 'exclude'] as $setting_key => $query_key) {
			if (! empty($settings[$setting_key])) {
				$args[$query_key] = array_filter(array_map('absint', (array) $settings[$setting_key]));
			}
		}

		$count_args           = $args;
		$count_args['fields'] = 'count';
		$total_terms          = get_terms($count_args);
		$total                = is_wp_error($total_terms) ? 0 : (int) $total_terms;
		$total                = max(0, $total - $offset);

		if ($per_page > 0) {
			$args['number'] = $per_page;
			$args['offset'] = $offset + (($page - 1) * $per_page);
		} elseif ($offset > 0) {
			$args['offset'] = $offset;
		}

		$terms = get_terms($args);

		if (is_wp_error($terms)) {
			$terms = [];
		}

		return (object) [
			'items'         => $terms,
			'total'         => $total,
			'max_num_pages' => $per_page > 0 ? (int) ceil($total / $per_page) : 1,
		];
	}

	protected function get_terms_orderby(array $settings)
	{
		$orderby = ! empty($settings['terms_query_orderby'])
			? sanitize_key($settings['terms_query_orderby'])
			: (! empty($settings['query_orderby']) ? sanitize_key($settings['query_orderby']) : 'name');

		$allowed = ['name', 'slug', 'term_id', 'id', 'count', 'description', 'parent', 'none'];

		if ('title' === $orderby) {
			return 'name';
		}

		if ('date' === $orderby || ! in_array($orderby, $allowed, true)) {
			return 'name';
		}

		return $orderby;
	}

	protected function get_terms_query_offset(array $settings)
	{
		return isset($settings['query_offset']) ? max(0, (int) $settings['query_offset']) : 0;
	}

	protected function get_item_number_offset(array $settings)
	{
		$page = $this->get_current_page($settings);

		if ($page <= 1) {
			return 0;
		}

		if ($this->is_terms_source($settings)) {
			$per_page = isset($settings['terms_per_page']) ? (int) $settings['terms_per_page'] : 0;
		} elseif (! empty($settings['is_archive_template']) && 'true' === $settings['is_archive_template']) {
			$per_page = isset($settings['archive_posts_per_page']) ? (int) $settings['archive_posts_per_page'] : 0;
		} else {
			$per_page = isset($settings['query_posts_per_page']) ? (int) $settings['query_posts_per_page'] : 0;
		}

		return $per_page > 0 ? ($page - 1) * $per_page : 0;
	}

	/**
	 * Find the alternate template that applies to the current item number.
	 *
	 * @param array $settings Widget settings.
	 * @param int   $item_number One-based item number.
	 * @return array|null
	 */
	protected function get_alternate_template_for_item($settings, $item_number)
	{
		if (
			empty($settings['alternate_template']) ||
			'yes' !== $settings['alternate_template'] ||
			empty($settings['alternate_templates']) ||
			! is_array($settings['alternate_templates'])
		) {
			return null;
		}

		foreach ($settings['alternate_templates'] as $template) {
			$template_id = ! empty($template['template_id']) ? absint($template['template_id']) : 0;
			$position    = ! empty($template['repeat_template']) ? absint($template['repeat_template']) : 0;

			if (! $template_id || ! $position || ! $this->is_loop_template($template_id)) {
				continue;
			}

			$is_static_position = ! empty($template['static_position']) && 'yes' === $template['static_position'];
			$applies_once       = ! empty($template['show_once']) && 'yes' === $template['show_once'];

			if ($is_static_position || $applies_once) {
				if ($item_number === $position) {
					return $template;
				}

				continue;
			}

			if (0 === $item_number % $position) {
				return $template;
			}
		}

		return null;
	}

	/**
	 * @param array $template Alternate template configuration.
	 * @return int
	 */
	protected function get_alternate_column_span($template)
	{
		$span = ! empty($template['column_span']) ? absint($template['column_span']) : 1;

		return min(12, max(1, $span));
	}

	/**
	 * @param string $tag Requested tag.
	 * @return string
	 */
	protected function get_safe_empty_message_tag($tag)
	{
		$allowed = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p'];

		return in_array($tag, $allowed, true) ? $tag : 'div';
	}

	/**
	 * @param \WP_Query $query       Query.
	 * @param array     $settings    Widget settings.
	 * @param int       $template_id Default loop item template.
	 */
	protected function render_loop_items($query, $settings, $template_id, $before_item = '', $after_item = '')
	{
		$frontend    = \Elementor\Plugin::$instance->frontend;
		$item_number = $this->get_item_number_offset($settings);

		while ($query->have_posts()) {
			$query->the_post();
			++$item_number;

			$item_template_id = $template_id;
			$column_span      = 1;
			$alternate        = $this->get_alternate_template_for_item($settings, $item_number);

			if ($alternate) {
				$item_template_id = absint($alternate['template_id']);
				$column_span      = $this->get_alternate_column_span($alternate);
			}

			$item_classes = [
				'jupiterx-loop-grid__item',
				'jupiterx-loop-item',
				'jupiterx-loop-item-' . get_the_ID(),
			];

			if ($alternate) {
				$item_classes[] = 'jupiterx-loop-grid__item--alternate';
			}

			$style = $column_span > 1 && (empty($settings['masonry']) || 'yes' !== $settings['masonry'])
				? ' style="grid-column: span min(' . esc_attr((string) $column_span) . ', var(--jx-loop-columns));"'
				: '';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $before_item;
			echo '<article class="' . esc_attr(implode(' ', $item_classes)) . '"' . $style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Loop_Grid_Module::enter_embedded_loop_item_render();
			try {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $frontend->get_builder_content_for_display($item_template_id, true);
			} finally {
				Loop_Grid_Module::leave_embedded_loop_item_render();
			}
			echo '</article>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $after_item;
		}

		wp_reset_postdata();
	}

	protected function render_loop_terms($term_result, array $settings, $template_id, $before_item = '', $after_item = '')
	{
		if (empty($term_result->items) || ! is_array($term_result->items)) {
			return;
		}

		$item_number = $this->get_item_number_offset($settings);

		foreach ($term_result->items as $term) {
			if (! $term instanceof \WP_Term) {
				continue;
			}

			$item_template_id = $template_id;
			++$item_number;
			$column_span      = 1;
			$alternate        = $this->get_alternate_template_for_item($settings, $item_number);

			if ($alternate) {
				$item_template_id = absint($alternate['template_id']);
				$column_span      = $this->get_alternate_column_span($alternate);
			}

			$item_classes = [
				'jupiterx-loop-grid__item',
				'jupiterx-loop-item',
				'jupiterx-loop-term-item',
				'jupiterx-loop-term-item-' . $term->term_id,
				'jupiterx-loop-term-' . sanitize_html_class($term->taxonomy),
			];

			if ($alternate) {
				$item_classes[] = 'jupiterx-loop-grid__item--alternate';
			}

			$style = $column_span > 1 && (empty($settings['masonry']) || 'yes' !== $settings['masonry'])
				? ' style="grid-column: span min(' . esc_attr((string) $column_span) . ', var(--jx-loop-columns));"'
				: '';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $before_item;
			echo '<article class="' . esc_attr(implode(' ', $item_classes)) . '"' . $style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->render_with_loop_term_context($term, $item_template_id);
			echo '</article>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $after_item;
		}
	}

	protected function render_with_loop_term_context(\WP_Term $term, $template_id)
	{
		global $wp_query;

		$previous_term           = self::$current_loop_term;
		$previous_queried_object = isset($wp_query->queried_object) ? $wp_query->queried_object : null;
		$previous_queried_id     = isset($wp_query->queried_object_id) ? $wp_query->queried_object_id : null;
		$previous_loop_term      = isset($wp_query->loop_term) ? $wp_query->loop_term : null;

		self::$current_loop_term   = $term;
		$wp_query->queried_object  = $term;
		$wp_query->queried_object_id = $term->term_id;
		$wp_query->loop_term       = $term;

		Loop_Grid_Module::enter_embedded_loop_item_render();
		try {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id, true);
		} finally {
			Loop_Grid_Module::leave_embedded_loop_item_render();
		}

		self::$current_loop_term = $previous_term;

		if (null === $previous_queried_object) {
			unset($wp_query->queried_object);
		} else {
			$wp_query->queried_object = $previous_queried_object;
		}

		if (null === $previous_queried_id) {
			unset($wp_query->queried_object_id);
		} else {
			$wp_query->queried_object_id = $previous_queried_id;
		}

		if (null === $previous_loop_term) {
			unset($wp_query->loop_term);
		} else {
			$wp_query->loop_term = $previous_loop_term;
		}
	}

	protected function is_editor_preview()
	{
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	protected function render_editor_placeholder_card($index)
	{
?>
		<article class="jupiterx-loop-grid__item jupiterx-loop-item jupiterx-loop-grid__item--placeholder">
			<div class="jupiterx-loop-placeholder-card">
				<div class="jupiterx-loop-placeholder-card__media"></div>
				<div class="jupiterx-loop-placeholder-card__content">
					<div class="jupiterx-loop-placeholder-card__title">
						<?php
						printf(
							/* translators: %d: Placeholder item number. */
							esc_html__('Loop Item Preview %d', 'jupiterx-core'),
							absint($index)
						);
						?>
					</div>
					<div class="jupiterx-loop-placeholder-card__line"></div>
					<div class="jupiterx-loop-placeholder-card__line jupiterx-loop-placeholder-card__line--short"></div>
				</div>
			</div>
		</article>
	<?php
	}

	protected function render_editor_placeholder_grid(array $settings)
	{
		$this->add_render_attribute('grid', 'class', 'jupiterx-loop-grid');

		if (! empty($settings['equal_height']) && 'yes' === $settings['equal_height']) {
			$this->add_render_attribute('grid', 'class', 'jupiterx-loop-grid--equal-height');
		}

		if (! empty($settings['masonry']) && 'yes' === $settings['masonry']) {
			$this->add_render_attribute('grid', 'class', 'jupiterx-loop-grid--masonry');
		}

		echo '<div ' . $this->get_render_attribute_string('grid') . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		for ($i = 1; $i <= 3; $i++) {
			$this->render_editor_placeholder_card($i);
		}
		echo '</div>';
	}

	protected function render_editor_placeholder_carousel()
	{
	?>
		<div class="jupiterx-loop-carousel raven-swiper">
			<div class="raven-main-swiper swiper">
				<div class="swiper-wrapper">
					<?php for ($i = 1; $i <= 3; $i++) : ?>
						<div class="swiper-slide">
							<?php $this->render_editor_placeholder_card($i); ?>
						</div>
					<?php endfor; ?>
				</div>
				<div class="swiper-pagination"></div>
				<div class="elementor-swiper-button elementor-swiper-button-prev">
					<i class="eicon-chevron-left" aria-hidden="true"></i>
					<span class="elementor-screen-only"><?php echo esc_html__('Previous', 'jupiterx-core'); ?></span>
				</div>
				<div class="elementor-swiper-button elementor-swiper-button-next">
					<i class="eicon-chevron-right" aria-hidden="true"></i>
					<span class="elementor-screen-only"><?php echo esc_html__('Next', 'jupiterx-core'); ?></span>
				</div>
			</div>
		</div>
<?php
	}

	public function render_ajax_items()
	{
		$settings    = $this->get_query_settings();
		$template_id = $this->get_loop_template_id( $settings );

		if (! $template_id || ! $this->is_loop_template($template_id)) {
			return [
				'html'        => '',
				'currentPage' => $this->get_current_page($settings),
				'maxPages'    => 1,
			];
		}

		if ($this->is_terms_source($settings)) {
			$query    = $this->get_terms_query_result($settings);
			$max_pages = (int) $query->max_num_pages;
		} else {
			$query     = $this->get_posts_query();
			$max_pages = (int) $query->max_num_pages;
		}

		ob_start();

		if ($this->is_terms_source($settings)) {
			$this->render_loop_terms($query, $settings, $template_id);
		} else {
			$this->render_loop_items($query, $settings, $template_id);
		}

		return [
			'html'        => ob_get_clean(),
			'currentPage' => $this->get_current_page($settings),
			'maxPages'    => max(1, $max_pages),
		];
	}

	/**
	 * @param \WP_Query $query Query.
	 * @param array     $settings Widget settings.
	 */
	protected function render_pagination($query, $settings)
	{
		if (empty($settings['pagination_type']) || $query->max_num_pages <= 1) {
			return;
		}

		$type      = $settings['pagination_type'];
		if (in_array($type, ['load_more', 'infinite_scroll'], true)) {
			$max_pages = 1;

			if ($query instanceof \WP_Query) {
				$max_pages = (int) $query->max_num_pages;
			} elseif (is_object($query) && isset($query->max_num_pages)) {
				$max_pages = (int) $query->max_num_pages;
			}

			$config = [
				'postId'      => get_the_ID(),
				'widgetId'    => $this->get_id(),
				'currentPage' => 1,
				'maxPages'    => max(1, $max_pages),
			];

			if ('load_more' === $type) {
				echo '<div class="jupiterx-loop-grid__load-more" data-settings="' . esc_attr(wp_json_encode($config)) . '">';
				echo '<button type="button" class="jupiterx-loop-grid__load-more-button">';
				echo esc_html(! empty($settings['load_more_text']) ? $settings['load_more_text'] : esc_html__('Load More', 'jupiterx-core'));
				echo '</button>';
				echo '</div>';
				return;
			}

			echo '<div class="jupiterx-loop-grid__infinite-scroll" data-settings="' . esc_attr(wp_json_encode($config)) . '"></div>';
			return;
		}

		$show_prev = in_array($type, ['prev_next', 'numbers_and_prev_next'], true);
		$show_nums = in_array($type, ['numbers', 'numbers_and_prev_next'], true);
		$base      = esc_url_raw(str_replace(PHP_INT_MAX, '%#%', get_pagenum_link(PHP_INT_MAX)));
		$current   = max(1, get_query_var('paged'), get_query_var('page'));
		$visible   = ! empty($settings['pagination_pages_visible']) ? absint($settings['pagination_pages_visible']) : 7;
		$end_size  = $show_nums && $visible > 1 ? 1 : 0;
		$mid_size  = $show_nums ? max(0, (int) floor(($visible - (2 * $end_size) - 1) / 2)) : 0;

		if (! $base) {
			global $wp_rewrite;
			$base = user_trailingslashit(trailingslashit(get_permalink()) . "{$wp_rewrite->pagination_base}/%#%/");
		}

		$prev_label = ! empty($settings['pagination_prev_label']) ? esc_html($settings['pagination_prev_label']) : esc_html__('Previous', 'jupiterx-core');
		$next_label = ! empty($settings['pagination_next_label']) ? esc_html($settings['pagination_next_label']) : esc_html__('Next', 'jupiterx-core');

		if ('prev_next' === $type) {
			$links = [];

			if ($current > 1) {
				$links[] = '<li><a class="prev page-numbers" href="' . esc_url(get_pagenum_link($current - 1)) . '">' . $prev_label . '</a></li>';
			}

			if ($current < (int) $query->max_num_pages) {
				$links[] = '<li><a class="next page-numbers" href="' . esc_url(get_pagenum_link($current + 1)) . '">' . $next_label . '</a></li>';
			}

			$pagination = ! empty($links) ? '<ul class="page-numbers">' . implode('', $links) . '</ul>' : '';
		} else {
			$pagination = paginate_links(
				[
					'base'      => $base,
					'format'    => '',
					'current'   => $current,
					'total'     => $query->max_num_pages,
					'type'      => 'list',
					'mid_size'  => $mid_size,
					'end_size'  => $end_size,
					'prev_next' => $show_prev,
					'prev_text' => $prev_label,
					'next_text' => $next_label,
				]
			);
		}

		if ($pagination) {
			echo '<nav class="jupiterx-loop-grid__pagination" aria-label="' . esc_attr__('Pagination', 'jupiterx-core') . '">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $pagination;
			echo '</nav>';
		}
	}

	protected function render()
	{
		if ( Loop_Grid_Module::should_suppress_nested_loop_widgets() ) {
			return;
		}

		$settings    = $this->get_settings_for_display();
		$template_id = $this->get_loop_template_id( $settings );

		if (! $template_id || ! $this->is_loop_template($template_id)) {
			if ($this->is_editor_preview()) {
				$this->render_editor_placeholder_grid($settings);
				return;
			}

			if (current_user_can('edit_posts')) {
				echo '<div class="elementor-alert elementor-alert-info jupiterx-loop-grid__empty">';
				echo esc_html__('Select a published JupiterX Loop Item template.', 'jupiterx-core');
				echo '</div>';
			}
			return;
		}

		$query_settings = $this->get_query_settings();
		$query = $this->is_terms_source($settings) ? $this->get_terms_query_result($query_settings) : $this->get_posts_query();

		$this->add_render_attribute('grid', 'class', 'jupiterx-loop-grid');

		if (! empty($settings['equal_height']) && 'yes' === $settings['equal_height']) {
			$this->add_render_attribute('grid', 'class', 'jupiterx-loop-grid--equal-height');
		}

		if (! empty($settings['masonry']) && 'yes' === $settings['masonry']) {
			$this->add_render_attribute('grid', 'class', 'jupiterx-loop-grid--masonry');
		}

		$has_items = $this->is_terms_source($settings) ? ! empty($query->items) : $query->have_posts();

		if (! $has_items) {
			if ($this->is_editor_preview()) {
				$this->render_editor_placeholder_grid($settings);
				return;
			}

			if (! empty($settings['enable_empty_message']) && 'yes' === $settings['enable_empty_message'] && ! empty($settings['empty_message'])) {
				$empty_tag = $this->get_safe_empty_message_tag($settings['empty_message_html_tag'] ?? 'div');
				echo '<' . tag_escape($empty_tag) . ' class="jupiterx-loop-grid__empty">';
				echo esc_html($settings['empty_message']);
				echo '</' . tag_escape($empty_tag) . '>';
			}
			return;
		}

		echo '<div ' . $this->get_render_attribute_string('grid') . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ($this->is_terms_source($settings)) {
			$this->render_loop_terms($query, $settings, $template_id);
		} else {
			$this->render_loop_items($query, $settings, $template_id);
		}

		echo '</div>';

		$this->render_pagination($query, $settings);
	}
}

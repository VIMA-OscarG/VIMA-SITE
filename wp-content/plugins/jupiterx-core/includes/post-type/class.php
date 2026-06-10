<?php
defined( 'ABSPATH' ) || die();
/**
 * Class to register Jupiter post types and custom taxonomies.
 *
 * @package JupiterX_Core\Post_Type
 *
 * @since 1.0.0
 */

/**
 * Handle the Jupiter Portfolio post type.
 *
 * @since 1.0.0
 *
 * @package JupiterX_Core\Post_Type
 */
final class JupiterX_Portfolio {

	/**
	 * Rewrite signature option key.
	 *
	 * @since 4.8.0
	 *
	 * @var string
	 */
	const REWRITE_SIGNATURE_OPTION = 'jupiterx_portfolio_rewrite_signature';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		if ( class_exists( 'acf' ) || jupiterx_core()->check_default_settings() ) {
			$post_types = [
				'post' => '',
				'portfolio' => 'portfolio_',
			];

			foreach ( $post_types as $post_type ) {
				add_filter( "manage_edit-{$post_type}category_columns", [ $this, 'taxonomy_category_order_heading' ] );
				add_action( "manage_{$post_type}category_custom_column", [ $this, 'taxonomy_category_order_content' ], 10, 3 );
			}
		}
	}

	/**
	 * Register post type.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_post_type() {
		$settings = $this->get_portfolio_settings();

		$labels = [
			'name'               => $settings['plural_label'],
			'singular_name'      => $settings['singular_label'],
			'menu_name'          => $settings['plural_label'],
			'name_admin_bar'     => $settings['singular_label'],
			'all_items'          => sprintf( esc_html__( 'All %s', 'jupiterx-core' ), $settings['plural_label'] ),
			'add_new'            => sprintf( esc_html__( 'Add New %s', 'jupiterx-core' ), $settings['singular_label'] ),
			'add_new_item'       => sprintf( esc_html__( 'Add New %s', 'jupiterx-core' ), $settings['singular_label'] ),
			'edit_item'          => sprintf( esc_html__( 'Edit %s', 'jupiterx-core' ), $settings['singular_label'] ),
			'new_item'           => sprintf( esc_html__( 'New %s', 'jupiterx-core' ), $settings['singular_label'] ),
			'view_item'          => sprintf( esc_html__( 'View %s', 'jupiterx-core' ), $settings['singular_label'] ),
			'search_items'       => sprintf( esc_html__( 'Search %s', 'jupiterx-core' ), $settings['plural_label'] ),
			'not_found'          => sprintf( esc_html__( 'No %s found', 'jupiterx-core' ), strtolower( $settings['plural_label'] ) ),
			'not_found_in_trash' => sprintf( esc_html__( 'No %s found in Trash', 'jupiterx-core' ), strtolower( $settings['plural_label'] ) ),
		];

		/**
		 * Filter portfolio post type arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The post type arguments.
		 */
		$args = apply_filters( 'jupiterx_portfolio_args', [
			'label'         => $settings['singular_label'],
			'description'   => sprintf( esc_html__( '%s Description', 'jupiterx-core' ), $settings['singular_label'] ),
			'labels'        => $labels,
			'supports'      => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'trackbacks', 'revisions', 'custom_fields', 'page-attributes' ],
			'public'        => true,
			'menu_position' => 5,
			'can_export'    => true,
			'has_archive'   => true,
			'rewrite'       => [ 'slug' => $settings['slug'] ],
			'show_in_rest'  => true,
		] );

		if ( 'hidden' === $settings['mode'] ) {
			$args = $this->get_hidden_post_type_args( $args );
		}

		register_post_type( 'portfolio', $args );
		$this->maybe_flush_rewrite_rules( $settings );
	}

	/**
	 * Call taxonomies registration.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$this->register_category_taxonomy();
		$this->register_tag_taxonomy();
	}

	/**
	 * Register category taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function register_category_taxonomy() {
		$settings = $this->get_portfolio_settings();

		$labels = array(
			'name'                       => jupiterx_core_get_portfolio_label( 'categories' ),
			'singular_name'              => _x( 'Category', 'Category Singular Name', 'jupiterx-core' ),
			'menu_name'                  => jupiterx_core_get_portfolio_label( 'categories' ),
			'all_items'                  => sprintf( esc_html__( 'All %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'categories' ) ),
			'parent_item'                => esc_html__( 'Parent Category', 'jupiterx-core' ),
			'parent_item_colon'          => esc_html__( 'Parent Category:', 'jupiterx-core' ),
			'new_item_name'              => esc_html__( 'New Category Name', 'jupiterx-core' ),
			'add_new_item'               => sprintf( esc_html__( 'Add New %s', 'jupiterx-core' ), esc_html__( 'Category', 'jupiterx-core' ) ),
			'edit_item'                  => sprintf( esc_html__( 'Edit %s', 'jupiterx-core' ), esc_html__( 'Category', 'jupiterx-core' ) ),
			'update_item'                => sprintf( esc_html__( 'Update %s', 'jupiterx-core' ), esc_html__( 'Category', 'jupiterx-core' ) ),
			'view_item'                  => sprintf( esc_html__( 'View %s', 'jupiterx-core' ), esc_html__( 'Category', 'jupiterx-core' ) ),
			'separate_items_with_commas' => esc_html__( 'Separate categories with commas', 'jupiterx-core' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove categories', 'jupiterx-core' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'jupiterx-core' ),
			'popular_items'              => sprintf( esc_html__( 'Popular %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'categories' ) ),
			'search_items'               => sprintf( esc_html__( 'Search %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'categories' ) ),
			'not_found'                  => esc_html__( 'Not Found', 'jupiterx-core' ),
			'no_terms'                   => sprintf( esc_html__( 'No %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'categories' ) ),
			'items_list'                 => sprintf( esc_html__( '%s list', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'categories' ) ),
			'items_list_navigation'      => sprintf( esc_html__( '%s list navigation', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'categories' ) ),
		);

		/**
		 * Filter portfolio category taxonomy arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The taxonomy arguments.
		 */
		$args = apply_filters( 'jupiterx_portfolio_category_args', [
			'labels'       => $labels,
			'rewrite'      => [ 'slug' => 'portfolio-category' ],
			'hierarchical' => true,
			'show_in_rest' => true,
		] );

		if ( 'hidden' === $settings['mode'] ) {
			$args = $this->get_hidden_taxonomy_args( $args );
		}

		register_taxonomy( 'portfolio_category', 'portfolio', $args );
	}

	/**
	 * Register tag taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function register_tag_taxonomy() {
		$settings = $this->get_portfolio_settings();

		$labels = array(
			'name'                       => jupiterx_core_get_portfolio_label( 'tags' ),
			'singular_name'              => _x( 'Tag', 'Tag Singular Name', 'jupiterx-core' ),
			'menu_name'                  => jupiterx_core_get_portfolio_label( 'tags' ),
			'all_items'                  => sprintf( esc_html__( 'All %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'tags' ) ),
			'parent_item'                => esc_html__( 'Parent Tag', 'jupiterx-core' ),
			'parent_item_colon'          => esc_html__( 'Parent Tag:', 'jupiterx-core' ),
			'new_item_name'              => esc_html__( 'New Tag Name', 'jupiterx-core' ),
			'add_new_item'               => sprintf( esc_html__( 'Add New %s', 'jupiterx-core' ), esc_html__( 'Tag', 'jupiterx-core' ) ),
			'edit_item'                  => sprintf( esc_html__( 'Edit %s', 'jupiterx-core' ), esc_html__( 'Tag', 'jupiterx-core' ) ),
			'update_item'                => sprintf( esc_html__( 'Update %s', 'jupiterx-core' ), esc_html__( 'Tag', 'jupiterx-core' ) ),
			'view_item'                  => sprintf( esc_html__( 'View %s', 'jupiterx-core' ), esc_html__( 'Tag', 'jupiterx-core' ) ),
			'separate_items_with_commas' => esc_html__( 'Separate tags with commas', 'jupiterx-core' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove tags', 'jupiterx-core' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'jupiterx-core' ),
			'popular_items'              => sprintf( esc_html__( 'Popular %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'tags' ) ),
			'search_items'               => sprintf( esc_html__( 'Search %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'tags' ) ),
			'not_found'                  => esc_html__( 'Not Found', 'jupiterx-core' ),
			'no_terms'                   => sprintf( esc_html__( 'No %s', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'tags' ) ),
			'items_list'                 => sprintf( esc_html__( '%s list', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'tags' ) ),
			'items_list_navigation'      => sprintf( esc_html__( '%s list navigation', 'jupiterx-core' ), jupiterx_core_get_portfolio_label( 'tags' ) ),
		);

		/**
		 * Filter portfolio tag taxonomy arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The taxonomy arguments.
		 */
		$args = apply_filters( 'jupiterx_portfolio_tag_args', [
			'labels'       => $labels,
			'rewrite'      => [ 'slug' => 'portfolio-tag' ],
			'show_in_rest' => true,
		] );

		if ( 'hidden' === $settings['mode'] ) {
			$args = $this->get_hidden_taxonomy_args( $args );
		}

		register_taxonomy( 'portfolio_tag', 'portfolio', $args );
	}

	/**
	 * Get portfolio settings from options.
	 *
	 * @since 4.8.0
	 *
	 * @return array
	 */
	private function get_portfolio_settings() {
		$mode = strtolower( trim( (string) $this->get_jupiterx_option( 'portfolio_post_type_mode', 'enabled' ) ) );

		if ( ! in_array( $mode, [ 'enabled', 'hidden' ], true ) ) {
			$mode = 'enabled';
		}

		$slug = sanitize_title( (string) $this->get_jupiterx_option( 'portfolio_post_type_slug', 'portfolio' ) );

		if ( empty( $slug ) ) {
			$slug = 'portfolio';
		}

		$singular_label = sanitize_text_field( (string) $this->get_jupiterx_option( 'portfolio_singular_label', 'Portfolio' ) );
		$plural_label   = sanitize_text_field( (string) $this->get_jupiterx_option( 'portfolio_plural_label', 'Portfolios' ) );

		if ( empty( $singular_label ) ) {
			$singular_label = 'Portfolio';
		}

		if ( empty( $plural_label ) ) {
			$plural_label = 'Portfolios';
		}

		return apply_filters( 'jupiterx_portfolio_settings', [
			'mode'           => $mode,
			'slug'           => $slug,
			'singular_label' => $singular_label,
			'plural_label'   => $plural_label,
		] );
	}

	/**
	 * Read an option from Jupiter option storage.
	 *
	 * @since 4.8.0
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed
	 */
	private function get_jupiterx_option( $key, $default = false ) {
		$options = get_option( 'jupiterx', [] );

		if ( ! isset( $options[ $key ] ) ) {
			return $default;
		}

		return $options[ $key ];
	}

	/**
	 * Convert post type args to hidden mode.
	 *
	 * @since 4.8.0
	 *
	 * @param array $args Post type arguments.
	 *
	 * @return array
	 */
	private function get_hidden_post_type_args( $args ) {
		$args['public']             = false;
		$args['publicly_queryable'] = false;
		$args['show_ui']            = false;
		$args['show_in_menu']       = false;
		$args['show_in_nav_menus']  = false;
		$args['exclude_from_search'] = true;
		$args['has_archive']        = false;
		$args['rewrite']            = false;
		$args['show_in_rest']       = false;

		return $args;
	}

	/**
	 * Convert taxonomy args to hidden mode.
	 *
	 * @since 4.8.0
	 *
	 * @param array $args Taxonomy arguments.
	 *
	 * @return array
	 */
	private function get_hidden_taxonomy_args( $args ) {
		$args['public']            = false;
		$args['show_ui']           = false;
		$args['show_in_nav_menus'] = false;
		$args['show_tagcloud']     = false;
		$args['rewrite']           = false;
		$args['show_in_rest']      = false;

		return $args;
	}

	/**
	 * Flush rewrite rules when mode or slug changes.
	 *
	 * @since 4.8.0
	 *
	 * @param array $settings Portfolio settings.
	 *
	 * @return void
	 */
	private function maybe_flush_rewrite_rules( $settings ) {
		$signature = wp_json_encode( [
			'mode' => $settings['mode'],
			'slug' => $settings['slug'],
		] );

		$stored_signature = get_option( self::REWRITE_SIGNATURE_OPTION, '' );

		if ( $signature === $stored_signature ) {
			return;
		}

		update_option( self::REWRITE_SIGNATURE_OPTION, $signature );

		// Flush after init has fully completed so all rewrite structures are registered.
		add_action( 'shutdown', function () {
			flush_rewrite_rules( false );
		} );
	}

	/**
	 * Add taxonomy category order heading.
	 *
	 * @since 1.21.0
	 */
	public function taxonomy_category_order_heading( $col_th ) {
		array_pop( $col_th );
		$col_th['jupiterx_order_cat'] = 'Order';
		$col_th['posts']              = 'Count';

		return wp_parse_args( array( 'jupiterx_order_cat' => 'Order' ), $col_th );
	}

	/**
	 * Add taxonomy category order content.
	 *
	 * @since 1.21.0
	 */
	public function taxonomy_category_order_content( $value, $column_name, $term_id ) {
		$column_name = '';

		if (
			class_exists( 'acf' ) &&
			! empty( get_field( 'jupiterx_taxonomy_order_number', 'category_' . $term_id ) )
		) {
			$value = get_field( 'jupiterx_taxonomy_order_number', 'category_' . $term_id );
		}

		$taxonomy_term_id = empty( $this->term ) ? $term_id : $this->term->term_id;

		if ( jupiterx_core()->check_default_settings() && ! empty( get_term_meta( $taxonomy_term_id, 'jupiterx_taxonomy_order_number', true ) ) ) {
			$value = get_term_meta( $term_id, 'jupiterx_taxonomy_order_number', true );
		}

		echo esc_html( $value );
	}
}

new JupiterX_Portfolio();

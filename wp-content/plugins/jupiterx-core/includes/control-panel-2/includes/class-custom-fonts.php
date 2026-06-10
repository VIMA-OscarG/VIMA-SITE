<?php
defined( 'ABSPATH' ) || die();

/**
 * Handles custom fonts functionality in control panel.
 *
 * @package JupiterX_Core\Control_Panel_2\Custom_Fonts
 *
 * @since 2.5.0
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class JupiterX_Core_Control_Panel_Custom_Fonts {

	private static $instance = null;

	const POST_TYPE = 'jupiterx-fonts';

	/**
	 * Instance of class.
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_jupiterx_custom_fonts', [ $this, 'handle_ajax' ] );
		add_action( 'wp_ajax_jupiterx_custom_fonts_get_posts', [ $this, 'get_posts' ] );
	}

	/**
	 * Handle ajax requests.
	 * Gets Ajax call sub_action parameter and call a function based on parameter value.
	 *
	 * @return void
	 * @since 2.5.0
	 */
	public function handle_ajax() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You do not have access to this section.', 'jupiterx-core' );
		}

		$action = filter_input( INPUT_POST, 'sub_action', FILTER_UNSAFE_RAW );

		if ( ! empty( $action ) && method_exists( $this, $action ) ) {
			call_user_func( [ $this, $action ] );
		}
	}

	/**
	 * Gets Custom fonts posts.
	 *
	 * @return void
	 * @since 2.5.0
	 */
	public function get_posts() {
		check_ajax_referer( 'jupiterx_control_panel', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You do not have access to this section.', 'jupiterx-core' );
		}

		$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

		$orderby_req = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'date'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_req   = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! in_array( $orderby_req, [ 'date', 'modified' ], true ) ) {
			$orderby_req = 'date';
		}
		if ( ! in_array( $order_req, [ 'ASC', 'DESC' ], true ) ) {
			$order_req = 'DESC';
		}

		/**
		 * Filter List Table query arguments.
		 *
		 * @param array $args The query arguments.
		 *
		 * @since 2.5.0
		 */
		$args = apply_filters( 'jupiterx_custom_font_list_table_' . self::POST_TYPE . '_args', [
			'post_type'      => self::POST_TYPE,
			'paged'          => $paged,
			'posts_per_page' => 20,
			'orderby'        => $orderby_req,
			'order'          => $order_req,
		] );

		$query   = new \WP_Query( $args );
		$posts   = apply_filters( 'jupiterx_custom_font_list_table_' . self::POST_TYPE . '_posts', $query->posts );
		$columns = apply_filters( 'jupiterx_custom_font_list_table_' . self::POST_TYPE . '_columns', [
			'labels' => [
				esc_html__( 'Author', 'jupiterx-core' ),
				esc_html__( 'Preview', 'jupiterx-core' ),
				esc_html__( 'Published date', 'jupiterx-core' ),
				esc_html__( 'Last modified', 'jupiterx-core' ),
			],
			'values' => [ '' ],
		], $posts );

		$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		foreach ( $posts as $post ) {
			$author_id = get_post_field( 'post_author', $post->ID );
			$columns['values'][ "post_{$post->ID}" ] = [
				get_the_author_meta( 'user_login', $author_id ),
				$this->get_custom_font_data( $post ),
				'',
				'',
			];
			$post->user_url = get_edit_user_link( get_the_author_meta( 'ID', $author_id ) );
			$post->custom_date = get_the_date( 'M d, Y', $post->ID );
			$post->custom_modified_date = get_the_modified_date( 'M d, Y', $post->ID );
			$post->custom_date_with_time = mysql2date( $date_time_format, $post->post_date );
			$post->custom_modified_date_with_time = mysql2date( $date_time_format, $post->post_modified );
		}

		wp_send_json_success( [
			'posts'         => $posts,
			'max_num_pages' => $query->max_num_pages,
			'columns'       => $columns,
		] );
	}

	/**
	 * Generates the CSS font face for each font from the font family name and font data.
	 *
	 * @param $font_family
	 * @param $data
	 *
	 * @return string
	 * @since 2.5.0
	 */
	public function get_font_face_from_data( $font_family, $data ) {
		$font_face = '';

		foreach ( $data as $variation ) {
			// Check if this is a variable font
			if ( isset( $variation['is_variable'] ) && $variation['is_variable'] ) {
				$font_face .= $this->generate_variable_font_face( $font_family, $variation );
			} else {
				$font_face .= $this->generate_static_font_face( $font_family, $variation );
			}
		}

		return $font_face;
	}

	/**
	 * Generate @font-face CSS for static fonts (existing functionality).
	 *
	 * @param string $font_family
	 * @param array $variation
	 *
	 * @return string
	 * @since 4.0.0
	 */
	private function generate_static_font_face( $font_family, $variation ) {
		$src = $this->get_font_src_per_type( $variation );

		$font_face  = '@font-face{';
		$font_face .= 'font-family: \'' . $font_family . '\';';
		$font_face .= 'font-style: ' . $variation['font_style'] . ';';
		$font_face .= 'font-weight: ' . $variation['font_weight'] . ';';
		$font_face .= 'src: ' . implode( ', ', $src ) . ';';
		$font_face .= '}';

		return $font_face;
	}

	/**
	 * Generate @font-face CSS for variable fonts.
	 *
	 * @param string $font_family
	 * @param array $variation
	 *
	 * @return string
	 * @since 4.0.0
	 */
	private function generate_variable_font_face( $font_family, $variation ) {
		$weight_min = ! empty( $variation['weight_min'] ) ? intval( $variation['weight_min'] ) : 100;
		$weight_max = ! empty( $variation['weight_max'] ) ? intval( $variation['weight_max'] ) : 900;
		if ( $weight_min > $weight_max ) {
			list( $weight_min, $weight_max ) = [ $weight_max, $weight_min ];
		}

		$has_italic_axis = $this->is_checked( $variation['has_ital'] ?? null );
		$font_style      = $has_italic_axis ? 'normal italic' : 'normal';

		$font_face  = '@font-face{';
		$font_face .= 'font-family: \'' . $font_family . '\';';
		$font_face .= 'src: ' . $this->get_variable_font_src( $variation ) . ';';
		$font_face .= 'font-weight: ' . $weight_min . ' ' . $weight_max . ';';
		$font_face .= 'font-style: ' . $font_style . ';';

		$has_width_axis = $this->is_checked( $variation['has_wdth'] ?? null );
		if ( $has_width_axis ) {
			$width_min = ! empty( $variation['width_min'] ) ? intval( $variation['width_min'] ) : 75;
			$width_max = ! empty( $variation['width_max'] ) ? intval( $variation['width_max'] ) : 125;
			if ( $width_min > $width_max ) {
				list( $width_min, $width_max ) = [ $width_max, $width_min ];
			}
			$font_face .= 'font-stretch: ' . $width_min . '% ' . $width_max . '%;';
		}

		$font_face .= '}';

		return $font_face;
	}

	private function get_font_src_per_type( $variation ) {
		$src = [];

		foreach ( [ 'woff', 'woff2', 'svg', 'ttf' ] as $type ) {
			if ( empty( $variation[ $type ] ) ) {
				continue;
			}

			if ( in_array( $type, [ 'woff', 'woff2', 'svg' ], true ) ) {
				$src[] = 'url(\'' . esc_attr( $variation[ $type ] ) . '\') format(\'' . $type . '\')';
			}

			if ( 'ttf' === $type ) {
				$src[] = 'url(\'' . esc_attr( $variation[ $type ] ) . '\') format(\'truetype\')';
			}
		}

		return $src;
	}

	/**
	 * Generate src for variable fonts.
	 *
	 * Variable fonts use a single file in the selected format.
	 *
	 * @param array $variation
	 *
	 * @return string
	 * @since 4.0.0
	 */
	private function get_variable_font_src( $variation ) {
		if ( empty( $variation['variable_file'] ) || empty( $variation['variable_format'] ) ) {
			return '';
		}

		$format_map = [
			'woff2' => 'woff2',
			'woff'  => 'woff',
			'ttf'   => 'truetype',
		];

		$format = isset( $format_map[ $variation['variable_format'] ] ) ? $format_map[ $variation['variable_format'] ] : 'woff2';

		return 'url(\'' . esc_attr( $variation['variable_file'] ) . '\') format(\'' . $format . '\')';
	}


	/**
	 * Check if a checkbox value is checked/on.
	 *
	 * @param mixed $value The value to check.
	 * @return bool
	 * @since 2.5.0
	 */
	private function is_checked( $value ) {
		return isset( $value ) && (
			true === $value ||
			'on' === $value ||
			'1' === $value ||
			1 === $value
		);
	}

	/**
	 * Validate variable font variation.
	 *
	 * @param array $variation The variation data to validate.
	 * @return void
	 * @since 2.5.0
	 */
	private function validate_variable_font( $variation ) {
		$error = $this->validate_variable_font_file( $variation );
		if ( $error ) {
			wp_send_json_error( $error );
			return;
		}

		$error = $this->validate_weight_axis( $variation );
		if ( $error ) {
			wp_send_json_error( $error );
			return;
		}

		$error = $this->validate_width_axis( $variation );
		if ( $error ) {
			wp_send_json_error( $error );
		}
	}

	/**
	 * Validate variable font file requirements.
	 *
	 * @param array $variation The variation data to validate.
	 * @return string|null Error message or null if valid.
	 * @since 2.5.0
	 */
	private function validate_variable_font_file( $variation ) {
		if ( empty( $variation['variable_file'] ) ) {
			return esc_html__( 'Variable fonts require a font file.', 'jupiterx-core' );
		}

		if ( empty( $variation['variable_format'] ) ) {
			return esc_html__( 'Please select a format for the variable font.', 'jupiterx-core' );
		}

		return null;
	}

	/**
	 * Validate weight axis range.
	 *
	 * @param array $variation The variation data to validate.
	 * @return string|null Error message or null if valid.
	 * @since 2.5.0
	 */
	private function validate_weight_axis( $variation ) {
		$weight_min = isset( $variation['weight_min'] ) ? intval( $variation['weight_min'] ) : 100;
		$weight_max = isset( $variation['weight_max'] ) ? intval( $variation['weight_max'] ) : 900;

		if ( $weight_min < 1 || $weight_min > 999 || $weight_max < 1 || $weight_max > 999 ) {
			return esc_html__( 'Weight axis: minimum must be between 1 and 999.', 'jupiterx-core' );
		}

		if ( $weight_min >= $weight_max ) {
			return esc_html__( 'Weight axis: minimum must be less than maximum.', 'jupiterx-core' );
		}

		return null;
	}

	/**
	 * Validate width axis range if enabled.
	 *
	 * @param array $variation The variation data to validate.
	 * @return string|null Error message or null if valid.
	 * @since 2.5.0
	 */
	private function validate_width_axis( $variation ) {
		if ( ! $this->is_checked( $variation['has_wdth'] ?? null ) ) {
			return null;
		}

		$width_min = isset( $variation['width_min'] ) ? intval( $variation['width_min'] ) : 75;
		$width_max = isset( $variation['width_max'] ) ? intval( $variation['width_max'] ) : 125;

		if ( $width_min < 25 || $width_min > 200 || $width_max < 25 || $width_max > 200 ) {
			return esc_html__( 'Width axis: minimum must be between 25% and 200%.', 'jupiterx-core' );
		}

		if ( $width_min >= $width_max ) {
			return esc_html__( 'Width axis: minimum must be less than maximum.', 'jupiterx-core' );
		}

		return null;
	}

	/**
	 * Validate static font variation.
	 *
	 * @param array $variation The variation data to validate.
	 * @return void
	 * @since 2.5.0
	 */
	private function validate_static_font( $variation ) {
		// Static fonts need at least one file
		if ( empty( $variation['woff'] ) && empty( $variation['woff2'] ) && empty( $variation['ttf'] ) && empty( $variation['svg'] ) ) {
			wp_send_json_error( esc_html__( 'Each font variation must have at least one font file.', 'jupiterx-core' ) );
		}
	}

	/**
	 * Validate all font variations.
	 *
	 * @param array $variations The variations to validate.
	 * @return void
	 * @since 2.5.0
	 */
	private function validate_variations( $variations ) {
		foreach ( $variations as $variation ) {
			$is_variable = $this->is_checked( $variation['is_variable'] ?? null );

			if ( $is_variable ) {
				$this->validate_variable_font( $variation );
			} else {
				$this->validate_static_font( $variation );
			}
		}
	}

	/**
	 * Check if font title already exists.
	 *
	 * @param string $title The font title to check.
	 * @param string $submit_mode The submit mode (empty for new, ID for update).
	 * @return void
	 * @since 2.5.0
	 */
	private function check_duplicate_title( $title, $submit_mode ) {
		// If it is not update, don't let duplicate title.
		if ( '' !== $submit_mode ) {
			return;
		}

		$query_args = [
			'post_type'      => self::POST_TYPE,
			's'              => $title,
			'posts_per_page' => 1,
		];

		$fonts_query      = new WP_Query( $query_args );
		$posts            = $fonts_query->get_posts();
		$current_font_obj = ( is_array( $posts ) && count( $posts ) > 0 ) ? (object) $posts[0] : false;

		if ( ! empty( $current_font_obj->ID ) ) {
			wp_send_json_error( esc_html__( 'This font title already exists. Please choose another one.', 'jupiterx-core' ) );
		}
	}

	/**
	 * Prepare post data for insertion.
	 *
	 * @param array $post The post data from request.
	 * @return array
	 * @since 2.5.0
	 */
	private function prepare_post_data( $post ) {
		$post_data = [
			'post_title'   => wp_strip_all_tags( $post['custom_fonts_post_title'] ),
			'post_content' => wp_json_encode( $post['custom_fonts_post_variation_url'] ),
			'post_status'  => 'publish',
			'post_type'    => self::POST_TYPE,
			'meta_input'   => [
				'jupiterx_font_face' => $this->get_font_face_from_data( $post['custom_fonts_post_title'], $post['custom_fonts_post_variation_url'] ),
			],
		];

		// Check if it's update query.
		if ( '' !== $post['custom_fonts_submit_mode'] ) {
			$post_data['ID'] = $post['custom_fonts_submit_mode'];
		}

		return $post_data;
	}

	/**
	 * Create and update post by ajax.
	 *
	 * @return void
	 * @since 2.5.0
	 */
	public function save_post() {
		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_FORCE_ARRAY );

		if ( empty( $post['custom_fonts_post_title'] ) ) {
			wp_send_json_error( esc_html__( 'Name of the custom font can not be empty.', 'jupiterx-core' ) );
		}

		if ( empty( $post['custom_fonts_post_variation_url'] ) ) {
			wp_send_json_error( esc_html__( 'You should add font variations before saving.', 'jupiterx-core' ) );
		}

		$this->validate_variations( $post['custom_fonts_post_variation_url'] );
		$this->check_duplicate_title( $post['custom_fonts_post_title'], $post['custom_fonts_submit_mode'] ?? '' );

		$post_data = $this->prepare_post_data( $post );
		$result    = wp_insert_post( $post_data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success();
	}

	/**
	 * Remove post by ajax.
	 *
	 * @return void
	 * @since 2.5.0
	 */
	public function remove_post() {
		$post   = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$result = $this->delete_post( $post );

		if ( empty( $result ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Delete a post.
	 *
	 * @param int $id
	 *
	 * @return array|false|WP_Post
	 * @since 2.5.0
	 */
	private function delete_post( $id ) {
		return wp_delete_post( $id, true );
	}

	/**
	 * Returns decoded custom font data as an object.
	 *
	 * @param $font
	 *
	 * @return object
	 * @since 2.5.0
	 */
	private function get_custom_font_data( $font ) {
		$font_settings = json_decode( $font->post_content );

		return (object) [ 'font_settings' => $font_settings ];
	}
}

JupiterX_Core_Control_Panel_Custom_Fonts::get_instance();

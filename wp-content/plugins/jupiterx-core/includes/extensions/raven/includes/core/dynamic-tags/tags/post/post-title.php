<?php
namespace JupiterX_Core\Raven\Core\Dynamic_Tags\Tags\Post;

use Elementor\Core\DynamicTags\Tag as Tag;

defined( 'ABSPATH' ) || die();

class Post_Title extends Tag {
	public function get_name() {
		return 'post-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'jupiterx-core' );
	}

	public function get_group() {
		return 'post';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	public function render() {
		$term = \JupiterX_Core\Raven\Modules\Loop_Grid\Widgets\Loop_Grid::get_current_loop_term();

		if ( $term instanceof \WP_Term ) {
			echo wp_kses_post( $term->name );
			return;
		}

		echo wp_kses_post( get_the_title() );
	}
}

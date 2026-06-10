<?php
/**
 * Loop Item CSS file handler.
 *
 * Prints Loop Item styles inline instead of enqueueing elementor-post-* with
 * an elementor-frontend dependency (not registered in the editor iframe).
 *
 * @package JupiterX_Core\Raven
 * @since NEXT
 */

namespace JupiterX_Core\Raven\Core\Document_Types\Type;

use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Plugin;

defined( 'ABSPATH' ) || die();

/**
 * Inline-only CSS handler for JupiterX Loop Item templates.
 */
class Jupiterx_Loop_Item_CSS extends Post_CSS {

	/**
	 * Ensure CSS meta exists without calling wp_enqueue_style().
	 *
	 * Loop Item styles are output via print_css() during builder rendering.
	 */
	public function enqueue() {
		$document = Plugin::$instance->documents->get( $this->get_post_id() );

		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return;
		}

		$meta = $this->get_meta();

		if ( self::CSS_STATUS_EMPTY === $meta['status'] ) {
			return;
		}

		if ( '' === $meta['status'] || $this->is_update_required() ) {
			$this->update();
		}

		/**
		 * Fires when Loop Item CSS is prepared.
		 *
		 * Matches Elementor Pro loop-builder behaviour: do not enqueue
		 * elementor-post-{id} with elementor-frontend as a dependency.
		 */
		do_action( 'elementor/css-file/post/enqueue', $this );
	}
}

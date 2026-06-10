<?php
/**
 * Add Document Type Module.
 *
 * @package JupiterX_Core\Raven
 * @since 3.7.0
 */

namespace JupiterX_Core\Raven\Core\Document_Types;

use JupiterX_Core\Raven\Core\Document_Types\Type;

defined( 'ABSPATH' ) || die();

class Module {
	public function __construct() {
		add_action( 'elementor/documents/register', [ $this, 'register_document_types' ] );
	}

	public function register_document_types( $documents_manager ) {
		jupiterx_core()->load_files( [
			'extensions/raven/includes/core/document-types/types/popup',
			'extensions/raven/includes/core/document-types/types/loop-item-css',
			'extensions/raven/includes/core/document-types/types/loop-item',
		] );

		$documents_manager->register_document_type( 'jupiterx-popups', Type\Jupiterx_Popup_Document::get_class_full_name() );
		$documents_manager->register_document_type( Type\Jupiterx_Loop_Item_Document::SLUG, Type\Jupiterx_Loop_Item_Document::get_class_full_name() );
	}
}

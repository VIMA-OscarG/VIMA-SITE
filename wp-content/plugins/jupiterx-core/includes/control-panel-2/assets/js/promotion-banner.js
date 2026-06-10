/**
 * Promotion Banner: async load and dismiss.
 *
 * @package JupiterX_Core
 */

( function( $, settings ) {
	'use strict';

	if ( ! settings || ! settings.ajaxUrl ) {
		return;
	}

	/**
	 * Send AJAX request to dismiss a promotion banner.
	 *
	 * @param {jQuery} $banner Banner element.
	 */
	const sendDismiss = ( $banner ) => {
		const promotionId = $banner.data( 'jx-promotion-id' );
		const nonce = $banner.data( 'jx-promotion-nonce' );

		if ( ! promotionId || ! nonce ) {
			return;
		}

		$.ajax( {
			url: settings.ajaxUrl,
			type: 'POST',
			data: {
				action: 'jupiterx_dismiss_admin_promotion',
				promotionId: promotionId,
				nonce: nonce,
			},
		} );
	};

	/**
	 * Bind dismiss buttons within a container.
	 *
	 * @param {jQuery} $container Container element.
	 */
	const bindDismiss = ( $container ) => {
		$container.find( '.jx-promotion-banner__dismiss' ).on( 'click', function( event ) {
			event.preventDefault();
			const $banner = $( this ).closest( '.jx-promotion-banner' );
			$banner.fadeOut( 200, function() {
				$banner.remove();
			} );
			sendDismiss( $banner );
		} );
	};

	$( function() {
		const $wrap = $( '#wpbody-content .wrap' ).first();
		let $heading = $wrap.children( 'h1' ).first();

		if ( ! $heading.length ) {
			$heading = $wrap.find( 'h1' ).first();
		}

		if ( ! $wrap.length || ! $heading.length ) {
			return;
		}

		const $mount = $( '<div/>' )
			.attr( {
				id: 'jx-promotion-banner-mount',
				'aria-live': 'polite',
			} )
			.addClass( 'jx-promotion-banner-mount' );

		$heading.after( $mount );

		if ( ! settings.fetchNonce ) {
			$mount.remove();
			return;
		}

		const urlParams = new URLSearchParams( window.location.search );

		$.ajax( {
			url: settings.ajaxUrl,
			type: 'POST',
			data: {
				action: 'jupiterx_fetch_promotion_banners',
				nonce: settings.fetchNonce,
				jx_pagenow: settings.pagenow || '',
				jx_page: urlParams.get( 'page' ) || '',
				jx_post_type: urlParams.get( 'post_type' ) || '',
			},
			timeout: 15000,
		} )
			.done( function( response ) {
				if ( ! response || ! response.success || ! response.data ) {
					$mount.remove();
					return;
				}

				const html = response.data.html || '';
				const css = response.data.css || '';

				if ( ! html ) {
					$mount.remove();
					return;
				}

				if ( css ) {
					const $style = $( '#jx-promotion-banner-remote-css' );
					if ( $style.length ) {
						$style.text( css );
					} else {
						$( '<style id="jx-promotion-banner-remote-css"></style>' ).text( css ).appendTo( 'head' );
					}
				}

				$mount.html( html );
				bindDismiss( $mount );
			} )
			.fail( function() {
				$mount.remove();
			} );
	} );
}( window.jQuery, window.jxPromotionBanner || {} ) );

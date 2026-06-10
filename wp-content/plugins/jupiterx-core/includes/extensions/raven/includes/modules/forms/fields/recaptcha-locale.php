<?php
/**
 * Google reCAPTCHA UI language (`hl`) from WPML / WordPress locale.
 *
 * @package JupiterX_Core\Raven
 * @since 4.51.0
 */

namespace JupiterX_Core\Raven\Modules\Forms\Fields;

defined( 'ABSPATH' ) || die();

/**
 * Maps WPML / WordPress language codes to Google's reCAPTCHA `hl` parameter.
 *
 * WPML uses codes like `en`, `fr`, `pt-br`; WordPress locales look like `pt_BR`
 * or `de_DE_formal`. Google's API expects ISO-style tags (e.g. `pt-BR`, `zh-CN`).
 */
class Recaptcha_Locale {

	/**
	 * Script handle used for reCAPTCHA v3 (must match {@see Recaptcha_V3} registration).
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'jupiterx-core-raven-recaptcha';

	/**
	 * Whether the script-loader filter was attached (avoid duplicate callbacks).
	 *
	 * @var bool
	 */
	private static $script_loader_filter_added = false;

	/**
	 * Resolve `hl` query value for https://www.google.com/recaptcha/api.js
	 *
	 * @since 4.51.0
	 *
	 * @return string Sanitized language tag for Google's `hl` parameter.
	 */
	public static function get_hl() {
		$lang = apply_filters( 'wpml_current_language', null );

		if ( empty( $lang ) || ! is_string( $lang ) ) {
			$lang = determine_locale();
		}

		if ( ! is_string( $lang ) || '' === $lang ) {
			$lang = 'en_US';
		}

		$hl = self::normalize_to_recaptcha_hl( $lang );

		return apply_filters( 'jupiterx_raven_recaptcha_hl', $hl, $lang );
	}

	/**
	 * Append `hl` to the registered reCAPTCHA v3 script URL.
	 *
	 * @param string|false $src    Script source URL.
	 * @param string       $handle Script handle.
	 * @return string|false
	 */
	public static function filter_script_loader_src( $src, $handle ) {
		if ( self::SCRIPT_HANDLE !== $handle ) {
			return $src;
		}

		if ( empty( $src ) || ! is_string( $src ) ) {
			return $src;
		}

		return add_query_arg( 'hl', self::get_hl(), $src );
	}

	/**
	 * Register `script_loader_src` once so `hl` is applied to the v3 script URL.
	 *
	 * @since 4.51.0
	 *
	 * @return void
	 */
	public static function register_script_loader_filter() {
		if ( self::$script_loader_filter_added ) {
			return;
		}

		add_filter( 'script_loader_src', [ __CLASS__, 'filter_script_loader_src' ], 10, 2 );
		self::$script_loader_filter_added = true;
	}

	/**
	 * Convert WPML / WordPress locale string to a Google reCAPTCHA `hl` tag.
	 *
	 * @param string $lang Locale or language code.
	 * @return string
	 */
	private static function normalize_to_recaptcha_hl( $lang ) {
		$lang = strtolower( str_replace( '_', '-', $lang ) );
		$lang = preg_replace( '/[^a-z0-9\-]/', '', $lang );

		if ( '' === $lang ) {
			return 'en';
		}

		// Exact-match locales where Google's `hl` differs from WP / WPML naming.
		$map = [
			'zh-hans' => 'zh-CN',
			'zh-cn'   => 'zh-CN',
			'zh-hant' => 'zh-TW',
			'zh-tw'   => 'zh-TW',
			'zh-hk'   => 'zh-HK',
			'nb'      => 'no',
			'nn'      => 'no',
		];

		if ( isset( $map[ $lang ] ) ) {
			return $map[ $lang ];
		}

		$parts = array_values( array_filter( explode( '-', $lang ) ) );

		// Primary language subtag aliases (Norwegian Bokmål/Nynorsk → Google's `no`).
		if ( ! empty( $parts[0] ) && isset( $map[ $parts[0] ] ) ) {
			return $map[ $parts[0] ];
		}

		// Language + ISO 3166-1 alpha-2 region (e.g. pt-br, en-gb, de-de-formal → de-de).
		if ( count( $parts ) >= 2 && 2 === strlen( $parts[1] ) && preg_match( '/^[a-z]{2}$/', $parts[1] ) ) {
			$language = $parts[0];
			if ( preg_match( '/^[a-z]{2,3}$/', $language ) ) {
				return $language . '-' . strtoupper( $parts[1] );
			}
		}

		// Primary subtag only (e.g. en, fr, es).
		if ( ! empty( $parts[0] ) && preg_match( '/^[a-z]{2,3}$/', $parts[0] ) ) {
			return $parts[0];
		}

		return 'en';
	}
}

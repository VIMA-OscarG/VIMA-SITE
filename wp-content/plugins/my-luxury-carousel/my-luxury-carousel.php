<?php
/**
 * Plugin Name: My Luxury Carousel
 * Plugin URI:  https://tusitio.com/
 * Description: Carrusel profesional con animación de deslizado infinito. Ofrece un shortcode: [luxury_carousel] responsive (renderiza desktop + mobile y muestra según ancho). Compatible con ?lang=es (si viene => español; si no viene => inglés). Los enlaces preservan el parámetro de idioma.
 * Version:     1.9
 * Author:      XCH
 * Author URI:  https://tusitio.com/
 * License:     GPL2
 * Text Domain: my-luxury-carousel
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MLC_VERSION', '1.9' );

/**
 * Debug:
 * - Activo si WP_DEBUG es true
 * - O si agregas ?mlc_debug=1 a la URL
 */
function mlc_is_debug(): bool {
	if ( isset($_GET['mlc_debug']) && $_GET['mlc_debug'] === '1' ) return true;
	return ( defined('WP_DEBUG') && WP_DEBUG );
}

function mlc_debug_log( string $message ): void {
	if ( ! mlc_is_debug() ) return;
	if ( function_exists('error_log') ) error_log('[MLC] ' . $message);
}

/* ============================================================
 * POPUPS (IDs fijos + registro temprano)
 * ============================================================ */
function mlc_get_popup_ids_static(): array {
	// IDs de Elementor Popup que usa el carrusel
	return array(14825, 14834, 14843);
}

/**
 * Registra un popup de Elementor Pro en el location actual
 * para que elementorProFrontend.config.popup[id] exista.
 *
 * IMPORTANTE: hacerlo temprano evita popups “a medias” (sin config),
 * que terminan descentrados y sin poder cerrar correctamente.
 */
function mlc_register_elementor_popup( int $popup_id ): void {
	static $done = array();

	if ( $popup_id <= 0 ) return;
	if ( isset($done[$popup_id]) ) return;

	if ( class_exists('\ElementorPro\Modules\Popup\Module') ) {
		\ElementorPro\Modules\Popup\Module::add_popup_to_location( $popup_id );
		$done[$popup_id] = true;

		mlc_debug_log('Registered popup to location: ' . $popup_id);
	}
}

/**
 * Registro temprano (antes del render del contenido).
 * Esto asegura que la config del popup esté lista cuando hagas showPopup().
 */
function mlc_register_popups_early(): void {
	static $ran = false;
	if ($ran) return;
	$ran = true;

	if ( ! class_exists('\ElementorPro\Modules\Popup\Module') ) {
		mlc_debug_log('ElementorPro Popup Module not available at wp hook.');
		return;
	}

	foreach ( mlc_get_popup_ids_static() as $id ) {
		mlc_register_elementor_popup( (int) $id );
	}
}
add_action('wp', 'mlc_register_popups_early', 1);

/* ============================================================
 * Helpers de idioma y URLs
 * ============================================================ */
function mlc_get_lang(): string {
	return ( isset($_GET['lang']) && $_GET['lang'] === 'es' ) ? 'es' : 'en';
}

function mlc_get_translations(): array {
	return array(
		'en' => array(
			'destination_label'   => 'Rental Rates',
			'destination_title'   => 'Destination',
			'property_label'      => 'Rental Rates & Seasonality',
			'property_title'      => 'Property',
			'close'               => 'Close',
			'tab_rates'           => 'Rates',
			'tab_seasonality'     => 'Seasonality',
			'unified_description' => 'Discover how much you can earn renting your vacation property',
			'unified_button_text' => 'Rental Rates',
			'previous'            => 'Previous',
			'next'                => 'Next',
			'no_resorts'          => 'No resorts or properties found for this destination.',
			'no_properties'       => 'No properties available.',
			'no_rates'            => 'No rates available.',
			'no_seasonality'      => 'No seasonality available.',
			'season'              => 'Season',
			'season_start'        => 'Start',
			'season_end'          => 'End',
		),
		'es' => array(
			'destination_label'   => 'Tarifas de renta',
			'destination_title'   => 'Destino',
			'property_label'      => 'Tarifas de renta y temporalidad',
			'property_title'      => 'Propiedad',
			'close'               => 'Cerrar',
			'tab_rates'           => 'Tarifas',
			'tab_seasonality'     => 'Temporalidad',
			'unified_description' => 'Descubre cuanto puedes ganar rentando tu propiedad vacacional',
			'unified_button_text' => 'Tarifas de renta',
			'previous'            => 'Anterior',
			'next'                => 'Siguiente',
			'no_resorts'          => 'No hay resorts o propiedades en este destino.',
			'no_properties'       => 'No hay propiedades disponibles.',
			'no_rates'            => 'No hay tarifas disponibles.',
			'no_seasonality'      => 'No hay temporalidades disponibles.',
			'season'              => 'Temporada',
			'season_start'        => 'Inicio',
			'season_end'          => 'Fin',
		),
	);
}

function mlc_t( string $key ): string {
	$lang = mlc_get_lang();
	$translations = mlc_get_translations();

	if ( isset( $translations[ $lang ][ $key ] ) ) {
		return $translations[ $lang ][ $key ];
	}

	if ( isset( $translations['en'][ $key ] ) ) {
		return $translations['en'][ $key ];
	}

	return $key;
}

/**
 * Agrega/preserva ?lang SOLO en links normales (http/https o rutas).
 * NO toca anchors (#...), ni mailto/tel/javascript, ni elementor-action.
 */
function mlc_url_with_lang( string $url, ?string $lang = null ): string {
	$lang = $lang ?: mlc_get_lang();

	if ( $url === '' ) return $url;

	// No tocar anchors (incluye elementor-action y cualquier #...)
	if ( isset($url[0]) && $url[0] === '#' ) return $url;

	// No tocar esquemas especiales
	if ( stripos($url, 'mailto:') === 0 || stripos($url, 'tel:') === 0 || stripos($url, 'javascript:') === 0 ) {
		return $url;
	}

	return add_query_arg( 'lang', $lang, $url );
}

/**
 * Construye el href nativo de Elementor para abrir popups:
 * #elementor-action:action=popup:open&settings=BASE64(JSON)
 */
function mlc_elementor_popup_href( int $popup_id ): string {
	$settings = array('id' => $popup_id, 'toggle' => false);
	$json    = wp_json_encode($settings);
	$encoded = base64_encode($json);
	return '#elementor-action:action=popup:open&settings=' . rawurlencode($encoded);
}

/* ============================================================
 * Encolar assets
 * ============================================================ */
function mlc_enqueue_assets() {

	$has_elementor     = did_action('elementor/loaded');
	$has_elementor_pro = did_action('elementor_pro/init');

	// Elementor base
	if ( $has_elementor ) {
		wp_enqueue_script('elementor-frontend');

		// Asegura dialog (overlay/cierre)
		if ( wp_script_is('elementor-dialog', 'registered') ) wp_enqueue_script('elementor-dialog');
		if ( wp_style_is('elementor-dialog', 'registered') ) wp_enqueue_style('elementor-dialog');
	}

	// Elementor Pro frontend (popups)
	if ( $has_elementor_pro ) {
		wp_enqueue_script('elementor-pro-frontend');
	}

	// CSS
	// CSS (cache-busting por filemtime)
$css_rel  = 'css/mlc-styles.css';
$css_path = plugin_dir_path(__FILE__) . $css_rel;
$css_ver  = file_exists($css_path) ? filemtime($css_path) : MLC_VERSION;

wp_register_style(
  'mlc-styles',
  plugin_dir_url(__FILE__) . $css_rel,
  array(),
  $css_ver,
  'all'
);
wp_enqueue_style('mlc-styles');


	/**
	 * Inline CSS:
	 * - Responsive visibility
	 * - Hardening para Elementor Popup (evita desalineación y bloqueo de cierre)
	 */
	$inline_css = <<<CSS
/* MLC Responsive visibility */
.mlc-mobile-carousel-wrapper { display: none; }
@media (max-width: 768px) {
  .mlc-luxury-carousel-wrapper { display: none !important; }
  .mlc-mobile-carousel-wrapper { display: block !important; }
}

/* Elementor Popup hardening: evita que quede “a un lado” o sin click */
.dialog-widget-overlay{
  position: fixed !important;
  inset: 0 !important;
  z-index: 999998 !important;
}

.elementor-popup-modal.dialog-widget{
  position: fixed !important;
  inset: 0 !important;
  margin: 0 !important;
  z-index: 999999 !important;
}

.elementor-popup-modal .dialog-close-button{
  pointer-events: auto !important;
  z-index: 1000000 !important;
}
CSS;
	wp_add_inline_style('mlc-styles', $inline_css);

	// JS (deps)
	$deps = array('jquery');
	if ( wp_script_is('elementor-pro-frontend', 'registered') || wp_script_is('elementor-pro-frontend', 'enqueued') ) {
	  $deps[] = 'elementor-pro-frontend';
	} elseif ( wp_script_is('elementor-frontend', 'registered') || wp_script_is('elementor-frontend', 'enqueued') ) {
	  $deps[] = 'elementor-frontend';
	}
	if ( wp_script_is('elementor-dialog', 'registered') || wp_script_is('elementor-dialog', 'enqueued') ) {
	  $deps[] = 'elementor-dialog';
	}

	// 1) Registrar + encolar tu script
	// JS (cache-busting por filemtime)
$js_rel  = 'js/mlc-script.js';
$js_path = plugin_dir_path(__FILE__) . $js_rel;
$js_ver  = file_exists($js_path) ? filemtime($js_path) : MLC_VERSION;

wp_register_script(
  'mlc-script',
  plugin_dir_url(__FILE__) . $js_rel,
  $deps,
  $js_ver,
  true
);
wp_enqueue_script('mlc-script');


	// 2) Config para tu modal de rental rates
	// API remota como fuente principal + JSON local como respaldo.
$json_rel  = 'data/rental-rates-data.json';
$json_path = plugin_dir_path(__FILE__) . $json_rel;
$json_ver  = file_exists($json_path) ? filemtime($json_path) : time();
$api_url   = 'https://members.vacationintervalsmanagement.com/api/public/rental-rates-json';

wp_localize_script('mlc-script', 'MLC_CONFIG', array(
  'apiUrl'          => $api_url,
  'fallbackDataUrl' => plugin_dir_url(__FILE__) . $json_rel . '?v=' . $json_ver,
  // Compatibilidad hacia atrás por si algo todavía lee dataUrl.
  'dataUrl'         => $api_url,
));


	// 3) Tus settings actuales (debug/lang/etc)
wp_localize_script('mlc-script', 'MLC_SETTINGS', array(
  'debug'           => mlc_is_debug(),
  'lang'            => mlc_get_lang(),
  'version'         => MLC_VERSION,
  'assets'          => array(
    'css_ver'  => isset($css_ver) ? $css_ver : null,
    'js_ver'   => isset($js_ver) ? $js_ver : null,
    'json_ver' => isset($json_ver) ? $json_ver : null,
  ),
  'hasElementor'    => $has_elementor ? 1 : 0,
  'hasElementorPro' => $has_elementor_pro ? 1 : 0,
  'deps'            => $deps,
));

wp_localize_script('mlc-script', 'MLC_I18N', mlc_get_translations()[ mlc_get_lang() ]);


	// Debug: confirmar carga (después de localize para que existan MLC_CONFIG/MLC_SETTINGS)
	wp_add_inline_script(
		'mlc-script',
		"console.log('[MLC] mlc-script loaded', window.MLC_CONFIG || {}, window.MLC_SETTINGS || {});",
		'after'
	);

	/**
	 * Inline: handler para abrir popups (Elementor) - lo DEJAMOS intacto para no romper nada.
	 */
	$inline = <<<JS
(function(){
  var DBG = (window.MLC_SETTINGS && window.MLC_SETTINGS.debug) ? true : false;
  function log(){ if(DBG && window.console) console.log.apply(console, arguments); }
  function warn(){ if(DBG && window.console) console.warn.apply(console, arguments); }
  function err(){ if(DBG && window.console) console.error.apply(console, arguments); }

  function findPopupDomById(popupId){
    return document.querySelector('#elementor-popup-modal-' + popupId);
  }

  function showPopupById(popupId){
    try {
      if (window.elementorProFrontend &&
          window.elementorProFrontend.modules &&
          window.elementorProFrontend.modules.popup &&
          typeof window.elementorProFrontend.modules.popup.showPopup === 'function') {

        var cfg = window.elementorProFrontend && elementorProFrontend.config && elementorProFrontend.config.popup
          ? elementorProFrontend.config.popup[popupId]
          : undefined;

        log('[MLC] showPopup attempt. id=', popupId, 'config=', cfg);
        window.elementorProFrontend.modules.popup.showPopup({ id: popupId });
        return true;
      }
    } catch(e) {
      err('[MLC] showPopup exception:', e);
    }
    return false;
  }

  function runElementorActionOpen(popupId){
    try {
      var urlActions = window.elementorFrontend
        && elementorFrontend.utils
        && elementorFrontend.utils.urlActions
        ? elementorFrontend.utils.urlActions
        : null;

      if (!urlActions) {
        warn('[MLC] urlActions no disponible.');
        return false;
      }

      if (typeof urlActions.runAction === 'function') {
        urlActions.runAction('popup:open', { id: popupId, toggle: false });
        log('[MLC] urlActions.runAction OK. id=', popupId);
        return true;
      }

      var settingsObj = { id: popupId, toggle: false };
      var settingsB64 = btoa(unescape(encodeURIComponent(JSON.stringify(settingsObj))));
      var hash = '#elementor-action:action=popup:open&settings=' + encodeURIComponent(settingsB64);

      if (location.hash !== hash) location.hash = hash;
      try { window.dispatchEvent(new HashChangeEvent('hashchange')); } catch(e) {}

      log('[MLC] hash fallback disparado. id=', popupId);
      return true;

    } catch(e) {
      err('[MLC] runElementorActionOpen exception:', e);
      return false;
    }
  }

  if (DBG) {
    log('[MLC] Debug ON', window.MLC_SETTINGS || {});
    log('[MLC] elementorFrontend=', window.elementorFrontend);
    log('[MLC] elementorProFrontend=', window.elementorProFrontend);
  }

  document.addEventListener('click', function(e){
    var a = e.target && e.target.closest ? e.target.closest('a.mlc-popup-trigger') : null;
    if (!a) return;

    var idAttr = a.getAttribute('data-popup-id');
    var popupId = parseInt(idAttr, 10);

    log('[MLC] Click detectado en trigger', { el: a, idAttr: idAttr, popupId: popupId });

    if (!popupId) return;

    e.preventDefault();
    e.stopPropagation();

    var called = showPopupById(popupId);

    if (!called) {
      warn('[MLC] showPopup no disponible; intentando urlActions...');
      runElementorActionOpen(popupId);
      return;
    }

    setTimeout(function(){
      var dom = findPopupDomById(popupId);
      var preventScroll = document.documentElement.classList.contains('dialog-prevent-scroll');
      log('[MLC] post-open check => domPopup=', !!dom, 'dialog-prevent-scroll=', preventScroll, 'dom=', dom);

      if (!dom && !preventScroll) {
        warn('[MLC] No apareció popup en DOM; fallback a urlActions...');
        runElementorActionOpen(popupId);
      }
    }, 350);

  }, true);

})();
JS;

	wp_add_inline_script( 'mlc-script', $inline, 'after' );

	mlc_debug_log('enqueue_assets: hasElementor=' . ($has_elementor ? '1' : '0') . ' hasElementorPro=' . ($has_elementor_pro ? '1' : '0') . ' deps=' . implode(',', $deps));
}
add_action( 'wp_enqueue_scripts', 'mlc_enqueue_assets', 20 );

/* ============================================================
 * NUEVA MODAL (SHELL EN FOOTER) - SOLO DOM, NO rompe carrusel
 * (IDs alineados con el JS/CSS nuevos: mlc-modal-overlay, mlc-destination-modal, mlc-rates-modal)
 * ============================================================ */
function mlc_render_new_modals_shell(): void { ?>
	<div class="mlc-modal-overlay" id="mlc-modal-overlay" aria-hidden="true"></div>

	<div class="mlc-modal" id="mlc-destination-modal" role="dialog" aria-modal="true" aria-hidden="true">
		<div class="mlc-modal__header">
			<div class="mlc-modal__kicker"><?php echo esc_html( mlc_t( 'destination_label' ) ); ?></div>
			<h2 class="mlc-modal__title" id="mlc-destination-title"><?php echo esc_html( mlc_t( 'destination_title' ) ); ?></h2>
			<button class="mlc-modal__close" data-mlc-close aria-label="<?php echo esc_attr( mlc_t( 'close' ) ); ?>">×</button>
		</div>
		<div class="mlc-modal__body" id="mlc-destination-body"></div>
	</div>

	<div class="mlc-modal mlc-modal--wide" id="mlc-rates-modal" role="dialog" aria-modal="true" aria-hidden="true">
		<div class="mlc-modal__header">
			<div class="mlc-modal__kicker"><?php echo esc_html( mlc_t( 'property_label' ) ); ?></div>
			<h2 class="mlc-modal__title" id="mlc-property-title"><?php echo esc_html( mlc_t( 'property_title' ) ); ?></h2>
			<button class="mlc-modal__close" data-mlc-close aria-label="<?php echo esc_attr( mlc_t( 'close' ) ); ?>">×</button>
		</div>

		<div class="mlc-modal__tabs">
			<button class="mlc-tab is-active" data-mlc-tab="rates" type="button"><?php echo esc_html( mlc_t( 'tab_rates' ) ); ?></button>
			<button class="mlc-tab" data-mlc-tab="seasonality" type="button"><?php echo esc_html( mlc_t( 'tab_seasonality' ) ); ?></button>
		</div>

		<div class="mlc-modal__body">
			<div class="mlc-tabpanel is-active" data-mlc-panel="rates" id="mlc-rates-body"></div>
			<div class="mlc-tabpanel" data-mlc-panel="seasonality" id="mlc-seasonality-body"></div>
		</div>
	</div>
<?php }
add_action('wp_footer', 'mlc_render_new_modals_shell', 50);

/* ============================================================
 * Texto unificado (EN/ES) para todas las cards
 * ============================================================ */
function mlc_get_unified_description(): string {
	return mlc_t( 'unified_description' );
}
function mlc_get_unified_button_text(): string {
	return mlc_t( 'unified_button_text' );
}

/* ============================================================
 * Cards DESKTOP
 * ============================================================ */
function mlc_get_desktop_cards(): array {
	$lang = mlc_get_lang();

	$desc = mlc_get_unified_description();
	$btn  = mlc_get_unified_button_text();

	$cards = array(
		array(
			'title'          => 'Los Cabos',
			'image'          => plugin_dir_url(__FILE__) . 'img/los-cabos.jpg',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 14843,
			'btn_link'       => '#',
		),
		array(
			'title'          => 'Nuevo Vallarta',
			'image'          => plugin_dir_url(__FILE__) . 'img/nuevo-vallarta.jpg',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 14825,
			'btn_link'       => '#',
		),
		array(
			'title'          => 'Riviera Maya',
			'image'          => plugin_dir_url(__FILE__) . 'img/riviera-maya.jpg',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 14834,
			'btn_link'       => '#',
		),
		
		array(
		'title'          => 'San Miguel de Allende',
		'image'          => plugin_dir_url(__FILE__) . 'img/SMA.png',
		'description_en' => $desc,
		'description_es' => $desc,
		'btn_text_en'    => $btn,
		'btn_text_es'    => $btn,

		// IMPORTANTE:
		// En tu render actual el botón "abre modal del JSON" cuando popup_id NO está vacío
		// (porque imprime .mlc-rates-trigger con data-mlc-destination).
		// Usa cualquier entero > 0.
		'popup_id'       => 1,

		'btn_link'       => '#',
),
	);

	// (Opcional) Re-registro seguro (no hace daño por static $done)
	foreach ( $cards as $c ) {
		if ( ! empty($c['popup_id']) ) {
			mlc_register_elementor_popup( (int) $c['popup_id'] );
		}
	}

	foreach ( $cards as &$c ) {
		$c['description'] = ( $lang === 'es' ) ? $c['description_es'] : $c['description_en'];
		$c['btn_text']    = ( $lang === 'es' ) ? $c['btn_text_es']    : $c['btn_text_en'];
		$c['btn_link']    = mlc_url_with_lang( $c['btn_link'], $lang );
	}
	unset($c);

	return $cards;
}

/* ============================================================
 * Cards MOBILE
 * ============================================================ */
function mlc_get_mobile_cards(): array {
	$lang = mlc_get_lang();

	$desc = mlc_get_unified_description();
	$btn  = mlc_get_unified_button_text();

	$cards = array(
			array(
			'title'          => 'Los Cabos',
			'image'          => plugin_dir_url(__FILE__) . 'img/los-cabos.jpg',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 14843,
			'btn_link'       => '#',
		),
		array(
			'title'          => 'Nuevo Vallarta',
			'image'          => plugin_dir_url(__FILE__) . 'img/nuevo-vallarta.jpg',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 14825,
			'btn_link'       => '#',
		),
		array(
			'title'          => 'Riviera Maya',
			'image'          => plugin_dir_url(__FILE__) . 'img/riviera-maya.jpg',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 14834,
			'btn_link'       => '#',
		),
	
		array(
			'title'          => 'San Miguel de Allende',
			'image'          => plugin_dir_url(__FILE__) . 'img/SMA.png',
			'description_en' => $desc,
			'description_es' => $desc,
			'btn_text_en'    => $btn,
			'btn_text_es'    => $btn,
			'popup_id'       => 1,
			'btn_link'       => '#',
			),
	);

	// (Opcional) Re-registro seguro
	foreach ( $cards as $c ) {
		if ( ! empty($c['popup_id']) ) {
			mlc_register_elementor_popup( (int) $c['popup_id'] );
		}
	}

	foreach ( $cards as &$c ) {
		$c['description'] = ( $lang === 'es' ) ? $c['description_es'] : $c['description_en'];
		$c['btn_text']    = ( $lang === 'es' ) ? $c['btn_text_es']    : $c['btn_text_en'];
		$c['btn_link']    = mlc_url_with_lang( $c['btn_link'], $lang );
	}
	unset($c);

	return $cards;
}

/* ============================================================
 * Shortcode ÚNICO RESPONSIVE (desktop + mobile)
 * ============================================================ */
function mlc_luxury_carousel_shortcode( $atts ) {
	$cards_desktop = mlc_get_desktop_cards();
	$cards_mobile  = mlc_get_mobile_cards();

	ob_start();
	?>

	<!-- DESKTOP -->
	<div class="mlc-luxury-carousel-wrapper" data-mlc-version="<?php echo esc_attr(MLC_VERSION); ?>">
		<div class="mlc-cards-track">
			<?php foreach ( $cards_desktop as $index => $card ) : ?>
<div class="mlc-card"
     data-index="<?php echo esc_attr($index); ?>"
     data-bg="<?php echo esc_url($card['image']); ?>">
					<h2 class="mlc-title"><?php echo esc_html( $card['title'] ); ?></h2>

					<div class="mlc-image">
						<img src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>">
					</div>

					<div class="mlc-description">
						<p><?php echo esc_html( $card['description'] ); ?></p>
					</div>

					<div class="mlc-button-wrapper">
						<?php if ( ! empty($card['popup_id']) ) : ?>
							<!-- NUEVO botón: dispara tu modal custom por destino -->
							<a href="javascript:void(0)"
							class="mlc-button mlc-rates-trigger"
							data-mlc-destination="<?php echo esc_attr($card['title']); ?>"
							aria-haspopup="dialog"
							role="button">
								<?php echo esc_html( $card['btn_text'] ); ?>
							</a>

						<?php else : ?>
							<a href="<?php echo esc_url( $card['btn_link'] ); ?>" class="mlc-button">
								<?php echo esc_html( $card['btn_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div><!-- /.mlc-cards-track -->

		<div class="mlc-carousel-indicators">
			<button class="mlc-prev-arrow" aria-label="<?php echo esc_attr( mlc_t( 'previous' ) ); ?>">&lt;</button>
			<span class="mlc-counter">
				<span class="mlc-current-slide">1</span>/
				<span class="mlc-total-slides"><?php echo (int) count($cards_desktop); ?></span>
			</span>
			<button class="mlc-next-arrow" aria-label="<?php echo esc_attr( mlc_t( 'next' ) ); ?>">&gt;</button>
		</div>
	</div>

	<!-- MOBILE -->
	<div class="mlc-mobile-carousel-wrapper" data-mlc-version="<?php echo esc_attr(MLC_VERSION); ?>">
		<div class="mlc-mobile-track">
			<?php foreach ( $cards_mobile as $index => $card ) : ?>
				<?php if ( ! empty($card['popup_id']) ) : ?>
					<!-- NUEVO trigger mobile: misma card, solo agrega data-mlc-destination -->
					<div class="mlc-mobile-card"
					   data-index="<?php echo esc_attr($index); ?>">
						<div class="mlc-mobile-image">
							<img src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>">
						</div>
						<h3 class="mlc-mobile-title"><?php echo esc_html( $card['title'] ); ?></h3>
						<div class="mlc-mobile-description">
							<p><?php echo esc_html( $card['description'] ); ?></p>
						</div>
						<button type="button"
						        class="mlc-mobile-cta mlc-rates-trigger"
						        data-mlc-destination="<?php echo esc_attr($card['title']); ?>"
						        aria-haspopup="dialog">
							<?php echo esc_html( $card['btn_text'] ); ?>
						</button>
					</div>
				<?php else : ?>
					<div class="mlc-mobile-card"
					   data-index="<?php echo esc_attr($index); ?>">
						<div class="mlc-mobile-image">
							<img src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>">
						</div>
						<h3 class="mlc-mobile-title"><?php echo esc_html( $card['title'] ); ?></h3>
						<div class="mlc-mobile-description">
							<p><?php echo esc_html( $card['description'] ); ?></p>
						</div>
						<a class="mlc-mobile-cta" href="<?php echo esc_url( $card['btn_link'] ); ?>">
							<?php echo esc_html( $card['btn_text'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div><!-- /.mlc-mobile-track -->
	</div><!-- /.mlc-mobile-carousel-wrapper -->

	<?php
	return ob_get_clean();
}
add_shortcode( 'luxury_carousel', 'mlc_luxury_carousel_shortcode' );





add_action('send_headers', function () {
  if (empty($_SERVER['REQUEST_URI'])) return;
  if (strpos($_SERVER['REQUEST_URI'], 'rental-rates-data.json') !== false) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
  }
});

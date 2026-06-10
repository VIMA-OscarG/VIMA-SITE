<?php
/**
 * Plugin Name: VIMA Luxury Map Experience
 * Description: Experiencia tipo Airbnb con Google Maps, 3 capas (Resorts -> Destinations -> Properties), datos por JSON estático y modal de canales.
 * Version: 1.3.1
 * Author: VIMA
 */

if (!defined('ABSPATH')) exit;

class VIMA_Luxury_Map_Experience {
  const VERSION = '1.3.1';

  // Option names
  const OPTION_API_KEY  = 'vima_luxury_map_api_key';
  const OPTION_DATA_URL = 'vima_luxury_map_data_url';

  // Handles
  const HANDLE_STYLE = 'vima-luxury-map';
  const HANDLE_JS    = 'vima-luxury-map';
  const HANDLE_GMAPS = 'vima-google-maps-js';
  const HANDLE_FONT  = 'vima-merriweather';

  private function get_current_lang() {
    $lang = isset($_GET['lang']) ? sanitize_text_field(wp_unslash($_GET['lang'])) : '';
    return strtolower($lang) === 'es' ? 'es' : 'en';
  }

  private function get_i18n($lang = null) {
    $lang = $lang ?: $this->get_current_lang();

    $translations = [
      'en' => [
        'portfolio_title' => 'Rental Properties',
        'stats_title' => 'OUR CURRENT NUMBERS',
        'label_resorts' => 'Current Resorts',
        'label_destinations' => 'Destinations',
        'label_properties' => 'Properties',
        'label_back' => 'Back',
        'label_change' => 'Change',
        'label_viewing' => 'Viewing:',
        'coming_soon' => 'Coming Soon',
        'booking_channels' => 'Booking Channels',
        'booking_channels_modal' => 'Booking Channels modal',
        'channels' => 'Channels',
        'property_gallery' => 'Property gallery',
        'virtual_tour' => 'Virtual Tour',
        'watch_video' => 'Watch Video',
        'close' => 'Close',
        'close_gallery' => 'Close gallery',
        'gallery_title' => 'Hotel Tour',
        'destinations_stat' => 'Destinations',
        'listings_stat' => 'Of Listings',
        'marketing_channels_stat' => 'Marketing Channels',
        'partnerships_stat' => 'Exclusive Partnerships',
        'api_key_missing' => 'Google Maps API Key is missing. Configure it in Settings → VIMA Luxury Map.',
      ],
      'es' => [
        'portfolio_title' => 'Propiedades en renta',
        'stats_title' => 'NUESTROS NÚMEROS ACTUALES',
        'label_resorts' => 'Resorts actuales',
        'label_destinations' => 'Destinos',
        'label_properties' => 'Propiedades',
        'label_back' => 'Volver',
        'label_change' => 'Cambiar',
        'label_viewing' => 'Viendo:',
        'coming_soon' => 'Próximamente',
        'booking_channels' => 'Canales de reserva',
        'booking_channels_modal' => 'Modal de canales de reserva',
        'channels' => 'Canales',
        'property_gallery' => 'Galería de la propiedad',
        'virtual_tour' => 'Tour virtual',
        'watch_video' => 'Ver video',
        'close' => 'Cerrar',
        'close_gallery' => 'Cerrar galería',
        'gallery_title' => 'Recorrido por el hotel',
        'destinations_stat' => 'Destinos',
        'listings_stat' => 'De listados',
        'marketing_channels_stat' => 'Canales de marketing',
        'partnerships_stat' => 'Alianzas exclusivas',
        'api_key_missing' => 'Falta configurar la Google Maps API Key en Settings → VIMA Luxury Map.',
      ],
    ];

    return $translations[$lang] ?? $translations['en'];
  }

  public function __construct() {
    add_action('admin_menu', [$this, 'admin_menu']);
    add_action('admin_init', [$this, 'admin_init']);

    add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    add_shortcode('vima_luxury_map', [$this, 'shortcode']);

    add_action('rest_api_init', [$this, 'register_rest_routes']);

    // Add async/defer to Google Maps loader
    add_filter('script_loader_tag', [$this, 'filter_script_loader_tag'], 10, 3);
  }

  /* ---------------------------
     Admin Settings
  ---------------------------- */
  public function admin_menu() {
    add_options_page(
      'VIMA Luxury Map',
      'VIMA Luxury Map',
      'manage_options',
      'vima-luxury-map',
      [$this, 'settings_page']
    );
  }

  public function admin_init() {
    register_setting('vima_luxury_map_settings', self::OPTION_API_KEY, [
      'type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => ''
    ]);

    register_setting('vima_luxury_map_settings', self::OPTION_DATA_URL, [
      'type' => 'string',
      'sanitize_callback' => 'esc_url_raw',
      'default' => ''
    ]);
  }

  public function settings_page() {
    if (!current_user_can('manage_options')) return;

    $api_key  = get_option(self::OPTION_API_KEY, '');
    $data_url = get_option(self::OPTION_DATA_URL, '');
    ?>
    <div class="wrap">
      <h1>VIMA Luxury Map Experience</h1>

      <form method="post" action="options.php">
        <?php settings_fields('vima_luxury_map_settings'); ?>
        <table class="form-table" role="presentation">

          <tr>
            <th scope="row">
              <label for="<?php echo esc_attr(self::OPTION_API_KEY); ?>">Google Maps API Key</label>
            </th>
            <td>
              <input
                type="text"
                id="<?php echo esc_attr(self::OPTION_API_KEY); ?>"
                name="<?php echo esc_attr(self::OPTION_API_KEY); ?>"
                value="<?php echo esc_attr($api_key); ?>"
                class="regular-text"
                placeholder="AIza..."
                autocomplete="off"
              />
              <p class="description">
                Habilita <strong>Maps JavaScript API</strong> en Google Cloud y restringe por dominio.
              </p>
            </td>
          </tr>

          <tr>
            <th scope="row">
              <label for="<?php echo esc_attr(self::OPTION_DATA_URL); ?>">Data JSON URL (opcional)</label>
            </th>
            <td>
              <input
                type="url"
                id="<?php echo esc_attr(self::OPTION_DATA_URL); ?>"
                name="<?php echo esc_attr(self::OPTION_DATA_URL); ?>"
                value="<?php echo esc_attr($data_url); ?>"
                class="regular-text"
                placeholder="<?php echo esc_attr(plugins_url('assets/data/data.example.json', __FILE__)); ?>"
              />
              <p class="description">
                Si lo dejas vacío, se usará el JSON incluido en el plugin.
              </p>
            </td>
          </tr>

        </table>
        <?php submit_button(); ?>
      </form>

      <hr />
      <h2>Uso</h2>
      <p>Inserta el shortcode:</p>
      <code>[vima_luxury_map]</code>

      <p style="margin-top:12px;">
        Tip: En Elementor usa el widget <strong>Shortcode</strong> y pega <code>[vima_luxury_map]</code>.
      </p>
    </div>
    <?php
  }

  /* ---------------------------
     Assets
  ---------------------------- */
  public function register_assets() {
    $css_url = plugins_url('assets/css/vima-luxury-map.css', __FILE__);
    $js_url  = plugins_url('assets/js/vima-luxury-map.js', __FILE__);

    wp_register_style(self::HANDLE_STYLE, $css_url, [], self::VERSION);
    wp_register_script(self::HANDLE_JS, $js_url, [], self::VERSION, true);

    // Merriweather font
    wp_register_style(
      self::HANDLE_FONT,
      'https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&display=swap',
      [],
      null
    );
  }

  public function filter_script_loader_tag($tag, $handle, $src) {
    if ($handle !== self::HANDLE_GMAPS) return $tag;
    return sprintf('<script src="%s" async defer></script>' . "\n", esc_url($src));
  }

  private function enqueue_google_maps($api_key) {
    $maps_url = add_query_arg([
      'key' => $api_key,
      'v'   => 'weekly',
    ], 'https://maps.googleapis.com/maps/api/js');

    wp_register_script(self::HANDLE_GMAPS, $maps_url, [], null, true);
    wp_enqueue_script(self::HANDLE_GMAPS);
  }

  /* ---------------------------
     REST: data resolver
  ---------------------------- */
  public function register_rest_routes() {
    register_rest_route('vima/v1', '/luxury-map-data', [
      'methods' => 'GET',
      'callback' => [$this, 'rest_get_data'],
      'permission_callback' => '__return_true',
    ]);
  }

  public function rest_get_data(\WP_REST_Request $request) {
    $override_url = get_option(self::OPTION_DATA_URL, '');
    if (!empty($override_url)) {
      return new WP_REST_Response([
        'mode' => 'remote',
        'url'  => $override_url,
      ], 200);
    }

    return new WP_REST_Response([
      'mode' => 'plugin',
      'url'  => plugins_url('assets/data/data.example.json', __FILE__),
    ], 200);
  }

  /* ---------------------------
     Stats defaults
  ---------------------------- */
  private function get_default_stats($lang = null) {
    $i18n = $this->get_i18n($lang);

    return [
      ['value' => 7,   'suffix' => '',   'label' => $i18n['destinations_stat']],
      ['value' => 100, 'suffix' => "'s", 'label' => $i18n['listings_stat']],
      ['value' => 20,  'suffix' => '+',  'label' => $i18n['marketing_channels_stat']],
      ['value' => 22,  'suffix' => '',   'label' => $i18n['partnerships_stat']],
    ];
  }

  /* ---------------------------
     Inline JS: count up stats
  ---------------------------- */
  private function add_inline_stats_counter_script() {
    $js = <<<JS
(function(){
  const roots = document.querySelectorAll('[data-vima-luxury-map="1"]');
  if (!roots.length) return;

  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateCount(el, to, suffix){
    const dur = 1000;
    const start = performance.now();
    const from = 0;

    function tick(now){
      const t = Math.min(1, (now - start) / dur);
      const eased = 1 - Math.pow(1 - t, 3);
      const val = Math.round(from + (to - from) * eased);
      el.textContent = String(val) + (suffix || '');
      if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  function run(section){
    if (!section || section.__vimaDone) return;
    section.__vimaDone = true;

    const items = section.querySelectorAll('[data-vima-stat]');
    items.forEach((node) => {
      const target = parseInt(node.getAttribute('data-target') || '0', 10);
      const suffix = node.getAttribute('data-suffix') || '';
      if (prefersReduced) {
        node.textContent = String(target) + suffix;
        return;
      }
      node.textContent = '0' + suffix;
      animateCount(node, target, suffix);
    });
  }

  roots.forEach((root) => {
    const section = root.querySelector('[data-vima-stats]');
    if (!section) return;

    if (!('IntersectionObserver' in window)) {
      run(section);
      return;
    }

    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          run(section);
          io.disconnect();
        }
      });
    }, { threshold: 0.35 });

    io.observe(section);
  });
})();
JS;

    wp_add_inline_script(self::HANDLE_JS, $js, 'after');
  }

  /* ---------------------------
     Shortcode
  ---------------------------- */
  public function shortcode($atts) {
    $lang = $this->get_current_lang();
    $i18n = $this->get_i18n($lang);
    $atts = shortcode_atts([
      'portfolio_title' => $i18n['portfolio_title'],
      'portfolio_subtitle' => '',
      'stats_title' => $i18n['stats_title'],

      // Labels (para 3 capas). JS los consumirá.
      'label_resorts' => $i18n['label_resorts'],
      'label_destinations' => $i18n['label_destinations'],
      'label_properties' => $i18n['label_properties'],
      'label_back' => $i18n['label_back'],
      'label_change' => $i18n['label_change'],
      'label_viewing' => $i18n['label_viewing'],
    ], $atts, 'vima_luxury_map');

    $api_key = get_option(self::OPTION_API_KEY, '');
    if (empty($api_key)) {
      return '<div class="vima-luxury-map__error">' . esc_html($i18n['api_key_missing']) . '</div>';
    }
    // Enqueue only when shortcode is used
    wp_enqueue_style(self::HANDLE_FONT);
    wp_enqueue_style(self::HANDLE_STYLE);

    $this->enqueue_google_maps($api_key);

    // Make our JS depend on Google Maps
    wp_register_script(
      self::HANDLE_JS,
      plugins_url('assets/js/vima-luxury-map.js', __FILE__),
      [self::HANDLE_GMAPS],
      self::VERSION,
      true
    );
    wp_enqueue_script(self::HANDLE_JS);

    // Config to frontend
    $config = [
      'restDataEndpoint' => esc_url_raw(rest_url('vima/v1/luxury-map-data')),
      'mapOptions' => [
        'defaultZoom'   => 5,
        'defaultCenter' => ['lat' => 23.6345, 'lng' => -102.5528],
        'styles' => null,
          // NUEVO: control fino por capa
        'singleMarkerZoom'       => 8.5, // 1 marcador en capa resorts (global)
        'destinationsSingleZoom' => 13.2, // 1 destino en capa 2 (más cerca)
        'propertiesSingleZoom'   => 13.2, // 1 destino en capa 3 (aún más cerca)
        'resortCenterZoom'       => 12.0, // si el resort trae solo center
        'destinationCenterZoom'  => 13.0, // si el destino trae solo center
      ],
      'ui' => [
        'lang' => $lang,
        'brandTitle' => 'VIMA',
        'labels' => [
          'resorts' => sanitize_text_field($atts['label_resorts']),
          'destinations' => sanitize_text_field($atts['label_destinations']),
          'properties' => sanitize_text_field($atts['label_properties']),
          'back' => sanitize_text_field($atts['label_back']),
          'change' => sanitize_text_field($atts['label_change']),
          'viewing' => sanitize_text_field($atts['label_viewing']),
          'comingSoon' => $i18n['coming_soon'],
          'bookingChannels' => $i18n['booking_channels'],
          'channels' => $i18n['channels'],
          'propertyGallery' => $i18n['property_gallery'],
          'virtualTour' => $i18n['virtual_tour'],
          'watchVideo' => $i18n['watch_video'],
          'close' => $i18n['close'],
          'closeGallery' => $i18n['close_gallery'],
          'galleryTitle' => $i18n['gallery_title'],
        ],
        // El JS nuevo manejará breadcrumbs y capas usando esto como inicial.
        'initialLayer' => 'resorts',
      ],
    ];
    wp_localize_script(self::HANDLE_JS, 'VIMA_LUXURY_MAP_CONFIG', $config);

    // Stats animation
    $this->add_inline_stats_counter_script();

    $instance_id = 'vimaLuxuryMap_' . wp_generate_uuid4();
    $stats = $this->get_default_stats($lang);

    ob_start();
    ?>
    <div class="vima-luxury-map vima-luxury-map--lux" id="<?php echo esc_attr($instance_id); ?>" data-vima-luxury-map="1">
      <div class="vima-luxury-map__chrome">

        <!-- =========================
             SINGLE COLUMN HEADER
             ========================= -->
        <div class="vima-luxury-map__header">
          <h2 class="vima-luxury-map__heading"><?php echo esc_html($atts['portfolio_title']); ?></h2>
          <?php if (!empty($atts['portfolio_subtitle'])): ?>
            <div class="vima-luxury-map__subheading"><?php echo esc_html($atts['portfolio_subtitle']); ?></div>
          <?php endif; ?>
        </div>

        <!-- =========================
             ONLY TWO COLUMNS SECTION
             (Cards | Map)
             ========================= -->
        <div class="vima-luxury-map__section">
          <div class="vima-luxury-map__layout">

            <!-- COL 1: CARDS -->
            <div class="vima-luxury-map__list">

              <!-- Top-left Back + Big Layer Title -->
              <div class="vima-luxury-map__layerbar" data-crumbs>
                <button class="vima-btn vima-btn--ghost vima-layer-back" type="button" data-action="back" disabled>
                  <?php echo esc_html($atts['label_back']); ?>
                </button>

                <!-- Big title (Resorts/Destinations/Properties; lo controla JS) -->
                <div class="vima-luxury-map__layer-title" data-crumb-label>
                  <?php echo esc_html($atts['label_resorts']); ?>
                </div>


                <!-- Dataset tabs: Resorts | Coming Soon (solo capa 1; JS/CSS lo controlan) -->
                <div class="vima-luxury-map__dataset-tabs" data-dataset-tabs>
                  <button class="vima-dataset-tab is-active" type="button" data-dataset="available"><?php echo esc_html($atts['label_resorts']); ?></button>
                  <button class="vima-dataset-tab" type="button" data-dataset="comingSoon"><?php echo esc_html($i18n['coming_soon']); ?></button>
                </div>
              </div>

              <div class="vima-luxury-map__cards" data-cards aria-live="polite"></div>

              <!-- Mobile pill (JS actualizará el texto según capa) -->
             
            </div>

            <!-- COL 2: MAP -->
            <div class="vima-luxury-map__map">
              <div class="vima-luxury-map__map-inner" data-map></div>
            </div>

          </div>
        </div>

        <!-- =========================
             SINGLE COLUMN FOOTER (STATS)
             ========================= -->
        <div class="vima-luxury-map__footer" data-vima-stats>
          <div class="vima-luxury-map__numbers">
            <h3 class="vima-luxury-map__numbers-title"><?php echo esc_html($atts['stats_title']); ?></h3>

            <div class="vima-luxury-map__numbers-grid">
              <?php foreach ($stats as $s): ?>
                <div class="vima-stat">
                  <div
                    class="vima-stat__value"
                    data-vima-stat
                    data-target="<?php echo esc_attr((int)$s['value']); ?>"
                    data-suffix="<?php echo esc_attr($s['suffix']); ?>"
                  >0<?php echo esc_html($s['suffix']); ?></div>
                  <div class="vima-stat__label"><?php echo esc_html($s['label']); ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

      </div>

      <!-- =========================
           MODAL (kept outside chrome to avoid clipping)
           ========================= -->
  <div class="vima-modal" aria-hidden="true" data-modal>
  <div class="vima-modal__backdrop" data-modal-close></div>

  <div class="vima-modal__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($i18n['booking_channels_modal']); ?>">
    <div class="vima-modal__header">
      <!-- Título fijo del modal -->
      <div class="vima-modal__title" data-modal-title><?php echo esc_html($i18n['booking_channels']); ?></div>

      <button class="vima-modal__close" type="button" aria-label="<?php echo esc_attr($i18n['close']); ?>" data-modal-close>✕</button>
    </div>

    <div class="vima-modal__body">
      <div class="vima-modal__grid">
        <!-- Preview (arriba: imagen, abajo: título propiedad + tags) -->
        <div class="vima-modal__left">
          <div class="vima-modal__property">
            <div class="vima-modal__hero" data-modal-hero></div>

            <div class="vima-modal__meta">
              <!-- Título de la propiedad (SIEMPRE debajo de la imagen) -->
              <div class="vima-modal__prop-title" data-modal-prop-title></div>

              <!-- Tags debajo del título -->
              <div class="vima-modal__tags" data-modal-tags></div>
              <button class="vima-modal__jump-btn" type="button" data-modal-view-channels></button>
            </div>
          </div>
        </div>

        <!-- Channels -->
        <div class="vima-modal__right">
          <div class="vima-modal__section-head">
            <div class="vima-modal__section-title"><?php echo esc_html($i18n['channels']); ?></div>
            <button class="vima-modal__jump-btn vima-modal__jump-btn--back" type="button" data-modal-back-to-property></button>
          </div>

          <div class="vima-modal__channels" data-modal-channels></div>
        </div>
      </div>
    </div><!-- /body -->
  </div><!-- /panel -->
</div><!-- /modal -->


<div class="vima-gallery-modal" aria-hidden="true" data-gallery-modal>
  <div class="vima-gallery-modal__backdrop" data-gallery-close></div>

  <div
    class="vima-gallery-modal__sheet"
    role="dialog"
    aria-modal="true"
    aria-label="<?php echo esc_attr($i18n['property_gallery']); ?>"
  >
    <div class="vima-gallery-modal__header">
      <div class="vima-gallery-modal__header-actions">
        <a
          href="#"
          class="vima-gallery-modal__cta vima-gallery-modal__cta--tour"
          target="_blank"
          rel="noopener noreferrer"
          data-gallery-virtual-tour
          hidden
        >
          <span class="vima-gallery-modal__cta-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 3.75C7.167 3.75 3.854 7.102 2.679 11.272a1 1 0 0 0 0 .456C3.854 15.898 7.167 19.25 12 19.25s8.146-3.352 9.321-7.522a1 1 0 0 0 0-.456C20.146 7.102 16.833 3.75 12 3.75Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <span class="vima-gallery-modal__cta-label"><?php echo esc_html($i18n['virtual_tour']); ?></span>
        </a>

        <a
          href="#"
          class="vima-gallery-modal__cta vima-gallery-modal__cta--video"
          target="_blank"
          rel="noopener noreferrer"
          data-gallery-video
          hidden
        >
          <span class="vima-gallery-modal__cta-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.75 8.25 20.25 5.75v12.5l-4.5-2.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <rect x="3.75" y="6.75" width="12" height="10.5" rx="2.25" stroke="currentColor" stroke-width="1.8"/>
              <path d="m10.1 10.05 3.6 1.95-3.6 1.95v-3.9Z" fill="currentColor"/>
            </svg>
          </span>
          <span class="vima-gallery-modal__cta-label"><?php echo esc_html($i18n['watch_video']); ?></span>
        </a>
      </div>

      <button
        class="vima-gallery-modal__close"
        type="button"
        aria-label="<?php echo esc_attr($i18n['close_gallery']); ?>"
        data-gallery-close
      >
        <?php echo esc_html($i18n['close']); ?>
      </button>
    </div>

    <div class="vima-gallery-modal__body" data-gallery-scroll>
      <header class="vima-gallery-modal__intro">
        <h2 class="vima-gallery-modal__title" data-gallery-title>
          <?php echo esc_html($i18n['gallery_title']); ?>
        </h2>
        <div class="vima-gallery-modal__subtitle" data-gallery-subtitle hidden></div>
      </header>

      <div class="vima-gallery-modal__nav-shell">
        <div class="vima-gallery-modal__nav" data-gallery-nav></div>
      </div>

      <div class="vima-gallery-modal__sections" data-gallery-sections></div>
    </div>

    <div class="vima-gallery-modal__footer" aria-hidden="true"></div>
  </div>
</div>

    <?php
    return ob_get_clean();
  }
} 

new VIMA_Luxury_Map_Experience();

<?php
if (!defined('ABSPATH')) {
  exit;
}

class Vima_DH_Acf_Routes {

  const NS = 'vima/v1';

  // Cache keys (v1 por si cambias el shape)
  const CACHE_INVENTORY = 'vima_dh_export_inventory_v1';
  const CACHE_RATES     = 'vima_dh_export_rental_rates_v1';

  // TTL (segundos). Con invalidación por save_post, puedes usar TTL alto.
  const CACHE_TTL = 3600; // 1 hora

  public static function register_routes() : void {

    // Endpoints export para consumo por plugins
    register_rest_route(self::NS, '/export/inventory', [
      'methods'             => WP_REST_Server::READABLE,
      'callback'            => [__CLASS__, 'export_inventory'],
      'permission_callback' => '__return_true', // lectura pública; si quieres auth lo cambiamos
    ]);

    register_rest_route(self::NS, '/export/rental-rates', [
      'methods'             => WP_REST_Server::READABLE,
      'callback'            => [__CLASS__, 'export_rental_rates'],
      'permission_callback' => '__return_true',
    ]);

    // Invalida cache al editar datos
    self::register_cache_invalidation_hooks();
  }

  private static function register_cache_invalidation_hooks() : void {
    // Cada vez que se guarda algo que impacta export, invalidamos.
    $post_types = ['vima_property', 'vima_resort', 'vima_destination', 'vima_channel', 'vima_season'];

    foreach ($post_types as $pt) {
      add_action("save_post_{$pt}", [__CLASS__, 'invalidate_cache_on_save'], 20, 3);
      add_action("deleted_post", [__CLASS__, 'invalidate_cache_on_delete'], 20, 1);
      add_action("trashed_post", [__CLASS__, 'invalidate_cache_on_delete'], 20, 1);
      add_action("untrashed_post", [__CLASS__, 'invalidate_cache_on_delete'], 20, 1);
    }
  }

  public static function invalidate_cache_on_save($post_id, $post, $update) : void {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
      return;
    }
    self::invalidate_cache();
  }

  public static function invalidate_cache_on_delete($post_id) : void {
    // No sabemos el post_type en delete sin get_post (puede ser null).
    self::invalidate_cache();
  }

  private static function invalidate_cache() : void {
    delete_transient(self::CACHE_INVENTORY);
    delete_transient(self::CACHE_RATES);
  }

  /**
   * GET /vima/v1/export/inventory
   *
   * Shape: resorts[] -> destinations[] -> properties[] -> channels[]
   * Basado en el JSON original: resorts/destinations/properties/channels. :contentReference[oaicite:0]{index=0}
   */
    public static function export_inventory(WP_REST_Request $request) {
        $cached = get_transient(self::CACHE_INVENTORY);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        $destinations_indexed = self::get_all_posts_indexed('vima_destination');
        $resorts = self::get_all_posts('vima_resort');
        $properties = self::get_all_posts('vima_property');

        // Agrupar properties por resort y luego por destination
        // [resort_id][dest_id] = [properties...]
        $props_by_resort_dest = [];
        foreach ($properties as $prop) {
            $resort_id = (int) self::acf_get_id($prop->ID, 'resort');
            $dest_id   = (int) self::acf_get_id($prop->ID, 'destination');
            if ($resort_id <= 0 || $dest_id <= 0) continue;

            if (!isset($props_by_resort_dest[$resort_id])) {
            $props_by_resort_dest[$resort_id] = [];
            }
            if (!isset($props_by_resort_dest[$resort_id][$dest_id])) {
            $props_by_resort_dest[$resort_id][$dest_id] = [];
            }
            $props_by_resort_dest[$resort_id][$dest_id][] = $prop;
        }

        $out_resorts = [];

        foreach ($resorts as $resort) {
            $resort_id = (int) $resort->ID;

            $destinations_for_resort = $props_by_resort_dest[$resort_id] ?? [];
            if (empty($destinations_for_resort)) {
            // Si quieres incluir resorts sin properties, quita este continue
            continue;
            }

            $out_destinations = [];
            foreach ($destinations_for_resort as $dest_id => $props_list) {
            if (!isset($destinations_indexed[$dest_id])) continue;

            $dest_post = $destinations_indexed[$dest_id];

            $out_props = [];
            foreach ($props_list as $prop) {
                $out_props[] = self::build_property_inventory($prop);
            }

            $out_destinations[] = [
                'id' => (int) $dest_post->ID,
                'name' => get_the_title($dest_post),
                'properties' => $out_props,
            ];
            }

            $out_resorts[] = [
            'id' => $resort_id,
            'name' => get_the_title($resort),
            'destinations' => $out_destinations,
            ];
        }

        $payload = [
            'resorts' => $out_resorts,
            'generatedAt' => gmdate('c'),
            'version' => 'v1',
        ];

        set_transient(self::CACHE_INVENTORY, $payload, self::CACHE_TTL);
        return rest_ensure_response($payload);
    }


  /**
   * GET /vima/v1/export/rental-rates
   *
   * Shape: destinations[] -> resorts[] -> properties[] -> rates[] + seasonality[]
   * Basado en tu JSON original de rates. :contentReference[oaicite:1]{index=1}
   */
    public static function export_rental_rates(WP_REST_Request $request) {
        $cached = get_transient(self::CACHE_RATES);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        $destinations_indexed = self::get_all_posts_indexed('vima_destination');
        $resorts = self::get_all_posts('vima_resort');
        $properties = self::get_all_posts('vima_property');

        // [resort_id][dest_id] = [properties...]
        $props_by_resort_dest = [];
        foreach ($properties as $prop) {
            $resort_id = (int) self::acf_get_id($prop->ID, 'resort');
            $dest_id   = (int) self::acf_get_id($prop->ID, 'destination');
            if ($resort_id <= 0 || $dest_id <= 0) continue;

            if (!isset($props_by_resort_dest[$resort_id])) $props_by_resort_dest[$resort_id] = [];
            if (!isset($props_by_resort_dest[$resort_id][$dest_id])) $props_by_resort_dest[$resort_id][$dest_id] = [];
            $props_by_resort_dest[$resort_id][$dest_id][] = $prop;
        }

        $out_resorts = [];

        foreach ($resorts as $resort) {
            $resort_id = (int) $resort->ID;
            $destinations_for_resort = $props_by_resort_dest[$resort_id] ?? [];
            if (empty($destinations_for_resort)) continue;

            $out_destinations = [];
            foreach ($destinations_for_resort as $dest_id => $props_list) {
            if (!isset($destinations_indexed[$dest_id])) continue;

            $dest_post = $destinations_indexed[$dest_id];

            $out_props = [];
            foreach ($props_list as $prop) {
                // build_property_rates ya incluye rates + seasonality
                $out_props[] = self::build_property_rates($prop);
            }

            $out_destinations[] = [
                'id' => (int) $dest_post->ID,
                'name' => get_the_title($dest_post),
                'properties' => $out_props,
            ];
            }

            $out_resorts[] = [
            'id' => $resort_id,
            'name' => get_the_title($resort),
            'destinations' => $out_destinations,
            ];
        }

        $payload = [
            'resorts' => $out_resorts,
            'generatedAt' => gmdate('c'),
            'version' => 'v1',
        ];

        set_transient(self::CACHE_RATES, $payload, self::CACHE_TTL);
        return rest_ensure_response($payload);
    }


  /**
   * Construye property para el export inventory.
   */
  private static function build_property_inventory(WP_Post $prop) : array {
    $thumb_id = (int) get_field('thumb', $prop->ID);
    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'full') : null;

    $channels_rows = get_field('channels', $prop->ID);
    $channels_out = [];

    if (is_array($channels_rows)) {
      foreach ($channels_rows as $row) {
        $channel_id = isset($row['channel']) ? (int) $row['channel'] : 0;
        if ($channel_id <= 0) continue;

        $channel_post = get_post($channel_id);
        if (!$channel_post || $channel_post->post_type !== 'vima_channel') continue;

        $urls = [];
        $urls_rows = $row['urls'] ?? [];
        if (is_array($urls_rows)) {
          foreach ($urls_rows as $urow) {
            $u = isset($urow['url']) ? trim((string)$urow['url']) : '';
            if ($u !== '') $urls[] = $u;
          }
        }

        // Compatibilidad con tu JSON: url + urls[].
        // url = primera url, urls = todas (incluyendo la primera).
        $primary_url = $urls[0] ?? '';

        $logo_id = (int) get_field('logo', $channel_id);
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : null;

        $channels_out[] = [
          'id' => (string) get_field('channel_key', $channel_id),
          'label' => (string) get_field('label', $channel_id),
          'logo' => $logo_url,
          'url' => $primary_url,
          'urls' => $urls,
          'previewMode' => (string) get_field('preview_mode', $channel_id),
          'previewDescription' => (string) get_field('preview_description', $channel_id),
        ];
      }
    }

    return [
      // Tu JSON original tiene "id" (string) para property; aquí exponemos WP ID y los external IDs.
      'wpId' => (int) $prop->ID,
      'id' => (string) get_field('inventory_property_id', $prop->ID),
      'name' => get_the_title($prop),
      'thumb' => $thumb_url,
      'listingPopupId' => (string) get_field('listing_popup_id', $prop->ID),
      'tags' => [], // si luego agregas taxonomy/tags, lo llenamos aquí
      'channels' => $channels_out,
    ];
  }

  /**
   * Construye property para export rental-rates.
   */
  private static function build_property_rates(WP_Post $prop) : array {
    $rates_rows = get_field('rates', $prop->ID);
    $rates_out = [];

    if (is_array($rates_rows)) {
      foreach ($rates_rows as $row) {
        $season_id = isset($row['season']) ? (int) $row['season'] : 0;
        if ($season_id <= 0) continue;

        $season_key = (string) get_field('season_key', $season_id);
        $amount = isset($row['amount']) ? $row['amount'] : null;
        $currency = isset($row['currency']) ? strtoupper(trim((string)$row['currency'])) : 'USD';

        $rates_out[] = [
          'season' => $season_key,
          'amount' => $amount,
          'currency' => $currency,
        ];
      }
    }

    $seasonality_rows = get_field('seasonality', $prop->ID);
    $seasonality_out = [];

    if (is_array($seasonality_rows)) {
      foreach ($seasonality_rows as $srow) {
        $season_id = isset($srow['season']) ? (int) $srow['season'] : 0;
        if ($season_id <= 0) continue;

        $season_key = (string) get_field('season_key', $season_id);

        $ranges = [];
        $ranges_rows = $srow['ranges'] ?? [];
        if (is_array($ranges_rows)) {
          foreach ($ranges_rows as $rrow) {
            $start = isset($rrow['start_date']) ? (string)$rrow['start_date'] : '';
            $end   = isset($rrow['end_date']) ? (string)$rrow['end_date'] : '';
            if ($start !== '' && $end !== '') {
              $ranges[] = [
                'start' => $start,
                'end' => $end,
              ];
            }
          }
        }

        $seasonality_out[] = [
          'season' => $season_key,
          'ranges' => $ranges,
        ];
      }
    }

    return [
      'wpId' => (int) $prop->ID,
      'property_name' => get_the_title($prop),
      'property_popup_id' => (string) get_field('rates_popup_id', $prop->ID),
      'rates' => $rates_out,
      'seasonality' => $seasonality_out,
    ];
  }

  /**
   * Helpers
   */
  private static function acf_get_id(int $post_id, string $field_name) : int {
    $val = get_field($field_name, $post_id);
    if (is_numeric($val)) return (int)$val;
    if (is_object($val) && isset($val->ID)) return (int)$val->ID;
    return 0;
  }

  private static function get_all_posts(string $post_type) : array {
    $q = new WP_Query([
      'post_type'      => $post_type,
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
      'no_found_rows'  => true,
      'fields'         => 'all',
    ]);
    return $q->posts ?: [];
  }

  /**
   * Devuelve array indexado por ID para accesos rápidos.
   * @return array<int, WP_Post>
   */
  private static function get_all_posts_indexed(string $post_type) : array {
    $posts = self::get_all_posts($post_type);
    $indexed = [];
    foreach ($posts as $p) {
      $indexed[(int)$p->ID] = $p;
    }
    return $indexed;
  }
}

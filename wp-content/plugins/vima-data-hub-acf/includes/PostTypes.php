<?php
if (!defined('ABSPATH')) {
  exit;
}

class Vima_DH_Acf_PostTypes {

  /**
   * Registra todos los Custom Post Types del Data Hub.
   *
   * Nota:
   * - show_in_rest => true para que puedas usar el WP REST API si lo necesitas,
   *   aunque nuestros endpoints export van aparte.
   * - public => false y show_ui => true porque son entidades de datos (admin-only).
   */
  public static function register() : void {
    self::register_destination();
    self::register_resort();
    self::register_property();
    self::register_channel();
    self::register_season();
  }

  private static function common_args(string $singular, string $plural, string $menu_icon) : array {
    $labels = [
      'name'               => $plural,
      'singular_name'      => $singular,
      'menu_name'          => $plural,
      'name_admin_bar'     => $singular,
      'add_new'            => 'Add New',
      'add_new_item'       => 'Add New ' . $singular,
      'new_item'           => 'New ' . $singular,
      'edit_item'          => 'Edit ' . $singular,
      'view_item'          => 'View ' . $singular,
      'all_items'          => 'All ' . $plural,
      'search_items'       => 'Search ' . $plural,
      'not_found'          => 'No ' . strtolower($plural) . ' found.',
      'not_found_in_trash' => 'No ' . strtolower($plural) . ' found in Trash.',
    ];

    return [
      'labels'             => $labels,
      'public'             => false,
      'show_ui'            => true,
      'show_in_menu'       => VIMA_DH_ACF_MENU_SLUG,
      'show_in_admin_bar'  => true,
      'show_in_rest'       => true,
      'menu_position'      => 25,
      'menu_icon'          => $menu_icon,
      'supports'           => ['title'],
      'hierarchical'       => false,
      'has_archive'        => false,
      'rewrite'            => false,
      'query_var'          => false,
      'capability_type'    => 'post',
      'map_meta_cap'       => true,
    ];
  }

  private static function register_destination() : void {
    $args = self::common_args('Destination', 'Destinations', 'dashicons-location');

    // Si quieres campos custom en el editor, ACF lo controla.
    register_post_type('vima_destination', $args);
  }

  private static function register_resort() : void {
    $args = self::common_args('Resort', 'Resorts', 'dashicons-building');

    register_post_type('vima_resort', $args);
  }

  private static function register_property() : void {
    $args = self::common_args('Property', 'Properties', 'dashicons-admin-home');

    // Esto ayuda para búsquedas por título
    $args['supports'] = ['title'];

    register_post_type('vima_property', $args);
  }

  private static function register_channel() : void {
    $args = self::common_args('Channel', 'Channels', 'dashicons-admin-links');

    register_post_type('vima_channel', $args);
  }

  private static function register_season() : void {
    $args = self::common_args('Season', 'Seasons', 'dashicons-calendar-alt');

    register_post_type('vima_season', $args);
  }
}

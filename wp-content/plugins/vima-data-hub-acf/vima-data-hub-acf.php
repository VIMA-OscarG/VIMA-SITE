<?php
/**
 * Plugin Name: VIMA Data Hub (ACF)
 * Description: Data hub para VIMA usando CPTs + ACF y endpoints REST de export.
 * Version: 0.1.0
 * Author: VIMA
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
  exit;
}

define('VIMA_DH_ACF_VERSION', '0.1.0');
define('VIMA_DH_ACF_PLUGIN_FILE', __FILE__);
define('VIMA_DH_ACF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VIMA_DH_ACF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Admin menu slug (padre)
define('VIMA_DH_ACF_MENU_SLUG', 'vima-data-hub');

// Includes
require_once VIMA_DH_ACF_PLUGIN_DIR . 'includes/PostTypes.php';
require_once VIMA_DH_ACF_PLUGIN_DIR . 'includes/AcfFields.php';
require_once VIMA_DH_ACF_PLUGIN_DIR . 'includes/Rest/Routes.php';

/**
 * Admin notice si ACF no está activo.
 */
function vima_dh_acf_admin_notice_missing_acf() {
  if (!current_user_can('activate_plugins')) {
    return;
  }
  echo '<div class="notice notice-error"><p>'
    . esc_html__('VIMA Data Hub (ACF) requiere Advanced Custom Fields (ACF) activo (idealmente ACF Pro).', 'vima-dh-acf')
    . '</p></div>';
}

/**
 * Detecta si ACF está disponible.
 */
function vima_dh_acf_is_acf_available() {
  return function_exists('acf_add_local_field_group');
}

/**
 * Crea menú padre "VIMA Data Hub" y submenús (CPT lists).
 * Nota: los CPT deben tener show_in_menu = VIMA_DH_ACF_MENU_SLUG para que cuelguen del padre.
 */
function vima_dh_acf_register_admin_menu() {
  add_menu_page(
    'VIMA Data Hub',
    'VIMA Data Hub',
    'manage_options',              // si quieres capability custom, lo cambiamos
    VIMA_DH_ACF_MENU_SLUG,
    'vima_dh_acf_render_admin_landing',
    'dashicons-database',
    25
  );

  // Submenús que apuntan a las pantallas nativas de listado de CPT
  add_submenu_page(
    VIMA_DH_ACF_MENU_SLUG,
    'Destinations',
    'Destinations',
    'manage_options',
    'edit.php?post_type=vima_destination'
  );

  add_submenu_page(
    VIMA_DH_ACF_MENU_SLUG,
    'Resorts',
    'Resorts',
    'manage_options',
    'edit.php?post_type=vima_resort'
  );

  add_submenu_page(
    VIMA_DH_ACF_MENU_SLUG,
    'Properties',
    'Properties',
    'manage_options',
    'edit.php?post_type=vima_property'
  );

  add_submenu_page(
    VIMA_DH_ACF_MENU_SLUG,
    'Channels',
    'Channels',
    'manage_options',
    'edit.php?post_type=vima_channel'
  );

  add_submenu_page(
    VIMA_DH_ACF_MENU_SLUG,
    'Seasons',
    'Seasons',
    'manage_options',
    'edit.php?post_type=vima_season'
  );
}

/**
 * Landing del menú padre.
 */
function vima_dh_acf_render_admin_landing() {
  echo '<div class="wrap">';
  echo '<h1>VIMA Data Hub</h1>';
  echo '<p>Centro de datos para administrar Destinations, Resorts, Properties, Channels y Seasons.</p>';

  echo '<h2>Links rápidos</h2>';
  echo '<ul style="list-style: disc; padding-left: 20px;">';
  echo '<li><a href="' . esc_url(admin_url('edit.php?post_type=vima_destination')) . '">Destinations</a></li>';
  echo '<li><a href="' . esc_url(admin_url('edit.php?post_type=vima_resort')) . '">Resorts</a></li>';
  echo '<li><a href="' . esc_url(admin_url('edit.php?post_type=vima_property')) . '">Properties</a></li>';
  echo '<li><a href="' . esc_url(admin_url('edit.php?post_type=vima_channel')) . '">Channels</a></li>';
  echo '<li><a href="' . esc_url(admin_url('edit.php?post_type=vima_season')) . '">Seasons</a></li>';
  echo '</ul>';

  echo '<h2>Endpoints</h2>';
  echo '<ul style="list-style: disc; padding-left: 20px;">';
  echo '<li><code>' . esc_html(rest_url('vima/v1/export/inventory')) . '</code></li>';
  echo '<li><code>' . esc_html(rest_url('vima/v1/export/rental-rates')) . '</code></li>';
  echo '</ul>';

  echo '</div>';
}

/**
 * Bootstrap del plugin.
 */
function vima_dh_acf_bootstrap() {
  // 0) Menú agrupado (antes de que WP arme el sidebar)
  add_action('admin_menu', 'vima_dh_acf_register_admin_menu', 9);

  // 1) Registrar CPTs temprano
  add_action('init', ['Vima_DH_Acf_PostTypes', 'register'], 5);

  // 2) Registrar rutas REST
  add_action('rest_api_init', ['Vima_DH_Acf_Routes', 'register_routes']);

  // 3) Registrar field groups cuando ACF esté listo
  if (vima_dh_acf_is_acf_available()) {
    add_action('acf/init', ['Vima_DH_Acf_Fields', 'register_field_groups']);
  } else {
    add_action('admin_notices', 'vima_dh_acf_admin_notice_missing_acf');
  }
}
add_action('plugins_loaded', 'vima_dh_acf_bootstrap');

/**
 * Activation: registrar CPTs y flush rewrite rules
 */
function vima_dh_acf_activate() {
  if (class_exists('Vima_DH_Acf_PostTypes')) {
    Vima_DH_Acf_PostTypes::register();
  }
  flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'vima_dh_acf_activate');

/**
 * Deactivation: flush rewrite rules
 */
function vima_dh_acf_deactivate() {
  flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'vima_dh_acf_deactivate');

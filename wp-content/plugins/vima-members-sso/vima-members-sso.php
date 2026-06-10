<?php
/**
 * Plugin Name: VIMA Members SSO
 * Description: Provides SSO token endpoint for members subdomain.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
  register_rest_route('vima/v1', '/sso-token', [
    'methods'  => 'GET',
    'callback' => 'vima_sso_token_handler',
    'permission_callback' => function () {
      return is_user_logged_in();
    },
  ]);

  register_rest_route('vima/v1', '/me', [
    'methods'  => 'GET',
    'callback' => 'vima_me_handler',
    'permission_callback' => function () {
      return is_user_logged_in();
    },
  ]);
});

/**
 * IMPORTANT:
 * Set this secret in wp-config.php:
 * define('VIMA_SSO_SECRET', '...long random...');
 */
function vima_get_secret() {
  if (!defined('VIMA_SSO_SECRET') || empty(VIMA_SSO_SECRET)) {
    return null;
  }
  return VIMA_SSO_SECRET;
}

function vima_base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function vima_hmac_sha256($data, $secret) {
  return hash_hmac('sha256', $data, $secret, true);
}

function vima_is_admin_user($user_id) {
  $user = get_user_by('id', $user_id);
  if (!$user) return false;
  // "manage_options" suele ser admin; ajusta si necesitas otro capability/rol.
  return user_can($user, 'manage_options');
}

function vima_sso_token_handler(WP_REST_Request $request) {
  $secret = vima_get_secret();
  if (!$secret) {
    return new WP_REST_Response(['error' => 'SSO secret not configured'], 500);
  }

  $user_id = get_current_user_id();
  $user = get_user_by('id', $user_id);
  if (!$user) {
    return new WP_REST_Response(['error' => 'User not found'], 404);
  }

  $iat = time();
  $exp = $iat + 60; // token válido 60 segundos
  $nonce = wp_generate_password(16, false, false);

  $payload = [
    'iss' => home_url(),
    'aud' => 'members.vacationintervalsmanagement.com',
    'wp_user_id' => $user_id,
    'email' => $user->user_email,
    'display_name' => (string) $user->display_name,
    'first_name'   => (string) get_user_meta($user->ID, 'first_name', true),
    'last_name'    => (string) get_user_meta($user->ID, 'last_name', true),
    'is_admin' => vima_is_admin_user($user_id),
    'iat' => $iat,
    'exp' => $exp,
    'nonce' => $nonce,
  ];

  $payload_json = wp_json_encode($payload);
  $payload_b64 = vima_base64url_encode($payload_json);
  $sig_b64 = vima_base64url_encode(vima_hmac_sha256($payload_b64, $secret));

  $token = $payload_b64 . '.' . $sig_b64;

  return new WP_REST_Response([
    'token' => $token,
    'exp' => $exp,
  ], 200);
}

function vima_me_handler(WP_REST_Request $request) {
  $user_id = get_current_user_id();
  $user = get_user_by('id', $user_id);
  if (!$user) {
    return new WP_REST_Response(['error' => 'User not found'], 404);
  }
  return new WP_REST_Response([
    'wp_user_id' => $user_id,
    'email' => $user->user_email,
    'display_name' => $user->display_name,
    'is_admin' => vima_is_admin_user($user_id),
  ], 200);
}


add_action('init', function () {
  add_rewrite_rule('^members-sso/?$', 'index.php?vima_members_sso=1', 'top');
  add_rewrite_tag('%vima_members_sso%', '1');
});

add_action('template_redirect', function () {
  if (get_query_var('vima_members_sso') != '1') return;

  // Si no está logueado, manda a login y vuelve a intentar
  if (!is_user_logged_in()) {
    $login_url = wp_login_url(home_url('/members-sso/'));
    wp_redirect($login_url);
    exit;
  }

  $secret = vima_get_secret();
  if (!$secret) {
    wp_die('SSO secret not configured', 500);
  }

  $user_id = get_current_user_id();
  $user = get_user_by('id', $user_id);
  if (!$user) {
    wp_die('User not found', 404);
  }

  $iat = time();
  $exp = $iat + 60;
  $nonce = wp_generate_password(16, false, false);

  $payload = [
    'iss' => home_url(),
    'aud' => 'members.vacationintervalsmanagement.com',
    'wp_user_id' => $user_id,
    'email' => $user->user_email,
    'is_admin' => vima_is_admin_user($user_id),
    'display_name' => (string) $user->display_name,
    'first_name'   => (string) get_user_meta($user->ID, 'first_name', true),
    'last_name'    => (string) get_user_meta($user->ID, 'last_name', true),
    'iat' => $iat,
    'exp' => $exp,
    'nonce' => $nonce,
  ];

  $payload_json = wp_json_encode($payload);
  $payload_b64 = vima_base64url_encode($payload_json);
  $sig_b64 = vima_base64url_encode(vima_hmac_sha256($payload_b64, $secret));
  $token = $payload_b64 . '.' . $sig_b64;

  $redirect = 'https://members.vacationintervalsmanagement.com/sso?token=' . rawurlencode($token);
  wp_redirect($redirect);
  exit;
});


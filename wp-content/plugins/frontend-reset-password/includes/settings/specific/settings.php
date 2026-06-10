<?php
/**
 * Plugin-specific settings registration for Frontend Reset Password.
 * 
 * This file contains all plugin-specific code that should NOT be overwritten
 * when syncing from the settings-framework. It includes:
 * - Option name filters for this plugin's settings pages
 * - Settings sanitization filters
 * - Plugin-specific REST endpoints
 * - Typesense search configuration
 * 
 * The common settings endpoint in organization/wp-enhanced/rest-endpoints/
 * remains plugin-agnostic and can be safely synced from settings-framework.
 */

// =============================================================================
// PLUGIN REGISTRATION FOR MULTI-PLUGIN SUPPORT
// =============================================================================

add_action( 'wpe_settings_register_plugin', 'somfrp_register_settings_plugin' );
function somfrp_register_settings_plugin( $registry ) {
    $registry->add( array(
        'slug'              => 'frontend-reset-password',
        'label'             => 'Frontend Reset Password',
        'script_url'        => plugins_url( 'includes/settings/dist/frontend-reset-password-pages.js', SOMFRP_FILE ),
        'version'           => defined( 'SOMFRP_VERSION' ) ? SOMFRP_VERSION : '1.0.0',
        'framework_version' => defined( 'WPE_SETTINGS_FRAMEWORK_VERSION' ) ? WPE_SETTINGS_FRAMEWORK_VERSION : '2.0.0',
    ) );
}

// =============================================================================
// TYPESENSE SEARCH CONFIGURATION
// =============================================================================

add_filter('wpe_typesense_configs', 'somfrp_register_typesense_config', 10, 1);

function somfrp_register_typesense_config($configs) {
  $configs[] = array(
    'searchOnlyApiKey' => 'q6o1PCxsm5f0awphPnTpFPJEyejgNE6r',
    'nodes' => array(
      array('host' => 'search.wpenhanced.com', 'port' => 443, 'protocol' => 'https'),
    ),
    'collection' => 'frontend-reset-password',
  );
  return $configs;
}

// =============================================================================
// OPTION NAME FILTERS
// =============================================================================

add_filter('wpe_option_name_frontend-reset-password-gen', function() {
  return 'somfrp_gen_settings';
});

add_filter('wpe_option_name_frontend-reset-password-security', function() {
  return 'somfrp_security_settings';
});

add_filter('wpe_option_name_frontend-reset-password-design', function() {
  return 'somfrp_design_settings';
});

// =============================================================================
// SETTINGS SANITIZATION FILTERS
// =============================================================================

add_filter('wpe_settings_sanitize_frontend-reset-password-gen', function($sanitized, $data) {
  $out = [];
  $out['somfrp_reset_page'] = isset($data['somfrp_reset_page']) ? absint($data['somfrp_reset_page']) : 0;
  $out['somfrp_request_success_page'] = isset($data['somfrp_request_success_page']) ? absint($data['somfrp_request_success_page']) : 0;
  $out['somfrp_reset_success_page'] = isset($data['somfrp_reset_success_page']) ? absint($data['somfrp_reset_success_page']) : 0;
  $out['somfrp_login_page'] = isset($data['somfrp_login_page']) ? absint($data['somfrp_login_page']) : 0;
  $out['somfrp_reset_form_title'] = isset($data['somfrp_reset_form_title']) ? sanitize_text_field($data['somfrp_reset_form_title']) : '';
  $out['somfrp_reset_lost_message'] = isset($data['somfrp_reset_lost_message']) ? sanitize_textarea_field($data['somfrp_reset_lost_message']) : '';
  $out['somfrp_reset_new_message'] = isset($data['somfrp_reset_new_message']) ? sanitize_textarea_field($data['somfrp_reset_new_message']) : '';
  $out['somfrp_reset_button_text'] = isset($data['somfrp_reset_button_text']) ? sanitize_text_field($data['somfrp_reset_button_text']) : '';
  
  // ColorFieldset sends RGBA object {r, g, b, a} - convert to rgba() string
  $out['somfrp_notice_bg'] = somfrp_sanitize_color($data['somfrp_notice_bg'] ?? '');
  
  $out['somfrp_email_message'] = isset($data['somfrp_email_message']) ? sanitize_textarea_field($data['somfrp_email_message']) : '';
  $out['somfrp_reset_link_text'] = isset($data['somfrp_reset_link_text']) ? sanitize_text_field($data['somfrp_reset_link_text']) : '';
  $out['somfrp_email_subject'] = isset($data['somfrp_email_subject']) ? sanitize_text_field($data['somfrp_email_subject']) : '';
  $out['somfrp_from_name'] = isset($data['somfrp_from_name']) ? sanitize_text_field($data['somfrp_from_name']) : '';
  $out['somfrp_email_address'] = isset($data['somfrp_email_address']) ? sanitize_email($data['somfrp_email_address']) : '';
  return $out;
}, 10, 2);

/**
 * Sanitize color value from ColorFieldset.
 * Accepts RGBA object {r, g, b, a}, rgba() string, or hex string.
 * Returns rgba() string for storage.
 */
function somfrp_sanitize_color($value) {
  if (empty($value)) {
    return '';
  }
  
  // Handle RGBA object from ColorFieldset: {r: 255, g: 0, b: 0, a: 1}
  if (is_array($value) && isset($value['r'], $value['g'], $value['b'])) {
    $r = absint($value['r']);
    $g = absint($value['g']);
    $b = absint($value['b']);
    $a = isset($value['a']) ? floatval($value['a']) : 1;
    
    // Clamp values
    $r = min(255, max(0, $r));
    $g = min(255, max(0, $g));
    $b = min(255, max(0, $b));
    $a = min(1, max(0, $a));
    
    return sprintf('rgba(%d, %d, %d, %.2f)', $r, $g, $b, $a);
  }
  
  // Handle string values (hex or rgba)
  if (is_string($value)) {
    $value = trim($value);
    
    // Allow rgba() strings
    if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $value)) {
      return sanitize_text_field($value);
    }
    
    // Allow hex colors
    $hex = sanitize_hex_color($value);
    if ($hex) {
      return $hex;
    }
  }
  
  return '';
}

add_filter('wpe_settings_sanitize_frontend-reset-password-security', function($sanitized, $data) {
  $out = [];
  $out['somfrp_pass_length'] = isset($data['somfrp_pass_length']) ? absint($data['somfrp_pass_length']) : 0;
  $out['somfrp_pass_lowercase'] = !empty($data['somfrp_pass_lowercase']) ? 'on' : '';
  $out['somfrp_pass_uppercase'] = !empty($data['somfrp_pass_uppercase']) ? 'on' : '';
  $out['somfrp_pass_number'] = !empty($data['somfrp_pass_number']) ? 'on' : '';
  $out['somfrp_pass_special'] = !empty($data['somfrp_pass_special']) ? 'on' : '';
  // Sanitize special characters - allow only printable ASCII characters, default to OWASP list
  $default_special_chars = ' !"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';
  $out['somfrp_special_chars'] = isset($data['somfrp_special_chars']) && $data['somfrp_special_chars'] !== '' 
    ? $data['somfrp_special_chars'] 
    : $default_special_chars;
  return $out;
}, 10, 2);

add_filter('wpe_settings_sanitize_frontend-reset-password-design', function($sanitized, $data) {
  $out = [];
  $out['somfrp_enable_eye_toggle'] = !empty($data['somfrp_enable_eye_toggle']) ? 'on' : '';
  return $out;
}, 10, 2);


<?php
/**
 * Plugin Name: Log WP Mail (diag)
 * Description: Loguea todos los wp_mail/PHPMailer con backtrace para identificar quién envía el correo.
 */

// Log en wp_mail (nivel alto para correr al final)
add_filter('wp_mail', function ($args) {
    error_log('[MAIL-LOGGER] wp_mail args: '.print_r($args, true));
    if (function_exists('debug_backtrace')) {
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
        error_log("[MAIL-LOGGER] Backtrace wp_mail:\n".print_r($bt, true));
    }
    return $args;
}, 9999);

// Log en PHPMailer (por si algún plugin salta wp_mail y toca el mailer)
add_action('phpmailer_init', function ($phpmailer) {
    $summary = sprintf(
        "To=%s | Subject=%s",
        is_array($phpmailer->getToAddresses()) ? json_encode($phpmailer->getToAddresses()) : 'n/a',
        $phpmailer->Subject
    );
    error_log('[MAIL-LOGGER] phpmailer_init: '.$summary);
    if (function_exists('debug_backtrace')) {
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
        error_log("[MAIL-LOGGER] Backtrace phpmailer_init:\n".print_r($bt, true));
    }
}, 9999);

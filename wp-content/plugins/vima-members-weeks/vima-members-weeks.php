<?php
/**
 * Plugin Name: VIMA Members Weeks
 * Description: Exposes secure REST endpoints for Laravel Members Area to create, update, and fetch rental-submissions posts in WordPress using a shared secret.
 * Version: 1.0.0
 * Author: Oscar García
 * Author URI: og.lopar711@gmail.com
 */

if (!defined('ABSPATH')) exit;

define('MEMBERS_WEEKS_PATH', plugin_dir_path(__FILE__));
define('MEMBERS_WEEKS_URL', plugin_dir_url(__FILE__));

require_once MEMBERS_WEEKS_PATH . 'includes/class-members-weeks-auth.php';
require_once MEMBERS_WEEKS_PATH . 'includes/class-members-weeks-media.php';
require_once MEMBERS_WEEKS_PATH . 'includes/class-members-weeks-api.php';

add_action('rest_api_init', function () {
    $api = new Members_Weeks_API();
    $api->register_routes();
});

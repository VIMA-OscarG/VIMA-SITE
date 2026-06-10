<?php
/**
 * Frontend Reset Password - File Loader
 * 
 * @version	1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load dependency files (functions etc)
require_once( SOMFRP_PATH . 'includes/somfrp-functions.php' );

require_once( SOMFRP_PATH . 'includes/settings/organization/wp-enhanced/class-common-settings.php' );
require_once( SOMFRP_PATH . 'includes/settings/specific/settings.php' );

//$pro_loader = SOMFRP_PATH . 'pro/somfrp-pro-loader.php';
//if ( file_exists( $pro_loader ) ) require_once( $pro_loader );

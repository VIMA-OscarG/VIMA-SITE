<?php
define( 'WP_CACHE', false ); // By Speed Optimizer by SiteGround

// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG_LOG', true );
// define( 'WP_DEBUG_DISPLAY', false );
 // Added by WP Rocket
 // Added by WP Rocket
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dbahahtxatcdjl' );
/** MySQL database username */
define( 'DB_USER', 'uzek0s9quw6uh' );
/** MySQL database password */
define( 'DB_PASSWORD', '4yo7ptfenzf1' );
/** MySQL hostname */
define( 'DB_HOST', '127.0.0.1' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

///PARA EL PLUGIN QUE CONECTA CON LA MEMBER AREA DE LARAVEL

define('VIMA_SSO_SECRET', 'esteelelsecretoaleatoriodeminimo64caracteresparamanejarelssod711');
define('MEMBERS_WEEKS_SHARED_SECRET', 'esteelelsecretoaleatoriodeminimo64caracteresparamanejarelssod711');


/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '41$+mPx= rMpYmM_4b_N!$|2,z:~rcbA7w8+{|[8RS8^LPxRq0A8Evb?XKiz@U (' );
define( 'SECURE_AUTH_KEY',   ']Ht&.qy3:N^pY,0|$&l2HFr3b}M7RsK8L:}Ws&D&<9oMG8DDaU/o8%NFwI8.-)]:' );
define( 'LOGGED_IN_KEY',     '/aeuXWz9Ewak2 gxqs9!11j!9D69mL!xmpC9WK#gRT(pAQEv];jo;!>IrNmF`Rk.' );
define( 'NONCE_KEY',         '|UhqvD.Cxi8RRKYaP&s$8+j:;^h<OQqSK{y]|;%m|1[PBN&Wp>@8_-as2z3,h(g$' );
define( 'AUTH_SALT',         '~_Wg0lpK<0[hkMB[._Sj:8L<JjN0-5c:{A F50Y[m<-%oys!2}dolbl6J^b:DsO|' );
define( 'SECURE_AUTH_SALT',  'M~viRL%ss?#&5pE_u$j0Ds?F]jF7|(uwp.KAX{P;PeCZtLX !B)`kr,y6!.5FmK3' );
define( 'LOGGED_IN_SALT',    '+M3I!9q>3cYQ6B;l)HDc%S<15hX)@k?88XdWp1;Jc$nxYWNb*kSLsk^JnWZEbmM2' );
define( 'NONCE_SALT',        '<NLZ1!25#@)5sg*|$rJtqxtkK4+=bv(d+wEi$.]:W3A8z-VG2)0ggOcodsEO*}he' );
define( 'WP_CACHE_KEY_SALT', '/A>]33bAO$a(#Amv6^V8[|}q_9eMU#`hlV+V9&aQd->O_gOimh_qE=A?&.g{R:LA' );
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define( 'WP_MEMORY_LIMIT', '2048M' );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}
/** Sets up WordPress vars and included files. */
@include_once('/var/lib/sec/wp-settings-pre.php'); // Added by SiteGround WordPress management system
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system

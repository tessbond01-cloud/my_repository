<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_actionscheduler_actions' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'U3I3k2$#K)v&,N n9)N/{eHYMKf}EuF{fjARe(TFrA</$k#V;/)<n%h0AT[0Rm0{' );
define( 'SECURE_AUTH_KEY',  '^`/eXA~4a}Bm%s?9aMN>EkBzV^rTOYrS|f:T#]4|0@/!R_I-bre6FC[~v;~zx*f#' );
define( 'LOGGED_IN_KEY',    'KRoHqTLcNv|(u#,raEWLgLF(/2v*;CU-]VN,1 TVsj)`GV(q6X1yT0B*cs31v(+-' );
define( 'NONCE_KEY',        'XLa!;)WRto7^i*}0Dm?8}hNhmk;YYaW#3g@{G  [>v*o:-o*h4a8d6b@7CWy|S%&' );
define( 'AUTH_SALT',        '6)V8fvhxoU4G3E`0aTs?{n^R7z06LUo2)Xd(spCwlj;_9Yw(1cD;Io?qbs(dS%p5' );
define( 'SECURE_AUTH_SALT', 'Keb;W*E[lX#1T $N9YnaE;_nwy2z:R`eYKV#fh3:a+S+$:v|yR+ E`~ DK%5W;`5' );
define( 'LOGGED_IN_SALT',   't>q/@XL)k.?Z050~*ty=~5W5MK:dT(9$fj;?#fSu1jHUok@/Ks6RjBqP DCw]$D:' );
define( 'NONCE_SALT',       'p/Q2nS$Yd<[|a2{W/Ra4A$<%Lk@II<7;Yzo7Gj=`kXMCZx91Col)}(g4^hq~eo*A' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_liquidation');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


define('FS_METHOD', 'direct');
define( 'WP_MEMORY_LIMIT', '64M' );
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '6ZBQ4YV17JlJnL>;S<z|9#PLZa2q!T5I)x=[7w=>+ 7l0+ECO~ay_#de/k* q8U+');
define('SECURE_AUTH_KEY',  'C=VA>wka>,LB9~{X%>-2*mOS~PzxxV64<o^7is?-> UFV.V$B33E$XdS#o;Kd~fs');
define('LOGGED_IN_KEY',    'X:(nRMDOD 8kj22=+`ZKSG% $+>@o-#K3Kr@aSMZ29MpoH-&ksJ>c8 nWK.51Cbo');
define('NONCE_KEY',        'h|-ef=|Gtv3!|1bV3@G+lS1R(hu4a;IvtzJT(OY5DEEMd$M1GS|Ah+Xa_teVp6RE');
define('AUTH_SALT',        'WcMb~D?XFOS|J4/8Vu:tpug{y #zW@%-iD9G|&+i?(@iCc37^GF;mCS}#*P~>NQ;');
define('SECURE_AUTH_SALT', '26;7+Q3d*NF?Z+EKAmy%{4TT<QttC+)pC=^=3&fV:>oVHLX4.[VM>cv2HjIh~4q,');
define('LOGGED_IN_SALT',   'F9i]5YxVN[.|M!MFl*FtEMa[9gL`5o$DpCin/-q,6dNUU RaIh8?1ik{AjIul%N)');
define('NONCE_SALT',       'ym|4(>3lEl&xC8:v#i4q-gLeOMO2t0TmER+aw|I5}MXYBuW;/E1ja!IF!&Vdc)Ma');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

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
define('DB_NAME', 'puredata_gmbl');

/** MySQL database username */
define('DB_USER', 'puredata_gmbl');

/** MySQL database password */
define('DB_PASSWORD', 'gidal01$^');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '?~>*[h(Qv|bN<`7`@*s^l_!9]yc77*9u&Jg/r3N7~Q.b.H{e*|+z]rQKv^i%P~?b');
define('SECURE_AUTH_KEY',  '9Xb*_+m(R[ng3=+utm_Axm7MD)5m-(}nQ4[!b.YQ f9sN-Ug1nzNJ/&,-Y*OO<8W');
define('LOGGED_IN_KEY',    ':^F@xPaD^7NH6. XMr,q>?(hQ8ic} y2lGa_u?v<IfG?(CqA$4y|/N:w{k<J-_7D');
define('NONCE_KEY',        ':4`d[VBTWiT#oZZrShW;-GJjQp0+|^c7BmQev!7tiu/MdG12N9f#j*c1!TpW)X;>');
define('AUTH_SALT',        '-I{ddWx_=bhN:Q{i1C$}g@ZxBRoBvh:+Pn)*v{ 691#2zU8,yzb ryKS.i;o?tZM');
define('SECURE_AUTH_SALT', 'Ff.WxpgCKc$+)n|i|!]vT/9*]6DDOZe,kpO1-q]j``s[tTPTXpJ>ggwDsbFSlv?)');
define('LOGGED_IN_SALT',   'sDz #98tw_@MC+a_`?5:Sw/<r+7>?4w3/655=n_1m#7N~!c?-K[<O^k<8F(|Am?h');
define('NONCE_SALT',       'Dejihy#Ir[%jUsI_Ww5pg7PXND-)(`zKO7RtLH`VQlqS~nIfu]jx}2^s6$0?$6t,');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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

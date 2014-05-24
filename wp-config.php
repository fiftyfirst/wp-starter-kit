<?php

/**
 * Define the APPLICATION_ENV environment variable. This is set
 * in php-fpm.conf. Defaults to 'production'.
 */
define(
    'APPLICATION_ENV',
    getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'
);

if (APPLICATION_ENV == 'development') {

    define('WP_DEBUG', true);
    define('WP_SITEURL', 'http://');
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASSWORD', '');

} elseif (APPLICATION_ENV == 'staging') {

    define('WP_DEBUG', false);
    define('WP_SITEURL', 'http://');
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASSWORD', '');

} else {

    define('WP_DEBUG', false);
    define('WP_SITEURL', 'http://');
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASSWORD', '');

}

/**
 * Blog address
 */
define('WP_HOME', WP_SITEURL);

/**
 * MySQL hostname
 */
define('DB_HOST', 'localhost');

/**
 * Database charset to use in creating database tables.
 */
define('DB_CHARSET', 'utf8');

/**
 * The Database Collate type. Don't change this if in doubt.
 */
define('DB_COLLATE', '');

/**
 * WordPress Database Table prefix.
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
 * Authentication Unique Keys and Salts.
 *
 * To invalidate all existing cookies and force all users to log in again, you
 * can reset the secrets by deleting the file wp-secret-keys.php
 */
if (!file_exists(dirname(__DIR__) . '/wp-secret-keys.php')) {

    $secretKeys = '<?php' . PHP_EOL;
    $secretKeys .= file_get_contents(
        'https://api.wordpress.org/secret-key/1.1/salt/'
    );

    file_put_contents(dirname(__DIR__) . '/wp-secret-keys.php', $secretKeys);

}

require_once(dirname(__DIR__) . '/wp-secret-keys.php');

/**
 * Absolute path to the WordPress directory.
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/**
 * Sets up WordPress vars and included files.
 */
require_once(ABSPATH . 'wp-settings.php');

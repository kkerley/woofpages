<?php
/*
  Plugin Name: Toolset CRED
  Plugin URI: https://wp-types.com/home/toolset-components/#cred
  Description: Create Edit Delete Wordpress content (ie. posts, pages, custom posts) from the front end using fully customizable forms
  Version: 1.8.6
  Author: OnTheGoSystems
  Author URI: http://www.onthegosystems.com/
  License: GPLv2
 */



// Abort if called directly.
if (!function_exists('add_action')) {
    die('CRED is a WordPress plugin and can not be called directly.');
}


// Abort if the plugin is already loaded.
if (defined('CRED_FE_VERSION')) {
    return;
}


/*
 * Define plugin constants - version, paths, etc.
 */

// Current plugin version
define( 'CRED_FE_VERSION', '1.8.6' );


if (!defined('CRED_ABSPATH')) {

    /**
     * Absolute path to the new plugin root.
     *
     * Everything else is legacy.
     * No other path definitions should be necessary.
     *
     * @since 1.8.6
     */
    define('CRED_ABSPATH', dirname(__FILE__));
}

if (!defined('CRED_ABSURL')) {
    define('CRED_ABSURL', plugins_url() . '/' . basename(CRED_ABSPATH));
}



/*
 * Bootstrap CRED
 */
require_once CRED_ABSPATH . '/application/bootstrap.php';
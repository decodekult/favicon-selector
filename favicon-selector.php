<?php
/*
Plugin Name: Favicon selector
Plugin URI: 
Description: Lets you select a favicon and have it applied to your site
Version: 1.0.0
Author: Juan de Paco
Author URI: http://decodekult.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: favicon-selector
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define the plugin path constant
define( 'FAVICON_SELECTOR_PATH', dirname( __FILE__ ) );

// Define the plugin URL constant based o the SSL settings
if ( is_ssl() ) {
	define('FAVICON_SELECTOR_FRONTEND_URL', rtrim( str_replace( 'http://', 'https://', plugins_url() ), '/' ) . '/' . basename( FAVICON_SELECTOR_PATH ) );
	define('FAVICON_SELECTOR_BACKEND_URL', rtrim( str_replace( 'http://', 'https://', plugins_url() ), '/' ) . '/' . basename( FAVICON_SELECTOR_PATH ) );
} else {
	define('FAVICON_SELECTOR_FRONTEND_URL', plugins_url() . '/' . basename( FAVICON_SELECTOR_PATH ) );
	define('FAVICON_SELECTOR_BACKEND_URL', plugins_url() . '/' . basename( FAVICON_SELECTOR_PATH ) );
}
if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
	define('FAVICON_SELECTOR_BACKEND_URL', rtrim( str_replace( 'http://', 'https://', plugins_url() ), '/' ) . '/' . basename( FAVICON_SELECTOR_PATH ) );
}

// Require the main class
require_once( plugin_dir_path( __FILE__ ) . 'classes/favicon-selector.class.php' );

// Plugin natural hooks
register_activation_hook( __FILE__, array( 'Favicon_Selector', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Favicon_Selector', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Favicon_Selector', 'get_instance' ) );

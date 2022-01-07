<?php
/*
	Plugin Name: Printy6 - Print on demand
	Plugin URI: https://www.printy6.com
	Description: Create products with your design for yourself or sell everywhere, we’ll fulfill your orders for you
	Version: 1.0.0
	Author: Printy6
	License: GPL2 http://www.gnu.org/licenses/gpl-2.0.html
	Text Domain: printy6
	WC requires at least: 3.0.0
	WC tested up to: 6.0
	*/


if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'PT6WC_NAME',                 		'Printy6 integration for woocommerce' );
define( 'PT6WC_TEST',                 		false );
define( 'PT6WC_VERSION',              		'1.0.0' );
define( 'PT6WC_API_DOMAIN',           		'https://www.printy6.com/api/open/v1' );
define( 'PT6WC_BRAND_NAME',           		'Printy6' );
define( 'PT6WC_REQUIRED_PHP_VERSION', 		'5.3' );                          									// because of get_called_class()
define( 'PT6WC_REQUIRED_WP_VERSION',  		'4.4' );                          									// because of esc_textarea()
define( 'PT6WC_PLUGIN_ABSOLUTE', 					dirname( plugin_basename( __FILE__ ) ) );						// printy6 for woocommerce plugin dirname

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function pt6wc_requirements_met() {
	global $wp_version;
	//require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		// to get is_plugin_active() early

	if ( version_compare( PHP_VERSION, PT6WC_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, PT6WC_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	/*
	if ( ! is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
		return false;
	}
	*/

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function pt6wc_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( pt6wc_requirements_met() ) {
	require_once( __DIR__ . '/classes/pt6wc-module.php' );
	require_once( __DIR__ . '/classes/pt6wc-base.php' );
	require_once( __DIR__ . '/classes/pt6wc-custom-post-type.php' );
	require_once( __DIR__ . '/classes/pt6wc-instance-class.php' );

	if ( class_exists( 'PT6_Base' ) ) {
		$GLOBALS['pt6wc'] = PT6_Base::get_instance();
		register_activation_hook(   __FILE__, array( $GLOBALS['pt6wc'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['pt6wc'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'pt6wc_requirements_error' );
}

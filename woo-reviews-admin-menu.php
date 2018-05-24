<?php
/**
 * Plugin Name: WooCommerce Reviews Admin Sidebar
 * Plugin URI:  https://github.com/liquidweb/woo-reviews-admin-menu
 * Description: Adds a dedicated menu item to look at reviews.
 * Version:     0.0.1
 * Author:      Andrew Norcross
 * Author URI:  http://andrewnorcross.com
 * Text Domain: woo-reviews-admin-menu
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package WooReviewsSidebarAdmin
 */

namespace WooReviewsSidebarAdmin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our version.
define( 'WOO_REVIEWS_SIDEADMIN_VERS', '0.0.1' );

// And load it up.
load();

// woocommerce_enable_reviews

/**
 * Run our initial loading check.
 */
function load() {

	// Set my plugin constants.
	set_constants();

	// Check for enabled reviews.
	$enable = get_option( 'woocommerce_enable_reviews', 'no' );

	// Make sure the setting is set to yes.
	if ( 'yes' !== $enable ) {

		// Deactivate the plugin.
		deactivate_plugins( WOO_REVIEWS_SIDEADMIN_BASE );

		// And display the notice.
		wp_die( sprintf( __( 'This plugin requires WooCommerce product reviews to be enabled. <a href="%s">Click here</a> to return to the plugins page.', 'woo-reviews-admin-menu' ), admin_url( '/plugins.php' ) ) );
	}

	// And include my files.
	require_once __DIR__ . '/includes/admin.php';
	require_once __DIR__ . '/includes/table.php';
}

/**
 * Define all the constants used in the plugin.
 */
function set_constants() {

	// Define our file base.
	if ( ! defined( 'WOO_REVIEWS_SIDEADMIN_BASE' ) ) {
		define( 'WOO_REVIEWS_SIDEADMIN_BASE', plugin_basename( __FILE__ ) );
	}

	// Plugin Folder URL.
	if ( ! defined( 'WOO_REVIEWS_SIDEADMIN_URL' ) ) {
		define( 'WOO_REVIEWS_SIDEADMIN_URL', plugin_dir_url( __FILE__ ) );
	}

	// Plugin root file.
	if ( ! defined( 'WOO_REVIEWS_SIDEADMIN_FILE' ) ) {
		define( 'WOO_REVIEWS_SIDEADMIN_FILE', __FILE__ );
	}

	// Set our menu base slug constant.
	if ( ! defined( 'WOO_REVIEWS_SIDEADMIN_MENU_BASE' ) ) {
		define( 'WOO_REVIEWS_SIDEADMIN_MENU_BASE', 'product-reviews' );
	}
}

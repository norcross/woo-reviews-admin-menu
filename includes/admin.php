<?php
/**
 * General admin functionality.
 *
 * @package WooReviewsSidebarAdmin
 */

namespace WooReviewsSidebarAdmin\Admin;

use WooReviewsSidebarAdmin\Table as Table;

/**
 * Start our engines.
 */
add_action( 'admin_head', __NAMESPACE__ . '\load_admin_table_css' );
add_action( 'admin_menu', __NAMESPACE__ . '\load_admin_menu', 99 );

/**
 * Load the small bit of CSS for the admin alert sidebar icon.
 *
 * @return void
 */
function load_admin_table_css() {

	// Open the style tag.
	echo '<style>';

		// Set the column width.
		echo 'table.productreviews .column-review_date {';
			echo 'width: 16%;';
		echo '}';

		echo 'table.productreviews .column-customer_name {';
			echo 'width: 20%;';
		echo '}';

		echo 'table.productreviews .column-product_item {';
			echo 'width: 20%;';
		echo '}';

		// Float the avatar.
		echo 'table.productreviews .column-customer_name img {';
			echo 'float: left;';
			echo 'margin-right: 10px;';
			echo 'margin-top: 1px;';
		echo '}';

	// Close the style tag.
	echo '</style>';
}

/**
 * Load our menu item.
 *
 * @return void
 */
function load_admin_menu() {

	// Add our submenu page.
	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Product Reviews', 'woo-reviews-admin-menu' ),
		__( 'Reviews', 'woo-reviews-admin-menu' ),
		'manage_options',
		WOO_REVIEWS_SIDEADMIN_MENU_BASE,
		__NAMESPACE__ . '\list_product_reviews'
	);
}

/**
 * Our actual settings page for things.
 *
 * @return mixed
 */
function list_product_reviews() {

	// Wrap the entire thing.
	echo '<div class="wrap woo-reviews-admin-wrap">';

		// Handle the title.
		echo '<h1 class="woo-reviews-admin-title">' . get_admin_page_title() . '</h1>';

		// Handle our table, but only if we have some.
		echo product_reviews_table();

	// Close the entire thing.
	echo '</div>';
}

/**
 * Create and return the table of reviews.
 *
 * @return HTML
 */
function product_reviews_table() {

	// Fetch the action link.
	$action = '';

	// Call our table class.
	$table  = new Table\WooProductReviews_Table();

	// And output the table.
	$table->prepare_items();

	// And handle the display
	echo '<form class="lw-woo-gdpr-admin-form" id="lw-woo-gdpr-requests-admin-form" action="' . esc_url( $action ) . '" method="post">';

	// The actual table itself.
	$table->display();

	// And close it up.
	echo '</form>';
}

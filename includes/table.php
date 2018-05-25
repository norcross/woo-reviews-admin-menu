<?php
/**
 * Our table setup for the product reviews.
 *
 * @package WooReviewsSidebarAdmin
 */

namespace WooReviewsSidebarAdmin\Table;

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WP_List_Table;

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class WooProductReviews_Table extends WP_List_Table {

	/**
	 * WooProductReviews_Table constructor.
	 *
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct( array(
			'singular' => __( 'Product Review', 'woo-reviews-admin-menu' ),
			'plural'   => __( 'Product Reviews', 'woo-reviews-admin-menu' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {

		// Roll out each part.
		$columns    = $this->get_columns();
		$hidden     = array();
		$sortable   = $this->get_sortable_columns();
		$dataset    = $this->table_data();

		// Handle our sorting.
		usort( $dataset, array( $this, 'sort_data' ) );

		$paginate   = 10;
		$current    = $this->get_pagenum();

		// Set my pagination args.
		$this->set_pagination_args( array(
			'total_items' => count( $dataset ),
			'per_page'    => $paginate
		));

		// Slice up our dataset.
		$dataset    = array_slice( $dataset, ( ( $current - 1 ) * $paginate ), $paginate );

		// Do the column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Make sure we have the single action running.
		$this->process_single_action();

		// Make sure we have the bulk action running.
		$this->process_bulk_action();

		// And the result.
		$this->items = $dataset;
	}

	/**
	 *
	 * @global string $comment_status
	 */
	public function no_items() {
		_e( 'No product reviews found.' );
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @return Array
	 */
	public function get_columns() {

		// Build our array of column setups.
		return array(
			'cb'            => '<input type="checkbox" />',
			'customer_name' => __( 'Customer Name', 'woo-reviews-admin-menu' ),
			'review_text'   => __( 'Review Content', 'woo-reviews-admin-menu' ),
			'product_item'  => __( 'Product', 'woo-reviews-admin-menu' ),
			'review_date'   => __( 'Review Date', 'woo-reviews-admin-menu' ),
		);
	}

	/**
	 * Display all the things.
	 *
	 * @return HTML
	 */
	public function display() {

		// Add a nonce for the bulk action.
		wp_nonce_field( 'woo_reviews_table_bulk_delete_action', 'woo_reviews_table_bulk_delete_nonce' );

		// And the parent display (which is most of it).
		parent::display();
	}

	/**
	 * Return null for our table, since no row actions exist.
	 *
	 * @param  object $item         The item being acted upon.
	 * @param  string $column_name  Current column name.
	 * @param  string $primary      Primary column name.
	 *
	 * @return null
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		return apply_filters( 'lw_woo_gdpr_table_row_actions', '', $item, $column_name, $primary );
 	}

	/**
	 * Define the sortable columns.
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {

		// Build our array of sortable columns.
		return array(
			'customer_name' => array( 'customer_name', false ),
			'product_item'  => array( 'product_item', true ),
			'review_date'   => array( 'review_date', true ),
		);
	}

	/**
	 * Return available bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {

		// Make a basic array of the actions we wanna include.
		return array(); // array( 'woo_reviews_table_delete' => __( 'Delete Reviews', 'woo-reviews-admin-menu' ) );
	}

	/**
	 * Checkbox column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {

		// Set my ID.
		$id = absint( $item['id'] );

		// Return my checkbox.
		return '<input type="checkbox" name="woo_reviews_admin[]" class="checkbox" id="cb-' . $id . '" value="' . $id . '" /><label for="cb-' . $id . '" class="screen-reader-text">' . __( 'Select review', 'woo-reviews-admin-menu' ) . '</label>';
	}

	/**
	 * The visible name column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_customer_name( $item ) {

		// Set an empty.
		$setup  = '';

		// Open with the strong tag and the image.
		$setup .= '<strong>';
		$setup .= get_avatar( $item['customer_email'], 32 );

		// Build the link setup if the user can edit.
		if ( current_user_can( 'edit_users' ) ) {

			// Fetch my edit link.
			$edit   = get_edit_user_link( $item['customer_id'] );

			// And handle the markup.
			$setup .= '<a title="' . __( 'View profile', 'woo-reviews-admin-menu' ) . '" href="' . esc_url( $edit ) . '">';
			$setup .= esc_html( $item['customer_name'] );
			$setup .= '</a>';

		} else {

			// Just show the name if the user can't edit others.
			$setup .= esc_html( $item['customer_name'] );
		}

		// Close my strong tag for the name.
		$setup .= '</strong>';

		// Now display the email.
		$setup .= sprintf( '<br><a href="%1$s">%2$s</a><br>', esc_url( 'mailto:' . $item['customer_email'] ), esc_html( $item['customer_email'] ) );

		// Run our formatted name through the filter.
		$build  = apply_filters( 'woo_reviews_admin_sidebar_column_customer_name', $setup, $item );

		// Return, along with our row actions.
		return $build . $this->row_actions( $this->setup_row_action_items( $item ) );
	}

	/**
	 * Return the content of the review.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return HTML
	 */
	protected function column_review_text( $item ) {

		// Get the review text.
		$review = get_comment_text( $item['id'] );

		// Set up the content.
		$setup  = wpautop( $review );

		// Filter and return it.
		return apply_filters( 'woo_reviews_admin_sidebar_column_review_text', $setup, $item );
	}

	/**
	 * The product column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_product_item( $item ) {
		//preprint( $item, true );

		if ( empty( $item['product_id'] ) ) {
			return '<em>' . esc_html__( 'No product was found', 'woo-reviews-admin-menu' ) . '</em>';
		}

		// Set my product ID.
		$product_id = absint( $item['product_id'] );

		// Get my items related to the product.
		$prod_edit  = get_edit_post_link( $product_id );
		$prod_link  = get_permalink( $product_id );
		$prod_name  = get_the_title( $product_id );

		// Set my empty.
		$setup  = '';

		// Set the initial div.
		$setup .= '<div class="response-links">';

			// Check the user attribute.
			$setup .= current_user_can( 'edit_post', $product_id ) ? '<a href="' . esc_url( $prod_edit ) . '" class="comments-edit-item-link">' . esc_html( $prod_name ) . '</a>' : esc_html( $prod_name );

			// Add the view item.
			$setup .= '<a class="comments-view-item-link" href="' . esc_url( $prod_link ) . '">' . esc_html__( 'View product', 'woo-reviews-admin-menu' ) . '</a>';

		// Close my div.
		$setup .= '</div>';

		// Filter and return it.
		return apply_filters( 'woo_reviews_admin_sidebar_column_product_item', $setup, $item );
	}

	/**
	 * The request date column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_review_date( $item ) {

		/* translators: 1: comment date, 2: comment time */
		$submitted = sprintf( __( '%1$s at %2$s' ),
			/* translators: comment date format. See https://secure.php.net/date */
			get_comment_date( __( 'Y/m/d' ), $item['id'] ),
			get_comment_date( __( 'g:i a' ), $item['id'] )
		);

		// Set my empty.
		$setup  = '';

		// Wrap the dates.
		$setup .= '<div class="submitted-on">';

		// Check the status.
		if ( 'approved' === esc_attr( $item['review_status'] ) && ! empty ( $item['product_id'] ) ) {
			$setup .= sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_comment_link( $item['id'] ) ),
				$submitted
			);
		} else {
			$setup .= $submitted;
		}

		// And close it.
		$setup .= '</div>';

		// Filter and return it.
		return apply_filters( 'woo_reviews_admin_sidebar_column_date', $setup, $item );
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {

		// Get the reviews.
		$reviews = get_comments( array( 'post_type' => 'product' ) );

		// Return an empty array if we don't have any reviews.
		if ( empty( $reviews ) ) {
			return array();
		}

		// Set my empty.
		$data   = array();

		// Loop my userdata.
		foreach ( $reviews as $review ) {

			// Fetch our userdata.
			$userdata   = get_user_by( 'email', $review->comment_author_email );

			// Now the data based on the user existing.
			$user_id    = ! empty( $userdata->ID ) ? $userdata->ID : 0;
			$show_name  = ! empty( $userdata->display_name ) ? $userdata->display_name : $review->comment_author;
			$show_email = ! empty( $userdata->user_email ) ? $userdata->user_email : $review->comment_author_email;

			// Set the array of the data we want.
			$setup  = array(
				'id'             => $review->comment_ID,
				'customer_id'    => $user_id,
				'customer_name'  => $show_name,
				'customer_email' => $show_email,
				'product_id'     => $review->comment_post_ID,
				'review_date'    => $review->comment_date,
				'review_status'  => wp_get_comment_status( $review ),
			);

			// Run it through a filter.
			$data[] = apply_filters( 'woo_reviews_admin_sidebar_table_data_item', $setup, $review, $userdata );
		}

		// Return our data.
		return apply_filters( 'woo_reviews_admin_sidebar_table_data_array', $data, $reviews );
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array  $dataset      Our entire dataset.
	 * @param  string $column_name  Current column name
	 *
	 * @return mixed
	 */
	public function column_default( $dataset, $column_name ) {

		// Run our column switch.
		switch ( $column_name ) {

			case 'customer_name' :
			case 'review_text' :
			case 'product_item' :
			case 'review_date' :
				return ! empty( $dataset[ $column_name ] ) ? $dataset[ $column_name ] : '';

			default :
				return apply_filters( 'woo_reviews_admin_sidebar_table_column_default', '', $dataset, $column_name );
		}
	}

	/**
	 * Handle bulk actions.
	 *
	 * @see $this->prepare_items()
	 */
	protected function process_bulk_action() {
	}

	/**
	 * Handle the single row action.
	 *
	 * @return void
	 */
	protected function process_single_action() {
	}

	/**
	 * Create the row actions we want.
	 *
	 * @param  array $item  The item from the dataset.
	 *
	 * @return array
	 */
	private function setup_row_action_items( $item ) {
		return array();
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {

		// Set defaults and check for query strings.
		$ordby  = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'review_date';
		$order  = ! empty( $_GET['order'] ) ? $_GET['order'] : 'desc';

		// Set my result up.
		$result = strcmp( $a[ $ordby ], $b[ $ordby ] );

		// Return it one way or the other.
		return 'asc' === $order ? $result : -$result;
	}
}

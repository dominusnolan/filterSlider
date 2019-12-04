<?php


namespace Financer\FilterSlider;

include_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Class AffiliateLogTable
 * @package Financer\FilterSlider
 */
class AffiliateLogTable extends \WP_List_Table {

	/**
	 * @inheritdoc
	 */
	public function __construct( $args ) {
		parent::__construct( [
			'singular' => 'log',
			'plural'   => 'Logs'
		] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_columns() {
		return [
			'company'    => 'Company',
			'old_url'    => 'Old URL',
			'new_url'    => 'New URL',
			'blog_title' => 'Site',
			'date'       => 'Date',
			'user'       => 'User',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function single_row( $item ) {
		switch_to_blog( $item['blog_id'] );
		parent::single_row( $item );
		restore_current_blog();
	}

	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = $this->get_column_info();
		$current_page          = $this->get_pagenum();

		$per_page = $this->get_items_per_page( str_replace( '-', '_', $this->screen->id . '_per_page' ) );

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}company_affiliate_log" );


		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page
		] );

		$sql = "SELECT * FROM {$wpdb->base_prefix}company_affiliate_log";

		$orderby = 'date';
		$order   = 'DESC';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$orderby = $_REQUEST['orderby'];
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = $_REQUEST['order'];
			if ( ! in_array( strtoupper( $order ), [ 'ASC', 'DESC' ] ) ) {
				$order = 'DESC';
			}
		}
		$sql .= ' ORDER BY ' . esc_sql( $orderby );
		$sql .= ' ' . esc_sql( $order );

		$sql .= " LIMIT $per_page";

		$sql .= ' OFFSET ' . ( $current_page - 1 ) * $per_page;


		$this->items = $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	/**
	 * @inheritDoc
	 */
	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_sortable_columns() {
		return [ 'blog_title' => [ 'blog_title', false ] ];
	}

	/**
	 * @param $data
	 *
	 * @return bool|int|string
	 */
	protected function column_date( $data ) {
		$date = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $data['date'] );

		return $date;
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	protected function column_user( $data ) {
		return get_user_by( 'id', get_post( $data['post_id'] )->post_author )->display_name;
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	protected function column_company( $data ) {
		$title = get_the_title( wp_get_post_parent_id( $data['post_id'] ) );

		return $title;
	}
}
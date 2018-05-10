<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if ( !class_exists( 'WP_List_Table' ) ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */


class MJUpdateLogTable extends WP_List_Table {

	/**
	 * 絞込後のリスト全件データ
	 *
	 * @var array
	 */
	public $data;

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'log',   //singular name of the listed records
			'plural'    => 'logs',  //plural name of the listed records
			'ajax'      => false    //does this table support ajax?
		) );

	}


	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default( $item, $column_name ){
		switch( $column_name ){
			case 'date':
			case 'name':
			// case 'type':function column_typeで処理(num to name)
			case 'state':
			case 'old_version':
			case 'new_version':
				return $item[ $column_name ];
			default:
				return $item[ $column_name ];
				// return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}


	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_type( $item ){
		if ( $item['type'] === '0' ) {
			return 'WordPress';
		} elseif ( $item['type'] === '1' ) {
			return 'Theme';
		} elseif ( $item['type'] === '2' ) {
			return 'Plugin';
		} else {
			return 'none';
		}
	}

	function column_user_id( $item ){
		$user = get_userdata( $item['user_id'] );
		if ( $user ) {
			return $user->user_login;
		} else {
			return null;
		}
	}


	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_columns(){
		$columns = array(
			'date'        => __( 'Date', 'mj-update-history' ),
			'name'        => __( 'Name', 'mj-update-history' ),
			'type'        => __( 'Type', 'mj-update-history' ),
			'state'       => __( 'State', 'mj-update-history' ),
			'old_version' => __( 'Old Version', 'mj-update-history' ),
			'new_version' => __( 'New Version', 'mj-update-history' ),
			'user_id'     => __( 'User Name', 'mj-update-history' )
		);
		return $columns;
	}


	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	function get_sortable_columns() {
		$sortable_columns = array(
			'date'        => array( 'date', true ), //true means it's already sorted
			'name'        => array( 'name', false ),
			'type'        => array( 'type', false ),
			'state'       => array( 'state', false ),
			'old_version' => array( 'old_version', false ),
			'new_version' => array( 'new_version', false ),
			'user_id'     => array( 'user_id', false )
		);
		return $sortable_columns;
	}


//	function display_tablenav( $which ) {
//		if ($which == "top") {
//
//			echo '<form method="get">';
//
//			// ダウンロード
//			echo '<div class="tablenav ' . esc_attr($which) . '">';
//			echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '">';
//			$this->bulk_actions();
//			echo '</div>';
//
//			// 絞り込み検索
//			echo '<div class="tablenav ' . esc_attr($which) . '">';
//			echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '">';
//			$this->extra_tablenav($which);
//			echo '</div>';
//
//			echo '</form>';
//
//		}
//
//	}


	function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			global $wpdb;
			$logs_table_name = $wpdb->prefix . 'mjuh_logs';

			if ( !isset( $_REQUEST['type'] ) ) {
				$_REQUEST['type'] = '';
			}
			if ( !isset( $_REQUEST['state'] ) ) {
				$_REQUEST['state'] = '';
			}
			if ( !isset( $_REQUEST['user_id'] ) ) {
				$_REQUEST['user_id'] = '';
			}


			echo '<div class="alignleft actions bulkactions">';

			$query = $wpdb->get_results( 'select * from ' . $logs_table_name . ' order by name asc', ARRAY_A );

			$this->select_months();

			// set type_array for select

			if ( !function_exists( 'array_column' ) ) {
				function array_column( $list, $key ) {
					$return_list = array();
					foreach ( $list as $data ) {
						$return_list[] = $data[ $key ];
					}
					return $return_list;
				}
			}

			$types_array = array_column( $query, 'type' );
			$types = array_unique( $types_array );
			sort( $types );

			if ( $types ) {
				echo '<select name="type">';
				echo '<option value="">' . esc_html__( 'All Types', 'mj-update-history' ) . '</option>';

				foreach ( $types as $type ) {
					$type_value = '';
					switch ( $type ) {
						case 0:
							$type_value = __( 'WordPress', 'mj-update-history' );
							break;

						case 1:
							$type_value = __( 'Theme', 'mj-update-history' );
							break;

						case 2:
							$type_value = __( 'Plugin', 'mj-update-history' );
							break;
					}
					$selected = '';
					if ( isset( $_GET['type'] ) && $_GET['type'] == $type ) {
						$selected = ' selected = "selected"';
					}
					echo '<option value="' . $type . '"' . $selected . '>' . $type_value . '</option>';
				}
				echo '</select>';
			}

			// set state_array for select
			$state_array = array_column( $query, 'state' );
			$states = array_unique( $state_array );
			sort( $states );

			if ( $states ) {
				echo '<select name="state">';
				echo '<option value="">' . esc_html__( 'All States', 'mj-update-history' ) . '</option>';

				foreach ($states as $state) {
					$selected = '';
					if ( isset( $_GET['state'] ) && $_GET['state'] == $state) {
						$selected = ' selected = "selected"';
					}
					echo '<option value="' . $state . '"' . $selected . '>' . $state . '</option>';
				}
				echo '</select>';
			}

			// set user_array for select
			$user_array = array_column( $query, 'user_id' );
			$users = array_unique( $user_array );
			sort( $users );

			if ( $users ) {
				echo '<select name="user">';
				echo '<option value="">' . esc_html__( 'All users', 'mj-update-history' ) . '</option>';

				foreach ( $users as $user_id ) {
					$selected = '';
					if ( isset( $_GET['user'] ) && $_GET['user'] == $user_id) {
						$selected = ' selected = "selected"';
					}
					$user_info = get_userdata( $user_id );
					if ( empty( $user_info ) ) {
						$user_name = 'none';
					} else {
						$user_name = $user_info->data->user_login;
					}
					echo '<option value="' . $user_id . '"' . $selected . '>' . $user_name . '</option>';
				}
				echo '</select>';
			}

			submit_button( esc_html__( 'Filter', 'mj-update-history' ), 'button', null, false );
			echo '</div>';
		}
		if ( $which == "bottom" ) {
			echo '<button type="submit" name="download-log" class="button button-primary" value="download-csv">';
			echo esc_html__( 'Download CSV file', 'mj-update-history' );
			echo '</button>';
			// echo '<button type="submit" name="email-log" class="button button-primary" value="email">';
			// echo esc_html__( 'Send E-Mail', 'mj-update-history' );
			// echo '</button>';
			echo '<button type="button" name="output-text" id="mjuh-output-text" class="button button-primary" value="output-text">';
			echo esc_html__( 'Text output for copy', 'mj-update-history' );
			echo '</button>';
			echo '<div id="mjuh-output-area"></div>';
		}

	}


	function filter_table_data( $data, $search_key ) {
		$filtered_table_data = array_values( array_filter( $data, function( $row ) use( $search_key ) {
			foreach( $row as $row_val ) {
				if( stripos( $row_val, $search_key ) !== false ) {
					return true;
				}
			}
		} ) );
		return $filtered_table_data;
	}


	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @access protected
	 */
	function select_months() {
		global $wpdb, $wp_locale;
		$logs_table_name = $wpdb->prefix . 'mjuh_logs';

		$meta_key = 'date';
		$months = $wpdb->get_results('
			SELECT DISTINCT YEAR( date ) AS year, MONTH( date ) AS month
			FROM ' . $logs_table_name . '
			ORDER BY date DESC
		');

		/**
		 * Filters the 'Months' drop-down results.
		 *
		 * @since 3.7.0
		 *
		 * @param object $months    The months drop-down query results.
		 * @param string $post_type The post type.
		 */
		$months = apply_filters( 'months_dropdown_results', $months );

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
?>
		<label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date' ); ?></label>
		<select name="m" id="filter-by-date">
			<option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' ); ?></option>
<?php
		foreach ( $months as $arc_row ) {

			if ( 0 == $arc_row->year )
				continue;

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $m, $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				/* translators: 1: month name, 2: 4-digit year */
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		}
?>
		</select>
<?php
	}


//	function get_bulk_actions() {
//		$actions = array(
//			'download' => 'Download CSV file'
//		);
//		return $actions;
//	}


	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		// resolve issue 'Request-URI Too Long'
		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->get_items_per_page( 'mjlh_logs_per_page', 10 );

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		// $columns = $this->get_columns();
		// $hidden = array();
		// $sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		// $this->_column_headers = array($columns, $hidden, $sortable);
		$this->_column_headers = $this->get_column_info();


		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		global $wpdb;
		$logs_table_name = $wpdb->prefix . 'mjuh_logs';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$where = ' WHERE 1 = 1';
		// if a filter was performed.
		if ( isset( $_REQUEST['type'] ) && $_REQUEST['type'] !== '' ) {
			$where .= $wpdb->prepare( ' AND `type` = %s', strtolower( $_REQUEST['type'] ) );
		}
		if ( isset( $_REQUEST['state'] ) && $_REQUEST['state'] !== '' ) {
			$where .= $wpdb->prepare( ' AND `state` = %s', strtolower( $_REQUEST['state'] ) );
		}
		if ( isset( $_REQUEST['user'] ) && $_REQUEST['user'] !== '' ) {
			$where .= $wpdb->prepare( ' AND `user_id` = %s', strtolower( $_REQUEST['user'] ) );
		}
		if ( isset( $_REQUEST['m'] ) && $_REQUEST['m'] !== '' && $_REQUEST['m'] !== '0' ) {
			require_once( ABSPATH . 'wp-load.php' );
			$where .= $wpdb->prepare( ' AND (DATE_FORMAT(date, %s) = %s)', '%Y%m', $_REQUEST['m'] );
		}

		$query = "SELECT * FROM $logs_table_name $where ORDER BY date DESC;";
		$data = $wpdb->get_results( $query, ARRAY_A );

		/**
		 * if a search was performed.
		 */
		$user_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		if( $user_search_key ) {
			$data = $this->filter_table_data( $data, $user_search_key );
		}


		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */
		if ( !function_exists( 'usort_reorder' ) ) {
			function usort_reorder( $a, $b ) {
				$orderby = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
				$order = ( !empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
				$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
				return ( $order === 'asc' ) ? $result : -$result; //Send final sort direction to usort
			}
			usort( $data, 'usort_reorder' );
		}


		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/

		// for output(csv/trext)
		$this->data = $data;

		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count( $data );


		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data_on_the_first_page = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );



		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data_on_the_first_page;


		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                    //WE have to calculate the total number of items
			'per_page'    => $per_page,                       //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page ) //WE have to calculate the total number of pages
		) );
	}


}

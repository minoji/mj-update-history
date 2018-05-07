<?php

class MJUHAdmin {

	// public static function get_instance() {

	// 	static $instance = null;

	// 	if ( $instance == null ) {
	// 		$instance = new self();
	// 	}

	// 	return $instance;
	// }

	/* =========================================================
		 管理画面関連
		 ========================================================= */

	/**
	 * 基本設定
	 */
	function admin_menu_action() {

		if ( isset( $_REQUEST['download-log'] ) ) {
			// ダウンロードの場合
			// TODO: メソッド化
			require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
			$mj_update_log_table = new MJUpdateLogTable();
			$mj_update_log_table->prepare_items();

			$items   = $mj_update_log_table->data;
			$columns = $mj_update_log_table->get_columns();

			$data = $this->set_data( $items, $columns );

			if ( $_REQUEST['download-log'] === 'download-csv' ) {
				$download_date = date_i18n( 'Y-m-d_H-i-s' );
				$this->download_csv( $data, $columns, $download_date );
			}
		}

		// if ( isset( $_REQUEST['email-log'] ) ) {
		// 	// メールの場合
		// 	// TODO: メソッド化
		// 	require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
		// 	$mj_update_log_table = new MJUpdateLogTable();
		// 	$mj_update_log_table->prepare_items();

		// 	$items   = $mj_update_log_table->data;
		// 	$columns = $mj_update_log_table->get_columns();

		// 	$data_array = $this->set_data( $items, $columns );

		// 	$data = $this->set_body( $data_array );

		// 	if ( $_REQUEST['email-log'] === 'email' ) {
		// 		$chk_sending = $this->send_email( $data );
		// 		if ( $chk_sending ) {
		// 			$message = 'success_message';
		// 		} else {
		// 			$message = 'error_message';
		// 		}
		// 		function success_message() {
		// 			echo '<div class="notice is-dismissible updated"><p>' . esc_html__( 'Message sent successfully', 'mj-update-history' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button></div>';
		// 		}
		// 		function error_message() {
		// 			echo '<div class="notice is-dismissible error"><p>' . esc_html__( 'Messgae couldn’t be sent', 'mj-update-history' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button></div>';
		// 		}
		// 		add_action( 'admin_notices', $message );
		// 	}
		// }

		$hook = add_menu_page(
			__( 'Update History', 'mj-update-history' ),    /* HTMLのページタイトル */
			__( 'Update History', 'mj-update-history' ),    /* 管理画面メニューの表示名 */
			'administrator',  /* この機能を利用できるユーザ */
			'mj_update_history',        /* urlに入る名前 */
			array( $this, 'admin_page' )   /* 機能を提供するメソッド */
		);

		add_action( "load-$hook", array( $this, 'admin_screen_options' ) );
	}


	/**
	 * assets/
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_style(
			'mj-update-history-style',
			plugins_url( '../assets/css/mj-update-history-style.css', __FILE__ ),
			false,
			false,
			'all'
		);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'mj-update-history-script.js',
			plugins_url( '../assets/js/mj-update-history-script.js', __FILE__ ),
			array( 'jquery'),
			'1.0.0',
			true
		);
	}


	/**
	 * Setting area
	 */
	function admin_screen_options() {
		$args = array(
			'label'   => __( 'Logs per page', 'logs' ),
			'default' => 10,
			'option'  => 'mjlh_logs_per_page'
		);
		add_screen_option( 'per_page', $args );

		require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
		$mj_update_log_table = new MJUpdateLogTable();
	}


	/**
	 * VIEW設定
	 */
	function admin_page() {

		//テーブル生成用class読み込み
		require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
		$mj_update_log_table = new MJUpdateLogTable();
		$table_data = $mj_update_log_table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1>';
		echo esc_html__( 'Update History', 'mj-update-history' );
		echo '</h1>';
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '">';
		$mj_update_log_table->search_box( __( 'Search', 'mj-update-history' ), 'search' );
		$mj_update_log_table->display();
		echo '</form>';
		// echo '<form action="#" id="mjuh-output-text" method="POST">';
		// echo '<input type="submit" class="button button-primary" value="' . esc_html__( 'Text output for copy', 'mj-update-history' ) . '">';
		// echo '</form>';
		// echo '<div id="mjuh-output-area"></div>';
		echo '</div>';

	}

	/**
	 * データ格納
	 */
	private function prep_row( $item, $columns ) {
		$row = [];

		foreach ( array_keys( $columns ) as $column ) {
			switch ( $column ) {
				case 'date':
					$row[ $column ] = date_i18n( 'Y/m/d H:i:s', $time_stamp = strtotime( $item['date'] ) );
//					$row[ $column ] = get_date_from_gmt( $date, 'Y/m/d h:i:s A' );
					break;

				case 'name':
					$row[ $column ] = $item['name'];
					break;

				case 'type':
					if ( $item['type'] === '0' ) { $row[ $column ] = __( 'WordPress', 'mj-update-history' ); };
					if ( $item['type'] === '1' ) { $row[ $column ] = __( 'Theme', 'mj-update-history' ); };
					if ( $item['type'] === '2' ) { $row[ $column ] = __( 'Plugin', 'mj-update-history' ); };
					break;

				case 'state':
					$row[ $column ] = $item['state'];
					break;

				case 'old_version':
					$row[ $column ] = $item['old_version'];
					break;

				case 'new_version':
					$row[ $column ] = $item['new_version'];
					break;

				case 'user_id':
					$row[ $column ] = $item['user_id'];
					break;
			}
		}

		return $row;
	}


	/**
	 * Set csv data
	 */
	function set_data( $items, $columns ) {

		$data = [];
		foreach ( $items as $item ) {
			$data[] = $this->prep_row( $item, $columns );
		}

		return $data;

	}


	/**
	 * Download CSV
	 */
	function download_csv( $data, $columns, $download_date ) {

		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename="export-log_' . $download_date . '.csv"' );

		$output = join( ',', array_values( $columns ) ) . "\n";
		foreach ( $data as $row ) {
			$output .= join( ',', $row ) . "\n";
		}

		echo $output; // @codingStandardsIgnoreLine text-only output

		exit;

	}


	/**
	 * データ作成
	 */
	function set_text_data() {
		$url = $_POST['url'];
		parse_str($url, $str);
		require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
		$mj_update_log_table = new MJUpdateLogTable();
		$mj_update_log_table->prepare_items();
		global $wpdb;
		$logs_table_name = $wpdb->prefix . 'mjuh_logs';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$where = ' WHERE 1 = 1';
		// if a filter was performed.
		if ( $str['type'] && $str['type'] !== '' ) {
			$where .= $wpdb->prepare( ' AND `type` = %s', strtolower( $str['type'] ) );
		}
		if ( $str['state'] && $str['state'] !== '' ) {
			$where .= $wpdb->prepare( ' AND `state` = %s', strtolower( $str['state'] ) );
		}
		if ( $str['user'] && $str['user'] !== '' ) {
			$where .= $wpdb->prepare( ' AND `user_id` = %s', strtolower( $str['user'] ) );
		}
		if ( $str['m'] && $str['m'] !== '' && $str['m'] !== '0' ) {
			require_once( ABSPATH . 'wp-load.php' );
			$where .= $wpdb->prepare( ' AND (DATE_FORMAT(date, %s) = %s)', '%Y%m', $str['m'] );
		}

		$query = "SELECT * FROM $logs_table_name $where ORDER BY date DESC;";
		$items = $wpdb->get_results( $query, ARRAY_A );

		$user_search_key = isset( $str['s'] ) ? wp_unslash( trim( $str['s'] ) ) : '';
		if( $user_search_key ) {
			$items = array_values( array_filter( $items, function( $row ) use( $user_search_key ) {
				foreach( $row as $row_val ) {
					if( stripos( $row_val, $user_search_key ) !== false ) {
						return true;
					}
				}
			} ) );
		}

			function usort_reorder_str( $a, $b ) {
				$url = $_POST['url'];
				parse_str($url, $str);
				$orderby = ( !empty( $str['orderby'] ) ) ? $str['orderby'] : 'date'; //If no sort, default to title
				$order = ( !empty( $str['order'] ) ) ? $str['order'] : 'desc'; //If no order, default to asc
				$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
				return ( $order === 'asc' ) ? $result : -$result; //Send final sort direction to usort
			}
			usort( $items, 'usort_reorder_str' );

		$columns = $mj_update_log_table->get_columns();

		$data_array = $this->set_data( $items, $columns );

		$data = $this->set_body( $data_array );

		$response = array(
			'message' => $data,
		);

		wp_send_json_success( $response );

		wp_die();
	}


	/**
	 * 本文作成
	 */
	function set_body( $data_array ) {

		$data = get_bloginfo( 'name' ) . ' ' . __( 'update history', 'mj-update-history' ) . ' (' . count( $data_array ) . __( 'items', 'mj-update-history' ) . ')';
		$data .= "\r\n";
		$data .= date_i18n( 'Y/m/d H:i:s' ) . "\r\n";
		$data .= '-------------------------------------------------------------';
		$data .= "\r\n\r\n";
		foreach ( $data_array as $item ) {
			$data .= date_i18n( 'Y/m/d H:i:s', $time_stamp  = strtotime( $item['date'] ) );
			$data .= "\r\n";
			$data .= $item['name'] . ' [' . $item['type'] . ']';
			$data .= "\r\n";
			$data .= $item['state'];
			$data .= "\r\n";
			if( !$item['old_version'] ) { $item['old_version'] = 'none'; }
			if( !$item['new_version'] ) { $item['new_version'] = 'none'; }
			$data .= $item['old_version'] . ' -> ' . $item['new_version'];
			$data .= "\r\n";
			$user_info = get_userdata( $item['user_id'] );
			if ( empty( $user_info ) ) {
				$user_name = 'none';
			} else {
				$user_name = $user_info->data->user_login;
			}
			$data .= $user_name;
			$data .= "\r\n";
			$data .= "\r\n";
		}
		$data .= '-------------------------------------------------------------';
		$data .= "\r\n";

		return $data;

	}


	/**
	 * Send email
	 */
	// function send_email( $data ) {

	// 	// set $to
	// 	$user = wp_get_current_user();
	// 	$to = $user->user_email;

	// 	$subject = get_bloginfo( 'name' ) . ' ' . __('update history', 'mj-update-history');
	// 	$message = $data;

	// 	return wp_mail( $to, $subject, $message );

	// }

}

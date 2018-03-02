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

		if( isset( $_REQUEST['download-log'] ) ) {
			// ダウンロードの場合
			require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
			$mj_update_log_table = new MJUpdateLogTable();
			$mj_update_log_table->prepare_items();

			$items = $mj_update_log_table->data;
			$columns = $mj_update_log_table->get_columns();

			$data = $this->set_data( $items, $columns );

			if ( $_REQUEST['download-log'] === 'download' ) {
				$this->download( $data, $columns );
			}
		}

		if( isset( $_REQUEST['email-log'] ) ) {
			// メールの場合
			require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
			$mj_update_log_table = new MJUpdateLogTable();
			$mj_update_log_table->prepare_items();

			$items = $mj_update_log_table->data;
			$columns = $mj_update_log_table->get_columns();

			$data = $this->set_data( $items, $columns );

			if ( $_REQUEST['email-log'] === 'email' ) {
				$this->send_email( $data );
			}
		}

		add_menu_page(
			__('Update History', 'mj-update-history'),    /* HTMLのページタイトル */
			__('Update History', 'mj-update-history'),    /* 管理画面メニューの表示名 */
			'administrator',  /* この機能を利用できるユーザ */
			'mj_update_history',        /* urlに入る名前 */
			array( $this,'admin_page' )   /* 機能を提供するメソッド */
		);
	}


	/**
	 * CSS
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_style(
			'mj-update-history-style',
			plugins_url( 'css/mj-update-history-style.css', __FILE__ ),
			false,
			false,
			'all'
		);
	}


	/**
	 * VIEW設定
	 */
	function admin_page() {

		//テーブル生成用class読み込み
		require_once( MJUH_PLUGIN_DIR . '/lib/table.php' );
		$mj_update_log_table = new MJUpdateLogTable();
		$mj_update_log_table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1>';
		echo __('Update History', 'mj-update-history');
		echo '</h1>';
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '">';
		$mj_update_log_table->search_box( __('Search', 'mj-update-history'), 'search');
		$mj_update_log_table->display();
		echo '</form>';
		echo '</div>';

	}

	private function prep_row( $item, $columns ) {
		$row = [];

		foreach ( array_keys( $columns ) as $column ) {
			switch ( $column ) {
				case 'date':
					$row[ $column ] = date( 'Y/m/d H:i:s', $time_stamp  =strtotime( $item['date'] ) );
//					$row[ $column ] = get_date_from_gmt( $date, 'Y/m/d h:i:s A' );
					break;

				case 'name':
					$row[ $column ] = $item['name'];
					break;

				case 'type':
					if( $item['type'] === '0' ) { $row[ $column ] = __('WordPress', 'mj-update-history'); };
					if( $item['type'] === '1' ) { $row[ $column ] = __('Theme', 'mj-update-history'); };
					if( $item['type'] === '2' ) { $row[ $column ] = __('Plugin', 'mj-update-history'); };
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
	 * Set data for csv
	 */
	function set_data( $items, $columns ) {

		$data = [];
		foreach ( $items as $item ) {
			$data[] = $this->prep_row( $item, $columns );
		}

		return $data;

	}


	/**
	 * ダウンロード
	 */
	function download( $data, $columns ) {

		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename="export-log.csv"' );

		$output = join( ',', array_values( $columns ) ) . "\n";
		foreach ( $data as $row ) {
			$output .= join( ',', $row ) . "\n";
		}

		echo $output; // @codingStandardsIgnoreLine text-only output

		exit;

	}


	/**
	 * メール送信
	 */
	function send_email( $data ) {

		// set $to
		$user = wp_get_current_user();
		$to = $user -> user_email;

		$subject = __('wordpress update log by mj update history');
		$message = $data;

		wp_mail ( $to, $subject, $message );

		exit;

	}

}

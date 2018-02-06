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
		add_menu_page(
			'作業履歴',    /* HTMLのページタイトル */
			'作業履歴',    /* 管理画面メニューの表示名 */
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

	?>

	<?php $mj_update_log_table->display() ?>
	<?php
	}


}

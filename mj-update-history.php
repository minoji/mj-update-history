<?php
/*
Plugin Name: MJ UPDATE HISTORY
Description: WordPress coreやプラグイン、テーマのアップデート履歴を出力するプラグインです。
Author: minoji
Version: 0.1
*/
class MJUpdateHistory {


/* =========================================================
   __construct
   ========================================================= */

	function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );            /* パネル作成 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_common_css' ) );  /* CSS出力 */

		/* プラグインアップデート前に現在のプラグイン名とバージョン確認 */
		add_filter( 'upgrader_pre_install', array( $this, 'get_plugin_current_version' ), 10, 2);
		/* プラグインアップデート後に新しいバージョンと日付を確認 */
		add_action( 'upgrader_process_complete', array($this, 'get_plugin_new_version' ), 10, 2 );


	}


/* =========================================================
   基本項目
   ========================================================= */

	/* ベースアクション */
	function admin_menu_action() {
		add_menu_page(
			'作業履歴',    /* HTMLのページタイトル */
			'作業履歴',    /* 管理画面メニューの表示名 */
			'administrator',  /* この機能を利用できるユーザ */
			'mj_update_history',        /* urlに入る名前 */
			array( $this,'mj_update_history_page' )   /* 機能を提供するメソッド */
		);
	}


	/* view */
	function mj_update_history_page() {
		include 'mj-update-history-output.php';
	}


/* =========================================================
   個別機能
   ========================================================= */

	/**
	* アップデート前のプラグイン情報を取得する
	*/
	function get_plugin_current_version() {

		//一時格納変数リセット
		$current_plugin = '';
		$plugin_name = '';
		$plugin_old_version = '';

		// プラグインの名前とバージョン出力
		$current_plugin = get_plugin_data();
		$plugin_name = $current_plugin['Name'];
		$plugin_old_version = $current_plugin['Version'];

	}


	/**
	* アップデート後のプラグイン情報を取得する
	*/
	function get_plugin_new_version( $upgrader_object, $options ) {
    $current_plugin_path_name = plugin_basename( __FILE__ );
    if ($options['action'] == 'update' && $options['type'] == 'plugin' ){
        foreach($options['plugins'] as $each_plugin){
            if ($each_plugin == $current_plugin_path_name ){

            // YOUR CODES

            }
        }
    }
	}

}

/* グローバル変数にMJUpdateHistoryインスタンスを生成 */
$mj_update_history = new MJUpdateHistory;

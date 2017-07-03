<?php
/*
Plugin Name: MJ UPDATE HISTORY
Description: WordPress coreやプラグイン、テーマのアップデート履歴を出力するプラグインです。
Author: minoji
Version: 0.1.0
*/


class MJUpdateHistory {

/* =========================================================
	 __construct
	 ========================================================= */
	function __construct() {

		/* アクティベート時にデータベース作成 */
		register_activation_hook( __FILE__, array( $this, 'database_install' ) );

		/* 管理画面設定 */
		add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );            /* パネル作成 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );  /* CSS出力 */

		/* プラグインアップデート前に現在のプラグイン名とバージョンを取得してデータベースに保存する */
		add_filter( 'upgrader_pre_install', array( $this, 'save_all_plugin_current_version' ), 10, 2 );
		/* プラグイン追加時に該当するプラグイン名とバージョンを取得してデータベースに保存する */
		// add_filter( ' ', array( $this, 'save_plugin_current_version' ), 10, 2 );
		/* プラグイン削除時に該当するプラグイン名とバージョンを取得してデータベースから削除する */
		// add_filter( ' ', array( $this, 'remove_plugin_current_version' ), 10, 2 );
		/* プラグインアップデート後に該当するプラグイン名と新しいバージョンと作業日を取得してベータベースに保存する */
		add_action( 'upgrader_process_complete', array( $this, 'save_log' ), 10, 2 );

	}


/* =========================================================
	 機能
	 ========================================================= */

	/**
	* データベース作成
	*/
	function database_install() {
		global $wpdb;
		$plugins_table_name = $wpdb->prefix . 'mj_udh_plugins';
		$logs_table_name    = $wpdb->prefix . 'mj_udh_logs';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		$plugins_sql = "CREATE TABLE $plugins_table_name (
			id bigint(9) NOT NULL AUTO_INCREMENT,
			name varchar(55) NOT NULL,
			version varchar(55) NOT NULL,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
		dbDelta( $plugins_sql );

		$logs_sql = "CREATE TABLE $logs_table_name (
			id bigint(9) NOT NULL AUTO_INCREMENT,
			name varchar(55) NOT NULL,
			old_version varchar(55) NOT NULL,
			new_version varchar(55) NOT NULL,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
		dbDelta( $logs_sql );

		$this->save_all_plugin_current_version();

	}


	/**
	* データベースのバージョンをチェックして最新版でない場合はアップデート
	*/


	/**
	* すべてのプラグイン名とバージョンを取得してデータベースに保存する
	*/
	function save_all_plugin_current_version() {
		global $wpdb;
		$plugins_table_name = $wpdb->prefix . 'mj_udh_plugins';
		// プラグイン取得
		// https://codex.wordpress.org/Function_Reference/get_plugins
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$all_plugins = get_plugins();
		// データベース登録
		foreach ( $all_plugins as $plugin ) {
			//プラグインがすでに登録されているか確認
			$plugin_name = $plugin['Name'];
			$plugin_version = $plugin['Version'];

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $plugins_table_name WHERE name = %s", $plugin_name ), ARRAY_A );

			if( $result ) {
				//プラグインがデータベースに存在する場合は更新
				$wpdb->update(
					$plugins_table_name,
					array(
						'version' => $plugin_version
					),
					array( 'name' => $plugin_name ),
					array(
						'%s'
					),
					array( '%s' )
				);
			} else {
				//プラグインがデータベースに存在しない場合は新規登録
				$wpdb->insert(
					$plugins_table_name,
					array(
						'name' => $plugin_name,
						'version' => $plugin_version
					),
					array(
						'%s',
						'%s'
					)
				);
			}

		}
	}


	/**
	* データベースに保存されているプラグイン名とバージョンを削除する
	*/
	function remove_plugin_current_version() {
		global $wpdb;
		$plugins_table_name = $wpdb->prefix . 'mj_udh_plugins';
		$logs_table_name    = $wpdb->prefix . 'mj_udh_logs';
	}


	/**
	* 作業logをデータベースに保存する
	*/
	function save_log( $upgrader_object, $options ) {
		global $wpdb;
		$plugins_table_name = $wpdb->prefix . 'mj_udh_plugins';
		$logs_table_name    = $wpdb->prefix . 'mj_udh_logs';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// 作業日取得
		$date = date( 'Y-n-j' );

		// 更新がプラグインの場合
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ){
			$current_plugin_path_name = plugin_basename( __FILE__ );
			foreach( $options['plugins'] as $plugin ) {
				// TODO: コメントアウト外して動作確認する
				// if ( $plugin == $current_plugin_path_name ) {

					// プラグインの情報を取得
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
					$plugin_name = $plugin_data['Name'];
					$plugin_new_version = $plugin_data['Version'];
					$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $plugins_table_name WHERE name = %s", $plugin_name ), ARRAY_A );
					$plugin_old_version = $result['version'];

					// データベース登録
					$wpdb->insert(
						$logs_table_name,
						array(
							'name' => $plugin_name,
							'old_version' => $plugin_old_version,
							'new_version' => $plugin_new_version,
							'date' => $date
						),
						array(
							'%s',
							'%s',
							'%s',
							'%s'
						)
					);

					// 旧バージョンのデータベースも更新
					$wpdb->update(
						$plugins_table_name,
						array(
							'version' => $plugin_new_version
						),
						array( 'name' => $plugin_name ),
						array(
							'%s'
						),
						array( '%s' )
					);

				// }
			}
		}

		//更新がテーマの場合
		elseif( $options['action'] == 'update' && $options['type'] == 'theme' ) {
			$current_theme_path_name = theme_basename( __FILE__ );
			foreach( $options['theme'] as $each_theme ){
				if ( $each_theme == $current_theme_path_name ) {
					$each_theme;
				}
			}
		}

		//更新がコアの場合
		elseif( $options['action'] == 'update' && $options['type'] == 'core' ) {
		}

	}


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
			'administrator',  /* この機能を		利用できるユーザ */
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
		global $wpdb;
		$logs_table_name    = $wpdb->prefix . 'mj_udh_logs';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$results = $wpdb->get_results( "SELECT * FROM $logs_table_name", ARRAY_A );
		foreach( $results as $val ){
	?>
		<p><?php echo esc_html($val['name']); ?></p>
		<p><?php echo esc_html($val['old_version']); ?></p>
		<p><?php echo esc_html($val['new_version']); ?></p>
		<p><?php echo esc_html($val['date']); ?></p>
	<?php
		}
	}
}


/* グローバル変数にMJUpdateHistoryインスタンスを生成 */
$mj_update_history = new MJUpdateHistory;

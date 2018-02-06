<?php

global $mjuh_db_version;
$mjuh_db_version = '1.0';

class MJUHDatabase {

	// public static function get_instance() {

	// 	static $instance = null;

	// 	if ( $instance == null ) {
	// 		$instance = new self();
	// 	}

	// 	return $instance;
	// }

/* =========================================================
	 初期作業
	 ========================================================= */

	/**
	* データベース作成
	*/
	function install_database() {

		global $wpdb;
		global $mduh_db_version;

		$datas_table_name   = $wpdb->prefix . 'mjuh_datas';
		$logs_table_name    = $wpdb->prefix . 'mjuh_logs';

		$charset_collate = $wpdb->get_charset_collate();

		$datas_sql = "CREATE TABLE $datas_table_name (
			id bigint(9) NOT NULL AUTO_INCREMENT,
			name varchar(55) NOT NULL,
			version varchar(55) NOT NULL,
			type int(11) NOT NULL,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		$logs_sql = "CREATE TABLE $logs_table_name (
			id bigint(9) NOT NULL AUTO_INCREMENT,
			name varchar(55) NOT NULL,
			type int(11) NOT NULL,
			state varchar(55) NOT NULL,
			old_version varchar(55) NOT NULL,
			new_version varchar(55) NOT NULL,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $datas_sql );
		dbDelta( $logs_sql );

		$this->save_all_plugin_current_version();

		add_option( 'mjuh_db_version', $mjuh_db_version );

	}


	/**
	* データベースのバージョンをチェックして最新版でない場合はアップデート
	*/


	/**
	* すべてのプラグイン名とバージョンを取得してデータベースに保存する
	*/
	function save_all_plugin_current_version() {
		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		// プラグイン取得
		// https://codex.wordpress.org/Function_Reference/get_plugins
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();
		// データベース登録
		foreach ( $all_plugins as $plugin ) {
			//プラグインがすでに登録されているか確認
			$plugin_name = $plugin['Name'];
			$plugin_version = $plugin['Version'];

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $datas_table_name WHERE name = %s", $plugin_name ), ARRAY_A );

			$date = date( 'Y-n-j H:i:s' );

			if( $result ) {
				//プラグインがデータベースに存在する場合は更新
				$wpdb->update(
					$datas_table_name,
					array(
						'version' => $plugin_version,
						'type' => 2,
						'date' => $date
					),
					array(
						'name' => $plugin_name,
						'type' => 2,
					),
					array(
						'%s'
					),
					array( '%s' )
				);
			} else {
				//プラグインがデータベースに存在しない場合は新規登録
				$wpdb->insert(
					$datas_table_name,
					array(
						'name' => $plugin_name,
						'version' => $plugin_version,
						'type' => 2,
						'date' => $date
					),
					array(
						'%s',
						'%s',
						'%s'
					)
				);
			}

		}
	}


	/**
	* すべてのテーマ名とバージョンを取得してデータベースに保存する
	*/
  function save_all_theme_current_version() {
  }


	/**
	* コアのバージョンを取得してデータベースに保存する
	*/
  function save_core_current_version() {
  }



/* =========================================================
	 個別作業
	 ========================================================= */

	/**
	* 作業logテーブル'mjuh_datas'へプラグイン/テーマ/コアの更新作業logを記録する
	*/
	function save_db_log( $name, $type, $state, $old_version, $new_version ) {

		global $wpdb;
		$logs_table_name = $wpdb->prefix . 'mjuh_logs';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$date = date( 'Y-n-j H:i:s' );

		// データベース登録
		$wpdb->insert(
			$logs_table_name,
			array(
				'name'        => $name,
				'type'        => $type,
				'state'       => $state,
				'old_version' => $old_version,
				'new_version' => $new_version,
				'date'        => $date
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			)
		);

	}


	/**
	 * データ一覧テーブル'mjuh_datas'へ新しいプラグイン/テーマの情報を追加する
	*/
	function save_db_data( $data_name, $version, $type ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$date = date( 'Y-n-j H:i:s' );


		// 旧バージョンのデータベースも更新
		$wpdb->insert(
			$datas_table_name,
			array(
				'name'    => $data_name,
				'version' => $version,
				'type'    => $type,
				'date'    => $date
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s'
			)
		);

	}


	/**
	 * データ一覧テーブル'mjuh_datas'へ新しいのプラグイン/テーマ/コアのバージョンを上書きする
	*/
	function update_data( $plugin_name, $plugin_new_version, $type ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// 旧バージョンのデータベースも更新
		$wpdb->update(
			$datas_table_name,
			array(
				'version' => $plugin_new_version
			),
			array(
				'name' => $plugin_name,
				'type' => $type
			),
			array(
				'%s'
			),
			array( '%s' )
		);

	}


}

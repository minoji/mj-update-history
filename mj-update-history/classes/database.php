<?php

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
			old_version varchar(55) NULL,
			new_version varchar(55) NOT NULL,
			user_id bigint(9) NOT NULL ,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $datas_sql );
		dbDelta( $logs_sql );

		$this->save_all_plugin_current_version();
		$this->save_all_theme_current_version();
		$this->save_core_current_version();

		update_option( 'mjuh_db_version', MJUH_DATABASE_VERSION );

	}


	/**
	* データベースのバージョンをチェック
	*/
	function check_database_version() {

		global $wpdb;

		if( get_option( 'mjuh_db_version' ) != MJUH_DATABASE_VERSION ) {

			$this->install_database();

		}

	}


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
			$type = 2;

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $datas_table_name WHERE name = %s", $plugin_name ), ARRAY_A );

			$date = date_i18n( 'Y-n-j H:i:s' );

			if( $result ) {
				//プラグインがデータベースに存在する場合は更新
				$this->update_data( $plugin_name, $plugin_version, $type );
			} else {
				//プラグインがデータベースに存在しない場合は新規登録
				$this->save_data( $plugin_name, $plugin_version, $type );
			}

		}
	}


	/**
	* すべてのテーマ名とバージョンを取得してデータベースに保存する
	*/
	function save_all_theme_current_version() {
		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		// テーマ取得
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_themes = wp_get_themes();
		// データベース登録
		foreach ( $all_themes as $theme ) {
			//プラグインがすでに登録されているか確認
			$theme_name = $theme['Name'];
			$theme_version = $theme['Version'];
			$type = 1;

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $datas_table_name WHERE name = %s", $theme_name ), ARRAY_A );

			$date = date_i18n( 'Y-n-j H:i:s' );

			if( $result ) {
				//プラグインがデータベースに存在する場合は更新
				$this->update_data( $theme_name, $theme_version, $type );
			} else {
				//プラグインがデータベースに存在しない場合は新規登録
				$this->save_data( $theme_name, $theme_version, $type );
			}

		}
	}


	/**
	* コアのバージョンを取得してデータベースに保存する
	*/
	function save_core_current_version() {
		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		// コア情報取得
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		// データベース登録
		// コアがすでに登録されているか確認
		$core_name = 'WordPress_Core';
		global $wp_version;
		$core_version = $wp_version;
		$type = 0;

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $datas_table_name WHERE name = %s", $core_name ), ARRAY_A );

		$date = date_i18n( 'Y-n-j H:i:s' );

		if( $result ) {
			//プラグインがデータベースに存在する場合は更新
			$this->update_data( $core_name, $core_version, $type );
		} else {
			//プラグインがデータベースに存在しない場合は新規登録
			$this->save_data( $core_name, $core_version, $type );
		}
	}



/* =========================================================
	 個別作業
	 ========================================================= */

	/**
	* 作業logテーブル'mjuh_datas'へプラグイン/テーマ/コアの更新作業logを記録する
	*/
	function save_log( $name, $type, $state, $old_version, $new_version, $user_id ) {

		global $wpdb;
		$logs_table_name = $wpdb->prefix . 'mjuh_logs';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$date = date_i18n( 'Y-n-j H:i:s' );

		// データベース登録
		$wpdb->insert(
			$logs_table_name,
			array(
				'name'        => $name,
				'type'        => $type,
				'state'       => $state,
				'old_version' => $old_version,
				'new_version' => $new_version,
				'user_id'     => $user_id,
				'date'        => $date
			),
			array(
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s'
			)
		);

	}


	/**
	 * データ一覧テーブル'mjuh_datas'へ新しいプラグイン/テーマの情報を追加する
	*/
	function save_data( $name, $version, $type ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$date = date_i18n( 'Y-n-j H:i:s' );


		// 旧バージョンのデータベースも更新
		$wpdb->insert(
			$datas_table_name,
			array(
				'name'    => $name,
				'version' => $version,
				'type'    => $type,
				'date'    => $date
			),
			array(
				'%s',
				'%s',
				'%d',
				'%s'
			)
		);

	}


	/**
	 * データ一覧テーブル'mjuh_datas'へ新しいのプラグイン/テーマ/コアのバージョンを上書きする
	*/
	function update_data( $name, $new_version, $type ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$date = date_i18n( 'Y-n-j H:i:s' );

		// 旧バージョンのデータベースも更新
		$wpdb->update(
			$datas_table_name,
			array(
				'version' => $new_version,
				'date' => $date
			),
			array(
				'name' => $name,
				'type' => $type
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%s',
				'%d'
			)
		);

	}


}

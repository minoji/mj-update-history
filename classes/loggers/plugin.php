<?php

class MJUHPluginLogger {

	/**
	* プラグインがアップデートされた時の処理
	* logとdataをデータベースに保存する
	*/
	function updated( $upgrader_object, $options ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
		$mjuh_database = new MJUHDatabase;

		// 更新がプラグインの場合
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ){

			foreach( $options['plugins'] as $plugin ) {

				// プラグインの情報を取得
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
				$plugin_name = $plugin_data['Name'];
				$type = 2; //2:プラグイン
				$state = 'updated';
				$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 2", $plugin_name ), ARRAY_A );
				$plugin_old_version = $result['version'];
				$plugin_new_version = $plugin_data['Version'];

				// logsテーブルに登録
				$mjuh_database->save_log( $plugin_name, $type, $state, $plugin_old_version, $plugin_new_version );
				// datasテーブルへも更新
				$mjuh_database->update_data( $plugin_name, $plugin_new_version, $type );

			}

		}

		//更新がテーマの場合
		elseif( $options['action'] == 'update' && $options['type'] == 'theme' ) {

			foreach( $options['themes'] as $theme ){

				// テーマの情報を取得
				$theme_data = wp_get_theme( $theme );
				$theme_name = $theme_data['Name'];
				$type = 1; //2:テーマ
				$state = 'updated';
				$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 1", $theme_name ), ARRAY_A );
				$theme_old_version = $result['version'];
				$theme_new_version = $theme_data['Version'];

				// logsテーブルに登録
				$mjuh_database->save_log( $theme_name, $type, $state, $theme_old_version, $theme_new_version );
				// datasテーブルへも更新
				$mjuh_database->update_data( $theme_name, $theme_new_version, $type );

			}

		}

	}


	/**
	* コアがアップデートされた時の処理
	* logとdataをデータベースに保存する
	*/
	function updated_core( $core_new_version ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
		$mjuh_database = new MJUHDatabase;

		// コア情報取得
		$core_name = 'WordPress_Core';
		$type = 0;
		$state = 'updated';

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 0", $core_name ), ARRAY_A );
		$core_old_version = $result['version'];

		// logsテーブルに登録
		$mjuh_database->save_log( $core_name, $type, $state, $core_old_version, $core_new_version );
		// datasテーブルへも更新
		$mjuh_database->update_data( $core_name, $core_new_version, $type );

	}


	/**
	* プラグインが有効化された時の処理
	* logとdataをデータベースに保存する
	*/
	function activated( $plugin ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
		$mjuh_database = new MJUHDatabase;

		// プラグインの情報を取得
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
		$plugin_name = $plugin_data['Name'];
		$type = 2;
		$state = 'activated';
		$plugin_new_version = $plugin_data['Version'];
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $datas_table_name WHERE name = %s AND type = 2", $plugin_name ), ARRAY_A );
		if( !$result ) {
			//datasテーブルに存在しない場合はdatasテーブルに新規登録
			$mjuh_database->save_data( $plugin_name, $plugin_new_version, $type );
		} else {
			//datasテーブルに存在する場合はdatasテーブルの該当プラグインを更新
			$mjuh_database->update_data( $plugin_name, $plugin_new_version, $type );
		}

		// logsテーブルに登録
		$mjuh_database->save_log( $plugin_name, $type, $state, null, $plugin_new_version );

	}


	/**
	* プラグインが無効化された時の処理
	* logとdataをデータベースに保存する
	*/
	function deactivated( $plugin ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
		$mjuh_database = new MJUHDatabase;

		// プラグインの情報を取得
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
		$plugin_name = $plugin_data['Name'];
		$type = 2; //2:プラグイン
		$state = 'deactivated';
		$plugin_version = $plugin_data['Version'];

		// logsテーブルに登録
		$mjuh_database->save_log( $plugin_name, $type, $state, null, $plugin_version );

	}


}

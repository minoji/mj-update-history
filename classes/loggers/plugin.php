<?php

class MJUHPluginLogger {

	/**
	* プラグイン / テーマ がアップデートされた時の処理
	* logとdataをデータベースに保存する
	*/
	function updated( $upgrader_object, $options ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
		$mjuh_database = new MJUHDatabase;

		$user_id = $this->get_user_id();

		// 更新がプラグインの場合
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ){

			foreach( $options['plugins'] as $plugin ) {

				// プラグインの情報を取得
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
				$plugin_name = $plugin_data['Name'];
				$type = 2;
				$state = __( 'updated', 'mj-update-history' );
				$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 2", $plugin_name ), ARRAY_A );
				$plugin_old_version = $result['version'];
				$plugin_new_version = $plugin_data['Version'];

				// logsテーブルに登録
				$mjuh_database->save_log( $plugin_name, $type, $state, $plugin_old_version, $plugin_new_version, $user_id );
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
				$type = 1;
				$state = __( 'updated', 'mj-update-history' );
				$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 1", $theme_name ), ARRAY_A );
				$theme_old_version = $result['version'];
				$theme_new_version = $theme_data['Version'];

				// logsテーブルに登録
				$mjuh_database->save_log( $theme_name, $type, $state, $theme_old_version, $theme_new_version, $user_id );
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

		$user_id = $this->get_user_id();

		// コア情報取得
		$core_name = 'WordPress_Core';
		$type = 0;
		$state = __( 'updated', 'mj-update-history' );

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 0", $core_name ), ARRAY_A );
		$core_old_version = $result['version'];

		// logsテーブルに登録
		$mjuh_database->save_log( $core_name, $type, $state, $core_old_version, $core_new_version, $user_id );
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

		$user_id = $this->get_user_id();

		// プラグインの情報を取得
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
		$plugin_name = $plugin_data['Name'];
		$type = 2;
		$state = __( 'activated', 'mj-update-history' );
		$plugin_old_version = '';
		$plugin_new_version = $plugin_data['Version'];
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $datas_table_name WHERE name = %s AND type = 2", $plugin_name ), ARRAY_A );
		if( !$result ) {
			// datasテーブルに存在しない場合はdatasテーブルに新規登録
			$mjuh_database->save_data( $plugin_name, $plugin_new_version, $type );
		} else {
			// datasテーブルに存在する場合はdatasテーブルの該当プラグインを更新
			$mjuh_database->update_data( $plugin_name, $plugin_new_version, $type );
		}

		// logsテーブルに登録
		$mjuh_database->save_log( $plugin_name, $type, $state, $plugin_old_version, $plugin_new_version, $user_id );

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

		$user_id = $this->get_user_id();

		// プラグインの情報を取得
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, true, false );
		$plugin_name = $plugin_data['Name'];
		$type = 2;
		$state = __( 'deactivated', 'mj-update-history' );
		$plugin_old_version = '';
		$plugin_new_version = $plugin_data['Version'];

		// logsテーブルに登録
		$mjuh_database->save_log( $plugin_name, $type, $state, $plugin_old_version, $plugin_new_version, $user_id );

	}


	/**
	* テーマが変更された時の処理
	* logをデータベースに保存する
	*/
	function changed_theme( $new_theme ) {

		global $wpdb;
		$datas_table_name = $wpdb->prefix . 'mjuh_datas';
		$logs_table_name = $wpdb->prefix . 'mjuh_logs';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
		$mjuh_database = new MJUHDatabase;

		$user_id = $this->get_user_id();

		// テーマの情報を取得
		$theme_name = $new_theme;
		$type = 1;
		$state = __( 'activated', 'mj-update-history' );
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 1", $theme_name ), ARRAY_A );
		$theme_old_version = '';
		$theme_new_version = $result['version'];

		// logsテーブルに登録
		$mjuh_database->save_log( $theme_name, $type, $state, $theme_old_version, $theme_new_version, $user_id );

	}


	/**
	* ユーザーIDの取得
	*/
	function get_user_id() {
		$user = wp_get_current_user();
		if ( empty( $user->ID ) ) {
			return 0;
		} else {
			return $user->ID;
		}
	}

}

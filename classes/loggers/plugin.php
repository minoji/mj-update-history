<?php

class MJUHPluginLogger {

	// public static function get_instance() {

	// 	static $instance = null;

	// 	if ( $instance == null ) {
	// 		$instance = new self();
	// 	}

	// 	return $instance;
	// }

  // installed;
  // updated;
  // activated;
  // deactivated;
  // uninstalled;

	/**
	* 作業logをデータベースに保存する
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
				$state = 'update';
				$result = $wpdb->get_row( $wpdb->prepare( "SELECT version FROM $datas_table_name WHERE name = %s AND type = 2", $plugin_name ), ARRAY_A );
				$plugin_old_version = $result['version'];
				$plugin_new_version = $plugin_data['Version'];

				// logsテーブルに登録
				$mjuh_database->save_db_log( $plugin_name, $type, $state, $plugin_old_version, $plugin_new_version );
				// datasテーブルへも更新
				$mjuh_database->update_data( $plugin_name, $plugin_new_version, $type );

			}

		}

		//更新がテーマの場合
		elseif( $options['action'] == 'update' && $options['type'] == 'theme' ) {

			foreach( $options['theme'] as $each_theme ){
				$each_theme;
			}

		}

		//更新がコアの場合
		elseif( $options['action'] == 'update' && $options['type'] == 'core' ) {

			$core;

		}

	}


}

<?php
/*
Plugin Name: MJ UPDATE HISTORY
Description: MJ UPDATE HISTORY is a plugin that can display a list in WordPress Core, Plugin, Theme Update History Recording, Administration screen, and output to csv.
Author: minoji
Version: 0.1.1
*/

define( 'MJUH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MJUH_DATABASE_VERSION', '0.1.5' );

require_once( MJUH_PLUGIN_DIR . 'classes/admin.php' );
require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
require_once( MJUH_PLUGIN_DIR . 'classes/loggers/plugin.php' );

$mjuh_database = new MJUHDatabase;
$mjuh_admin = new MJUHAdmin;
$mjuh_plugin_logger = new MJUHPluginLogger;

add_action( 'plugins_loaded', 'mj_update_history_textdomain' );
function mj_update_history_textdomain() {
	load_plugin_textdomain( 'mj-update-history', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


/* アクティベート時にデータベースのバージョンチェックし存在しなければデータベースインストール */
register_activation_hook( __FILE__, array( $mjuh_database, 'install_database' ) );
/* プラグインロード時にデータベースのバージョンチェックし存在しなければデータベースインストール */
add_action( 'plugins_loaded', array( $mjuh_database, 'check_database_version' ) );
/* ディアクティベート時にデータベース消去 */
// register_deactivation_hook( __FILE__, array( $mjuh_database, 'uninstall_database' ) );

/* 管理画面設定 */
add_action( 'admin_menu', array( $mjuh_admin, 'admin_menu_action' ) );            /* パネル作成 */
add_action( 'admin_enqueue_scripts', array( $mjuh_admin, 'admin_enqueue_scripts' ) );  /* CSS出力 */

add_filter('set-screen-option', 'mjlh_logs_per_page_set_option', 10, 3);
function mjlh_logs_per_page_set_option($status, $option, $value) {
	if ( 'mjlh_logs_per_page' == $option ) return $value;
	return $status;
}


/**
* Plugin
* アップデート前に現在のプラグイン名とバージョンを取得してデータベースに保存する
*/
// add_filter( 'upgrader_pre_install', array( $mjuh_database, 'save_all_plugin_current_version' ), 10, 2 );

/**
* Plugin
* 有効化時に該当するプラグイン名とバージョンを取得してデータベースに保存する
*/
add_filter( 'activated_plugin', array( $mjuh_plugin_logger, 'activated' ), 10, 2 );

/**
* Plugin
* 無効化時に該当するプラグイン名とバージョンを取得してデータベースに保存する
*/
add_filter( 'deactivated_plugin', array( $mjuh_plugin_logger, 'deactivated' ), 10, 2 );

/**
* Theme
* 変更時に旧テーマと新テーマ名を取得してデータベースに保存する
*/
add_filter( 'switch_theme', array( $mjuh_plugin_logger, 'changed_theme' ), 10, 2 );

/**
* Core/Theme/Plugin
* アップデート後に該当する項目の名前と新旧バージョンを取得してベータベースに保存する
*/
add_action( 'upgrader_process_complete', array( $mjuh_plugin_logger, 'updated' ), 10, 2 );
add_action( '_core_updated_successfully', array( $mjuh_plugin_logger, 'updated_core' ), 10, 2 );



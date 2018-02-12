<?php
/*
Plugin Name: MJ UPDATE HISTORY
Description: WordPress coreやプラグイン、テーマのアップデート履歴を出力するプラグインです。
Author: minoji
Version: 0.1.1
*/

define( 'MJUH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MJUH_DATABASE_VERSION', '0.1.1' );


require_once( MJUH_PLUGIN_DIR . 'classes/admin.php' );
require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
require_once( MJUH_PLUGIN_DIR . 'classes/loggers/plugin.php' );

$mjuh_database = new MJUHDatabase;
$mjuh_admin = new MJUHAdmin;
$mjuh_plugin_logger = new MJUHPluginLogger;

/* アクティベート時にデータベースのバージョンチェックし存在しなければデータベースインストール */
register_activation_hook( __FILE__, array( $mjuh_database, 'install_database' ) );
/* プラグインロード時にデータベースのバージョンチェックし存在しなければデータベースインストール */
add_action( 'plugins_loaded', array( $mjuh_database, 'check_database_version' ) );
/* ディアクティベート時にデータベース消去 */
// register_deactivation_hook( __FILE__, array( $mjuh_database, 'uninstall_database' ) );

/* 管理画面設定 */
add_action( 'admin_menu', array( $mjuh_admin, 'admin_menu_action' ) );            /* パネル作成 */
add_action( 'admin_enqueue_scripts', array( $mjuh_admin, 'admin_enqueue_scripts' ) );  /* CSS出力 */

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
* 無効化時に該当するプラグイン名とバージョンを取得してデータベースから削除する
*/
add_filter( 'deactivated_plugin', array( $mjuh_plugin_logger, 'deactivated' ), 10, 2 );

/**
* Core/Theme/Plugin
* アップデート後に該当する項目の名前と新旧バージョンを取得してベータベースに保存する
*/
add_action( 'upgrader_process_complete', array( $mjuh_plugin_logger, 'updated' ), 10, 2 );
add_action( '_core_updated_successfully', array( $mjuh_plugin_logger, 'updated_core' ), 10, 2 );



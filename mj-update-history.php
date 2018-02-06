<?php
/*
Plugin Name: MJ UPDATE HISTORY
Description: WordPress coreやプラグイン、テーマのアップデート履歴を出力するプラグインです。
Author: minoji
Version: 0.1.0
*/

define( 'MJUH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


require_once( MJUH_PLUGIN_DIR . 'classes/admin.php' );
require_once( MJUH_PLUGIN_DIR . 'classes/database.php' );
require_once( MJUH_PLUGIN_DIR . 'classes/loggers/plugin.php' );

$mjuh_database = new MJUHDatabase;
$mjuh_admin = new MJUHAdmin;
$mjuh_plugin_logger = new MJUHPluginLogger;

/* アクティベート時にデータベース作成 */
register_activation_hook( __FILE__, array( $mjuh_database, 'install_database' ) );

/* 管理画面設定 */
add_action( 'admin_menu', array( $mjuh_admin, 'admin_menu_action' ) );            /* パネル作成 */
add_action( 'admin_enqueue_scripts', array( $mjuh_admin, 'admin_enqueue_scripts' ) );  /* CSS出力 */

/* プラグインアップデート前に現在のプラグイン名とバージョンを取得してデータベースに保存する */
// add_filter( 'upgrader_pre_install', array( $mjuh_database, 'save_all_plugin_current_version' ), 10, 2 );
/* プラグイン追加時に該当するプラグイン名とバージョンを取得してデータベースに保存する */
// add_filter( '********************', array( $mjuh_plugin_logger, 'installed' ), 10, 2 );
/* プラグイン無効化時に該当するプラグイン名とバージョンを取得してデータベースから削除する */
// add_filter( '********************', array( $mjuh_plugin_logger, 'deactivated' ), 10, 2 );
/* プラグインアップデート後に該当するプラグイン名と新しいバージョンと作業日を取得してベータベースに保存する */
add_action( 'upgrader_process_complete', array( $mjuh_plugin_logger, 'updated' ), 10, 2 );



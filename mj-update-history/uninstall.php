<?php


if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

global $wpdb;
$datas_table_name   = $wpdb->prefix . 'mjuh_datas';
$logs_table_name    = $wpdb->prefix . 'mjuh_logs';

$wpdb->query( "DROP TABLE IF EXISTS $datas_table_name" );
$wpdb->query( "DROP TABLE IF EXISTS $logs_table_name" );

delete_option( 'mjuh_db_version' );

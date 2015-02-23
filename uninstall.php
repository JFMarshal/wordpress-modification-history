<?php
namespace robido;
$ModHistory = new ModHistory;

// Uninstall ModHistory
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$ModHistory->table}" );
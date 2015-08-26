<?php
namespace robido;
// require_once( dirname( __FILE__ ) . '/mod_history.php' );
// $ModHistory = new ModHistory;

// Uninstall ModHistory
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$ModHistory->table}" );
delete_option( 'wp_mod_history_options' );
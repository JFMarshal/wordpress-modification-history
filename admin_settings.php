<?php
namespace robido;
require_once( dirname( __FILE__ ) . '/mod_history.php' );

class AdminSettings extends ModHistory {
	
	function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_pages' ) );
	}

	/**
	 * Add settings page(s)
	 */
	function add_settings_pages() {
		add_submenu_page( 'settings.php', 'Modification History', 'Modification History', 'manage_options', 'mod-history', array( $this, 'settings_page' ) );		
	}

	/**
	 * Render the settings page for our options
	 */
	function settings_page() {
		echo '<h1>hai</h1>';
	}

}

new AdminSettings;
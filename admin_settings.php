<?php
namespace robido;
if ( ! defined( 'ABSPATH' ) ) exit;

class AdminSettings extends ModHistory {
	
	private $post_types_blacklist = array( 'attachment', 'revision', 'nav_menu_item' );
	private $post_types_enabled = array( 'post', 'page' );

	function __construct() {
		// Add our actions for admin settings
		add_action( 'admin_menu', array( $this, 'add_settings_pages' ) );

		// Apply filter to allow developers to change the default post types that are enabled
		$this->post_types_enabled = apply_filters( 'wp_mods_post_types_enabled', $this->post_types_enabled );
	}

	/**
	 * Add settings page(s)
	 */
	function add_settings_pages() {
		add_options_page( 'Modification History Settings', 'Modification History', 'manage_options', 'mod-history', array( $this, 'settings_page' ) );
	}

	/**
	 * Render the settings page for our options
	 */
	function settings_page() {
		$this->process_post();
		echo '<h1>Modification History Settings</h1>';
		$post_types = get_post_types();

		// Apply wp_mods_post_types_blacklist filter to not display certain post types for tracking (return an empty array on this filter to enable these)
		$post_types = array_diff( $post_types, apply_filters( 'wp_mods_post_types_blacklist', $this->post_types_blacklist ) );

		// Get post types that are enabled for modification tracking
		$options = get_option( 'wp_mod_history_options', false );
		$this->post_types_enabled = $options ? $options['post_types'] : $this->post_types_enabled;

		echo '<h4>' . __( 'Track modifications on these post types:', 'wp_mod_history' ) . '</h4>';
		echo '<form method="post">';
		wp_nonce_field( 'wp_mod_history_settings', 'wp_mod_history_settings' );
		if ( ! empty( $post_types ) ) {
			echo '<ul style="padding-left:1em;">';
			foreach ( $post_types as $post_type ) {
				echo '<li><label><input type="checkbox" name="wp_mod_history_post_types[' . esc_attr( $post_type ) . ']"' . checked( in_array( $post_type, $this->post_types_enabled ), true, false ) . ' style="margin-top:0">' . $post_type . '</label></li>';
			}
			echo '</ul>';
		}
		echo '<input type="submit" value="Save Settings" class="button button-primary">';
		echo '</form>';
	}

	private function process_post() {
		if ( ! empty( $_POST ) && isset( $_POST['wp_mod_history_settings'] ) && wp_verify_nonce( $_POST['wp_mod_history_settings'], 'wp_mod_history_settings' ) ) {
			if ( isset( $_POST['wp_mod_history_post_types'] ) && is_array( $_POST['wp_mod_history_post_types'] ) ) {
				// Update/add plugin options
				$options = array();
				$post_types = array_keys( $_POST['wp_mod_history_post_types'] );
				$options['post_types'] = $post_types;
				update_option( 'wp_mod_history_options', $options );
			}
		}
	}

}

new AdminSettings;
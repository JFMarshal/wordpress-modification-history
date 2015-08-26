<?php
namespace robido;
if ( ! defined( 'ABSPATH' ) ) exit;

class AdminSettings extends ModHistory {
	
	private $post_types_blacklist = array( 'attachment', 'revision', 'nav_menu_item' );
	private $post_types_enabled = array( 'post', 'page' );
	private $date_format = 'n/j/y';
	private $time_format = 'g:ia';
	private $settings = array();
	private $options = array();

	function __construct( $args = false ) {
		// Run parent construct
		parent::__construct( $args );

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
	 * Render the settings page to store options
	 */
	function settings_page() {
		$this->process_post();
		echo '<h1>Modification History Settings</h1><hr>';
		echo '<form method="post">';

		// Apply wp_mods_post_types_blacklist filter to not display certain post types for tracking (return an empty array on this filter to enable these)
		$post_types = get_post_types();
		$post_types = array_diff( $post_types, apply_filters( 'wp_mods_post_types_blacklist', $this->post_types_blacklist ) );

		// Get post types that are enabled for modification tracking
		$this->options = get_option( 'wp_mod_history_options' );
		$this->post_types_enabled = $this->options ? $this->options['post_types'] : $this->post_types_enabled;
		$this->settings = $this->options ? $this->options['settings'] : $this->settings;

		// General settings
		echo '<h3>' . __( 'General settings:', 'wp_mod_history' ) . '</h3>';
		echo '<ul style="padding-left:1em;">';
			echo '<li><label><input type="checkbox" name="wp_mod_history_settings[unchanged]"' . checked( isset( $this->settings['unchanged'] ), true, false ) . ' style="margin-top:0">Show <i>Updated with no modifications</i> in modification history.</label></li>';
		echo '</ul>';

		// Post types
		echo '<h3>' . __( 'Track modifications on these post types:', 'wp_mod_history' ) . '</h3>';
		wp_nonce_field( 'wp_mod_history_options', 'wp_mod_history_options' );
		if ( ! empty( $post_types ) ) {
			echo '<ul style="padding-left:1em;">';
			foreach ( $post_types as $post_type ) {
				echo '<li><label><input type="checkbox" name="wp_mod_history_post_types[' . esc_attr( $post_type ) . ']"' . checked( in_array( $post_type, $this->post_types_enabled ), true, false ) . ' style="margin-top:0">' . $post_type . '</label></li>';
			}
			echo '</ul>';
		}

		// Localization settings
		echo '<h3>' . __( 'Localization settings:', 'wp_mod_history' ) . '</h3>';
		echo '<table style="padding-left:1em;">';
			echo '<tr><td valign="top" style="padding-top:5px;"><label for="wp_mod_history_settings[date_format]">Date format:</label></td><td><input type="text" style="width:100%;" name="wp_mod_history_settings[date_format]" value="' . ( isset( $this->settings['date_format'] ) ? esc_attr( $this->settings['date_format'] ) : $this->date_format ) . '" style="margin-top:0"><br>How to display the modification date - <em>(see <a href="http://php.net/manual/en/function.date.php" target="_blank">PHP <strong>date()</strong> format</a>)</em></td></tr>';
			echo '<tr><td valign="top" style="padding-top:5px;"><label for="wp_mod_history_settings[time_format]">Time format:</label></td><td><input type="text" style="width:100%;" name="wp_mod_history_settings[time_format]" value="' . ( isset( $this->settings['time_format'] ) ? esc_attr( $this->settings['time_format'] ) : $this->time_format ) . '" style="margin-top:0"><br>How to display the modification time - <em>(see <a href="http://php.net/manual/en/function.date.php" target="_blank">PHP <strong>date()</strong> format</a>)</em></td></tr>';
		echo '</table>';

		// Submit button
		echo '<input type="submit" value="Save Settings" class="button button-primary">';
		echo '</form>';
	}

	private function process_post() {
		if ( ! empty( $_POST ) && isset( $_POST['wp_mod_history_options'] ) && wp_verify_nonce( $_POST['wp_mod_history_options'], 'wp_mod_history_options' ) ) {
			// Update/add plugin options
			$options = array(
				'post_types'	=> array_keys( $_POST['wp_mod_history_post_types'] ),
				'settings'		=> $_POST['wp_mod_history_settings'],
			);
			update_option( 'wp_mod_history_options', $options );
		}
	}

}

new AdminSettings;
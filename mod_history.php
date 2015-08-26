<?php
namespace robido;
if ( ! defined( 'ABSPATH' ) ) exit;

class ModHistory {

	public $table = 'modifications';
	private $mods = false;
	private $options = array();
	private $settings = array();
	private $post_types = array();

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_action( 'add_meta_boxes', array( $this, 'metaboxes' ) );
		add_action( 'pre_post_update', array( $this, 'history_save' ) );
		add_action( 'post_updated', array( $this, 'modifications_saved' ) );
		add_action( 'wp_insert_post', array( $this, 'postmeta_modifications_saved' ), 99999 );
		$this->options = get_option( 'wp_mod_history_options' );
		$this->settings = isset( $this->options['settings'] ) ? $this->options['settings'] : array();
		$this->post_types = isset( $this->options['post_types'] ) ? $this->options['post_types'] : array();
	}

	/**
	 * Install routine
	 */
	function install() {
		wp_die('temp install test ;o');
		// Create custom modification history table
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}{$this->table}` (
					`ID` int(11) NOT NULL AUTO_INCREMENT,
					`post_id` int(11) NOT NULL,
					`user_id` int(11) NOT NULL,
					`posts_before` mediumtext NOT NULL,
					`postmeta_before` mediumtext NOT NULL,
					`posts_after` mediumtext NOT NULL,
					`postmeta_after` mediumtext NOT NULL,
					`modified` datetime NOT NULL,
					PRIMARY KEY (`ID`)
				) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Diff an array of postmeta values
	 *
	 * @param 	(array)	First array to diff
	 * @param 	(array)	Second array to diff
	 * @return 	(array)	Keys and values that have differences between First and Second array params
	 */
	function array_diff_meta( $array1, $array2 ) {
		if ( ! is_array( $array1 ) || ! is_array( $array2 ) ) return false;
		$return = array();
		foreach ( $array1 as $key => $val ) {
			if ( isset( $array2[ $key ] ) && is_array( $array2[ $key ] ) && is_array( $val ) ) {
				if ( $array2[ $key ][0] != $val[0] ) $return[ $key ] = $val[0];
			}
		}
		return $return;
	}

	/**
	 * Add modification history metabox on post edit screen(s)
	 */
	function metaboxes() {
		if ( $this->post_types_check() ) {
			add_meta_box( 'wp_modification_history', __( 'Modification History', 'wp-mod-history' ), array( $this, 'wp_modification_history' ), null );
		}
	}

	/**
	 * Modification history metabox HTML
	 */
	function wp_modification_history() {
		global $post, $wpdb;

		// Nonce
		wp_nonce_field( 'modification_history', 'modification_history' );

		// Get our modifications
		$mods = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}{$this->table} WHERE `post_id` = " . absint( $post->ID ) . " ORDER BY `modified`" );
		if ( ! empty( $mods ) ) {
		?>
		<table>
			<thead>
				<tr style="text-align:left;">
					<th>User</th>
					<th colspan="2">Date</th>
					<th>Modifications</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$last_user = '';
					$last_time = false;
					foreach ( $mods as $mod ) {
						// Set the user that made this modification
						$user = get_user_by( 'id', $mod->user_id );
						$user = $user ? str_replace( ' ', '&nbsp;', $user->data->display_name ) : '';

						// Build modifications arrays for posts table
						$posts_modified = array(
							'before'	=> (array) unserialize( $mod->posts_before ),
							'after'		=> (array) unserialize( $mod->posts_after ),
						);
						unset( $posts_modified['after']['post_modified'] );
						unset( $posts_modified['after']['post_modified_gmt'] );

						// Build modifications arrays for postmeta table
						$postmeta_modified = array(
							'before'	=> (array) unserialize( $mod->postmeta_before ),
							'after'		=> (array) unserialize( $mod->postmeta_after ),
						);
						unset( $postmeta_modified['after']['post_modified'] );
						unset( $postmeta_modified['after']['post_modified_gmt'] );

						// Build array of differences for each table modifications array
						$posts_mods = array_diff_assoc( $posts_modified['after'], $posts_modified['before'] );
						$postmeta_mods = array_diff_assoc( $postmeta_modified['after'], $postmeta_modified['before'] );

						if ( isset( $this->settings['unchanged'] ) && empty( $posts_mods ) && empty( $postmeta_mods ) ) {

							// Updated with no modifications
							echo '<tr>';
								echo '<td style="vertical-align:top;">' . $user . '</td>';
								echo '<td style="vertical-align:top;">' . date( $this->settings['date_format'], strtotime( $mod->modified ) ) . '</td>';
								echo '<td style="vertical-align:top;">';
									echo '<em>Updated with no modifications</em>';
								echo '</td>';
							echo '</tr>';

						} else if ( ! empty( $posts_mods ) || ! empty( $postmeta_mods ) ) {

							// Modifications were made
							echo '<tr>';
								echo '<td style="vertical-align:top;">' . $user . '</td>';
								echo '<td style="vertical-align:top;">' . date( $this->settings['date_format'], strtotime( $mod->modified ) ) . '</td>';
								echo '<td style="vertical-align:top;">';
								if ( ! empty( $posts_mods ) ) {

									// Optionally display the time if it's different than the last diff
									if ( date( $this->settings['time_format'], strtotime( $mod->modified ) ) != $last_time ) {
										$last_time = date( $this->settings['time_format'], strtotime( $mod->modified ) );
										echo $last_time;
									}
									echo '</td><td style="vertical-align:top;width:100%;">';

									// Display each post modification
									foreach ( $posts_mods as $key => $postmod ) {
										echo '<h4 style="margin:0;padding:0 6px;background:#eee;box-shadow:0 1px 3px 0px rgba(50, 50, 50, 0.25);cursor:pointer;position:relative;" class="togglenext">' . $key . '<div style="color:#aaa;position:absolute;right:0;" class="dashicons dashicons-arrow-down"></div></h4>';
										echo '<div style="display:none;">' . wp_text_diff( $posts_modified['before'][ $key ], $postmod ) . '</div>';
									}

								} else if ( date( $this->settings['time_format'], strtotime( $mod->modified ) ) != $last_time ) {
									$last_time = date( $this->settings['time_format'], strtotime( $mod->modified ) );
									echo $last_time . '</td><td style="vertical-align:top;width:100%;">';
								}
								if ( ! empty( $postmeta_mods ) ) {
									foreach ( $postmeta_mods as $key => $metamod ) {
										echo '<h4 style="margin:0;padding:0 6px;background:#eee;box-shadow:0 1px 3px 0px rgba(50, 50, 50, 0.25);cursor:pointer;position:relative;" class="togglenext">' . $key . '<div style="color:#aaa;position:absolute;right:0;" class="dashicons dashicons-arrow-down"></div></h4>';
										if ( ! isset( $post_modified['before'][ $key ] ) ) {
											echo '<div style="display:none;">Set value to <strong>' . $metamod[0] . '</strong></div>';
										} else {
											$diff = wp_text_diff( $postmeta_modified['before'][ $key ], $metamod );
											echo '<div style="display:none;">' . wp_text_diff( $postmeta_modified['before'][ $key ], $metamod ) . '</div>';
										}
									}
								} else {
									$diff = $this->array_diff_meta( $postmeta_modified['after'], $postmeta_modified['before'] );
									if ( ! empty( $diff ) ) {
										foreach ( $diff as $key => $change ) {
											echo '<h4 style="margin:0;padding:0 6px;background:#eee;box-shadow:0 1px 3px 0px rgba(50, 50, 50, 0.25);cursor:pointer;" class="togglenext">' . $key . '<div style="float:right;color:#aaa;clear:right;" class="dashicons dashicons-arrow-down"></div></h4>';
											echo '<div style="display:none;">' . wp_text_diff( $postmeta_modified['before'][$key][0], $change ) . '</div>';
										}
									}
								}
								echo '</td>';
							echo '</tr>';

						}
						$last_user = $user;
					}
				?>
			</tbody>
		</table>
		<?php
		} else {
			echo '<h2>No modification history is available for this post.</h2>';
		}
		?>

		<script>
			jQuery(document).ready(function($) {
				$('h4.togglenext').on('click', function() {
					$(this).next().stop().slideToggle();
					if ( $(this).children('.dashicons-arrow-down').length ) {
						$(this).children('.dashicons-arrow-down').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
					} else {
						$(this).children('.dashicons-arrow-up').removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
					}
				});
			});
		</script>

		<?php
	}


	/**
	 * Before post save hook to track modification history
	 *
	 * @param 	(integer) 	$post_id of the post being saved
	 */
	function history_save( $post_id ) {

		// Check if we should track these modifications
		if ( ! $this->post_types_check() ) return;

		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'modification_history' ] ) && wp_verify_nonce( $_POST[ 'modification_history' ], 'modification_history' ) ) ? true : false;
	 
		// Exits script depending on save status
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		// Get current data from posts table
		$this->mods = array(
			'before'	=> array(
				'posts' 	=> get_post( $post_id ),
				'postmeta' 	=> get_post_meta( $post_id ),
			),
		);

		// Unset values we don't want to track as changes
		unset( $this->mods['before']['postmeta']['_encloseme'] );
		unset( $this->mods['before']['postmeta']['_pingme'] );

	}

	/**
	 * After post save hook to track modification history
	 *
	 * @param 	(integer) 	$post_id of the post being saved
	 */
	function modifications_saved( $post_id ) {

		// Check if we should track these modifications
		if ( ! $this->post_types_check() ) return;

		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'modification_history' ] ) && wp_verify_nonce( $_POST[ 'modification_history' ], 'modification_history' ) ) ? true : false;
	 
		// Exits script depending on save status
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		// Update the modification history
		$this->mods['after'] = array(
			'posts' 	=> get_post( $post_id )
		);

	}

	/**
	 * After postmeta save hook to track modification history (and insert into DB when differences are found)
	 *
	 * @param 	(integer) 	$post_id of the post being saved
	 */
	function postmeta_modifications_saved( $post_id ) {

		// Check if we should track these modifications
		if ( ! $this->post_types_check() ) return;

		global $wpdb, $current_user;
		get_currentuserinfo();

		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'modification_history' ] ) && wp_verify_nonce( $_POST[ 'modification_history' ], 'modification_history' ) ) ? true : false;
	 
		// Exits script depending on save status
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		// Update the modification history
		$this->mods['after']['posts'] = get_post( $post_id );
		$this->mods['after']['postmeta'] = get_post_meta( $post_id );

		// Unset values we don't want to track as changes
		unset( $this->mods['after']['postmeta']['_encloseme'] );
		unset( $this->mods['after']['postmeta']['_pingme'] );

		// Insert modifications into modification table
		$diff_posts = array_diff_assoc( (array) $this->mods['after']['posts'], (array) $this->mods['before']['posts'] );
		$diff_postmeta = array_diff_assoc( $this->mods['after']['postmeta'], $this->mods['before']['postmeta'] );
		if ( ! empty( $diff_posts ) || ! empty( $diff_postmeta ) ) {
			$wpdb->insert(
				$wpdb->prefix . $this->table,
				array(
					'post_id'			=> $post_id,
					'user_id'			=> $current_user->ID,
					'posts_before'		=> serialize( $this->mods['before']['posts'] ),
					'postmeta_before'	=> serialize( $this->mods['before']['postmeta'] ),
					'posts_after'		=> serialize( $this->mods['after']['posts'] ),
					'postmeta_after'	=> serialize( $this->mods['after']['postmeta'] ),
					'modified'			=> current_time( 'Y-m-d H:i:s' ),
				),
				array(
					'%d',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
		}

	}

	/**
	 * Check to see if the current post type is in the options of post types to track modification history
	 *
	 * @return 	(bool) 	True if current post type is trackable, False if not
	 */
	private function post_types_check() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && in_array( $screen->post_type, $this->options['post_types'] ) ) {
			return true;
		}
		return false;
	}

}

new ModHistory;
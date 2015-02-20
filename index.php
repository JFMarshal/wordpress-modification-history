<?php
namespace ModHistory;

/**
 * Plugin Name:	WP Modification History
 * Plugin URI:	http://robido.com/wp-mod-history
 * Description:	A simple plugin to enable a metabox on particular post types that shows you a history of modifications made to a post by user and time/date
 * Version:		1.0.0
 * Author:		Jeff Hays (jphase)
 * Author URI:	https://profiles.wordpress.org/jphase/
 * Text Domain:	wp-mod-history
 * License:		GPL2
 */

class Main {

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}

	function install() {
		wp_die( 'yep!' );
	}

}
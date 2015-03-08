<?php
/**
 * Plugin Name:	WP Modification History
 * Plugin URI:	http://robido.com/wp-mod-history
 * Description:	A simple plugin to enable a metabox on particular post types that shows you a history of modifications 
 *				made to a post and (post meta for that post) displayed by user, date, and time.
 * Version:		1.0.0
 * Author:		Jeff Hays (jphase)
 * Author URI:	https://profiles.wordpress.org/jphase/
 * Text Domain:	wp-mod-history
 * License:		GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( __FILE__ ) . '/mod_history.php' );
require_once( dirname( __FILE__ ) . '/admin_settings.php' );
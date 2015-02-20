<?php
namespace ModHistory;

class Main {

	public function __construct() {
		register_activation_hook( __FILE__, array( 'Main', 'install' ) );
	}

	function install() {
		wp_die( 'yep!' );
	}

}
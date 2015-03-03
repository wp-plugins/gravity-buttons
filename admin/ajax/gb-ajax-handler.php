<?php

/*
* gb_ajax_handler
*
* @description: handler for all admin ajax calls
*
*/

class gb_ajax_handler{
	
	function __construct(){
		add_action('wp_ajax_gb_admin_ajax', array($this, 'process_admin_ajax') );
		add_action('wp_ajax_nopriv_gb_public_ajax', array($this, 'process_nopriv_ajax') );
	}
	
	function process_nopriv_ajax(){
		
		//security check using wp nonce param http://codex.wordpress.org/WordPress_Nonces
		if( !check_ajax_referer('gb_public_nonce', 'gb_public_nonce', false) ){
			die('security test failed');
		}
		
		switch($_REQUEST['endpoint']){
			//No requests yet
		}
		
		die();
	}
	
	function process_admin_ajax(){
		//security check using wp nonce param http://codex.wordpress.org/WordPress_Nonces
		if( !check_ajax_referer('gb_admin_nonce', 'gb_admin_nonce', false) || !current_user_can('manage_options') ){
			die('security test failed');
		}
		
		switch($_REQUEST['endpoint']){
			case 'update_gb_settings':
				echo gb_settings::save_settings($_REQUEST['settings'], 'json');
			break;
			case 'destroy_plugin_data':
				echo gb_settings::destroy_plugin_data();
			break;
			case 'complete_setup':
				echo gb_setup::complete_setup($_REQUEST['email'], $_REQUEST['subscribed'], 'json');
			break;
			
		}

		die();
	}
}

new gb_ajax_handler();

?>
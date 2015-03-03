<?php

/*
* gb_settings
*
* @description: conroller for gravity buttons settings sub menu page
*
*/

class gb_settings{
	
	var $action;
	
	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
	}
	
	function admin_menu(){
		$page = add_options_page(
			'Settings Admin',
			'Gravity Buttons',
			'manage_options', 'gb-admin',
			array( $this, 'html' )
    );
	}
	
	function subscribe_to_email_list($email_address){
		update_option('gb_subscribed', true);
		
		/*Subscribe via Mailchimp*/
		
		return true;
	}
	
	/*Save the user's gravity buttons settings from the settings page*/
	static function save_settings($settings, $format = 'php'){
		
		update_option('gb_email', $settings['email']);
		update_option('gb_subscribed', $settings['subscribed']);
		
		$result = true;
		
		if($format == 'json'){
			return json_encode($result);
		}
		else{
			return $result;
		}
		
	}
	
	static function destroy_plugin_data(){
		gb()->deactivateAndDestroyGBData();
		
		return;
	}
	
	//echo out the settings view (html file) file when loading the bars admin page
	function html(){
		readfile(GB_PLUGIN_PATH . 'admin/views/settings.html');
		
		//enqueue scripts for this view
		$this->enqueue_scripts_for_view();
		
	}
	
	function enqueue_scripts_for_view(){
		
		wp_enqueue_script('gb-settings', GB_PLUGIN_URL . 'admin/js/settings.js', array('jquery', 'knockout', 'underscore'), microtime(), true);
		wp_localize_script('gb-settings', 'GB_GLOBALS', array( 'GB_ADMIN_NONCE' => wp_create_nonce('gb_admin_nonce') ));
		
		wp_localize_script('gb-settings', 'gb_settings', array(
			'email' => get_option('gb_email'),
			'subscribed' => get_option('gb_subscribed'),
			'fname' => wp_get_current_user()->user_firstname,
			'website' => get_site_url()
		) );
		
	}
}

new gb_settings();

?>
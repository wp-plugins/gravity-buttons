<?php
/*
Plugin Name: Gravity Buttons
Plugin URI: http://gravitybuttons.com
Description: Gravity Buttons is a button creator for WordPress that allows anyone to create beautiful buttons anywhere on their site.
Version: 1.0.0
Author: Phil Baylog
Author URI: http://gravitybuttons.com
License: GPLv2
*/

//Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

define( 'GB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

global $GB_VERSION;
$GB_VERSION = '1.0.0';

class GravityButtons{

	private static $instance;

	private function __construct(){

		$this->include_before_plugin_loaded();
		add_action('plugins_loaded', array($this, 'include_after_plugin_loaded'));
	}

	/** Singleton Instance Implementation **********/
	public static function instance(){
		if ( !isset( self::$instance ) ){
			self::$instance = new GravityButtons();
			self::$instance->init();
			//self::$instance->load_textdomain();
		}
		return self::$instance;
	}

	//called before the 'plugins_loaded action is fired
	function include_before_plugin_loaded(){
		global $wpdb;
	}
	
	function add_gb_tiny_mce_button($buttons){
		array_push($buttons, 'gravitybuttons');
		
		return $buttons;
	}
	function add_gb_tiny_mce_js($plugin_array){
		$plugin_array['gravitybuttons'] = plugins_url( '/admin/js/tinymce.gravitybuttons-plugin.js',__file__);
	  
		return $plugin_array;
	}
	function add_gb_tiny_mce_css($mce_css){
		if(!empty($mce_css)){
			$mce_css .= ',';
		}
		
		$mce_css .= GB_PLUGIN_URL . 'public/style/gb-button.css';//gb button styles (includes open sans google font)
		$mce_css .= ',' . '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css';//fontawesome
		
		return $mce_css;
	}
	
	function render_gb_modal(){
		readfile(GB_PLUGIN_PATH . 'admin/views/gb-modal.html');
	}

	//called after the 'plugins_loaded action is fired
	function include_after_plugin_loaded(){
		
		global $GB_VERSION;
		
		//update database or options if plugin version updated
		if(get_option('GB_VERSION') != $GB_VERSION){
			gb()->initializeGBOptions();
			gb()->initializeGBDB();
			
			update_option('GB_VERSION', $GB_VERSION);
		}

		//admin only includes
		if( is_admin() ){
			
			/*If setup is not complete, render the setup page. Otherwise, render the settings page*/
			if(false && !get_option('gb_setup_complete')){//TODO remove "FALSE"
				include_once( GB_PLUGIN_PATH . 'admin/controllers/setup.php');
			}
			else{
				include_once( GB_PLUGIN_PATH . 'admin/controllers/settings.php');
			}
			
			//Add tiny mce button filters (one for button and one for JS)
			add_filter('mce_buttons', array( $this, 'add_gb_tiny_mce_button' ) );
			add_filter('mce_external_plugins', array( $this, 'add_gb_tiny_mce_js' ) );
			add_filter('mce_css', array( $this, 'add_gb_tiny_mce_css' ) );

			//include ajax handler for processing ajax calls made from admin pages
			include_once( GB_PLUGIN_PATH . 'admin/ajax/gb-ajax-handler.php');
			
			//TODO check if edit post / edit page & only include if on one of those pages
			add_action('admin_footer', array( $this, 'render_gb_modal') );
		}

		add_action( 'wp_print_scripts',					array( $this, 'print_scripts'		) );
		add_action( 'admin_print_scripts',			array( $this, 'print_scripts'	) );
		add_action( 'wp_print_styles',					array( $this, 'print_styles'			) );
		add_action( 'admin_print_styles',				array( $this, 'print_styles'	) );

	}

	private function init(){

	}
	
	static function initializeGBOptions(){
		
		if(!get_option('gb_setup_complete')){
			update_option('gb_setup_complete', false);
		}
		if(!get_option('gb_email')){
			update_option('gb_email', '');
		}
		if(!get_option('gb_subscribed')){
			update_option('gb_subscribed', false);
		}
		
	}
	
	static function initializeGBDB(){
		
		return;
		
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();

		//No sql here
		$sql = '';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($sql);
		
		add_option('gb_db_version', '1.0.0');
	}
	
	static function destroyGBOptions(){
		return;
		delete_option('GB_VERSION');
		delete_option('gb_setup_complete');
		delete_option('gb_email');
		delete_option('gb_subscribed');
	}
	
	static function destroyGBDB(){
		
		return;
		
		global $wpdb;
		
		//$sql = "DROP TABLE IF EXISTS " . $wpdb->gb_bars . ", " . $wpdb->gb_views . ", " . $wpdb->gb_conversions . ";";
		
		//$wpdb->query($sql);
	}

	static function activate(){
		
		
	}

	static function deactivate(){
		//This should be done every time plugin is deactivated
	}
	
	/*Delete all gb options, bars, and conversion data, and deactivate the plugin*/
	static function deactivateAndDestroyGBData(){
		return;
		global $GB_VERSION;
		
		gb()->destroyGBOptions();
		gb()->destroyGBDB();
		
		//if plugin is in default folder name
		if(is_plugin_active('gravity-buttons/gravity-buttons.php')){
			deactivate_plugins('gravity-buttons/gravity-buttons.php');
		}
		
		//if plugin is in '-plugin' folder name
		if(is_plugin_active('gravity-buttons-plugin/gravity-buttons.php')){
			deactivate_plugins('gravity-buttons-plugin/gravity-buttons.php');
		}
		
		//if plugin is in versioned folder name
		if(is_plugin_active('gravity-buttons-' . $GB_VERSION . '/gravity-buttons.php')){
			deactivate_plugins('gravity-buttons-' . $GB_VERSION . '/gravity-buttons.php');
		}
		
	}

	function print_scripts(){
		
		global $GB_VERSION;
		
		if( is_admin() ){

			wp_enqueue_script('knockout', GB_PLUGIN_URL . 'admin/js/inc/knockout-3.2.0.js', array('jquery'), '3.2.0', true);
			wp_enqueue_script('knockout-gb-utilities', GB_PLUGIN_URL . 'admin/js/inc/knockout-utilities.js', array('jquery', 'knockout'), '3.2.0', true);
			
			//COL PICK
			
			
			//GB dialog
			wp_enqueue_script('gb-modal', GB_PLUGIN_URL . 'admin/js/gb-modal.js', array( 'jquery' ), $GB_VERSION, false);
			wp_localize_script('gb-modal', 'ajaxurl', admin_url('admin-ajax.php') );
			
			//GB javascript
			//wp_enqueue_script('gravitybuttons', GB_PLUGIN_URL . 'public/js/gb.js', array( 'jquery' ), $GB_VERSION, false);
			//wp_localize_script('gravitybuttons', 'ajaxurl', admin_url('admin-ajax.php') );
			
			wp_enqueue_script('colpick', GB_PLUGIN_URL . 'admin/js/inc/colpick/js/colpick.js', array('jquery'), '0.0.0', true);
			wp_enqueue_script('tooltipster', GB_PLUGIN_URL . 'admin/js/inc/tooltipster/jquery.tooltipster.min.js', array('jquery'), '0.0.0', true);
		}

	}

	function print_styles(){
		
		global $GB_VERSION;
		
		//if admin...
		if( is_admin() ){
			
			wp_enqueue_style('gb-admin', GB_PLUGIN_URL . 'admin/style/gb.css', false, microtime(), 'all');
			
			//todo add check if editing post?
			wp_enqueue_style('colpick', GB_PLUGIN_URL . 'admin/js/inc/colpick/css/colpick.css', false, '0.0.0', 'all');
			wp_enqueue_style('tooltipster', GB_PLUGIN_URL . 'admin/js/inc/tooltipster/tooltipster.css', false, '0.0.0', 'all');
			
		}

		//always...
		
		//required fonts for GB
		wp_enqueue_style( 'fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', false, '4.3.0', 'all' );
		
		//Open Sans font already included in gb-button.css stylesheet (see next line)
		
		//public gb_button styles
		wp_enqueue_style( 'gb', GB_PLUGIN_URL . 'public/style/gb-button.css', false, $GB_VERSION, 'all');
		
	}
	
}/*end GravityButtons class*/

//The main function used to retrieve the one true GravityButtons instance (a shortcut for GravityButtons::instance())
function gb(){
	return GravityButtons::instance();
}

//initialize
gb();

//activation
if(is_admin()){
	register_activation_hook( __FILE__, array( 'GravityButtons', 'activate') );
}

//deactivation
if(is_admin()){
	register_deactivation_hook( __FILE__, array( 'GravityButtons', 'deactivate') );
}
?>

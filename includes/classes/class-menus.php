<?php
/*
* @Author 		Jaed Mosharraf
* Copyright: 	2015 Jaed Mosharraf
*/

if ( ! defined('ABSPATH')) exit;  // if direct access

class WPPN_Menus{

	public function __construct(){
		
		add_action('admin_menu', array( $this, 'wppn_menu_init' ));
	}

	public function wppn_dashboard(){
		include( WPPN_PLUGIN_DIR. 'templates/menus/dashboard.php');			
	}
	public function wppn_subscribers(){
		include( WPPN_PLUGIN_DIR. 'templates/menus/subscribers.php');			
	}

	public function wppn_send_notification(){
		include( WPPN_PLUGIN_DIR. 'templates/menus/send-notification.php');			
	}

	public function wppn_settings(){
		include( WPPN_PLUGIN_DIR. 'templates/menus/settings.php');			
	}
	
	public function wppn_menu_init() {
		add_menu_page( __('WordPress Push Notification Dashboard',WPPN_TEXTDOMAIN), __('WPPN Dashboard ',WPPN_TEXTDOMAIN), 'manage_options', 'wppn-dashboard', array( $this, 'wppn_dashboard' ), 'dashicons-controls-volumeon', 40 );
		add_submenu_page( 'wppn-dashboard', __('Subscribers',WPPN_TEXTDOMAIN), __('Subscribers',WPPN_TEXTDOMAIN), 'manage_options', 'wppn-subscribers', array( $this, 'wppn_subscribers' ) );
		add_submenu_page( 'wppn-dashboard', __('Send Notification',WPPN_TEXTDOMAIN), __('Send Notification',WPPN_TEXTDOMAIN), 'manage_options', 'wppn-send-notification', array( $this, 'wppn_send_notification' ) );
		add_submenu_page( 'wppn-dashboard', __('Settings',WPPN_TEXTDOMAIN), __('Settings',WPPN_TEXTDOMAIN), 'manage_options', 'wppn-settings', array( $this, 'wppn_settings' ) );
	}
}
	
new WPPN_Menus();
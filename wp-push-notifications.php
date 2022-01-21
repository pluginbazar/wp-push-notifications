<?php
/*
	Plugin Name: WP Push Notifications
	Plugin URI: https://www.pluginbazar.net/product/wp-push-notifications/
	Description: Sends Notifications to your users, customers, subscribers or visitors. NO LIMIT AND FREE
	Version: 1.0.6
	Author: Jaed Mosharraf
	Author URI: https://pluginbazar.net/
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

class WP_Push_notifications {
	
	private static $instance;
	public static $cache_name = '__offline-shell';
  
	public function __construct(){
	
		$this->wppn_define_constants();
		
		require_once(plugin_dir_path(__FILE__) . 'includes/sw-manager/class-wp-sw-manager.php');
		include_once(plugin_dir_path(__FILE__) . 'includes/classes/class-register-sw.php');
		include_once(plugin_dir_path(__FILE__) . 'includes/classes/class-functions.php');
		include_once(plugin_dir_path(__FILE__) . 'includes/classes/class-menus.php');
		include_once(plugin_dir_path(__FILE__) . 'includes/functions.php');
		
		register_activation_hook( __FILE__, array( $this, 'wppn_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wppn_deactivation') );
		
		WPPN_Offline_Shell_Main::init();
		$this->wppn_load_scripts();
	}
	
	public function wppn_deactivation(){
		$WPPN_Functions = new WPPN_Functions();
		$wppn_schedules = $WPPN_Functions->wppn_schedules();
		foreach( $wppn_schedules as $schedule_name => $schedule ):
			wp_clear_scheduled_hook( $schedule_name );
		endforeach;
	}
	
	public function wppn_activation(){
		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name		 = $wpdb->prefix . WPPN_DATA_TABLE;
		
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name ." (
			id int(255) NOT NULL AUTO_INCREMENT,
			s_endpoint VARCHAR(1000) NOT NULL,
			s_key VARCHAR(1000) NOT NULL,
			s_token VARCHAR(1000) NOT NULL,
			s_cat_id int(100),
			s_user_id int(100),
			s_datetime DATETIME NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		$WPPN_Functions = new WPPN_Functions();
		$wppn_schedules = $WPPN_Functions->wppn_schedules();
		foreach( $wppn_schedules as $schedule_name => $schedule ):
			if (! wp_next_scheduled ( $schedule_name ) ) {
				wp_schedule_event( time(), $schedule_name, "wppn_callback_action_$schedule_name", $schedule_name );
			}
		endforeach;
	}
	
	public function wppn_define_constants(){
		
		define('WPPN_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );
		define('WPPN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define('WPPN_TEXTDOMAIN', 'wp-push-notifications' );
		define('WPPN_DATA_TABLE', 'wppn_subscribers' );
	}
	
	public function wppn_load_scripts(){
		
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
		add_action( 'wp_enqueue_scripts', array( $this, 'wppn_front_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wppn_admin_scripts' ) );
	}
	
	public function wppn_front_scripts() {
		wp_enqueue_script('jquery'); 
		
		wp_enqueue_script('wppn_js', plugins_url( 'assets/front/scripts.js' , __FILE__ ) );
		wp_localize_script( 'wppn_js', 'wppn_ajax', array( 'wppn_ajaxurl' => admin_url( 'admin-ajax.php')));

		wp_enqueue_style('wppn_style', WPPN_PLUGIN_URL.'assets/front/css/style.css');
		wp_enqueue_style('font-awesome', WPPN_PLUGIN_URL.'assets/global/css/font-awesome.css');
	}

	public function wppn_admin_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_style( 'jquery-ui' ); 
		
		wp_enqueue_style('wppn_admin_style', WPPN_PLUGIN_URL.'assets/admin/css/style.css');
		wp_enqueue_style('font-awesome', WPPN_PLUGIN_URL.'assets/global/css/font-awesome.css');
		
		wp_enqueue_script('wppn_admin_js', plugins_url( 'assets/admin/js/scripts.js' , __FILE__ ) );
		wp_localize_script( 'wppn_admin_js', 'wppn_ajax', array( 'wppn_ajaxurl' => admin_url( 'admin-ajax.php')));
	}
	
	
	 private function set_urls() {
        $this->sw_scope = home_url('/');
    }
	
	private function setup_sw() {
        Mozilla\WP_SW_Manager::get_manager()->sw()->add_content(array($this, 'render_sw'));
    }
	
	public function render_sw() {
        $sw_scope = $this->sw_scope;
        $this->render(plugin_dir_path(__FILE__) . 'lib/js/sw.js', array(
            '$debug' => boolval($this->options->get('offline_debug_sw')),
            '$networkTimeout' => intval($this->options->get('offline_network_timeout')),
            '$resources' => $this->get_precache_list(),
            '$excludedPaths' => $this->get_excluded_paths()
        ));
    }
	
	 private function render($path, $replacements) {
        $contents = file_get_contents($path);
        $incremental_hash = hash_init('md5');
        hash_update($incremental_hash, $contents);
        foreach ($replacements as $key => $replacement) {
            $value = json_encode($replacement);
            hash_update($incremental_hash, $value);
            $contents = str_replace($key, $value, $contents);
        }
        $version = json_encode(hash_final($incremental_hash));
        $contents = str_replace('$version', $version, $contents);
        echo $contents;
    }
	
} new WP_Push_notifications();
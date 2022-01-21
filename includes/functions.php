<?php
/*
* @Author 		Jaed Mosharraf
* Copyright: 	2015 Jaed Mosharraf
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

function wppn_after_publish_schedule_function( $post_id ){
	
	$wppn_options_post_types = get_option( 'wppn_options_post_types' );
	if( empty( $wppn_options_post_types ) ) $wppn_options_post_types = array();
	
	$Published_post = get_post( $post_id );
	if( ! in_array( $Published_post->post_type, $wppn_options_post_types ) ) return;
	if( $Published_post->post_status != 'publish' ) return;
	
	wppn_send_push_notifications( array(
		'title' => $Published_post->post_title,
		'body' => mb_substr($Published_post->post_content, 0, 50),
		'icon' => get_the_post_thumbnail_url() ? get_the_post_thumbnail_url( $post_id ) : "",
		'link' => get_the_permalink() ? get_the_permalink( $post_id ) : "",
	) );
	
}
add_action( 'save_post', 'wppn_after_publish_schedule_function' );



function wppn_delete_subscriber(){
	
	$s_id	= isset( $_POST['s_id'] ) ? sanitize_text_field( $_POST['s_id'] ) : "";
		
	if( empty( $s_id ) ) {
		echo __("Something went wrong !", WPPN_TEXTDOMAIN );
		die();
	}
	
	global $wpdb;
	$del_ret = $wpdb->delete( $wpdb->prefix . WPPN_DATA_TABLE, array( 'id' => $s_id ) );

	if( $del_ret ) echo "delete_success";	
	die();
}
add_action('wp_ajax_wppn_delete_subscriber', 'wppn_delete_subscriber');
add_action('wp_ajax_nopriv_wppn_delete_subscriber', 'wppn_delete_subscriber');
	
function wppn_ajax_send_push_notifications(){
	
	$title	= isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : get_bloginfo('name');
	$body	= isset( $_POST['body'] ) ? sanitize_text_field( $_POST['body'] ) : "";
	$icon	= isset( $_POST['icon'] ) ? sanitize_text_field( $_POST['icon'] ) : "";
	$link	= isset( $_POST['link'] ) ? sanitize_text_field( $_POST['link'] ) : "";
	
	// if( empty( $title ) )
	// $title = get_option( 'wppn_notification_title' );
	// if( empty( $body ) )
	// $body = get_option( 'wppn_notification_body' );
	// if( empty( $icon ) )
	// $icon = get_option( 'wppn_notification_icon' );
	// if( empty( $link ) )
	// $link = get_option( 'wppn_notification_link' );
	
	$response = wppn_send_push_notifications( array(
		'title' => $title,
		'body' => $body,
		'icon' => $icon,
		'link' => $link,
	) );

	echo "<pre>"; print_r( $response ); echo "</pre>";
	die();
}
add_action('wp_ajax_wppn_ajax_send_push_notifications', 'wppn_ajax_send_push_notifications');
add_action('wp_ajax_nopriv_wppn_ajax_send_push_notifications', 'wppn_ajax_send_push_notifications');

function wppn_send_subscription(){
	
	$method 	= isset( $_POST['method'] ) ? sanitize_text_field( $_POST['method'] ) : "";
	$endpoint 	= isset( $_POST['endpoint'] ) ? sanitize_text_field( $_POST['endpoint'] ) : "";
	$key 		= isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : "";
	$token 		= isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : "";
	
	if( empty( $method ) || empty( $endpoint ) || empty( $key ) || empty( $token ) ) die();
	
	global $wpdb;
	$current_user 	= wp_get_current_user();
	$user_id 		= $current_user->ID;
	$gmt_offset 	= get_option('gmt_offset');	
	
		
	if( $method == "PUT" ){
		
		$subscription_count = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix . WPPN_DATA_TABLE." WHERE s_endpoint = '$endpoint'" );
		
		if( $subscription_count > 0 ) echo "already_subscribed";
		die();
		
		// $update_ret = $wpdb->update( 
			// $wpdb->prefix . WPPN_DATA_TABLE,
			// array( 
				// 's_key' => $key,
				// 's_token' => $token
			// ), 
			// array( 
				// 's_endpoint' => $endpoint
			// )
		// );
		// if( $update_ret ) echo "already_subscribed";
		// die();
	}
	
	if( $method == "DELETE" ){
		
		$del_ret = $wpdb->delete( 
			$wpdb->prefix . WPPN_DATA_TABLE,
			array( 
				's_endpoint' => $endpoint,
			) 
		);
		
		if( $del_ret ) echo "success";
		die();
	}
	
	
	if( $method == "POST" ) {
		
		$wpdb->insert(
			$wpdb->prefix . WPPN_DATA_TABLE,
			array( 
				's_endpoint'	=> $endpoint, 
				's_key' 		=> $key,
				's_token' 		=> $token,
				's_user_id' 	=> $user_id,
				's_datetime' 	=> date('Y-m-d h:i:s', strtotime('+'.$gmt_offset.' hour')),
			)
		);
		die();
	}
	
	
}
add_action('wp_ajax_wppn_send_subscription', 'wppn_send_subscription');
add_action('wp_ajax_nopriv_wppn_send_subscription', 'wppn_send_subscription');

function wppn_footer_filter_action(){
	echo "<div class='wppn_notification_bel'><i class='fa fa-bell-slash'></i></div>";
}
add_action('wp_footer', 'wppn_footer_filter_action', 10 );


function wppn_add_custom_cron_schedule_function( $schedules ) {
	
	$WPPN_Functions = new WPPN_Functions();
	$wppn_schedules = $WPPN_Functions->wppn_schedules();
	
	foreach( $wppn_schedules as $schedule_name => $schedule ):
	
		if( isset( $schedules[$schedule_name] ) ) continue;
		$schedules[$schedule_name] = array(
			'interval' => $schedule['interval'],
			'display' => $schedule['label']
		);
	
	endforeach;
		
 	return $schedules;
}
add_filter( 'cron_schedules', 'wppn_add_custom_cron_schedule_function' );

function wppn_callback_action_daily_function( $schedule_name ){
	
	$wppn_options_schedule = get_option( 'wppn_options_schedule' );
	if( empty( $wppn_options_schedule ) ) $wppn_options_schedule = array();
	
	if( ! in_array( $schedule_name, $wppn_options_schedule ) ) return;
	wppn_send_push_notifications();
}
add_action( 'wppn_callback_action_daily', 'wppn_callback_action_daily_function' );

function wppn_callback_action_weekly_function( $schedule_name ){
	
	$wppn_options_schedule = get_option( 'wppn_options_schedule' );
	if( empty( $wppn_options_schedule ) ) $wppn_options_schedule = array();
	
	if( ! in_array( $schedule_name, $wppn_options_schedule ) ) return;
	wppn_send_push_notifications();
}
add_action( 'wppn_callback_action_weekly', 'wppn_callback_action_weekly_function' );

function wppn_callback_action_biweekly_function( $schedule_name ){
	
	$wppn_options_schedule = get_option( 'wppn_options_schedule' );
	if( empty( $wppn_options_schedule ) ) $wppn_options_schedule = array();
	
	if( ! in_array( $schedule_name, $wppn_options_schedule ) ) return;
	wppn_send_push_notifications();
}
add_action( 'wppn_callback_action_biweekly', 'wppn_callback_action_biweekly_function' );

function wppn_callback_action_monthly_function( $schedule_name ){
	
	$wppn_options_schedule = get_option( 'wppn_options_schedule' );
	if( empty( $wppn_options_schedule ) ) $wppn_options_schedule = array();
	
	if( ! in_array( $schedule_name, $wppn_options_schedule ) ) return;
	wppn_send_push_notifications();
}
add_action( 'wppn_callback_action_monthly', 'wppn_callback_action_monthly_function' );

function wppn_send_push_notifications( $notification_data = array() ){
	
	$title	= isset( $notification_data['title'] ) ? $notification_data['title'] : "";
	$body	= isset( $notification_data['body'] ) ? $notification_data['body'] : "";
	$icon	= isset( $notification_data['icon'] ) ? $notification_data['icon'] : "";
	$link	= isset( $notification_data['link'] ) ? $notification_data['link'] : "";
	
	if( empty( $title ) ) $title = get_option( 'wppn_notification_title' );
	if( empty( $body ) ) $body = get_option( 'wppn_notification_body' );
	if( empty( $icon ) ) $icon = get_option( 'wppn_notification_icon' );
	if( empty( $link ) ) $link = get_option( 'wppn_notification_link' );
	
	if( empty( $title ) ) $title = get_bloginfo('name');
	if( empty( $icon ) ) $icon = WPPN_PLUGIN_URL ."assets/images/notification-icon.png";
	
	if( empty( $title ) || empty( $body ) || empty( $icon ) ){
		return __("Something went wrong !", WPPN_TEXTDOMAIN );
	}
	
	global $wpdb;
	$subscription_data 	= array();
	$TABLE_NAME			= $wpdb->prefix . WPPN_DATA_TABLE;
	$wppn_subscribers 	= $wpdb->get_results( "SELECT * FROM $TABLE_NAME ORDER BY id DESC", OBJECT );
	if( empty( $wppn_subscribers ) ) {
		return __("No Subscription found on your WordPress !", WPPN_TEXTDOMAIN );
	}
	
	foreach( $wppn_subscribers as $sub_record ){
		
		if( empty( $sub_record->s_endpoint ) || empty( $sub_record->s_key ) || empty( $sub_record->s_token ) ) continue;
		
		$subscription_data[] = array(
			'endpoint' => $sub_record->s_endpoint,
			'key' => $sub_record->s_key,
			'token' => $sub_record->s_token,
		);
	}
	
	$_POST_DATA = array(
		'title' => $title,
		'body' => $body,
		'icon' => $icon,
		'link' => $link,
		'subscriptions' => $subscription_data,
	);
	
	$curl = curl_init('https://pluginbazar.net/send-push/send_push_notification.php');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( $_POST_DATA ) );

	$response = curl_exec($curl);	
	curl_close($curl);
	
	return $response;
}
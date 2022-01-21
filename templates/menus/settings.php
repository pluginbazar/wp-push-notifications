<?php
/*
* @Author 		Jaed Mosharraf
* Copyright: 	2015 Jaed Mosharraf
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

$WPPN_Functions = new WPPN_Functions();
$wppn_schedules = $WPPN_Functions->wppn_schedules();
$post_types 	= get_post_types( array('public'=>true), 'objects' );

// $scheduled_schedules = wp_get_schedules();
// echo "<pre>"; print_r( $scheduled_schedules ); echo "</pre>";

$nonce = isset( $_POST['wppn_settings_nonce_check_value'] ) ? $_POST['wppn_settings_nonce_check_value'] : '';
if ( !empty( $nonce ) && wp_verify_nonce($nonce, 'wppn_settings_nonce_check') ) {
	if( ! empty( $_POST['wppn_hidden'] ) ) :	if( $_POST['wppn_hidden'] == 'Y' ) :
		
		if ( current_user_can('manage_options') ) {
			
			$wppn_options_post_types = isset($_POST['wppn_options_post_types']) ? $_POST['wppn_options_post_types'] : '';
			$wppn_options_schedule = isset($_POST['wppn_options_schedule']) ? $_POST['wppn_options_schedule'] : '';
			$wppn_notification_title = isset($_POST['wppn_notification_title']) ? $_POST['wppn_notification_title'] : '';
			$wppn_notification_body = isset($_POST['wppn_notification_body']) ? $_POST['wppn_notification_body'] : '';
			$wppn_notification_icon = isset($_POST['wppn_notification_icon']) ? $_POST['wppn_notification_icon'] : '';
			$wppn_notification_link = isset($_POST['wppn_notification_link']) ? $_POST['wppn_notification_link'] : '';
			
			update_option( 'wppn_options_post_types', $wppn_options_post_types );
			update_option( 'wppn_options_schedule', $wppn_options_schedule );
			update_option( 'wppn_notification_title', $wppn_notification_title );
			update_option( 'wppn_notification_body', $wppn_notification_body );
			update_option( 'wppn_notification_icon', $wppn_notification_icon );
			update_option( 'wppn_notification_link', $wppn_notification_link );
			
			printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success is-dismissible', __( 'Changes Saved!', WPPN_TEXTDOMAIN ) ); 
		}
		else {
			printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error is-dismissible', __( 'Something went wrong!', WPPN_TEXTDOMAIN ) ); 
		}
		
	endif; endif;
}

$wppn_options_post_types = get_option( 'wppn_options_post_types' );
$wppn_options_schedule = get_option( 'wppn_options_schedule' );
$wppn_notification_title = get_option( 'wppn_notification_title' );
$wppn_notification_body = get_option( 'wppn_notification_body' );
$wppn_notification_icon = get_option( 'wppn_notification_icon' );
$wppn_notification_link = get_option( 'wppn_notification_link' );

if( empty( $wppn_options_post_types ) ) $wppn_options_post_types = array();
if( empty( $wppn_options_schedule ) ) $wppn_options_schedule = array();
?>

<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2>WP Push Notifications - Settings</h2><br>
	
	<form class="wppn_option_box_container" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="wppn_hidden" value="Y" />
		<?php wp_nonce_field('wppn_settings_nonce_check', 'wppn_settings_nonce_check_value'); ?>
		<div class="wppn_option_box wppn_post_type_selector">
			<div class="wppn_box_title">After Pulish - {Post_type}</div>
			<div class="wppn_box_info">Notifications will send to the subscribers when a new post will published from your selected post types</div>
			<div class="wppn_box_input">
			<?php foreach( $post_types as $post_type ): ?>
				<?php $checked = in_array( $post_type->name, $wppn_options_post_types ) ? "checked" : ""; ?>
				<input type="checkbox" <?php echo $checked; ?> name="wppn_options_post_types[]" id="<?php echo $post_type->name; ?>" value="<?php echo $post_type->name; ?>" />
				<label for="<?php echo $post_type->name; ?>"><?php echo $post_type->label; ?> </label> 
				<br>
			<?php endforeach; ?>
			</div>
		</div>
		<br>
	
		<div class="wppn_option_box">
			<div class="wppn_box_title">Set a Schedule event</div>
			<div class="wppn_box_info">Notification will sent upon your selection from this setting</div>
			<div class="wppn_box_input">
			<?php foreach( $wppn_schedules as $schedule_name => $schedule ): ?>
				<?php $checked = in_array( $schedule_name, $wppn_options_schedule ) ? "checked" : ""; ?>
				<input type="checkbox" <?php echo $checked; ?> name="wppn_options_schedule[]" id="<?php echo $schedule_name; ?>" value="<?php echo $schedule_name; ?>" />
				<label for="<?php echo $schedule_name; ?>"><?php echo $schedule['label'] ." - ". $schedule['details']; ?> </label> 
				<br>
			<?php endforeach; ?>
			</div>
		</div>
		
		<br>
		<div class="wppn_option_box">
			<div class="wppn_box_title">Notification Data</div>
			
			<div class="wppn_box_info"><strong>Notification Title</strong><br>Default: <?php echo get_bloginfo('name'); ?></div>
			<div class="wppn_box_input">
				<input type="text" name="wppn_notification_title" value="<?php echo $wppn_notification_title; ?>" placeholder="<?php echo get_bloginfo('name'); ?>" />
			</div>
			<br>
			
			<div class="wppn_box_info"><strong>Notification Body</strong><br>Default: Hello guys, Greetings from <?php echo get_bloginfo('name'); ?></div>
			<div class="wppn_box_input">
				<textarea rows="5" cols="40" name="wppn_notification_body" placeholder="Our new post is about your..."><?php echo $wppn_notification_body; ?></textarea>
			</div>
			<br>
			
			<div class="wppn_box_info"><strong>Notification Icon</strong><br>Default: <?php echo WPPN_PLUGIN_URL; ?>assets/images/notification-icon.png</div>
			<div class="wppn_box_input">
				<input type="text" name="wppn_notification_icon" value="<?php echo $wppn_notification_icon; ?>" placeholder="https://www.mywebsite.com/my-custom-icon.png" />
			</div>
			<br>
			
			<div class="wppn_box_info"><strong>Notification Link</strong><br>Default: (empty) Optional</div>
			<div class="wppn_box_input">
				<input type="text" name="wppn_notification_link" value="<?php echo $wppn_notification_link; ?>" placeholder="https://www.mywebpage.com/new-post..." />
			</div>
			
		</div>
		
		<button class="wppn_save_settings button button-orange">Save Changes</button>
		
	</form>
	
</div>

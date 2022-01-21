<?php
/*
* @Author 		Jaed Mosharraf
* Copyright: 	2015 Jaed Mosharraf
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

?>

<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2>WP Push Notifications - Send Notification</h2><br>
	
	<div class="wppn_option_box_container">
	
		<div class="wppn_option_box">
			<div class="wppn_box_title">Notification Title</div>
			<div class="wppn_box_info"><strong>Default: </strong><?php echo get_bloginfo('name'); ?></div>
			<div class="wppn_box_input">
				<input type="text" id="wppn_notification_title" placeholder="<?php echo get_bloginfo('name'); ?>" />
			</div>
		</div>
		
		<div class="wppn_option_box">
			<div class="wppn_box_title">Notification Body</div>
			<div class="wppn_box_info"><strong>Default: </strong>Hello guys, Greetings from <?php echo get_bloginfo('name'); ?></div>
			<div class="wppn_box_input">
				<textarea rows="5" cols="40" id="wppn_notification_body" placeholder="Our new post is about your..."></textarea>
			</div>
		</div>
		
		<div class="wppn_option_box">
			<div class="wppn_box_title">Notification Icon</div>
			<div class="wppn_box_info"><strong>Default: </strong><?php echo WPPN_PLUGIN_URL; ?>assets/images/notification-icon.png</div>
			<div class="wppn_box_input">
				<input type="hidden" id="wppn_notification_icon" id="file_wppn_notification_icon" /><br>
				<input id="upload_button_wppn_notification_icon" class="upload_button_wppn_notification_icon button" type="button" value="Upload File" />
				<br /><br /><div style="overflow:hidden;max-height:150px;max-width:150px;" class="logo-preview"><img width="100%" src="" /></div><br /><br />
			</div>
		</div>
		
		<div class="wppn_option_box">
			<div class="wppn_box_title">Notification Link</div>
			<div class="wppn_box_info"><strong>Default: </strong>(empty) Optional</div>
			<div class="wppn_box_input">
				<input type="text" id="wppn_notification_link" placeholder="https://www.mywebpage.com/new-post..." />
			</div>
		</div>
		
		<br>
		<button class="wppn_send_notification button button-orange">Send Notifications</button>
		<span class="wppn_submission_message">An aJax will fired and sending the notifications</span>
			
	</div>
	
</div>

jQuery(document).ready(function($) {
	
	$(document).on('click', '.wppn_subscribers_list .s_delete', function() {
		
		s_id = $(this).attr('s_id');
		if( s_id.length == 0 ) return;
		
		$(this).html('Deleting...');
		
		$.ajax(
			{
		type: 'POST',
		context: this,
		url:wppn_ajax.wppn_ajaxurl,
		data: {
			"action" : "wppn_delete_subscriber", 
			"s_id"	 : s_id,
		},
		success: function(data) {
			
			location.reload();
		},
			});
			
			
	})
	
	$(document).on('click', '.wppn_send_notification', function() {
		
		if ( ! confirm(
			"Are you sure about this Action?"+
			"\nPress Enter or Click on Yes to Continue. Or Cancel it"+
			"\nNotification will send all the subscribers automatically"
		) ) return;
		
		wppn_notification_title = $('#wppn_notification_title').val();
		wppn_notification_body = $('#wppn_notification_body').val();
		wppn_notification_icon = $('#wppn_notification_icon').val();
		wppn_notification_link = $('#wppn_notification_link').val();

		__HTML__ = $(this).html();
		$(this).html( 'Sending... <i class="fa fa-spin fa-cog"></i>' );
		
		$.ajax(
			{
		type: 'POST',
		context: this,
		url:wppn_ajax.wppn_ajaxurl,
		data: {
			"action" : "wppn_ajax_send_push_notifications", 
			"title"	 : wppn_notification_title,
			"body"	 : wppn_notification_body, 
			"icon"	 : wppn_notification_icon, 
			"link"	 : wppn_notification_link, 
		},
		success: function(data) {
			
			$(this).html( __HTML__ );			
			console.log( data );
		},
			});
	})
	
	var custom_uploader; 
	jQuery('#upload_button_wppn_notification_icon').click(function(e) {
		e.preventDefault();
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose File',
			button: {
				text: 'Choose File'
			},
			multiple: false
		});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			attachment_url = attachment.url;
			jQuery('.logo-preview img').attr('src',attachment_url);											
			jQuery('#wppn_notification_icon').attr('value',attachment_url);											
		});
		custom_uploader.open();
	});
	
});
	 

jQuery(document).ready(function($) {
	
	const applicationServerKey = "BCmti7ScwxxVAlB7WAyxoOXtV7J8vVCXwEDIFXjKvD-ma-yJx_eHJLdADyyzzTKRGb395bSAtxlh4wuDycO3Ih4";
	let isPushEnabled = false;
	
    if (!('serviceWorker' in navigator)) {
        console.warn("Service workers are not supported by this browser");
        changePushButtonState('incompatible');
        return;
    }

    if (!('PushManager' in window)) {
        console.warn('Push notifications are not supported by this browser');
        changePushButtonState('incompatible');
        return;
    }

    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.warn('Notifications are not supported by this browser');
        changePushButtonState('incompatible');
        return;
    }

   if (Notification.permission === 'denied') {
        console.warn('Notifications are denied by the user');
        changePushButtonState('incompatible');
        return;
    }
	
	function changePushButtonState (state) {
		
		$('.wppn_notification_bel').removeClass('wppn_push_enabled');
		$('.wppn_notification_bel').removeClass('wppn_push_disabled');
		$('.wppn_notification_bel').removeClass('wppn_push_computing');
		$('.wppn_notification_bel').removeClass('wppn_push_incompatible');
		
		$('.wppn_notification_bel i').removeClass('fa-spin fa-cog fa-bell-slash fa-bell');
		
        switch (state) {
            case 'enabled':
                $('.wppn_notification_bel').addClass('wppn_push_enabled');
				$('.wppn_notification_bel i').addClass('fa-bell');
                isPushEnabled = true;
                break;
            case 'disabled':
                $('.wppn_notification_bel').addClass('wppn_push_disabled');
				$('.wppn_notification_bel i').addClass('fa-bell-slash');
                isPushEnabled = false;
                break;
            case 'computing':
                $('.wppn_notification_bel').addClass('wppn_push_computing');
                $('.wppn_notification_bel i').addClass('fa-spin fa-cog');
                break;
            case 'incompatible':
                $('.wppn_notification_bel').addClass('wppn_push_incompatible');
				$('.wppn_notification_bel i').addClass('fa-bell-slash');
                break;
            default:
                console.error('Unhandled push button state', state);
                break;
        }
    }
	
	
	$(document).on('click', '.wppn_notification_bel', function() {
		
		if (isPushEnabled) {
			wppn_push_unsubscribe();
        } else {
			wppn_push_subscribe();
        }
	})
	
	wppn_push_update_subscription();
	
	function wppn_push_update_subscription(){

		navigator.serviceWorker.ready.then(swRegistration => swRegistration.pushManager.getSubscription())
		.then(subscription => {
			changePushButtonState('disabled');
			if (!subscription) {
				console.log( "No subscription !" );
				return;
			}
			return push_sendSubscriptionToServer(subscription, 'PUT');
		})
		.then(subscription => subscription && changePushButtonState('enabled'))
			.catch(e => {
			console.error('Error when updating the subscription', e);
		});
		
		console.log( navigator.serviceWorker );
		
	}
	
	
	function wppn_push_subscribe(){
		
		changePushButtonState('computing');
		
		navigator.serviceWorker.ready.then(function(swRegistration) {
			navigator.serviceWorker.ready
			.then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.subscribe({
				userVisibleOnly: true,
				applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
			}))
			.then(subscription => {
				changePushButtonState('enabled');
				return push_sendSubscriptionToServer(subscription, 'POST');
			})
			.catch(e => {
				if (Notification.permission === 'denied') {
					console.warn('Notifications are denied by the user.');
				} else {
					console.error('Impossible to subscribe to push notifications', e);
				}
			});
			
		});
	}
	
	function wppn_push_unsubscribe(){
		
		changePushButtonState('computing');
        navigator.serviceWorker.ready
        .then(swRegistration => swRegistration.pushManager.getSubscription())
        .then(subscription => {
            if (!subscription) {
                changePushButtonState('disabled');
                return;
            }
            return push_sendSubscriptionToServer(subscription, 'DELETE');
        })
        .then(() => changePushButtonState('disabled'))
        .catch(e => {
            console.error('Error when unsubscribing the user', e);
            changePushButtonState('disabled');
        });
	}
	
    function push_sendSubscriptionToServer(subscription, method) {
		
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
		
		$.ajax(
			{
		type: 'POST',
		context: this,
		url:wppn_ajax.wppn_ajaxurl,
		data: {
			"action"	: "wppn_send_subscription", 
			"method"	: method,
			"endpoint"	: subscription.endpoint,
			"key"		: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            "token"		: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
		},
		success: function(data) {
			
			console.log( data );
			
			if( data == "already_subscribed" ){
				changePushButtonState('enabled');
			}
			
			if( data == "success" && method == "DELETE" ){
				subscription.unsubscribe();
				changePushButtonState('disabled');
				console.log( 'Deleted' );
			}
			
			
		}
			});		
    }
	
	
	function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

});	

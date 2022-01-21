'use strict';


self.addEventListener('install', function(event){
	
	event.waitUntil(
    caches.open('v1').then(function(cache) {
      return cache.addAll([
        '/',
        '/index.html',
        // '/sw-test/style.css',
        'scripts.js',
        // '/sw-test/image-list.js',
        // '/sw-test/star-wars-logo.jpg',
        // '/sw-test/gallery/',
        // '/sw-test/gallery/bountyHunters.jpg',
        // '/sw-test/gallery/myLittleVader.jpg',
        // '/sw-test/gallery/snowTroopers.jpg'
      ]);
    })
  );
  
});

self.addEventListener('activate', function(event){
    // console.log(event);
});

self.addEventListener('push', function (event) {
	
    if (!(self.Notification && self.Notification.permission === 'granted')) {
		console.log("Permission problem. Returning...");
        return;
    }

    const sendNotification = body => {
        
		const title = "Web Push example";
		return self.registration.showNotification(title, {
            body,
        });
    };

    if (event.data) {
        const message = event.data.text();
        event.waitUntil(sendNotification(message));
    }
});

/* 
const expectedCaches = ['static-sw'];

self.addEventListener('install', event => {

	var href 		= self.location.href;
	var arr_href 	=  href.split("/").slice(0, -3); ;
	var icon 		= arr_href.join("/") + "/fb.png";
	
	event.waitUntil( caches.open( 'static-sw' ).then( cache => cache.add( icon )) );
});


self.addEventListener('push', function(event) {
	
	console.log('[Service Worker] Push had this data: "${event.data.text()}"');

	const title = 'Pickplugins';
	const options = {
		body: 'Yay it works.',
		icon: 'images/icon.png',
		badge: 'images/badge.png'
	};

  event.waitUntil(self.registration.showNotification(title, options));
  
}); */

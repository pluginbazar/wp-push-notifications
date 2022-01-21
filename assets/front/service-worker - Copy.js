(function(self, localforage){

  // Represents the name of both storage and serviceworker
  // This shouldn't change because the SW code below is smart enough to
  // update what's needed and delete what isn't
  var storageKey = '$name';

  var wpOfflineShell = self.wpOfflineShell = {
    // A { url: hash } object which will be used to populate cache
    // A changed url object will ensure the SW is downloaded when URLs change
    urls: $urls,
    // Allowed to use console functions?
    debug: $debug,
    // Race cache-network or only cache?
    raceEnabled: $raceEnabled,
    // Instance of localForage to save urls and hashes to see if anything has changed
    storage: localforage.createInstance({ name: storageKey }),
    // Name of the cache the plugin will use
    cacheName: storageKey,
    // Method to cleanse a URL before comparing and caching
    normalizeAndAnonymize: function(request) {
      var url = new URL(request.url);
      if (url.origin !== location.origin) {
        return request.url;
      }

      url.search = '';
      url.fragment = '';
      return url;
    },
    // Detect if a URL should be cacheable and in the desired URL list
    shouldBeHandled: function(method, url) {
      return method === 'GET' && (url in this.urls);
    },
    // Adds URLs to cache and localForage if the file has changed or needs to be added
    update: function() {
      // For every URL (file) the user wants cached...
      return Promise.all(Object.keys(this.urls).map(url => {
        var hash = this.urls[url];

        // ... get its hash from storage ...
        return this.storage.getItem(url).then(value => {
          // ... and if nothing has changed, just move on to the next URL
          if(value === hash) {
            this.log('[update] Hash unchanged, doing nothing: ', url);
            return Promise.resolve();
          }

          this.log('[update] Hash changed or new URL, adding to cache "' + this.cacheName +'" : ' , url);
          // ... Add the new/updated URL and its response to cache ...
          return self.caches.open(this.cacheName).then(cache => {
            return cache.add(url).then(() => {
              // ... and once it's successful add its hash to storage
              return this.storage.setItem(url, hash);
            });
          });

        })
        .catch(e => {
          this.warn('[update] error: ', e);
        });
      }));
    },
    // Check each URL in cache and delete anything that shouldn't be there anymore
    // i.e. the user unchecked a file's box in admin
    removeOldUrls: function() {
      return caches.open(this.cacheName).then(cache =>  {
        return cache.keys().then(keys =>  {
          return Promise.all(keys.map(key => {
            if(!(key.url in this.urls)) {
              return this.removeOldUrl(cache, key);
            }
            return Promise.resolve();
          }));
        })
      })
      .catch(e => {
        this.warn('[removeOldUrls] error: ', e);
      });
    },
    // Removes one individual URL from cache and storage
    removeOldUrl: function(cache, request) {
      this.log('[removeOldUrl] Removing URL no longer desired: ', request.url);
      return cache.delete(request).then(() => {
        return this.storage.removeItem(request.url);
      });
    },
    // Install step that kicks off adding/updating URLs in cache and storage
    onInstall: function(event) {
      this.log('[install] Event triggered');
      this.log('[install] Initial cache list is: ', Object.keys(this.urls));

      event.waitUntil(Promise.all([self.skipWaiting(), this.update()]));
    },
    // Does cleanup after everything went well
    onActivate: function(event) {
      this.log('[activate] Event triggered');
      event.waitUntil(Promise.all([self.clients.claim(), this.removeOldUrls()]));
    },
    // Manages returning responses from cache or the server
    onFetch: function(event) {
      var request = event.request;

      var url = this.normalizeAndAnonymize(request);
      if (!this.shouldBeHandled(request.method, url)) {
        return;
      }

      var gotFromCache = false;
      var gotFromNetwork = false;

      var fromCache = caches.match(url)
      .then(response => {
        gotFromCache = true;
        return response;
      })
      .catch(e => {
        this.warn('[fetch] error: ', e);
      });

      var promise;
      if (this.raceEnabled) {
        var fromNetwork = fetch(request)
        .then(response => {
          gotFromNetwork = true;
          return response;
        });

        promise = Promise.race([ fromCache, fromNetwork ])
        .then(response => {
          if (gotFromCache) {
            if (response) {
              this.log('[fetch] Cache hit, returning from ServiceWorker cache: ', event.request.url);
            } else {
              this.log('[fetch] Cache miss, retrieving from server: ', event.request.url);
            }
          } else {
            this.log('[fetch] Retrieved from server: ', event.request.url);
          }

          // If we couldn't find the resource in the cache, we have to wait for the
          // network request to finish.
          return response || fromNetwork;
        });
      } else {
        promise = fromCache
        .then(response => {
          if (response) {
            this.log('[fetch] Cache hit, returning from ServiceWorker cache: ', event.request.url);
            return response;
          }

          // If we couldn't find the resource in the cache, we have to perform a
          // network request.
          this.log('[fetch] Cache miss, retrieving from server: ', event.request.url);
          return fetch(request);
        });
      }

      event.respondWith(promise);
    }
  };

  // Add debugging functions
  ['log', 'warn'].forEach(function(level) {
    wpOfflineShell[level] = function() {
      if(this.debug) {
        console[level].apply(console, arguments);
      }
    };
  });

  // Kick off the event listeners
  self.addEventListener('install', wpOfflineShell.onInstall.bind(wpOfflineShell));
  self.addEventListener('activate', wpOfflineShell.onActivate.bind(wpOfflineShell));
  self.addEventListener('fetch', wpOfflineShell.onFetch.bind(wpOfflineShell));
  
  // self.addEventListener('fetch', wpSwManager.onFetch.bind(wpSwManager));
	
	self.addEventListener('notificationclick', function(event) {
		
		if( event.notification.data.length == 0 ) return;
	
		let url = event.notification.data;
		
		event.notification.close();
		event.waitUntil(
			clients.matchAll({type: 'window'}).then( windowClients => {
				for (var i = 0; i < windowClients.length; i++) {
					var client = windowClients[i];
					if (client.url === url && 'focus' in client) {
						return client.focus();
					}
				}
				
				if (clients.openWindow) {
					return clients.openWindow(url);
				}
			})
		);
	});

	self.addEventListener('push', function (event) {
		
		if (!(self.Notification && self.Notification.permission === 'granted')) {
			console.log("Permission problem. Returning...");
			return;
		}
		
		const sendNotification = body => {
			
			console.log( notification_data );
			
			const notification_data = body.split('~');
			
			const title = notification_data[0];		
			
			// var body = typeof notification_data[0] == "undefined" || notification_data[0] == null ? 
			
			const options = {
				body: notification_data[1],
				icon: notification_data[2],
 				data: notification_data[3].length == 0 ? "" : notification_data[3]
			};
			
			return self.registration.showNotification(title,options);
		};

		if (event.data) {
			
			var message = event.data.text();
			event.waitUntil(sendNotification(message));
		} 
		
	});
	
	
	
	
	
})(self, localforage);






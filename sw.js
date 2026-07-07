const CACHE_NAME = 'savings-v2';
const STATIC_ASSETS = [
  'index.php',
  'manifest.json',
  'views/header.php',
  'views/footer.php'
];

// Install — cache static assets
self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(
        keys.filter(function(key) { return key !== CACHE_NAME; })
            .map(function(key) { return caches.delete(key); })
      );
    })
  );
  self.clients.claim();
});

// Fetch — network first, fallback to cache
self.addEventListener('fetch', function(event) {
  // Skip non-GET and API calls
  if (event.request.method !== 'GET') return;
  if (event.request.url.includes('action=')) return;

  event.respondWith(
    fetch(event.request)
      .then(function(response) {
        // Cache successful responses
        if (response.ok) {
          var clone = response.clone();
          caches.open(CACHE_NAME).then(function(cache) {
            cache.put(event.request, clone);
          });
        }
        return response;
      })
      .catch(function() {
        // Offline fallback
        return caches.match(event.request).then(function(cached) {
          return cached || caches.match('index.php');
        });
      })
  );
});

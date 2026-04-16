// Simplified Service Worker for PWA
const CACHE_NAME = 'fromcampus-job-portal-v3';
const OFFLINE_URL = '/';

// Assets to cache immediately on install
const STATIC_ASSETS = [
    '/',
    '/assets/css/tailwind.css?v=1.0.4',
    '/assets/logo/fc_logo_crop.webp',
    '/assets/logo/icon-192x192.png',
    '/assets/logo/icon-512x512.png',
    '/favicon.ico',
    '/manifest.json'
];

// Install event
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('Static assets cached successfully');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Failed to cache static assets:', error);
            })
    );
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('Service Worker activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('Claiming all clients');
            return self.clients.claim();
        })
    );
});

// Fetch event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests and external requests
    if (request.method !== 'GET' || !url.origin.includes(self.location.origin)) {
        return;
    }

    // Handle different request types
    if (request.mode === 'navigate') {
        // Network first for navigation
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then((cachedResponse) => {
                            return cachedResponse || caches.match(OFFLINE_URL);
                        });
                })
        );
    } else if (STATIC_ASSETS.some(asset => request.url.includes(asset)) || 
               request.url.includes('/assets/') || 
               request.url.includes('/favicon.ico') ||
               request.url.includes('/manifest.json')) {
        // Cache first for static assets
        event.respondWith(
            caches.match(request)
                .then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return fetch(request)
                        .then((response) => {
                            if (response.ok) {
                                const responseClone = response.clone();
                                caches.open(CACHE_NAME).then((cache) => {
                                    cache.put(request, responseClone);
                                });
                            }
                            return response;
                        });
                })
        );
    } else {
        // Network only for API calls and PHP files
        event.respondWith(fetch(request));
    }
});

// Handle skip waiting message
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

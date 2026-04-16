// Service Worker for Push Notifications and PWA Caching
const CACHE_NAME = 'fromcampus-job-portal-v2';
const OFFLINE_URL = '/';
const STATIC_CACHE = 'fromcampus-static-v1';
const DYNAMIC_CACHE = 'fromcampus-dynamic-v1';

// Assets to cache immediately on install
const STATIC_ASSETS = [
    '/',
    '/assets/css/tailwind.css?v=1.0.4',
    '/assets/logo/fc_logo_crop.webp',
    '/assets/logo/icon-192x192.png',
    '/assets/logo/icon-512x512.png',
    '/favicon.ico',
    '/manifest.json',
    '/firebase-config.js',
    '/assets/js/push-notifications.js'
];

// Import Firebase scripts for messaging
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Initialize Firebase in service worker
firebase.initializeApp({
    apiKey: "AIzaSyBkwnyJ6ffLJh41h8-CBE8shejK7lpyxOk",
    authDomain: "my-jnp-project.firebaseapp.com",
    projectId: "my-jnp-project",
    storageBucket: "my-jnp-project.firebasestorage.app",
    messagingSenderId: "535417052099",
    appId: "1:535417052099:web:512dfff48d21290b57bb76"
});

// Get Firebase Messaging instance
const messaging = firebase.messaging();

// Handle skip waiting message
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Install event
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
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
                    // Delete old caches except current ones
                    if (cacheName !== STATIC_CACHE && 
                        cacheName !== DYNAMIC_CACHE && 
                        cacheName !== CACHE_NAME) {
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

// Fetch event (for offline support and caching)
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests and external requests
    if (request.method !== 'GET' || !url.origin.includes(self.location.origin)) {
        return;
    }

    // Handle different request types with appropriate strategies
    if (request.mode === 'navigate') {
        // Network first for navigation, fallback to cache, then offline page
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache successful responses
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(DYNAMIC_CACHE).then((cache) => {
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
                                caches.open(STATIC_CACHE).then((cache) => {
                                    cache.put(request, responseClone);
                                });
                            }
                            return response;
                        });
                })
        );
    } else if (request.url.includes('/api/') || request.url.includes('.php')) {
        // Network only for API calls and PHP files (no caching)
        event.respondWith(fetch(request));
    } else {
        // Stale while revalidate for other content
        event.respondWith(
            caches.match(request)
                .then((cachedResponse) => {
                    const fetchPromise = fetch(request)
                        .then((response) => {
                            if (response.ok) {
                                const responseClone = response.clone();
                                caches.open(DYNAMIC_CACHE).then((cache) => {
                                    cache.put(request, responseClone);
                                });
                            }
                            return response;
                        });
                    return cachedResponse || fetchPromise;
                })
        );
    }
});

// Push event
self.addEventListener('push', (event) => {
    console.log('Push message received:', event);
    
    let notificationData = {
        title: 'New Job Alert!',
        body: 'A new job has been posted on FromCampus',
        icon: '/assets/logo/fc_logo_crop.webp',
        badge: '/favicon.ico',
        tag: 'job-alert',
        data: {
            url: '/',
            timestamp: Date.now()
        },
        actions: [
            {
                action: 'view',
                title: 'View Job'
            },
            {
                action: 'dismiss',
                title: 'Dismiss'
            }
        ]
    };

    if (event.data) {
        try {
            const pushData = event.data.json();
            notificationData = {
                ...notificationData,
                ...pushData,
                data: {
                    ...notificationData.data,
                    ...pushData.data
                }
            };
        } catch (e) {
            console.error('Error parsing push data:', e);
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('Notification click received:', event);
    
    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const urlToOpen = event.notification.data?.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if there's already a window open with the target URL
                for (const client of clientList) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // Otherwise, open a new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Notification close event
self.addEventListener('notificationclose', (event) => {
    console.log('Notification closed:', event);
    
    // You can track notification dismissals here
    const notificationData = event.notification.data;
    
    // Send analytics data if needed
    if (notificationData && notificationData.analyticsUrl) {
        fetch(notificationData.analyticsUrl, {
            method: 'POST',
            body: JSON.stringify({
                action: 'dismissed',
                timestamp: Date.now(),
                notificationId: notificationData.notificationId
            })
        }).catch(() => {
            // Ignore analytics errors
        });
    }
});

// Background sync for failed notifications
self.addEventListener('sync', (event) => {
    console.log('Background sync event:', event);
    
    if (event.tag === 'background-sync-notifications') {
        event.waitUntil(
            // Retry failed notifications
            retryFailedNotifications()
        );
    }
});

// Function to retry failed notifications
async function retryFailedNotifications() {
    try {
        // Get failed notifications from IndexedDB
        const failedNotifications = await getFailedNotifications();
        
        for (const notification of failedNotifications) {
            try {
                // Retry sending the notification
                await retryNotification(notification);
                // Remove from failed queue if successful
                await removeFailedNotification(notification.id);
            } catch (error) {
                console.error('Failed to retry notification:', error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// IndexedDB helpers for offline notification storage
async function getFailedNotifications() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('FromCampusNotifications', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['failedNotifications'], 'readonly');
            const store = transaction.objectStore('failedNotifications');
            const getAllRequest = store.getAll();
            
            getAllRequest.onerror = () => reject(getAllRequest.error);
            getAllRequest.onsuccess = () => resolve(getAllRequest.result);
        };
        
        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('failedNotifications')) {
                db.createObjectStore('failedNotifications', { keyPath: 'id' });
            }
        };
    });
}

// Firebase Cloud Messaging background message handler
messaging.onBackgroundMessage((payload) => {
    console.log('[Firebase] Received background message: ', payload);
    
    // Customize notification here
    const notificationTitle = payload.notification.title || 'New Job Notification';
    const notificationOptions = {
        body: payload.notification.body || 'Check out the latest job opportunities!',
        icon: '/assets/logo/fc_logo_crop.webp',
        badge: '/favicon.ico',
        tag: payload.data.tag || 'job-notification',
        data: payload.data || {},
        requireInteraction: false,
        renotify: true,
        actions: [
            {
                action: 'view',
                title: 'View Job'
            },
            {
                action: 'dismiss',
                title: 'Dismiss'
            }
        ]
    };

    // Show the notification
    self.registration.showNotification(notificationTitle, notificationOptions);
});

async function removeFailedNotification(id) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('FromCampusNotifications', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['failedNotifications'], 'readwrite');
            const store = transaction.objectStore('failedNotifications');
            const deleteRequest = store.delete(id);
            
            deleteRequest.onerror = () => reject(deleteRequest.error);
            deleteRequest.onsuccess = () => resolve();
        };
    });
}

async function retryNotification(notification) {
    // This would implement retry logic
    // For now, we'll just log it
    console.log('Retrying notification:', notification);
}

// Service Worker for Push Notifications
const CACHE_NAME = 'fromcampus-job-portal-v1';
const OFFLINE_URL = '/';

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

// Install event
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll([
                    '/',
                    '/assets/css/tailwind.css',
                    '/assets/logo/fc_logo_crop.webp',
                    '/favicon.ico'
                ]);
            })
    );
    self.skipWaiting();
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
        })
    );
    return self.clients.claim();
});

// Fetch event (for offline support)
self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
    } else {
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
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

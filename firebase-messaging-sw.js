// Firebase Messaging Service Worker
// This file handles background push notifications

importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Initialize Firebase in the service worker
firebase.initializeApp({
    apiKey: "AIzaSyBkwnyJ6ffLJh41h8-CBE8shejK7lpyxOk",
    authDomain: "my-jnp-project.firebaseapp.com",
    projectId: "my-jnp-project",
    storageBucket: "my-jnp-project.firebasestorage.app",
    messagingSenderId: "535417052099",
    appId: "1:535417052099:web:512dfff48d21290b57bb76"
});

// Retrieve Firebase Messaging object
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    
    // Customize notification here
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/assets/logo/fc_logo_crop.webp',
        badge: '/favicon.ico',
        tag: payload.data.tag || 'job-notification',
        data: payload.data,
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

// Handle notification click
self.addEventListener('notificationclick', function(event) {
    console.log('[firebase-messaging-sw.js] Notification click received.', event);
    
    event.notification.close();
    
    // Handle different action clicks
    if (event.action === 'view') {
        // Open the job page or homepage
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    } else if (event.action === 'dismiss') {
        // Just close the notification
        return;
    } else {
        // Default action - open the URL
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

// Handle notification close
self.addEventListener('notificationclose', function(event) {
    console.log('[firebase-messaging-sw.js] Notification closed.', event);
});

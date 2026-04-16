// Firebase Configuration
// Your actual Firebase config from Firebase Console
const firebaseConfig = {
    apiKey: "AIzaSyBkwnyJ6ffLJh41h8-CBE8shejK7lpyxOk",
    authDomain: "my-jnp-project.firebaseapp.com",
    projectId: "my-jnp-project",
    storageBucket: "my-jnp-project.firebasestorage.app",
    messagingSenderId: "535417052099",
    appId: "1:535417052099:web:512dfff48d21290b57bb76"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Initialize Firebase Cloud Messaging
const messaging = firebase.messaging();

// Request notification permission and get token
async function requestNotificationPermission() {
    try {
        // First, ensure service worker is registered
        let registration;
        if ('serviceWorker' in navigator) {
            try {
                // Register the Firebase messaging service worker
                registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('Firebase Service Worker registered with scope:', registration.scope);

                // Wait for service worker to be ready
                await navigator.serviceWorker.ready;
                console.log('Service Worker is ready');
            } catch (error) {
                console.log('Service Worker registration failed:', error);
                return null;
            }
        }
        
        // Then request notification permission
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            console.log('Notification permission granted.');
            
            // Now get FCM token with VAPID key for web push
            const token = await messaging.getToken({
                vapidKey: 'BFTxQwylyJKkYJ8sA3eR9x1Q4Z8-x4z8n8v4k7Q2Xl4N8oP5sT7uW9yZ0a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6' // Replace with your actual VAPID key from Firebase Console
            });
            
            console.log('FCM Token:', token);
            
            // Send token to server
            await sendTokenToServer(token);
            
            return token;
        } else {
            console.log('Unable to get permission to notify.');
            return null;
        }
    } catch (error) {
        console.error('Error getting notification permission:', error);
        return null;
    }
}

// Send FCM token to server
async function sendTokenToServer(token) {
    try {
        const response = await fetch('/api/save-fcm-token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                token: token,
                user_agent: navigator.userAgent,
                timestamp: Date.now()
            })
        });
        
        if (response.ok) {
            console.log('FCM token saved successfully');
        } else {
            console.error('Failed to save FCM token');
        }
    } catch (error) {
        console.error('Error sending token to server:', error);
    }
}

// Handle incoming messages
messaging.onMessage((payload) => {
    console.log('Message received. ', payload);
    
    // Show notification
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/assets/logo/fc_logo_crop.webp',
        badge: '/favicon.ico',
        tag: payload.data?.tag || 'job-notification',
        data: payload.data || {},
        requireInteraction: false,
        renotify: true
    };
    
    // Show the notification
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(registration => {
            registration.showNotification(notificationTitle, notificationOptions);
        });
    } else {
        new Notification(notificationTitle, notificationOptions);
    }
});

// Handle token refresh
messaging.onTokenRefresh(async () => {
    try {
        const refreshedToken = await messaging.getToken();
        console.log('Token refreshed:', refreshedToken);
        await sendTokenToServer(refreshedToken);
    } catch (error) {
        console.error('Unable to retrieve refreshed token ', error);
    }
});

// Auto-request permission when page loads
document.addEventListener('DOMContentLoaded', () => {
    if ('Notification' in window) {
        requestNotificationPermission();
    }
});

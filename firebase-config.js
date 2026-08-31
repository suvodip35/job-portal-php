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
        let registration = null;
        if ('serviceWorker' in navigator) {
            try {
                registration = await navigator.serviceWorker.getRegistration('/firebase-messaging-sw.js');
                if (!registration) {
                    registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    await navigator.serviceWorker.ready;
                }
            } catch (error) {
                console.log('Service Worker registration notice:', error);
            }
        }
        
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            const tokenOptions = {
                vapidKey: 'BOt9XnxPzEX2b8pn0-kGRNqpS1rfby1CEbV-Dc_G87H9Wp5qnd6E_nyDBTHiD_NLoXGyx4Y0RhwbxTNSI9O9dtA'
            };

            if (registration && registration.pushManager) {
                tokenOptions.serviceWorkerRegistration = registration;
            }

            const token = await messaging.getToken(tokenOptions);
            
            if (token) {
                await sendTokenToServer(token);
                return token;
            }
            return null;
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

// Handle token refresh if supported
if (typeof messaging.onTokenRefresh === 'function') {
    messaging.onTokenRefresh(async () => {
        try {
            const refreshedToken = await messaging.getToken();
            console.log('Token refreshed:', refreshedToken);
            await sendTokenToServer(refreshedToken);
        } catch (error) {
            console.error('Unable to retrieve refreshed token ', error);
        }
    });
}

// Push Notification Manager for FromCampus Job Portal
// Handles Firebase Cloud Messaging subscription and UI

class PushNotificationManager {
    constructor() {
        this.isSubscribed = false;
        this.registration = null;
        this.init();
    }

    async init() {
        // Check if notifications are supported
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            this.updateUI(false);
            return;
        }

        // Check current permission status
        const permission = Notification.permission;
        this.isSubscribed = permission === 'granted';
        this.updateUI(this.isSubscribed);

        // Setup button listeners
        this.setupEventListeners();

        // If already granted, register service worker
        if (this.isSubscribed) {
            await this.registerServiceWorker();
        }
    }

    setupEventListeners() {
        this.setupButtonListeners();
        
        // Retry if buttons not found (might be loaded later)
        if (!document.getElementById('subscribePushBtn') || !document.getElementById('mobilePushNotificationBtn')) {
            setTimeout(() => {
                this.setupButtonListeners();
            }, 1000);
        }
    }
    
    setupButtonListeners() {
        const desktopBtn = document.getElementById('subscribePushBtn');
        const mobileBtn = document.getElementById('mobilePushNotificationBtn');

        if (desktopBtn) {
            desktopBtn.addEventListener('click', () => this.handleSubscribeClick());
        }

        if (mobileBtn) {
            mobileBtn.addEventListener('click', () => this.handleSubscribeClick());
        }
    }

    async handleSubscribeClick() {
        if (this.isSubscribed) {
            // Already subscribed, show message
            this.showMessage('You are already subscribed to job alerts!');
            return;
        }

        // Request permission
        const permission = await Notification.requestPermission();

        if (permission === 'granted') {
            this.isSubscribed = true;
            this.updateUI(true);
            await this.registerServiceWorker();
            this.showMessage('Successfully subscribed to job alerts!');
        } else if (permission === 'denied') {
            this.showMessage('Please enable notifications in your browser settings to receive job alerts.', 'error');
        } else {
            this.showMessage('Notification permission was dismissed. Click again to subscribe.', 'warning');
        }
    }

    async registerServiceWorker() {
        try {
            // Register Firebase messaging service worker
            this.registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

            // Wait for service worker to be ready
            await navigator.serviceWorker.ready;

            // Initialize Firebase and get token
            await this.initializeFirebase();
        } catch (error) {
            // Service Worker registration failed
        }
    }

    async initializeFirebase() {
        try {
            // Check if Firebase is already loaded
            if (typeof firebase === 'undefined') {
                setTimeout(() => this.initializeFirebase(), 1000);
                return;
            }
            
            const messaging = firebase.messaging();

            // Get FCM token
            // VAPID Key: Copy from Firebase Console > Project Settings > Cloud Messaging > Web Push certificates > Key pair
            const token = await messaging.getToken({
                vapidKey: 'BOt9XnxPzEX2b8pn0-kGRNqpS1rfby1CEbV-Dc_G87H9Wp5qnd6E_nyDBTHiD_NLoXGyx4Y0RhwbxTNSI9O9dtA'
            });

            if (token) {
                await this.sendTokenToServer(token);
            } else {
                this.showMessage('Failed to get token. Please try again.', 'error');
            }

            // Handle token refresh
            messaging.onTokenRefresh(async () => {
                const refreshedToken = await messaging.getToken({
                    vapidKey: 'BOt9XnxPzEX2b8pn0-kGRNqpS1rfby1CEbV-Dc_G87H9Wp5qnd6E_nyDBTHiD_NLoXGyx4Y0RhwbxTNSI9O9dtA'
                });
                await this.sendTokenToServer(refreshedToken);
            });

        } catch (error) {
            // Error initializing Firebase
        }
    }

    async sendTokenToServer(token) {
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
                const data = await response.json();
            }
        } catch (error) {
            // Error sending token to server
        }
    }

    updateUI(isSubscribed) {
        const desktopBtn = document.getElementById('subscribePushBtn');
        const mobileBtn = document.getElementById('mobilePushNotificationBtn');

        if (desktopBtn) {
            if (isSubscribed) {
                desktopBtn.textContent = 'Subscribed ✓';
                desktopBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                desktopBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            } else {
                desktopBtn.textContent = 'Get Job Alerts';
                desktopBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                desktopBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }
        }

        if (mobileBtn) {
            if (isSubscribed) {
                mobileBtn.innerHTML = `
                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Subscribed
                `;
                mobileBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                mobileBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            } else {
                mobileBtn.innerHTML = `
                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                    </svg>
                    Get Alerts
                `;
                mobileBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                mobileBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }
        }
    }

    showMessage(message, type = 'success') {
        // Create toast notification
        const toast = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-green-500';

        toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-y-0`;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateY(150%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    
    showUnsupportedMessage() {
        const desktopBtn = document.getElementById('subscribePushBtn');
        const mobileBtn = document.getElementById('mobileSubscribePushBtn');

        if (desktopBtn) {
            desktopBtn.textContent = 'Alerts Not Supported';
            desktopBtn.disabled = true;
            desktopBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        if (mobileBtn) {
            mobileBtn.style.display = 'none';
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Small delay to ensure all elements are rendered
    setTimeout(() => {
        new PushNotificationManager();
    }, 100);
});

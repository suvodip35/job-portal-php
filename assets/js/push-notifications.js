// Push Notification Manager
class PushNotificationManager {
    constructor() {
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.subscription = null;
        this.isSubscribed = false;
        this.swRegistration = null;
        this.applicationServerKey = null;
        
        // VAPID public key (you should get this from your server)
        this.vapidPublicKey = 'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ';
        
        this.init();
    }

    async init() {
        if (!this.isSupported) {
            console.warn('Push notifications are not supported in this browser');
            this.showUnsupportedMessage();
            return;
        }

        try {
            // Register service worker
            this.swRegistration = await navigator.serviceWorker.register('/sw.js');
            console.log('Service Worker registered:', this.swRegistration);

            // Get existing subscription
            this.subscription = await this.swRegistration.pushManager.getSubscription();
            this.isSubscribed = !(this.subscription === null);

            // Update UI
            this.updateUI();

            // Listen for subscription changes
            navigator.serviceWorker.addEventListener('message', this.handleServiceWorkerMessage.bind(this));

        } catch (error) {
            console.error('Error during service worker registration:', error);
            this.showError('Failed to initialize push notifications');
        }
    }

    async subscribe() {
        if (!this.isSupported || this.isSubscribed) {
            return;
        }

        try {
            // Convert VAPID key
            const applicationServerKey = this.urlB64ToUint8Array(this.vapidPublicKey);

            // Subscribe to push notifications
            this.subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });

            this.isSubscribed = true;

            // Send subscription to server
            await this.sendSubscriptionToServer(this.subscription);

            // Update UI
            this.updateUI();
            
            // Show success message
            this.showSuccess('Successfully subscribed to job alerts!');

        } catch (error) {
            console.error('Error subscribing to push notifications:', error);
            this.showError('Failed to subscribe to push notifications');
        }
    }

    async unsubscribe() {
        if (!this.isSupported || !this.isSubscribed) {
            return;
        }

        try {
            // Unsubscribe from push notifications
            await this.subscription.unsubscribe();
            
            // Remove subscription from server
            await this.removeSubscriptionFromServer(this.subscription);

            this.subscription = null;
            this.isSubscribed = false;

            // Update UI
            this.updateUI();
            
            // Show success message
            this.showSuccess('Successfully unsubscribed from job alerts');

        } catch (error) {
            console.error('Error unsubscribing from push notifications:', error);
            this.showError('Failed to unsubscribe from push notifications');
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push-subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: subscription.getKey('p256dh') ? btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))) : null,
                        auth: subscription.getKey('auth') ? btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))) : null
                    },
                    userAgent: navigator.userAgent,
                    ipAddress: await this.getClientIP()
                })
            });

            if (!response.ok) {
                throw new Error(`Server responded with ${response.status}`);
            }

            const result = await response.json();
            console.log('Subscription saved on server:', result);

        } catch (error) {
            console.error('Error sending subscription to server:', error);
            throw error;
        }
    }

    async removeSubscriptionFromServer(subscription) {
        try {
            const response = await fetch('/api/push-unsubscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint
                })
            });

            if (!response.ok) {
                throw new Error(`Server responded with ${response.status}`);
            }

            console.log('Subscription removed from server');

        } catch (error) {
            console.error('Error removing subscription from server:', error);
            throw error;
        }
    }

    async getClientIP() {
        try {
            const response = await fetch('https://api.ipify.org?format=json');
            const data = await response.json();
            return data.ip;
        } catch (error) {
            console.error('Error getting client IP:', error);
            return null;
        }
    }

    updateUI() {
        const subscribeBtn = document.getElementById('subscribePushBtn');
        const mobileSubscribeBtn = document.getElementById('mobileSubscribePushBtn');
        const statusText = document.getElementById('pushStatusText');

        if (subscribeBtn) {
            if (this.isSubscribed) {
                subscribeBtn.textContent = 'Disable Job Alerts';
                subscribeBtn.className = 'px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition';
                subscribeBtn.onclick = () => this.unsubscribe();
            } else {
                subscribeBtn.textContent = 'Get Job Alerts';
                subscribeBtn.className = 'px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition';
                subscribeBtn.onclick = () => this.subscribe();
            }
        }

        if (mobileSubscribeBtn) {
            if (this.isSubscribed) {
                mobileSubscribeBtn.textContent = 'Disable Job Alerts';
                mobileSubscribeBtn.onclick = () => this.unsubscribe();
            } else {
                mobileSubscribeBtn.textContent = 'Get Job Alerts';
                mobileSubscribeBtn.onclick = () => this.subscribe();
            }
        }

        if (statusText) {
            statusText.textContent = this.isSubscribed ? 'You are subscribed to job alerts' : 'Subscribe to get instant job notifications';
        }
    }

    showUnsupportedMessage() {
        const container = document.querySelector('.max-w-6xl.mx-auto.px-4 .flex.items-center.space-x-4');
        if (container) {
            const message = document.createElement('span');
            message.className = 'text-sm text-gray-500';
            message.textContent = 'Push notifications not supported';
            container.appendChild(message);
        }
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
        
        if (type === 'success') {
            toast.className += ' bg-green-500 text-white';
        } else if (type === 'error') {
            toast.className += ' bg-red-500 text-white';
        } else {
            toast.className += ' bg-blue-500 text-white';
        }

        toast.textContent = message;
        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    handleServiceWorkerMessage(event) {
        const data = event.data;
        
        if (data.type === 'PUSH_NOTIFICATION_RECEIVED') {
            console.log('Push notification received:', data.payload);
            // You can handle custom logic here
        }
    }

    urlB64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Check notification permission
    async checkPermission() {
        if ('Notification' in window) {
            const permission = await Notification.requestPermission();
            return permission;
        }
        return 'denied';
    }

    // Request notification permission
    async requestPermission() {
        if ('Notification' in window) {
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                this.showSuccess('Notification permission granted!');
                return true;
            } else if (permission === 'denied') {
                this.showError('Notification permission denied. Please enable in browser settings.');
                return false;
            } else {
                this.showError('Notification permission was not granted.');
                return false;
            }
        }
        return false;
    }
}

// Initialize push notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Wait a bit for other scripts to load
    setTimeout(() => {
        window.pushNotificationManager = new PushNotificationManager();
    }, 1000);
});

// Handle subscription button clicks with permission check
async function handleSubscribeClick() {
    const manager = window.pushNotificationManager;
    if (!manager) return;

    const permission = await manager.checkPermission();
    
    if (permission === 'default') {
        // Permission not yet requested
        const granted = await manager.requestPermission();
        if (granted) {
            await manager.subscribe();
        }
    } else if (permission === 'granted') {
        // Permission already granted
        if (manager.isSubscribed) {
            await manager.unsubscribe();
        } else {
            await manager.subscribe();
        }
    } else {
        // Permission denied
        manager.showError('Please enable notifications in your browser settings to receive job alerts');
    }
}

// Export for global access
window.handleSubscribeClick = handleSubscribeClick;

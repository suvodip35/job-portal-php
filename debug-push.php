<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Push Notification Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .debug-log {
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Push Notification Debug</h1>
        
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-lg font-semibold mb-2">Test Button</h2>
            <button id="mobilePushNotificationBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                </svg>
                Get Alerts
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-lg font-semibold mb-2">Debug Logs</h2>
            <div id="debugLogs" class="debug-log bg-gray-900 text-green-400 p-3 rounded h-64 overflow-y-auto">
                Waiting for logs...
            </div>
            <button onclick="clearLogs()" class="mt-2 px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                Clear Logs
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-2">System Info</h2>
            <div id="systemInfo" class="text-sm">
                <p><strong>User Agent:</strong> <span id="userAgent"></span></p>
                <p><strong>Notifications Support:</strong> <span id="notificationSupport"></span></p>
                <p><strong>Service Worker Support:</strong> <span id="serviceWorkerSupport"></span></p>
                <p><strong>Current Permission:</strong> <span id="currentPermission"></span></p>
            </div>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>
    <script src="/firebase-config.js"></script>

    <script>
        // Debug logging function
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? '#ff6b6b' : type === 'success' ? '#51cf66' : '#74c0fc';
            const logElement = document.getElementById('debugLogs');
            logElement.innerHTML += `<span style="color: ${color}">[${timestamp}] ${message}</span>\n`;
            logElement.scrollTop = logElement.scrollHeight;
        }

        function clearLogs() {
            document.getElementById('debugLogs').innerHTML = 'Logs cleared...\n';
        }

        // Show system info
        document.getElementById('userAgent').textContent = navigator.userAgent;
        document.getElementById('notificationSupport').textContent = 'Notification' in window ? 'YES' : 'NO';
        document.getElementById('serviceWorkerSupport').textContent = 'serviceWorker' in navigator ? 'YES' : 'NO';
        document.getElementById('currentPermission').textContent = Notification.permission;

        log('Page loaded', 'info');
        log('Firebase config loading...', 'info');

        // Simple PushNotificationManager for testing
        class TestPushNotificationManager {
            constructor() {
                this.isSubscribed = false;
                this.init();
            }

            async init() {
                log('PushNotificationManager: Initializing...', 'info');
                
                // Check if notifications are supported
                if (!('Notification' in window) || !('serviceWorker' in navigator)) {
                    log('Push notifications not supported', 'error');
                    return;
                }
                log('Push notifications supported', 'success');

                // Check current permission
                const permission = Notification.permission;
                this.isSubscribed = permission === 'granted';
                log('Current permission: ' + permission, 'info');

                // Setup button click handler
                const btn = document.getElementById('mobilePushNotificationBtn');
                if (btn) {
                    log('Button found, attaching click handler', 'success');
                    btn.addEventListener('click', () => this.handleSubscribeClick());
                } else {
                    log('Button NOT found!', 'error');
                }
            }

            async handleSubscribeClick() {
                log('Button clicked!', 'info');
                
                if (this.isSubscribed) {
                    log('Already subscribed', 'warning');
                    return;
                }

                // Request permission
                log('Requesting notification permission...', 'info');
                const permission = await Notification.requestPermission();
                log('Permission result: ' + permission, permission === 'granted' ? 'success' : 'error');

                if (permission === 'granted') {
                    this.isSubscribed = true;
                    log('Permission granted, registering service worker...', 'info');
                    await this.registerServiceWorker();
                } else if (permission === 'denied') {
                    log('Permission denied', 'error');
                } else {
                    log('Permission dismissed', 'warning');
                }
            }

            async registerServiceWorker() {
                try {
                    log('Registering service worker...', 'info');
                    this.registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    log('Service Worker registered: ' + this.registration.scope, 'success');

                    // Wait for service worker to be ready
                    await navigator.serviceWorker.ready;
                    log('Service Worker is ready', 'success');

                    // Initialize Firebase and get token
                    await this.initializeFirebase();
                } catch (error) {
                    log('Service Worker registration failed: ' + error.message, 'error');
                }
            }

            async initializeFirebase() {
                log('Initializing Firebase...', 'info');
                try {
                    if (typeof firebase === 'undefined') {
                        log('Firebase not loaded yet, retrying...', 'warning');
                        setTimeout(() => this.initializeFirebase(), 1000);
                        return;
                    }
                    log('Firebase loaded', 'success');

                    const messaging = firebase.messaging();
                    log('Got Firebase messaging instance', 'success');

                    // Get FCM token
                    const token = await messaging.getToken({
                        vapidKey: 'BOt9XnxPzEX2b8pn0-kGRNqpS1rfby1CEbV-Dc_G87H9Wp5qnd6E_nyDBTHiD_NLoXGyx4Y0RhwbxTNSI9O9dtA'
                    });

                    if (token) {
                        log('FCM Token obtained: ' + token.substring(0, 30) + '...', 'success');
                        await this.sendTokenToServer(token);
                    } else {
                        log('No FCM token received', 'error');
                    }
                } catch (error) {
                    log('Firebase initialization error: ' + error.message, 'error');
                }
            }

            async sendTokenToServer(token) {
                log('Sending token to server...', 'info');
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
                        log('Token saved to server: ' + JSON.stringify(data), 'success');
                    } else {
                        const errorText = await response.text();
                        log('Failed to save token, status: ' + response.status + ' ' + errorText, 'error');
                    }
                } catch (error) {
                    log('Error sending token to server: ' + error.message, 'error');
                }
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            log('DOM loaded, starting test...', 'info');
            new TestPushNotificationManager();
        });
    </script>
</body>
</html>

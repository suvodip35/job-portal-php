// PWA Install Prompt Functionality
let deferredPrompt;
let installButton;

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA install prompt available');
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button
    showInstallButton();
});

// Show install button if prompt is available
function showInstallButton() {
    // Remove existing button if any
    const existingBtn = document.getElementById('pwa-install-btn');
    if (existingBtn) {
        existingBtn.remove();
    }
    
    // Create install button
    installButton = document.createElement('button');
    installButton.id = 'pwa-install-btn';
    installButton.className = 'fixed bottom-4 right-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-lg transition-all duration-300 z-50 flex items-center gap-2';
    installButton.innerHTML = `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
        Install App
    `;
    
    installButton.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to the install prompt: ${outcome}`);
            
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
                // Hide the button
                hideInstallButton();
                // Show success message
                showInstallMessage('App installed successfully!', 'success');
            } else {
                console.log('User dismissed the install prompt');
                showInstallMessage('Install dismissed. You can install later from browser menu.', 'info');
            }
            
            deferredPrompt = null;
        }
    });
    
    document.body.appendChild(installButton);
    
    // Auto-hide after 10 seconds
    setTimeout(() => {
        if (installButton && installButton.parentNode) {
            installButton.style.opacity = '0.7';
            installButton.style.transform = 'scale(0.9)';
        }
    }, 10000);
}

function hideInstallButton() {
    if (installButton && installButton.parentNode) {
        installButton.remove();
        installButton = null;
    }
}

function showInstallMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-blue-500';
    
    messageDiv.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 300);
    }, 3000);
}

// Check if app is already installed
window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    hideInstallButton();
    showInstallMessage('FromCampus Job Portal is now installed!', 'success');
    
    // Save installation status
    localStorage.setItem('pwa-installed', 'true');
});

// Show install button for iOS users (since beforeinstallprompt doesn't work on iOS)
function showIOSInstallInstructions() {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isInStandaloneMode = ('standalone' in window.navigator) && window.navigator.standalone;
    
    if (isIOS && !isInStandaloneMode && !localStorage.getItem('ios-install-dismissed')) {
        const iosMessage = document.createElement('div');
        iosMessage.className = 'fixed bottom-4 left-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-md mx-auto';
        iosMessage.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-semibold mb-2">Install FromCampus App</h3>
                    <p class="text-sm">To install this app: tap the share button <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/></svg> and then "Add to Home Screen"</p>
                </div>
                <button onclick="dismissIOSInstall()" class="ml-2 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(iosMessage);
    }
}

function dismissIOSInstall() {
    const iosMessage = document.querySelector('.fixed.bottom-4.left-4.right-4');
    if (iosMessage) {
        iosMessage.remove();
    }
    localStorage.setItem('ios-install-dismissed', 'true');
}

// Check if we should show install prompt
document.addEventListener('DOMContentLoaded', () => {
    // Don't show if already installed
    if (localStorage.getItem('pwa-installed') === 'true') {
        return;
    }
    
    // Show iOS instructions after a delay
    setTimeout(() => {
        showIOSInstallInstructions();
    }, 5000);
    
    // Check if running as PWA
    if (window.matchMedia('(display-mode: standalone)').matches) {
        console.log('Running in standalone mode (PWA)');
        localStorage.setItem('pwa-installed', 'true');
    }
});

// Handle service worker updates
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('Service worker updated');
        window.location.reload();
    });
}

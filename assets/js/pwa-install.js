// PWA Install Prompt Functionality
let deferredPrompt;
let installButton;

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA install prompt available');
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button immediately
    showInstallButton();
    
    // Also create banner for all devices (desktop and mobile)
    showInstallBanner();
});

// Create install banner for mobile
function showInstallBanner() {
    // Remove existing banner
    const existingBanner = document.getElementById('pwa-install-banner');
    if (existingBanner) {
        existingBanner.remove();
    }
    
    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.style.cssText = `
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #008dff 0%, #0066cc 100%);
        color: white;
        padding: 12px 20px;
        text-align: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
        animation: slideUp 0.3s ease-out;
    `;
    
    banner.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; padding: 10px;">
            <div style="text-align: center; margin-bottom: 10px;">
                <strong style="font-size: 16px; margin-bottom: 5px;">📱 Install FromCampus App</strong>
                <div style="font-size: 12px; opacity: 0.8;">Get faster access to latest jobs</div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="installPWA()" style="
                    background: white; 
                    color: #008dff; 
                    border: 2px solid #008dff; 
                    padding: 8px 16px; 
                    border-radius: 25px; 
                    font-size: 14px; 
                    font-weight: 600; 
                    cursor: pointer; 
                    transition: all 0.2s ease;
                    box-shadow: 0 2px 8px rgba(0,141,255,0.3);
                " onmouseover="this.style.transform='scale(1.05)'" 
                   onmouseout="this.style.transform='scale(1)'">
                    Install Now
                </button>
                <button onclick="showManualTrigger()" style="
                    background: #f8f9fa; 
                    color: #333; 
                    border: 1px solid #ddd; 
                    padding: 6px 12px; 
                    border-radius: 20px; 
                    font-size: 12px; 
                    cursor: pointer;
                ">Test Prompt</button>
                <button onclick="dismissBanner()" style="
                    background: transparent; 
                    color: white; 
                    border: 1px solid white; 
                    padding: 4px 8px; 
                    border-radius: 50%; 
                    cursor: pointer;
                    font-size: 16px;
                    line-height: 1;
                ">×</button>
            </div>
        </div>
    `;
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(banner);
    
    // Auto-hide after 30 seconds (more time to see)
    setTimeout(() => {
        if (document.getElementById('pwa-install-banner')) {
            banner.style.opacity = '0.7';
        }
    }, 30000);
}

function dismissBanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
        banner.style.animation = 'slideDown 0.3s ease-out';
        setTimeout(() => banner.remove(), 300);
    }
    localStorage.setItem('pwa-banner-dismissed', 'true');
}

function installPWA() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
                dismissBanner();
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
        });
    }
}

function showManualTrigger() {
    console.log('Manual install trigger activated');
    if (deferredPrompt) {
        console.log('Install prompt is available - showing banner again');
        showInstallBanner();
    } else {
        console.log('No install prompt available yet');
        alert('Install prompt not available yet. Try browsing the site for 30+ seconds.');
    }
}

// Show install button if prompt is available
function showInstallButton() {
    // Remove existing button if any
    const existingBtn = document.getElementById('pwa-install-btn');
    if (existingBtn) {
        existingBtn.remove();
    }
    
    // Create install button (desktop fallback)
    installButton = document.createElement('button');
    installButton.id = 'pwa-install-btn';
    installButton.className = 'install-btn';
    installButton.textContent = '📱 Install FromCampus App';
    installButton.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #008dff;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        z-index: 9998;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(installButton);
    
    // Also show desktop install icon in address bar area
    setTimeout(() => {
        if (!document.getElementById('desktop-install-icon')) {
            const desktopIcon = document.createElement('div');
            desktopIcon.id = 'desktop-install-icon';
            desktopIcon.innerHTML = '📱';
            desktopIcon.style.cssText = `
                position: fixed;
                top: 20px;
                right: 180px;
                background: #008dff;
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                z-index: 9997;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                animation: pulse 2s infinite;
                cursor: pointer;
            `;
            desktopIcon.onclick = () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            desktopIcon.style.background = '#4caf50';
                            desktopIcon.innerHTML = '✓';
                            setTimeout(() => desktopIcon.remove(), 2000);
                        } else {
                            desktopIcon.style.background = '#f44336';
                            setTimeout(() => desktopIcon.remove(), 2000);
                        }
                        deferredPrompt = null;
                    });
                }
            };
            
            document.body.appendChild(desktopIcon);
        }
    }, 2000);
    
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
    
    // Don't show if banner was dismissed
    if (localStorage.getItem('pwa-banner-dismissed') === 'true') {
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

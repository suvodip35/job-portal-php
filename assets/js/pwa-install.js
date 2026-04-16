// PWA Install Prompt Functionality
console.log('PWA INSTALL SCRIPT LOADED - Version 1.0');
let deferredPrompt;

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA install prompt event fired!', e);
    e.preventDefault();
    deferredPrompt = e;
    
    console.log('Mobile check:', window.innerWidth <= 768);
    console.log('Dismissed session check:', sessionStorage.getItem('pwa-banner-dismissed-session'));
    
    // Only show banner on mobile devices
    if (window.innerWidth <= 768) {
        // Simple check - only hide if explicitly dismissed this session
        if (!sessionStorage.getItem('pwa-banner-dismissed-session')) {
            console.log('Showing install banner...');
            showInstallBanner();
            sessionStorage.setItem('pwa-banner-shown-session', 'true');
        } else {
            console.log('Banner already dismissed this session');
        }
    } else {
        console.log('Desktop device - no banner');
    }
});

// Force show banner for debugging (remove this in production)
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded - checking mobile...');
    alert('PWA Script Loaded - Mobile: ' + (window.innerWidth <= 768));
    
    // Create a simple test banner immediately
    const simpleBanner = document.createElement('div');
    simpleBanner.style.cssText = `
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        background: red !important;
        color: white !important;
        padding: 20px !important;
        text-align: center !important;
        z-index: 999999 !important;
        font-size: 20px !important;
    `;
    simpleBanner.textContent = 'SIMPLE TEST BANNER - If you see this, JavaScript works!';
    document.body.appendChild(simpleBanner);
    
    if (window.innerWidth <= 768) {
        console.log('Mobile detected - forcing banner for debugging...');
        setTimeout(() => {
            showInstallBanner();
        }, 2000);
    }
});

// Simple fallback - show banner after 5 seconds if no event
setTimeout(() => {
    console.log('Fallback check - Mobile:', window.innerWidth <= 768);
    console.log('Has deferred prompt:', !!deferredPrompt);
    console.log('🔍 Has deferred prompt:', !!deferredPrompt);
    if (window.innerWidth <= 768 && !sessionStorage.getItem('pwa-banner-shown-session')) {
        console.log('🔄 Fallback: Showing banner anyway...');
        showInstallBanner();
        sessionStorage.setItem('pwa-banner-shown-session', 'true');
    }
}, 5000);

// Create install banner for mobile
function showInstallBanner() {
    console.log('showInstallBanner() called - creating banner...');
    
    // Remove existing banner
    const existingBanner = document.getElementById('pwa-install-banner');
    if (existingBanner) {
        console.log('Removing existing banner...');
        existingBanner.remove();
    }
    
    console.log('Creating new banner element...');
    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.style.cssText = `
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%) !important;
        color: white !important;
        padding: 20px !important;
        border-radius: 0 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        z-index: 999999 !important;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.5) !important;
        max-width: none !important;
        width: 100% !important;
        height: auto !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        transform: none !important;
        border: none !important;
        text-align: center !important;
    `;
    
    banner.innerHTML = `
        <div style="text-align: center;">
            <div style="font-size: 18px; margin-bottom: 8px;">Install FromCampus App</div>
            <div style="font-size: 14px; margin-bottom: 12px;">Get faster access to latest jobs</div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="installPWA()" style="
                    background: white; 
                    color: #ff0000; 
                    border: none; 
                    padding: 12px 24px; 
                    border-radius: 8px; 
                    font-size: 16px; 
                    font-weight: 600; 
                    cursor: pointer;
                ">
                    Install
                </button>
                <button onclick="dismissBanner()" style="
                    background: transparent; 
                    color: white; 
                    border: 2px solid white; 
                    padding: 12px 24px; 
                    border-radius: 8px; 
                    font-size: 16px; 
                    font-weight: 600; 
                    cursor: pointer;
                ">
                    Later
                </button>
            </div>
        </div>
    `;
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { 
                transform: translateX(100%);
                opacity: 0;
            }
            to { 
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from { 
                transform: translateX(0);
                opacity: 1;
            }
            to { 
                transform: translateX(100%);
                opacity: 0;
            }
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    console.log('Appending banner to body...');
    document.body.appendChild(banner);
    
    console.log('Banner added to DOM. Checking if visible...');
    const addedBanner = document.getElementById('pwa-install-banner');
    console.log('Banner in DOM:', !!addedBanner);
    console.log('Banner styles:', addedBanner ? addedBanner.style.cssText.substring(0, 100) : 'Not found');
    
    // Add a simple test element to verify visibility
    const testDiv = document.createElement('div');
    testDiv.style.cssText = `
        position: fixed !important;
        top: 50px !important;
        left: 10px !important;
        background: yellow !important;
        color: black !important;
        padding: 10px !important;
        z-index: 999999 !important;
        font-size: 16px !important;
        border: 2px solid black !important;
    `;
    testDiv.textContent = 'TEST BANNER VISIBLE';
    document.body.appendChild(testDiv);
    
    console.log('Test div added - if you see this, DOM manipulation works');
    
    // Auto-hide after 30 seconds
    setTimeout(() => {
        if (document.getElementById('pwa-install-banner')) {
            banner.style.opacity = '0.7';
        }
    }, 30000);
}

function dismissBanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
        banner.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => banner.remove(), 300);
    }
    // Remember dismissal for this session
    sessionStorage.setItem('pwa-banner-dismissed-session', 'true');
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

// Check if app is already installed
window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    dismissBanner();
    
    // Save installation status
    localStorage.setItem('pwa-installed', 'true');
});

// Check if we should show install prompt
document.addEventListener('DOMContentLoaded', () => {
    // Don't show if already installed
    if (localStorage.getItem('pwa-installed') === 'true') {
        return;
    }
    
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

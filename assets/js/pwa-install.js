// PWA Install Prompt Functionality
console.log('PWA INSTALL SCRIPT LOADED - Version 3.3 (Compact White & Blue)');
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
        // Check if user dismissed within last week
        const dismissedUntil = localStorage.getItem('pwa-banner-dismissed-until');
        const now = new Date();
        const isDismissed = dismissedUntil && new Date(dismissedUntil) > now;
        
        if (isDismissed) {
            console.log('Banner dismissed until:', dismissedUntil);
            return;
        }
        
        console.log('Showing install banner...');
        showInstallBanner();
        sessionStorage.setItem('pwa-banner-shown-session', 'true');
    } else {
        console.log('Desktop device - no banner');
    }
});

// Force show banner for debugging (remove this in production)
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded - checking mobile...');
    console.log('Browser detected:', navigator.userAgent);
    
    // Check if Chrome
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    console.log('Is Chrome:', isChrome);
    
    // Check 1-week dismissal before showing banner
    const dismissedUntil = localStorage.getItem('pwa-banner-dismissed-until');
    const now = new Date();
    const isDismissed = dismissedUntil && new Date(dismissedUntil) > now;
    
    if (isDismissed) {
        console.log('Banner dismissed until:', dismissedUntil);
        return;
    }
    
    // For Chrome mobile, show banner immediately without waiting for beforeinstallprompt
    if (window.innerWidth <= 768 && isChrome) {
        console.log('Chrome mobile detected - showing banner immediately...');
        setTimeout(() => {
            showInstallBanner();
        }, 3000);
    }
    
    if (window.innerWidth <= 768) {
        console.log('Mobile detected - showing banner...');
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
        bottom: 20px !important;
        right: 20px !important;
        background: white !important;
        color: #333 !important;
        padding: 12px !important;
        border-radius: 12px !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        z-index: 999999 !important;
        box-shadow: 0 4px 20px rgba(0, 141, 255, 0.2) !important;
        max-width: 280px !important;
        width: 280px !important;
        height: auto !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        transform: none !important;
        border: 2px solid #008dff !important;
        animation: slideInRight 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
    `;
    
    banner.innerHTML = `
        <div style="text-align: center;">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                <div style="width: 28px; height: 28px; background: #008dff; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                    <span style="font-size: 16px; color: white; font-weight: bold;">FC</span>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 14px; font-weight: 700; color: #333;">FromCampus</div>
                    <div style="font-size: 11px; opacity: 0.7; color: #666;">Job Portal App</div>
                </div>
            </div>
            <div style="font-size: 11px; margin-bottom: 10px; opacity: 0.8; color: #555;">
                Get instant job alerts & apply faster
            </div>
            <div style="display: flex; gap: 6px; justify-content: center;">
                <button onclick="installPWA()" style="
                    background: #008dff; 
                    color: white; 
                    border: none; 
                    padding: 6px 14px; 
                    border-radius: 6px; 
                    font-size: 12px; 
                    font-weight: 700; 
                    cursor: pointer;
                    transition: all 0.2s ease;
                " onmouseover="this.style.transform='scale(1.05)'" 
                   onmouseout="this.style.transform='scale(1)'">
                    Install
                </button>
                <button onclick="dismissBanner()" style="
                    background: transparent; 
                    color: #008dff; 
                    border: 1px solid #008dff; 
                    padding: 6px 14px; 
                    border-radius: 6px; 
                    font-size: 12px; 
                    font-weight: 600; 
                    cursor: pointer;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='#008dff'; this.style.color='white'" 
                   onmouseout="this.style.background='transparent'; this.style.color='#008dff'">
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
                transform: translateX(400px);
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
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    console.log('Appending banner to body...');
    document.body.appendChild(banner);
    
    console.log('Banner added to DOM successfully');
    
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
        // Animate out to right
        banner.style.animation = 'slideOutRight 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important';
        setTimeout(() => banner.remove(), 600);
    }
    // Remember dismissal for 1 week
    const oneWeekFromNow = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);
    localStorage.setItem('pwa-banner-dismissed-until', oneWeekFromNow.toISOString());
    console.log('Banner dismissed until:', oneWeekFromNow.toISOString());
}

function installPWA() {
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    
    if (deferredPrompt) {
        console.log('Using deferred prompt...');
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
    } else if (isChrome && window.innerWidth <= 768) {
        // Chrome mobile manual install instructions
        alert('To install FromCampus App:\n\n1. Tap the menu button (3 dots) in Chrome\n2. Tap "Add to Home Screen"\n3. Tap "Add" to install the app');
        dismissBanner();
    } else {
        console.log('No install prompt available');
        alert('To install FromCampus App:\n\nLook for the install option in your browser menu (usually 3 dots) and select "Add to Home Screen"');
        dismissBanner();
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

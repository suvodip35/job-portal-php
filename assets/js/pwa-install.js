// PWA Install Prompt Functionality - Production Ready (No Alerts)
let deferredPrompt;

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Only show banner on mobile devices
    if (window.innerWidth <= 768) {
        // Check if user dismissed within last week
        const dismissedUntil = localStorage.getItem('pwa-banner-dismissed-until');
        const now = new Date();
        const isDismissed = dismissedUntil && new Date(dismissedUntil) > now;
        
        if (isDismissed) {
            return;
        }
        
        showInstallBanner();
        sessionStorage.setItem('pwa-banner-shown-session', 'true');
    }
});

// Show banner on page load
document.addEventListener('DOMContentLoaded', () => {
    // Check if Chrome
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    
    // Check 1-week dismissal before showing banner
    const dismissedUntil = localStorage.getItem('pwa-banner-dismissed-until');
    const now = new Date();
    const isDismissed = dismissedUntil && new Date(dismissedUntil) > now;
    
    if (isDismissed) {
        return;
    }
    
    // For Chrome mobile, show banner immediately without waiting for beforeinstallprompt
    if (window.innerWidth <= 768 && isChrome) {
        setTimeout(() => {
            showInstallBanner();
        }, 3000);
    }
    
    if (window.innerWidth <= 768) {
        setTimeout(() => {
            showInstallBanner();
        }, 2000);
    }
});

// Simple fallback - show banner after 5 seconds if no event
setTimeout(() => {
    if (window.innerWidth <= 768 && !sessionStorage.getItem('pwa-banner-shown-session')) {
        showInstallBanner();
        sessionStorage.setItem('pwa-banner-shown-session', 'true');
    }
}, 5000);

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
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        background: white !important;
        color: #333 !important;
        padding: 16px !important;
        border-top: 3px solid #008dff !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        z-index: 999999 !important;
        box-shadow: 0 -4px 20px rgba(0, 141, 255, 0.15) !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        transform: none !important;
        animation: slideInUp 0.5s ease-out !important;
    `;
    
    banner.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 600px; margin: 0 auto;">
            <div style="display: flex; align-items: center;">
                <div style="width: 40px; height: 40px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px; border: 1px solid #e0e0e0;">
                    <img src="/assets/logo/fc_logo_crop.png" alt="FromCampus" style="width: 32px; height: 32px; object-fit: contain;">
                </div>
                <div>
                    <div style="font-size: 16px; font-weight: 700; color: #333;">Install FromCampus App</div>
                    <div style="font-size: 12px; opacity: 0.8; color: #666;">Get instant job alerts & apply faster</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="installPWA()" style="
                    background: #008dff; 
                    color: white; 
                    border: none; 
                    padding: 8px 20px; 
                    border-radius: 8px; 
                    font-size: 14px; 
                    font-weight: 700; 
                    cursor: pointer;
                    transition: all 0.2s ease;
                    box-shadow: 0 2px 8px rgba(0, 141, 255, 0.3);
                " onmouseover="this.style.transform='scale(1.05)'" 
                   onmouseout="this.style.transform='scale(1)'">
                    Install
                </button>
                <button onclick="dismissBanner()" style="
                    background: transparent; 
                    color: #008dff; 
                    border: 2px solid #008dff; 
                    padding: 8px 20px; 
                    border-radius: 8px; 
                    font-size: 14px; 
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
        @keyframes slideInUp {
            from { 
                transform: translateY(100%);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }
        @keyframes slideOutDown {
            from { 
                transform: translateY(0);
                opacity: 1;
            }
            to { 
                transform: translateY(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(banner);
    
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
        // Animate out to bottom
        banner.style.animation = 'slideOutDown 0.5s ease-in !important';
        setTimeout(() => banner.remove(), 500);
    }
    // Remember dismissal for 1 week
    const oneWeekFromNow = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);
    localStorage.setItem('pwa-banner-dismissed-until', oneWeekFromNow.toISOString());
}

function installPWA() {
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                dismissBanner();
            }
            deferredPrompt = null;
        });
    } else {
        // Just dismiss the banner - user can install manually if needed
        dismissBanner();
    }
}

function showManualTrigger() {
    if (deferredPrompt) {
        showInstallBanner();
    }
}

// Check if app is already installed
window.addEventListener('appinstalled', () => {
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
        localStorage.setItem('pwa-installed', 'true');
    }
});

// Handle service worker updates
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        window.location.reload();
    });
}

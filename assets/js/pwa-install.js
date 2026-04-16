// PWA Install Prompt Functionality
let deferredPrompt;

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('🎯 PWA install prompt event fired!', e);
    e.preventDefault();
    deferredPrompt = e;
    
    console.log('📱 Mobile check:', window.innerWidth <= 768);
    console.log('💾 Session check:', sessionStorage.getItem('pwa-banner-shown-session'));
    
    // Only show banner on mobile devices
    if (window.innerWidth <= 768) {
        // Check if banner was shown this session
        if (!sessionStorage.getItem('pwa-banner-shown-session')) {
            console.log('🚀 Showing install banner...');
            showInstallBanner();
            sessionStorage.setItem('pwa-banner-shown-session', 'true');
        } else {
            console.log('⏸️ Banner already shown this session');
        }
    } else {
        console.log('💻 Desktop device - no banner');
    }
});

// Also add manual trigger for testing
setTimeout(() => {
    console.log('🔍 Manual check - Mobile:', window.innerWidth <= 768);
    console.log('🔍 Has deferred prompt:', !!deferredPrompt);
    if (window.innerWidth <= 768 && !deferredPrompt) {
        console.log('⚠️ No install prompt yet - trying to show anyway...');
        showInstallBanner();
    }
}, 5000);

// Fallback: Show banner after 10 seconds even if beforeinstallprompt doesn't fire
setTimeout(() => {
    if (window.innerWidth <= 768 && !sessionStorage.getItem('pwa-banner-shown-session')) {
        console.log('🔄 Fallback: Showing banner after delay...');
        showInstallBanner();
        sessionStorage.setItem('pwa-banner-shown-session', 'true');
    }
}, 10000);

// Immediate manual trigger for testing
if (window.innerWidth <= 768) {
    const manualBtn = document.createElement('button');
    manualBtn.textContent = '🎯 Show Banner';
    manualBtn.style.cssText = `
        position: fixed;
        top: 60px;
        right: 10px;
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 10001;
        cursor: pointer;
    `;
    manualBtn.onclick = () => {
        console.log('🎯 Manual banner trigger clicked');
        showInstallBanner();
        manualBtn.remove();
    };
    document.body.appendChild(manualBtn);
}

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
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #008dff 0%, #0066cc 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        max-width: 320px;
        backdrop-filter: blur(10px);
    `;
    
    banner.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">FromCampus</span>
                <div style="flex: 1;">
                    <div style="font-size: 14px; font-weight: 600;">Install App</div>
                    <div style="font-size: 12px; opacity: 0.8;">Get faster access to jobs</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="installPWA()" style="
                    flex: 1;
                    background: white; 
                    color: #008dff; 
                    border: none; 
                    padding: 10px 16px; 
                    border-radius: 8px; 
                    font-size: 14px; 
                    font-weight: 600; 
                    cursor: pointer; 
                    transition: all 0.2s ease;
                    box-shadow: 0 2px 8px rgba(0,141,255,0.3);
                " onmouseover="this.style.transform='scale(1.02)'" 
                   onmouseout="this.style.transform='scale(1)'">
                    Install
                </button>
                <button onclick="dismissBanner()" style="
                    flex: 1;
                    background: transparent; 
                    color: white; 
                    border: 1px solid rgba(255,255,255,0.3); 
                    padding: 10px 16px; 
                    border-radius: 8px; 
                    font-size: 14px; 
                    font-weight: 600; 
                    cursor: pointer;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(255,255,255,0.1)'" 
                   onmouseout="this.style.background='transparent'">
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
    
    document.body.appendChild(banner);
    
    // No auto-hide - banner stays visible until user action
}

function dismissBanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
        banner.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => banner.remove(), 300);
    }
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

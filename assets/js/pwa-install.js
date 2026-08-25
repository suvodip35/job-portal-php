// PWA Install Prompt Functionality - Mobile & PC Optimized (Zero Overflow & Nav Aware)
let deferredPrompt = null;

// Early check if beforeinstallprompt was already captured before script loaded
if (window.deferredPwaPrompt) {
    deferredPrompt = window.deferredPwaPrompt;
}

// Global listener for beforeinstallprompt
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    window.deferredPwaPrompt = e;
});

// Check if user dismissed prompt (Session or LocalStorage)
function isDismissed() {
    try {
        if (sessionStorage.getItem('pwa-banner-dismissed-session') === 'true') {
            return true;
        }
        const dismissedUntil = localStorage.getItem('pwa-banner-dismissed-until');
        if (!dismissedUntil) return false;
        const date = new Date(dismissedUntil);
        if (isNaN(date.getTime())) return false;
        return date > new Date();
    } catch (e) {
        return false;
    }
}

// Check if app is installed
function isPWAInstalled() {
    try {
        return localStorage.getItem('pwa-installed') === 'true' || 
               window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    } catch (e) {
        return false;
    }
}

// Show install banner if eligible
function showInstallBanner() {
    if (isPWAInstalled() || isDismissed()) return;
    
    // Prevent duplicate banners
    const existingBanner = document.getElementById('pwa-install-banner');
    if (existingBanner) return;
    
    const isDark = document.documentElement.classList.contains('dark') || 
                   (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    
    // Inject responsive styles for banner and modal
    if (!document.getElementById('pwa-banner-style')) {
        const style = document.createElement('style');
        style.id = 'pwa-banner-style';
        style.textContent = `
            #pwa-install-banner {
                position: fixed !important;
                left: 12px !important;
                right: 12px !important;
                bottom: 66px !important; /* Positioned safely above mobile bottom navbar (height ~56px) */
                width: calc(100% - 24px) !important;
                max-width: 480px !important;
                margin: 0 auto !important;
                box-sizing: border-box !important;
                background: ${isDark ? '#1e293b' : '#ffffff'} !important;
                color: ${isDark ? '#f8fafc' : '#0f172a'} !important;
                padding: 12px 14px !important;
                border-radius: 12px !important;
                border: 1px solid ${isDark ? '#334155' : '#e2e8f0'} !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
                z-index: 99990 !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
                animation: pwaSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            @media (min-width: 768px) {
                #pwa-install-banner {
                    left: auto !important;
                    right: 20px !important;
                    bottom: 20px !important;
                    width: 380px !important;
                }
            }
            @keyframes pwaSlideUp {
                from { transform: translateY(120%); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes pwaSlideDown {
                from { transform: translateY(0); opacity: 1; }
                to { transform: translateY(120%); opacity: 0; }
            }
            .pwa-instruction-modal {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(4px);
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                box-sizing: border-box;
                animation: pwaFadeIn 0.25s ease-out;
            }
            @keyframes pwaFadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    banner.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; box-sizing: border-box;">
            <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1 1 auto;">
                <div style="width: 36px; height: 36px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e2e8f0; padding: 2px; box-sizing: border-box;">
                    <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="28" height="28" style="width: 28px; height: 28px; object-fit: contain;">
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 13px; font-weight: 700; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Install FromCampus App</div>
                    <div style="font-size: 11px; opacity: 0.75; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Instant job alerts & fast access</div>
                </div>
            </div>
            <div style="display: flex; gap: 6px; flex-shrink: 0; align-items: center;">
                <button onclick="installPWA()" style="
                    background: #2563eb; 
                    color: #ffffff; 
                    border: none; 
                    padding: 6px 13px; 
                    border-radius: 6px; 
                    font-size: 12px; 
                    font-weight: 700; 
                    cursor: pointer;
                    white-space: nowrap;
                    line-height: 1.4;
                ">
                    Install
                </button>
                <button onclick="dismissBanner()" style="
                    background: transparent; 
                    color: ${isDark ? '#94a3b8' : '#64748b'}; 
                    border: 1px solid ${isDark ? '#475569' : '#cbd5e1'}; 
                    padding: 5px 10px; 
                    border-radius: 6px; 
                    font-size: 12px; 
                    font-weight: 600; 
                    cursor: pointer;
                    white-space: nowrap;
                    line-height: 1.4;
                ">
                    Later
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(banner);
}

function dismissBanner() {
    // Mark as dismissed in both session and local storage immediately
    try {
        sessionStorage.setItem('pwa-banner-dismissed-session', 'true');
        const threeDaysFromNow = new Date(Date.now() + 3 * 24 * 60 * 60 * 1000);
        localStorage.setItem('pwa-banner-dismissed-until', threeDaysFromNow.toISOString());
    } catch (e) {}

    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
        banner.style.animation = 'pwaSlideDown 0.3s cubic-bezier(0.4, 0, 1, 1) forwards';
        setTimeout(() => {
            if (banner && banner.parentNode) {
                banner.parentNode.removeChild(banner);
            }
        }, 300);
    }
}

function installPWA() {
    const promptEvent = deferredPrompt || window.deferredPwaPrompt;
    
    if (promptEvent) {
        try {
            promptEvent.prompt();
            promptEvent.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    localStorage.setItem('pwa-installed', 'true');
                    dismissBanner();
                } else {
                    dismissBanner();
                }
                deferredPrompt = null;
                window.deferredPwaPrompt = null;
            }).catch(() => {
                dismissBanner();
            });
        } catch (e) {
            showInstructionModal();
        }
    } else {
        // Fallback for browsers/iOS without native prompt support
        showInstructionModal();
    }
}

function showInstructionModal() {
    dismissBanner();
    
    const existingModal = document.getElementById('pwa-instruction-modal');
    if (existingModal) existingModal.remove();

    const isiOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isDark = document.documentElement.classList.contains('dark') || 
                   (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

    const modal = document.createElement('div');
    modal.id = 'pwa-instruction-modal';
    modal.className = 'pwa-instruction-modal';

    const stepsHtml = isiOS ? `
        <ol style="margin: 12px 0; padding-left: 20px; font-size: 13px; line-height: 1.6; color: ${isDark ? '#cbd5e1' : '#334155'};">
            <li style="margin-bottom: 6px;">Tap the <strong>Share button</strong> <span style="display:inline-block; font-size: 15px;">⎋ / ⍗</span> in Safari toolbar.</li>
            <li style="margin-bottom: 6px;">Scroll down and tap <strong>Add to Home Screen</strong> <span style="display:inline-block; font-size: 15px;">➕</span>.</li>
            <li>Tap <strong>Add</strong> in the top right corner.</li>
        </ol>
    ` : `
        <ol style="margin: 12px 0; padding-left: 20px; font-size: 13px; line-height: 1.6; color: ${isDark ? '#cbd5e1' : '#334155'};">
            <li style="margin-bottom: 6px;">Tap browser menu <strong style="font-size: 15px;">⋮</strong> (top right).</li>
            <li style="margin-bottom: 6px;">Select <strong>Install app</strong> or <strong>Add to Home Screen</strong>.</li>
            <li>Follow on-screen prompt to confirm.</li>
        </ol>
    `;

    modal.innerHTML = `
        <div style="
            background: ${isDark ? '#1e293b' : '#ffffff'};
            color: ${isDark ? '#f8fafc' : '#0f172a'};
            padding: 20px;
            border-radius: 16px;
            max-width: 360px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            border: 1px solid ${isDark ? '#334155' : '#e2e8f0'};
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
        ">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="24" height="24">
                    <span style="font-size: 15px; font-weight: 700;">Install FromCampus</span>
                </div>
                <button onclick="document.getElementById('pwa-instruction-modal').remove()" style="
                    background: transparent;
                    border: none;
                    color: ${isDark ? '#94a3b8' : '#64748b'};
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0 4px;
                    line-height: 1;
                ">&times;</button>
            </div>
            ${stepsHtml}
            <button onclick="document.getElementById('pwa-instruction-modal').remove()" style="
                width: 100%;
                background: #2563eb;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 700;
                cursor: pointer;
                margin-top: 8px;
            ">Got it!</button>
        </div>
    `;

    document.body.appendChild(modal);
}

// App installed event listener
window.addEventListener('appinstalled', () => {
    try {
        localStorage.setItem('pwa-installed', 'true');
    } catch (e) {}
    dismissBanner();
});

// Check if running as standalone PWA on load
document.addEventListener('DOMContentLoaded', () => {
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        try {
            localStorage.setItem('pwa-installed', 'true');
        } catch (e) {}
    }
    
    // Automatically attempt to show banner after DOM ready if eligible
    setTimeout(() => {
        if (!isPWAInstalled() && !isDismissed()) {
            showInstallBanner();
        }
    }, 1500);
});

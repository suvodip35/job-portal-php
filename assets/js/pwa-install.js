// PWA Install Functionality
let deferredPrompt;

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
    
    // Add manual trigger button for testing
    setTimeout(() => {
        const triggerBtn = document.createElement('button');
        triggerBtn.id = 'manual-trigger-btn';
        triggerBtn.textContent = '🎯 Show Install Banner';
        triggerBtn.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            background: #ff6b35;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            z-index: 9997;
            box-shadow: 0 2px 8px rgba(255,107,0,0.3);
        `;
        triggerBtn.onclick = () => {
            console.log('Manual trigger: Showing install banner');
            showInstallBanner();
        };
        document.body.appendChild(triggerBtn);
    }, 3000);
}

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA install prompt available');
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install banner immediately
    showInstallBanner();
    showInstallButton();
});

// Create install banner
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
        padding: 10px 15px;
        text-align: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13px;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.3);
        animation: slideUp 0.3s ease-out;
    `;
    
    banner.innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 8px;">
            <span style="font-size: 12px; font-weight: 600;">📱 Install FromCampus</span>
            <div style="display: flex; gap: 8px;">
                <button onclick="installPWA()" style="
                    background: white; 
                    color: #008dff; 
                    border: none; 
                    padding: 6px 12px; 
                    border-radius: 15px; 
                    font-size: 12px; 
                    font-weight: 600; 
                    cursor: pointer; 
                    transition: all 0.2s ease;
                " onmouseover="this.style.transform='scale(1.05)'" 
                   onmouseout="this.style.transform='scale(1)'">
                    Install
                </button>
                <button onclick="dismissBanner()" style="
                    background: transparent; 
                    color: white; 
                    border: 1px solid white; 
                    padding: 2px 6px; 
                    border-radius: 50%; 
                    cursor: pointer;
                    font-size: 14px;
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
        @keyframes slideDown {
            from { transform: translateY(0); }
            to { transform: translateY(100%); }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(banner);
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
                localStorage.setItem('pwa-installed', 'true');
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
        });
    }
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

// Listen for app installed
window.addEventListener('appinstalled', () => {
    console.log('FromCampus app was installed');
    localStorage.setItem('pwa-installed', 'true');
    
    // Hide any install prompts
    dismissBanner();
    const installBtn = document.getElementById('pwa-install-btn');
    if (installBtn) {
        installBtn.remove();
    }
});

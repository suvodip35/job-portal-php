<?php
// error handler function
function setupErrorLogger($logFile = __DIR__ . '/error_files.txt') {
    // Set timezone for accurate timestamps
    date_default_timezone_set('Asia/Kolkata');

    // Custom error handler
    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logFile) {
        $date = date('Y-m-d H:i:s');
        $message = "[$date] ERROR: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
        return true; // prevent default PHP error handler
    });

    // Fatal error handler
    register_shutdown_function(function () use ($logFile) {
        $error = error_get_last();
        if ($error !== null) {
            $date = date('Y-m-d H:i:s');
            $message = "[$date] FATAL: {$error['message']} in {$error['file']} on line {$error['line']}" . PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);
        }
    });

    // Exception handler
    set_exception_handler(function ($exception) use ($logFile) {
        $date = date('Y-m-d H:i:s');
        $message = "[$date] EXCEPTION: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}" . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
    });
}

// Call the function at the very top of header
setupErrorLogger();
?>

<?php
require_once __DIR__ . '/../.hta_config/functions.php';
$siteTitle = $siteTitle ?? APP_NAME;
$metaDesc = $metaDesc ?? 'Latest job notifications, mock tests and exam resources.';

define("PUBLIC_VAPID_KEY", "BNCdSNWUm6dVr6aRDexQcKQ_UuTEeZCpbY89lztrDcPtzFiN3MFWKbZtp1HT1IaoGEHTZrDpba71xCw74phxqBU");
define("PRIVATE_VAPID_KEY", "E_rtiwrFGQCxhXLUvvnPM2NVBdegcy1-ZDDbOAdRdak");

?>

<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($siteTitle) ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <link rel="icon" href="<?= BASE_URL ?>assets/favicon.ico">
  <!-- Tailwind CDN for quick production-ready UI; for full production consider building Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Theme: apply saved preference -->
  <script>
    try {
      const theme = localStorage.getItem('theme');
      if (theme === 'dark') document.documentElement.classList.add('dark');
    } catch (e) {}
  </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
<!-- Main Navigation -->
<nav class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <!-- Logo and Desktop Search -->
      <div class="flex items-center">
        <a href="/" class="text-xl font-semibold"><?= e(APP_NAME) ?></a>
        <form action="<?= BASE_URL ?>search" method="get" class="ml-6 hidden md:block">
          <input value="<?php if(isset($_GET['q'])){echo $_GET['q'];} ?>" type="search" name="q" placeholder="Search jobs or keywords..." class="px-3 py-1 rounded border focus:outline-none dark:bg-gray-900 dark:border-gray-700 w-64" />
        </form>
      </div>
      
      <!-- Desktop Navigation Items -->
      <div class="hidden md:flex items-center space-x-4">
        <!-- <a href="<?= BASE_URL ?>mock_tests" class="hover:text-blue-600 dark:hover:text-blue-400">Mock Tests</a>
        <a href="<?= BASE_URL ?>sitemap" class="hover:text-blue-600 dark:hover:text-blue-400">Sitemap</a> -->
        <a href="/" class="hover:text-blue-600 dark:hover:text-blue-400">Home</a>
        <a href="/" class="hover:text-blue-600 dark:hover:text-blue-400">Jobs</a>
        <a href="/contact" class="hover:text-blue-600 dark:hover:text-blue-400">Contact</a>
        <a href="/saved-jobs" class="hover:text-blue-600 dark:hover:text-blue-400">Saved</a>
        
        <!-- <button id="themeToggle" title="Toggle theme" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition">
          <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path id="themePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-9H21M3 12H2m15.36 6.36l-.71.71M6.34 5.34l-.71.71M18.36 5.64l-.71-.71M6.34 18.66l-.71-.71" />
          </svg>
        </button> -->

        <button id="subscribePushBtn" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Get Job Alerts</button>

        <a href="<?= BASE_URL ?>admin/login" class="px-3 py-1 border rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">Admin</a>
      </div>
      
      <!-- Mobile Menu Button -->
      <div class="md:hidden flex items-center">
        <button id="mobileMenuButton" class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>
  
  <!-- Mobile Menu (hidden by default) -->
  <div id="mobileMenu" class="md:hidden hidden bg-gray-100 dark:bg-gray-800">
    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
      <form action="/search" method="get" class="px-3 pb-2">
        <input type="search" name="q" placeholder="Search jobs or keywords..." class="w-full px-3 py-2 rounded border focus:outline-none dark:bg-gray-900 dark:border-gray-700" />
      </form>
      <a href="/" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Home</a>
      <a href="/" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Jobs</a>
      <a href="/saved-jobs" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Saved</a>
      <a href="/contact" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Contact</a>
      <!-- <a href="<?= BASE_URL ?>mock_tests" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Mock Tests</a> -->
      <!-- <a href="<?= BASE_URL ?>sitemap" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Sitemap</a> -->
      <button id="mobileSubscribePushBtn" class="w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Get Job Alerts</button>
      <a href="<?= BASE_URL ?>/admin/login" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Admin</a>
    </div>
  </div>
</nav>

<!-- Fixed Bottom Navigation for Mobile -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50">
  <div class="flex justify-around">
    <a href="<?= BASE_URL ?>" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <i class="fas fa-home text-lg mb-1"></i>
      <span>Home</span>
    </a>
    <a href="#" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <i class="fas fa-file-alt text-lg mb-1"></i>
      <span>Tests</span>
    </a>

    <a href="/saved-jobs" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <i class="fas fa-bookmark text-lg mb-1"></i>
      <span>Saved</span>
    </a>
    
    <!-- <button id="bottomThemeToggle" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-9H21M3 12H2m15.36 6.36l-.71.71M6.34 5.34l-.71.71M18.36 5.64l-.71-.71M6.34 18.66l-.71-.71" />
      </svg>
      <span>Theme</span>
    </button> -->
    <button id="bottomSubscribePushBtn" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <i class="fas fa-bell text-lg mb-1"></i>
      <span>Alerts</span>
    </button>
  </div>
</div>

<main class="max-w-6xl mx-auto px-4 py-6 md:pb-6 pb-20"> <!-- Added padding-bottom for mobile -->
<script type="module" src="app.js"></script>
<script>
  // Mobile menu toggle
  const mobileMenuButton = document.getElementById('mobileMenuButton');
  const mobileMenu = document.getElementById('mobileMenu');
  
  mobileMenuButton.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
  });
  
  // Theme toggle functionality (for both top and bottom buttons)
  function toggleTheme() {
    const html = document.documentElement;
    html.classList.toggle('dark');
    const isDark = html.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Update icon
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
      const themePath = document.getElementById('themePath');
      if (isDark) {
        themePath.setAttribute('d', 'M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.39 5.39 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z');
      } else {
        themePath.setAttribute('d', 'M12 3v1m0 16v1m8.66-9H21M3 12H2m15.36 6.36l-.71.71M6.34 5.34l-.71.71M18.36 5.64l-.71-.71M6.34 18.66l-.71-.71');
      }
    }
  }
  
  document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
  document.getElementById('bottomThemeToggle')?.addEventListener('click', toggleTheme);
  
async function handleSubscribe() {
  if (!("serviceWorker" in navigator)) {
    alert("Service Worker not supported in this browser.");
    return;
  }

  try {
    const reg = await navigator.serviceWorker.register("/sw.js");
    console.log("Service Worker registered", reg);

    const permission = await Notification.requestPermission();
    if (permission !== "granted") {
      alert("Notifications permission denied.");
      return;
    }

    // Public VAPID key (PHP backend generate kore dibe)
    const vapidKey = "<?= PUBLIC_VAPID_KEY ?>";

    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(vapidKey)
    });

    // Send subscription to server
    await fetch("/save-subscription.php", {
      method: "POST",
      body: JSON.stringify(sub),
      headers: {
        "Content-Type": "application/json"
      }
    });
console.log('front data', sub)
    alert("You are subscribed to Job Alerts!");
  } catch (err) {
    console.error("Subscription failed", err);
  }
}

// Helper: convert base64 public key to Uint8Array
function urlBase64ToUint8Array(base64String) {
  const padding = "=".repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// Button click events
document.getElementById("subscribePushBtn")?.addEventListener("click", handleSubscribe);
document.getElementById("mobileSubscribePushBtn")?.addEventListener("click", handleSubscribe);
document.getElementById("bottomSubscribePushBtn")?.addEventListener("click", handleSubscribe);
</script>
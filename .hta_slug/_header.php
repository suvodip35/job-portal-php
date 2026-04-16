<?php
  $file = $_SERVER['DOCUMENT_ROOT'] . '/counter.txt';

    // Cookie name
    $cookieName = "viewed_page";

    // যদি আগে cookie না থাকে তাহলে count বাড়ানো হবে
    if(!isset($_COOKIE[$cookieName])) {

        // Cookie set for 24 hours
        setcookie($cookieName, true, time() + 86400, "/");

        // File read + increment
        if(!file_exists($file)) {
            file_put_contents($file, 0);
        }

        $count = (int) file_get_contents($file);
        $count++;
        file_put_contents($file, $count);

    } else {
        // Cookie থাকলে কাউন্ট বাড়বে না
        $count = (int) file_get_contents($file);
    }

    // echo "Total Views: " . $count;
  // error handler function
  function setupErrorLogger($logFile = null) {
      // Set timezone for accurate timestamps
      date_default_timezone_set('Asia/Kolkata');
      
      // Use system temp directory if no file specified or if permissions issue
      if ($logFile === null) {
          $logFile = sys_get_temp_dir() . '/error_files.txt';
      }
      
      // Create directory if it doesn't exist
      $logDir = dirname($logFile);
      if (!is_dir($logDir)) {
          @mkdir($logDir, 0755, true);
      }
      
      // Check if we can write to the log file
      if (!is_writable($logDir) || (file_exists($logFile) && !is_writable($logFile))) {
          // Fallback to system temp directory
          $logFile = sys_get_temp_dir() . '/error_files.txt';
      }

      // Safe write function
      $safeWrite = function($message) use ($logFile) {
          @file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
      };

      // Custom error handler
      set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($safeWrite) {
          $date = date('Y-m-d H:i:s');
          $message = "[$date] ERROR: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
          $safeWrite($message);
          return true; // prevent default PHP error handler
      });

      // Fatal error handler
      register_shutdown_function(function () use ($safeWrite) {
          $error = error_get_last();
          if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
              $date = date('Y-m-d H:i:s');
              $message = "[$date] FATAL: {$error['message']} in {$error['file']} on line {$error['line']}" . PHP_EOL;
              $safeWrite($message);
          }
      });

      // Exception handler
      set_exception_handler(function ($exception) use ($safeWrite) {
          $date = date('Y-m-d H:i:s');
          $message = "[$date] EXCEPTION: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}" . PHP_EOL;
          $safeWrite($message);
      });
  }

  // Call the function at the very top of header
  setupErrorLogger();

  require_once __DIR__ . '/../.hta_config/functions.php';
  require_once __DIR__ . '/../.hta_config/config.php';
  $ogImageURIPrefix = 'https://fromcampus.com';
  // Use job-specific values if available, otherwise fallback to defaults
  $siteTitle      = $siteTitle      ?? APP_NAME;
  $metaDesc       = $metaDesc       ?? 'Latest job notifications, mock tests and exam resources.';
  $pageTitle      = $pageTitle      ?? $siteTitle;
  $pageDescription= $pageDescription?? $metaDesc;
  $keywords       = $keywords       ?? "Government JOBS, ITI JOBS, Railway Jobs, Engineer";
  $author         = $author         ?? "FromCampus";
  if (!empty($job['thumbnail'])) {
    $ogImage = $ogImageURIPrefix . $job['thumbnail'];
  } elseif (!empty($update['thumbnail'])) {
      $ogImage = $ogImageURIPrefix . $update['thumbnail'];
  } else {
      $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
  }

  $canonicalUrl   = $canonicalUrl   ?? BASE_URL;
  $ampHtmlCanonical = $ampHtmlCanonical ?? "https://fromcampus.com/amp/job";
// echo $ogImage;
?>

<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#008dff">
  <link rel="icon" href="/favicon.ico" />

  <!-- Basic Meta Tags -->
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>">
  <meta name="author" content="<?php echo htmlspecialchars($author); ?>">
  
  <!-- Open Graph Meta Tags (Facebook) -->
  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <meta property="og:image" content="<?php echo $ogImage; ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <meta property="og:type" content="website">
  
  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
  <meta name="twitter:image" content="<?php echo $ogImage; ?>">

  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  
  <meta property="og:site_name" content="FromCampus">

  <meta name="twitter:site" content="@FromCampus">
  <meta name="twitter:creator" content="@FromCampus">

  <meta property="og:locale" content="en_IN">
  <meta property="og:locale" content="bn_IN">
  <meta property="og:locale" content="hi_IN">

  <link rel="preload" as="image" href="FIRST_IMAGE_URL">

  <!-- <link rel="stylesheet" href="/assets/css/tailwind.css?v=1.0.4" /> -->

<link rel="preload" href="/assets/css/tailwind.css?v=1.0.4" as="style">
<link rel="stylesheet" href="/assets/css/tailwind.css?v=1.0.4">
  
  <!-- Canonical URL -->
  <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <link rel="amphtml" href="<?= $ampHtmlCanonical ?>">

  <meta name="robots" content="index, follow">
  <meta name="googlebot" content="index, follow">

  <!-- Google tag (gtag.js) -->
  <script id="opt1">
    // run after DOM ready, not full load
    document.addEventListener('DOMContentLoaded', function () {
      
      setTimeout(function () {

        // Google Analytics
        var gtagScript = document.createElement('script');
        gtagScript.src = "https://www.googletagmanager.com/gtag/js?id=G-7JQW8FVNQ2";
        gtagScript.async = true;
        document.head.appendChild(gtagScript);

        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-7JQW8FVNQ2');

        // Google Ads
        var adsScript = document.createElement('script');
        adsScript.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4941413774457326";
        adsScript.async = true;
        adsScript.crossOrigin = "anonymous";
        document.head.appendChild(adsScript);

      }, 3000); // increase delay একটু
    });
  </script>
  <meta name="google-adsense-account" content="ca-pub-4941413774457326">
  <!-- <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4941413774457326" crossorigin="anonymous"></script> -->
  <meta property="fb:app_id" content="1469923257657008" />

  <!-- Firebase SDK for Push Notifications -->
  <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>
  <script src="/firebase-config.js"></script>

</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
<!-- Main Navigation -->
 
<nav class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <!-- Logo and Desktop Search -->
      <div class="flex items-center">
        <!-- <a href="/" class="text-xl font-semibold">From Campus</a> -->
        <a href="/" class="text-xl font-semibold flex flex-col justify-center items-center" >
          <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus Logo" class="w-[40px] h-auto" width="40" height="40" />
          <p class="text-xs"><?= e(APP_NAME) ?></p>
        </a>
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
        <a href="/books" class="hover:text-blue-600 dark:hover:text-blue-400">Books</a>
        <a href="/updates" class="hover:text-blue-600 dark:hover:text-blue-400">Updates</a>
        <a href="/tools" class="hover:text-blue-600 dark:hover:text-blue-400">Tools</a>
        <a href="/contact" class="hover:text-blue-600 dark:hover:text-blue-400">Contact</a>
        <a href="/saved-jobs" class="hover:text-blue-600 dark:hover:text-blue-400">Saved</a>
        
        <!-- <button id="themeToggle" title="Toggle theme" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition">
          <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path id="themePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-9H21M3 12H2m15.36 6.36l-.71.71M6.34 5.34l-.71.71M18.36 5.64l-.71-.71M6.34 18.66l-.71-.71" />
          </svg>
        </button> -->

        <button id="subscribePushBtn" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition hidden md:block">Get Job Alerts</button>
        
        <!-- <a href="/adminqeIUgwefgWEOAjx/dashboard" class="px-3 py-1 border rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">Admin</a> -->
      </div>
      
      <!-- Mobile Menu Button -->
      <div class="md:hidden flex items-center gap-2">
        <!-- Mobile Push Notification Button -->
        <button id="mobilePushNotificationBtn" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-lg">
          <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
          </svg>
          Get Alerts
        </button>
        
        <button id="mobileMenuButton" class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none" aria-label="Open mobile menu">
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
      <a href="/books" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Books</a>
      <a href="/updates" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Updates</a>
      <a href="/tools" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Tools</a>
      <a href="/saved-jobs" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Saved</a>
      <a href="/contact" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Contact</a>
      <!-- <a href="<?= BASE_URL ?>mock_tests" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Mock Tests</a> -->
      <!-- <a href="<?= BASE_URL ?>sitemap" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Sitemap</a> -->
      <!-- <a href="/adminqeIUgwefgWEOAjx/dashboard" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Admin</a> -->
    </div>
  </div>
</nav>


<main class="container mx-auto max-w-8xl mx-auto px-4 py-6 md:pb-6 pb-20"> <!-- Added padding-bottom for mobile -->
<script>
  // console.log('Test Image', '<?php echo "Image Link" . $ogImage; ?>');
  // Mobile menu toggle
  const mobileMenuButton = document.getElementById('mobileMenuButton');
  const mobileMenu = document.getElementById('mobileMenu');
  
  mobileMenuButton.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
  });
  
  // Test if JavaScript is working on mobile
  console.log('HEADER SCRIPT: Loaded on ' + (window.innerWidth <= 768 ? 'MOBILE' : 'DESKTOP'));
  
  // Simple mobile button test
  const mobileBtn = document.getElementById('mobilePushNotificationBtn');
  if (mobileBtn) {
    console.log('HEADER SCRIPT: Mobile button found, adding test click');
    mobileBtn.addEventListener('click', () => {
      console.log('HEADER SCRIPT: Mobile button clicked!');
      alert('Mobile button clicked! Test successful.');
    });
  } else {
    console.log('HEADER SCRIPT: Mobile button NOT found');
  }
  
</script>
<style>

/* =========================
   Markdown Container
========================= */

#markdownContent{
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
  font-size:16px;
  line-height:1.7;
  word-wrap:break-word;
}

#markdownContent *{
  box-sizing:border-box;
}

/* =========================
   Responsive Headings
========================= */

#markdownContent h1,
#markdownContent h2,
#markdownContent h3,
#markdownContent h4,
#markdownContent h5,
#markdownContent h6{
  font-weight:600;
  margin-top:28px;
  margin-bottom:16px;
  line-height:1.25;
}

#markdownContent h1{
  font-size:clamp(28px,4vw,36px);
  border-bottom:1px solid #eaecef;
  padding-bottom:.3em;
}

#markdownContent h2{
  font-size:clamp(24px,3vw,30px);
  border-bottom:1px solid #eaecef;
  padding-bottom:.3em;
}

#markdownContent h3{font-size:clamp(20px,2.4vw,24px);}
#markdownContent h4{font-size:clamp(18px,2vw,20px);}
#markdownContent h5{font-size:clamp(16px,1.6vw,18px);}
#markdownContent h6{font-size:clamp(14px,1.2vw,16px);}

/* =========================
   Paragraph
========================= */

#markdownContent p{
  margin-top:0;
  margin-bottom:16px;
}

/* =========================
   Links
========================= */

#markdownContent a{
  color:#0366d6;
  text-decoration:none;
}

#markdownContent a:hover{
  text-decoration:underline;
}

/* =========================
   Lists
========================= */

#markdownContent ul,
#markdownContent ol{
  padding-left:2em;
  margin-top:0;
  margin-bottom:16px;
}

#markdownContent ul{
  list-style-type: disc;
}

#markdownContent ol{
  list-style-type: decimal;
}

#markdownContent li{
  margin-bottom:4px;
}

/* =========================
   Blockquote
========================= */

#markdownContent blockquote{
  margin:0 0 16px 0;
  padding:0 1em;
  color:#6a737d;
  border-left:.25em solid #dfe2e5;
}

/* =========================
   Horizontal Rule
========================= */

#markdownContent hr{
  height: 1px;
  margin:20px 0;
  background-color:#e1e4e8;
  border:0;
}

/* =========================
   Inline Code
========================= */

#markdownContent code{
  font-family:SFMono-Regular,Consolas,"Liberation Mono",Menlo,monospace;
  padding:.2em .4em;
  font-size:85%;
  background-color:rgba(27,31,35,.05);
  border-radius:3px;
}

/* =========================
   Code Block
========================= */

#markdownContent pre{
  padding:16px;
  overflow:auto;
  font-size:85%;
  line-height:1.45;
  background-color:#f6f8fa;
  border-radius:6px;
}

#markdownContent pre code{
  background:transparent;
  padding:0;
}

/* =========================
   Tables (Scrollable)
========================= */

#markdownContent table{
  border-spacing:0;
  border-collapse:collapse;
  margin-bottom:16px;
  width:100%;
  display:block;
  overflow-x:auto;
  max-width:100%;
}

#markdownContent table th,
#markdownContent table td{
  padding:6px 13px;
  border:1px solid #dfe2e5;
  white-space:nowrap;
}

#markdownContent table th{
  font-weight:600;
}

/* =========================
   Images
========================= */

#markdownContent img{
  max-width:100%;
  box-sizing:border-box;
}

/* =========================
   DARK MODE
========================= */

.dark #markdownContent{
  color:#e6edf3;
}

.dark #markdownContent h1,
.dark #markdownContent h2{
  border-bottom:1px solid #30363d;
}

.dark #markdownContent blockquote{
  color:#8b949e;
  border-left:.25em solid #30363d;
}

.dark #markdownContent hr{
  background-color:#30363d;
}

.dark #markdownContent code{
  background-color:rgba(110,118,129,.4);
}

.dark #markdownContent pre{
  background-color:#161b22;
}

.dark #markdownContent table th,
.dark #markdownContent table td{
  border:1px solid #30363d;
}

</style>

<!-- Push Notification Script -->
<script src="/assets/js/push-notifications.js"></script>
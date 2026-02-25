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
  <link rel="preload" href="/assets/css/tailwind.css?v=1.0.4" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=1.0.4">
  </noscript>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-7JQW8FVNQ2"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-7JQW8FVNQ2');
  </script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
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
  <!-- Canonical URL -->
  <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <link rel="amphtml" href="<?= $ampHtmlCanonical ?>">

  <meta name="robots" content="index, follow">
  <meta name="googlebot" content="index, follow">

  <meta name="google-adsense-account" content="ca-pub-4941413774457326">
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4941413774457326" crossorigin="anonymous"></script>
  <meta property="fb:app_id" content="1469923257657008" />

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
          <img src="/assets/logo/fc_logo_crop.png" alt="FromCampus Logo" class="w-[40px] h-auto" />
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

        <!-- <button id="subscribePushBtn" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Get Job Alerts</button> -->
        
        <!-- <a href="/adminqeIUgwefgWEOAjx/dashboard" class="px-3 py-1 border rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">Admin</a> -->
      </div>
      
      <!-- Mobile Menu Button -->
      <div class="md:hidden flex items-center">
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
      <!-- <button id="mobileSubscribePushBtn" class="w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-gray-200 dark:hover:bg-gray-700">Get Job Alerts</button> -->
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
  


</script>
<style>
  #markdownContent > h2 {
    font-size: 22px;
    font-weight: bold;
    margin-top: 20px;
    margin-bottom: 20px;
  }
  .job-description table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
    border: 1px solid #d1d5db; /* Light mode border */
  }

  /* Light Mode */
  .job-description th {
    font-weight: 600;
    padding: 0.3rem 1rem;
    border: 1px solid #d1d5db; /* Light border */
    text-align: left;
  }

  .job-description td {
    padding: 0.3rem 1rem;
    border: 1px solid #d1d5db;
  }

  /* 🌙 Dark Mode */
  .dark .job-description table {
    border: 1px solid #ffffffff; /* gray-700 */
  }

  #markdownContent > .dark .job-description th {
    color: #f9fafb; /* White text */
    border: 1px solid #374151;
  }

  #markdownContent > .dark .job-description td {
    color: #e5e7eb; /* Light gray text */
    border: 1px solid #374151;
  }

  #markdownContent {
  overflow: visible !important;
}


/* This is new applied class if not required remove in future */
/* Prose class এর override */
#markdownContent.prose * {
  overflow: visible !important;
  white-space: normal !important;
  word-break: break-word !important;
}

/* Table natural responsive behaviour */
#markdownContent table {
  display: table !important;
  width: 100% !important;
  table-layout: auto !important;
}

#markdownContent th,
#markdownContent td {
  white-space: normal !important;
  word-break: break-word !important;
}
#markdownContent  a {
  color: #2477fdff;
  text-decoration: underline;
}
#markdownContent  a:hover {
  color: #104fb6ff;
  text-decoration: underline;
}
</style>
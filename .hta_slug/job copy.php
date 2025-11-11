<?php
$slug = $_GET['slug'] ?? '';
if (!$slug) {
  echo "<script>window.location.href = '/'</script>";
    exit;
}

// Start output buffering to capture the entire page
ob_start();

$stmt = $pdo->prepare("SELECT j.*, c.category_name FROM jobs j LEFT JOIN job_categories c ON j.category_slug = c.category_slug WHERE j.job_title_slug = ? AND j.status='published' LIMIT 1");
$stmt->execute([$slug]);
$job = $stmt->fetch();
$ogImageURIPrefix = 'https://fromcampus.com';
if (!$job) {
    // require_once __DIR__ . '/includes/header.php';
    echo "<h1>Job not found.</h1>";
    // require_once __DIR__ . '/includes/footer.php';
    exit;
}

// OVERRIDE THE META VARIABLES (these were declared in header.php)
$pageTitle = $job['meta_title'] ?: $job['job_title'] . ' - ' . APP_NAME;
$pageDescription = $job['meta_description'] ?: mb_substr(strip_tags($job['description']), 0, 160);
$keywords = "Government JOBS, ITI JOBS, Railway Jobs, Engineer, " . $job['job_title'];
$ogImage = BASE_URL . $job['thumbnail'] ? $job['thumbnail'] : "assets/logo/FromCampus_Color_text.png";
$canonicalUrl = "https://fromcampus.com/job?slug=" . $slug;
$ampHtmlCanonical = "https://fromcampus.com/amp/job?slug=".$slug;

// Get related jobs (same category, excluding current job)
$relatedJobs = [];
if ($job['category_slug']) {
    $stmt = $pdo->prepare("SELECT job_id, job_title, job_title_slug, company_name, posted_date FROM jobs WHERE category_slug = ? AND job_id != ? AND status='published' ORDER BY posted_date DESC LIMIT 5");
    $stmt->execute([$job['category_slug'], $job['job_id']]);
    $relatedJobs = $stmt->fetchAll();
}

// Get latest jobs (excluding current job)
$latestStmt = $pdo->prepare("SELECT job_id, job_title, job_title_slug, company_name, posted_date FROM jobs WHERE job_id != ? AND status='published' ORDER BY posted_date DESC LIMIT 5");
$latestStmt->execute([$job['job_id']]);
$latestJobs = $latestStmt->fetchAll();

// Prepare structured data JSON-LD (JobPosting)
$schema = [
    "@context" => "https://schema.org/",
    "@type" => "JobPosting",
    "title" => $job['job_title'] ?? "N/A",
    "description" => strip_tags($job['meta_description'] ?? $job['description'] ?? "N/A"),
    "identifier" => [
        "@type" => "PropertyValue",
        "name" => $job['company_name'] ?? "N/A",
        "value" => $job['job_id'] ?? "N/A"
    ],
    "datePosted" => !empty($job['posted_date']) ? date('c', strtotime($job['posted_date'])) : date('c'),
    "validThrough" => !empty($job['last_date']) ? date('c', strtotime($job['last_date'])) : date('c', strtotime('+30 days')),
    "employmentType" => strtoupper($job['job_type'] ?? 'FULL_TIME'),
    "hiringOrganization" => [
        "@type" => "Organization",
        "name" => $job['company_name'] ?? "N/A",
        "logo" => !empty($job['thumbnail']) ? $ogImageURIPrefix.$job['thumbnail'] : "https://fromcampus.com/assets/logo/FromCampus_Color_text.png",
        "sameAs" => "https://fromcampus.com/"
    ],
    "jobLocation" => [
        "@type" => "Place",
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => $job['location'] ?? "N/A",
            "addressRegion"   => $job['location'] ?? "N/A",
            "addressCountry"  => "IN"
        ]
    ],
    "qualifications" => strip_tags($job['requirements'] ?? "N/A"),
    "url" => BASE_URL.'/jobs/'.($job['job_title_slug'] ?? "N/A"),
    "applicationContact" => [
        "@type" => "ContactPoint",
        "url" => $job['apply_url'] ?? "N/A"
    ],
    "dateModified" => !empty($job['updated_at']) ? date('c', strtotime($job['updated_at'])) : date('c')
];

// Handle salary only if at least one value exists, else send N/A fallback
if (!empty($job['min_salary']) || !empty($job['max_salary'])) {
    $schema["baseSalary"] = [
        "@type" => "MonetaryAmount",
        "currency" => "INR",
        "value" => [
            "@type" => "QuantitativeValue",
            "minValue" => !empty($job['min_salary']) ? (int)$job['min_salary'] : "N/A",
            "maxValue" => !empty($job['max_salary']) ? (int)$job['max_salary'] : "N/A",
            "unitText" => "MONTH"
        ]
    ];
} else {
    $schema["baseSalary"] = [
        "@type" => "MonetaryAmount",
        "currency" => "INR",
        "value" => [
            "@type" => "QuantitativeValue",
            "minValue" => "N/A",
            "maxValue" => "N/A",
            "unitText" => "MONTH"
        ]
    ];
}

require_once('_header.php');
require __DIR__ . '/../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();

// Generate current page URL for sharing
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$shareTitle = urlencode($job['job_title']);
$shareText = urlencode("Check out this job opportunity: " . $job['job_title'] . " at " . $job['company_name']);
?>

<!-- Loading Placeholder (shown initially) -->
<div id="loading-placeholder" class="min-h-screen bg-gray-100 dark:bg-gray-900 p-4">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Breadcrumb placeholder -->
        <div class="lg:col-span-4">
            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
        </div>
        
        <!-- Main content placeholder -->
        <main class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <!-- Image placeholder -->
                <div class="w-full h-80 bg-gray-300 dark:bg-gray-700 animate-pulse"></div>
                
                <div class="p-6">
                    <!-- Share buttons placeholder -->
                    <div class="flex flex-wrap items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-16 animate-pulse"></div>
                        <div class="flex items-center gap-2">
                            <?php for($i = 0; $i < 6; $i++): ?>
                            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-700 rounded-full animate-pulse"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <!-- Title placeholder -->
                    <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                    <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-4 animate-pulse"></div>
                    
                    <!-- Meta info placeholder -->
                    <div class="flex items-center gap-4 mt-4">
                        <?php for($i = 0; $i < 3; $i++): ?>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-24 animate-pulse"></div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Content placeholder -->
                    <div class="mt-6 space-y-3">
                        <?php for($i = 0; $i < 10; $i++): ?>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Requirements placeholder -->
                    <div class="mt-8">
                        <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/4 mb-4 animate-pulse"></div>
                        <div class="space-y-2">
                            <?php for($i = 0; $i < 5; $i++): ?>
                            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <!-- Buttons placeholder -->
                    <div class="mt-8 flex gap-4">
                        <div class="h-12 bg-gray-300 dark:bg-gray-700 rounded w-32 animate-pulse"></div>
                        <div class="h-12 bg-gray-300 dark:bg-gray-700 rounded w-40 animate-pulse"></div>
                    </div>
                </div>
            </div>
            
            <!-- Related jobs placeholder (mobile) -->
            <div class="mt-8 lg:hidden">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-4">
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="p-4 border rounded-lg dark:border-gray-700">
                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Latest jobs placeholder (mobile) -->
            <div class="mt-8 lg:hidden">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-4">
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="p-4 border rounded-lg dark:border-gray-700">
                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
        
        <!-- Sidebar placeholder -->
        <aside class="hidden lg:block space-y-6">
            <!-- Related jobs placeholder -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-3">
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="p-3 border rounded dark:border-gray-700">
                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Latest jobs placeholder -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-3">
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="p-3 border rounded dark:border-gray-700">
                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Actual Content (hidden initially) -->
<div id="actual-content" style="display: none;">
<div class="max-w-7xl mx-auto  grid grid-cols-1 lg:grid-cols-4 gap-8">
  <!-- Breadcrumb -->
  <div class="lg:col-span-4">
    <nav class="flex px-4" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3 line-clamp-1">
        <li class="inline-flex items-center">
          <a href="<?= BASE_URL ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
            <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
            </svg>
            Home
          </a>
        </li>
        <li>
          <div class="flex items-center">
            <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <a href="<?= BASE_URL ?>?cat=<?= e($job['category_slug']) ?>" class="ml-1 text-sm line-clamp-1 font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">
              <?= e($job['category_name']) ?>
            </a>
          </div>
        </li>
        <li aria-current="page">
          <div class="flex items-center">
            <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400 line-clamp-1"><?= e($job['job_title']) ?></span>
          </div>
        </li>
      </ol>
    </nav>
  </div>

  <!-- Main Content -->
  <main class="lg:col-span-3">
    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
      <?php if ($job['thumbnail']): ?>
      <div class="w-full h-80 overflow-hidden">
        <div class="w-full aspect-[16/9] relative overflow-hidden rounded">
          <!-- blurred background from same image -->
          <div class="absolute inset-0">
            <img src="<?= e($job['thumbnail']) ?>" 
                fetchpriority="high"
                alt="" 
                class="w-full h-full object-cover blur-lg scale-110" />
          </div>

          <!-- main image (object-contain) -->
          <img src="<?= e($job['thumbnail']) ?>" 
              fetchpriority="high"
              alt="<?= e($job['job_title']) ?>" 
              class="relative w-full h-full object-contain" />
        </div>

      </div>
      <?php endif; ?>
      
      <div class="p-6">
        <!-- Share Buttons -->
        <div class="flex flex-wrap items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Share:</span>
          <div class="flex items-center gap-2">
            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition" title="Share on Facebook">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>

            <!-- WhatsApp -->
            <a href="https://wa.me/?text=<?= urlencode($shareText . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-green-500 text-white rounded-full hover:bg-green-600 transition" title="Share on WhatsApp">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.150-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.050-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
              </svg>
            </a>

            <!-- Telegram -->
             <a href="https://t.me/share/url?url=<?= urlencode($currentUrl) ?>&text=<?= urlencode($shareText) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition" title="Share on Telegram">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.999 15.169l-.398 5.601c.568 0 .812-.244 1.106-.537l2.663-2.544 5.522 4.034c1.012.559 1.731.266 1.988-.936l3.603-16.894.001-.001c.318-1.482-.535-2.06-1.516-1.702L1.502 9.75c-1.447.561-1.426 1.362-.246 1.727l5.548 1.73 12.878-8.143c.607-.367 1.162-.164.707.234"/></svg>
            </a>

            <!-- LinkedIn -->
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-700 text-white rounded-full hover:bg-blue-800 transition" title="Share on LinkedIn">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>

            <!-- Twitter -->
            <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition" title="Share on Twitter">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>

            <!-- Discord -->
            <a href="https://discord.com/channels/@me" target="_blank" rel="noopener" class="p-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition" title="Share on Discord">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.27 5.33C17.94 4.71 16.5 4.26 15 4a.09.09 0 0 0-.07.03c-.18.33-.39.76-.53 1.09a16.09 16.09 0 0 0-4.8 0c-.14-.34-.35-.76-.54-1.09c-.01-.02-.04-.03-.07-.03c-1.5.26-2.93.71-4.27 1.33c-.01 0-.02.01-.03.02C2.44 9.59 1.91 13.75 2.2 17.86c0 .02.01.04.03.05c1.62 1.21 3.59 1.93 5.58 2.22c.04.01.08 0 .1-.02c.43-.59.81-1.22 1.14-1.88c.02-.04 0-.08-.04-.09c-.57-.22-1.11-.48-1.64-.78c-.04-.02-.04-.08-.01-.11c.11-.08.22-.17.33-.25c.02-.02.05-.02.07-.01c3.44 1.57 7.15 1.57 10.55 0c.02-.01.05-.01.07.01c.11.09.22.17.33.26c.04.03.04.09-.01.11c-.52.31-1.07.56-1.64.78c-.04.01-.05.06-.04.09c.33.66.71 1.29 1.14 1.88c.02.02.06.03.10.02c2-.29 3.96-1.01 5.58-2.22c.02-.01.03-.03.03-.05c.36-4.53-.82-8.64-3.30-12.51c-.01-.02-.02-.02-.04-.02zM8.95 15.05c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.84 2.12-1.89 2.12zm6.1 0c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.83 2.12-1.89 2.12z"/>
              </svg>
            </a>

            <!-- Copy Link -->
            <button onclick="copyToClipboard(this, '<?= $currentUrl ?>')" class="p-2 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition" title="Copy link">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-xl md:text-3xl font-bold dark:text-white"><?= e($job['job_title']) ?></h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 mt-1"><?= e($job['company_name']) ?></p>
          </div>
          <?php if (strtotime($job['posted_date']) > strtotime('-7 days')): ?>
            <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-sm">New</span>
          <?php endif; ?>
        </div>
        
        <div class="flex items-center gap-2 mt-4 text-xs text-gray-600 dark:text-gray-400">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <?= e($job['location']) ?>
          </span>
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Posted <?= date('M d, Y', strtotime($job['posted_date'])) ?>
          </span>
          <?php if ($job['last_date']): ?>
          <span class="flex items-center gap-1 text-red-600 dark:text-red-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Apply by <?= date('M d, Y', strtotime($job['last_date'])) ?>
          </span>
          <?php endif; ?>
        </div>
        
        <div id="markdownContent" class="job-description mt-6 prose dark:prose-invert max-w-none text-justify leading-7"><?= $Parsedown->text($job['description']) ?></div>


        <?php if (!empty($job['requirements'])): ?>
        <div class="mt-8">
          <h3 class="text-xl font-bold dark:text-white mb-4">Requirements</h3>
          <div class="prose dark:prose-invert max-w-none">
            <?= $job['requirements'] ?>
          </div>
        </div>
        <?php endif; ?>

        
        <div class="mt-8 flex justify-between">
          <?php if (!empty($job['apply_url'])): ?>
            <a aria-label="Apply now for <?php e($job['job_title']) ?>" href="<?= e($job['apply_url']) ?>" target="_blank" rel="noopener" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
              Apply Now
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
              </svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($job['document_link'])): ?>
            <a aria-label="Documentation for <?php e($job['job_title']) ?>" href="<?= e($job['document_link']) ?>" target="_blank" rel="noopener" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
              Documentation
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
              </svg>
            </a>
          <?php endif; ?>
        </div>
        
      </div>
    </article>

    <!-- Related Jobs (Mobile - Bottom) -->
    <?php if (!empty($relatedJobs)): ?>
    <div class="mt-8 lg:hidden">
      <h2 class="text-2xl font-bold dark:text-white mb-4">Related Jobs</h2>
      <div class="space-y-4">
        <?php foreach ($relatedJobs as $rJob): ?>
        <a href="job?slug=<?= e($rJob['job_title_slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition dark:border-gray-700 dark:hover:bg-gray-800">
          <h1 class="font-semibold dark:text-white"><?= e($rJob['job_title']) ?></h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?= e($rJob['company_name']) ?></p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Posted <?= date('M d, Y', strtotime($rJob['posted_date'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Latest Jobs (Mobile - Bottom) -->
    <?php if (!empty($latestJobs)): ?>
    <div class="mt-8 lg:hidden">
      <h2 class="text-2xl font-bold dark:text-white mb-4">Latest Jobs</h2>
      <div class="space-y-4">
        <?php foreach ($latestJobs as $lJob): ?>
        <a href="job?slug=<?= e($lJob['job_title_slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition dark:border-gray-700 dark:hover:bg-gray-800">
          <h1 class="font-semibold dark:text-white"><?= e($lJob['job_title']) ?></h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?= e($lJob['company_name']) ?></p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Posted <?= date('M d, Y', strtotime($lJob['posted_date'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </main>

  <!-- Sidebar (Desktop) -->
  <aside class="hidden lg:block space-y-6">
    <!-- Related Jobs -->
    <?php if (!empty($relatedJobs)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4">Related Jobs</h2>
      <div class="space-y-3">
        <?php foreach ($relatedJobs as $rJob): ?>
        <a href="job?slug=<?= e($rJob['job_title_slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h1 class="font-medium dark:text-white line-clamp-1"><?= e($rJob['job_title']) ?></h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?= e($rJob['company_name']) ?></p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Posted <?= date('M d, Y', strtotime($rJob['posted_date'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Latest Jobs -->
    <?php if (!empty($latestJobs)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4">Latest Jobs</h2>
      <div class="space-y-3">
        <?php foreach ($latestJobs as $lJob): ?>
        <a href="job?slug=<?= e($lJob['job_title_slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h1 class="font-medium dark:text-white line-clamp-1"><?= e($lJob['job_title']) ?></h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?= e($lJob['company_name']) ?></p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Posted <?= date('M d, Y', strtotime($lJob['posted_date'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>
</div>

<script type="application/ld+json">
  <?= json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) ?>
</script>

<script>
// Show actual content and hide placeholder once page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Small delay to ensure all content is rendered
  setTimeout(function() {
    document.getElementById('loading-placeholder').style.display = 'none';
    document.getElementById('actual-content').style.display = 'block';
  }, 300);
});

function copyToClipboard(button, text) {
  navigator.clipboard.writeText(text).then(function() {
    const originalHtml = button.innerHTML;

    button.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
    button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
    button.classList.add('bg-green-600', 'hover:bg-green-700');

    setTimeout(() => {
      button.innerHTML = originalHtml;
      button.classList.remove('bg-green-600', 'hover:bg-green-700');
      button.classList.add('bg-gray-600', 'hover:bg-gray-700');
    }, 2000);
  }).catch(function(err) {
    console.error('Could not copy text: ', err);
    alert('Failed to copy link. Please try again.');
  });
}
</script>

<style>

/* Animation for placeholder */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
<?php 
  // require_once __DIR__ . '/includes/footer.php'; 
?>
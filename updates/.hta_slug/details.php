<?php

// Get slug from URL
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    echo "<script>window.location.href='/'</script>";
    exit;
}
// echo "<script>console.log('Slug: " . $slug . "');</script>";
// Fetch update details
$stmt = $pdo->prepare("SELECT * FROM updates WHERE slug = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$update = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$update) {
    echo "<h1>Update not found</h1>";
    exit;
}

// Fetch latest jobs (last 30 days)
$latestJobs = $pdo->query("SELECT job_title, job_title_slug, company_name, location, posted_date, last_date FROM jobs WHERE status='published' AND posted_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY posted_date DESC LIMIT 10")->fetchAll();

// Function to check if job is new (within last 2 days)
function isNewJob($posted_date) {
    return strtotime($posted_date) > strtotime('-2 days');
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Meta values override
$pageTitle       = ($update['meta_title'] ?? $update['title']) . ' - ' . APP_NAME;
$pageDescription = mb_substr(strip_tags($update['meta_description'] ?? $update['description']), 0, 160);
$metaTitle       = $update['meta_title'] ?? $update['title'];
$keywords        = "Exam Updates, Admit Card, Result, Govt Notice, " . $metaTitle;
$ogImage         = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$canonicalUrl    = "https://fromcampus.com/updates/details?slug=" . $slug;

$schema = [
  "@context" => "https://schema.org",
  "@type" => "NewsArticle",
  "mainEntityOfPage" => [
    "@type" => "WebPage",
    "@id" => $canonicalUrl
  ],
  "headline" => $update['meta_title'] ?? $update['title'],
  "description" => strip_tags($update['meta_description'] ?? $update['description']),
  "image" => "https://fromcampus.com/assets/logo/FromCampus_Color_text.png",
  "author" => [
    "@type" => "Organization",
    "name" => "FromCampus"
  ],
  "publisher" => [
    "@type" => "Organization",
    "name" => "FromCampus",
    "logo" => [
      "@type" => "ImageObject",
      "url" => "https://fromcampus.com/assets/logo/FromCampus_Color_text.png"
    ]
  ],
  "datePublished" => date('c', strtotime($update['created_at']))
];

if (!empty($update['updated_at'])) {
  $schema["dateModified"] = date('c', strtotime($update['updated_at']));
}

// Markdown parser for content
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();
require_once __DIR__ . '/../../.hta_slug/_header.php';
// Generate current URL
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$shareTitle = urlencode($update['title']);
$shareText  = urlencode("Check out this update: " . $update['title']);

// Fetch updates for bottom filters
$examUpdates = $pdo->query("SELECT slug, title, created_at FROM updates WHERE update_type = 'exam' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$resultUpdates = $pdo->query("SELECT slug, title, created_at FROM updates WHERE update_type = 'result' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$syllabusUpdates = $pdo->query("SELECT slug, title, created_at FROM updates WHERE update_type = 'syllabus' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$ansKeyUpdates = $pdo->query("SELECT slug, title, created_at FROM updates WHERE update_type = 'ans_key' ORDER BY created_at DESC LIMIT 4")->fetchAll();
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
                    
                    <!-- Content placeholder -->
                    <div class="mt-6 space-y-3">
                        <?php for($i = 0; $i < 10; $i++): ?>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                        <?php endfor; ?>
                    </div>
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
            <!-- Latest jobs placeholder -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-3">
                    <?php for($i = 0; $i < 5; $i++): ?>
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
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8 p-4">
  <!-- Breadcrumb -->
  <div class="lg:col-span-4">
    <nav class="flex px-4" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
          <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Home</a>
          <svg class="w-3 h-3 text-gray-400 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
        </li>
        <li class="inline-flex items-center">
          <a href="/updates" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Updates</a>
        </li>
        <li aria-current="page">
          <div class="flex items-center">
            <svg class="w-3 h-3 text-gray-400 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400"><?= e($update['title']) ?></span>
          </div>
        </li>
      </ol>
    </nav>
  </div>

  <!-- Main Content -->
  <main class="lg:col-span-3">
    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
      <div class="p-6">
        <!-- Share Buttons -->

        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"><?= e($update['title']) ?></h1>

        <!-- Date -->
        <?php if ($update['created_at']): ?>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Published on <?= date("F j, Y", strtotime($update['created_at'])) ?>
          </p>
        <?php endif; ?>

        <!-- Content -->
        <!-- <div class="prose dark:prose-invert max-w-none"><?= $Parsedown->text($update['description']) ?></div> -->
        <div id="markdownContent" class="job-description mt-6 prose dark:prose-invert max-w-none text-justify leading-7"><?= $Parsedown->text($update['description']) ?></div>
        <!-- Apply Link -->
        
          <div class="mt-8">
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
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.999 15.169l-.398 5.601c.568 0 .812-.244 1.106-.537l2.663-2.544 5.522 4.034c1.012.559 1.731.266 1.988-.936l3.603-16.894.001-.001c.318-1.482-.535-2.06-1.516-1.702L1.502 9.75c-1.447.561-1.426 1.362-.246 1.727l5.548 1.730 12.878-8.143c.607-.367 1.162-.164.707.234"/></svg>
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
                    <path d="M19.27 5.33C17.94 4.71 16.5 4.26 15 4a.09.09 0 0 0-.07.03c-.18.33-.39.76-.53 1.09a16.09 16.09 0 0 0-4.8 0c-.14-.34-.35-.76-.54-1.09c-.01-.02-.04-.03-.07-.03c-1.5.26-2.93.71-4.27 1.33c-.01 0-.02.01-.03.02C2.44 9.59 1.91 13.75 2.2 17.86c0 .02.01.04.03.05c1.62 1.21 3.59 1.93 5.58 2.22c.04.01.08 0 .10-.02c.43-.59.81-1.22 1.14-1.88c.02-.04 0-.08-.04-.09c-.57-.22-1.11-.48-1.64-.78c-.04-.02-.04-.08-.01-.11c.11-.08.22-.17.33-.25c.02-.02.05-.02.07-.01c3.44 1.57 7.15 1.57 10.55 0c.02-.01.05-.01.07.01c.11.09.22.17.33.26c.04.03.04.09-.01.11c-.52.31-1.07.56-1.64.78c-.04.01-.05.06-.04.09c.33.66.71 1.29 1.14 1.88c.02.02.06.03.10.02c2-.29 3.96-1.01 5.58-2.22c.02-.01.03-.03.03-.05c.36-4.53-.82-8.64-3.30-12.51c-.01-.02-.02-.02-.04-.02zM8.95 15.05c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.84 2.12-1.89 2.12zm6.1 0c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.83 2.12-1.89 2.12z"/>
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
          </div>
      </div>
    </article>

    <!-- Latest Jobs (Mobile - Bottom) -->
    <div class="mt-8 lg:hidden">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
            </svg>
            Latest Job Opportunities
        </h2>
        
        <div class="space-y-4">
            <?php if (empty($latestJobs)): ?>
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm">No recent job openings available</p>
                </div>
            <?php else: ?>
                <?php foreach ($latestJobs as $job): ?>
                <a href="/job?slug=<?= e($job['job_title_slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 group">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                            <?= e($job['job_title']) ?>
                        </h3>
                        <?php if (isNewJob($job['posted_date'])): ?>
                            <span class="blink-badge" style="background-color: #3b82f6;">NEW</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center text-xs text-gray-600 dark:text-gray-300 mt-1">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <?= e($job['location']) ?>
                    </div>
                    
                    <div class="flex items-center text-xs text-gray-600 dark:text-gray-300 mt-1">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                        <?= e($job['company_name']) ?>
                    </div>
                    
                    <div class="flex items-center justify-between mt-3">
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            Posted: <?= formatDate($job['posted_date']) ?>
                        </p>
                        <?php if (!empty($job['last_date'])): ?>
                            <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                                Apply by: <?= formatDate($job['last_date']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="mt-6 text-center">
            <a href="/" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                View All Jobs
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </div>
  </main>

  <!-- Sidebar (Desktop) - Latest Jobs Section -->
  <!-- Sidebar (Desktop) - Latest Jobs Section -->
  <aside class="hidden lg:block space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 h-96 flex flex-col">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
          <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
        </svg>
        Latest Jobs
      </h2>

      <!-- Scroll container -->
      <div class="relative flex-1 overflow-hidden">
        <div class="scroll-content space-y-3">
          <?php if (empty($latestJobs)): ?>
            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
              <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2 text-xs">No recent job openings</p>
            </div>
          <?php else: ?>
            <?php foreach ($latestJobs as $job): ?>
              <a href="/job?slug=<?= e($job['job_title_slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700 group">
                <div class="flex justify-between items-start mb-1">
                  <h3 class="font-medium text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                    <?= e($job['job_title']) ?>
                  </h3>
                  <?php if (isNewJob($job['posted_date'])): ?>
                    <span class="blink-badge" style="background-color: #3b82f6;">NEW</span>
                  <?php endif; ?>
                </div>
                <div class="flex items-center text-xs text-gray-600 dark:text-gray-300 mt-1">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                  <?= e($job['location']) ?>
                </div>
                <div class="flex items-center justify-between mt-2">
                  <p class="text-xs text-gray-500 dark:text-gray-500">
                    <?= formatDate($job['posted_date']) ?>
                  </p>
                  <?php if (!empty($job['last_date'])): ?>
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                      Apply by: <?= date('M d', strtotime($job['last_date'])) ?>
                    </p>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Footer fixed -->
      <div class="mt-4 text-center">
        <a href="/" class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium">
          View All Jobs
          <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </a>
      </div>
    </div>
  </aside>

</div>

<!-- Bottom Filter Sections -->
<div class="max-w-7xl mx-auto mt-12 p-4">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Exam Updates -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
        </svg>
        Exam Updates      
      </h2>
      <div class="space-y-3">
        <?php foreach ($examUpdates as $exam): ?>
        <a href="details?slug=<?= e($exam['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($exam['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?= date('M d, Y', strtotime($exam['created_at'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/updates" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All Exam Updates →
      </a>
    </div>

    <!-- Admit Card Updates -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        Result Updates
      </h2>
      <div class="space-y-3">
        <?php foreach ($resultUpdates as $result): ?>
        <a href="details?slug=<?= e($result['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($result['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?= date('M d, Y', strtotime($result['created_at'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/updates" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All Result Updates →
      </a>
    </div>

    <!-- Result Updates -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
        </svg>
        Syllabus Updates
      </h2>
      <div class="space-y-3">
        <?php foreach ($syllabusUpdates as $syllabus): ?>
        <a href="details?slug=<?= e($syllabus['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($syllabus['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?= date('M d, Y', strtotime($syllabus['created_at'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/updates" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All Syllabus Updates →
      </a>
    </div>

    <!-- Notice Updates -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        Answerkey Updates
      </h2>
      <div class="space-y-3">
        <?php foreach ($ansKeyUpdates as $ansKey): ?>
        <a href="details?slug=<?= e($ansKey['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($ansKey['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?= date('M d, Y', strtotime($ansKey['created_at'])) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/updates" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All Answerkey Updates →
      </a>
    </div>
  </div>
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

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.blink-badge {
    display: inline-block;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    border-radius: 4px;
    color: #fff;
    animation: blink 1.5s infinite;
    flex-shrink: 0;
    margin-left: 8px;
    line-height: 1.2;
}

@keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.5; }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes scrollContent {
  0%   { transform: translateY(0); }
  100% { transform: translateY(-50%); }
}

.scroll-content {
  display: flex;
  flex-direction: column;
  animation: scrollContent 25s linear infinite;
}

.scroll-content:hover {
  animation-play-state: paused;
}

</style>
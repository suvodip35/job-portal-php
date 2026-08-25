<?php
require_once __DIR__ . '/../.hta_config/functions.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: /current-affairs');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM current_affairs WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    echo "<h1>Current Affairs article not found.</h1>";
    exit;
}

// Increment view count
$updateViews = $pdo->prepare("UPDATE current_affairs SET views = views + 1 WHERE id = ?");
$updateViews->execute([$article['id']]);

// SEO Meta Variables
$pageTitle = $article['meta_title'] ?: $article['title'] . ' - Current Affairs - ' . APP_NAME;
$pageDescription = $article['meta_description'] ?: mb_substr(strip_tags($article['description']), 0, 160);
$keywords = "Current Affairs, " . ($article['category'] ?? 'News') . ", Exam Preparation, " . $article['title'];
$ogImage = !empty($article['thumbnail']) ? BASE_URL . ltrim($article['thumbnail'], '/') : BASE_URL . "assets/logo/fc_logo_crop.webp";
$canonicalUrl = BASE_URL . "current-affairs/" . $slug;

// Related Articles (same category, excluding current article)
$relatedArticles = [];
if (!empty($article['category'])) {
    $relStmt = $pdo->prepare("SELECT id, title, slug, category, event_date, thumbnail, created_at FROM current_affairs WHERE category = ? AND id != ? AND status = 'published' ORDER BY event_date DESC, created_at DESC LIMIT 5");
    $relStmt->execute([$article['category'], $article['id']]);
    $relatedArticles = $relStmt->fetchAll();
}

// If no same-category related articles, fallback to general related articles
if (empty($relatedArticles)) {
    $relStmt = $pdo->prepare("SELECT id, title, slug, category, event_date, thumbnail, created_at FROM current_affairs WHERE id != ? AND status = 'published' ORDER BY event_date DESC, created_at DESC LIMIT 5");
    $relStmt->execute([$article['id']]);
    $relatedArticles = $relStmt->fetchAll();
}

// Latest Articles (excluding current article)
$latestStmt = $pdo->prepare("SELECT id, title, slug, category, event_date, thumbnail, created_at FROM current_affairs WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 5");
$latestStmt->execute([$article['id']]);
$latestArticles = $latestStmt->fetchAll();

// Categories list for sidebar navigation
$categories = ['National', 'International', 'Sports', 'Economy', 'Science & Tech', 'Appointments', 'Obituaries', 'Awards & Honours'];

// NewsArticle Schema JSON-LD
$schema = [
    "@context" => "https://schema.org",
    "@type" => "NewsArticle",
    "headline" => $article['title'],
    "image" => [$ogImage],
    "datePublished" => date('c', strtotime($article['created_at'])),
    "dateModified" => date('c', strtotime($article['updated_at'] ?? $article['created_at'])),
    "author" => [
        "@type" => "Organization",
        "name" => APP_NAME,
        "url" => BASE_URL
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => APP_NAME,
        "logo" => [
            "@type" => "ImageObject",
            "url" => BASE_URL . "assets/logo/fc_logo_crop.webp"
        ]
    ],
    "description" => $pageDescription
];

// Clean description to avoid repeating article title if description starts with ### Title
$cleanDescription = $article['description'];
if (preg_match('/^#+\s+' . preg_quote($article['title'], '/') . '/i', trim($cleanDescription))) {
    $cleanDescription = preg_replace('/^#+\s+.*?\n+/i', '', trim($cleanDescription));
}

// Estimate Reading Time helper
if (!function_exists('estimate_reading_time')) {
    function estimate_reading_time($text) {
        $clean = strip_tags($text);
        $words = str_word_count($clean);
        $minutes = max(1, (int)ceil($words / 180));
        return $minutes . ' min read';
    }
}

// Category Linear-Gradient Style Generator
if (!function_exists('get_category_style')) {
    function get_category_style($cat) {
        switch ($cat) {
            case 'Science & Tech':
                return 'background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #0284c7 100%); color: #ffffff;';
            case 'Economy':
                return 'background: linear-gradient(135deg, #065f46 0%, #059669 50%, #0d9488 100%); color: #ffffff;';
            case 'National':
                return 'background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #3b82f6 100%); color: #ffffff;';
            case 'International':
                return 'background: linear-gradient(135deg, #0369a1 0%, #0284c7 50%, #2563eb 100%); color: #ffffff;';
            case 'Sports':
                return 'background: linear-gradient(135deg, #9a3412 0%, #d97706 50%, #ea580c 100%); color: #ffffff;';
            case 'Awards & Honours':
                return 'background: linear-gradient(135deg, #6b21a8 0%, #8b5cf6 50%, #d946ef 100%); color: #ffffff;';
            default:
                return 'background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #0284c7 100%); color: #ffffff;';
        }
    }
}

// Case-insensitive valid thumbnail inspector
if (!function_exists('is_valid_thumbnail')) {
    function is_valid_thumbnail($thumb) {
        if (empty($thumb)) return false;
        $thumbLower = strtolower($thumb);
        if (strpos($thumbLower, 'logo') !== false) return false;
        if (strpos($thumbLower, 'fc_') !== false) return false;
        if (strpos($thumbLower, 'fromcampus') !== false) return false;
        return true;
    }
}

$headerStyle = get_category_style($article['category'] ?? 'General');
$hasCustomImage = is_valid_thumbnail($article['thumbnail']);
$readTime = estimate_reading_time($article['description']);

require_once('_header.php');
require_once __DIR__ . '/../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();

$shareText = urlencode("Check out this current affairs article: " . $article['title']);
?>

<!-- NewsArticle Schema JSON-LD -->
<script type="application/ld+json">
<?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<!-- Main Page Layout Matching job.php 1:1 -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-1 lg:grid-cols-4 gap-8">
    
    <!-- Breadcrumb (Spans Full 4 Cols, Matching job.php 1:1) -->
    <div class="lg:col-span-4">
        <nav class="flex px-1" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 line-clamp-1 text-sm font-medium">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>" title="Home Page" class="inline-flex items-center text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                        </svg>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 6 10">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <a href="/current-affairs" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">Current Affairs</a>
                    </div>
                </li>
                <?php if (!empty($article['category'])): ?>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 6 10">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <a href="/current-affairs?cat=<?= urlencode($article['category']) ?>" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white"><?= e($article['category']) ?></a>
                        </div>
                    </li>
                <?php endif; ?>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 6 10">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2 dark:text-gray-400 line-clamp-1"><?= e($article['title']) ?></span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Main Content Article (lg:col-span-3 matching job.php) -->
    <main class="lg:col-span-3">
        <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            
            <!-- Hero Image Header (Matching job.php aspect-[16/9] blur background) -->
            <?php if ($hasCustomImage): ?>
                <div class="w-full h-80 overflow-hidden">
                    <div class="w-full aspect-[16/9] relative overflow-hidden rounded">
                        <div class="absolute inset-0 bg-cover bg-center blur-lg scale-110" style="background-image: url('<?= e($article['thumbnail']) ?>');"></div>
                        <img src="<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" width="800" height="450" loading="eager" fetchpriority="high" class="relative w-full h-full object-contain" />
                    </div>
                </div>
            <?php else: ?>
                <div class="w-full aspect-[16/9] max-h-[220px] relative overflow-hidden p-6 flex flex-col justify-between" style="aspect-ratio: 16/9; <?= $headerStyle ?>">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 bg-black/30 backdrop-blur rounded text-xs font-bold uppercase border border-white/20">
                            <?= e($article['category'] ?? 'General') ?>
                        </span>
                        <span class="text-xs font-semibold text-white/90 drop-shadow"><?= $readTime ?></span>
                    </div>
                    <div class="text-xs text-white/80 font-medium">
                        Posted <?= date('M d, Y', strtotime($article['event_date'] ?? $article['created_at'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="p-6">
                
                <!-- Share Buttons (7 Buttons Matching job.php 1:1) -->
                <div class="flex flex-wrap items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Share:</span>
                    <div class="flex items-center gap-2">
                        
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition" title="Share on Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://wa.me/?text=<?= urlencode($shareText . ' ' . $canonicalUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-green-500 text-white rounded-full hover:bg-green-600 transition" title="Share on WhatsApp">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.050-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
                            </svg>
                        </a>

                        <!-- Telegram -->
                        <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($shareText) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition" title="Share on Telegram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.999 15.169l-.398 5.601c.568 0 .812-.244 1.106-.537l2.663-2.544 5.522 4.034c1.012.559 1.731.266 1.988-.936l3.603-16.894.001-.001c.318-1.482-.535-2.06-1.516-1.702L1.502 9.75c-1.447.561-1.426 1.362-.246 1.727l5.548 1.73 12.878-8.143c.607-.367 1.162-.164.707.234"/></svg>
                        </a>

                        <!-- LinkedIn -->
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-700 text-white rounded-full hover:bg-blue-800 transition" title="Share on LinkedIn">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>

                        <!-- Twitter -->
                        <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition" title="Share on Twitter">
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

                        <!-- Copy Link Button with Toast Notification -->
                        <button onclick="copyToClipboard(this, '<?= $canonicalUrl ?>')" class="p-2 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition" title="Copy link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Article Main Title & Subtitle (Matching job.php 1:1) -->
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold dark:text-white"><?= e($article['title']) ?></h1>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mt-1">
                            <?= e($article['category'] ?? 'General') ?> Current Affairs
                        </p>
                    </div>
                    <?php if (strtotime($article['event_date'] ?? $article['created_at']) > strtotime('-7 days')): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-sm">New</span>
                    <?php endif; ?>
                </div>
                
                <!-- Meta Info Row (Matching job.php 1:1) -->
                <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-gray-600 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 pb-4">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <?= e($article['category'] ?? 'General') ?>
                    </span>

                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Posted <?= date('M d, Y', strtotime($article['event_date'] ?? $article['created_at'])) ?>
                    </span>
                    
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?= $readTime ?>
                    </span>

                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <?= number_format($article['views']) ?> views
                    </span>
                </div>

                <!-- PDF Notes Callout (if PDF link exists) -->
                <?php if (!empty($article['pdf_link'])): ?>
                    <div class="mt-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-red-600 text-white rounded-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-red-900 dark:text-red-200 text-sm">Free PDF Study Notes Available</h3>
                                <p class="text-xs text-red-700 dark:text-red-300">Download official reference PDF for exam preparation.</p>
                            </div>
                        </div>
                        <a href="<?= e($article['pdf_link']) ?>" target="_blank" rel="noopener" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold text-xs shadow transition">
                            Download PDF
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Markdown Content Body (Matching job.php 1:1) -->
                <div id="markdownContent" class="job-description mt-6 prose dark:prose-invert max-w-none leading-7 text-gray-800 dark:text-gray-200">
                    <?= $Parsedown->text($cleanDescription) ?>
                </div>

            </div>
        </article>
    </main>

    <!-- Sidebar (Desktop Right lg:col-span-1 matching job.php 1:1) -->
    <aside class="hidden lg:block space-y-6">
        
        <!-- Related Current Affairs Card (Same Category) -->
        <?php if (!empty($relatedArticles)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <h2 class="text-xl font-bold dark:text-white mb-4">Related Current Affairs</h2>
                <div class="space-y-3">
                    <?php foreach ($relatedArticles as $rArt): ?>
                        <a href="/current-affairs/<?= e($rArt['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
                            <h3 class="font-medium dark:text-white line-clamp-2 text-sm leading-snug"><?= e($rArt['title']) ?></h3>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1"><?= e($rArt['category']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Posted <?= date('M d, Y', strtotime($rArt['event_date'] ?? $rArt['created_at'])) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Latest Current Affairs Card -->
        <?php if (!empty($latestArticles)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <h2 class="text-xl font-bold dark:text-white mb-4">Latest Current Affairs</h2>
                <div class="space-y-3">
                    <?php foreach ($latestArticles as $lArt): ?>
                        <a href="/current-affairs/<?= e($lArt['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
                            <h3 class="font-medium dark:text-white line-clamp-2 text-sm leading-snug"><?= e($lArt['title']) ?></h3>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1"><?= e($lArt['category']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Posted <?= date('M d, Y', strtotime($lArt['event_date'] ?? $lArt['created_at'])) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </aside>

    <!-- Mobile Bottom Related Section (Matching job.php 1:1) -->
    <?php if (!empty($relatedArticles)): ?>
        <div class="mt-8 lg:hidden lg:col-span-4">
            <h2 class="text-2xl font-bold dark:text-white mb-4">Related Current Affairs</h2>
            <div class="space-y-4">
                <?php foreach ($relatedArticles as $rArt): ?>
                    <a href="/current-affairs/<?= e($rArt['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition dark:border-gray-700 dark:hover:bg-gray-800">
                        <h3 class="font-semibold dark:text-white text-base"><?= e($rArt['title']) ?></h3>
                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-1"><?= e($rArt['category']) ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Posted <?= date('M d, Y', strtotime($rArt['event_date'] ?? $rArt['created_at'])) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
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

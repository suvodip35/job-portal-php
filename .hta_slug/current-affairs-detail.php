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

// Increment view count asynchronously/safely
$updateViews = $pdo->prepare("UPDATE current_affairs SET views = views + 1 WHERE id = ?");
$updateViews->execute([$article['id']]);

// SEO Meta Variables
$pageTitle = $article['meta_title'] ?: $article['title'] . ' - Current Affairs - ' . APP_NAME;
$pageDescription = $article['meta_description'] ?: mb_substr(strip_tags($article['description']), 0, 160);
$keywords = "Current Affairs, " . ($article['category'] ?? 'News') . ", Exam Preparation, " . $article['title'];
$ogImage = !empty($article['thumbnail']) ? BASE_URL . ltrim($article['thumbnail'], '/') : BASE_URL . "assets/logo/fc_logo_crop.webp";
$canonicalUrl = BASE_URL . "current-affairs/" . $slug;

// Related Articles
$relStmt = $pdo->prepare("SELECT id, title, slug, category, event_date, thumbnail FROM current_affairs WHERE id != ? AND status = 'published' ORDER BY event_date DESC, created_at DESC LIMIT 4");
$relStmt->execute([$article['id']]);
$relatedArticles = $relStmt->fetchAll();

// Trending Sidebar Articles
$trendingArticles = cache_get_or_set('ca_detail_sidebar_trending', 300, function() use ($pdo, $article) {
    try {
        $tStmt = $pdo->prepare("SELECT id, title, slug, category, event_date, thumbnail, views FROM current_affairs WHERE id != ? AND status = 'published' ORDER BY views DESC, created_at DESC LIMIT 5");
        $tStmt->execute([$article['id']]);
        return $tStmt->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }
});

// Categories list for sidebar
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
?>

<!-- Include NewsArticle JSON-LD -->
<script type="application/ld+json">
<?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<div class="w-full max-w-7xl mx-auto space-y-5">
    
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 dark:text-gray-400 overflow-x-auto no-scrollbar py-0.5" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center text-xs font-semibold text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg width="14" height="14" style="width: 14px; height: 14px;" class="mr-1.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg width="12" height="12" style="width: 12px; height: 12px;" class="text-gray-400 mx-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <a href="/current-affairs" class="text-xs font-semibold text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Current Affairs</a>
                </div>
            </li>
            <?php if (!empty($article['category'])): ?>
                <li>
                    <div class="flex items-center">
                        <svg width="12" height="12" style="width: 12px; height: 12px;" class="text-gray-400 mx-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <a href="/current-affairs?cat=<?= urlencode($article['category']) ?>" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline"><?= e($article['category']) ?></a>
                    </div>
                </li>
            <?php endif; ?>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg width="12" height="12" style="width: 12px; height: 12px;" class="text-gray-400 mx-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 line-clamp-1 max-w-xs sm:max-w-md"><?= e($article['title']) ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Main Content Layout (Flex Article Left + Sidebar Right) -->
    <div class="flex flex-col md:flex-row gap-6 items-start">
        
        <!-- Left Column: Article Container (w-full md:w-2/3) -->
        <div class="w-full md:w-2/3 min-w-0">
            <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700/80 overflow-hidden mb-6">
                
                <!-- Hero Banner Header -->
                <div class="w-full aspect-[16/9] max-h-[340px] relative overflow-hidden p-5 sm:p-6 md:p-8 flex flex-col justify-between" style="aspect-ratio: 16/9; <?= $headerStyle ?>">
                    <?php if ($hasCustomImage): ?>
                        <img src="<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" width="800" height="450" loading="eager" fetchpriority="high" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>
                    <?php endif; ?>

                    <div class="relative z-10 flex items-center justify-between gap-3">
                        <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-blue-600 text-white shadow">
                            <?= e($article['category'] ?? 'National') ?>
                        </span>

                        <div class="flex items-center gap-2.5 text-xs font-semibold text-white/90 drop-shadow">
                            <span class="flex items-center gap-1">
                                <svg width="13" height="13" style="width: 13px; height: 13px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <?= $article['event_date'] ? date('M d, Y', strtotime($article['event_date'])) : date('M d, Y', strtotime($article['created_at'])) ?>
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <svg width="13" height="13" style="width: 13px; height: 13px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?= $readTime ?>
                            </span>
                        </div>
                    </div>

                    <!-- Title Banner if no photo -->
                    <?php if (!$hasCustomImage): ?>
                        <div class="relative z-10 my-auto py-2">
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white leading-tight drop-shadow tracking-tight">
                                <?= e($article['title']) ?>
                            </h1>
                        </div>
                    <?php endif; ?>

                    <div class="relative z-10 flex items-center justify-between text-xs text-white/90 font-medium pt-2 border-t border-white/10">
                        <span class="flex items-center gap-1.5">
                            <svg width="15" height="15" style="width: 15px; height: 15px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <?= number_format($article['views']) ?> Views
                        </span>
                        <span class="bg-black/30 backdrop-blur px-2 py-0.5 rounded text-[11px]">Exam Prep Special</span>
                    </div>
                </div>

                <div class="p-5 sm:p-6 md:p-7">
                    <?php if ($hasCustomImage): ?>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight mb-5">
                            <?= e($article['title']) ?>
                        </h1>
                    <?php endif; ?>

                    <!-- Social Share Buttons Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 sm:p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700/80 mb-6">
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                            <svg width="15" height="15" style="width: 15px; height: 15px;" class="text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            Share Article:
                        </span>

                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' ' . $canonicalUrl) ?>" target="_blank" rel="noopener" class="ca-btn-share ca-btn-whatsapp">
                                WhatsApp
                            </a>

                            <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="ca-btn-share ca-btn-telegram">
                                Telegram
                            </a>

                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="ca-btn-share ca-btn-facebook">
                                Facebook
                            </a>

                            <button onclick="navigator.clipboard.writeText('<?= $canonicalUrl ?>'); alert('Article link copied to clipboard!');" class="ca-btn-share ca-btn-copy">
                                Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- PDF Download Callout -->
                    <?php if (!empty($article['pdf_link'])): ?>
                        <div class="p-4 sm:p-5 rounded-2xl text-white shadow-md mb-6 flex flex-col sm:flex-row items-center justify-between gap-3" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0" style="width: 40px; height: 40px;">
                                    <svg width="22" height="22" style="width: 22px; height: 22px;" class="text-white shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm sm:text-base">Download Free PDF Study Notes</h3>
                                    <p class="text-xs text-red-100 mt-0.5">Download official current affairs PDF reference for revision & competitive exams.</p>
                                </div>
                            </div>
                            <a href="<?= e($article['pdf_link']) ?>" target="_blank" rel="noopener" class="w-full sm:w-auto px-5 py-2 bg-white hover:bg-red-50 text-red-700 rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 shadow transition shrink-0">
                                <svg width="15" height="15" style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download PDF Notes
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Key Exam Takeaways Highlight Box -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/70 dark:bg-gray-900/60 border-l-4 border-blue-600 dark:border-blue-500 mb-6">
                        <h3 class="text-xs sm:text-sm font-extrabold text-blue-900 dark:text-blue-300 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            ⚡ Quick Summary / Key Exam Highlights
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            <?= e($pageDescription) ?>
                        </p>
                    </div>

                    <!-- Main Markdown Article Body -->
                    <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 leading-relaxed text-sm sm:text-base border-t border-gray-100 dark:border-gray-700/80 pt-5" id="markdownContent">
                        <?= $Parsedown->text($cleanDescription) ?>
                    </div>
                </div>
            </article>

            <!-- Related Current Affairs Grid -->
            <?php if (!empty($relatedArticles)): ?>
                <div class="mt-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <span class="w-2 h-4 bg-blue-600 rounded-full inline-block"></span>
                        Related Current Affairs
                    </h2>
                    
                    <div class="grid sm:grid-cols-2 gap-4">
                        <?php foreach ($relatedArticles as $rel): 
                            $relStyle = get_category_style($rel['category'] ?? 'General');
                            $relHasImage = is_valid_thumbnail($rel['thumbnail']);
                        ?>
                            <a href="/current-affairs/<?= e($rel['slug']) ?>" class="group block p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 hover:border-blue-500/50 hover:shadow-md transition">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="px-2 py-0.5 text-[10px] bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 rounded font-bold uppercase tracking-wider">
                                        <?= e($rel['category'] ?? 'General') ?>
                                    </span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                        <?= date('M d, Y', strtotime($rel['event_date'] ?? $rel['created_at'])) ?>
                                    </span>
                                </div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    <?= e($rel['title']) ?>
                                </h3>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Sidebar (w-full md:w-1/3) -->
        <aside class="w-full md:w-1/3 shrink-0 space-y-5">
            
            <!-- Trending GK Sidebar Card -->
            <?php if (!empty($trendingArticles)): ?>
                <div class="p-4 sm:p-5 border rounded-2xl bg-white dark:bg-gray-800 shadow-sm border-gray-200 dark:border-gray-700/80">
                    <h3 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider pb-2.5 mb-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-1.5">
                        <span class="text-blue-600 dark:text-blue-400">🔥</span> Popular GK Articles
                    </h3>

                    <div class="space-y-3">
                        <?php foreach ($trendingArticles as $rank => $tArticle): ?>
                            <a href="/current-affairs/<?= e($tArticle['slug']) ?>" class="group flex items-start gap-2.5 p-1.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 font-extrabold text-[11px] flex items-center justify-center shrink-0 mt-0.5" style="width: 20px; height: 20px;">
                                    <?= $rank + 1 ?>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                        <?= e($tArticle['category'] ?? 'General') ?>
                                    </span>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug mt-0.5">
                                        <?= e($tArticle['title']) ?>
                                    </h4>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 block">
                                        <?= date('M d, Y', strtotime($tArticle['event_date'] ?? $tArticle['created_at'])) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Push Notification Card -->
            <div class="p-5 sm:p-6 rounded-2xl text-white shadow-md relative overflow-hidden" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);">
                <div class="relative z-10">
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-2.5" style="width: 36px; height: 36px;">
                        <svg width="18" height="18" style="width: 18px; height: 18px;" class="text-white shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white mb-1">Get Daily GK Alerts</h3>
                    <p class="text-xs text-blue-100 mb-3.5 leading-relaxed">
                        Stay ahead in competitive exams with real-time push notifications for daily current affairs.
                    </p>
                    <button onclick="if(typeof subscribeToPushNotifications==='function'){ subscribeToPushNotifications(this); }" class="w-full py-2 bg-white hover:bg-blue-50 text-blue-800 rounded-xl font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                        Enable Notifications
                    </button>
                </div>
            </div>

            <!-- Categories Navigator -->
            <div class="p-4 sm:p-5 border rounded-2xl bg-white dark:bg-gray-800 shadow-sm border-gray-200 dark:border-gray-700/80">
                <h3 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider mb-2.5 pb-2 border-b border-gray-100 dark:border-gray-700">
                    Browse GK Categories
                </h3>
                <ul class="space-y-1 text-xs font-semibold">
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="flex items-center justify-between p-1.5 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                    <?= e($cat) ?>
                                </span>
                                <svg width="13" height="13" style="width: 13px; height: 13px;" class="text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </aside>
    </div>

</div>

<?php
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

// Category Linear-Gradient Style Generator (Direct CSS for 100% Reliability)
if (!function_exists('get_category_style')) {
    function get_category_style($cat) {
        switch ($cat) {
            case 'Science & Tech':
                return 'background: linear-gradient(135deg, #2563eb 0%, #4f46e5 50%, #0891b2 100%); color: #ffffff;';
            case 'Economy':
                return 'background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%); color: #ffffff;';
            case 'National':
                return 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #2563eb 100%); color: #ffffff;';
            case 'International':
                return 'background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 50%, #4338ca 100%); color: #ffffff;';
            case 'Sports':
                return 'background: linear-gradient(135deg, #d97706 0%, #ea580c 50%, #dc2626 100%); color: #ffffff;';
            case 'Awards & Honours':
                return 'background: linear-gradient(135deg, #9333ea 0%, #db2777 50%, #e11d48 100%); color: #ffffff;';
            default:
                return 'background: linear-gradient(135deg, #2563eb 0%, #4f46e5 50%, #7c3aed 100%); color: #ffffff;';
        }
    }
}

$headerStyle = get_category_style($article['category'] ?? 'General');
$hasCustomImage = !empty($article['thumbnail']) && strpos($article['thumbnail'], 'fc_logo') === false && strpos($article['thumbnail'], 'logo') === false;

require_once('_header.php');
require_once __DIR__ . '/../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();
?>

<!-- Include NewsArticle JSON-LD -->
<script type="application/ld+json">
<?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 md:py-10">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 dark:text-gray-400 mb-6 overflow-x-auto no-scrollbar py-1" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <a href="/current-affairs" class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Current Affairs</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 line-clamp-1 max-w-xs sm:max-w-md"><?= e($article['title']) ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Main Article Container -->
    <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700/80 overflow-hidden">
        
        <!-- Hero Header Banner -->
        <div class="w-full aspect-[16/9] max-h-[360px] relative overflow-hidden p-6 md:p-10 flex flex-col justify-between" style="aspect-ratio: 16/9; <?= $headerStyle ?>">
            <?php if ($hasCustomImage): ?>
                <img src="<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" width="800" height="450" loading="eager" fetchpriority="high" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
            <?php endif; ?>

            <div class="relative z-10 flex items-center justify-between gap-3">
                <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-black/40 text-white backdrop-blur border border-white/20 shadow">
                    <?= e($article['category'] ?? 'National') ?>
                </span>
                <div class="flex items-center gap-4 text-xs font-semibold text-white/90 drop-shadow">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <?= $article['event_date'] ? date('F d, Y', strtotime($article['event_date'])) : date('F d, Y', strtotime($article['created_at'])) ?>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <?= number_format($article['views']) ?> views
                    </span>
                </div>
            </div>

            <!-- Title in Hero Banner if no photo -->
            <?php if (!$hasCustomImage): ?>
                <div class="relative z-10 my-auto">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white leading-tight drop-shadow tracking-tight">
                        <?= e($article['title']) ?>
                    </h1>
                </div>
            <?php endif; ?>
        </div>

        <div class="p-6 md:p-8">
            <?php if ($hasCustomImage): ?>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6">
                    <?= e($article['title']) ?>
                </h1>
            <?php endif; ?>

            <!-- Share Buttons Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700/80 mb-6">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Share Article:
                </span>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' ' . $canonicalUrl) ?>" target="_blank" rel="noopener" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition shadow-sm" style="padding: 8px 16px !important; border-radius: 8px !important; background-color: #16a34a !important; color: #ffffff !important; text-decoration: none !important; min-height: 38px !important; display: inline-flex !important; align-items: center !important;">
                        WhatsApp
                    </a>
                    <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition shadow-sm" style="padding: 8px 16px !important; border-radius: 8px !important; background-color: #0284c7 !important; color: #ffffff !important; text-decoration: none !important; min-height: 38px !important; display: inline-flex !important; align-items: center !important;">
                        Telegram
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition shadow-sm" style="padding: 8px 16px !important; border-radius: 8px !important; background-color: #1d4ed8 !important; color: #ffffff !important; text-decoration: none !important; min-height: 38px !important; display: inline-flex !important; align-items: center !important;">
                        Facebook
                    </a>
                    <button onclick="navigator.clipboard.writeText('<?= $canonicalUrl ?>'); alert('Link copied to clipboard!');" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition shadow-sm border-none cursor-pointer" style="padding: 8px 16px !important; border-radius: 8px !important; background-color: #4b5563 !important; color: #ffffff !important; min-height: 38px !important; cursor: pointer !important; display: inline-flex !important; align-items: center !important;">
                        Copy Link
                    </button>
                </div>
            </div>

            <!-- PDF Download Box -->
            <?php if (!empty($article['pdf_link'])): ?>
                <div class="p-4 bg-red-50 dark:bg-gray-900 border-l-4 border-red-600 rounded-r-xl mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base">Download Free PDF Notes</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Download official study notes & PDF reference material for revision.</p>
                        </div>
                    </div>
                    <a href="<?= e($article['pdf_link']) ?>" target="_blank" rel="noopener" class="w-full sm:w-auto px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PDF
                    </a>
                </div>
            <?php endif; ?>

            <!-- Article Content -->
            <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 leading-relaxed text-base sm:text-lg" id="markdownContent">
                <?= $Parsedown->text($cleanDescription) ?>
            </div>
        </div>
    </article>

    <!-- Related Current Affairs -->
    <?php if (!empty($relatedArticles)): ?>
        <div class="mt-10">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Current Affairs</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <?php foreach ($relatedArticles as $rel): 
                    $relStyle = get_category_style($rel['category'] ?? 'General');
                    $relHasImage = !empty($rel['thumbnail']) && strpos($rel['thumbnail'], 'fc_logo') === false && strpos($rel['thumbnail'], 'logo') === false;
                ?>
                    <a href="/current-affairs/<?= e($rel['slug']) ?>" class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500/50 hover:shadow-md transition flex items-center gap-4 group">
                        <?php if ($relHasImage): ?>
                            <img src="<?= e($rel['thumbnail']) ?>" alt="" width="64" height="64" loading="lazy" decoding="async" class="w-16 h-16 object-cover rounded-lg shrink-0 group-hover:scale-105 transition duration-300">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-lg flex items-center justify-center shrink-0 shadow-sm" style="<?= $relStyle ?>">
                                <img src="/assets/logo/fc_logo_crop.webp" alt="" width="28" height="28" class="w-7 h-7 object-contain filter brightness-0 invert">
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="px-2 py-0.5 text-[10px] bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 rounded-full font-bold uppercase tracking-wider"><?= e($rel['category'] ?? 'General') ?></span>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-2 mt-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"><?= e($rel['title']) ?></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= date('M d, Y', strtotime($rel['event_date'] ?? $rel['created_at'])) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

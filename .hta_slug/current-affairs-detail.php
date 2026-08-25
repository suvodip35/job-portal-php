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
?>

<!-- Include NewsArticle JSON-LD -->
<script type="application/ld+json">
<?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
        <a href="/" class="hover:text-blue-600">Home</a>
        <span>/</span>
        <a href="/current-affairs" class="hover:text-blue-600">Current Affairs</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs"><?= e($article['title']) ?></span>
    </nav>

    <!-- Article Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                <?= e($article['category'] ?? 'National') ?>
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                Event Date: <?= $article['event_date'] ? date('F d, Y', strtotime($article['event_date'])) : date('F d, Y', strtotime($article['created_at'])) ?>
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <?= number_format($article['views']) ?> views
            </span>
        </div>

        <h1 class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white leading-tight mb-4">
            <?= e($article['title']) ?>
        </h1>
    </div>

    <!-- Featured Image -->
    <?php if (!empty($article['thumbnail'])): ?>
        <div class="mb-8 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 max-h-[450px]">
            <img src="<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" class="w-full h-full object-cover">
        </div>
    <?php endif; ?>

    <!-- PDF Download Banner -->
    <?php if (!empty($article['pdf_link'])): ?>
        <div class="mb-8 p-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 rounded-r-xl flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Download PDF Notes</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-300">Official PDF document for offline study and revision.</p>
                </div>
            </div>
            <a href="<?= e($article['pdf_link']) ?>" target="_blank" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition shadow-md text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
        </div>
    <?php endif; ?>

    <!-- Article Content -->
    <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 leading-relaxed mb-10 text-base" id="markdownContent">
        <?= Parsedown::instance()->text($article['description']) ?>
    </div>

    <!-- Social Share Buttons -->
    <div class="border-t border-b border-gray-200 dark:border-gray-700 py-4 my-8 flex items-center justify-between flex-wrap gap-4">
        <span class="font-semibold text-gray-700 dark:text-gray-300 text-sm">Share this Article:</span>
        <div class="flex items-center gap-3">
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $canonicalUrl) ?>" target="_blank" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg font-medium flex items-center gap-1.5 hover:bg-green-700">
                WhatsApp
            </a>
            <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="px-3 py-1.5 bg-blue-500 text-white text-xs rounded-lg font-medium flex items-center gap-1.5 hover:bg-blue-600">
                Telegram
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" class="px-3 py-1.5 bg-blue-700 text-white text-xs rounded-lg font-medium flex items-center gap-1.5 hover:bg-blue-800">
                Facebook
            </a>
        </div>
    </div>

    <!-- Related Current Affairs -->
    <?php if (!empty($relatedArticles)): ?>
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Related Current Affairs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($relatedArticles as $rel): ?>
                    <a href="/current-affairs/<?= e($rel['slug']) ?>" class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition flex items-center gap-4">
                        <?php if (!empty($rel['thumbnail'])): ?>
                            <img src="<?= e($rel['thumbnail']) ?>" alt="" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                        <?php endif; ?>
                        <div>
                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400"><?= e($rel['category'] ?? 'General') ?></span>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-2 mt-0.5"><?= e($rel['title']) ?></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= date('M d, Y', strtotime($rel['event_date'] ?? $rel['created_at'])) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

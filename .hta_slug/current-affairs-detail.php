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

require_once('_header.php');
require_once __DIR__ . '/../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();
?>

<!-- Include NewsArticle JSON-LD -->
<script type="application/ld+json">
<?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<div class="ca-container" style="max-width: 960px;">
    <!-- Breadcrumb -->
    <nav style="display: flex; align-items: center; gap: 0.5rem; font-size: 13px; color: #6b7280; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 4px;" class="no-scrollbar">
        <a href="/" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <span>/</span>
        <a href="/current-affairs" style="color: inherit; text-decoration: none;">Current Affairs</a>
        <span>/</span>
        <span style="color: #111827; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px;" class="dark:text-white"><?= e($article['title']) ?></span>
    </nav>

    <!-- Article Main Header -->
    <header style="margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
            <span style="padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background-color: #dbeafe; color: #1e40af;">
                <?= e($article['category'] ?? 'National') ?>
            </span>
            <span style="font-size: 13px; color: #6b7280; display: inline-flex; align-items: center; gap: 0.375rem;">
                <svg style="width: 14px; height: 14px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= $article['event_date'] ? date('F d, Y', strtotime($article['event_date'])) : date('F d, Y', strtotime($article['created_at'])) ?>
            </span>
            <span style="font-size: 13px; color: #6b7280; display: inline-flex; align-items: center; gap: 0.375rem; margin-left: auto;">
                <svg style="width: 14px; height: 14px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <?= number_format($article['views']) ?> views
            </span>
        </div>

        <h1 style="font-size: 2rem; font-weight: 800; line-height: 1.25; color: #111827; margin-bottom: 1rem;" class="dark:text-white">
            <?= e($article['title']) ?>
        </h1>
    </header>

    <!-- Featured Banner Container -->
    <?php if (!empty($article['thumbnail'])): ?>
        <div style="margin-bottom: 2rem; border-radius: 1rem; overflow: hidden; border: 1px solid #e5e7eb; max-height: 320px; width: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #2563eb, #4f46e5);">
            <?php if (strpos($article['thumbnail'], 'logo') === false): ?>
                <img src="<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" width="800" height="450" fetchpriority="high" decoding="async" style="width: 100%; height: 100%; max-height: 320px; object-fit: cover;">
            <?php else: ?>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; color: #ffffff;">
                    <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="64" height="64" style="width: 64px; height: 64px; object-fit: contain; filter: brightness(0) invert(1);">
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 8px; opacity: 0.85;">FromCampus Current Affairs</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- PDF Download Banner -->
    <?php if (!empty($article['pdf_link'])): ?>
        <div style="margin-bottom: 2rem; padding: 1.25rem; background-color: #fef2f2; border-left: 4px solid #dc2626; border-radius: 0.75rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;" class="dark:bg-gray-800">
            <div style="display: flex; align-items: center; gap: 0.875rem;">
                <div style="width: 40px; height: 40px; border-radius: 0.5rem; background-color: #dc2626; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                </div>
                <div>
                    <h3 style="font-weight: 700; color: #111827; font-size: 1rem; margin: 0;" class="dark:text-white">Download Free PDF Notes</h3>
                    <p style="font-size: 12px; color: #4b5563; margin-top: 2px;" class="dark:text-gray-300">Download official study notes & PDF reference material for revision.</p>
                </div>
            </div>
            <a href="<?= e($article['pdf_link']) ?>" target="_blank" rel="noopener" style="padding: 0.625rem 1.25rem; background-color: #dc2626; color: #ffffff; border-radius: 0.5rem; font-weight: 700; font-size: 12px; text-transform: uppercase; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
        </div>
    <?php endif; ?>

    <!-- Article Content Box -->
    <div style="background-color: #ffffff; border-radius: 1rem; padding: 1.75rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 2rem;" class="dark:bg-gray-800 dark:border-gray-700">
        <div class="prose prose-blue dark:prose-invert max-w-none text-gray-800 dark:text-gray-200" style="font-size: 1rem; line-height: 1.7;" id="markdownContent">
            <?= $Parsedown->text($cleanDescription) ?>
        </div>
    </div>

    <!-- Social Share Toolbar -->
    <div style="background-color: #f9fafb; border-radius: 1rem; padding: 1rem 1.25rem; border: 1px solid #e5e7eb; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;" class="dark:bg-gray-800 dark:border-gray-700">
        <span style="font-weight: 700; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 0.5rem;" class="dark:text-gray-200">
            <svg style="width: 16px; height: 16px; color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            Share Article:
        </span>
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $canonicalUrl) ?>" target="_blank" rel="noopener" style="padding: 0.5rem 0.875rem; background-color: #16a34a; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 0.5rem; text-decoration: none;">
                WhatsApp
            </a>
            <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" style="padding: 0.5rem 0.875rem; background-color: #3b82f6; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 0.5rem; text-decoration: none;">
                Telegram
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" style="padding: 0.5rem 0.875rem; background-color: #1d4ed8; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 0.5rem; text-decoration: none;">
                Facebook
            </a>
            <button onclick="navigator.clipboard.writeText('<?= $canonicalUrl ?>'); alert('Link copied to clipboard!');" style="padding: 0.5rem 0.875rem; background-color: #4b5563; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 0.5rem; border: none; cursor: pointer;">
                Copy Link
            </button>
        </div>
    </div>

    <!-- Related Current Affairs -->
    <?php if (!empty($relatedArticles)): ?>
        <div style="margin-top: 3rem;">
            <h2 style="font-size: 1.375rem; font-weight: 800; color: #111827; margin-bottom: 1.25rem;" class="dark:text-white">Related News & Current Affairs</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                <?php foreach ($relatedArticles as $rel): ?>
                    <a href="/current-affairs/<?= e($rel['slug']) ?>" style="padding: 1rem; background-color: #ffffff; border-radius: 0.75rem; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.875rem; text-decoration: none; color: inherit; transition: border-color 0.2s;" class="dark:bg-gray-800 dark:border-gray-700">
                        <?php if (!empty($rel['thumbnail']) && strpos($rel['thumbnail'], 'logo') === false): ?>
                            <img src="<?= e($rel['thumbnail']) ?>" alt="" width="64" height="64" style="width: 64px; height: 64px; object-fit: cover; border-radius: 0.5rem; flex-shrink: 0;">
                        <?php else: ?>
                            <div style="width: 64px; height: 64px; border-radius: 0.5rem; background: linear-gradient(135deg, #2563eb, #4f46e5); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="/assets/logo/fc_logo_crop.webp" alt="" width="28" height="28" style="width: 28px; height: 28px; object-fit: contain; filter: brightness(0) invert(1);">
                            </div>
                        <?php endif; ?>
                        <div>
                            <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #2563eb; background-color: #dbeafe; padding: 2px 6px; border-radius: 4px;"><?= e($rel['category'] ?? 'General') ?></span>
                            <h3 style="font-weight: 700; color: #111827; font-size: 13px; line-height: 1.3; margin-top: 4px; margin-bottom: 0;" class="dark:text-white"><?= e($rel['title']) ?></h3>
                            <p style="font-size: 11px; color: #6b7280; margin-top: 4px; margin-bottom: 0;"><?= date('M d, Y', strtotime($rel['event_date'] ?? $rel['created_at'])) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

<?php
$pageTitle = "Latest Current Affairs & News - " . APP_NAME;
$pageDescription = "Stay updated with daily current affairs, national and international news, sports, economy, science & technology updates for competitive exams.";
$keywords = "Current Affairs, Daily News, Exam Preparation, GK Updates, Government Exam News";
$canonicalUrl = BASE_URL . "current-affairs";
$ogImage = BASE_URL . "assets/logo/fc_logo_crop.webp";

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['cat'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$categories = ['National', 'International', 'Sports', 'Economy', 'Science & Tech', 'Appointments', 'Obituaries', 'Awards & Honours'];

$where = ["status = 'published'"];
$params = [];

if ($search) {
    $where[] = "(title LIKE :q OR description LIKE :q)";
    $params[':q'] = "%$search%";
}

if ($category && in_array($category, $categories)) {
    $where[] = "category = :cat";
    $params[':cat'] = $category;
}

$whereSql = "WHERE " . implode(' AND ', $where);

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM current_affairs $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Fetch
$stmt = $pdo->prepare("SELECT id, title, slug, category, description, event_date, thumbnail, pdf_link, views, created_at FROM current_affairs $whereSql ORDER BY event_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Helper function to strip markdown tags for card previews
if (!function_exists('clean_markdown_snippet')) {
    function clean_markdown_snippet($text, $length = 130) {
        $text = preg_replace('/^#+\s+/m', '', $text); // Remove headings
        $text = preg_replace('/[*_~`>]/', '', $text);   // Remove markdown syntax
        $clean = trim(strip_tags($text));
        if (mb_strlen($clean) > $length) {
            return mb_substr($clean, 0, $length) . '...';
        }
        return $clean;
    }
}

require_once('_header.php');
?>

<div class="ca-main-wrapper" style="max-width: 1240px; margin: 0 auto; padding: 2.5rem 1.25rem 4rem 1.25rem;">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-10" style="text-align: center; margin-bottom: 2.5rem;">
        <span class="ca-hero-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 9999px; font-size: 12px; font-weight: 700; margin-bottom: 1rem;">
            <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Exam Prep Special
        </span>
        <h1 class="ca-hero-title" style="font-size: 2.25rem; font-weight: 800; line-height: 1.2; margin-bottom: 0.75rem;">
            Daily Current Affairs & News
        </h1>
        <p class="ca-hero-sub" style="font-size: 1.05rem; max-width: 42rem; margin: 0 auto;">
            Curated daily updates on National, International, Sports, Economy, and Science news for competitive exams.
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="ca-search-panel" style="border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 2.5rem;">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <div class="relative flex-1 w-full" style="flex: 1 1 0%; min-width: 240px;">
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="ca-input" style="width: 100%; padding: 12px 18px; border-radius: 14px; font-size: 14px;">
            </div>
            
            <select name="cat" class="ca-input w-full md:w-56" style="padding: 12px 18px; border-radius: 14px; font-size: 14px; min-width: 180px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button type="submit" class="ca-btn-primary" style="padding: 12px 24px; border-radius: 14px; font-weight: 700; font-size: 14px; cursor: pointer; border: none;">Search</button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="ca-topic-pill ca-topic-pill-inactive" style="padding: 10px 18px; border-radius: 9999px; font-weight: 600; font-size: 13px; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-4 mt-4 border-t border-gray-100 dark:border-gray-800" style="display: flex; gap: 0.5rem; overflow-x: auto; padding-top: 1rem; margin-top: 1rem; border-top: 1px solid rgba(128,128,128,0.15);">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider shrink-0 mr-1" style="font-size: 11px; font-weight: 700; text-transform: uppercase; margin-right: 4px;">TOPICS:</span>
            <a href="/current-affairs" class="ca-topic-pill <?= empty($category) ? 'ca-topic-pill-active' : 'ca-topic-pill-inactive' ?>" style="padding: 8px 18px; border-radius: 9999px; font-size: 13px; font-weight: 600; text-decoration: none;">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="ca-topic-pill <?= $category === $cat ? 'ca-topic-pill-active' : 'ca-topic-pill-inactive' ?>" style="padding: 8px 18px; border-radius: 9999px; font-size: 13px; font-weight: 600; text-decoration: none;">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div class="ca-detail-box text-center py-12 max-w-xl mx-auto my-8" style="padding: 3rem 1.5rem; text-align: center; border-radius: 1.25rem;">
            <div class="w-12 h-12 bg-blue-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-600 dark:text-blue-400" style="width: 48px; height: 48px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1" style="font-size: 1.125rem; font-weight: 700;">No Articles Found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto" style="font-size: 0.875rem;">We couldn't find any current affairs articles matching your search criteria.</p>
            <a href="/current-affairs" class="ca-btn-primary inline-block mt-4" style="padding: 10px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; display: inline-block; margin-top: 1rem;">View All News</a>
        </div>
    <?php else: ?>
        <div class="ca-grid">
            <?php foreach ($articles as $item): ?>
                <article class="ca-article-card group">
                    <!-- Thumbnail Container with Overlay Badge -->
                    <div class="ca-card-media">
                        <?php if (!empty($item['thumbnail']) && strpos($item['thumbnail'], 'logo') === false): ?>
                            <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['title']) ?>" width="640" height="360" loading="lazy" decoding="async" class="ca-card-img">
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; width: 100%; background: linear-gradient(135deg, #2563eb, #4f46e5); color: #ffffff;">
                                <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="48" height="48" style="width: 48px; height: 48px; object-fit: contain; filter: brightness(0) invert(1);">
                                <span style="font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px; opacity: 0.85;">FromCampus News</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Tag -->
                        <span class="ca-category-tag" style="position: absolute; top: 14px; left: 14px; padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(15, 23, 42, 0.75); color: #ffffff; border: 1px solid rgba(255,255,255,0.2);">
                            <?= e($item['category'] ?? 'General') ?>
                        </span>
                    </div>

                    <!-- Content -->
                    <div style="padding: 1.25rem; flex: 1 1 0%; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; margin-bottom: 8px;" class="text-gray-400 dark:text-gray-500">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span><?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?></span>
                            </div>
                            
                            <h2 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 8px; line-height: 1.35;" class="text-gray-900 dark:text-white">
                                <a href="/current-affairs/<?= e($item['slug']) ?>" style="color: inherit; text-decoration: none;"><?= e($item['title']) ?></a>
                            </h2>
                            
                            <p style="font-size: 0.875rem; line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" class="text-gray-600 dark:text-gray-300">
                                <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                            </p>
                        </div>

                        <!-- Card Footer -->
                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(128,128,128,0.15); font-size: 12px;" class="text-gray-400 dark:text-gray-500">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?= number_format($item['views']) ?> views
                            </span>

                            <a href="/current-affairs/<?= e($item['slug']) ?>" style="font-weight: 700; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px;">
                                Read More <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-center: center; gap: 8px; margin-top: 2.5rem; justify-content: center;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="ca-topic-pill <?= $i === $page ? 'ca-topic-pill-active' : 'ca-topic-pill-inactive' ?>" style="padding: 8px 16px; border-radius: 9999px; font-weight: 600; font-size: 13px; text-decoration: none;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

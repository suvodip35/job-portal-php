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

<div class="ca-main-wrapper">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-10">
        <span class="ca-hero-badge mb-3">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Exam Prep Special
        </span>
        <h1 class="ca-hero-title">
            Daily Current Affairs & News
        </h1>
        <p class="ca-hero-sub">
            Curated daily updates on National, International, Sports, Economy, and Science news for competitive exams.
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="ca-search-panel">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="ca-input">
            </div>
            
            <select name="cat" class="ca-input w-full md:w-56">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex items-center gap-2">
                <button type="submit" class="ca-btn-primary">Search</button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="ca-topic-pill ca-topic-pill-inactive">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider shrink-0 mr-1">TOPICS:</span>
            <a href="/current-affairs" class="ca-topic-pill <?= empty($category) ? 'ca-topic-pill-active' : 'ca-topic-pill-inactive' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="ca-topic-pill <?= $category === $cat ? 'ca-topic-pill-active' : 'ca-topic-pill-inactive' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div class="ca-detail-box text-center py-12 max-w-xl mx-auto my-8">
            <div class="w-12 h-12 bg-blue-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-600 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Articles Found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">We couldn't find any current affairs articles matching your search criteria.</p>
            <a href="/current-affairs" class="ca-btn-primary inline-block mt-4">View All News</a>
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
                            <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
                                <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="48" height="48" class="w-12 h-12 object-contain filter brightness-0 invert drop-shadow">
                                <span class="text-[10px] font-bold tracking-widest uppercase text-white/80 mt-1">FromCampus News</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Tag -->
                        <span class="ca-category-tag">
                            <?= e($item['category'] ?? 'General') ?>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 mb-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span><?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?></span>
                            </div>
                            
                            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 leading-snug group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                <a href="/current-affairs/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a>
                            </h2>
                            
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4 leading-relaxed">
                                <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                            </p>
                        </div>

                        <!-- Card Footer -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400 dark:text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?= number_format($item['views']) ?> views
                            </span>

                            <a href="/current-affairs/<?= e($item['slug']) ?>" class="font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                Read More <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center gap-2 mt-10">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="ca-topic-pill <?= $i === $page ? 'ca-topic-pill-active' : 'ca-topic-pill-inactive' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

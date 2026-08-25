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
    function clean_markdown_snippet($text, $length = 140) {
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

<div class="ca-container">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 mb-3 border border-blue-200 dark:border-blue-800" style="padding: 4px 12px; border-radius: 9999px; font-size: 12px;">
            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20" style="width: 14px; height: 14px;">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Exam Prep Special
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white mb-3" style="font-size: 2rem; font-weight: 800; line-height: 1.25;">
            Daily Current Affairs & News
        </h1>
        <p class="text-base text-gray-600 dark:text-gray-300" style="font-size: 1rem; color: #4b5563;">
            Curated daily updates on National, International, Sports, Economy, and Science news for competitive exams.
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8" style="padding: 1.25rem; border-radius: 1rem; background-color: #ffffff; border: 1px solid #e5e7eb; margin-bottom: 2rem;">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <div class="relative flex-1 w-full" style="flex: 1 1 0%; min-width: 240px; position: relative;">
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm" style="width: 100%; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 1px solid #d1d5db; background-color: #f9fafb;">
            </div>
            
            <select name="cat" class="w-full md:w-56 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm" style="padding: 0.625rem 1rem; border-radius: 0.75rem; border: 1px solid #d1d5db; background-color: #f9fafb; min-width: 180px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex items-center gap-2" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm" style="padding: 0.625rem 1.5rem; background-color: #2563eb; color: #ffffff; border-radius: 0.75rem; font-weight: 600;">Search</button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-medium" style="padding: 0.625rem 1rem; background-color: #f3f4f6; color: #374151; border-radius: 0.75rem;">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-4 mt-4 border-t border-gray-100 dark:border-gray-700/60" style="display: flex; gap: 0.5rem; overflow-x: auto; padding-top: 1rem; margin-top: 1rem; border-top: 1px solid #f3f4f6;">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider shrink-0 mr-1" style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Topics:</span>
            <a href="/current-affairs" class="ca-pill <?= empty($category) ? 'ca-pill-active' : 'ca-pill-inactive' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="ca-pill <?= $category === $cat ? 'ca-pill-active' : 'ca-pill-inactive' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm max-w-xl mx-auto my-8" style="padding: 3rem 1.5rem; text-align: center; background-color: #ffffff; border-radius: 1rem; border: 1px solid #e5e7eb;">
            <div class="w-12 h-12 bg-blue-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-600" style="width: 48px; height: 48px; border-radius: 9999px; background-color: #eff6ff; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto; color: #2563eb;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1" style="font-size: 1.125rem; font-weight: 700;">No Articles Found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto" style="font-size: 0.875rem; color: #6b7280;">We couldn't find any current affairs articles matching your search criteria.</p>
            <a href="/current-affairs" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: #2563eb; color: #ffffff; border-radius: 0.5rem; font-weight: 700; font-size: 12px; display: inline-block;">View All News</a>
        </div>
    <?php else: ?>
        <div class="ca-card-grid">
            <?php foreach ($articles as $item): ?>
                <article class="ca-card">
                    <!-- Thumbnail Container with Overlay Badge -->
                    <div class="ca-thumb-box">
                        <?php if (!empty($item['thumbnail']) && strpos($item['thumbnail'], 'logo') === false): ?>
                            <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['title']) ?>" width="640" height="360" loading="lazy" decoding="async" class="ca-thumb-img">
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center p-4" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; width: 100%; background: linear-gradient(135deg, #2563eb, #4f46e5); color: #ffffff;">
                                <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="48" height="48" style="width: 48px; height: 48px; object-fit: contain; filter: brightness(0) invert(1);">
                                <span style="font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px; opacity: 0.85;">FromCampus News</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <span class="ca-badge">
                            <?= e($item['category'] ?? 'General') ?>
                        </span>
                    </div>

                    <!-- Content -->
                    <div style="padding: 1.25rem; flex: 1 1 0%; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 12px; color: #6b7280; margin-bottom: 0.5rem;">
                                <svg style="width: 14px; height: 14px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span><?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?></span>
                            </div>
                            
                            <h2 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.35;">
                                <a href="/current-affairs/<?= e($item['slug']) ?>" class="hover:text-blue-600 dark:hover:text-blue-400" style="color: inherit; text-decoration: none;"><?= e($item['title']) ?></a>
                            </h2>
                            
                            <p style="font-size: 0.875rem; color: #4b5563; line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                            </p>
                        </div>

                        <!-- Card Footer -->
                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #f3f4f6; font-size: 12px; color: #6b7280;">
                            <span style="display: flex; align-items: center; gap: 0.375rem;">
                                <svg style="width: 14px; height: 14px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?= number_format($item['views']) ?> views
                            </span>

                            <a href="/current-affairs/<?= e($item['slug']) ?>" style="font-weight: 700; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
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
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2.5rem;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="ca-pill <?= $i === $page ? 'ca-pill-active' : 'ca-pill-inactive' ?>" style="padding: 0.5rem 1rem; border-radius: 0.5rem;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

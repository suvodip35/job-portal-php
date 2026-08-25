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

require_once('_header.php');
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3">Daily Current Affairs & News</h1>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Get daily updates on National, International, Sports, Economy, and Science news curated for competitive exam preparation.</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row gap-3">
            <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            
            <select name="cat" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">Search</button>
            <?php if ($search || $category): ?>
                <a href="/current-affairs" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 text-center">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Category Pills -->
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="/current-affairs" class="px-3.5 py-1.5 rounded-full text-sm font-medium transition <?= empty($category) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="px-3.5 py-1.5 rounded-full text-sm font-medium transition <?= $category === $cat ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200' ?>">
                <?= e($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <p class="text-gray-600 dark:text-gray-400 font-medium">No Current Affairs articles match your criteria.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $item): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col hover:shadow-md transition">
                    <?php if (!empty($item['thumbnail'])): ?>
                        <a href="/current-affairs/<?= e($item['slug']) ?>" class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-900">
                            <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['title']) ?>" width="600" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover hover:scale-105 transition duration-300" style="width: 100%; height: 180px;">
                        </a>
                    <?php endif; ?>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    <?= e($item['category'] ?? 'General') ?>
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?>
                                </span>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-blue-600 dark:hover:text-blue-400">
                                <a href="/current-affairs/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a>
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                                <?= e(mb_substr(strip_tags($item['description']), 0, 130)) ?>...
                            </p>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?= number_format($item['views']) ?> views
                            </span>
                            <?php if (!empty($item['pdf_link'])): ?>
                                <a href="<?= e($item['pdf_link']) ?>" target="_blank" class="text-red-600 dark:text-red-400 font-semibold flex items-center gap-1 hover:underline">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                    PDF
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center gap-2 mt-10">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-4 py-2 rounded-lg font-medium border <?= $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-700 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

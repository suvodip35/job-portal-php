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

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-12">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-10">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 mb-4 border border-blue-200 dark:border-blue-800">
            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Exam Prep Special
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4 leading-tight">
            Daily Current Affairs & News
        </h1>
        <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            Curated daily updates on National, International, Sports, Economy, and Science news for SSC, Railway, Banking & State PSC exams.
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-gray-800 p-4 md:p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>
            
            <select name="cat" class="w-full md:w-56 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-initial px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition shadow-sm hover:shadow">Search</button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-medium transition text-center">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/60">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider shrink-0 mr-1">Topics:</span>
            <a href="/current-affairs" class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= empty($category) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= $category === $cat ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm max-w-xl mx-auto my-8">
            <div class="w-16 h-16 bg-blue-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 dark:text-blue-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Articles Found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">We couldn't find any current affairs articles matching your search criteria.</p>
            <a href="/current-affairs" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition">View All News</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $item): ?>
                <article class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-200 dark:border-gray-700/80 overflow-hidden flex flex-col transition-all duration-300 hover:-translate-y-1">
                    <!-- Thumbnail Container with Overlay Badge -->
                    <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700">
                        <?php if (!empty($item['thumbnail']) && strpos($item['thumbnail'], 'logo') === false): ?>
                            <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['title']) ?>" width="640" height="360" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" style="aspect-ratio: 16/9;">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center p-4 bg-gradient-to-br from-blue-600 to-indigo-700">
                                <div class="flex flex-col items-center gap-1.5 opacity-90">
                                    <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="48" height="48" class="w-12 h-12 object-contain filter brightness-0 invert drop-shadow">
                                    <span class="text-[10px] font-bold tracking-widest uppercase text-white/80">FromCampus News</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-[11px] font-bold tracking-wide uppercase bg-black/40 text-white backdrop-blur border border-white/20 shadow-sm">
                            <?= e($item['category'] ?? 'General') ?>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?= number_format($item['views']) ?> views
                            </span>

                            <a href="/current-affairs/<?= e($item['slug']) ?>" class="font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
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
        <div class="flex justify-center gap-2 mt-12">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-4 py-2 rounded-xl font-bold text-sm border transition <?= $i === $page ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

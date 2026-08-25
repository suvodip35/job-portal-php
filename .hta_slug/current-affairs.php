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

// Category Linear-Gradient Style Generator (Direct CSS for 100% Reliability)
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

require_once('_header.php');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 md:py-10">
    <!-- Hero Header -->
    <div class="text-center max-w-3xl mx-auto mb-8">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 mb-3 border border-blue-200 dark:border-blue-800">
            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Exam Prep Special
        </span>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">
            Daily Current Affairs & News
        </h1>
        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300">
            Curated daily updates on National, International, Sports, Economy, and Science news for competitive exams.
        </p>
    </div>

    <!-- Search & Filter Panel -->
    <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <select name="cat" class="w-full md:w-56 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition shadow-sm">Search</button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold transition text-center">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Topic Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider shrink-0 mr-1">TOPICS:</span>
            <a href="/current-affairs" class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold transition <?= empty($category) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold transition <?= $category === $cat ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div class="p-8 border rounded-2xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-center my-8 max-w-lg mx-auto">
            <div class="w-12 h-12 bg-blue-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-600 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">No Articles Found</h3>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">No current affairs articles matched your search criteria.</p>
            <a href="/current-affairs" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition">View All News</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $item): 
                $headerStyle = get_category_style($item['category'] ?? 'General');
                $hasCustomImage = !empty($item['thumbnail']) && strpos($item['thumbnail'], 'fc_logo') === false && strpos($item['thumbnail'], 'logo') === false;
            ?>
                <article 
                    onclick="location.href='/current-affairs/<?= e($item['slug']) ?>'" 
                    class="group border cursor-pointer rounded-2xl bg-white dark:bg-gray-800 shadow-sm hover:shadow-xl border-gray-200 dark:border-gray-700/80 transition-all duration-300 hover:-translate-y-1 overflow-hidden flex flex-col justify-between">
                    
                    <div>
                        <!-- Header Banner -->
                        <div class="w-full aspect-[16/9] relative overflow-hidden p-5 flex flex-col justify-between" style="aspect-ratio: 16/9; min-height: 180px; <?= $headerStyle ?>">
                            <?php if ($hasCustomImage): ?>
                                <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['title']) ?>" width="640" height="360" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <?php endif; ?>

                            <!-- Category Badge -->
                            <div class="relative z-10 flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-black/40 text-white backdrop-blur border border-white/20 shadow-sm">
                                    <?= e($item['category'] ?? 'General') ?>
                                </span>
                                <span class="text-[11px] font-semibold text-white/90 drop-shadow flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?>
                                </span>
                            </div>

                            <!-- Branded Title inside Header (If no custom photo) -->
                            <?php if (!$hasCustomImage): ?>
                                <div class="relative z-10 my-auto text-center">
                                    <h3 class="text-white font-black text-lg sm:text-xl drop-shadow tracking-tight line-clamp-2 opacity-95">
                                        <?= e($item['title']) ?>
                                    </h3>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug mb-2">
                                <a href="/current-affairs/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a>
                            </h2>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed">
                                <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-5 py-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50">
                        <span class="flex items-center gap-1.5 font-medium">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <?= number_format($item['views']) ?> views
                        </span>

                        <a href="/current-affairs/<?= e($item['slug']) ?>" class="font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                            Read More <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center gap-2 mt-10">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $i === $page ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

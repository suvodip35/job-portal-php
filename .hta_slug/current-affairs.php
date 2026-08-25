<?php
require_once __DIR__ . '/../.hta_config/functions.php';

$pageTitle = "Latest Current Affairs & News - " . APP_NAME;
$pageDescription = "Stay updated with daily current affairs, national and international news, sports, economy, science & technology updates for competitive exams.";
$keywords = "Current Affairs, Daily News, Exam Preparation, GK Updates, Government Exam News";
$canonicalUrl = BASE_URL . "current-affairs";
$ogImage = BASE_URL . "assets/logo/fc_logo_crop.webp";

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['cat'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9; // 3x3 grid matching homepage 3-column layout
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

// Fetch Main Articles
$stmt = $pdo->prepare("SELECT id, title, slug, category, description, event_date, thumbnail, pdf_link, views, created_at FROM current_affairs $whereSql ORDER BY event_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Fetch Trending Current Affairs for Sidebar
$latestUpdates = cache_get_or_set('ca_sidebar_trending', 300, function() use ($pdo) {
    try {
        $tStmt = $pdo->query("SELECT id, title, slug, category, event_date, thumbnail, views FROM current_affairs WHERE status = 'published' ORDER BY views DESC, created_at DESC LIMIT 5");
        return $tStmt->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }
});

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

// Estimate Reading Time helper
if (!function_exists('estimate_reading_time')) {
    function estimate_reading_time($text) {
        $clean = strip_tags($text);
        $words = str_word_count($clean);
        $minutes = max(1, (int)ceil($words / 180));
        return $minutes . ' min read';
    }
}

// Category Gradient Style Generator matching site color palette
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

require_once('_header.php');
?>

<!-- Homepage-Matching Layout Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-1 md:grid-cols-12 gap-6">

    <!-- Sidebar (Desktop Left) - Matching _home.php -->
    <aside class="hidden md:block md:col-span-3">
        <div class="sticky top-20 space-y-4">
            
            <!-- Category Topics Navigation Card -->
            <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
                <h3 class="text-sm font-semibold mb-3 text-gray-900 dark:text-white">GK Categories</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="/current-affairs" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-semibold <?= empty($category) ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>">
                            <span>All News & Updates</span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-semibold <?= $category === $cat ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>">
                                <span><?= e($cat) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Search Filter Card -->
            <form method="get" action="/current-affairs" class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
                <h3 class="text-sm font-semibold mb-3 text-gray-900 dark:text-white">Search News</h3>
                <?php if ($category): ?>
                    <input type="hidden" name="cat" value="<?= e($category) ?>">
                <?php endif; ?>

                <label class="block text-xs mb-1 text-gray-600 dark:text-gray-400">Keyword</label>
                <input name="search" value="<?= e($search) ?>" placeholder="Title, event or topic..." class="w-full mb-3 px-3 py-2 text-xs border rounded-lg dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500"/>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                        Search
                    </button>
                    <?php if ($search || $category): ?>
                        <a href="/current-affairs" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-300 transition text-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Latest Updates / Trending GK Card -->
            <?php if (!empty($latestUpdates)): ?>
                <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
                    <h3 class="text-sm font-semibold mb-3 text-gray-900 dark:text-white">Trending GK News</h3>
                    <ul class="space-y-3">
                        <?php foreach ($latestUpdates as $lu): ?>
                            <li class="flex items-start gap-2">
                                <span class="inline-block mt-1 w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
                                <a href="/current-affairs/<?= e($lu['slug']) ?>" title="<?= e($lu['title']) ?>" class="text-xs text-gray-800 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 line-clamp-2 leading-snug">
                                    <?= e($lu['title']) ?>
                                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        <?= date('M d, Y', strtotime($lu['event_date'] ?? $lu['created_at'])) ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        </div>
    </aside>

    <!-- Main Content Area (Desktop Right 9 Cols) -->
    <main class="md:col-span-9">
        
        <!-- Section Header Bar (Matching _home.php) -->
        <div class="flex items-baseline justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                <?= $category ? e($category) . ' Current Affairs' : 'Current Affairs & Exam News' ?> — <?= e($total) ?> found
            </h1>
            
            <?php if ($search || $category): ?>
                <a href="/current-affairs" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold">Clear Filters</a>
            <?php endif; ?>
        </div>

        <?php if (empty($articles)): ?>
            <div class="mt-4 p-6 border rounded-xl bg-white dark:bg-gray-800 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-300">No current affairs articles matched your filters.</p>
                <a href="/current-affairs" class="inline-block mt-3 px-4 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">View All News</a>
            </div>
        <?php else: ?>
            
            <!-- Cards Grid (3 Columns on desktop, matching _home.php mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-6) -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($articles as $index => $item): 
                    $headerStyle = get_category_style($item['category'] ?? 'General');
                    $hasCustomImage = is_valid_thumbnail($item['thumbnail']);
                    $readTime = estimate_reading_time($item['description']);
                ?>
                    <article 
                        aria-label="<?= e($item['title']) ?>" 
                        onclick="location.href='/current-affairs/<?= e($item['slug']) ?>'" 
                        class="group border cursor-pointer rounded-2xl bg-white dark:bg-gray-800 shadow hover:shadow-2xl transition duration-200 overflow-hidden w-full flex flex-col justify-between">
                        
                        <div>
                            <!-- Visual Banner (Aspect ratio 16/9 matching homepage cards) -->
                            <div class="w-full aspect-[16/9] overflow-hidden relative p-3 flex flex-col justify-between" style="aspect-ratio: 16/9; min-height: 160px; <?= !$hasCustomImage ? $headerStyle : '' ?>">
                                <?php if ($hasCustomImage): ?>
                                    <img 
                                        src="<?= e($item['thumbnail']) ?>"
                                        <?php if ($index === 0): ?>
                                            loading="eager"
                                            fetchpriority="high"
                                        <?php else: ?>
                                            loading="lazy"
                                            decoding="async"
                                        <?php endif; ?>
                                        width="640"
                                        height="360"
                                        alt="<?= e($item['title']) ?>"
                                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                        style="aspect-ratio: 16/9;"
                                    />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                                <?php else: ?>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                <?php endif; ?>

                                <!-- Category Badge (Top Left) & Reading Time (Top Right) -->
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-black/45 text-white backdrop-blur border border-white/20 shadow-sm">
                                        <?= e($item['category'] ?? 'General') ?>
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-black/40 text-white/90 backdrop-blur">
                                        <?= $readTime ?>
                                    </span>
                                </div>

                                <!-- Date & PDF Tag (Bottom Banner Overlay) -->
                                <div class="relative z-10 flex items-center justify-between text-[11px] text-white/90 font-medium drop-shadow">
                                    <span class="flex items-center gap-1">
                                        <svg width="12" height="12" style="width: 12px; height: 12px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?>
                                    </span>
                                    <?php if (!empty($item['pdf_link'])): ?>
                                        <span class="bg-red-600 text-white text-[9px] px-1.5 py-0.5 rounded font-extrabold uppercase shadow-sm">PDF Notes</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Card Content Body (Matching Homepage Card Layout 1:1) -->
                            <div class="p-3.5">
                                <div class="flex justify-between items-start gap-2 mb-1.5">
                                    <div class="min-w-0 flex-1">
                                        <a href="/current-affairs/<?= e($item['slug']) ?>" title="<?= e($item['title']) ?>">
                                            <h2 class="text-sm sm:text-base font-semibold text-blue-600 dark:text-blue-400 group-hover:underline line-clamp-2 leading-snug">
                                                <?= e($item['title']) ?>
                                            </h2>
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1 truncate">
                                            <span>🏢 <?= e($item['category'] ?? 'General') ?></span>
                                            <span>•</span>
                                            <span>⏱️ <?= $readTime ?></span>
                                        </p>
                                    </div>

                                    <?php if (strtotime($item['event_date'] ?? $item['created_at']) > strtotime('-7 days')): ?>
                                        <span class="px-2 py-0.5 text-[10px] bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300 rounded-full shrink-0 font-bold">New</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-3 overflow-hidden leading-relaxed mt-2.5">
                                    <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                                </p>

                                <!-- Card Footer (Inside Body Padding - 100% Homepage Match) -->
                                <div class="mt-4 flex justify-between items-center">
                                    <a class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition" href="/current-affairs/<?= e($item['slug']) ?>">Details</a>
                                    
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                            <?= date('M d, Y', strtotime($item['event_date'] ?? $item['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-8 flex justify-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="/current-affairs?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            ← Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $i === $page ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="/current-affairs?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

</div>

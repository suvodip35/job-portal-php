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
$perPage = 10;
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
$trendingArticles = cache_get_or_set('ca_sidebar_trending', 300, function() use ($pdo) {
    try {
        $tStmt = $pdo->query("SELECT id, title, slug, category, event_date, thumbnail, views FROM current_affairs WHERE status = 'published' ORDER BY views DESC, created_at DESC LIMIT 5");
        return $tStmt->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }
});

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

<div class="w-full max-w-7xl mx-auto space-y-6">
    
    <!-- Hero Header Banner -->
    <div class="relative overflow-hidden rounded-2xl text-white p-6 sm:p-8 md:p-10 shadow-lg border border-blue-700/40" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e1b4b 100%);">
        <div class="relative z-10 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30 backdrop-blur">
                    <svg width="14" height="14" style="width: 14px; height: 14px;" class="text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    Daily Exam GK Special • <?= date('F Y') ?>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300 border border-green-400/30">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Live Daily Updates
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white leading-tight mb-3">
                Daily Current Affairs & Exam News
            </h1>
            
            <p class="text-xs sm:text-sm md:text-base text-blue-100 leading-relaxed mb-6">
                Comprehensive daily GK updates covering National, International, Economy, Sports, and Science news curated for UPSC, SSC, Banking, Railway, and State PSC competitive exams.
            </p>

            <!-- Feature Badges -->
            <div class="flex flex-wrap items-center gap-2.5 text-xs text-blue-200">
                <span class="flex items-center gap-1.5 bg-black/30 px-3 py-1.5 rounded-lg border border-white/10">
                    <svg width="15" height="15" style="width: 15px; height: 15px;" class="text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verified News Sources
                </span>
                <span class="flex items-center gap-1.5 bg-black/30 px-3 py-1.5 rounded-lg border border-white/10">
                    <svg width="15" height="15" style="width: 15px; height: 15px;" class="text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Free Study PDF Notes
                </span>
                <span class="flex items-center gap-1.5 bg-black/30 px-3 py-1.5 rounded-lg border border-white/10">
                    <svg width="15" height="15" style="width: 15px; height: 15px;" class="text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Exam-Focused Points
                </span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Panel -->
    <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative w-full" style="flex: 1 1 auto; width: 100%;">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg width="16" height="16" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." style="width: 100% !important; display: block;" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>
            
            <select name="cat" class="w-full md:w-52 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition shrink-0">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex items-center gap-2 w-full md:w-auto shrink-0">
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                    <svg width="15" height="15" style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold transition text-center">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Quick Pills -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-3.5 mt-3.5 border-t border-gray-100 dark:border-gray-700">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider shrink-0 mr-1 flex items-center gap-1">
                <svg width="14" height="14" style="width: 14px; height: 14px;" class="text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Topics:
            </span>
            <a href="/current-affairs" class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold transition <?= empty($category) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">All News</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold transition <?= $category === $cat ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Spotlight Featured Card -->
    <?php if ($page === 1 && empty($search) && empty($category) && !empty($articles)): 
        $spotlight = $articles[0];
        $spotlightHeaderStyle = get_category_style($spotlight['category'] ?? 'General');
        $spotlightHasImg = is_valid_thumbnail($spotlight['thumbnail']);
        $spotlightReadTime = estimate_reading_time($spotlight['description']);
    ?>
        <div class="group cursor-pointer bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl border border-gray-200 dark:border-gray-700/80 overflow-hidden transition-all duration-300" onclick="location.href='/current-affairs/<?= e($spotlight['slug']) ?>'">
            <div class="flex flex-col md:flex-row items-stretch">
                <!-- Visual / Header Banner -->
                <div class="w-full md:w-1/2 relative min-h-[180px] md:min-h-[240px] flex flex-col justify-between p-5 sm:p-6 shrink-0" style="<?= $spotlightHeaderStyle ?>">
                    <?php if ($spotlightHasImg): ?>
                        <img src="<?= e($spotlight['thumbnail']) ?>" alt="<?= e($spotlight['title']) ?>" width="800" height="450" loading="eager" fetchpriority="high" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <?php else: ?>
                        <!-- Decorative Watermark Icon for Banner without Photo -->
                        <div class="absolute right-4 bottom-3 text-white/15 pointer-events-none">
                            <svg width="100" height="100" style="width: 100px; height: 100px;" fill="currentColor" viewBox="0 0 24 24"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        </div>
                    <?php endif; ?>

                    <div class="relative z-10 flex items-center justify-between">
                        <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-blue-600 text-white shadow">
                            ⚡ Featured Story
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-black/40 text-white backdrop-blur border border-white/20">
                            <?= e($spotlight['category'] ?? 'National') ?>
                        </span>
                    </div>

                    <div class="relative z-10 flex items-center justify-between text-xs text-white/90 font-medium drop-shadow pt-6">
                        <span class="flex items-center gap-1.5">
                            <svg width="14" height="14" style="width: 14px; height: 14px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <?= $spotlight['event_date'] ? date('F d, Y', strtotime($spotlight['event_date'])) : date('F d, Y', strtotime($spotlight['created_at'])) ?>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg width="14" height="14" style="width: 14px; height: 14px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?= $spotlightReadTime ?>
                        </span>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="w-full md:w-1/2 p-5 sm:p-6 flex flex-col justify-between bg-white dark:bg-gray-800">
                    <div>
                        <div class="flex items-center gap-2 mb-2 text-xs font-semibold text-blue-600 dark:text-blue-400">
                            <span>Top Daily GK Highlight</span>
                            <span>•</span>
                            <span class="text-gray-500 dark:text-gray-400"><?= number_format($spotlight['views']) ?> reads</span>
                        </div>

                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug mb-3">
                            <a href="/current-affairs/<?= e($spotlight['slug']) ?>"><?= e($spotlight['title']) ?></a>
                        </h2>

                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed mb-4">
                            <?= e(clean_markdown_snippet($spotlight['description'], 180)) ?>
                        </p>
                    </div>

                    <div class="pt-3.5 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
                        <a href="/current-affairs/<?= e($spotlight['slug']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            Read Featured Story
                            <svg width="14" height="14" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>

                        <?php if (!empty($spotlight['pdf_link'])): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-lg border border-red-200 dark:border-red-800">
                                <svg width="13" height="13" style="width: 13px; height: 13px;" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                PDF
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Layout (Flex Feed + Sidebar) -->
    <div class="flex flex-col md:flex-row gap-6 items-start">
        
        <!-- Articles Feed (w-full md:w-2/3) -->
        <div class="w-full md:w-2/3 min-w-0">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-5 bg-blue-600 rounded-full inline-block"></span>
                    <?= $search ? 'Search Results' : ($category ? e($category) . ' News' : 'Latest Current Affairs') ?>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                        <?= number_format($total) ?> Articles
                    </span>
                </h2>
            </div>

            <?php if (empty($articles)): ?>
                <div class="p-8 border rounded-2xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-center my-4">
                    <div class="w-14 h-14 bg-blue-50 dark:bg-gray-700/60 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-600 dark:text-blue-400">
                        <svg width="28" height="28" style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">No Articles Found</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-4">No current affairs articles matched your current search or topic filter.</p>
                    <a href="/current-affairs" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition">
                        View All Current Affairs
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php foreach ($articles as $item): 
                        $headerStyle = get_category_style($item['category'] ?? 'General');
                        $hasCustomImage = is_valid_thumbnail($item['thumbnail']);
                        $readTime = estimate_reading_time($item['description']);
                    ?>
                        <article 
                            onclick="location.href='/current-affairs/<?= e($item['slug']) ?>'" 
                            class="group cursor-pointer rounded-2xl bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg border border-gray-200 dark:border-gray-700/80 transition-all duration-300 hover:-translate-y-0.5 overflow-hidden flex flex-col justify-between">
                            
                            <div>
                                <!-- Banner / Header -->
                                <div class="w-full aspect-[16/9] relative overflow-hidden p-3.5 flex flex-col justify-between" style="aspect-ratio: 16/9; min-height: 140px; <?= $headerStyle ?>">
                                    <?php if ($hasCustomImage): ?>
                                        <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['title']) ?>" width="640" height="360" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                    <?php else: ?>
                                        <!-- Decorative Watermark Icon for Banner without Photo -->
                                        <div class="absolute right-3 bottom-2 text-white/15 pointer-events-none">
                                            <svg width="72" height="72" style="width: 72px; height: 72px;" fill="currentColor" viewBox="0 0 24 24"><path d="M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Category Badge & Date -->
                                    <div class="relative z-10 flex items-center justify-between">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-black/40 text-white backdrop-blur border border-white/20 shadow-sm">
                                            <?= e($item['category'] ?? 'General') ?>
                                        </span>
                                        <span class="text-[11px] font-semibold text-white/90 drop-shadow flex items-center gap-1">
                                            <svg width="13" height="13" style="width: 13px; height: 13px;" class="text-white/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?>
                                        </span>
                                    </div>

                                    <div class="relative z-10 flex items-center justify-between text-[11px] text-white/80 font-medium pt-1.5">
                                        <span><?= $readTime ?></span>
                                        <?php if (!empty($item['pdf_link'])): ?>
                                            <span class="bg-red-600 text-white text-[9px] px-1.5 py-0.5 rounded font-bold uppercase">PDF</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Card Body (Single Title Rendering) -->
                                <div class="p-4 sm:p-4.5">
                                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug mb-1.5">
                                        <a href="/current-affairs/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a>
                                    </h3>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed">
                                        <?= e(clean_markdown_snippet($item['description'], 110)) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="px-4 py-2.5 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50">
                                <span class="flex items-center gap-1 font-medium text-[11px]">
                                    <svg width="13" height="13" style="width: 13px; height: 13px;" class="text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <?= number_format($item['views']) ?> views
                                </span>

                                <a href="/current-affairs/<?= e($item['slug']) ?>" class="font-bold text-blue-600 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform flex items-center gap-1 text-xs">
                                    Read Article <svg width="12" height="12" style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center gap-2 mt-8">
                    <?php if ($page > 1): ?>
                        <a href="/current-affairs?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            ← Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $i === $page ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="/current-affairs?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar (w-full md:w-1/3) -->
        <aside class="w-full md:w-1/3 shrink-0 space-y-5">
            
            <!-- Trending GK & Current Affairs Card -->
            <?php if (!empty($trendingArticles)): ?>
                <div class="p-4 sm:p-5 border rounded-2xl bg-white dark:bg-gray-800 shadow-sm border-gray-200 dark:border-gray-700/80">
                    <div class="flex items-center justify-between pb-2.5 mb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <span class="text-blue-600 dark:text-blue-400">🔥</span> Trending Current Affairs
                        </h3>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($trendingArticles as $rank => $tArticle): ?>
                            <a href="/current-affairs/<?= e($tArticle['slug']) ?>" class="group flex items-start gap-2.5 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 font-extrabold text-[11px] flex items-center justify-center shrink-0 mt-0.5">
                                    <?= $rank + 1 ?>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                        <?= e($tArticle['category'] ?? 'General') ?>
                                    </span>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug mt-0.5">
                                        <?= e($tArticle['title']) ?>
                                    </h4>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 block">
                                        <?= $tArticle['event_date'] ? date('M d, Y', strtotime($tArticle['event_date'])) : date('M d, Y', strtotime($tArticle['created_at'])) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Push Alert Banner -->
            <div class="p-5 sm:p-6 rounded-2xl text-white shadow-md relative overflow-hidden" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);">
                <div class="relative z-10">
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-2.5">
                        <svg width="18" height="18" style="width: 18px; height: 18px;" class="text-white shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white mb-1">Get Daily GK & Job Alerts</h3>
                    <p class="text-xs text-blue-100 mb-3.5 leading-relaxed">
                        Never miss crucial daily current affairs, exam notifications, and free PDF notes updates.
                    </p>
                    <button onclick="if(typeof subscribeToPushNotifications==='function'){ subscribeToPushNotifications(this); }" class="w-full py-2 bg-white hover:bg-blue-50 text-blue-800 rounded-xl font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                        Enable Notifications
                    </button>
                </div>
            </div>

            <!-- Browse Topics List -->
            <div class="p-4 sm:p-5 border rounded-2xl bg-white dark:bg-gray-800 shadow-sm border-gray-200 dark:border-gray-700/80">
                <h3 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider mb-2.5 pb-2 border-b border-gray-100 dark:border-gray-700">
                    Explore Exam Categories
                </h3>
                <ul class="space-y-1 text-xs font-semibold">
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="flex items-center justify-between p-1.5 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                    <?= e($cat) ?>
                                </span>
                                <svg width="13" height="13" style="width: 13px; height: 13px;" class="text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </aside>
    </div>

</div>

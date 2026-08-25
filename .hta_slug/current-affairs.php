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

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 md:py-10">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-8">
        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 mb-3" style="padding: 4px 12px; border-radius: 9999px; font-size: 12px;">
            Exam Prep Special
        </span>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
            Daily Current Affairs & News
        </h1>
        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
            Get daily updates on National, International, Sports, Economy, and Science news for competitive exams.
        </p>
    </div>

    <!-- Search & Filter Bar (Matching _home.php job filters) -->
    <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 mb-8" style="border-radius: 1rem;">
        <form method="get" action="/current-affairs" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full" style="flex: 1 1 0%;">
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search news, topics, or keywords..." class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm" style="padding: 10px 16px; border-radius: 12px;">
            </div>
            
            <select name="cat" class="w-full md:w-56 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm" style="padding: 10px 16px; border-radius: 12px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm" style="padding: 10px 24px; border-radius: 12px; background-color: #2563eb; color: #ffffff;">Search</button>
                <?php if ($search || $category): ?>
                    <a href="/current-affairs" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-medium" style="padding: 10px 16px; border-radius: 12px;">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Pills (Matching _home.php tab pills) -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-4 mt-4 border-t border-gray-100 dark:border-gray-700" style="display: flex; gap: 8px; overflow-x: auto; padding-top: 12px; margin-top: 12px;">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider shrink-0 mr-1" style="font-size: 11px; font-weight: 700;">Topics:</span>
            <a href="/current-affairs" class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= empty($category) ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 hover:bg-blue-200' ?>" style="padding: 6px 14px; border-radius: 9999px; font-size: 12px; text-decoration: none;">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= $category === $cat ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 hover:bg-blue-200' ?>" style="padding: 6px 14px; border-radius: 9999px; font-size: 12px; text-decoration: none;">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Articles Grid (Matching _home.php Job Card grid) -->
    <?php if (empty($articles)): ?>
        <div class="p-6 border rounded-xl bg-white dark:bg-gray-800 text-center text-gray-600 dark:text-gray-300 my-6">
            No current affairs articles matched your filters.
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" style="display: grid; gap: 1.5rem;">
            <?php foreach ($articles as $item): ?>
                <article 
                    onclick="location.href='/current-affairs/<?= e($item['slug']) ?>'" 
                    class="group border cursor-pointer rounded-2xl bg-white dark:bg-gray-800 shadow hover:shadow-2xl transition overflow-hidden w-full flex flex-col justify-between"
                    style="border-radius: 1rem;">
                    
                    <div>
                        <!-- Thumbnail Box with Blurred Background (Matching job.php line 170-178) -->
                        <div class="w-full aspect-[16/9] relative overflow-hidden bg-gray-100 dark:bg-gray-900" style="aspect-ratio: 16/9; min-height: 180px; position: relative;">
                            <?php if (!empty($item['thumbnail'])): ?>
                                <div class="absolute inset-0 bg-cover bg-center blur-lg scale-110" style="background-image: url('<?= e($item['thumbnail']) ?>');"></div>
                                <img 
                                    src="<?= e($item['thumbnail']) ?>" 
                                    width="640" 
                                    height="360" 
                                    alt="<?= e($item['title']) ?>" 
                                    loading="lazy" 
                                    decoding="async" 
                                    class="relative w-full h-full object-contain" 
                                    style="aspect-ratio: 16/9;" 
                                />
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-blue-600 text-white">
                                    <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus" width="48" height="48" class="w-12 h-12 object-contain filter brightness-0 invert">
                                </div>
                            <?php endif; ?>

                            <!-- Category Badge -->
                            <span class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-600 text-white shadow" style="position: absolute; top: 10px; left: 10px; padding: 4px 10px; border-radius: 9999px; font-size: 11px; background-color: #2563eb; color: #ffffff;">
                                <?= e($item['category'] ?? 'General') ?>
                            </span>
                        </div>

                        <!-- Card Content -->
                        <div class="p-4" style="padding: 1rem;">
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span><?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?></span>
                            </div>

                            <a href="/current-affairs/<?= e($item['slug']) ?>" title="<?= e($item['title']) ?>">
                                <h2 class="text-base font-semibold text-blue-600 dark:text-blue-400 group-hover:underline line-clamp-2" style="font-size: 1rem; font-weight: 600; line-height: 1.35;">
                                    <?= e($item['title']) ?>
                                </h2>
                            </a>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-3" style="font-size: 13px; margin-top: 8px; line-height: 1.45;">
                                <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="p-4 pt-0 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 mt-3" style="padding: 1rem; padding-top: 0.75rem; margin-top: 0.75rem; border-top: 1px solid rgba(128,128,128,0.15);">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <?= number_format($item['views']) ?> views
                        </span>

                        <a href="/current-affairs/<?= e($item['slug']) ?>" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1" style="color: #2563eb; font-weight: 600; text-decoration: none;">
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
                <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?= $i === $page ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 hover:bg-blue-200' ?>" style="padding: 6px 14px; border-radius: 9999px; text-decoration: none;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once('_footer.php'); ?>

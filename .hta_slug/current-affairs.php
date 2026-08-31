<?php
require_once __DIR__ . '/../.hta_config/functions.php';

$pageTitle = "Latest Current Affairs & GK Updates - " . APP_NAME;
$pageDescription = "Stay updated with daily current affairs, national and international news, sports, economy, science & technology updates for competitive exams.";
$keywords = "Current Affairs, Daily News, Exam Preparation, GK Updates, Government Exam News, PDF Study Notes";
$canonicalUrl = BASE_URL . "current-affairs";
$ogImage = BASE_URL . "assets/logo/fc_logo_crop.webp";

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['cat'] ?? '');
$pdfOnly = (int)($_GET['pdf'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10; // 1 Featured + 9 Grid items on page 1
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

if ($pdfOnly === 1) {
    $where[] = "(pdf_link IS NOT NULL AND pdf_link != '')";
}

$whereSql = "WHERE " . implode(' AND ', $where);

// Count total matching articles
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM current_affairs $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Fetch Main Articles
$stmt = $pdo->prepare("SELECT id, title, slug, category, description, event_date, thumbnail, pdf_link, views, created_at FROM current_affairs $whereSql ORDER BY event_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Determine if we show Featured Hero Card (First article on Page 1 when no search query)
$showFeatured = ($page === 1 && empty($search) && !empty($articles));
$featuredArticle = $showFeatured ? $articles[0] : null;
$gridArticles = $showFeatured ? array_slice($articles, 1) : $articles;

// Fetch Trending Current Affairs for Sidebar (Cached 5 min for performance)
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

<style>
/* Dedicated Scoped CSS to bypass purged Tailwind restrictions */
.ca-pub-wrapper {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.75rem 1rem;
  color: #f8fafc;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.ca-pub-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.75rem;
}

@media (min-width: 768px) {
  .ca-pub-layout {
    grid-template-columns: 3fr 9fr;
  }
}

/* Category Filter Chips Bar */
.ca-chips-bar {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  margin-bottom: 1.25rem;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.ca-chips-bar::-webkit-scrollbar { display: none; }

.ca-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
  text-decoration: none !important;
  transition: all 0.2s ease;
}

.ca-chip-inactive {
  background-color: #1e293b;
  color: #cbd5e1 !important;
  border: 1px solid #334155;
}
.ca-chip-inactive:hover {
  background-color: #334155;
  color: #ffffff !important;
}

.ca-chip-active {
  background-color: #2563eb;
  color: #ffffff !important;
  box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4);
}

.ca-chip-pdf-active {
  background-color: #dc2626;
  color: #ffffff !important;
  box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.4);
}

.ca-chip-pdf-inactive {
  background-color: rgba(220, 38, 38, 0.15);
  color: #f87171 !important;
  border: 1px solid rgba(220, 38, 38, 0.3);
}
.ca-chip-pdf-inactive:hover {
  background-color: rgba(220, 38, 38, 0.3);
  color: #ffffff !important;
}

/* Header Section */
.ca-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  padding-bottom: 0.875rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #334155;
}

.ca-header-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #ffffff !important;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.ca-header-count {
  font-size: 0.75rem;
  font-weight: 800;
  background-color: rgba(59, 130, 246, 0.2);
  color: #60a5fa !important;
  border: 1px solid rgba(59, 130, 246, 0.3);
  padding: 0.2rem 0.625rem;
  border-radius: 9999px;
}

/* Featured Hero Spotlight Card */
.ca-featured-card {
  display: grid;
  grid-template-columns: 1fr;
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 1rem;
  overflow: hidden;
  margin-bottom: 1.75rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.ca-featured-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
}

@media (min-width: 1024px) {
  .ca-featured-card.has-img {
    grid-template-columns: 5fr 7fr;
  }
}

.ca-featured-banner {
  position: relative;
  min-height: 200px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.ca-featured-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* 3-Column Articles Grid */
.ca-grid-3col {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 640px) {
  .ca-grid-3col { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
  .ca-grid-3col { grid-template-columns: repeat(3, 1fr); }
}

/* Grid Card Component */
.ca-card-item {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 1rem;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.ca-card-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.4);
  border-color: #475569;
}

/* Card Visual Banner Top */
.ca-card-banner {
  position: relative;
  min-height: 150px;
  padding: 1.125rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
}

.ca-card-top-row {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.ca-card-bottom-row {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.95);
  font-weight: 500;
  margin-top: 1.5rem;
}

/* Badges */
.ca-badge-category {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.65rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background-color: rgba(0, 0, 0, 0.55);
  color: #ffffff !important;
  border: 1px solid rgba(255, 255, 255, 0.25);
  line-height: 1;
}

.ca-badge-readtime {
  display: inline-block;
  padding: 0.35rem 0.625rem;
  border-radius: 0.5rem;
  font-size: 0.65rem;
  font-weight: 700;
  background-color: rgba(0, 0, 0, 0.45);
  color: rgba(255, 255, 255, 0.95) !important;
  line-height: 1;
}

.ca-badge-pdf {
  background-color: #dc2626;
  color: #ffffff !important;
  font-size: 0.6rem;
  font-weight: 900;
  padding: 0.2rem 0.5rem;
  border-radius: 0.25rem;
  text-transform: uppercase;
}

/* Card Body Content */
.ca-card-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
}

.ca-card-title {
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.35;
  color: #ffffff !important;
  margin-bottom: 0.625rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-decoration: none !important;
}

.ca-card-item:hover .ca-card-title {
  color: #38bdf8 !important;
}

.ca-card-snippet {
  font-size: 0.75rem;
  line-height: 1.5;
  color: #cbd5e1;
  margin-bottom: 1.125rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Card Footer */
.ca-card-footer {
  padding-top: 0.875rem;
  border-top: 1px solid #334155;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ca-btn-details {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.45rem 0.875rem;
  background-color: #2563eb;
  color: #ffffff !important;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 0.5rem;
  text-decoration: none !important;
  transition: background-color 0.15s;
}
.ca-btn-details:hover {
  background-color: #1d4ed8;
}

.ca-views-count {
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 600;
}

/* Sidebar Styles */
.ca-side-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 1rem;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.ca-side-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #ffffff !important;
  margin-bottom: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ca-side-input {
  width: 100%;
  padding: 0.6rem 0.875rem;
  background-color: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  color: #ffffff !important;
  font-size: 0.75rem;
  outline: none;
  margin-bottom: 0.75rem;
}
</style>

<!-- Main Page Layout Grid -->
<div class="ca-pub-wrapper">
  <div class="ca-pub-layout">

    <!-- Sidebar (Desktop Left) -->
    <aside class="hidden md:block">
        <div style="position: sticky; top: 5rem;">
            
            <!-- Category Topics Navigation Card -->
            <div class="ca-side-card">
                <div class="ca-side-title">
                    <span>GK Categories</span>
                    <span style="font-size: 0.65rem; background-color: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 0.1rem 0.5rem; border-radius: 9999px; font-weight: 800;"><?= count($categories) ?></span>
                </div>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.375rem;">
                        <a href="/current-affairs" class="ca-chip <?= empty($category) && $pdfOnly === 0 ? 'ca-chip-active' : 'ca-chip-inactive' ?>" style="width: 100%; justify-content: space-between; border-radius: 0.5rem;">
                            <span>All News &amp; Updates</span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li style="margin-bottom: 0.375rem;">
                            <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="ca-chip <?= $category === $cat ? 'ca-chip-active' : 'ca-chip-inactive' ?>" style="width: 100%; justify-content: space-between; border-radius: 0.5rem;">
                                <span><?= e($cat) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li style="padding-top: 0.5rem; border-top: 1px solid #334155; margin-top: 0.5rem;">
                        <a href="/current-affairs?pdf=1" class="ca-chip <?= $pdfOnly === 1 ? 'ca-chip-pdf-active' : 'ca-chip-pdf-inactive' ?>" style="width: 100%; justify-content: space-between; border-radius: 0.5rem;">
                            <span>📄 PDF Study Notes</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Search Filter Card -->
            <form method="get" action="/current-affairs" class="ca-side-card">
                <div class="ca-side-title">Search News</div>
                <?php if ($category): ?>
                    <input type="hidden" name="cat" value="<?= e($category) ?>">
                <?php endif; ?>
                <?php if ($pdfOnly): ?>
                    <input type="hidden" name="pdf" value="1">
                <?php endif; ?>

                <label style="display: block; font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.375rem; font-weight: 500;">Search Keyword</label>
                <input name="search" value="<?= e($search) ?>" placeholder="Topic, title or event..." class="ca-side-input"/>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="ca-btn-details" style="flex: 1; justify-content: center; padding: 0.5rem;">
                        Search
                    </button>
                    <?php if ($search || $category || $pdfOnly): ?>
                        <a href="/current-affairs" class="ca-chip ca-chip-inactive" style="padding: 0.5rem 0.75rem; border-radius: 0.5rem;">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Trending GK News Card -->
            <?php if (!empty($latestUpdates)): ?>
                <div class="ca-side-card">
                    <div class="ca-side-title">
                        <span style="display: flex; align-items: center; gap: 0.375rem;">
                            <span style="width: 0.5rem; height: 0.5rem; border-radius: 9999px; background-color: #ef4444; display: inline-block;"></span>
                            Trending GK News
                        </span>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($latestUpdates as $lu): ?>
                            <li style="margin-bottom: 0.75rem;">
                                <a href="/current-affairs/<?= e($lu['slug']) ?>" title="<?= e($lu['title']) ?>" style="font-size: 0.75rem; color: #cbd5e1; text-decoration: none; line-height: 1.35; display: block;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='#cbd5e1'">
                                    <strong style="color: #ffffff; display: block; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= e($lu['title']) ?></strong>
                                    <span style="font-size: 0.65rem; color: #94a3b8; margin-top: 0.125rem; display: block;">
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

    <!-- Main Content Area -->
    <main>
        
        <!-- Mobile Search Field -->
        <form method="get" action="/current-affairs" class="block md:hidden" style="margin-bottom: 1.25rem;">
            <div style="display: flex; gap: 0.5rem;">
                <input name="search" value="<?= e($search) ?>" placeholder="Search current affairs &amp; exam news..." class="ca-side-input" style="margin-bottom: 0; flex: 1;"/>
                <button type="submit" class="ca-btn-details" style="padding: 0.6rem 1rem;">
                    Search
                </button>
            </div>
        </form>

        <!-- Horizontal Category Filter Chips Bar -->
        <div class="ca-chips-bar">
            <a href="/current-affairs" class="ca-chip <?= empty($category) && $pdfOnly === 0 ? 'ca-chip-active' : 'ca-chip-inactive' ?>">
                All Updates
            </a>
            <a href="/current-affairs?pdf=1" class="ca-chip <?= $pdfOnly === 1 ? 'ca-chip-pdf-active' : 'ca-chip-pdf-inactive' ?>">
                📄 Free PDF Notes
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/current-affairs?cat=<?= urlencode($cat) ?>" class="ca-chip <?= $category === $cat ? 'ca-chip-active' : 'ca-chip-inactive' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Section Title & Results Count Header -->
        <div class="ca-header-row">
            <div>
                <h1 class="ca-header-title">
                    <span><?= $category ? e($category) . ' Current Affairs' : ($pdfOnly ? 'PDF Study Notes' : 'Current Affairs &amp; GK Updates') ?></span>
                    <span class="ca-header-count"><?= e($total) ?></span>
                </h1>
            </div>
            
            <?php if ($search || $category || $pdfOnly): ?>
                <a href="/current-affairs" style="font-size: 0.75rem; color: #38bdf8; text-decoration: none; font-weight: 700;">Clear Filters</a>
            <?php endif; ?>
        </div>

        <?php if (empty($articles)): ?>
            <div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; padding: 2.5rem; text-align: center;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #ffffff; margin-bottom: 0.5rem;">No current affairs articles found</h3>
                <p style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 1.25rem;">Try adjusting your search terms or category selection.</p>
                <a href="/current-affairs" class="ca-btn-details" style="display: inline-flex;">View All News</a>
            </div>
        <?php else: ?>
            
            <!-- FEATURED HERO SPOTLIGHT CARD -->
            <?php if ($showFeatured && $featuredArticle): 
                $featHeaderStyle = get_category_style($featuredArticle['category'] ?? 'General');
                $featHasImg = is_valid_thumbnail($featuredArticle['thumbnail']);
                $featReadTime = estimate_reading_time($featuredArticle['description']);
            ?>
                <div onclick="location.href='/current-affairs/<?= e($featuredArticle['slug']) ?>'" class="ca-featured-card <?= $featHasImg ? 'has-img' : '' ?>">
                    
                    <?php if ($featHasImg): ?>
                        <div class="ca-featured-banner">
                            <img 
                                src="<?= e($featuredArticle['thumbnail']) ?>"
                                loading="eager"
                                fetchpriority="high"
                                width="640"
                                height="360"
                                alt="<?= e($featuredArticle['title']) ?>"
                                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none;"
                            />
                            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.3), rgba(0,0,0,0.2));"></div>

                            <div class="ca-card-top-row">
                                <span style="background-color: #dc2626; color: #ffffff; font-size: 0.65rem; font-weight: 900; padding: 0.35rem 0.75rem; border-radius: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    ★ Featured Story
                                </span>
                                <span class="ca-badge-readtime">
                                    <?= $featReadTime ?>
                                </span>
                            </div>

                            <div class="ca-card-bottom-row">
                                <span><?= date('M d, Y', strtotime($featuredArticle['event_date'] ?? $featuredArticle['created_at'])) ?></span>
                                <?php if (!empty($featuredArticle['pdf_link'])): ?>
                                    <span class="ca-badge-pdf">PDF Notes</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="ca-featured-body" style="<?= !$featHasImg ? $featHeaderStyle : '' ?>">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <span class="ca-badge-category" style="<?= !$featHasImg ? 'background-color: rgba(0,0,0,0.35); border-color: rgba(255,255,255,0.3);' : '' ?>">
                                    <?= e($featuredArticle['category'] ?? 'General') ?>
                                </span>
                                <span style="font-size: 0.75rem; color: <?= $featHasImg ? '#94a3b8' : 'rgba(255,255,255,0.85)' ?>; font-weight: 500;">&bull; <?= number_format($featuredArticle['views']) ?> views</span>
                                <span style="font-size: 0.75rem; color: <?= $featHasImg ? '#94a3b8' : 'rgba(255,255,255,0.85)' ?>; font-weight: 500; margin-left: auto;"><?= $featReadTime ?></span>
                            </div>

                            <a href="/current-affairs/<?= e($featuredArticle['slug']) ?>" style="text-decoration: none;">
                                <h2 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; line-height: 1.35; margin-bottom: 0.75rem;">
                                    <?= e($featuredArticle['title']) ?>
                                </h2>
                            </a>

                            <p style="font-size: 0.8rem; color: <?= $featHasImg ? '#cbd5e1' : 'rgba(255,255,255,0.9)' ?>; line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= e(clean_markdown_snippet($featuredArticle['description'], 220)) ?>
                            </p>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid <?= $featHasImg ? '#334155' : 'rgba(255,255,255,0.25)' ?>;">
                            <a href="/current-affairs/<?= e($featuredArticle['slug']) ?>" class="ca-btn-details" style="<?= !$featHasImg ? 'background-color: #ffffff; color: #0f172a !important;' : '' ?>">
                                <span>Read Full Article &rarr;</span>
                            </a>
                            <span style="font-size: 0.75rem; color: <?= $featHasImg ? '#94a3b8' : 'rgba(255,255,255,0.8)' ?>; font-weight: 500;"><?= date('M d, Y', strtotime($featuredArticle['event_date'] ?? $featuredArticle['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CARDS GRID (3 Columns on Desktop) -->
            <div class="ca-grid-3col">
                <?php foreach ($gridArticles as $index => $item): 
                    $headerStyle = get_category_style($item['category'] ?? 'General');
                    $hasCustomImage = is_valid_thumbnail($item['thumbnail']);
                    $readTime = estimate_reading_time($item['description']);
                ?>
                    <article 
                        aria-label="<?= e($item['title']) ?>" 
                        onclick="location.href='/current-affairs/<?= e($item['slug']) ?>'" 
                        class="ca-card-item">
                        
                        <div>
                            <!-- Visual Banner Header -->
                            <div class="ca-card-banner" style="<?= !$hasCustomImage ? $headerStyle : '' ?>">
                                <?php if ($hasCustomImage): ?>
                                    <img 
                                        src="<?= e($item['thumbnail']) ?>"
                                        loading="lazy"
                                        decoding="async"
                                        width="640"
                                        height="360"
                                        alt="<?= e($item['title']) ?>"
                                        style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none;"
                                    />
                                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.3), rgba(0,0,0,0.2));"></div>
                                <?php else: ?>
                                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0.1), transparent);"></div>
                                <?php endif; ?>

                                <!-- Category Badge (Top Left) & Reading Time (Top Right) -->
                                <div class="ca-card-top-row">
                                    <span class="ca-badge-category">
                                        <?= e($item['category'] ?? 'General') ?>
                                    </span>
                                    <span class="ca-badge-readtime">
                                        <?= $readTime ?>
                                    </span>
                                </div>

                                <!-- Date & PDF Tag (Bottom Banner Overlay) -->
                                <div class="ca-card-bottom-row">
                                    <span><?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : date('M d, Y', strtotime($item['created_at'])) ?></span>
                                    <?php if (!empty($item['pdf_link'])): ?>
                                        <span class="ca-badge-pdf">PDF Notes</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Card Content Body -->
                            <div class="ca-card-body">
                                <div>
                                    <a href="/current-affairs/<?= e($item['slug']) ?>" class="ca-card-title">
                                        <?= e($item['title']) ?>
                                    </a>

                                    <p class="ca-card-snippet">
                                        <?= e(clean_markdown_snippet($item['description'], 130)) ?>
                                    </p>
                                </div>

                                <!-- Card Footer -->
                                <div class="ca-card-footer">
                                    <a class="ca-btn-details" href="/current-affairs/<?= e($item['slug']) ?>">
                                        Read Details
                                    </a>
                                    
                                    <span class="ca-views-count">
                                        👁️ <?= number_format($item['views']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; justify-content: center; gap: 0.375rem; margin-top: 2rem;">
                    <?php if ($page > 1): ?>
                        <a href="/current-affairs?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>&pdf=<?= $pdfOnly ?>" class="ca-chip ca-chip-inactive" style="border-radius: 0.5rem;">
                            ← Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/current-affairs?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>&pdf=<?= $pdfOnly ?>" class="ca-chip <?= $i === $page ? 'ca-chip-active' : 'ca-chip-inactive' ?>" style="border-radius: 0.5rem;">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="/current-affairs?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($category) ?>&pdf=<?= $pdfOnly ?>" class="ca-chip ca-chip-inactive" style="border-radius: 0.5rem;">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

  </div>
</div>

<?php require_once('_footer.php'); ?>

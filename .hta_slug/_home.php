<?php
$pageTitle = "FromCampus - JOB Notification Portal";
$pageDescription = "FromCampus - JOB Notification Portal";
$keywords = "Goverment JOBS, ITI JOBS, Railway Jobs, Engineer";
$author = "FromCampus";
$ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$canonicalUrl = "https://fromcampus.com/";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "WebSite",
    "name" => "FromCampus - JOB Notification Portal",
    "url" => "https://fromcampus.com/",
    "description" => "FromCampus - JOB Notification Portal",
    "keywords" => "Goverment JOBS, ITI JOBS, Railway Jobs, Engineer",
    "publisher" => [
        "@type" => "Organization",
        "name" => "FromCampus",
        "url" => "https://fromcampus.com/",
        "logo" => [
            "@type" => "ImageObject",
            "url" => "https://fromcampus.com/assets/logo/FromCampus_Color_text.png"
        ]
    ]
];

require_once('_header.php');

require __DIR__ . '/../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();

/**
 * CATEGORY TABS
 */
$CATEGORY_TABS = [
  ['label' => 'All',    'slug' => 'all'],
  ['label' => 'Bank',   'slug' => 'bank-jobs'],
  ['label' => 'Railway','slug' => 'railway-jobs'],
  ['label' => 'ITI',    'slug' => 'iti-jobs'],
  ['label' => 'Police', 'slug' => 'police-jobs'],
  ['label' => 'Army',   'slug' => 'army-jobs'],
  ['label' => 'Teaching', 'slug' => 'teaching-jobs'],
  ['label' => 'Others', 'slug' => 'others'],
];

// Inputs
$activeTab = isset($_GET['cat']) ? strtolower(trim($_GET['cat'])) : 'all';
$search    = isset($_GET['search']) ? trim($_GET['search']) : '';
$location  = isset($_GET['location']) ? trim($_GET['location']) : '';
$since     = isset($_GET['since']) ? (int)$_GET['since'] : 0;
$jobType   = isset($_GET['type']) ? trim($_GET['type']) : '';
$sort      = isset($_GET['sort']) ? trim($_GET['sort']) : 'recent';
$salaryMin = isset($_GET['smin']) ? (int)$_GET['smin'] : 0;
$salaryMax = isset($_GET['smax']) ? (int)$_GET['smax'] : 0;

// Pagination
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage= 9;
$offset = ($page - 1) * $perPage;

// Marquee (latest 20)
$marqStmt = $pdo->query("SELECT job_title, job_title_slug FROM jobs WHERE status='published' ORDER BY posted_date DESC LIMIT 20");
$marqueeJobs = $marqStmt->fetchAll();

// Latest Updates Section (last 7 days)
$latestStmt = $pdo->prepare("SELECT job_id, job_title, job_title_slug, posted_date FROM jobs WHERE status='published' AND posted_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY posted_date DESC LIMIT 5");
$latestStmt->execute();
$latestUpdates = $latestStmt->fetchAll();

// Build WHERE clause
$where = ["j.status='published'"];
$params = [];
$excludeSlugs = [];

// Get all category slugs from database to exclude for 'others'
try {
    $catRows = $pdo->query("SELECT category_slug FROM job_categories")->fetchAll();
    $dbCategorySlugs = array_map(function($cat) { return $cat['category_slug']; }, $catRows);
    
    foreach ($CATEGORY_TABS as $t) {
        if (!in_array($t['slug'], ['all','others'], true) && in_array($t['slug'], $dbCategorySlugs)) {
            $excludeSlugs[] = $t['slug'];
        }
    }
} catch(\Throwable $e){ 
    // If there's an error, use the predefined slugs
    foreach ($CATEGORY_TABS as $t) {
        if (!in_array($t['slug'], ['all','others'], true)) {
            $excludeSlugs[] = $t['slug'];
        }
    }
}

if ($activeTab !== 'all' && $activeTab !== 'others') {
    $where[] = "j.category_slug = :catslug"; 
    $params[':catslug'] = $activeTab;
} elseif ($activeTab === 'others' && !empty($excludeSlugs)) {
    $placeholders = implode(',', array_fill(0, count($excludeSlugs), '?'));
    $where[] = "j.category_slug NOT IN ($placeholders)";
    foreach ($excludeSlugs as $index => $slug) {
        $params[':exclude_' . $index] = $slug;
    }
}

if ($search) {
    $where[] = "(j.job_title LIKE :search1 OR j.company_name LIKE :search2 OR j.description LIKE :search3)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

if ($location) {
    $where[] = "j.location LIKE :location"; 
    $params[':location'] = "%$location%";
}

if ($since) {
    $where[] = "j.posted_date >= DATE_SUB(NOW(), INTERVAL :since DAY)"; 
    $params[':since'] = $since;
}

if ($jobType) {
    $where[] = "j.job_type = :jobType"; 
    $params[':jobType'] = $jobType;
}

if ($salaryMin) {
    $where[] = "j.min_salary >= :salaryMin"; 
    $params[':salaryMin'] = $salaryMin;
}

if ($salaryMax) {
    $where[] = "j.max_salary <= :salaryMax"; 
    $params[':salaryMax'] = $salaryMax;
}

$whereSql = implode(' AND ', $where);
$orderSql = $sort === 'last_date' ? "j.last_date ASC, j.posted_date DESC" : "j.posted_date DESC";

// Count total jobs
$countSql = "SELECT COUNT(*) FROM jobs j WHERE $whereSql";
$stmt = $pdo->prepare($countSql);
foreach ($params as $k => $v) {
    // Skip the exclude parameters as they are bound by position in the NOT IN clause
    if (strpos($k, ':exclude_') === 0) continue;
    
    $paramType = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($k, $v, $paramType);
}

// Bind exclude parameters for NOT IN clause
if ($activeTab === 'others' && !empty($excludeSlugs)) {
    foreach ($excludeSlugs as $index => $slug) {
        $stmt->bindValue($index + 1, $slug, PDO::PARAM_STR);
    }
}

$stmt->execute();
$total = (int)$stmt->fetchColumn();

// Fetch jobs with pagination
$sql = "SELECT j.*, c.category_name FROM jobs j LEFT JOIN job_categories c ON j.category_slug = c.category_slug WHERE $whereSql ORDER BY $orderSql LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind all parameters
foreach ($params as $k => $v) {
    // Skip the exclude parameters as they are bound by position in the NOT IN clause
    if (strpos($k, ':exclude_') === 0) continue;
    
    $paramType = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($k, $v, $paramType);
}

// Bind exclude parameters for NOT IN clause
if ($activeTab === 'others' && !empty($excludeSlugs)) {
    foreach ($excludeSlugs as $index => $slug) {
        $stmt->bindValue($index + 1, $slug, PDO::PARAM_STR);
    }
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll();


// $updatesStmt = $pdo->prepare("SELECT * FROM updates WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 20");
$updatesStmt = $pdo->prepare("SELECT * FROM updates ORDER BY created_at DESC LIMIT 20");
$updatesStmt->execute();
$currentUpdates = $updatesStmt->fetchAll();
?>

<!-- Loading Placeholder (shown initially) -->
<div id="loading-placeholder" class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Subnav Placeholder -->
    <div class="sticky top-0 z-30 bg-white/90 dark:bg-gray-900/90 backdrop-blur border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2">
                <?php for($i = 0; $i < 8; $i++): ?>
                <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded-full w-20 animate-pulse"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Marquee Placeholder -->
    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex items-center gap-3 overflow-hidden">
            <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-16 animate-pulse"></div>
            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
        </div>
    </div>

    <!-- Main Content Placeholder -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Sidebar Placeholder -->
        <aside class="hidden md:block md:col-span-3">
            <div class="sticky top-20 space-y-4">
                <!-- Latest Updates Card Placeholder -->
                <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
                    <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-32 mb-3 animate-pulse"></div>
                    <ul class="space-y-3">
                        <?php for($i = 0; $i < 5; $i++): ?>
                        <li class="flex items-start gap-2">
                            <div class="mt-0.5 w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-700 animate-pulse"></div>
                            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>

                <!-- Filter Card Placeholder -->
                <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
                    <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-20 mb-3 animate-pulse"></div>
                    <?php for($i = 0; $i < 6; $i++): ?>
                    <div class="h-10 bg-gray-300 dark:bg-gray-700 rounded w-full mb-3 animate-pulse"></div>
                    <?php endfor; ?>
                    <div class="flex gap-3 mt-4">
                        <div class="h-10 bg-gray-300 dark:bg-gray-700 rounded flex-1 animate-pulse"></div>
                        <div class="h-10 bg-gray-300 dark:bg-gray-700 rounded flex-1 animate-pulse"></div>
                    </div>
                </div>

                <!-- Quick Links Placeholder -->
                <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
                    <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-20 mb-3 animate-pulse"></div>
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full mb-2 animate-pulse"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </aside>

        <!-- Content Placeholder -->
        <main class="md:col-span-9">
            <div class="flex items-baseline justify-between mb-4">
                <div class="h-7 bg-gray-300 dark:bg-gray-700 rounded w-48 animate-pulse"></div>
                <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-24 animate-pulse"></div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php for($i = 0; $i < 6; $i++): ?>
                <article class="border rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden w-full">
                    <div class="w-full aspect-[16/9] bg-gray-300 dark:bg-gray-700 animate-pulse"></div>
                    <div class="p-3">
                        <div class="flex justify-between items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                                <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-10 animate-pulse"></div>
                                <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-12 animate-pulse"></div>
                            </div>
                        </div>

                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-16 mt-2 animate-pulse"></div>

                        <div class="mt-3 space-y-2">
                            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-2/3 animate-pulse"></div>
                        </div>

                        <div class="mt-4 flex justify-between items-center">
                            <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-16 animate-pulse"></div>
                            <div class="flex items-center gap-3">
                                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-12 animate-pulse"></div>
                                <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-16 animate-pulse"></div>
                            </div>
                        </div>

                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-32 mt-3 animate-pulse"></div>
                    </div>
                </article>
                <?php endfor; ?>
            </div>

            <!-- Pagination Placeholder -->
            <div class="mt-8 flex justify-center gap-2">
                <?php for($i = 0; $i < 5; $i++): ?>
                <div class="h-10 bg-gray-300 dark:bg-gray-700 rounded w-10 animate-pulse"></div>
                <?php endfor; ?>
            </div>

            <!-- Current Updates Placeholder -->
            <div class="mt-4 mb-8">
                <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-1/4 mb-6 animate-pulse"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                    <?php for($i = 0; $i < 4; $i++): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3">
                        <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-2/3 mb-3 animate-pulse"></div>
                        <div class="space-y-3">
                            <?php for($j = 0; $j < 3; $j++): ?>
                            <div class="p-2 border rounded dark:border-gray-700">
                                <div class="flex justify-between mb-2">
                                    <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 animate-pulse"></div>
                                    <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-10 animate-pulse"></div>
                                </div>
                                <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/2 animate-pulse"></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Actual Content (hidden initially) -->
<div id="actual-content" style="display: none;">

<!-- ===== Category Sub-Nav (sticky under main nav) ===== -->
<div id="subnav" class="sticky top-0 z-30 bg-white/90 dark:bg-gray-900/90 backdrop-blur border-b border-gray-200 dark:border-gray-700">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2">
      <?php foreach ($CATEGORY_TABS as $t): 
        $active = ($activeTab===$t['slug']);
        $cls = $active ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200';
        $url = BASE_URL.'?cat='.urlencode($t['slug'])
              .'&search='.urlencode($search)
              .'&location='.urlencode($location)
              .'&since='.urlencode((string)$since)
              .'&type='.urlencode($jobType)
              .'&smin='.urlencode((string)$salaryMin)
              .'&smax='.urlencode((string)$salaryMax)
              .'&sort='.urlencode($sort);
      ?>
      <a href="<?= e($url) ?>" class="whitespace-nowrap px-3 py-1.5 rounded-full text-sm hover:opacity-90 transition <?= $cls ?>">
        <?= e($t['label']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>


<!-- ===== Marquee (Native tag as requested) ===== -->
<?php if ($marqueeJobs): ?>
<div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex items-center gap-3 overflow-hidden">
    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-yellow-300 text-black">Breaking</span>
    <div class="marquee-wrap">
      <div class="marquee-content">
        <?php foreach ($marqueeJobs as $i=>$mj): ?>
          <a class="marquee-link" href="<?= BASE_URL ?>job?slug=<?= e($mj['job_title_slug']) ?>">
            <?= e($mj['job_title']) ?>
          </a><?= $i<count($marqueeJobs)-1 ? ' • ' : '' ?>
        <?php endforeach; ?>
        <!-- Duplicate for seamless loop -->
        <?php foreach ($marqueeJobs as $i=>$mj): ?>
          <a class="marquee-link" href="<?= BASE_URL ?>job?slug=<?= e($mj['job_title_slug']) ?>">
            <?= e($mj['job_title']) ?>
          </a><?= $i<count($marqueeJobs)-1 ? ' • ' : '' ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== Layout: Sidebar + Content ===== -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-1 md:grid-cols-12 gap-6">

  <!-- Sidebar (Desktop) - Fixed Position -->
  <aside class="hidden md:block md:col-span-3">
    <div class="sticky top-20 space-y-4">
      <!-- Latest Updates Card -->
      <?php if (!empty($latestUpdates)): ?>
      <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
        <h3 class="text-sm font-semibold mb-3">Latest Updates</h3>
        <ul class="space-y-3">
          <?php foreach ($latestUpdates as $lu): ?>
          <li class="flex items-start gap-2">
            <span class="inline-block mt-0.5 w-1.5 h-1.5 rounded-full bg-blue-600"></span>
            <a href="<?= BASE_URL ?>job?slug=<?= e($lu['job_title_slug']) ?>" class="text-sm hover:underline line-clamp-2">
              <?= e($lu['job_title']) ?>
              <span class="block text-xs text-gray-500 mt-0.5"><?= date('M d', strtotime($lu['posted_date'])) ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <!-- Filter Card -->
      <form id="filterForm" method="get" class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
        <input type="hidden" name="cat" value="<?= e($activeTab) ?>">
        <h3 class="text-sm font-semibold mb-3">Filters</h3>

        <label class="block text-xs mb-1">Keyword</label>
        <input name="search" value="<?= e($search) ?>" placeholder="Title, Company" class="w-full mb-3 px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700"/>

        <label for="location" class="block text-xs mb-1">Location</label>
        <select id="location" name="location" <?= e($location) ?> class="w-full mb-3 px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
          <option value="">-- Select State --</option>
          <?php foreach ($indianStates as $slug => $name): ?>
            <option value="<?= htmlspecialchars($slug) ?>">
              <?= htmlspecialchars($name) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="since" class="block text-xs mb-1">Since</label>
            <select id="since" name="since" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
              <option value="0"  <?= $since==0?'selected':'' ?>>Any</option>
              <option value="7"  <?= $since==7?'selected':'' ?>>7 days</option>
              <option value="30" <?= $since==30?'selected':'' ?>>30 days</option>
              <option value="90" <?= $since==90?'selected':'' ?>>90 days</option>
            </select>
          </div>
          <div>
            <label for class="block text-xs mb-1">Type</label>
            <select id="type" name="type" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
              <option value="" <?= $jobType===''?'selected':'' ?>>Any</option>
              <option value="full-time"  <?= $jobType==='full-time'?'selected':'' ?>>Full-time</option>
              <option value="part-time"  <?= $jobType==='part-time'?'selected':'' ?>>Part-time</option>
              <option value="contract"   <?= $jobType==='contract'?'selected':'' ?>>Contract</option>
              <option value="internship" <?= $jobType==='internship'?'selected':'' ?>>Internship</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-3">
          <div>
            <label class="block text-xs mb-1">Salary Min</label>
            <input type="number" name="smin" value="<?= e((string)$salaryMin) ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700"/>
          </div>
          <div>
            <label class="block text-xs mb-1">Salary Max</label>
            <input type="number" name="smax" value="<?= e((string)$salaryMax) ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700"/>
          </div>
        </div>

        <label for="sort" class="block text-xs mb-1 mt-3">Sort</label>
        <select name="sort" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
          <option value="recent"    <?= $sort==='recent'?'selected':'' ?>>Recent first</option>
          <option value="last_date" <?= $sort==='last_date'?'selected':'' ?>>Closest last date</option>
        </select>

        <div class="flex gap-3 mt-4">
          <a href="<?= BASE_URL ?>?cat=<?= e($activeTab) ?>" class="flex-1 px-3 py-2 border rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-center">Reset</a>
          <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply</button>
        </div>
      </form>

      <!-- Quick Links -->
      <div class="p-4 border rounded-xl bg-white dark:bg-gray-900 shadow">
        <h3 class="text-sm font-semibold mb-3">Quick Links</h3>
        <ul class="space-y-2 text-sm">
          <li><a class="hover:underline" href="<?= BASE_URL ?>mock_tests">Mock Tests</a></li>
          <li><a class="hover:underline" href="<?= BASE_URL ?>notifications">Push Alerts</a></li>
          <li><a class="hover:underline" href="<?= BASE_URL ?>contact">Contact</a></li>
        </ul>
      </div>
    </div>
  </aside>

  <!-- Drawer (Mobile Filters) -->
  <div id="drawer" class="fixed inset-0 z-40 pointer-events-none" style="overflow: hidden;">
    <div id="drawerBackdrop" class="absolute inset-0 bg-black/30 opacity-0 transition-opacity"></div>
    <div id="drawerPanel" class="absolute top-0 left-0 h-full w-10/12 max-w-sm -translate-x-full transition-transform bg-white dark:bg-gray-900 border-r dark:border-gray-800 shadow-xl overflow-y-auto">
      <div class="h-full p-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold">Filters</h3>
          <button id="closeDrawer" class="px-2 py-1 border rounded dark:border-gray-700">Close</button>
        </div>
        <!-- Same filter form as sidebar -->
        <form method="get" class="space-y-3">
          <input type="hidden" name="cat" value="<?= e($activeTab) ?>">
          
          <input name="search" value="<?= e($search) ?>" placeholder="Keyword" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700"/>
          <select name="location" <?= e($location) ?> class="w-full mb-3 px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
            <option value="">-- Select State --</option>
            <?php foreach ($indianStates as $slug => $name): ?>
              <option value="<?= htmlspecialchars($slug) ?>">
                <?= htmlspecialchars($name) ?>
              </option>
            <?php endforeach; ?>
          </select>
          
          <div class="grid grid-cols-2 gap-3">
            <label for="since" class="sr-only">Since</label>
            <select id="since" name="since" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
              <option value="0"  <?= $since==0?'selected':'' ?>>Any</option>
              <option value="7"  <?= $since==7?'selected':'' ?>>7 days</option>
              <option value="30" <?= $since==30?'selected':'' ?>>30 days</option>
              <option value="90" <?= $since==90?'selected':'' ?>>90 days</option>
            </select>
            <label for="type" class="sr-only">Type</label>
            <select id="type" name="type" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
              <option value="" <?= $jobType===''?'selected':'' ?>>Any</option>
              <option value="full-time"  <?= $jobType==='full-time'?'selected':'' ?>>Full-time</option>
              <option value="part-time"  <?= $jobType==='part-time'?'selected':'' ?>>Part-time</option>
              <option value="contract"   <?= $jobType==='contract'?'selected':'' ?>>Contract</option>
              <option value="internship" <?= $jobType==='internship'?'selected':'' ?>>Internship</option>
            </select>
            
            <input type="number" name="smin" value="<?= e((string)$salaryMin) ?>" placeholder="Salary Min" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700"/>
            
            <input type="number" name="smax" value="<?= e((string)$salaryMax) ?>" placeholder="Salary Max" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700"/>
          </div>
          <label for="sort" class="sr-only">Sort By</label>
          <select name="sort" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
            <option value="recent"    <?= $sort==='recent'?'selected':'' ?>>Recent first</option>
            <option value="last_date" <?= $sort==='last_date'?'selected':'' ?>>Closest last date</option>
          </select>

          <div class="flex gap-3 pt-2">
            <a href="<?= BASE_URL ?>?cat=<?= e($activeTab) ?>" class="flex-1 px-3 py-2 border rounded text-center">Reset</a>
            <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded">Apply</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Content -->
  <main class="md:col-span-9">
    <div class="flex items-baseline justify-between">
      <h1 class="text-xl font-bold">
        <?php $cur='All'; foreach($CATEGORY_TABS as $t){ if($t['slug']===$activeTab){ $cur=$t['label']; break; } } ?>
        <?= e($cur) ?> Jobs — <?= e($total) ?> found
      </h1>
      <a href="<?= BASE_URL ?>saved-jobs" class="text-sm px-3 py-1.5 rounded bg-amber-200 text-amber-900 hover:bg-amber-300" id="savedCountBtn">
        Saved (<span id="savedCount">0</span>)
      </a>
    </div>

    <?php if (empty($jobs)): ?>
      <div class="mt-6 p-6 border rounded-xl bg-white dark:bg-gray-800">No jobs matched your filters.</div>
    <?php else: ?>
      <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($jobs as $job): ?>
        <article aria-label="<?= e($job['job_title']) ?>" onclick="location.href='<?= BASE_URL ?>job?slug=<?= e($job['job_title_slug']) ?>'" class="group border cursor-pointer rounded-2xl bg-white dark:bg-gray-800 shadow hover:shadow-2xl transition overflow-hidden w-full">
          <?php if (!empty($job['thumbnail'])): ?>
          <div class="w-full aspect-[16/9] overflow-hidden rounded">
            <img src="<?= e($job['thumbnail']) ?>" fetchpriority="high"  alt="<?= e($job['job_title']) ?>" class="w-full h-full object-cover" />
          </div>
          <?php endif; ?>
          <div class="p-3">
            <div class="flex justify-between items-start gap-3">
              <div class="min-w-0">
                <a href="<?= BASE_URL ?>job?slug=<?= e($job['job_title_slug']) ?>">
                  <h1 class="text-lg font-semibold text-blue-600 group-hover:underline truncate"><?= e($job['job_title']) ?></h1>
                </a>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 truncate">🏢 <?= e($job['company_name']) ?> • 📍 <?= e($job['location']) ?></p>
              </div>
              <div class="flex items-center gap-2">
                <?php if (strtotime($job['posted_date']) > strtotime('-7 days')): ?>
                  <span class="px-2 py-0.5 text-[10px] bg-green-100 text-green-800 rounded-full">New</span>
                <?php endif; ?>
                <?php if (!empty($job['job_type'])): ?>
                  <span class="px-2 py-0.5 text-[10px] bg-indigo-100 text-indigo-800 rounded-full"><?= e(ucfirst($job['job_type'])) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($job['category_name'])): ?>
              <span class="inline-block mt-2 px-2 py-0.5 text-[11px] bg-blue-100 text-blue-800 rounded-full"><?= e($job['category_name']) ?></span>
            <?php  endif;  ?>

              <div class="mt-3 text-sm text-gray-700 dark:text-gray-200 clamp-3">
                <h2><?= $Parsedown->text($job['description']) ?></h2>
              </div>

            <div class="mt-4 flex justify-between items-center">
              <a class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700" href="<?= BASE_URL ?>job?slug=<?= e($job['job_title_slug']) ?>">Details</a>
              <div class="flex items-center gap-3">
                <button class="save-btn text-sm px-2 py-1 border rounded hover:bg-gray-100 dark:hover:bg-gray-700" data-slug="<?= e($job['job_title_slug']) ?>">★ Save</button>
                <span class="text-xs text-gray-500"><?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
              </div>
            </div>

            <?php if (!empty($job['last_date'])): ?>
              <div class="mt-3 text-xs text-red-600 dark:text-red-400">Last date: <?= e(date('M d, Y', strtotime($job['last_date']))) ?></div>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <div class="mt-8 flex justify-center">
        <?php
          $base = BASE_URL.'?cat='.urlencode($activeTab).'&search='.urlencode($search).'&location='.urlencode($location).'&since='.urlencode((string)$since).'&type='.urlencode($jobType).'&smin='.urlencode((string)$salaryMin).'&smax='.urlencode((string)$salaryMax).'&sort='.urlencode($sort).'&';
          echo paginate($total, $perPage, $page, $base);
        ?>
      </div>
    <?php endif; ?>
    <button aria-label="Open drawer menu" id="openDrawer"class="fixed bottom-20 right-4 md:right-20  px-4 py-2 text-sm rounded-full shadow-lg bg-blue-600 text-white dark:bg-gray-800 dark:border-gray-700 flex flex-col items-center justify-center space-x-2 w-16 h-16 rounded-full">
      <svg width="20px" height="20px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>Filter</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Filter"> <rect id="Rectangle" fill-rule="nonzero" x="0" y="0" width="24" height="24"> </rect> <line x1="4" y1="5" x2="16" y2="5" id="Path" stroke="#ffffff" stroke-width="2" stroke-linecap="round"> </line> <line x1="4" y1="12" x2="10" y2="12" id="Path" stroke="#ffffff" stroke-width="2" stroke-linecap="round"> </line> <line x1="14" y1="12" x2="20" y2="12" id="Path" stroke="#ffffff" stroke-width="2" stroke-linecap="round"> </line> <line x1="8" y1="19" x2="20" y2="19" id="Path" stroke="#ffffff" stroke-width="2" stroke-linecap="round"> </line> <circle id="Oval" stroke="#ffffff" stroke-width="2" stroke-linecap="round" cx="18" cy="5" r="2"> </circle> <circle id="Oval" stroke="#ffffff" stroke-width="2" stroke-linecap="round" cx="12" cy="12" r="2"> </circle> <circle id="Oval" stroke="#ffffff" stroke-width="2" stroke-linecap="round" cx="6" cy="19" r="2"> </circle> </g> </g> </g></svg>  
      Filters
    </button>

    <!-- Current Updates Section -->
    <?php if (!empty($currentUpdates)): ?>
      <div class="mt-4 mb-8">
          <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                  <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                  Current Updates
              </h2>
              <a href="<?= BASE_URL ?>updates" class="text-blue-600 dark:text-blue-300 hover:underline font-medium flex items-center gap-1">
                  View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
              </a>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
              <?php 
              $updateTypes = [
                  'exam' => ['title' => 'Exam Updates', 'color' => 'blue', 'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z'],
                  'result' => ['title' => 'Result Updates', 'color' => 'green', 'icon' => 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z'],
                  'syllabus' => ['title' => 'Syllabus Updates', 'color' => 'yellow', 'icon' => 'M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l1.5-1.5a2 2 0 11-2.828-2.828l3-3z'],
                  'ans_key' => ['title' => 'Answer Key', 'color' => 'red', 'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z']
              ];
              
              foreach ($updateTypes as $type => $typeInfo): 
                  $filteredUpdates = array_filter($currentUpdates, function($update) use ($type) {
                      return $update['update_type'] === $type;
                  });
                  
                  // শুধুমাত্র সেই টাইপের updates থাকলে show করবে
                  if (!empty($filteredUpdates)):
              ?>
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
                  <div class="bg-<?= $typeInfo['color'] ?>-600 p-3 flex items-center">
                      <svg class="w-5 h-5 mr-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="<?= $typeInfo['icon'] ?>" clip-rule="evenodd"/>
                      </svg>
                      <h3 class="text-lg font-bold text-white"><?= $typeInfo['title'] ?></h3>
                  </div>
                  <div class="p-3 space-y-3 max-h-80 overflow-y-auto scroll-container">
                      <div class="scroll-inner">
                          <?php foreach ($filteredUpdates as $update): ?>
                          <a href="<?= BASE_URL ?>updates/details?slug=<?= e($update['slug']) ?>" class="block p-2 border rounded hover:shadow-md transition dark:border-gray-700 dark:hover:bg-gray-700">
                              <div class="flex justify-between items-start">
                                  <h4 class="text-sm font-medium dark:text-white line-clamp-2 flex-1"><?= e($update['title']) ?></h4>
                                  <?php if (strtotime($update['created_at']) > strtotime('-2 days')): ?>
                                      <span class="blink-badge" style="background-color: <?= 
                                          $typeInfo['color'] === 'blue' ? '#3b82f6' : 
                                          ($typeInfo['color'] === 'green' ? '#10b981' : 
                                          ($typeInfo['color'] === 'yellow' ? '#f59e0b' : '#ef4444')) 
                                      ?>;">NEW</span>
                                  <?php endif; ?>
                              </div>
                              <p class="text-xs text-gray-600 dark:text-gray-300 mt-1"><?= date('M d, Y', strtotime($update['created_at'])) ?></p>
                          </a>
                          <?php endforeach; ?>
                      </div>
                  </div>
              </div>
              <?php 
                  endif;
              endforeach; 
              ?>
          </div>
      </div>
    <?php endif; ?>
  </main>
</div>
</div>
<script type="application/ld+json">
  <?= json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) ?>
</script>

<!-- ===== Utilities ===== -->
<style>
  .scroll-container {
    position: relative;
    overflow: hidden;
    height: 20rem; /* তোমার max-h-80 */
  }

  .scroll-inner {
    display: flex;
    flex-direction: column;
    animation: scrollLoop 10s linear infinite;
  }

  .scroll-container:hover .scroll-inner {
    animation-play-state: paused;
  }

  @keyframes scrollLoop {
    0% {
      transform: translateY(0);
    }
    100% {
      transform: translateY(-50%);
    }
  }

    /* Blinking badge animation */
    @keyframes blink {
        0%, 50%, 100% { opacity: 1; }
        25%, 75% { opacity: 0.5; }
    }

    .blink-badge {
        display: inline-block;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: bold;
        border-radius: 4px;
        color: #fff;
        animation: blink 1.5s infinite;
        flex-shrink: 0;
        margin-left: 8px;
        line-height: 1.2;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  .line-clamp-3 {
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
  }
  @media (max-width: 767px) {
    #drawerPanel {
      width: 85%;
    }
  }
  
  /* Animation for placeholder */
  @keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
  }

  .animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
  
  .clamp-3 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    overflow: hidden;
  }

  .clamp-3 * {
    display: inline;
  }

  .marquee-wrap {
    overflow: hidden;
    white-space: nowrap;
    position: relative;
  }

  .marquee-content {
    display: inline-block;
    white-space: nowrap;
    will-change: transform;
    animation: marquee 120s linear infinite;
  }

  /* Hover করলে marquee থামবে */
  .marquee-wrap:hover .marquee-content {
    animation-play-state: paused;
  }

  /* লিঙ্ক স্টাইল */
  .marquee-link {
    text-decoration: none;
    margin: 0 6px;
    transition: all 0.5s ease-in-out;
  }

  /* শুধু লিঙ্ক hover করলে underline হবে */
  .marquee-link:hover {
    text-decoration: underline;
  }

  @keyframes marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
  }
</style>

<script>
  // Show actual content and hide placeholder once page is loaded
  document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure all content is rendered
    setTimeout(function() {
      document.getElementById('loading-placeholder').style.display = 'none';
      document.getElementById('actual-content').style.display = 'block';
    }, 300);
  });

  // Mobile Drawer
  const openBtn = document.getElementById('openDrawer');
  const closeBtn = document.getElementById('closeDrawer');
  const drawer = document.getElementById('drawer');
  const panel  = document.getElementById('drawerPanel');
  const back   = document.getElementById('drawerBackdrop');

  function openDrawer(){
    drawer.classList.remove('pointer-events-none');
    document.body.style.overflow = 'hidden';
    back.classList.remove('opacity-0');
    back.classList.add('opacity-100');
    panel.classList.remove('-translate-x-full');
  }
  function closeDrawer(){
    back.classList.add('opacity-0');
    back.classList.remove('opacity-100');
    panel.classList.add('-translate-x-full');
    document.body.style.overflow = '';
    setTimeout(()=>drawer.classList.add('pointer-events-none'), 200);
  }
  if (openBtn) openBtn.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (back) back.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeDrawer(); });

  // Saved jobs (localStorage)
  function getSaved(){ try{return JSON.parse(localStorage.getItem('saved_jobs')||'[]');}catch(_){return[];} }
  function setSaved(v){ localStorage.setItem('saved_jobs', JSON.stringify(v)); updateSavedCount(); }
  function updateSavedCount(){ const el = document.getElementById('savedCount'); if(el) el.textContent = (getSaved()||[]).length; }
  document.addEventListener('click', (e)=>{
    if (e.target && e.target.classList.contains('save-btn')) {
      const slug = e.target.getAttribute('data-slug');
      let s = getSaved();
      if (!s.includes(slug)) { 
        s.push(slug); 
        setSaved(s); 
        e.target.textContent='✓ Saved';
        e.target.classList.add('bg-amber-100', 'text-amber-800');
      } else { 
        s = s.filter(x=>x!==slug); 
        setSaved(s); 
        e.target.textContent='★ Save';
        e.target.classList.remove('bg-amber-100', 'text-amber-800');
      }
    }
  });
  updateSavedCount();
</script>
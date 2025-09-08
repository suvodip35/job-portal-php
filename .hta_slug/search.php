<?php
require_once('_header.php');
// require_once __DIR__ . '/includes/header.php';
// require_once __DIR__ . '/functions.php';

$q = trim($_GET['q'] ?? '');
$siteTitle = 'Search: ' . ($q ?: '');
$metaDesc = 'Search results for ' . e($q);

if ($q === '') {
    echo "<p class='text-gray-600'>Please enter a search term.</p>";
    // require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Basic search - wildcard
$term = '%' . str_replace(' ', '%', $q) . '%';
$stmt = $pdo->prepare("SELECT job_id, job_title, job_title_slug, company_name, location, posted_date FROM jobs WHERE status='published' AND (job_title LIKE :t1 OR description LIKE :t2 OR company_name LIKE :t3) ORDER BY posted_date DESC LIMIT 50");
$stmt->execute([ ':t1' => $term, ':t2' => $term, ':t3' => $term, ]);
$results = $stmt->fetchAll();
?>
<h1 class="text-2xl font-bold mb-4">Search Results for "<?= e($q) ?>"</h1>

<?php if (empty($results)): ?>
  <p>No results found.</p>
<?php else: ?>
  <div class="grid gap-4">
    <?php foreach ($results as $r): ?>
      <div class="p-3 border rounded bg-white dark:bg-gray-800">
        <a aria-label="<?= e($r['job_title']) ?>" href="<?= BASE_URL ?>job?slug=<?= e($r['job_title_slug']) ?>"><h3 class="font-semibold"><?= e($r['job_title']) ?></h3></a>
        <p class="text-sm text-gray-600"><?= e($r['company_name']) ?> • <?= e($r['location']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php 
  // require_once __DIR__ . '/includes/footer.php';
?>

<?php
require_once __DIR__ . '/.hta_config/functions.php';
header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(BASE_URL, '/');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <!-- Homepage -->
  <url>
    <loc><?= e($base) ?></loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Updates main page -->
  <url>
    <loc><?= e($base . '/updates') ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>

<?php
// Jobs
$stmt = $pdo->query("SELECT job_title_slug, posted_date FROM jobs WHERE status='published' ORDER BY posted_date DESC LIMIT 1000");
while ($r = $stmt->fetch()) {
    $loc = $base . '/job?slug=' . urlencode($r['job_title_slug']);
    $lastmod = date('Y-m-d', strtotime($r['posted_date']));
    echo "<url>\n";
    echo "  <loc>" . e($loc) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>weekly</changefreq>\n";
    echo "  <priority>0.8</priority>\n";
    echo "</url>\n";
}

// Updates
$stmt = $pdo->query("SELECT slug, update_type, created_at FROM updates ORDER BY created_at DESC LIMIT 1000");
while ($r = $stmt->fetch()) {
    $loc = $base . '/updates/' . urlencode($r['slug']);
    $lastmod = date('Y-m-d', strtotime($r['created_at']));

    // Mapping based on update_type
    switch ($r['update_type']) {
        case 'exam':
            $priority = '0.9';
            $freq = 'daily';
            break;
        case 'admit_card':
            $priority = '0.8';
            $freq = 'weekly';
            break;
        case 'result':
            $priority = '0.8';
            $freq = 'weekly';
            break;
        case 'notice':
        default:
            $priority = '0.7';
            $freq = 'monthly';
            break;
    }

    echo "<url>\n";
    echo "  <loc>" . e($loc) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>$freq</changefreq>\n";
    echo "  <priority>$priority</priority>\n";
    echo "</url>\n";
}
?>
</urlset>

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

  <!-- Static Pages -->
  <url><loc><?= e($base) ?>/about-us</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <url><loc><?= e($base) ?>/terms</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <url><loc><?= e($base) ?>/privacy-policy</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <url><loc><?= e($base) ?>/contact</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>

  <!-- Updates Main Page -->
  <url>
    <loc><?= e($base . '/updates') ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>

  <!-- Books Main Page -->
  <url>
    <loc><?= e($base . '/books') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>

<?php
// ============================
// JOBS + AMP JOB URLs
// ============================
$stmt = $pdo->query("SELECT job_title_slug, posted_date FROM jobs WHERE status='published' ORDER BY posted_date DESC LIMIT 2000");
while ($r = $stmt->fetch()) {
    $slug = urlencode($r['job_title_slug']);
    $lastmod = date('Y-m-d', strtotime($r['posted_date']));

    // Normal job page
    $loc = $base . '/job?slug=' . $slug;
    echo "<url>\n";
    echo "  <loc>" . e($loc) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>weekly</changefreq>\n";
    echo "  <priority>0.8</priority>\n";
    echo "</url>\n";

    // AMP job page
    $amp = $base . '/amp/job?slug=' . $slug;
    echo "<url>\n";
    echo "  <loc>" . e($amp) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>weekly</changefreq>\n";
    echo "  <priority>0.7</priority>\n";
    echo "</url>\n";
}

// ============================
// UPDATES + AMP (OPTIONAL)
// ============================
$stmt = $pdo->query("SELECT slug, update_type, created_at FROM updates ORDER BY created_at DESC LIMIT 2000");
while ($r = $stmt->fetch()) {
    $slug = urlencode($r['slug']);
    $lastmod = date('Y-m-d', strtotime($r['created_at']));
    $loc = $base . '/updates/details?slug=' . $slug;

    switch ($r['update_type']) {
        case 'exam': $priority='0.9'; $freq='daily'; break;
        case 'admit_card': $priority='0.8'; $freq='weekly'; break;
        case 'result': $priority='0.8'; $freq='weekly'; break;
        default: $priority='0.7'; $freq='monthly'; break;
    }

    echo "<url>\n";
    echo "  <loc>" . e($loc) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>$freq</changefreq>\n";
    echo "  <priority>$priority</priority>\n";
    echo "</url>\n";

    // OPTIONAL: Updates AMP page (only if you have AMP version)
    /*
    $amp = $base . '/amp/updates/details?slug=' . $slug;
    echo "<url>\n";
    echo "  <loc>" . e($amp) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>$freq</changefreq>\n";
    echo "  <priority>0.6</priority>\n";
    echo "</url>\n";
    */
}

// ============================
// BOOKS URLs
// ============================
$stmt = $pdo->query("SELECT slug, created_at FROM books WHERE status='active' ORDER BY created_at DESC LIMIT 2000");
while ($r = $stmt->fetch()) {
    $slug = urlencode($r['slug']);
    $lastmod = date('Y-m-d', strtotime($r['created_at']));
    $loc = $base . '/books/book-details?slug=' . $slug;

    echo "<url>\n";
    echo "  <loc>" . e($loc) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>monthly</changefreq>\n";
    echo "  <priority>0.7</priority>\n";
    echo "</url>\n";

    // OPTIONAL: Books AMP page (only if you have AMP version)
    /*
    $amp = $base . '/amp/books/book-details?slug=' . $slug;
    echo "<url>\n";
    echo "  <loc>" . e($amp) . "</loc>\n";
    echo "  <lastmod>$lastmod</lastmod>\n";
    echo "  <changefreq>monthly</changefreq>\n";
    echo "  <priority>0.6</priority>\n";
    echo "</url>\n";
    */
}
?>

<!-- Tools Page -->
 <url>
    <loc><?= e($base . '/tools') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
   <url>
    <loc><?= e($base . '/tools/image-compressor') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= e($base . '/tools/todo-list') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= e($base . '/tools/typing-speed') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= e($base . '/tools/lettercase-converter') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= e($base . '/tools/qr-code-generator') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
  <!-- Review page -->
  <url>
    <loc><?= e($base . '/review') ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
</urlset>
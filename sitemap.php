<?php
phpinfo();
// require_once __DIR__ . '/functions.php';
// header('Content-Type: application/xml; charset=utf-8');

// $base = rtrim(BASE_URL, '/');
// echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!-- <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> -->
  <!-- Homepage -->
  <!-- <url>
    <loc><?= e($base) ?></loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url> -->

<?php
// Jobs
// $stmt = $pdo->query("SELECT job_title_slug, posted_date FROM jobs WHERE status='published' ORDER BY posted_date DESC LIMIT 1000");
// while ($r = $stmt->fetch()) {
//     $loc = $base . '/job.php?slug=' . urlencode($r['job_title_slug']);
//     $lastmod = date('Y-m-d', strtotime($r['posted_date']));
//     echo "<url>\n";
//     echo "<loc>" . e($loc) . "</loc>\n";
//     echo "<lastmod>$lastmod</lastmod>\n";
//     echo "<changefreq>weekly</changefreq>\n";
//     echo "<priority>0.8</priority>\n";
//     echo "</url>\n";
// }
?>
<!-- </urlset> -->

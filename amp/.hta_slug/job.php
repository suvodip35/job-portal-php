<?php
require_once __DIR__ . '../../../.hta_config/functions.php';
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';

$slug = $_GET['slug'] ?? '';

// ---------------- AMP Sanitizer ----------------
function ampSanitizeJobDescription($markdown) {
    $Parsedown = new Parsedown();
    $html = $Parsedown->text($markdown);

    // Remove disallowed tags
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);

    // Remove invalid attributes
    $html = preg_replace('/\s(style|border|align|cellpadding|cellspacing|valign|onclick|onload|on\w+|target|rel)="[^"]*"/i', '', $html);

    // YouTube → amp-youtube
    $html = preg_replace_callback(
        '#https?://(?:www\.)?youtu(?:be\.com/watch\?v=|\.be/)([a-zA-Z0-9_-]+)#',
        function($m) {
            return '<amp-youtube data-videoid="'.$m[1].'" width="480" height="270" layout="responsive"></amp-youtube>';
        },
        $html
    );

    // img → amp-img
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div id="amp-wrapper">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');
    for ($i = $images->length - 1; $i >= 0; $i--) {
        $img = $images->item($i);

        $ampImg = $dom->createElement('amp-img');
        $ampImg->setAttribute('src', $img->getAttribute('src'));
        $ampImg->setAttribute('alt', $img->getAttribute('alt') ?: 'image');
        $ampImg->setAttribute('layout', 'responsive');
        $ampImg->setAttribute('width', '800');
        $ampImg->setAttribute('height', '450');

        $img->parentNode->replaceChild($ampImg, $img);
    }

    $finalHTML = $dom->saveHTML($dom->getElementById('amp-wrapper'));
    $finalHTML = preg_replace('/^<div id="amp-wrapper">/i', '', $finalHTML);
    $finalHTML = preg_replace('/<\/div>$/i', '', $finalHTML);

    return trim($finalHTML);
}

// ---------------- JOB LIST ----------------
if (!$slug) {
    $jobs = $pdo->query("SELECT * FROM jobs WHERE status='published' ORDER BY posted_date DESC")->fetchAll();

    $pageTitle = "FromCampus - JOB Notification Portal";
    $canonicalUrl = "https://fromcampus.com/job";
?>
<!doctype html>
<html amp lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="canonical" href="<?= $canonicalUrl ?>">
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

<script async src="https://cdn.ampproject.org/v0.js"></script>

<!-- ✅ CORRECT AMP BOILERPLATE -->
<style amp-boilerplate>
body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;
-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;
-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;
animation:-amp-start 8s steps(1,end) 0s 1 normal both}
@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
</style>
<noscript>
<style amp-boilerplate>
body{-webkit-animation:none;
-moz-animation:none;
-ms-animation:none;
animation:none}
</style>
</noscript>

<style amp-custom>
body { font-family: Arial; background:#f8f9fa; margin:0 }
.fc-container { max-width:800px; margin:auto; padding:20px; background:#fff }
.fc-job-card { border:1px solid #eee; padding:15px; margin-bottom:15px; border-radius:8px }
.fc-job-title { font-size:20px; margin:0 }
.fc-job-link { background:#3498db; color:#fff; padding:8px 12px; display:inline-block; margin-top:10px; text-decoration:none }
</style>
</head>

<body>
<div class="fc-container">
<h1>📝 All Jobs</h1>

<?php foreach ($jobs as $job): ?>
<article class="fc-job-card">
<h2><?= htmlspecialchars($job['job_title']) ?></h2>
<p><?= htmlspecialchars($job['company_name']) ?></p>
<a href="/amp/job?slug=<?= urlencode($job['job_title_slug']) ?>" class="fc-job-link">View</a>
</article>
<?php endforeach; ?>

</div>
</body>
</html>
<?php exit; }

// ---------------- SINGLE JOB ----------------
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE job_title_slug=? AND status='published'");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) { echo "Not found"; exit; }

$pageTitle = $job['meta_title'] ?: $job['job_title'];
$canonicalUrl = "https://fromcampus.com/job?slug=".$job['job_title_slug'];
$thumbnailUrl = "https://fromcampus.com".$job['thumbnail'];
$jobDescriptionAMP = ampSanitizeJobDescription($job['description']);
?>

<!doctype html>
<html amp lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="canonical" href="<?= $canonicalUrl ?>">
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

<script async src="https://cdn.ampproject.org/v0.js"></script>

<!-- Only required component -->
<script async custom-element="amp-youtube"
src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>

<!-- ✅ CORRECT AMP BOILERPLATE -->
<style amp-boilerplate>
body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;
-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;
-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;
animation:-amp-start 8s steps(1,end) 0s 1 normal both}
@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
</style>
<noscript>
<style amp-boilerplate>
body{-webkit-animation:none;
-moz-animation:none;
-ms-animation:none;
animation:none}
</style>
</noscript>

<style amp-custom>
body { font-family: Arial; background:#f8f9fa; margin:0 }
.fc-container { max-width:800px; margin:auto; padding:20px; background:#fff }
.fc-job-title { font-size:24px }
.fc-job-meta { font-size:14px; color:#777 }
table { display:block; overflow-x:auto }
.fc-main-btn { background:#e67e22; color:#fff; padding:12px 20px; display:inline-block; margin-top:20px; text-decoration:none }
</style>
</head>

<body>
<div class="fc-container">

<h1><?= htmlspecialchars($job['job_title']) ?></h1>

<p class="fc-job-meta">
🏢 <?= htmlspecialchars($job['company_name']) ?> |
📍 <?= htmlspecialchars($job['location']) ?>
</p>

<?php if ($job['thumbnail']): ?>
<amp-img src="<?= $thumbnailUrl ?>" width="800" height="450" layout="responsive"></amp-img>
<?php endif; ?>

<div>
<?= $jobDescriptionAMP ?>
</div>

<a href="<?= $canonicalUrl ?>" class="fc-main-btn">View Main Page</a>

</div>
</body>
</html>
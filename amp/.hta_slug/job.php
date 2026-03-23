<?php
require_once __DIR__ . '../../../.hta_config/functions.php';
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';

$Parsedown = new Parsedown();
$slug = $_GET['slug'] ?? '';

// 1. AMP Sanitizer Function (Fixed logic for AMP tags)
function ampSanitizeJobDescription($markdown) {
    $Parsedown = new Parsedown();
    $html = $Parsedown->text($markdown);

    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/on\w+="[^"]*"/i', '', $html);
    $html = preg_replace('/style="[^"]*"/i', '', $html);

    // Convert YouTube
    $html = preg_replace_callback(
        '#https?://(?:www\.)?youtu(?:be\.com/watch\?v=|\.be/)([a-zA-Z0-9_-]+)#',
        function($m) {
            return '<amp-youtube data-videoid="'.$m[1].'" width="480" height="270" layout="responsive"></amp-youtube>';
        },
        $html
    );

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div id="amp-wrapper">'.$html.'</div>');
    libxml_clear_errors();

    foreach ($dom->getElementsByTagName('img') as $img) {
        $amp = $dom->createElement('amp-img');
        $amp->setAttribute('src', $img->getAttribute('src'));
        $amp->setAttribute('alt', $img->getAttribute('alt') ?: '');
        $amp->setAttribute('layout', 'responsive');
        $amp->setAttribute('width', '800');
        $amp->setAttribute('height', '450');
        $amp->appendChild($dom->createElement('noscript')); 
        $img->parentNode->replaceChild($amp, $img);
    }

    $finalHTML = $dom->saveHTML();
    $finalHTML = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $finalHTML);
    $finalHTML = preg_replace('/^.*<div id="amp-wrapper">/s', '', $finalHTML);
    $finalHTML = preg_replace('/<\/div>.*$/s', '', $finalHTML);

    return trim($finalHTML);
}

// -------- Case 1: Job List AMP Page --------
if (!$slug) {
    $stmt = $pdo->prepare("SELECT j.*, c.category_name FROM jobs j LEFT JOIN job_categories c ON j.category_slug = c.category_slug WHERE j.status='published' ORDER BY j.posted_date DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();

    $pageTitle = "FromCampus - JOB Notification Portal";
    $pageDescription = "Latest government and private sector job notifications.";
    $canonicalUrl = "https://fromcampus.com/job"; // FIXED: Pointing to Main Page
    $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
  <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
  <script async src="https://cdn.ampproject.org/v0.js"></script>
  <style amp-custom>
    body { font-family: Arial, sans-serif; background-color: #f8f9fa; line-height:1.6; padding:0; margin:0; }
    .fc-container { max-width:800px; margin:0 auto; padding:20px; background: white; min-height: 100vh; }
    .fc-header { text-align: center; border-bottom: 2px solid #3498db; margin-bottom: 20px; padding-bottom: 10px; }
    .fc-job-card { border:1px solid #e1e8ed; padding:15px; margin-bottom:15px; border-radius:8px; }
    .fc-job-title { font-size:20px; color: #2c3e50; margin:0; }
    .fc-job-link { display:inline-block; background:#3498db; color:#fff; padding:8px 15px; text-decoration:none; border-radius:5px; margin-top:10px; }
  </style>
</head>
<body>
  <div class="fc-container">
    <header class="fc-header"><h1>📝 All Jobs</h1></header>
    <?php foreach ($jobs as $job): ?>
      <article class="fc-job-card">
        <h2 class="fc-job-title"><?= htmlspecialchars($job['job_title']) ?></h2>
        <p>🏢 <?= htmlspecialchars($job['company_name']) ?></p>
        <a href="/amp/job?slug=<?= urlencode($job['job_title_slug']) ?>" class="fc-job-link">View Details</a>
      </article>
    <?php endforeach; ?>
  </div>
</body>
</html>
<?php exit; } 

// -------- Case 2: Single Job AMP Page --------
$stmt = $pdo->prepare("SELECT j.*, c.category_name FROM jobs j LEFT JOIN job_categories c ON j.category_slug = c.category_slug WHERE j.job_title_slug = ? AND j.status='published' LIMIT 1");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) { header("HTTP/1.1 404 Not Found"); echo "Job not found"; exit; }

$pageTitle = $job['meta_title'] ?: $job['job_title'];
$canonicalUrl = "https://fromcampus.com/job?slug=".$job['job_title_slug']; // FIXED: Pointing to Main Page
$thumbnailUrl = $job['thumbnail'] ? "https://fromcampus.com".$job['thumbnail'] : "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$jobDescriptionAMP = ampSanitizeJobDescription($job['description']);
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="canonical" href="<?= $canonicalUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
  
  <script async src="https://cdn.ampproject.org/v0.js"></script>
  <script async custom-element="amp-img" src="https://cdn.ampproject.org/v0/amp-img-0.1.js"></script>
  <script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>

  <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>

  <style amp-custom>
    body { font-family: Arial, sans-serif; background-color: #f8f9fa; line-height: 1.6; padding:0; margin:0; }
    .fc-container { max-width:800px; margin:0 auto; padding:20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); min-height: 100vh; }
    .fc-job-header { border-bottom: 2px solid #3498db; padding-bottom: 15px; margin-bottom: 20px; }
    .fc-job-title { font-size:24px; color: #2c3e50; margin-bottom:10px; }
    .fc-job-meta { font-size:14px; color: #7f8c8d; background: #f8f9fa; padding: 10px; border-radius: 5px; }
    .fc-job-content { margin-top: 20px; color: #2c3e50; }
    .fc-action-buttons { text-align: center; margin: 30px 0; padding-bottom: 50px; }
    .fc-main-btn { display:inline-block; padding:12px 20px; background:#e67e22; color:#fff; border-radius:5px; text-decoration:none; font-weight:bold; }
    .fc-footer { text-align:center; padding:20px; font-size:12px; color:#7f8c8d; border-top: 1px solid #eee; }
  </style>
</head>
<body>
  <div class="fc-container">
    <article>
      <header class="fc-job-header">
        <h1 class="fc-job-title"><?= htmlspecialchars($job['job_title']) ?></h1>
        <div class="fc-job-meta">
          🏢 <strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?> | 
          📍 <strong>Location:</strong> <?= htmlspecialchars($job['location']) ?>
        </div>
      </header>

      <?php if ($job['thumbnail']): ?>
        <amp-img src="<?= $thumbnailUrl ?>" width="800" height="450" layout="responsive" alt="Thumbnail"></amp-img>
      <?php endif; ?>

      <div class="fc-job-content">
        <?= $jobDescriptionAMP ?>
      </div>
    </article>

    <div class="fc-action-buttons">
      <a href="<?= $canonicalUrl ?>" class="fc-main-btn">📄 View Main Page for Full Details</a>
    </div>

    <footer class="fc-footer">
      <p>&copy; <?= date('Y') ?> FromCampus. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
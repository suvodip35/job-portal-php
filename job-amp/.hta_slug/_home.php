<?php
require_once __DIR__ . '../../../.hta_config/functions.php';
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';

$Parsedown = new Parsedown();
$slug = $_GET['slug'] ?? '';

// If no slug → Show all published jobs
if (!$slug) {
    $stmt = $pdo->prepare("SELECT j.*, c.category_name 
        FROM jobs j 
        LEFT JOIN job_categories c ON j.category_slug = c.category_slug 
        WHERE j.status='published' ORDER BY j.posted_date DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();

    $pageTitle = "FromCampus - JOB Notification Portal";
    $pageDescription = "FromCampus - JOB Notification Portal";
    $keywords = "Government JOBS, ITI JOBS, Railway Jobs, Engineer";
    $author = "FromCampus";
    $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
    $currentUrl = "https://fromcampus.com";
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDescription ?>">
  <meta name="keywords" content="<?= $keywords ?>">
  <meta name="author" content="<?= $author ?>">
  <link rel="canonical" href="<?= $currentUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $currentUrl ?>">
  <meta property="og:title" content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= $pageDescription ?>">
  <meta property="og:image" content="<?= $ogImage ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= $currentUrl ?>">
  <meta property="twitter:title" content="<?= $pageTitle ?>">
  <meta property="twitter:description" content="<?= $pageDescription ?>">
  <meta property="twitter:image" content="<?= $ogImage ?>">

  <!-- AMP mandatory boilerplate -->
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
      body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}
    </style>
  </noscript>

  <!-- AMP runtime -->
  <script async src="https://cdn.ampproject.org/v0.js"></script>

  <!-- AMP components -->
  <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
  <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>

  <style amp-custom>
    body { font-family: Arial, sans-serif; margin:0; padding:0; line-height:1.6; }
    .fc-container { max-width:800px; margin:0 auto; padding:20px; }
    .fc-job-card { border:1px solid #ddd; padding:15px; margin-bottom:20px; border-radius:8px; }
    .fc-job-title { font-size:20px; margin:0 0 10px; }
    .fc-job-company { color:#3498db; font-weight:500; }
    .fc-job-meta { font-size:14px; margin-bottom:10px; }
    .fc-job-link { display:inline-block; background:#3498db; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; }
    .fc-footer { text-align:center; padding:20px; font-size:14px; color:#777; }
  </style>
</head>
<body>
  <div class="fc-container">
    <h1>All Job Notifications</h1>
    <?php if (count($jobs) > 0): ?>
      <?php foreach ($jobs as $job): 
        $jobTitle = htmlspecialchars($job['job_title']);
        $jobExcerpt = htmlspecialchars(strip_tags(substr($job['description'],0,150))) . '...';
        $jobUrl = "/job-amp?slug=" . htmlspecialchars($job['job_title_slug']);
      ?>
        <article class="fc-job-card">
          <h2 class="fc-job-title"><?= $jobTitle ?></h2>
          <p class="fc-job-company"><?= htmlspecialchars($job['company_name']) ?></p>
          <div class="fc-job-meta">
            <span><?= htmlspecialchars($job['location']) ?></span> | 
            <span>Posted: <?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
          </div>
          <p><?= $jobExcerpt ?></p>
          <a href="<?= $jobUrl ?>" class="fc-job-link">View Details</a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No job openings available right now.</p>
    <?php endif; ?>
    <footer class="fc-footer">&copy; <?= date('Y') ?> FromCampus. All rights reserved.</footer>
  </div>
</body>
</html>
<?php
    exit;
}

// -------- Single Job AMP Page --------
$stmt = $pdo->prepare("SELECT j.*, c.category_name 
  FROM jobs j 
  LEFT JOIN job_categories c ON j.category_slug = c.category_slug 
  WHERE j.job_title_slug = ? AND j.status='published' LIMIT 1");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) { echo "<h1>Job not found</h1>"; exit; }

$pageTitle = $job['meta_title'] ?: $job['job_title'] . " - FromCampus";
$pageDescription = $job['meta_description'] ?: mb_substr(strip_tags($job['description']),0,160);
$keywords = "Government JOBS, ITI JOBS, Railway Jobs, Engineer, " . $job['job_title'];
$ogImage = $job['thumbnail'] ? "https://fromcampus.com".$job['thumbnail'] : "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$currentUrl = "https://fromcampus.com/job?slug=".$job['job_title_slug'];
$jobTitle = htmlspecialchars($job['job_title']);
$jobDescription = $Parsedown->text($job['description']);
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDescription ?>">
  <meta name="keywords" content="<?= $keywords ?>">
  <link rel="canonical" href="<?= $currentUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

  <!-- OG/Twitter -->
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?= $currentUrl ?>">
  <meta property="og:title" content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= $pageDescription ?>">
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="twitter:card" content="summary_large_image">

  <!-- AMP mandatory boilerplate -->
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
      body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}
    </style>
  </noscript>

  <!-- AMP runtime -->
  <script async src="https://cdn.ampproject.org/v0.js"></script>

  <style amp-custom>
    body { font-family: Arial, sans-serif; margin:0; padding:0; }
    .fc-container { max-width:800px; margin:0 auto; padding:20px; }
    .fc-job-title { font-size:26px; margin:0 0 10px; }
    .fc-job-company { font-size:18px; color:#3498db; margin-bottom:10px; }
    .fc-job-meta { font-size:14px; margin-bottom:15px; }
    .fc-job-content { margin-bottom:20px; }
    .fc-footer { text-align:center; padding:20px; font-size:14px; color:#777; }
    .fc-main-btn {
      display:inline-block;
      padding:10px 18px;
      background:#e67e22;
      color:#fff;
      border-radius:5px;
      text-decoration:none;
      font-size:16px;
      font-weight:bold;
    }
  </style>
</head>
<body>
  <div class="fc-container">
    <article>
      <h1 class="fc-job-title"><?= $jobTitle ?></h1>
      <p class="fc-job-company"><?= htmlspecialchars($job['company_name']) ?></p>
      <div class="fc-job-meta">
        <span><?= htmlspecialchars($job['location']) ?></span> | 
        <span>Posted: <?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
      </div>

      <?php if ($job['thumbnail']): ?>
        <amp-img src="<?= $ogImage ?>" width="800" height="450" layout="responsive" alt="<?= $jobTitle ?>"></amp-img>
      <?php endif; ?>

      <div class="fc-job-content"><?= $jobDescription ?></div>
    </article>
    <div style="text-align:center; margin:20px 0;">
      <a href="https://fromcampus.com/job?slug=<?= $job['job_title_slug'] ?>" 
        class="fc-main-btn">View Main Page</a>
    </div>
    <footer class="fc-footer">&copy; <?= date('Y') ?> FromCampus. All rights reserved.</footer>
  </div>
</body>
</html>

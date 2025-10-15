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
    $currentUrl = "https://fromcampus.com/job-amp";
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
  <meta name="author" content="<?= htmlspecialchars($author) ?>">
  <link rel="canonical" href="<?= $currentUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $currentUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta property="og:image" content="<?= $ogImage ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= $currentUrl ?>">
  <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
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
    body { 
        font-family: Arial, sans-serif; 
        margin:0; 
        padding:0; 
        line-height:1.6;
        background-color: #f8f9fa;
    }
    .fc-container { 
        max-width:800px; 
        margin:0 auto; 
        padding:20px;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-height: 100vh;
    }
    .fc-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #3498db;
    }
    .fc-header h1 {
        color: #2c3e50;
        margin: 0;
        font-size: 28px;
    }
    .fc-job-card { 
        border:1px solid #e1e8ed; 
        padding:20px; 
        margin-bottom:20px; 
        border-radius:8px;
        background: white;
        transition: transform 0.2s ease;
    }
    .fc-job-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .fc-job-title { 
        font-size:20px; 
        margin:0 0 10px;
        color: #2c3e50;
    }
    .fc-job-company { 
        color:#3498db; 
        font-weight:600;
        font-size: 16px;
        margin-bottom: 8px;
    }
    .fc-job-meta { 
        font-size:14px; 
        margin-bottom:12px;
        color: #7f8c8d;
    }
    .fc-job-excerpt {
        color: #5d6d7e;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    .fc-job-link { 
        display:inline-block; 
        background:#3498db; 
        color:#fff; 
        padding:10px 20px; 
        text-decoration:none; 
        border-radius:6px;
        font-weight: 500;
        transition: background 0.3s ease;
    }
    .fc-job-link:hover {
        background: #2980b9;
    }
    .fc-footer { 
        text-align:center; 
        padding:30px 20px; 
        font-size:14px; 
        color:#7f8c8d;
        margin-top: 40px;
        border-top: 1px solid #e1e8ed;
    }
    .fc-no-jobs {
        text-align: center;
        padding: 40px 20px;
        color: #7f8c8d;
        font-size: 16px;
    }
    .fc-category-badge {
        display: inline-block;
        background: #e74c3c;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        margin-left: 10px;
        vertical-align: middle;
    }
  </style>
</head>
<body>
  <div class="fc-container">
    <header class="fc-header">
        <h1>📝 All Job Notifications</h1>
        <p>Latest government and private sector job opportunities</p>
    </header>
    
    <?php if (count($jobs) > 0): ?>
      <?php foreach ($jobs as $job): 
        $jobTitle = htmlspecialchars($job['job_title']);
        $jobExcerpt = htmlspecialchars(strip_tags(substr($job['description'],0,200))) . '...';
        $jobUrl = "/job-amp?slug=" . urlencode($job['job_title_slug']);
        $categoryName = htmlspecialchars($job['category_name'] ?? 'General');
      ?>
        <article class="fc-job-card">
          <h2 class="fc-job-title">
              <?= $jobTitle ?>
              <?php if ($job['category_name']): ?>
                  <span class="fc-category-badge"><?= $categoryName ?></span>
              <?php endif; ?>
          </h2>
          <p class="fc-job-company">🏢 <?= htmlspecialchars($job['company_name']) ?></p>
          <div class="fc-job-meta">
            📍 <span><?= htmlspecialchars($job['location']) ?></span> | 
            📅 <span>Posted: <?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
          </div>
          <p class="fc-job-excerpt"><?= $jobExcerpt ?></p>
          <a href="<?= $jobUrl ?>" class="fc-job-link">View Details & Apply</a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="fc-no-jobs">
        <p>🚫 No job openings available right now.</p>
        <p>Please check back later for new opportunities.</p>
      </div>
    <?php endif; ?>
    
    <footer class="fc-footer">
        <p>&copy; <?= date('Y') ?> FromCampus. All rights reserved.</p>
        <p>Your trusted job notification portal</p>
    </footer>
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

if (!$job) { 
    echo "<h1>Job not found</h1>"; 
    exit; 
}



// Prepare meta data
$pageTitle = $job['meta_title'] ?: $job['job_title'] . " - FromCampus";
$pageDescription = $job['meta_description'] ?: mb_substr(strip_tags($job['description']),0,160);
$keywords = "Government JOBS, ITI JOBS, Railway Jobs, Engineer, " . $job['job_title'];
$thumbnailUrl = $job['thumbnail'] ? "https://fromcampus.com".$job['thumbnail'] : "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$currentUrl = "https://fromcampus.com/job-amp?slug=".$job['job_title_slug'];
$jobTitle = htmlspecialchars($job['job_title']);

// Process and sanitize description for AMP
$jobDescription = $Parsedown->text($job['description']);
// Remove any script tags and inline event handlers
$jobDescription = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $jobDescription);
$jobDescription = preg_replace('/on\w+=\s*"[^"]*"/', '', $jobDescription);
$jobDescription = preg_replace("/on\w+=\s*'[^']*'/", '', $jobDescription);
$jobDescription = preg_replace('/on\w+=\s*[^\s>]+/', '', $jobDescription);

// Ensure images are AMP compatible
$jobDescription = preg_replace('/<img(/[^>]*>)/', '<amp-img$1 layout="responsive" width="16" height="9"', $jobDescription);
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
  <link rel="canonical" href="<?= $currentUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

  <!-- OG/Twitter -->
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?= $currentUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta property="og:image" content="<?= $thumbnailUrl ?>">
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= $currentUrl ?>">
  <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta property="twitter:image" content="<?= $thumbnailUrl ?>">

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

  <!-- AMP components for images -->
  <script async custom-element="amp-img" src="https://cdn.ampproject.org/v0/amp-img-0.1.js"></script>

  <style amp-custom>
    body { 
        font-family: Arial, sans-serif; 
        margin:0; 
        padding:0;
        background-color: #f8f9fa;
        line-height: 1.6;
    }
    .fc-container { 
        max-width:800px; 
        margin:0 auto; 
        padding:20px;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-height: 100vh;
    }
    .fc-job-title { 
        font-size:28px; 
        margin:0 0 15px;
        color: #2c3e50;
        line-height: 1.3;
    }
    .fc-job-company { 
        font-size:20px; 
        color:#3498db; 
        margin-bottom:15px;
        font-weight: 600;
    }
    .fc-job-meta { 
        font-size:16px; 
        margin-bottom:20px;
        color: #7f8c8d;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .fc-job-content { 
        margin-bottom:30px;
        color: #2c3e50;
    }
    .fc-job-content h1, 
    .fc-job-content h2, 
    .fc-job-content h3 {
        color: #2c3e50;
        margin-top: 25px;
        margin-bottom: 15px;
    }
    .fc-job-content p {
        margin-bottom: 15px;
    }
    .fc-job-content ul, 
    .fc-job-content ol {
        margin-bottom: 15px;
        padding-left: 20px;
    }
    .fc-job-content li {
        margin-bottom: 8px;
    }
    .fc-job-content strong {
        color: #2c3e50;
    }
    .fc-job-content amp-img {
        margin: 20px 0;
        border-radius: 8px;
        overflow: hidden;
    }
    .fc-footer { 
        text-align:center; 
        padding:30px 20px; 
        font-size:14px; 
        color:#7f8c8d;
        margin-top: 40px;
        border-top: 1px solid #e1e8ed;
    }
    .fc-main-btn {
      display:inline-block;
      padding:12px 25px;
      background:#e67e22;
      color:#fff;
      border-radius:6px;
      text-decoration:none;
      font-size:16px;
      font-weight:bold;
      transition: background 0.3s ease;
      margin: 10px;
    }
    .fc-main-btn:hover {
        background: #d35400;
    }
    .fc-back-btn {
        display:inline-block;
        padding:12px 25px;
        background:#95a5a6;
        color:#fff;
        border-radius:6px;
        text-decoration:none;
        font-size:16px;
        font-weight:bold;
        transition: background 0.3s ease;
        margin: 10px;
    }
    .fc-back-btn:hover {
        background: #7f8c8d;
    }
    .fc-action-buttons {
        text-align: center;
        margin: 30px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        position: fixed;
        bottom: 20px;
        left: 0;
        right: 0;
    }
    .fc-category-badge {
        display: inline-block;
        background: #e74c3c;
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        margin-left: 15px;
        vertical-align: middle;
        font-weight: 500;
    }
    .fc-job-header {
        border-bottom: 2px solid #3498db;
        padding-bottom: 20px;
        margin-bottom: 25px;
    }
  </style>
</head>
<body>
  <div class="fc-container">
    <article>
      <header class="fc-job-header">
        <h1 class="fc-job-title">
            <?= $jobTitle ?>
            <?php if ($job['category_name']): ?>
                <span class="fc-category-badge"><?= htmlspecialchars($job['category_name']) ?></span>
            <?php endif; ?>
        </h1>
        <p class="fc-job-company">🏢 <?= htmlspecialchars($job['company_name']) ?></p>
        <div class="fc-job-meta">
          📍 <strong>Location:</strong> <?= htmlspecialchars($job['location']) ?> | 
          📅 <strong>Posted:</strong> <?= date('M d, Y', strtotime($job['posted_date'])) ?> |
          ⏰ <strong>Last Date:</strong> <?= $job['application_deadline'] ? date('M d, Y', strtotime($job['application_deadline'])) : 'Not specified' ?>
        </div>
      </header>

      <?php if ($job['thumbnail']): ?>
        <amp-img src="<?= $thumbnailUrl ?>" 
                 width="800" 
                 height="450" 
                 layout="responsive" 
                 alt="<?= $jobTitle ?>"
                 class="fc-job-image">
        </amp-img>
      <?php endif; ?>

      <div class="fc-job-content">
          <?= $jobDescription ?>
      </div>
    </article>

    <div class="fc-action-buttons">
      <a href="https://fromcampus.com/job?slug=<?= urlencode($job['job_title_slug']) ?>" 
         class="fc-main-btn">📄 View Main Page</a>
      <a href="/" class="fc-back-btn">⬅ Back to Jobs List</a>
    </div>

    <footer class="fc-footer">
      <p>&copy; <?= date('Y') ?> FromCampus. All rights reserved.</p>
      <p>Your trusted job notification portal | AMP Optimized</p>
    </footer>
  </div>
</body>
</html>
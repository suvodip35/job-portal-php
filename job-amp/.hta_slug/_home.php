<?php
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();
$slug = $_GET['slug'] ?? '';

// If no slug provided, show all published jobs
if (!$slug) {
    $stmt = $pdo->prepare("SELECT j.*, c.category_name FROM jobs j LEFT JOIN job_categories c ON j.category_slug = c.category_slug WHERE j.status='published' ORDER BY j.posted_date DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();
    
    // Generate meta tags for the listing page
    $pageTitle = "FromCampus - JOB Notification Portal";
    $pageDescription = "FromCampus - JOB Notification Portal";
    $keywords = "Government JOBS, ITI JOBS, Railway Jobs, Engineer";
    $author = "FromCampus";
    $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
    $pageThumbnail = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
    $currentUrl = "https://fromcampus.com";
    
    require_once __DIR__ . '/../../.hta_slug/_header.php';
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
  
  <!-- Open Graph / Facebook -->
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
  
  <style amp-custom>
    /* Base styles */
    .fc-body { 
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
        margin: 0; 
        padding: 0; 
        line-height: 1.6;
    }
    .fc-container { 
        max-width: 800px; 
        margin: 0 auto; 
        padding: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .fc-header {
        text-align: center;
        padding: 20px 0;
        border-bottom: 1px solid #eaeaea;
        margin-bottom: 30px;
    }
    .fc-logo {
        max-width: 180px;
        height: auto;
    }
    .fc-title {
        font-size: 28px;
        margin: 20px 0 10px;
    }
    .fc-subtitle {
        font-size: 18px;
        margin-bottom: 30px;
    }
    
    /* Job cards */
    .fc-job-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .fc-job-card {
        border: 1px solid #eaeaea;
        border-radius: 8px;
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .fc-job-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .fc-job-title {
        font-size: 20px;
        margin: 0 0 10px;
    }
    .fc-job-company {
        font-size: 16px;
        color: #3498db;
        margin: 0 0 10px;
        font-weight: 500;
    }
    .fc-job-meta {
        font-size: 14px;
        margin: 0 0 15px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .fc-job-excerpt {
        font-size: 15px;
        margin: 0 0 15px;
    }
    .fc-job-link {
        display: inline-block;
        padding: 10px 20px;
        background: #3498db;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .fc-job-link:hover {
        background: #2980b9;
    }
    
    /* Breadcrumb */
    .fc-breadcrumb {
        margin-bottom: 20px;
        font-size: 14px;
        color: #7f8c8d;
    }
    .fc-breadcrumb a {
        color: #3498db;
        text-decoration: none;
    }
    .fc-breadcrumb a:hover {
        text-decoration: underline;
    }
    
    /* Footer */
    .fc-footer {
        text-align: center;
        padding: 30px 0;
        margin-top: 40px;
        border-top: 1px solid #eaeaea;
        color: #7f8c8d;
        font-size: 14px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 600px) {
        .fc-container {
            padding: 15px;
        }
        .fc-title {
            font-size: 24px;
        }
        .fc-job-title {
            font-size: 18px;
        }
        .fc-job-meta {
            flex-direction: column;
            gap: 8px;
        }
    }
    
    /* Loading animation */
    .fc-loading {
        display: flex;
        justify-content: center;
        padding: 40px 0;
    }
    .fc-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: fc-spin 1s linear infinite;
    }
    @keyframes fc-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
  </style>
  <script async src="https://cdn.ampproject.org/v0.js"></script>
  <!-- AMP components -->
  <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
  <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
</head>
<body class="fc-body">
  <div class="fc-container">
    
    <div class="fc-breadcrumb">
        <a href="/">Home</a> &gt; <a href="/jobs/">Jobs</a> &gt; All Positions
    </div>
    
    <main>
      <div class="fc-job-grid">
        <?php if (count($jobs) > 0): ?>
          <?php foreach ($jobs as $job): 
            $jobTitle = htmlspecialchars($job['job_title']);
            $jobExcerpt = htmlspecialchars(strip_tags(substr($job['description'], 0, 150))) . '...';
            $jobUrl = "/job-amp?slug=" . htmlspecialchars($job['job_title_slug']);
          ?>
            <article class="fc-job-card">
              <h2 class="fc-job-title"><?= $jobTitle ?></h2>
              <p class="fc-job-company"><?= htmlspecialchars($job['company_name']) ?></p>
              <div class="fc-job-meta">
                <span><?= htmlspecialchars($job['location']) ?></span>
                <span>Posted: <?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
                <?php if (!empty($job['category_name'])): ?>
                  <span>Category: <?= htmlspecialchars($job['category_name']) ?></span>
                <?php endif; ?>
              </div>
              <p class="fc-job-excerpt"><?= $Parsedown->text($jobExcerpt) ?></p>
              <a href="<?= $jobUrl ?>" class="fc-job-link">View Details</a>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="fc-no-jobs">
            <p>No job openings available at the moment. Please check back later.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
    
    <footer class="fc-footer">
      <p>&copy; <?= date('Y') ?> FromCampus. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
<?php
    exit;
}

// If slug is provided, show the specific job
$stmt = $pdo->prepare("SELECT j.*, c.category_name FROM jobs j LEFT JOIN job_categories c ON j.category_slug = c.category_slug WHERE j.job_title_slug = ? AND j.status='published' LIMIT 1");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) {
    echo "<h1>Job not found.</h1>";
    exit;
}

// Set meta data for single job page
$pageTitle = $job['meta_title'] ?: $job['job_title'] . ' - ' . APP_NAME;
$pageDescription = $job['meta_description'] ?: mb_substr(strip_tags($job['description']), 0, 160);
$keywords = "Government JOBS, ITI JOBS, Railway Jobs, Engineer, " . $job['job_title'];
$ogImage = $job['thumbnail'] ? "https://fromcampus.com" . $job['thumbnail'] : "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$currentUrl = "https://fromcampus.com/job?slug=".$job['job_title_slug'];
$jobTitle = htmlspecialchars($job['job_title']);
$jobDescription = htmlspecialchars(strip_tags($job['description']));
$jobThumbnail = $job['thumbnail'] ? "https://fromcampus.com".$job['thumbnail'] : "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";

require_once __DIR__ . '/../../.hta_slug/_header.php';
?>
<!doctype html>
<html ⚡ lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDescription ?>">
  <meta name="keywords" content="<?= $keywords ?>">
  <meta name="author" content="FromCampus">
  <link rel="canonical" href="<?= $currentUrl ?>">
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="article">
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
  
  <style amp-custom>
    /* Base styles */
    .fc-body { 
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
        margin: 0; 
        padding: 0;  
        line-height: 1.6;
    }
    .fc-container { 
        max-width: 800px; 
        margin: 0 auto; 
        padding: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .fc-header {
        text-align: center;
        padding: 20px 0;
        border-bottom: 1px solid #eaeaea;
        margin-bottom: 30px;
    }
    .fc-logo {
        max-width: 180px;
        height: auto;
    }
    
    /* Breadcrumb */
    .fc-breadcrumb {
        margin-bottom: 20px;
        font-size: 14px;
        color: #7f8c8d;
    }
    .fc-breadcrumb a {
        color: #3498db;
        text-decoration: none;
    }
    .fc-breadcrumb a:hover {
        text-decoration: underline;
    }
    
    /* Single job view */
    .fc-single-job {
        padding: 20px 0;
    }
    .fc-job-header {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eaeaea;
    }
    .fc-job-title {
        font-size: 28px;
        margin: 0 0 10px;
    }
    .fc-job-company {
        font-size: 20px;
        color: #3498db;
        margin: 0 0 15px;
        font-weight: 500;
    }
    .fc-job-meta {
        font-size: 16px;
        margin: 0 0 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .fc-job-thumbnail {
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 25px;
    }
    .fc-job-content {
        margin-bottom: 25px;
    }
    .fc-job-content h2 {
        font-size: 22px;
        margin: 25px 0 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eaeaea;
    }
    .fc-job-content h3 {
        font-size: 18px;
        margin: 20px 0 12px;
    }
    .fc-job-content p {
        margin-bottom: 15px;
    }
    .fc-job-content ul, .fc-job-content ol {
        margin-bottom: 15px;
        padding-left: 20px;
    }
    .fc-job-content li {
        margin-bottom: 8px;
    }
    .fc-job-content table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .fc-job-content table, .fc-job-content th, .fc-job-content td {
        border: 1px solid #ddd;
    }
    .fc-job-content th, .fc-job-content td {
        padding: 10px;
        text-align: left;
    }
    .fc-apply-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eaeaea;
    }
    .fc-button-primary {
        display: inline-block;
        padding: 12px 25px;
        background: #3498db;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .fc-button-primary:hover {
        background: #2980b9;
    }
    .fc-button-secondary {
        display: inline-block;
        padding: 12px 25px;
        background: #ecf0f1;
        color: #2c3e50;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .fc-button-secondary:hover {
        background: #dfe6e9;
    }
    
    /* Related jobs */
    .fc-related-jobs {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid #eaeaea;
    }
    .fc-related-title {
        font-size: 22px;
        margin-bottom: 20px;
    }
    
    /* Footer */
    .fc-footer {
        text-align: center;
        padding: 30px 0;
        margin-top: 40px;
        border-top: 1px solid #eaeaea;
        font-size: 14px;
    }
        .fc-job-link {
        display: inline-block;
        padding: 10px 20px;
        background: #3498db;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .fc-job-link:hover {
        background: #2980b9;
    }

    /* Responsive adjustments */
    @media (max-width: 600px) {
        .fc-container {
            padding: 15px;
        }
        .fc-job-title {
            font-size: 24px;
        }
        .fc-job-meta {
            flex-direction: column;
            gap: 8px;
        }
        .fc-apply-buttons {
            flex-direction: column;
        }
        .fc-button-primary, .fc-button-secondary {
            text-align: center;
        }
    }
  </style>
  <script async src="https://cdn.ampproject.org/v0.js"></script>
  <!-- AMP components -->
  <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
  <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
</head>
<body class="fc-body">
  <div class="fc-container">
    
    <div class="fc-breadcrumb">
        <a href="/">Home</a> &gt; <a href="/job-amp/">Jobs</a> &gt; <?= htmlspecialchars($job['job_title']) ?>
    </div>
    
    <article class="fc-single-job">
      <div class="fc-job-header">
        <h1 class="fc-job-title"><?= $jobTitle ?></h1>
        <p class="fc-job-company"><?= htmlspecialchars($job['company_name']) ?></p>
        <div class="fc-job-meta">
          <span><?= htmlspecialchars($job['location']) ?></span>
          <span>Posted: <?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
          <?php if (!empty($job['category_name'])): ?>
            <span>Category: <?= htmlspecialchars($job['category_name']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if($job['thumbnail']): ?>
        <div class="fc-job-thumbnail">
          <amp-img src="<?= $jobThumbnail ?>" width="800" height="450" layout="responsive" alt="<?= $jobTitle ?>"></amp-img>
        </div>
      <?php endif; ?>

      <div class="fc-job-content">
        <?= $Parsedown->text($job['description']) ?>
      </div>

      <?php if (!empty($job['requirements'])): ?>
      <div class="fc-job-content">
        <h2>Requirements</h2>
        <?= $Parsedown->text($job['requirements']) ?>
      </div>
      <?php endif; ?>

      <div class="fc-apply-buttons">
        <?php if (!empty($job['apply_url'])): ?>
          <a href="<?= htmlspecialchars($job['apply_url']) ?>" target="_blank" class="fc-button-primary">Apply Now</a>
        <?php endif; ?>
        <?php if (!empty($job['document_link'])): ?>
          <a href="<?= htmlspecialchars($job['document_link']) ?>" target="_blank" class="fc-button-secondary">View Documentation</a>
        <?php endif; ?>
      </div>
    </article>
    
    <!-- Related Jobs Section -->
    <?php
    $relatedStmt = $pdo->prepare("SELECT j.* FROM jobs j WHERE j.category_slug = ? AND j.job_title_slug != ? AND j.status='published' ORDER BY j.posted_date DESC LIMIT 3");
    $relatedStmt->execute([$job['category_slug'], $slug]);
    $relatedJobs = $relatedStmt->fetchAll();
    
    if (count($relatedJobs) > 0): ?>
    <section class="fc-related-jobs">
      <h2 class="fc-related-title">Related Job</h2>
      <div class="fc-job-grid">
        <?php foreach ($relatedJobs as $relatedJob): 
          $relatedJobTitle = htmlspecialchars($relatedJob['job_title']);
          $relatedJobExcerpt = htmlspecialchars(strip_tags(substr($relatedJob['description'], 0, 100))) . '...';
          $relatedJobUrl = "/job-amp?slug=" . htmlspecialchars($relatedJob['job_title_slug']);
        ?>
          <article class="fc-job-card">
            <h3 class="fc-job-title"><?= $relatedJobTitle ?></h3>
            <p class="fc-job-company"><?= htmlspecialchars($relatedJob['company_name']) ?></p>
            <div class="fc-job-meta">
              <span><?= htmlspecialchars($relatedJob['location']) ?></span>
              <span>Posted: <?= date('M d, Y', strtotime($relatedJob['posted_date'])) ?></span>
            </div>
            <p class="fc-job-excerpt"><?= $Parsedown->text($relatedJobExcerpt) ?></p>
            <a href="<?= $relatedJobUrl ?>" class="fc-job-link">View Details</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
    
    <footer class="fc-footer">
      <p>&copy; <?= date('Y') ?> FromCampus. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require_admin();

// Stats queries
$totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$published = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status='published'")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalUpdates = (int)$pdo->query("SELECT COUNT(*) FROM updates")->fetchColumn();
$totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$activeBooks = (int)$pdo->query("SELECT COUNT(*) FROM books WHERE status='active'")->fetchColumn();
$totalCA = (int)$pdo->query("SELECT COUNT(*) FROM current_affairs")->fetchColumn();
$publishedCA = (int)$pdo->query("SELECT COUNT(*) FROM current_affairs WHERE status='published'")->fetchColumn();
$siteTitle = "FromCampus - Admin Dashboard";

// Pagination for jobs
$jobsPerPage = 5;
$jobPage = isset($_GET['jpage']) ? max(1, (int)$_GET['jpage']) : 1;
$jobOffset = ($jobPage - 1) * $jobsPerPage;
$totalJobPages = ceil($totalJobs / $jobsPerPage);

$jobStmt = $pdo->prepare("SELECT job_id, job_title, company_name, status, posted_date FROM jobs ORDER BY posted_date DESC LIMIT :limit OFFSET :offset");
$jobStmt->bindValue(':limit', $jobsPerPage, PDO::PARAM_INT);
$jobStmt->bindValue(':offset', $jobOffset, PDO::PARAM_INT);
$jobStmt->execute();

// Pagination for updates
$updatesPerPage = 5;
$updatePage = isset($_GET['upage']) ? max(1, (int)$_GET['upage']) : 1;
$updateOffset = ($updatePage - 1) * $updatesPerPage;
$totalUpdatePages = ceil($totalUpdates / $updatesPerPage);

$updateStmt = $pdo->prepare("SELECT id, title, update_type, created_at FROM updates ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$updateStmt->bindValue(':limit', $updatesPerPage, PDO::PARAM_INT);
$updateStmt->bindValue(':offset', $updateOffset, PDO::PARAM_INT);
$updateStmt->execute();

// Pagination for books
$booksPerPage = 5;
$bookPage = isset($_GET['bpage']) ? max(1, (int)$_GET['bpage']) : 1;
$bookOffset = ($bookPage - 1) * $booksPerPage;
$totalBookPages = ceil($totalBooks / $booksPerPage);

$bookStmt = $pdo->prepare("SELECT id, title, author, book_type, status, created_at FROM books ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$bookStmt->bindValue(':limit', $booksPerPage, PDO::PARAM_INT);
$bookStmt->bindValue(':offset', $bookOffset, PDO::PARAM_INT);
$bookStmt->execute();

// Pagination for Current Affairs
$caPerPage = 5;
$caPage = isset($_GET['capage']) ? max(1, (int)$_GET['capage']) : 1;
$caOffset = ($caPage - 1) * $caPerPage;
$totalCAPages = ceil($totalCA / $caPerPage);

$caStmt = $pdo->prepare("SELECT id, title, category, status, event_date, created_at FROM current_affairs ORDER BY event_date DESC, created_at DESC LIMIT :limit OFFSET :offset");
$caStmt->bindValue(':limit', $caPerPage, PDO::PARAM_INT);
$caStmt->bindValue(':offset', $caOffset, PDO::PARAM_INT);
$caStmt->execute();

// Helper function to build pagination query params keeping state across sections
function buildDashUrl($paramsToUpdate) {
    $queryParams = $_GET;
    foreach ($paramsToUpdate as $k => $v) {
        $queryParams[$k] = $v;
    }
    return '?' . http_build_query($queryParams);
}
?>

<style>
/* Reset & Theme Base */
.dash-wrapper {
  color: #f8fafc;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.dash-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

/* Hero Banner */
.dash-hero {
  background: linear-gradient(135deg, #1e1b4b 0%, #1e293b 50%, #0f172a 100%);
  border: 1px solid #334155;
  border-radius: 1rem;
  padding: 1.5rem;
  color: #ffffff;
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.dash-hero-title {
  font-size: 1.75rem;
  font-weight: 800;
  line-height: 1.2;
  margin: 0;
  color: #ffffff !important;
}

.dash-hero-sub {
  font-size: 0.875rem;
  color: #94a3b8;
  margin-top: 0.375rem;
}

.dash-avatar {
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 0.75rem;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: #ffffff;
  font-size: 1.5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

/* Stats Cards Grid */
.dash-stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.dash-stat-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  padding: 1.125rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.dash-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.3);
  border-color: #475569;
}

.dash-stat-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8 !important;
  margin-bottom: 0.5rem;
}

.dash-stat-val {
  font-size: 1.875rem;
  font-weight: 800;
  line-height: 1;
  color: #ffffff !important;
}

.dash-stat-sub {
  font-size: 0.75rem;
  font-weight: 600;
  margin-top: 0.375rem;
}

/* Quick Action Bar */
.dash-action-bar {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.dash-action-heading {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8 !important;
  margin-bottom: 0.875rem;
}

.dash-btn-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.625rem;
}

.dash-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 1.1rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 0.625rem;
  text-decoration: none !important;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
}

.dash-btn-green { background-color: #16a34a; color: #ffffff !important; }
.dash-btn-green:hover { background-color: #15803d; transform: translateY(-1px); }

.dash-btn-blue { background-color: #2563eb; color: #ffffff !important; }
.dash-btn-blue:hover { background-color: #1d4ed8; transform: translateY(-1px); }

.dash-btn-purple { background-color: #9333ea; color: #ffffff !important; }
.dash-btn-purple:hover { background-color: #7e22ce; transform: translateY(-1px); }

.dash-btn-teal { background-color: #0d9488; color: #ffffff !important; }
.dash-btn-teal:hover { background-color: #0f766e; transform: translateY(-1px); }

.dash-btn-teal-outline {
  background-color: rgba(13, 148, 136, 0.15);
  color: #2dd4bf !important;
  border: 1px solid #0d9488;
}
.dash-btn-teal-outline:hover {
  background-color: rgba(13, 148, 136, 0.3);
  color: #ffffff !important;
}

.dash-btn-indigo { background-color: #4f46e5; color: #ffffff !important; }
.dash-btn-indigo:hover { background-color: #4338ca; transform: translateY(-1px); }

.dash-btn-red-ghost {
  background-color: rgba(239, 68, 68, 0.15);
  color: #f87171 !important;
  border: 1px solid rgba(239, 68, 68, 0.3);
}
.dash-btn-red-ghost:hover {
  background-color: rgba(239, 68, 68, 0.3);
  color: #ffffff !important;
}

/* 2x2 Grid Layout */
.dash-grid-2x2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 1024px) {
  .dash-grid-2x2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Section Cards */
.dash-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
}

.dash-card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #334155;
  background-color: #0f172a;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dash-card-title {
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  color: #ffffff !important;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dash-card-link {
  font-size: 0.75rem;
  font-weight: 700;
  color: #38bdf8 !important;
  text-decoration: none !important;
  transition: color 0.15s;
}

.dash-card-link:hover {
  color: #7dd3fc !important;
  text-decoration: underline !important;
}

.dash-card-link-teal {
  color: #2dd4bf !important;
}

.dash-card-link-teal:hover {
  color: #99f6e4 !important;
}

.dash-dot {
  width: 0.625rem;
  height: 0.625rem;
  border-radius: 9999px;
  display: inline-block;
}

/* Status Badges */
.dash-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
  line-height: 1;
}

.dash-badge-published {
  background-color: rgba(16, 185, 129, 0.2);
  color: #34d399 !important;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.dash-badge-draft {
  background-color: rgba(245, 158, 11, 0.2);
  color: #fbbf24 !important;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.dash-badge-type {
  background-color: rgba(59, 130, 246, 0.2);
  color: #60a5fa !important;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.dash-badge-ca {
  background-color: rgba(20, 184, 166, 0.2);
  color: #2dd4bf !important;
  border: 1px solid rgba(20, 184, 166, 0.3);
}

/* Data Tables */
.dash-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
}

.dash-table th {
  padding: 0.75rem 1rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8 !important;
  border-bottom: 1px solid #334155;
  background-color: #0f172a;
}

.dash-table td {
  padding: 0.875rem 1rem;
  border-bottom: 1px solid #334155;
  color: #f1f5f9 !important;
}

.dash-table tr:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

.dash-action-edit {
  color: #38bdf8 !important;
  font-weight: 600;
  text-decoration: none !important;
}
.dash-action-edit:hover { text-decoration: underline !important; color: #7dd3fc !important; }

.dash-action-delete {
  color: #f87171 !important;
  font-weight: 600;
  text-decoration: none !important;
}
.dash-action-delete:hover { text-decoration: underline !important; color: #fca5a5 !important; }

/* Pagination */
.dash-pagination {
  padding: 0.875rem 1.25rem;
  border-top: 1px solid #334155;
  background-color: #0f172a;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
  color: #94a3b8;
}

.dash-page-link {
  padding: 0.3rem 0.65rem;
  border-radius: 0.375rem;
  background-color: #1e293b;
  border: 1px solid #334155;
  color: #cbd5e1 !important;
  text-decoration: none !important;
  transition: all 0.15s;
}

.dash-page-link:hover {
  background-color: #334155;
  color: #ffffff !important;
}

.dash-page-link.active {
  background-color: #2563eb;
  border-color: #2563eb;
  color: #ffffff !important;
  font-weight: 700;
}

.dash-page-link.active-teal {
  background-color: #0d9488;
  border-color: #0d9488;
  color: #ffffff !important;
  font-weight: 700;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

<div class="dash-wrapper">
  <div class="dash-container">
    
    <!-- Hero Welcome Banner -->
    <div class="dash-hero">
      <div style="display: flex; align-items: center; gap: 1rem;">
        <div class="dash-avatar">
          <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
          <h1 class="dash-hero-title">Welcome back, <?= e($_SESSION['admin_name'] ?? 'Admin') ?></h1>
          <div class="dash-hero-sub">
            <?= date('l, F j, Y') ?> &bull; FromCampus Admin Control Panel
          </div>
        </div>
      </div>
      
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <a href="/" target="_blank" class="dash-btn" style="background-color: rgba(255,255,255,0.15); color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2);">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          View Main Site
        </a>
        <a href="/adminqeIUgwefgWEOAjx/logout" class="dash-btn dash-btn-red-ghost">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Logout
        </a>
      </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="dash-stat-grid">
      <div class="dash-stat-card">
        <div class="dash-stat-label">Total Jobs</div>
        <div class="dash-stat-val"><?= e($totalJobs) ?></div>
        <div class="dash-stat-sub" style="color: #60a5fa;">Job Listings</div>
      </div>
      
      <div class="dash-stat-card">
        <div class="dash-stat-label">Published Jobs</div>
        <div class="dash-stat-val" style="color: #34d399;"><?= e($published) ?></div>
        <div class="dash-stat-sub" style="color: #34d399;">Active Online</div>
      </div>
      
      <div class="dash-stat-card">
        <div class="dash-stat-label">Total Users</div>
        <div class="dash-stat-val" style="color: #c084fc;"><?= e($totalUsers) ?></div>
        <div class="dash-stat-sub" style="color: #c084fc;">Registered</div>
      </div>
      
      <div class="dash-stat-card">
        <div class="dash-stat-label">Total Updates</div>
        <div class="dash-stat-val" style="color: #fbbf24;"><?= e($totalUpdates) ?></div>
        <div class="dash-stat-sub" style="color: #fbbf24;">Exams &amp; Results</div>
      </div>
      
      <div class="dash-stat-card">
        <div class="dash-stat-label">Total Books</div>
        <div class="dash-stat-val" style="color: #e879f9;"><?= e($totalBooks) ?></div>
        <div class="dash-stat-sub" style="color: #e879f9;"><?= e($activeBooks) ?> Active</div>
      </div>
      
      <div class="dash-stat-card" style="border-color: #0d9488; background: linear-gradient(135deg, #1e293b 0%, #134e4a 100%);">
        <div class="dash-stat-label" style="color: #2dd4bf !important;">Current Affairs</div>
        <div class="dash-stat-val" style="color: #2dd4bf !important;"><?= e($totalCA) ?></div>
        <div class="dash-stat-sub" style="color: #5eead4;"><?= e($publishedCA) ?> Published</div>
      </div>
    </div>

    <!-- Quick Management Actions Bar -->
    <div class="dash-action-bar">
      <div class="dash-action-heading">Quick Management Actions</div>
      <div class="dash-btn-group">
        <a href="/adminqeIUgwefgWEOAjx/add_job" class="dash-btn dash-btn-green">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add Job
        </a>
        
        <a href="/adminqeIUgwefgWEOAjx/add_update" class="dash-btn dash-btn-blue">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add Update
        </a>
        
        <a href="/adminqeIUgwefgWEOAjx/add_book" class="dash-btn dash-btn-purple">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add Book
        </a>
        
        <a href="/adminqeIUgwefgWEOAjx/add_current_affairs" class="dash-btn dash-btn-teal">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add Current Affairs
        </a>
        
        <a href="/adminqeIUgwefgWEOAjx/current_affairs" class="dash-btn dash-btn-teal-outline">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-.586-1.414l-4.5-4.5A2 2 0 0012.586 3H5"/></svg>
          Manage Current Affairs
        </a>
        
        <a href="/adminqeIUgwefgWEOAjx/mock_tests" class="dash-btn dash-btn-indigo">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Manage Mock Tests
        </a>

        <a href="/adminqeIUgwefgWEOAjx/system_health" class="dash-btn" style="background-color: #0284c7; color: #ffffff !important;">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          System Health Diagnostics
        </a>
      </div>
    </div>

    <!-- Content Sections Grid (2x2 Layout) -->
    <div class="dash-grid-2x2">
      
      <!-- Jobs Section -->
      <div class="dash-card">
        <div>
          <div class="dash-card-header">
            <h2 class="dash-card-title">
              <span class="dash-dot" style="background-color: #10b981;"></span>
              Recent Jobs
            </h2>
            <a href="/adminqeIUgwefgWEOAjx/jobs" class="dash-card-link">View All &rarr;</a>
          </div>
          
          <div style="overflow-x: auto;">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Company</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($r = $jobStmt->fetch()): ?>
                <tr>
                  <td class="line-clamp-1" style="font-weight: 600; color: #ffffff !important;" title="<?= e($r['job_title']) ?>">
                    <?= e($r['job_title']) ?>
                  </td>
                  <td style="color: #cbd5e1 !important;"><?= e($r['company_name']) ?></td>
                  <td>
                    <span class="dash-badge <?= $r['status'] === 'published' ? 'dash-badge-published' : 'dash-badge-draft' ?>">
                      <?= e($r['status']) ?>
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <a class="dash-action-edit" href="/adminqeIUgwefgWEOAjx/edit_job?id=<?= e($r['job_id']) ?>">Edit</a>
                    <span style="color: #475569; margin: 0 4px;">|</span>
                    <a class="dash-action-delete" href="/adminqeIUgwefgWEOAjx/delete_job?id=<?= e($r['job_id']) ?>" onclick="return confirm('Delete this job?')">Delete</a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Jobs Pagination -->
        <?php if ($totalJobPages > 1): ?>
        <div class="dash-pagination">
          <span>Showing <?= $jobOffset + 1 ?>-<?= min($jobOffset + $jobsPerPage, $totalJobs) ?> of <?= $totalJobs ?></span>
          <div style="display: flex; gap: 4px;">
            <?php if ($jobPage > 1): ?>
              <a href="<?= buildDashUrl(['jpage' => $jobPage - 1]) ?>" class="dash-page-link">Prev</a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $jobPage - 1);
            $endPage = min($totalJobPages, $startPage + 3);
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
              <a href="<?= buildDashUrl(['jpage' => $i]) ?>" class="dash-page-link <?= $i == $jobPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($jobPage < $totalJobPages): ?>
              <a href="<?= buildDashUrl(['jpage' => $jobPage + 1]) ?>" class="dash-page-link">Next</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Updates Section -->
      <div class="dash-card">
        <div>
          <div class="dash-card-header">
            <h2 class="dash-card-title">
              <span class="dash-dot" style="background-color: #3b82f6;"></span>
              Recent Updates
            </h2>
            <a href="/adminqeIUgwefgWEOAjx/updates" class="dash-card-link">View All &rarr;</a>
          </div>
          
          <div style="overflow-x: auto;">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($r = $updateStmt->fetch()): ?>
                <tr>
                  <td class="line-clamp-1" style="font-weight: 600; color: #ffffff !important;" title="<?= e($r['title']) ?>">
                    <?= e($r['title']) ?>
                  </td>
                  <td>
                    <?php 
                    $typeLabels = ['exam' => 'Exam', 'ans_key' => 'Answer Key', 'result' => 'Result', 'syllabus' => 'Syllabus'];
                    $type = $typeLabels[$r['update_type']] ?? ucfirst($r['update_type']);
                    ?>
                    <span class="dash-badge dash-badge-type"><?= e($type) ?></span>
                  </td>
                  <td style="color: #cbd5e1 !important; font-size: 0.75rem;"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                  <td style="text-align: right;">
                    <a class="dash-action-edit" href="/adminqeIUgwefgWEOAjx/edit_update?id=<?= e($r['id']) ?>">Edit</a>
                    <span style="color: #475569; margin: 0 4px;">|</span>
                    <a class="dash-action-delete" href="/adminqeIUgwefgWEOAjx/delete_update?id=<?= e($r['id']) ?>" onclick="return confirm('Delete this update?')">Delete</a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Updates Pagination -->
        <?php if ($totalUpdatePages > 1): ?>
        <div class="dash-pagination">
          <span>Showing <?= $updateOffset + 1 ?>-<?= min($updateOffset + $updatesPerPage, $totalUpdates) ?> of <?= $totalUpdates ?></span>
          <div style="display: flex; gap: 4px;">
            <?php if ($updatePage > 1): ?>
              <a href="<?= buildDashUrl(['upage' => $updatePage - 1]) ?>" class="dash-page-link">Prev</a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $updatePage - 1);
            $endPage = min($totalUpdatePages, $startPage + 3);
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
              <a href="<?= buildDashUrl(['upage' => $i]) ?>" class="dash-page-link <?= $i == $updatePage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($updatePage < $totalUpdatePages): ?>
              <a href="<?= buildDashUrl(['upage' => $updatePage + 1]) ?>" class="dash-page-link">Next</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Books Section -->
      <div class="dash-card">
        <div>
          <div class="dash-card-header">
            <h2 class="dash-card-title">
              <span class="dash-dot" style="background-color: #a855f7;"></span>
              Recent Books
            </h2>
            <a href="/adminqeIUgwefgWEOAjx/books" class="dash-card-link">View All &rarr;</a>
          </div>
          
          <div style="overflow-x: auto;">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Author</th>
                  <th>Category</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($r = $bookStmt->fetch()): ?>
                <tr>
                  <td class="line-clamp-1" style="font-weight: 600; color: #ffffff !important;" title="<?= e($r['title']) ?>">
                    <?= e($r['title']) ?>
                  </td>
                  <td style="color: #cbd5e1 !important;"><?= e($r['author']) ?></td>
                  <td>
                    <?php 
                    $currentCategory = null;
                    if (isset($bookCategories) && is_array($bookCategories)) {
                        foreach ($bookCategories as $category) {
                            if ($category['category_slug'] === $r['book_type']) {
                                $currentCategory = $category;
                                break;
                            }
                        }
                    }
                    $bookType = $currentCategory ? $currentCategory['category_name'] : ucfirst(str_replace('-', ' ', $r['book_type']));
                    ?>
                    <span class="dash-badge dash-badge-type"><?= e($bookType) ?></span>
                  </td>
                  <td style="text-align: right;">
                    <a class="dash-action-edit" href="/adminqeIUgwefgWEOAjx/edit_book?id=<?= e($r['id']) ?>">Edit</a>
                    <span style="color: #475569; margin: 0 4px;">|</span>
                    <a class="dash-action-delete" href="/adminqeIUgwefgWEOAjx/delete_book?id=<?= e($r['id']) ?>" onclick="return confirm('Delete this book?')">Delete</a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Books Pagination -->
        <?php if ($totalBookPages > 1): ?>
        <div class="dash-pagination">
          <span>Showing <?= $bookOffset + 1 ?>-<?= min($bookOffset + $booksPerPage, $totalBooks) ?> of <?= $totalBooks ?></span>
          <div style="display: flex; gap: 4px;">
            <?php if ($bookPage > 1): ?>
              <a href="<?= buildDashUrl(['bpage' => $bookPage - 1]) ?>" class="dash-page-link">Prev</a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $bookPage - 1);
            $endPage = min($totalBookPages, $startPage + 3);
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
              <a href="<?= buildDashUrl(['bpage' => $i]) ?>" class="dash-page-link <?= $i == $bookPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($bookPage < $totalBookPages): ?>
              <a href="<?= buildDashUrl(['bpage' => $bookPage + 1]) ?>" class="dash-page-link">Next</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Current Affairs Section -->
      <div class="dash-card" style="border-color: #0d9488;">
        <div>
          <div class="dash-card-header" style="background-color: #0f172a; border-bottom-color: #0d9488;">
            <h2 class="dash-card-title">
              <span class="dash-dot" style="background-color: #14b8a6;"></span>
              Recent Current Affairs
            </h2>
            <a href="/adminqeIUgwefgWEOAjx/current_affairs" class="dash-card-link-teal">View All &rarr;</a>
          </div>
          
          <div style="overflow-x: auto;">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($totalCA === 0): ?>
                  <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 1.5rem 1rem;">No Current Affairs articles added yet.</td>
                  </tr>
                <?php else: ?>
                  <?php while ($r = $caStmt->fetch()): ?>
                  <tr>
                    <td class="line-clamp-1" style="font-weight: 600; color: #ffffff !important;" title="<?= e($r['title']) ?>">
                      <?= e($r['title']) ?>
                    </td>
                    <td>
                      <span class="dash-badge dash-badge-ca">
                        <?= e($r['category'] ?? 'National') ?>
                      </span>
                    </td>
                    <td>
                      <span class="dash-badge <?= $r['status'] === 'published' ? 'dash-badge-published' : 'dash-badge-draft' ?>">
                        <?= e(ucfirst($r['status'])) ?>
                      </span>
                    </td>
                    <td style="text-align: right;">
                      <a class="dash-action-edit" href="/adminqeIUgwefgWEOAjx/edit_current_affairs?id=<?= e($r['id']) ?>">Edit</a>
                      <span style="color: #475569; margin: 0 4px;">|</span>
                      <a class="dash-action-delete" href="/adminqeIUgwefgWEOAjx/delete_current_affairs?id=<?= e($r['id']) ?>" onclick="return confirm('Delete this current affairs article?')">Delete</a>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Current Affairs Pagination -->
        <?php if ($totalCAPages > 1): ?>
        <div class="dash-pagination">
          <span>Showing <?= $caOffset + 1 ?>-<?= min($caOffset + $caPerPage, $totalCA) ?> of <?= $totalCA ?></span>
          <div style="display: flex; gap: 4px;">
            <?php if ($caPage > 1): ?>
              <a href="<?= buildDashUrl(['capage' => $caPage - 1]) ?>" class="dash-page-link">Prev</a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $caPage - 1);
            $endPage = min($totalCAPages, $startPage + 3);
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
              <a href="<?= buildDashUrl(['capage' => $i]) ?>" class="dash-page-link <?= $i == $caPage ? 'active-teal' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($caPage < $totalCAPages): ?>
              <a href="<?= buildDashUrl(['capage' => $caPage + 1]) ?>" class="dash-page-link">Next</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
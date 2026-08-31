<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE :q OR description LIKE :q)";
    $params[':q'] = "%$search%";
}

if ($category) {
    $where[] = "category = :category";
    $params[':category'] = $category;
}

if ($statusFilter) {
    $where[] = "status = :status";
    $params[':status'] = $statusFilter;
}

$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM current_affairs $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Fetch records
$stmt = $pdo->prepare("SELECT * FROM current_affairs $whereSql ORDER BY event_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$articles = $stmt->fetchAll();

$categories = ['National', 'International', 'Sports', 'Economy', 'Science & Tech', 'Appointments', 'Obituaries', 'Awards & Honours'];
?>

<style>
.ca-wrapper {
  color: #f8fafc;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.ca-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.ca-header-bar {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.ca-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff !important;
  margin: 0;
}

.ca-sub {
  font-size: 0.875rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

.ca-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.ca-form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .ca-form-grid {
    grid-template-columns: 2fr 1fr 1fr auto;
  }
}

.ca-input, .ca-select {
  width: 100%;
  padding: 0.6rem 0.875rem;
  background-color: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  color: #f8fafc !important;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s;
}

.ca-input:focus, .ca-select:focus {
  border-color: #0d9488;
}

.ca-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.1rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 0.5rem;
  text-decoration: none !important;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
  white-space: nowrap;
}

.ca-btn-teal { background-color: #0d9488; color: #ffffff !important; }
.ca-btn-teal:hover { background-color: #0f766e; }

.ca-btn-slate { background-color: #334155; color: #ffffff !important; }
.ca-btn-slate:hover { background-color: #475569; }

.ca-btn-outline {
  background-color: transparent;
  color: #cbd5e1 !important;
  border: 1px solid #334155;
}
.ca-btn-outline:hover { background-color: #334155; color: #ffffff !important; }

.ca-table-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.ca-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
}

.ca-table th {
  padding: 0.875rem 1rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8 !important;
  border-bottom: 1px solid #334155;
  background-color: #0f172a;
}

.ca-table td {
  padding: 0.875rem 1rem;
  border-bottom: 1px solid #334155;
  color: #f1f5f9 !important;
  vertical-align: middle;
}

.ca-table tr:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

.ca-thumb {
  width: 2.75rem;
  height: 2.75rem;
  object-fit: cover;
  border-radius: 0.5rem;
  border: 1px solid #334155;
  background-color: #0f172a;
}

.ca-thumb-placeholder {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.5rem;
  background-color: #0f172a;
  border: 1px solid #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 0.65rem;
  font-weight: 600;
}

.ca-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
  line-height: 1;
}

.ca-badge-cat {
  background-color: rgba(20, 184, 166, 0.15);
  color: #2dd4bf !important;
  border: 1px solid rgba(20, 184, 166, 0.3);
}

.ca-badge-pub {
  background-color: rgba(16, 185, 129, 0.15);
  color: #34d399 !important;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.ca-badge-draft {
  background-color: rgba(245, 158, 11, 0.15);
  color: #fbbf24 !important;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.ca-link-title {
  color: #ffffff !important;
  font-weight: 600;
  text-decoration: none !important;
  transition: color 0.15s;
}
.ca-link-title:hover { color: #38bdf8 !important; text-decoration: underline !important; }

.ca-action-edit {
  color: #38bdf8 !important;
  font-weight: 600;
  text-decoration: none !important;
}
.ca-action-edit:hover { text-decoration: underline !important; color: #7dd3fc !important; }

.ca-action-del {
  color: #f87171 !important;
  font-weight: 600;
  text-decoration: none !important;
}
.ca-action-del:hover { text-decoration: underline !important; color: #fca5a5 !important; }

.ca-pagination {
  padding: 1rem;
  background-color: #0f172a;
  border-top: 1px solid #334155;
  display: flex;
  justify-content: center;
  gap: 0.375rem;
}

.ca-page-item {
  padding: 0.4rem 0.75rem;
  border-radius: 0.375rem;
  background-color: #1e293b;
  border: 1px solid #334155;
  color: #cbd5e1 !important;
  text-decoration: none !important;
  font-size: 0.875rem;
  font-weight: 500;
}

.ca-page-item:hover {
  background-color: #334155;
  color: #ffffff !important;
}

.ca-page-item.active {
  background-color: #0d9488;
  border-color: #0d9488;
  color: #ffffff !important;
  font-weight: 700;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

<div class="ca-wrapper">
  <div class="ca-container">
    
    <!-- Top Header -->
    <div class="ca-header-bar">
      <div>
        <h1 class="ca-title">Current Affairs Management</h1>
        <div class="ca-sub">Total Articles Found: <strong style="color: #2dd4bf;"><?= $total ?></strong></div>
      </div>
      
      <div style="display: flex; gap: 0.625rem;">
        <a href="/adminqeIUgwefgWEOAjx/dashboard" class="ca-btn ca-btn-outline">
          &larr; Back to Dashboard
        </a>
        <a href="/adminqeIUgwefgWEOAjx/add_current_affairs" class="ca-btn ca-btn-teal">
          <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add New Article
        </a>
      </div>
    </div>

    <!-- Search & Filter Form -->
    <div class="ca-card">
      <form method="get" class="ca-form-grid">
        <div>
          <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search article title or content..." class="ca-input">
        </div>
        <div>
          <select name="category" class="ca-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <select name="status" class="ca-select">
            <option value="">All Statuses</option>
            <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <button type="submit" class="ca-btn ca-btn-teal">Filter</button>
          <a href="/adminqeIUgwefgWEOAjx/current_affairs" class="ca-btn ca-btn-outline">Reset</a>
        </div>
      </form>
    </div>

    <!-- Articles Table Card -->
    <div class="ca-table-card">
      <div style="overflow-x: auto;">
        <table class="ca-table">
          <thead>
            <tr>
              <th style="width: 60px;">Image</th>
              <th>Article Title</th>
              <th>Category</th>
              <th>Event Date</th>
              <th>Views</th>
              <th>Status</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($articles)): ?>
              <tr>
                <td colspan="7" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                  No Current Affairs articles found matching your criteria.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($articles as $item): ?>
                <tr>
                  <td>
                    <?php if (!empty($item['thumbnail'])): ?>
                      <img src="<?= e($item['thumbnail']) ?>" alt="" class="ca-thumb">
                    <?php else: ?>
                      <div class="ca-thumb-placeholder">NO IMG</div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="/current-affairs/<?= e($item['slug']) ?>" target="_blank" class="ca-link-title line-clamp-2" title="<?= e($item['title']) ?>">
                      <?= e($item['title']) ?>
                    </a>
                  </td>
                  <td>
                    <span class="ca-badge ca-badge-cat">
                      <?= e($item['category'] ?? 'General') ?>
                    </span>
                  </td>
                  <td style="color: #94a3b8; font-size: 0.8rem; white-space: nowrap;">
                    <?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : 'N/A' ?>
                  </td>
                  <td style="font-weight: 700; color: #38bdf8;">
                    <?= number_format($item['views']) ?>
                  </td>
                  <td>
                    <span class="ca-badge <?= $item['status'] === 'published' ? 'ca-badge-pub' : 'ca-badge-draft' ?>">
                      <?= e(ucfirst($item['status'])) ?>
                    </span>
                  </td>
                  <td style="text-align: right; white-space: nowrap;">
                    <a href="/adminqeIUgwefgWEOAjx/edit_current_affairs?id=<?= $item['id'] ?>" class="ca-action-edit">Edit</a>
                    <span style="color: #475569; margin: 0 4px;">|</span>
                    <a href="/adminqeIUgwefgWEOAjx/delete_current_affairs?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to delete this current affairs article?')" class="ca-action-del">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="ca-pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($statusFilter) ?>" class="ca-page-item <?= $i === $page ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../../.hta_slug/_footer.php'; ?>

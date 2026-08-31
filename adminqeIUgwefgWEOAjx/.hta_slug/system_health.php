<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require_admin();

$healthData = check_admin_modules($pdo);

$totalModules = count($healthData);
$passedModules = 0;
$failedModules = 0;

foreach ($healthData as $m) {
    if ($m['status'] === 'OK') {
        $passedModules++;
    } else {
        $failedModules++;
    }
}

$systemOverall = ($failedModules === 0) ? 'HEALTHY' : 'ISSUES DETECTED';
?>

<style>
.sh-wrapper {
  color: #f8fafc;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.sh-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.sh-header-bar {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.sh-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff !important;
  margin: 0;
}

.sh-btn {
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
}

.sh-btn-teal { background-color: #0d9488; color: #ffffff !important; }
.sh-btn-teal:hover { background-color: #0f766e; }

.sh-btn-outline {
  background-color: transparent;
  color: #cbd5e1 !important;
  border: 1px solid #334155;
}
.sh-btn-outline:hover { background-color: #334155; color: #ffffff !important; }

/* Status Cards Grid */
.sh-stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.sh-stat-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  padding: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.sh-stat-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8 !important;
  margin-bottom: 0.375rem;
}

.sh-stat-val {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff !important;
}

/* Badges */
.sh-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 9999px;
  line-height: 1;
}

.sh-badge-pass {
  background-color: rgba(16, 185, 129, 0.2);
  color: #34d399 !important;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.sh-badge-fail {
  background-color: rgba(239, 68, 68, 0.2);
  color: #f87171 !important;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.sh-badge-warn {
  background-color: rgba(245, 158, 11, 0.2);
  color: #fbbf24 !important;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

/* Table Card */
.sh-table-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.sh-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
}

.sh-table th {
  padding: 0.875rem 1rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8 !important;
  border-bottom: 1px solid #334155;
  background-color: #0f172a;
}

.sh-table td {
  padding: 1rem;
  border-bottom: 1px solid #334155;
  color: #f1f5f9 !important;
  vertical-align: middle;
}

.sh-table tr:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

.sh-msg-box {
  background-color: rgba(239, 68, 68, 0.1);
  border-left: 3px solid #ef4444;
  padding: 0.5rem 0.75rem;
  margin-top: 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #fca5a5;
}
</style>

<div class="sh-wrapper">
  <div class="sh-container">
    
    <!-- Top Header -->
    <div class="sh-header-bar">
      <div>
        <h1 class="sh-title">System Health & Module Diagnostics</h1>
        <p style="font-size: 0.875rem; color: #94a3b8; margin-top: 0.25rem;">
          Production environment automated route & database schema validator
        </p>
      </div>
      
      <div style="display: flex; gap: 0.625rem;">
        <a href="/adminqeIUgwefgWEOAjx/dashboard" class="sh-btn sh-btn-outline">
          &larr; Dashboard
        </a>
        <a href="/adminqeIUgwefgWEOAjx/system_health" class="sh-btn sh-btn-teal">
          ↻ Re-run Diagnostic Check
        </a>
      </div>
    </div>

    <!-- Health Summary Cards -->
    <div class="sh-stat-grid">
      <div class="sh-stat-card">
        <div class="sh-stat-label">System Health Status</div>
        <div class="sh-stat-val" style="color: <?= $failedModules === 0 ? '#34d399' : '#f87171' ?> !important;">
          <?= $systemOverall ?>
        </div>
      </div>
      
      <div class="sh-stat-card">
        <div class="sh-stat-label">Modules Checked</div>
        <div class="sh-stat-val"><?= $totalModules ?></div>
      </div>

      <div class="sh-stat-card">
        <div class="sh-stat-label">Healthy Modules</div>
        <div class="sh-stat-val" style="color: #34d399 !important;"><?= $passedModules ?> / <?= $totalModules ?></div>
      </div>

      <div class="sh-stat-card">
        <div class="sh-stat-label">PHP Engine</div>
        <div class="sh-stat-val" style="font-size: 1.25rem; color: #60a5fa !important;">PHP <?= PHP_VERSION ?></div>
      </div>
    </div>

    <!-- Diagnostic Details Table -->
    <div class="sh-table-card">
      <div style="overflow-x: auto;">
        <table class="sh-table">
          <thead>
            <tr>
              <th>Module Name</th>
              <th>Route Slug</th>
              <th>Handler File</th>
              <th>DB Table &amp; Schema</th>
              <th>Upload Dirs</th>
              <th>Health Status</th>
              <th style="text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($healthData as $name => $m): ?>
              <tr>
                <td style="font-weight: 700; color: #ffffff !important;">
                  <?= e($name) ?>
                </td>
                
                <td>
                  <code style="background-color: #0f172a; padding: 0.2rem 0.5rem; border-radius: 0.25rem; color: #38bdf8; font-size: 0.8rem;">
                    /<?= e($m['slug']) ?>
                  </code>
                </td>

                <td>
                  <?php if ($m['file_exists']): ?>
                    <span style="color: #34d399; font-weight: 600;">✓ Present</span>
                    <?php if ($m['syntax_check'] === 'PASS'): ?>
                      <span style="font-size: 0.75rem; color: #94a3b8;">(Lint PASS)</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span style="color: #f87171; font-weight: 600;">❌ Missing</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($m['table_exists'] && $m['columns_ok']): ?>
                    <span style="color: #34d399; font-weight: 600;">✓ Valid Schema</span>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                      (<?= number_format($m['record_count']) ?> records)
                    </div>
                  <?php else: ?>
                    <span style="color: #f87171; font-weight: 600;">❌ Invalid DB Schema</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($m['dirs_ok']): ?>
                    <span style="color: #34d399; font-weight: 600;">✓ Writable</span>
                  <?php else: ?>
                    <span style="color: #fbbf24; font-weight: 600;">⚠️ Not Writable</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($m['status'] === 'OK'): ?>
                    <span class="sh-badge sh-badge-pass">PASS</span>
                  <?php elseif ($m['status'] === 'WARNING'): ?>
                    <span class="sh-badge sh-badge-warn">WARNING</span>
                  <?php else: ?>
                    <span class="sh-badge sh-badge-fail">FAIL</span>
                  <?php endif; ?>
                </td>

                <td style="text-align: right;">
                  <a href="/adminqeIUgwefgWEOAjx/<?= e($m['slug']) ?>" target="_blank" style="color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 0.8rem;">
                    Test Route &rarr;
                  </a>
                </td>
              </tr>

              <?php if (!empty($m['messages'])): ?>
                <tr>
                  <td colspan="7" style="background-color: #0f172a; padding: 0.75rem 1rem;">
                    <?php foreach ($m['messages'] as $msg): ?>
                      <div class="sh-msg-box">
                        ⚠️ <strong><?= e($name) ?> Error:</strong> <?= e($msg) ?>
                      </div>
                    <?php endforeach; ?>
                  </td>
                </tr>
              <?php endif; ?>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../../.hta_slug/_footer.php'; ?>

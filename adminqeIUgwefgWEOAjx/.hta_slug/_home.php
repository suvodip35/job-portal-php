<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require_admin();

// stats
$totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$published = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status='published'")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalUpdates = (int)$pdo->query("SELECT COUNT(*) FROM updates")->fetchColumn();
$siteTitle = "FromCampus - Admin Dashboard";

// Pagination for jobs
$jobsPerPage = 5;
$jobPage = isset($_GET['jpage']) ? max(1, (int)$_GET['jpage']) : 1;
$jobOffset = ($jobPage - 1) * $jobsPerPage;
$totalJobPages = ceil($totalJobs / $jobsPerPage);

// Fetch jobs with pagination
$jobStmt = $pdo->prepare("SELECT job_id, job_title, company_name, status, posted_date FROM jobs ORDER BY posted_date DESC LIMIT :limit OFFSET :offset");
$jobStmt->bindValue(':limit', $jobsPerPage, PDO::PARAM_INT);
$jobStmt->bindValue(':offset', $jobOffset, PDO::PARAM_INT);
$jobStmt->execute();

// Pagination for updates
$updatesPerPage = 5;
$updatePage = isset($_GET['upage']) ? max(1, (int)$_GET['upage']) : 1;
$updateOffset = ($updatePage - 1) * $updatesPerPage;
$totalUpdatePages = ceil($totalUpdates / $updatesPerPage);

// Fetch updates with pagination
$updateStmt = $pdo->prepare("SELECT id, title, update_type, created_at FROM updates ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$updateStmt->bindValue(':limit', $updatesPerPage, PDO::PARAM_INT);
$updateStmt->bindValue(':offset', $updateOffset, PDO::PARAM_INT);
$updateStmt->execute();
?>

<div class="container mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-1 dark:text-white">Admin Dashboard</h1>
  <p class="text-xl font-bold mb-4 dark:text-white">Welcome Back <?= $_SESSION['admin_name']?></p>
  
  <!-- Stats Cards -->
  <div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
      <h3 class="font-semibold dark:text-white">Total Jobs</h3>
      <div class="text-3xl dark:text-white"><?= e($totalJobs) ?></div>
    </div>
    <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
      <h3 class="font-semibold dark:text-white">Published</h3>
      <div class="text-3xl dark:text-white"><?= e($published) ?></div>
    </div>
    <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
      <h3 class="font-semibold dark:text-white">Total Users</h3>
      <div class="text-3xl dark:text-white"><?= e($totalUsers) ?></div>
    </div>
    <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
      <h3 class="font-semibold dark:text-white">Total Updates</h3>
      <div class="text-3xl dark:text-white"><?= e($totalUpdates) ?></div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="mb-6 flex flex-wrap gap-2">
    <a href="add_job" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">Add Job</a>
    <a href="add_update" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Add Update</a>
    <a href="mock_tests" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">Manage Mock Tests</a>
    <a href="logout" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">Logout</a>
  </div>

  <!-- Content Sections -->
  <div class="grid md:grid-cols-2 gap-6">
    <!-- Jobs Section -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-3 dark:text-white">Jobs</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b dark:border-gray-700">
              <th class="p-2 dark:text-white">Title</th>
              <th class="p-2 dark:text-white">Company</th>
              <th class="p-2 dark:text-white">Status</th>
              <th class="p-2 dark:text-white">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $jobStmt->fetch()): ?>
            <tr class="border-b dark:border-gray-700">
              <td class="p-2 line-clamp-1 dark:text-white"><?= e($r['job_title']) ?></td>
              <td class="p-2 dark:text-white"><?= e($r['company_name']) ?></td>
              <td class="p-2 dark:text-white">
                <span class="px-2 py-1 text-xs rounded-full <?= $r['status'] === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' ?>">
                  <?= e($r['status']) ?>
                </span>
              </td>
              <td class="p-2">
                <div class="inline-flex gap-x-2">
                  <a class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" href="edit_job/?id=<?= e($r['job_id']) ?>">Edit</a> |
                  <a class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" href="delete_job?id=<?= e($r['job_id']) ?>" onclick="return confirm('Delete this job?')">Delete</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Jobs Pagination -->
      <?php if ($totalJobPages > 1): ?>
      <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Showing <?= $jobOffset + 1 ?>-<?= min($jobOffset + $jobsPerPage, $totalJobs) ?> of <?= $totalJobs ?> jobs
        </div>
        <div class="flex space-x-1">
          <?php if ($jobPage > 1): ?>
            <a href="?jpage=<?= $jobPage - 1 ?><?= isset($_GET['upage']) ? '&upage=' . $_GET['upage'] : '' ?>" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Previous</a>
          <?php endif; ?>
          
          <?php 
          $startPage = max(1, $jobPage - 2);
          $endPage = min($totalJobPages, $startPage + 4);
          $startPage = max(1, $endPage - 4);
          
          for ($i = $startPage; $i <= $endPage; $i++): 
          ?>
            <a href="?jpage=<?= $i ?><?= isset($_GET['upage']) ? '&upage=' . $_GET['upage'] : '' ?>" class="px-3 py-1 <?= $i == $jobPage ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' ?> rounded transition-colors"><?= $i ?></a>
          <?php endfor; ?>
          
          <?php if ($jobPage < $totalJobPages): ?>
            <a href="?jpage=<?= $jobPage + 1 ?><?= isset($_GET['upage']) ? '&upage=' . $_GET['upage'] : '' ?>" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Next</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Updates Section -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-3 dark:text-white">Updates</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b dark:border-gray-700">
              <th class="p-2 dark:text-white">Title</th>
              <th class="p-2 dark:text-white">Type</th>
              <th class="p-2 dark:text-white">Date</th>
              <th class="p-2 dark:text-white">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $updateStmt->fetch()): ?>
            <tr class="border-b dark:border-gray-700">
              <td class="p-2 line-clamp-1 dark:text-white"><?= e($r['title']) ?></td>
              <td class="p-2 dark:text-white">
                <?php 
                $typeLabels = [
                  'exam' => 'Exam',
                  'ans_key' => 'Answer Key',
                  'result' => 'Result',
                  'syllabus' => 'Syllabus'
                ];
                $type = $typeLabels[$r['update_type']] ?? ucfirst($r['update_type']);
                $typeColors = [
                  'exam' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                  'ans_key' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                  'result' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                  'syllabus' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                ];
                $color = $typeColors[$r['update_type']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                ?>
                <span class="px-2 py-1 text-xs rounded-full <?= $color ?>"><?= e($type) ?></span>
              </td>
              <td class="p-2 dark:text-white"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
              <td class="p-2">
                <div class="inline-flex gap-x-2">
                  <a class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" href="edit_update/?id=<?= e($r['id']) ?>">Edit</a> |
                  <a class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" href="delete_update?id=<?= e($r['id']) ?>" onclick="return confirm('Delete this update?')">Delete</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Updates Pagination -->
      <?php if ($totalUpdatePages > 1): ?>
      <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Showing <?= $updateOffset + 1 ?>-<?= min($updateOffset + $updatesPerPage, $totalUpdates) ?> of <?= $totalUpdates ?> updates
        </div>
        <div class="flex space-x-1">
          <?php if ($updatePage > 1): ?>
            <a href="?upage=<?= $updatePage - 1 ?><?= isset($_GET['jpage']) ? '&jpage=' . $_GET['jpage'] : '' ?>" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Previous</a>
          <?php endif; ?>
          
          <?php 
          $startPage = max(1, $updatePage - 2);
          $endPage = min($totalUpdatePages, $startPage + 4);
          $startPage = max(1, $endPage - 4);
          
          for ($i = $startPage; $i <= $endPage; $i++): 
          ?>
            <a href="?upage=<?= $i ?><?= isset($_GET['jpage']) ? '&jpage=' . $_GET['jpage'] : '' ?>" class="px-3 py-1 <?= $i == $updatePage ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' ?> rounded transition-colors"><?= $i ?></a>
          <?php endfor; ?>
          
          <?php if ($updatePage < $totalUpdatePages): ?>
            <a href="?upage=<?= $updatePage + 1 ?><?= isset($_GET['jpage']) ? '&jpage=' . $_GET['jpage'] : '' ?>" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 dark:text-white rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Next</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
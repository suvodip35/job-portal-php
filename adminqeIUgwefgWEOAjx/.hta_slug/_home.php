<?php
  require_once __DIR__ . '/../../.hta_slug/_header.php';
require_admin();
// stats
$totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$published = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status='published'")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$siteTitle = "Dashboard - Admin";

?>
<h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>
<div class="grid md:grid-cols-3 gap-4">
  <div class="p-4 rounded bg-white dark:bg-gray-800">
    <h3 class="font-semibold">Total Jobs</h3>
    <div class="text-3xl"><?= e($totalJobs) ?></div>
  </div>
  <div class="p-4 rounded bg-white dark:bg-gray-800">
    <h3 class="font-semibold">Published</h3>
    <div class="text-3xl"><?= e($published) ?></div>
  </div>
  <div class="p-4 rounded bg-white dark:bg-gray-800">
    <h3 class="font-semibold">Total Users</h3>
    <div class="text-3xl"><?= e($totalUsers) ?></div>
  </div>
</div>

<div class="mt-6">
  <a href="add_job" class="px-4 py-2 bg-green-600 text-white rounded">Add Job</a>
  <a href="mock_tests" class="px-4 py-2 bg-indigo-600 text-white rounded">Manage Mock Tests</a>
  <a href="logout" class="px-4 py-2 bg-red-600 text-white rounded">Logout</a>
</div>

<div class="mt-6">
  <h2 class="text-xl font-semibold mb-3">Latest Jobs</h2>
  <table class="w-full text-left">
    <thead><tr class="border-b"><th>Title</th><th>Company</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
<?php
$stmt = $pdo->query("SELECT job_id, job_title, company_name, status FROM jobs ORDER BY posted_date DESC LIMIT 20");
while ($r = $stmt->fetch()):
?>
<tr class="border-b">
  <td><?= e($r['job_title']) ?></td>
  <td><?= e($r['company_name']) ?></td>
  <td><?= e($r['status']) ?></td>
  <td>
    <a class="text-blue-600" href="edit_job/?id=<?= e($r['job_id']) ?>">Edit</a> |
    <a class="text-red-600" href="delete_job?id=<?= e($r['job_id']) ?>" onclick="return confirm('Delete this job?')">Delete</a>
  </td>
</tr>
<?php endwhile; ?>
    </tbody>
  </table>
</div>


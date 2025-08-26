<?php
require_once __DIR__ . '/../functions.php';
require_admin();

$tests = $pdo->query("SELECT * FROM mock_tests ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-4">Mock Tests</h1>
<a href="add_mock_test.php" class="px-3 py-2 bg-green-600 text-white rounded">Add Mock Test</a>
<table class="w-full mt-4">
  <thead><tr class="border-b"><th>Title</th><th>Duration</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($tests as $t): ?>
    <tr class="border-b">
      <td><?= e($t['title']) ?></td>
      <td><?= e($t['duration_minutes']) ?> min</td>
      <td>
        <a href="edit_mock_test.php?id=<?= e($t['id']) ?>">Edit</a> |
        <a href="view_attempts.php?test_id=<?= e($t['id']) ?>">Attempts</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

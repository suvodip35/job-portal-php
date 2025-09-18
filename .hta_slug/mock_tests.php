<?php
// require_once __DIR__ . '../.hta_config/functions.php';
require_once('_header.php');

$tests = $pdo->query("SELECT * FROM mock_tests WHERE visibility='published' ORDER BY created_at DESC")->fetchAll();
?>
<h1 class="text-2xl font-bold mb-4">Mock Tests</h1>

<div class="grid md:grid-cols-2 gap-4">
<?php foreach ($tests as $t): ?>
  <div class="p-4 border rounded bg-white dark:bg-gray-800">
    <h3 class="font-semibold"><?= e($t['title']) ?></h3>
    <p class="text-sm text-gray-600"><?= e($t['duration_minutes']) ?> minutes • <?= e($t['total_marks']) ?> marks</p>
    <div class="mt-3">
      <a class="px-3 py-2 bg-blue-600 text-white rounded" href="take_test.php?slug=<?= e($t['slug']) ?>">Start Test</a>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>

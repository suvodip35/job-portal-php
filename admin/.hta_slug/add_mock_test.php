<?php
require_admin();
require_once __DIR__ . '/../../.hta_slug/_header.php';
$err = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $slug = slugify($title);
    $slug = unique_slug($pdo, 'mock_tests', 'test_slug', $slug);
    $duration = (int)($_POST['duration_minutes'] ?? 30);
    $total = (int)($_POST['total_marks'] ?? 100);
    $neg = (float)($_POST['negative_marking'] ?? 0.0);
    $status = $_POST['visibility'] ?? 'published';
    if (!$title) $err = 'Title required';
    else {
        $stmt = $pdo->prepare("INSERT INTO mock_tests (title, slug, duration_minutes, total_marks, negative_marking, visibility, created_by) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $duration, $total, $neg, $status, $_SESSION['admin_id'] ?? null]);
        $success = 'Mock test created. Now add questions.';
    }
}

// include header
?>
<h1 class="text-2xl font-bold mb-4">Add Mock Test</h1>
<?php if ($err): ?><div class="p-2 bg-red-100 text-red-800 mb-3 rounded"><?= e($err) ?></div><?php endif; ?>
<?php if ($success): ?><div class="p-2 bg-green-100 text-green-800 mb-3 rounded"><?= e($success) ?></div><?php endif; ?>

<form method="post" class="space-y-3">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <label>Title<input name="title" class="w-full p-2 border rounded"></label>
  <div class="grid grid-cols-3 gap-3">
    <label>Duration (minutes)<input type="number" name="duration_minutes" value="30" class="w-full p-2 border rounded"></label>
    <label>Total Marks<input type="number" name="total_marks" value="100" class="w-full p-2 border rounded"></label>
    <label>Negative Marking<input type="text" name="negative_marking" value="0" class="w-full p-2 border rounded"></label>
  </div>
  <div class="flex justify-end"><button class="px-4 py-2 bg-green-600 text-white rounded">Create Test</button></div>
</form>


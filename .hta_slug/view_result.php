<?php
require_once('_header.php');
require_once __DIR__ . '/functions.php';
$attemptId = (int)($_GET['attempt'] ?? 0);
$stmt = $pdo->prepare("SELECT ta.*, mt.total_marks, mt.title FROM test_attempts ta JOIN mock_tests mt ON ta.mock_test_id = mt.id WHERE ta.id = ?");
$stmt->execute([$attemptId]);
$attempt = $stmt->fetch();
if (!$attempt) { header('Location: mock_tests.php'); exit; }

$answers = $pdo->prepare("SELECT aa.*, q.question_text, o.option_text FROM attempt_answers aa JOIN questions q ON aa.question_id = q.id LEFT JOIN options o ON aa.selected_option_id = o.id WHERE aa.attempt_id = ?");
$answers->execute([$attemptId]);
$ans = $answers->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<h1 class="text-2xl font-bold mb-4">Result: <?= e($attempt['title']) ?></h1>
<p>Score: <?= e($attempt['total_score']) ?> / <?= e($attempt['total_marks']) ?></p>

<div class="mt-4 space-y-4">
  <?php foreach ($ans as $a): ?>
    <div class="p-3 border rounded bg-white dark:bg-gray-800">
      <div class="font-semibold"><?= e($a['question_text']) ?></div>
      <div class="text-sm mt-2">
        Your answer: <?= e($a['option_text'] ?? 'No answer') ?> —
        <?= $a['is_correct'] ? '<span class="text-green-600">Correct</span>' : '<span class="text-red-600">Wrong</span>' ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

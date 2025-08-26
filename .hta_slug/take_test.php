<?php
require_once __DIR__ . '/functions.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: mock_tests.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM mock_tests WHERE slug = ? AND visibility='published' LIMIT 1");
$stmt->execute([$slug]);
$test = $stmt->fetch();
if (!$test) { header('Location: mock_tests.php'); exit; }

// On first load (GET) show start page. On POST, create attempt and show questions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    // create attempt
    $userId = $_SESSION['admin_id'] ?? null; // optional - allow guest attempts by null
    $stmt = $pdo->prepare("INSERT INTO test_attempts (user_id, mock_test_id, started_at) VALUES (?,?,NOW())");
    $stmt->execute([$userId, $test['id']]);
    $attemptId = (int)$pdo->lastInsertId();

    // fetch questions
    $questions = $pdo->prepare("SELECT q.*, GROUP_CONCAT(CONCAT(o.id, '::', o.option_text, '::', o.is_correct) SEPARATOR '||') as opts FROM questions q LEFT JOIN options o ON q.id = o.question_id WHERE q.mock_test_id = ? GROUP BY q.id ORDER BY q.order_no ASC");
    $questions->execute([$test['id']]);
    $qs = $questions->fetchAll();

    // store attempt state in session
    $_SESSION['attempt_'.$attemptId] = [
        'attempt_id' => $attemptId,
        'mock_test_id' => $test['id'],
        'expires_at' => time() + ($test['duration_minutes'] * 60)
    ];
    header('Location: take_test.php?attempt=' . $attemptId);
    exit;
}

if (isset($_GET['attempt'])) {
    $attemptId = (int)$_GET['attempt'];
    $state = $_SESSION['attempt_'.$attemptId] ?? null;
    if (!$state) { header('Location: mock_tests.php'); exit; }

    // check expiry
    if (time() > $state['expires_at']) {
        header('Location: submit_test.php?attempt=' . $attemptId);
        exit;
    }

    // get questions
    $qstmt = $pdo->prepare("SELECT q.* FROM questions q WHERE q.mock_test_id = ? ORDER BY q.order_no ASC");
    $qstmt->execute([$state['mock_test_id']]);
    $questions = $qstmt->fetchAll();

    require_once __DIR__ . '/includes/header.php';
    ?>
    <h1 class="text-2xl font-bold mb-4"><?= e($test['title']) ?></h1>
    <form method="post" action="submit_test.php">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="attempt_id" value="<?= e($attemptId) ?>">
      <?php foreach ($questions as $i => $q): ?>
        <?php
          $opts = $pdo->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY order_no ASC");
          $opts->execute([$q['id']]);
          $options = $opts->fetchAll();
        ?>
        <div class="p-4 border rounded mb-3 bg-white dark:bg-gray-800">
          <div class="font-semibold">Q<?= ($i+1) ?>. <?= $q['question_text'] ?></div>
          <div class="mt-2 space-y-2">
            <?php foreach ($options as $opt): ?>
              <label class="block"><input type="radio" name="q_<?= e($q['id']) ?>" value="<?= e($opt['id']) ?>"> <?= e($opt['option_text']) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="flex justify-end">
        <button class="px-4 py-2 bg-green-600 text-white rounded">Submit Test</button>
      </div>
    </form>

    <?php
    // require_once __DIR__ . '/includes/footer.php';
    exit;
}

// default: show start page
// require_once __DIR__ . '/includes/header.php';
?>
<h1 class="text-2xl font-bold mb-4"><?= e($test['title']) ?></h1>
<p class="text-gray-700 dark:text-gray-300"><?= e($test['total_marks']) ?> marks • <?= e($test['duration_minutes']) ?> minutes</p>

<form method="post">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <div class="mt-4">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Start Test</button>
  </div>
</form>

<!-- <?php require_once __DIR__ . '/includes/footer.php'; ?> -->

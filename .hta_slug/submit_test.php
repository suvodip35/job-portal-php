<?php
require_once __DIR__ . '/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mock_tests.php');
    exit;
}
csrf_check($_POST['csrf_token'] ?? '');
$attemptId = (int)($_POST['attempt_id'] ?? 0);
$state = $_SESSION['attempt_'.$attemptId] ?? null;
if (!$state) { die('Attempt not found or expired'); }

$mock_test_id = $state['mock_test_id'];

// fetch questions for test
$qstmt = $pdo->prepare("SELECT id, marks FROM questions WHERE mock_test_id = ?");
$qstmt->execute([$mock_test_id]);
$questions = $qstmt->fetchAll();

$totalScore = 0;
foreach ($questions as $q) {
    $qid = $q['id'];
    $selected = (int)($_POST['q_'.$qid] ?? 0);
    if ($selected) {
        // check if selected is correct
        $os = $pdo->prepare("SELECT is_correct FROM options WHERE id = ?");
        $os->execute([$selected]);
        $opt = $os->fetch();
        $is_correct = $opt ? (int)$opt['is_correct'] : 0;
        $scoreEarned = $is_correct ? (float)$q['marks'] : 0.0;
        $pdo->prepare("INSERT INTO attempt_answers (attempt_id, question_id, selected_option_id, is_correct, score_earned, answered_at) VALUES (?,?,?,?,?,NOW())")
            ->execute([$attemptId, $qid, $selected, $is_correct, $scoreEarned]);
        $totalScore += $scoreEarned;
    } else {
        // no answer
        $pdo->prepare("INSERT INTO attempt_answers (attempt_id, question_id, selected_option_id, is_correct, score_earned, answered_at) VALUES (?,?,?,?,?,NOW())")
            ->execute([$attemptId, $qid, null, 0, 0.0]);
    }
}

// update attempt
$upd = $pdo->prepare("UPDATE test_attempts SET submitted_at = NOW(), total_score = ?, duration_used_sec = ? WHERE id = ?");
$durationUsed = max(0, time() - ($_SESSION['attempt_'.$attemptId]['expires_at'] - ($mock_test_id ? ($pdo->query("SELECT duration_minutes FROM mock_tests WHERE id=".$mock_test_id)->fetchColumn()*60) : 0)));
$upd->execute([$totalScore, $durationUsed, $attemptId]);

// clear session attempt
unset($_SESSION['attempt_'.$attemptId]);

header('Location: view_result.php?attempt='.$attemptId);
exit;

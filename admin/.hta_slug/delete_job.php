<?php
require_once __DIR__ . '/../functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ?");
    $stmt->execute([$id]);
}
header('Location: dashboard.php');
exit;

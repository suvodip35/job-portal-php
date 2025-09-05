<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require_once __DIR__ . '/../functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ?");
    $stmt->execute([$id]);
}
echo "<script>window.location.href='/admin/dashboard'</script>";
// header('Location: dashboard.php');
exit;

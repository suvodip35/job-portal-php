<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    // Fetch article to delete thumbnail and pdf files
    $stmt = $pdo->prepare("SELECT thumbnail, pdf_link FROM current_affairs WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if ($item) {
        if (!empty($item['thumbnail'])) {
            $thumbPath = __DIR__ . '/../../' . ltrim($item['thumbnail'], '/');
            if (file_exists($thumbPath)) @unlink($thumbPath);
        }
        if (!empty($item['pdf_link'])) {
            $pdfPath = __DIR__ . '/../../' . ltrim($item['pdf_link'], '/');
            if (file_exists($pdfPath)) @unlink($pdfPath);
        }

        $deleteStmt = $pdo->prepare("DELETE FROM current_affairs WHERE id = ?");
        $deleteStmt->execute([$id]);
    }
}

// Redirect back to the referring page or fallback to current_affairs list
$redirectUrl = '/adminqeIUgwefgWEOAjx/current_affairs';

if (!empty($_GET['redirect'])) {
    $redirectUrl = $_GET['redirect'];
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $parsedReferer = parse_url($referer);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';

    if (empty($parsedReferer['host']) || $parsedReferer['host'] === $currentHost) {
        $redirectUrl = $referer;
    }
}

header("Location: " . $redirectUrl);
exit;
?>

<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /adminqeIUgwefgWEOAjx/current_affairs.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM current_affairs WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: /adminqeIUgwefgWEOAjx/current_affairs.php');
    exit;
}

$err = '';
$success = '';

function handleThumbnailUpload() {
    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $uploadDir = __DIR__ . '/../../thumbnails/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $file = $_FILES['thumbnail'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload error: ' . $file['error']];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'Image exceeds 5MB limit.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Invalid file format. Allowed: JPG, PNG, WEBP'];
    }
    $filename = 'ca_' . uniqid() . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (compressImage($file['tmp_name'], $destination, 80, 600, 400)) {
        return '/thumbnails/' . $filename;
    }
    return ['error' => 'Failed to save image.'];
}

function handlePdfUpload() {
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $uploadDir = __DIR__ . '/../../pdf/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $file = $_FILES['pdf_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'PDF upload error: ' . $file['error']];
    }
    if ($file['size'] > 20 * 1024 * 1024) {
        return ['error' => 'PDF exceeds 20MB limit.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return ['error' => 'Only PDF documents are allowed.'];
    }
    $filename = 'ca_pdf_' . uniqid() . '.pdf';
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return '/pdf/' . $filename;
    }
    return ['error' => 'Failed to save PDF file.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'National');
    $description = trim($_POST['description'] ?? '');
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : $article['event_date'];
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');
    $status = $_POST['status'] ?? 'published';
    $errors = [];

    if ($title === '') $errors[] = "Article title is required.";
    if ($description === '') $errors[] = "Article description is required.";

    $thumbRes = handleThumbnailUpload();
    $thumbnailToSave = $article['thumbnail'];
    if (is_array($thumbRes) && isset($thumbRes['error'])) {
        $errors[] = "Thumbnail error: " . $thumbRes['error'];
    } elseif ($thumbRes !== null) {
        $thumbnailToSave = $thumbRes;
    }

    $pdfRes = handlePdfUpload();
    $pdfToSave = $article['pdf_link'];
    if (is_array($pdfRes) && isset($pdfRes['error'])) {
        $errors[] = "PDF error: " . $pdfRes['error'];
    } elseif ($pdfRes !== null) {
        $pdfToSave = $pdfRes;
    }

    if ($title !== $article['title']) {
        $base_slug = slugify($title);
        $slug = unique_slug($pdo, 'current_affairs', 'slug', $base_slug, $id);
    } else {
        $slug = $article['slug'];
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE current_affairs SET title = ?, slug = ?, category = ?, description = ?, event_date = ?, thumbnail = ?, pdf_link = ?, meta_title = ?, meta_description = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $category, $description, $event_date, $thumbnailToSave, $pdfToSave, $meta_title, $meta_desc, $status, $id]);

        $success = "Article updated successfully!";

        // Refresh record
        $stmt = $pdo->prepare("SELECT * FROM current_affairs WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
    } else {
        $err = implode('<br>', $errors);
    }
}

$categories = ['National', 'International', 'Sports', 'Economy', 'Science & Tech', 'Appointments', 'Obituaries', 'Awards & Honours'];
?>

<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4 dark:text-white">Edit Current Affairs Article</h1>

    <?php if ($err): ?>
        <div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:border-red-400 dark:text-red-200">
            <?= $err ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="p-4 mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:border-green-400 dark:text-green-200">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Article Title *</label>
                <input required name="title" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?= e($_POST['title'] ?? $article['title']) ?>">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Category *</label>
                    <select name="category" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($_POST['category'] ?? $article['category']) === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Event Date *</label>
                    <input type="date" name="event_date" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?= e($_POST['event_date'] ?? $article['event_date']) ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Status</label>
                    <select name="status" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="published" <?= ($_POST['status'] ?? $article['status']) === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= ($_POST['status'] ?? $article['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Thumbnail Image</label>
                    <?php if (!empty($article['thumbnail'])): ?>
                        <img src="<?= e($article['thumbnail']) ?>" alt="" class="w-24 h-24 object-cover rounded mb-2">
                    <?php endif; ?>
                    <input type="file" name="thumbnail" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">PDF Document</label>
                    <?php if (!empty($article['pdf_link'])): ?>
                        <p class="text-xs text-blue-600 mb-2">Current PDF: <a href="<?= e($article['pdf_link']) ?>" target="_blank" class="underline">View PDF</a></p>
                    <?php endif; ?>
                    <input type="file" name="pdf_file" accept=".pdf" class="block w-full text-sm text-gray-500 dark:text-gray-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Content (Markdown) *</label>
                <textarea id="markdown-editor" name="description" rows="8" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"><?= e($_POST['description'] ?? $article['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Meta Title</label>
                <input name="meta_title" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?= e($_POST['meta_title'] ?? $article['meta_title']) ?>">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"><?= e($_POST['meta_description'] ?? $article['meta_description']) ?></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="/adminqeIUgwefgWEOAjx/current_affairs.php" class="px-4 py-2 bg-gray-300 rounded dark:bg-gray-600 dark:text-white">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
        </div>
    </form>
</div>

<script>
  const easyMDE = new EasyMDE({
    element: document.getElementById('markdown-editor'),
    spellChecker: false,
    autosave: { enabled: false },
    forceSync: true
  });
</script>

<?php require_once __DIR__ . '/../../.hta_slug/_footer.php'; ?>

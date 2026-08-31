<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();

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
    if ($file['size'] > 20 * 1024 * 1024) { // 20MB
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
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : date('Y-m-d');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');
    $status = $_POST['status'] ?? 'published';

    $base_slug = slugify($title);
    $slug = unique_slug($pdo, 'current_affairs', 'slug', $base_slug);
    $errors = [];

    if ($title === '') $errors[] = "Article title is required.";
    if ($description === '') $errors[] = "Article description is required.";

    $thumbRes = handleThumbnailUpload();
    $thumbnail = null;
    if (is_array($thumbRes) && isset($thumbRes['error'])) {
        $errors[] = "Thumbnail error: " . $thumbRes['error'];
    } else {
        $thumbnail = $thumbRes;
    }

    $pdfRes = handlePdfUpload();
    $pdf_link = null;
    if (is_array($pdfRes) && isset($pdfRes['error'])) {
        $errors[] = "PDF error: " . $pdfRes['error'];
    } else {
        $pdf_link = $pdfRes;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO current_affairs (title, slug, category, description, event_date, thumbnail, pdf_link, meta_title, meta_description, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $category, $description, $event_date, $thumbnail, $pdf_link, $meta_title, $meta_desc, $status]);

        $success = "Current Affairs article added successfully!";

        // Send Push Notification if published
        if ($status === 'published') {
            try {
                require_once __DIR__ . '/../../lib/FCMNotificationService.php';
                $fcmService = new FCMNotificationService($pdo);
                $notificationResult = $fcmService->sendToAll(
                    "Current Affairs: " . $title,
                    mb_substr(strip_tags($description), 0, 120),
                    [
                        'url' => BASE_URL . 'current-affairs/' . $slug,
                        'notification_type' => 'current_affairs',
                        'slug' => $slug
                    ]
                );
                if ($notificationResult['success']) {
                    $success .= ' Push notifications sent to ' . $notificationResult['sent_count'] . ' subscribers.';
                }
            } catch (\Throwable $e) {
                error_log("Error sending FCM notification for current affairs: " . $e->getMessage());
            }
        }
    } else {
        $err = implode('<br>', $errors);
    }
}

$categories = ['National', 'International', 'Sports', 'Economy', 'Science & Tech', 'Appointments', 'Obituaries', 'Awards & Honours'];
?>

<!-- EasyMDE -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<style>
.ca-wrapper {
  color: #f8fafc;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.ca-container {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.ca-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.ca-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff !important;
  margin: 0;
}

.ca-card {
  background-color: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.875rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.ca-form-group {
  margin-bottom: 1.25rem;
}

.ca-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #cbd5e1 !important;
  margin-bottom: 0.375rem;
}

.ca-input, .ca-select, .ca-textarea {
  width: 100%;
  padding: 0.65rem 0.875rem;
  background-color: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  color: #ffffff !important;
  font-size: 0.875rem;
  outline: none;
  box-sizing: border-box;
}

.ca-input:focus, .ca-select:focus, .ca-textarea:focus {
  border-color: #0d9488;
}

.ca-grid-3 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .ca-grid-3 { grid-template-columns: repeat(3, 1fr); }
}

.ca-grid-2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .ca-grid-2 { grid-template-columns: repeat(2, 1fr); }
}

.ca-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.65rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 0.5rem;
  text-decoration: none !important;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
}

.ca-btn-teal { background-color: #0d9488; color: #ffffff !important; }
.ca-btn-teal:hover { background-color: #0f766e; }

.ca-btn-outline {
  background-color: transparent;
  color: #cbd5e1 !important;
  border: 1px solid #334155;
}
.ca-btn-outline:hover { background-color: #334155; color: #ffffff !important; }

.ca-alert-error {
  padding: 1rem;
  margin-bottom: 1.5rem;
  background-color: rgba(239, 68, 68, 0.15);
  border-left: 4px solid #ef4444;
  color: #fca5a5;
  border-radius: 0.375rem;
}

.ca-alert-success {
  padding: 1rem;
  margin-bottom: 1.5rem;
  background-color: rgba(16, 185, 129, 0.15);
  border-left: 4px solid #10b981;
  color: #6ee7b7;
  border-radius: 0.375rem;
}

.EasyMDEContainer {
  background-color: #0f172a;
  border-radius: 0.5rem;
  overflow: hidden;
}

.EasyMDEContainer .CodeMirror {
  background-color: #0f172a;
  color: #ffffff;
  border-color: #334155;
}

.EasyMDEContainer .editor-toolbar {
  background-color: #1e293b;
  border-color: #334155;
}

.EasyMDEContainer .editor-toolbar button {
  color: #cbd5e1 !important;
}

.EasyMDEContainer .editor-toolbar button.active,
.EasyMDEContainer .editor-toolbar button:hover {
  background-color: #334155 !important;
  color: #ffffff !important;
}
</style>

<div class="ca-wrapper">
  <div class="ca-container">
    
    <div class="ca-header-bar">
      <h1 class="ca-title">Add New Current Affairs Article</h1>
      <a href="/adminqeIUgwefgWEOAjx/current_affairs" class="ca-btn ca-btn-outline">&larr; Back to List</a>
    </div>

    <?php if ($err): ?>
        <div class="ca-alert-error">
            <?= $err ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="ca-alert-success">
            <?= $success ?>
            <div style="margin-top: 0.75rem; display: flex; gap: 0.75rem;">
              <a href="/adminqeIUgwefgWEOAjx/current_affairs" class="ca-btn ca-btn-teal" style="font-size:0.75rem; padding:0.35rem 0.75rem;">Manage All Articles</a>
              <a href="/adminqeIUgwefgWEOAjx/add_current_affairs" class="ca-btn ca-btn-outline" style="font-size:0.75rem; padding:0.35rem 0.75rem;">Add Another Article</a>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="ca-card">
            <div class="ca-form-group">
                <label class="ca-label">Article Title *</label>
                <input required name="title" class="ca-input" value="<?= e($_POST['title'] ?? '') ?>" placeholder="e.g., India successfully launches new satellite">
            </div>

            <div class="ca-form-group ca-grid-3">
                <div>
                    <label class="ca-label">Category *</label>
                    <select name="category" class="ca-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="ca-label">Event Date *</label>
                    <input type="date" name="event_date" class="ca-input" value="<?= e($_POST['event_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div>
                    <label class="ca-label">Status</label>
                    <select name="status" class="ca-select">
                        <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>

            <div class="ca-form-group ca-grid-2">
                <div>
                    <label class="ca-label">Thumbnail Image (Optional)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="ca-input">
                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Recommended size: 600x400px (Max 5MB)</p>
                </div>
                <div>
                    <label class="ca-label">PDF Document (Optional)</label>
                    <input type="file" name="pdf_file" accept=".pdf" class="ca-input">
                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">PDF compilation / notes download (Max 20MB)</p>
                </div>
            </div>

            <div class="ca-form-group">
                <label class="ca-label">Content (Markdown) *</label>
                <textarea id="markdown-editor" name="description" rows="8"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="ca-form-group">
                <label class="ca-label">Meta Title (SEO)</label>
                <input name="meta_title" class="ca-input" value="<?= e($_POST['meta_title'] ?? '') ?>" placeholder="SEO title tag">
            </div>

            <div class="ca-form-group">
                <label class="ca-label">Meta Description (SEO)</label>
                <textarea name="meta_description" rows="3" class="ca-textarea" placeholder="SEO meta description summary"><?= e($_POST['meta_description'] ?? '') ?></textarea>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="/adminqeIUgwefgWEOAjx/current_affairs" class="ca-btn ca-btn-outline">Cancel</a>
            <button type="submit" class="ca-btn ca-btn-teal">Save Article</button>
        </div>
    </form>
  </div>
</div>

<script>
  const easyMDE = new EasyMDE({
    element: document.getElementById('markdown-editor'),
    spellChecker: false,
    autosave: { enabled: false },
    placeholder: "Write current affairs details in Markdown...",
    forceSync: true
  });
</script>

<?php require_once __DIR__ . '/../../.hta_slug/_footer.php'; ?>

<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();
$err = '';
$success = ''; 

// Thumbnail Upload Handler (same as Add Job)
function handleThumbnailUpload() {
    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No new thumbnail uploaded
    }

    $uploadDir = __DIR__ . '/../../thumbnails/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

    $file = $_FILES['thumbnail'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => "Image upload failed. Error Code: " . $file['error']];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ['error' => 'Image exceeds 2MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        return ['error' => 'Only JPG, PNG, WEBP formats allowed.'];
    }

    $filename = uniqid() . "." . $ext;
    $destination = $uploadDir . $filename;

    if (compressImage($file['tmp_name'], $destination, 80, 600, 400)) {
        return '/thumbnails/' . $filename;
    }

    return ['error' => 'Failed to save the compressed image.'];
}

// ----------------------
// Fetch update by ID
// ----------------------
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: /admin/updates');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
$stmt->execute([$id]);
$update = $stmt->fetch();

if (!$update) {
    header('Location: /admin/updates');
    exit;
}

// ----------------------------------
// FORM SUBMISSION
// ----------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title'] ?? '');
    $update_type = $_POST['update_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');
    $errors = [];

    // Validation
    if ($title === '') $errors[] = "Title is required.";
    if ($update_type === '') $errors[] = "Update type is required.";
    if ($description === '') $errors[] = "Description is required.";

    if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid URL format.";
    }

    // Thumbnail upload handler
    $thumbnailResult = handleThumbnailUpload();
    $uploadError = '';
    $newThumbnail = null;

    if (is_array($thumbnailResult) && isset($thumbnailResult['error'])) {
        $uploadError = $thumbnailResult['error'];
        $errors[] = "Thumbnail error: " . $uploadError;
    } else {
        $newThumbnail = $thumbnailResult; // new image path OR null
    }

    // Title change → generate new slug
    if ($title !== $update['title']) {
        $base_slug = slugify($title);
        $slug = unique_slug($pdo, 'updates', 'slug', $base_slug, $id);
    } else {
        $slug = $update['slug'];
    }

    if (empty($errors)) {

        // If user uploaded new thumbnail → remove old one
        $thumbnailToSave = $update['thumbnail'];

        if ($newThumbnail !== null) {
            $thumbnailToSave = $newThumbnail;

            // delete old file
            if (!empty($update['thumbnail'])) {
                $oldFile = __DIR__ . '/../../' . ltrim($update['thumbnail'], '/');
                if (file_exists($oldFile)) unlink($oldFile);
            }
        }

        // Update the record
        $stmt = $pdo->prepare("
            UPDATE updates 
            SET title = ?, slug = ?, update_type = ?, description = ?, link = ?, meta_title = ?, meta_description = ?, thumbnail = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title,
            $slug,
            $update_type,
            $description,
            $link,
            $meta_title,
            $meta_desc,
            $thumbnailToSave,
            $id
        ]);

        $success = "Update saved successfully!";

        // reload updated data
        $stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
        $stmt->execute([$id]);
        $update = $stmt->fetch();
    } else {
        $err = implode("<br>", $errors);
    }
}
?>

<!-- EasyMDE -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<h1 class="text-2xl font-bold mb-4 dark:text-white">Edit Update</h1>

<?php if ($err): ?>
<div class="p-4 mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:border-red-400 dark:text-red-200">
  <?= $err ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="p-4 mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:border-green-400 dark:text-green-200">
  <?= $success ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="space-y-6">

<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border dark:border-gray-700">
    
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Update Information</h2>

    <!-- Title -->
    <div>
        <label class="block mb-1 dark:text-gray-300">Title *</label>
        <input name="title" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
               value="<?= e($_POST['title'] ?? $update['title']) ?>">
    </div>

    <!-- Update Type -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1 dark:text-gray-300">Update Type *</label>
            <select name="update_type" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">-- Select --</option>
                <option value="exam" <?= ($update['update_type'] == 'exam') ? 'selected' : '' ?>>Exam</option>
                <option value="ans_key" <?= ($update['update_type'] == 'ans_key') ? 'selected' : '' ?>>Answer Key</option>
                <option value="result" <?= ($update['update_type'] == 'result') ? 'selected' : '' ?>>Result</option>
                <option value="syllabus" <?= ($update['update_type'] == 'syllabus') ? 'selected' : '' ?>>Syllabus</option>
            </select>
        </div>

        <!-- Link -->
        <div>
            <label class="block mb-1 dark:text-gray-300">Official Link</label>
            <input name="link" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                   value="<?= e($_POST['link'] ?? $update['link']) ?>">
        </div>
    </div>

    <!-- Thumbnail Upload -->
    <div class="mt-4">
        <label class="block mb-1 dark:text-gray-300">Thumbnail Image</label>

        <?php if (!empty($update['thumbnail'])): ?>
            <p class="text-sm dark:text-gray-300 mb-2">Current Thumbnail:</p>
            <img src="<?= $update['thumbnail'] ?>" alt="" class="w-40 rounded shadow mb-3">
        <?php endif; ?>

        <input type="file" name="thumbnail" accept="image/*"
               class="block w-full text-sm text-gray-500 dark:text-gray-300">
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Max 2MB, JPG/PNG/WEBP — Uploading a new one will replace the old image.
        </p>
    </div>

    <!-- Description -->
    <div class="mt-4">
        <label class="block mb-1 dark:text-gray-300">Description *</label>
        <textarea id="markdown-editor" name="description" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  rows="6"><?= e($_POST['description'] ?? $update['description']) ?></textarea>
    </div>

    <!-- Meta Title + Description -->
    <div class="mt-4">
        <label class="block mb-1 dark:text-gray-300">Meta Title</label>
        <input name="meta_title" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
               value="<?= e($_POST['meta_title'] ?? $update['meta_title']) ?>">
    </div>

    <div class="mt-4">
        <label class="block mb-1 dark:text-gray-300">Meta Description</label>
        <textarea name="meta_description" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  rows="4"><?= e($_POST['meta_description'] ?? $update['meta_description']) ?></textarea>
    </div>

</div>

<div class="flex justify-end gap-3">
    <a href="/admin/updates" class="px-4 py-2 bg-gray-300 rounded dark:bg-gray-600 dark:text-white">Cancel</a>
    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-700">Save Changes</button>
</div>

</form>

<script>
  // Initialize EasyMDE editor with custom toolbar
  const easyMDE = new EasyMDE({
    element: document.getElementById('markdown-editor'),
    spellChecker: false,
    autosave: { enabled: false },
    toolbar: [
      "bold", "italic", "heading", "|", 
      "quote", "unordered-list", "ordered-list", "|", 
      "link", "image", "|", 
      {
        name: "table",
        action: function(editor) {
          const cm = editor.codemirror;
          const selectedText = cm.getSelection();
          let tableMarkdown;
          if (selectedText) {
            const columns = selectedText.split('|').map(col => col.trim());
            const header = `| ${columns.join(' | ')} |\n`;
            const separator = `| ${columns.map(() => '---').join(' | ')} |\n`;
            const row = `| ${columns.map(() => 'Cell').join(' | ')} |\n`;
            tableMarkdown = `\n${header}${separator}${row}`;
          } else {
            tableMarkdown = '\n| Column 1 | Column 2 | Column 3 |\n|----------|----------|----------|\n| Cell 1   | Cell 2   | Cell 3   |\n';
          }
          cm.replaceSelection(tableMarkdown);
        },
        className: "fa fa-table",
        title: "Insert Table",
      },
      {
        name: "underline",
        action: function(editor) {
          const cm = editor.codemirror;
          const selectedText = cm.getSelection();
          cm.replaceSelection(selectedText ? `<u>${selectedText}</u>` : '<u>underlined text</u>');
        },
        className: "fa fa-underline",
        title: "Underline",
      },
      {
        name: "highlight",
        action: function(editor) {
          const cm = editor.codemirror;
          const selectedText = cm.getSelection();
          cm.replaceSelection(selectedText ? `<mark>${selectedText}</mark>` : '<mark>highlighted text</mark>');
        },
        className: "fa fa-highlight",
        title: "Highlight",
      },
      "|", "preview", "side-by-side", "fullscreen", "|", "guide"
    ],
    renderingConfig: {
      singleLineBreaks: false,
      codeSyntaxHighlighting: true,
      markedOptions: { sanitize: false, breaks: true, gfm: true }
    },
    placeholder: "Provide detailed information about the update...",
    forceSync: true,
  });

  // Custom line-break handling
  const cm = easyMDE.codemirror;

  // Desktop: Shift+Enter inserts <br>
  cm.addKeyMap({
    "Shift-Enter": function(cm) {
      cm.replaceSelection("<br>\n");
    }
  });

  // Mobile: Enter inserts <br>
  cm.on("keydown", function(editor, event) {
    if (event.key === "Enter" && !event.shiftKey) {
      if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        event.preventDefault();
        editor.replaceSelection("<br>\n");
      }
    }
  });

  // Preview styling
  const style = document.createElement('style');
  style.textContent = `
    .editor-preview mark { background-color: #ffeb3b; padding: 0.2em; color: extreme; }
    .editor-preview u { text-decoration: underline; }
    .fa-highlight:before { content: "H"; font-weight: bold; padding: 0 3px; }
    .fa-underline:before { content: "U"; text-decoration: underline; }
  `;
  document.head.appendChild(style);
</script>
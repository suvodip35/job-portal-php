<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();
$err = '';
$success = '';

/* ------------------------------------------
   THUMBNAIL UPLOAD HANDLER (ADDED)
------------------------------------------ */
function handleThumbnailUpload() {
    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // optional thumbnail
    }

    $uploadDir = __DIR__ . '/../../thumbnails/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

    $file = $_FILES['thumbnail'];

    // Upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'File size too large. Max 2MB allowed.'];
        }
        return ['error' => 'File upload error: ' . $file['error']];
    }

    // Size check (2MB max)
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['error' => 'Thumbnail exceeds 2MB limit.'];
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        return ['error' => 'Invalid file type. Allowed: JPG, JPEG, PNG, WEBP'];
    }

    // Unique filename
    $filename = uniqid() . "." . $ext;
    $destination = $uploadDir . $filename;

    // Compress & resize
    if (compressImage($file['tmp_name'], $destination, 80, 600, 400)) {
        return '/thumbnails/' . $filename;
    }

    return ['error' => 'Failed to save the thumbnail file.'];
}

/* ------------------------------------------
   FORM SUBMISSION
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title'] ?? '');
    $update_type = $_POST['update_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');

    $base_slug = slugify($title);
    $slug = unique_slug($pdo, 'updates', 'slug', $base_slug);

    $errors = [];

    // Validation
    if ($title === '') $errors[] = "Title is required.";
    if ($update_type === '') $errors[] = "Update type is required.";
    if ($description === '') $errors[] = "Description is required.";

    if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid URL format.";
    }

    // Thumbnail Upload
    $thumbnailResult = handleThumbnailUpload();
    $thumbnail = null;
    $uploadError = '';

    if (is_array($thumbnailResult) && isset($thumbnailResult['error'])) {
        $uploadError = $thumbnailResult['error'];
        $errors[] = "Thumbnail error: " . $uploadError;
    } else {
        $thumbnail = $thumbnailResult;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO updates (title, slug, update_type, description, link, meta_title, meta_description, thumbnail) VALUES (?,?,?,?,?,?,?,?)");

        $stmt->execute([ $title, $slug, $update_type, $description, $link, $meta_title, $meta_desc, $thumbnail ]);

        $success = "Update added successfully!";
    } else {
        $err = implode("<br>", $errors);
    }
}
?>

<!-- Add EasyMDE CSS -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<h1 class="container mx-auto text-2xl font-bold mb-4 dark:text-white">Add New Update</h1>

<?php if ($err): ?>
<div class="container mx-auto p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r dark:bg-red-900/30 dark:border-red-400 dark:text-red-200">
    <p class="font-medium">Please fix the following issues:</p>
    <div class="mt-1 text-sm"><?= $err ?></div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="container mx-auto p-4 mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r dark:bg-green-900/30 dark:border-green-400 dark:text-green-200">
    <p class="font-medium">Success!</p>
    <div class="mt-1 text-sm"><?= $success ?></div>
</div>
<?php endif; ?>

<form method="post" class="space-y-6 container mx-auto" enctype="multipart/form-data" accept-charset="UTF-8">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Update Information</h2>

    <!-- Title -->
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300">
            Title <span class="text-red-500">*</span>
        </label>
        <input name="title" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?= e($_POST['title'] ?? '') ?>" placeholder="Enter update title">
    </div>

    <!-- Update Type -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium dark:text-gray-300">Update Type *</label>
            <select name="update_type" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">-- Select Type --</option>
                <option value="exam">Exam</option>
                <option value="ans_key">Answer Key</option>
                <option value="result">Result</option>
                <option value="syllabus">Syllabus</option>
            </select>
        </div>

        <!-- Official Link -->
        <div>
            <label class="block text-sm font-medium dark:text-gray-300">Official Link (Optional)</label>
            <input name="link" type="url" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?= e($_POST['link'] ?? '') ?>" placeholder="https://...">
        </div>
    </div>

    <!-- Meta Title -->
    <div class="mb-4">
        <label class="block text-sm font-medium dark:text-gray-300">Meta Title</label>
        <input name="meta_title" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?= e($_POST['meta_title'] ?? '') ?>">
    </div>

    <!-- Meta Description -->
    <div class="mb-4">
        <label class="block text-sm font-medium dark:text-gray-300">Meta Description</label>
        <textarea name="meta_description" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="3"><?= e($_POST['meta_description'] ?? '') ?></textarea>
    </div>

    <!-- Thumbnail Upload -->
    <div class="mb-4">
        <label class="block text-sm font-medium dark:text-gray-300">Thumbnail Image (Optional)</label>
        <input type="file" name="thumbnail" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-200">
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 2MB • JPG, PNG, WEBP • Recommended size: 600×400</p>
    </div>

    <!-- Description Editor -->
    <div class="mb-4">
        <label class="block text-sm font-medium dark:text-gray-300">Description *</label>
        <textarea id="markdown-editor" name="description" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="6"><?= e($_POST['description'] ?? '') ?></textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports Markdown</p>
    </div>
  </div>

  <div class="flex justify-end gap-3">
    <button type="reset" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Reset</button>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Update</button>
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
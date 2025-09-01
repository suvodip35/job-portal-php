<?php
// require_once __DIR__ . '/../functions.php';
// require_once __DIR__ . '/../../.hta_config/functions.php';

require_admin();
$err = '';
$success = '';

// Handle file upload
function handleThumbnailUpload() {
    if (!isset($_FILES['thumbnail'])) return null;
    
    $uploadDir = __DIR__ . '/../../thumbnails/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $file = $_FILES['thumbnail'];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array(strtolower($ext), $allowed)) return null;
    
    $filename = uniqid() . '.' . $ext;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return '/thumbnails/' . $filename;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $title = trim($_POST['job_title'] ?? '');
    $company = trim($_POST['company_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $category_slug = (int)($_POST['category_slug'] ?? 0);
    $job_type = $_POST['job_type'] ?? 'full-time';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');
    $apply_url = trim($_POST['apply_url'] ?? '');
    $last_date = $_POST['last_date'] ?? null;
    $status = $_POST['status'] ?? 'published';
    $min_salary = (int)($_POST['min_salary'] ?? 0);
    $max_salary = (int)($_POST['max_salary'] ?? 0);
    $document_link = trim($_POST['document_link'] ?? '');
    $createdBy = 'Suvo';
    $thumbnail = handleThumbnailUpload();

    if ($title === '' || $company === '') {
        $err = 'Title and Company are required.';
    } else {
        $base_slug = slugify($title);
        $slug = unique_slug($pdo, 'jobs', 'job_title_slug', $base_slug);
        $stmt = $pdo->prepare("INSERT INTO jobs (category_slug, job_title, job_title_slug, meta_title, meta_description, company_name, location, description, requirements, job_type, apply_url, last_date, status, min_salary, max_salary, document_link, created_by, thumbnail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$category_slug, $title, $slug, $meta_title, $meta_desc, $company, $location, $description, $requirements, $job_type, $apply_url, $last_date, $status, $min_salary, $max_salary, $document_link, $createdBy, $thumbnail]);
        $success = 'Job added successfully.';
    }
}

// fetch categories for select
$cats = $pdo->query("SELECT * FROM job_categories ORDER BY category_name ASC")->fetchAll();
?>

<!-- Add EasyMDE CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<h1 class="text-2xl font-bold mb-4 dark:text-white">Add Job</h1>
<?php if ($err): ?><div class="p-2 bg-red-100 text-red-800 mb-3 rounded dark:bg-red-900 dark:text-red-200"><?= e($err) ?></div><?php endif; ?>
<?php if ($success): ?><div class="p-2 bg-green-100 text-green-800 mb-3 rounded dark:bg-green-900 dark:text-green-200"><?= e($success) ?></div><?php endif; ?>

<form method="post" class="space-y-4" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  
  <div>
    <label class="block dark:text-gray-300">Job Title
      <input required name="job_title" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['job_title'] ?? '') ?>">
    </label>
  </div>
  
  <div class="grid grid-cols-2 gap-4">
    <label class="dark:text-gray-300">Company Name
      <input name="company_name" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['company_name'] ?? '') ?>">
    </label>
    <label class="dark:text-gray-300">Location
      <select name="location" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        <option value="">-- Select State --</option>
        <?php foreach ($indianStates as $slug => $name): ?>
          <option value="<?= htmlspecialchars($slug) ?>" <?= (($_POST['location'] ?? '') == $slug) ? 'selected' : '' ?>>
            <?= htmlspecialchars($name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  
  <div class="grid grid-cols-3 gap-4">
    <label class="dark:text-gray-300">Category
      <select name="category_slug" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        <option value="0">Select</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= e($c['category_slug']) ?>" <?= (($_POST['category_slug'] ?? 0) == $c['category_slug']) ? 'selected' : '' ?>><?= e($c['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="dark:text-gray-300">Minimum Salary ($)
      <input type="number" name="min_salary" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['min_salary'] ?? '') ?>" min="0">
    </label>
    <label class="dark:text-gray-300">Maximum Salary ($)
      <input type="number" name="max_salary" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['max_salary'] ?? '') ?>" min="0">
    </label>
  </div>
  
  <div>
    <label class="dark:text-gray-300">Description (Markdown supported)
      <textarea id="markdown-editor" name="description" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" rows="6"><?= e($_POST['description'] ?? '') ?></textarea>
    </label>
  </div>
  
  <div>
    <label class="dark:text-gray-300">Requirements
      <textarea name="requirements" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" rows="4"><?= e($_POST['requirements'] ?? '') ?></textarea>
    </label>
  </div>
  
  <div class="grid grid-cols-3 gap-4">
    <label class="dark:text-gray-300">Apply URL
      <input name="apply_url" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['apply_url'] ?? '') ?>">
    </label>
    <label class="dark:text-gray-300">Document Link (PDF, DOC, etc.)
      <input name="document_link" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['document_link'] ?? '') ?>">
    </label>
    <label class="dark:text-gray-300">Last Date
      <input type="date" name="last_date" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['last_date'] ?? '') ?>">
    </label>
  </div>
  
  <div class="grid grid-cols-3 gap-4">
    <label class="dark:text-gray-300">Job Type
      <select name="job_type" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        <option value="full-time" <?= (($_POST['job_type'] ?? 'full-time') == 'full-time') ? 'selected' : '' ?>>Full-time</option>
        <option value="part-time" <?= (($_POST['job_type'] ?? '') == 'part-time') ? 'selected' : '' ?>>Part-time</option>
        <option value="contract" <?= (($_POST['job_type'] ?? '') == 'contract') ? 'selected' : '' ?>>Contract</option>
        <option value="internship" <?= (($_POST['job_type'] ?? '') == 'internship') ? 'selected' : '' ?>>Internship</option>
      </select>
    </label>
    <label class="dark:text-gray-300">Status
      <select name="status" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        <option value="published" <?= (($_POST['status'] ?? 'published') == 'published') ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= (($_POST['status'] ?? '') == 'draft') ? 'selected' : '' ?>>Draft</option>
        <option value="closed" <?= (($_POST['status'] ?? '') == 'closed') ? 'selected' : '' ?>>Closed</option>
      </select>
    </label>
    <label class="dark:text-gray-300">Meta Title
      <input name="meta_title" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" value="<?= e($_POST['meta_title'] ?? '') ?>">
    </label>
  </div>
  
  <div>
    <label class="dark:text-gray-300">Meta Description
      <textarea name="meta_description" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" rows="2"><?= e($_POST['meta_description'] ?? '') ?></textarea>
    </label>
  </div>

  <!-- Thumbnail Upload -->
  <div class="space-y-2">
    <label class="block dark:text-gray-300">Thumbnail Image</label>
    <div class="flex items-center gap-4">
      <label class="flex-1">
        <input type="file" name="thumbnail" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400
          file:mr-4 file:py-2 file:px-4
          file:rounded-md file:border-0
          file:text-sm file:font-semibold
          file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-200
          hover:file:bg-blue-100 dark:hover=file:bg-blue-800">
      </label>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400">Max 2MB (JPG, PNG, WEBP)</p>
  </div>
  
  <div class="flex justify-end">
    <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800">Add Job</button>
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
    placeholder: "Write job description here...",
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
    .editor-preview mark { background-color: #ffeb3b; padding: 0.2em; color: #000; }
    .editor-preview u { text-decoration: underline; }
    .fa-highlight:before { content: "H"; font-weight: bold; padding: 0 3px; }
    .fa-underline:before { content: "U"; text-decoration: underline; }
  `;
  document.head.appendChild(style);
</script>


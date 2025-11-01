<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();
$err = '';
$success = ''; 

// Get the update ID from the URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: /admin/updates');
    exit;
}

// Fetch the existing update
$stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
$stmt->execute([$id]);
$update = $stmt->fetch();

if (!$update) {
    header('Location: /admin/updates');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $update_type = $_POST['update_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $errors = [];
    
    if ($title === '') {
        $errors[] = "Title is required.";
    }
    
    if ($update_type === '') {
        $errors[] = "Update type is required.";
    }
    
    if ($description === '') {
        $errors[] = "Description is required.";
    }
    
    if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid URL format for the link.";
    }

    if (empty($errors)) {
        // Only generate a new slug if the title has changed
        if ($title !== $update['title']) {
            $base_slug = slugify($title);
            $slug = unique_slug($pdo, 'updates', 'slug', $base_slug, $id);
        } else {
            $slug = $update['slug'];
        }
        
        $stmt = $pdo->prepare("UPDATE updates SET title = ?, slug = ?, update_type = ?, description = ?, link = ?, meta_title = ?, meta_description = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $update_type, $description, $link, $_POST['meta_title'], $_POST['meta_description'], $id]);
        $success = "Update updated successfully!";
        
        // Refresh the update data
        $stmt = $pdo->prepare("SELECT * FROM updates WHERE id = ?");
        $stmt->execute([$id]);
        $update = $stmt->fetch();
    } else {
        $err = implode('<br>', $errors);
    }
}
?>

<!-- Add EasyMDE CSS -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<h1 class="text-2xl font-bold mb-4 dark:text-white">Edit Update</h1>

<?php if ($err): ?>
<div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r dark:bg-red-900/30 dark:border-red-400 dark:text-red-200">
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <p class="font-medium">Please fix the following issues:</p>
            <div class="mt-1 text-sm"><?= $err ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="p-4 mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r dark:bg-green-900/30 dark:border-green-400 dark:text-green-200">
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <p class="font-medium">Success!</p>
            <div class="mt-1 text-sm"><?= $success ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<form method="post" class="space-y-6" accept-charset="UTF-8">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Update Information</h2>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="title">
            Title <span class="text-red-500">*</span>
        </label>
        <input required name="title" id="title" 
               class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white 
                      <?= (isset($_POST['title']) && trim($_POST['title']) === '') ? 'border-red-500' : '' ?>"
               value="<?= e($_POST['title'] ?? $update['title']) ?>" 
               placeholder="e.g., UPSC Civil Services Preliminary Exam 2023 Notification">
        <?php if (isset($_POST['title']) && trim($_POST['title']) === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a title</p>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="update_type">
                Update Type <span class="text-red-500">*</span>
            </label>
            <select name="update_type" id="update_type" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                           <?= (!isset($_POST['update_type']) || ($_POST['update_type'] ?? '') === '') ? 'border-red-500' : '' ?>" required>
                <option value="">-- Select Type --</option>
                <option value="exam" <?= (($_POST['update_type'] ?? $update['update_type']) == 'exam') ? 'selected' : '' ?>>Exam</option>
                <option value="ans_key" <?= (($_POST['update_type'] ?? $update['update_type']) == 'ans_key') ? 'selected' : '' ?>>Answer Key</option>
                <option value="result" <?= (($_POST['update_type'] ?? $update['update_type']) == 'result') ? 'selected' : '' ?>>Result</option>
                <option value="syllabus" <?= (($_POST['update_type'] ?? $update['update_type']) == 'syllabus') ? 'selected' : '' ?>>Syllabus</option>
            </select>
            <?php if (!isset($_POST['update_type']) || ($_POST['update_type'] ?? '') === ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please select an update type</p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="link">
                Official Link (Optional)
            </label>
            <input type="url" name="link" id="link" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['link']) && !filter_var($_POST['link'], FILTER_VALIDATE_URL) && $_POST['link'] !== '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['link'] ?? $update['link']) ?>" 
                   placeholder="https://...">
            <?php if (isset($_POST['link']) && !filter_var($_POST['link'], FILTER_VALIDATE_URL) && $_POST['link'] !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please enter a valid URL</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="markdown-editor">
            Description (Markdown supported) <span class="text-red-500">*</span>
        </label>
        <textarea id="markdown-editor" name="description" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                         <?= (!isset($_POST['description']) || trim($_POST['description'] ?? '') === '') ? 'border-red-500' : '' ?>" 
                  rows="6" placeholder="Provide detailed information about the update..."><?= e($_POST['description'] ?? $update['description']) ?></textarea>
        <?php if (!isset($_POST['description']) || trim($_POST['description'] ?? '') === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a description</p>
        <?php endif; ?>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports Markdown formatting</p>
    </div>
    <div class="mb-4">
      <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_title">
          Meta Title
      </label>
      <input type="text" name="meta_title" id="meta_title" 
            class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                    <?= (isset($_POST['meta_title']) && trim($_POST['meta_title']) === '') ? 'border-red-500' : '' ?>"
            value="<?= e($_POST['meta_title'] ?? $update['meta_title']) ?>" 
            placeholder="Enter meta title here...">
      <?php if (isset($_POST['meta_title']) && trim($_POST['meta_title']) === ''): ?>
      <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a meta title</p>
        <?php endif; ?>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_description">
            Meta Description
        </label>
        <textarea name="meta_description" id="meta_description" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                        <?= (!isset($_POST['meta_description']) || trim($_POST['meta_description'] ?? '') === '') ? 'border-red-500' : '' ?>" 
                  rows="6" placeholder="Enter meta description here..."><?= e($_POST['meta_description'] ?? $update['meta_description']) ?></textarea>
        <?php if (!isset($_POST['meta_description']) || trim($_POST['meta_description'] ?? '') === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a meta description</p>
        <?php endif; ?>
    </div>    
  </div>
  
  <div class="flex justify-end gap-3">
    <a href="/adminqeIUgwefgWEOAjx/updates" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
        Cancel
    </a>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        Update Changes
    </button>
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
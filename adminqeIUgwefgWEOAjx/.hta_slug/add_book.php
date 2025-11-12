<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();
$err = '';
$success = ''; 

// Define upload directory
$uploadDir = __DIR__ . '/../../book-image/';
// Ensure the directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $book_type = $_POST['book_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $amazon_link = trim($_POST['amazon_link'] ?? '');
    $flipkart_link = trim($_POST['flipkart_link'] ?? '');
    $base_slug = slugify($title);
    $slug = unique_slug($pdo, 'books', 'slug', $base_slug);
    $errors = [];
    
    // Image upload handling
    $book_image = '';
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_type = mime_content_type($_FILES['book_image']['tmp_name']);
        $file_size = $_FILES['book_image']['size'];
        
        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, JPEG, PNG, WebP, and GIF images are allowed.";
        }
        
        // Validate file size
        if ($file_size > $max_size) {
            $errors[] = "Image size must be less than 5MB.";
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $file_extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
            $filename = $slug . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_path)) {
                $book_image = '/book-image/' . $filename;
            } else {
                $errors[] = "Failed to upload image. Please try again.";
            }
        }
    }
    
    if ($title === '') {
        $errors[] = "Book title is required.";
    }
    
    if ($book_type === '') {
        $errors[] = "Book type/category is required.";
    }
    
    if ($description === '') {
        $errors[] = "Book description is required.";
    }
    
    if ($author === '') {
        $errors[] = "Author name is required.";
    }
    
    if (!empty($amazon_link) && !filter_var($amazon_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid URL format for Amazon link.";
    }
    
    if (!empty($flipkart_link) && !filter_var($flipkart_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid URL format for Flipkart link.";
    }
    
    if (!empty($publication_year) && !preg_match('/^\d{4}$/', $publication_year)) {
        $errors[] = "Publication year must be a valid 4-digit year.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO books (title, slug, book_type, description, author, publisher, publication_year, isbn, amazon_link, flipkart_link, book_image, meta_title, meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $book_type, $description, $author, $publisher, $publication_year, $isbn, $amazon_link, $flipkart_link, $book_image, $_POST['meta_title'], $_POST['meta_description']]);
        $success = "Book added successfully!";
    } else {
        $err = implode('<br>', $errors);
    }
}
?>

<!-- Add EasyMDE CSS -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<h1 class="container mx-auto text-2xl font-bold mb-4 dark:text-white">Add New Book</h1>

<?php if ($err): ?>
<div class="container mx-auto p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r dark:bg-red-900/30 dark:border-red-400 dark:text-red-200">
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
<div class="container mx-auto p-4 mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r dark:bg-green-900/30 dark:border-green-400 dark:text-green-200">
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

<form method="post" class="space-y-6 container mx-auto" accept-charset="UTF-8" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Book Information</h2>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="title">
            Book Title <span class="text-red-500">*</span>
        </label>
        <input required name="title" id="title" 
               class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white 
                      <?= (isset($_POST['title']) && trim($_POST['title']) === '') ? 'border-red-500' : '' ?>"
               value="<?= e($_POST['title'] ?? '') ?>" 
               placeholder="e.g., Indian Polity for Civil Services Examination">
        <?php if (isset($_POST['title']) && trim($_POST['title']) === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a book title</p>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="book_type">
                Book Category <span class="text-red-500">*</span>
            </label>
            <select name="book_type" id="book_type" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                        <?= (!isset($_POST['book_type']) || $_POST['book_type'] === '') ? 'border-red-500' : '' ?>" required>
                <option value="">-- Select Category --</option>
                <option value="bank-jobs" <?= (($_POST['book_type'] ?? '') == 'bank-jobs') ? 'selected' : '' ?>>Bank Jobs</option>
                <option value="railway-jobs" <?= (($_POST['book_type'] ?? '') == 'railway-jobs') ? 'selected' : '' ?>>Railway Jobs</option>
                <option value="iti-jobs" <?= (($_POST['book_type'] ?? '') == 'iti-jobs') ? 'selected' : '' ?>>ITI Jobs</option>
                <option value="police-jobs" <?= (($_POST['book_type'] ?? '') == 'police-jobs') ? 'selected' : '' ?>>Police Jobs</option>
                <option value="army-jobs" <?= (($_POST['book_type'] ?? '') == 'army-jobs') ? 'selected' : '' ?>>Army Jobs</option>
                <option value="teaching-jobs" <?= (($_POST['book_type'] ?? '') == 'teaching-jobs') ? 'selected' : '' ?>>Teaching Jobs</option>
                <option value="defence-jobs" <?= (($_POST['book_type'] ?? '') == 'defence-jobs') ? 'selected' : '' ?>>Defence Jobs</option>
                <option value="engineering-jobs" <?= (($_POST['book_type'] ?? '') == 'engineering-jobs') ? 'selected' : '' ?>>Engineering Jobs</option>
                <option value="medical-jobs" <?= (($_POST['book_type'] ?? '') == 'medical-jobs') ? 'selected' : '' ?>>Medical Jobs</option>
                <option value="government-jobs" <?= (($_POST['book_type'] ?? '') == 'government-jobs') ? 'selected' : '' ?>>Government Jobs</option>
                <option value="private-jobs" <?= (($_POST['book_type'] ?? '') == 'private-jobs') ? 'selected' : '' ?>>Private Jobs</option>
                <option value="internships" <?= (($_POST['book_type'] ?? '') == 'internships') ? 'selected' : '' ?>>Internships</option>
                <option value="part-time-jobs" <?= (($_POST['book_type'] ?? '') == 'part-time-jobs') ? 'selected' : '' ?>>Part-Time Jobs</option>
                <option value="work-from-home" <?= (($_POST['book_type'] ?? '') == 'work-from-home') ? 'selected' : '' ?>>Work From Home</option>
                <option value="overseas-jobs" <?= (($_POST['book_type'] ?? '') == 'overseas-jobs') ? 'selected' : '' ?>>Overseas Jobs</option>
            </select>
            <?php if (!isset($_POST['book_type']) || $_POST['book_type'] === ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please select a book category</p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="author">
                Author <span class="text-red-500">*</span>
            </label>
            <input required name="author" id="author" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['author']) && trim($_POST['author']) === '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['author'] ?? '') ?>" 
                   placeholder="e.g., M. Laxmikanth">
            <?php if (isset($_POST['author']) && trim($_POST['author']) === ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide author name</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="publisher">
                Publisher
            </label>
            <input name="publisher" id="publisher" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   value="<?= e($_POST['publisher'] ?? '') ?>" 
                   placeholder="e.g., McGraw Hill">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="publication_year">
                Publication Year
            </label>
            <input name="publication_year" id="publication_year" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['publication_year']) && !preg_match('/^\d{4}$/', $_POST['publication_year']) && $_POST['publication_year'] !== '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['publication_year'] ?? '') ?>" 
                   placeholder="e.g., 2023" maxlength="4">
            <?php if (isset($_POST['publication_year']) && !preg_match('/^\d{4}$/', $_POST['publication_year']) && $_POST['publication_year'] !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please enter a valid 4-digit year</p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="isbn">
                ISBN
            </label>
            <input name="isbn" id="isbn" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   value="<?= e($_POST['isbn'] ?? '') ?>" 
                   placeholder="e.g., 978-8121923329">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="amazon_link">
                Amazon Link
            </label>
            <input type="url" name="amazon_link" id="amazon_link" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['amazon_link']) && !filter_var($_POST['amazon_link'], FILTER_VALIDATE_URL) && $_POST['amazon_link'] !== '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['amazon_link'] ?? '') ?>" 
                   placeholder="https://amazon.in/dp/...">
            <?php if (isset($_POST['amazon_link']) && !filter_var($_POST['amazon_link'], FILTER_VALIDATE_URL) && $_POST['amazon_link'] !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please enter a valid URL</p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="flipkart_link">
                Flipkart Link
            </label>
            <input type="url" name="flipkart_link" id="flipkart_link" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['flipkart_link']) && !filter_var($_POST['flipkart_link'], FILTER_VALIDATE_URL) && $_POST['flipkart_link'] !== '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['flipkart_link'] ?? '') ?>" 
                   placeholder="https://flipkart.com/...">
            <?php if (isset($_POST['flipkart_link']) && !filter_var($_POST['flipkart_link'], FILTER_VALIDATE_URL) && $_POST['flipkart_link'] !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please enter a valid URL</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="book_image">
            Book Cover Image
        </label>
        <input type="file" name="book_image" id="book_image" 
               accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
               class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Recommended: JPG, PNG, or WebP format. Max size: 5MB. Optimal dimensions: 300x400px.
        </p>
        <div id="image-preview" class="mt-2 hidden">
            <img id="preview" class="max-w-xs max-h-40 border rounded shadow-sm">
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="markdown-editor">
            Book Description (Markdown supported) <span class="text-red-500">*</span>
        </label>
        <textarea id="markdown-editor" name="description" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                         <?= (!isset($_POST['description']) || trim($_POST['description'] ?? '') === '') ? 'border-red-500' : '' ?>" 
                  rows="6" placeholder="Provide detailed description about the book, key features, topics covered, etc..."><?= e($_POST['description'] ?? '') ?></textarea>
        <?php if (!isset($_POST['description']) || trim($_POST['description'] ?? '') === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a book description</p>
        <?php endif; ?>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports Markdown formatting. Include key features, topics covered, and why it's recommended.</p>
    </div>
  </div>
  
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">SEO Information</h2>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_title">
            Meta Title
        </label>
        <input type="text" name="meta_title" id="meta_title" 
              class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              value="<?= e($_POST['meta_title'] ?? '') ?>" 
              placeholder="Best Book for UPSC Preparation - [Book Title]">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_description">
            Meta Description
        </label>
        <textarea name="meta_description" id="meta_description" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                  rows="4" placeholder="Complete guide for competitive exam preparation. Recommended by experts and toppers..."><?= e($_POST['meta_description'] ?? '') ?></textarea>
    </div>
  </div>
  
  <div class="flex justify-end gap-3">
    <button type="reset" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
        Reset Form
    </button>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        Add Book
    </button>
  </div>
</form>

<script>
  // Image preview functionality
  document.getElementById('book_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('image-preview');
    
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        previewContainer.classList.remove('hidden');
      }
      reader.readAsDataURL(file);
    } else {
      previewContainer.classList.add('hidden');
    }
  });

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
            tableMarkdown = '\n| Chapter | Topics Covered | Pages |\n|---------|----------------|--------|\n| 1       | Topic details  | 1-50   |\n';
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
    placeholder: "Provide detailed description about the book, key features, topics covered, why it's recommended for competitive exams...",
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
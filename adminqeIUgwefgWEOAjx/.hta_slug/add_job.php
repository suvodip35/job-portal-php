<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();
$err = '';
$success = '';

// Handle file upload
function handleThumbnailUpload() {
    if (!isset($_FILES['thumbnail'])) return null;
    
    $uploadDir = __DIR__ . '/../../thumbnails/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $file = $_FILES['thumbnail'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'File size is too large. Maximum allowed is 2MB.'];
        }
        return ['error' => 'File upload failed with error code: ' . $file['error']];
    }
    
    // Check file size (max 2MB)
    if ($file['size'] > 2097152) {
        return ['error' => 'File size exceeds the 2MB limit.'];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array(strtolower($ext), $allowed)) {
        return ['error' => 'Invalid file type. Allowed formats: JPG, PNG, WEBP.'];
    }
    
    $filename = uniqid() . '.' . $ext;
    $destination = $uploadDir . $filename;
    
    if (compressImage($file['tmp_name'], $destination, 80, 600, 400)) {
        return '/thumbnails/' . $filename;
    }
    return ['error' => 'Failed to save the uploaded file.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    $title = trim($_POST['job_title'] ?? '');
    $company = trim($_POST['company_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $category_slug = $_POST['category_slug'] ?? '';
    $job_type = $_POST['job_type'] ?? 'full-time';
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_description'] ?? '');
    $apply_url = trim($_POST['apply_url'] ?? '');
    $last_date = !empty($_POST['last_date']) ? $_POST['last_date'] : null; // Allow empty last_date
    $status = $_POST['status'] ?? 'published';
    $min_salary = (int)($_POST['min_salary'] ?? 0);
    $max_salary = (int)($_POST['max_salary'] ?? 0);
    $document_link = trim($_POST['document_link'] ?? '');
    $createdBy = $_SESSION['admin_name'] ?? '';
    
    // Handle file upload and check for errors
    $thumbnailResult = handleThumbnailUpload();
    $thumbnail = null;
    $uploadError = '';
    
    if (is_array($thumbnailResult) && isset($thumbnailResult['error'])) {
        $uploadError = $thumbnailResult['error'];
    } else {
        $thumbnail = $thumbnailResult;
    }

    $errors = [];
    
    if ($title === '') {
        $errors[] = 'Job title is required.';
    }
    
    if ($company === '') {
        $errors[] = 'Company name is required.';
    }
    
    if ($category_slug === '0') {
        $errors[] = 'Please select a job category.';
    }
    
    if (!empty($uploadError)) {
        $errors[] = 'Thumbnail error: ' . $uploadError;
    }
    
    if ($min_salary > 0 && $max_salary > 0 && $min_salary > $max_salary) {
        $errors[] = 'Minimum salary cannot be higher than maximum salary.';
    }
    
    if (!empty($apply_url) && !filter_var($apply_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid URL for the application link.';
    }
    
    if (!empty($document_link) && !filter_var($document_link, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid URL for the document link.';
    }
    
    if (empty($errors)) {
        $base_slug = slugify($title);
        $slug = unique_slug($pdo, 'jobs', 'job_title_slug', $base_slug);

        // Handle suggested books - convert to JSON array
        $suggested_books = [];
        if (isset($_POST['select_books']) && is_array($_POST['select_books'])) {
            // Filter out empty values and convert to integers
            $suggested_books = array_filter($_POST['select_books'], function($bookId) {
                return !empty($bookId) && is_numeric($bookId);
            });
            $suggested_books = array_map('intval', $suggested_books);
        }
        
        // Convert to JSON string for storage
        $suggested_books_json = !empty($suggested_books) ? json_encode($suggested_books) : null;

        $stmt = $pdo->prepare("INSERT INTO jobs (category_slug, job_title, job_title_slug, meta_title, meta_description, company_name, location, description, requirements, job_type, apply_url, last_date, status, min_salary, max_salary, document_link, created_by, thumbnail, suggested_books) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$category_slug, $title, $slug, $meta_title, $meta_desc, $company, $location, $description, $requirements, $job_type, $apply_url, $last_date, $status, $min_salary, $max_salary, $document_link, $createdBy, $thumbnail, $suggested_books_json]);
        $success = 'Job posted successfully! It is now live on the website.';
    } else {
        $err = implode('<br>', $errors);
    }
}

// fetch categories for select
$cats = $pdo->query("SELECT * FROM job_categories ORDER BY category_name ASC")->fetchAll();
$allBooks = $pdo->query("SELECT * FROM books WHERE status = 'active' ORDER BY created_at ASC")->fetchAll();
// var_dump($bookTypeLabels);
?>

<!-- Add EasyMDE CSS -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<h1 class="text-2xl font-bold mb-4 dark:text-white">Add New Job Posting</h1>

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

<form method="post" class="space-y-6" enctype="multipart/form-data" accept-charset="UTF-8">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Basic Information</h2>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="job_title">
            Job Title <span class="text-red-500">*</span>
        </label>
        <input required name="job_title" id="job_title" 
               class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white 
                      <?= (isset($_POST['job_title']) && trim($_POST['job_title']) === '') ? 'border-red-500' : '' ?>"
               value="<?= e($_POST['job_title'] ?? '') ?>" 
               placeholder="e.g., Senior Web Developer">
        <?php if (isset($_POST['job_title']) && trim($_POST['job_title']) === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a job title</p>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="company_name">
                Company Name <span class="text-red-500">*</span>
            </label>
            <input name="company_name" id="company_name" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['company_name']) && trim($_POST['company_name']) === '') ? 'border-red-500' : '' ?>"
                   value="<?= e($_POST['company_name'] ?? '') ?>" 
                   placeholder="Company name">
            <?php if (isset($_POST['company_name']) && trim($_POST['company_name']) === '') : ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a company name</p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="location">
                Location (State) <span class="text-red-500">*</span>
            </label>
            <select name="location" id="location" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                           <?= (!isset($_POST['location']) || $_POST['location'] === '') ? 'border-red-500' : '' ?>">
                <option value="">-- Select State --</option>
                <?php foreach ($indianStates as $slug => $name): ?>
                <option value="<?= htmlspecialchars($slug) ?>" <?= (($_POST['location'] ?? '') == $slug) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if (!isset($_POST['location']) || $_POST['location'] === ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please select a state</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="category_slug">
                Category <span class="text-red-500">*</span>
            </label>
            <select name="category_slug" id="category_slug" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                           <?= (isset($_POST['category_slug']) && $_POST['category_slug'] === '0') ? 'border-red-500' : '' ?>">
                <option value="0">-- Select Category --</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= e($c['category_slug']) ?>" <?= (($_POST['category_slug'] ?? 0) == $c['category_slug']) ? 'selected' : '' ?>>
                    <?= e($c['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($_POST['category_slug']) && $_POST['category_slug'] === '0'): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please select a category</p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="min_salary">
                Minimum Salary (₹)
            </label>
            <input type="number" name="min_salary" id="min_salary" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   value="<?= e($_POST['min_salary'] ?? '') ?>" min="0" placeholder="e.g., 30000">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="max_salary">
                Maximum Salary (₹)
            </label>
            <input type="number" name="max_salary" id="max_salary" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   value="<?= e($_POST['max_salary'] ?? '') ?>" min="0" placeholder="e.g., 50000">
        </div>
    </div>
  </div>
  
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Job Details</h2>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="markdown-editor">
            Job Description <span class="text-red-500">*</span>
        </label>
        <textarea id="markdown-editor" name="description" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                         <?= (!isset($_POST['description']) || trim($_POST['description'] ?? '') === '') ? 'border-red-500' : '' ?>" 
                  rows="6" placeholder="Describe the job responsibilities, role, etc."><?= e($_POST['description'] ?? '') ?></textarea>
        <?php if (!isset($_POST['description']) || trim($_POST['description'] ?? '') === ''): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please provide a job description</p>
        <?php endif; ?>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports Markdown formatting</p>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="requirements">
            Requirements & Qualifications
        </label>
        <textarea name="requirements" id="requirements" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                  rows="4" placeholder="List the required skills, experience, education, etc."><?= e($_POST['requirements'] ?? '') ?></textarea>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="apply_url">
                Application URL
            </label>
            <input name="apply_url" id="apply_url" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['apply_url']) && !filter_var($_POST['apply_url'], FILTER_VALIDATE_URL) && $_POST['apply_url'] !== '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['apply_url'] ?? '') ?>" 
                   placeholder="https://...">
            <?php if (isset($_POST['apply_url']) && !filter_var($_POST['apply_url'], FILTER_VALIDATE_URL) && $_POST['apply_url'] !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please enter a valid URL</p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="document_link">
                Document Link
            </label>
            <input name="document_link" id="document_link" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= (isset($_POST['document_link']) && !filter_var($_POST['document_link'], FILTER_VALIDATE_URL) && $_POST['document_link'] !== '') ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['document_link'] ?? '') ?>" 
                   placeholder="https://...">
            <?php if (isset($_POST['document_link']) && !filter_var($_POST['document_link'], FILTER_VALIDATE_URL) && $_POST['document_link'] !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400">Please enter a valid URL</p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="last_date">
                Application Deadline (Optional)
            </label>
            <input type="date" name="last_date" id="last_date" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   value="<?= e($_POST['last_date'] ?? '') ?>">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty if there's no specific deadline</p>
        </div>
    </div>
  </div>
  
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Additional Information</h2>
    
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="job_type">
                    Job Type
                </label>
                <select name="job_type" id="job_type" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="full-time" <?= (($_POST['job_type'] ?? 'full-time') == 'full-time') ? 'selected' : '' ?>>Full-time</option>
                    <option value="part-time" <?= (($_POST['job_type'] ?? '') == 'part-time') ? 'selected' : '' ?>>Part-time</option>
                    <option value="contract" <?= (($_POST['job_type'] ?? '') == 'contract') ? 'selected' : '' ?>>Contract</option>
                    <option value="internship" <?= (($_POST['job_type'] ?? '') == 'internship') ? 'selected' : '' ?>>Internship</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="status">
                    Status
                </label>
                <select name="status" id="status" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="published" <?= (($_POST['status'] ?? 'published') == 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= (($_POST['status'] ?? '') == 'draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="closed" <?= (($_POST['status'] ?? '') == 'closed') ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
        </div>
    
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_title">
                Meta Title (for SEO)
            </label>
            <input name="meta_title" id="meta_title" 
                class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                value="<?= e($_POST['meta_title'] ?? '') ?>" 
                placeholder="Recommended: 50-60 characters">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Appears in search engine results</p>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_description">
                Meta Description (for SEO)
            </label>
            <textarea name="meta_description" id="meta_description" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                    rows="2" placeholder="Brief summary of the job posting"><?= e($_POST['meta_description'] ?? '') ?></textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 150-160 characters</p>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium dark:text-gray-300">Thumbnail Image</label>
            <div class="flex items-center gap-4">
                <label class="flex-1">
                    <input type="file" name="thumbnail" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-200
                    hover:file:bg-blue-100 dark:hover:file:bg-blue-800
                    <?= (isset($uploadError) && $uploadError !== '') ? 'border-red-500' : '' ?>">
                </label>
            </div>
            <?php if (isset($uploadError) && $uploadError !== ''): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($uploadError) ?></p>
            <?php else: ?>
            <p class="text-xs text-gray-500 dark:text-gray-400">Max 2MB (JPG, PNG, WEBP). Recommended: 600×400 pixels</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">Suggest Books</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="book_type">
                    Book Type
                </label>
                <select onchange="fetchBooksByCategory(this.value)" name="book_type" id="book_type" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">-Select-</option>
                    <?php
                        foreach ($bookCategories as $category) {
                            $selected = (($_POST['book_type'] ?? '') == $category['category_slug']) ? 'selected' : '';
                            echo "<option value=\"{$category['category_slug']}\" $selected>{$category['category_name']}</option>";
                        }
                    ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="select_books">
                    Select Books (Multiple)
                </label>
                <select name="select_books[]" id="select_books" multiple 
                        class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white h-32">
                    <option value="">-Select a book type first-</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple books</p>
            </div>
        </div>

        <div id="selected-books-preview" class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 hidden">
            <h3 class="text-sm font-medium mb-2 dark:text-gray-300">Selected Books:</h3>
            <div id="selected-books-list" class="space-y-1"></div>
        </div>
    </div>
  
  <div class="flex justify-end gap-3">
    <button type="reset" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
        Reset Form
    </button>
    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        Publish Job
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
    placeholder: "Describe the job responsibilities, requirements, and benefits...",
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

async function fetchBooksByCategory(categorySlug) {
    const bookSelect = document.getElementById('select_books');
    
    // Clear and show loading
    bookSelect.innerHTML = '<option value="">Loading books...</option>';
    bookSelect.disabled = true;
    
    if (!categorySlug) {
        bookSelect.innerHTML = '<option value="">-Select a book type first-</option>';
        bookSelect.disabled = false;
        return;
    }

    try {
        const response = await fetch(`/get-books.php?categorySlug=${encodeURIComponent(categorySlug)}`);
        
        if (!response.ok) {
            throw new Error(`Failed to fetch books: ${response.status}`);
        }

        const books = await response.json();
        
        // Populate dropdown
        bookSelect.innerHTML = '<option value="">-Select books-</option>';
        
        if (books.length > 0) {
            books.forEach(book => {
                const option = document.createElement('option');
                option.value = book.id;
                option.textContent = book.title;
                bookSelect.appendChild(option);
            });
        } else {
            bookSelect.innerHTML = '<option value="">-No books available-</option>';
        }
        
    } catch (error) {
        console.error('Error fetching books:', error);
        bookSelect.innerHTML = '<option value="">-Error loading books-</option>';
    } finally {
        bookSelect.disabled = false;
    }
}

// Function to update selected books preview
function updateSelectedBooksPreview() {
    const bookSelect = document.getElementById('select_books');
    const previewContainer = document.getElementById('selected-books-preview');
    const selectedBooksList = document.getElementById('selected-books-list');
    
    const selectedOptions = Array.from(bookSelect.selectedOptions);
    
    if (selectedOptions.length === 0) {
        previewContainer.classList.add('hidden');
        return;
    }
    
    // Show preview container
    previewContainer.classList.remove('hidden');
    
    // Update selected books list
    selectedBooksList.innerHTML = '';
    
    selectedOptions.forEach(option => {
        if (option.value) {
            const bookItem = document.createElement('div');
            bookItem.className = 'flex items-center justify-between text-sm p-2 bg-white dark:bg-gray-600 rounded';
            bookItem.innerHTML = `
                <span class="dark:text-gray-200">${option.textContent}</span>
                <button type="button" onclick="deselectBook('${option.value}')" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                    ×
                </button>
            `;
            selectedBooksList.appendChild(bookItem);
        }
    });
}

    // Function to deselect a book
    function deselectBook(bookId) {
        const bookSelect = document.getElementById('select_books');
        const option = bookSelect.querySelector(`option[value="${bookId}"]`);
        
        if (option) {
            option.selected = false;
            updateSelectedBooksPreview();
        }
    }

    // Initialize event listeners when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const bookSelect = document.getElementById('select_books');
        bookSelect.addEventListener('change', updateSelectedBooksPreview);
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
<style>
    #select_books option:checked {
        background-color: #3b82f6;
        color: white;
    }

    .selected-book-item {
        transition: all 0.2s ease-in-out;
    }

    .selected-book-item:hover {
        background-color: #f3f4f6;
        dark:background-color: #4b5563;
    }
</style>
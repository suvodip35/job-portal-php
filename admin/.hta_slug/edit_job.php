<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
// require_admin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo '<script>window.location.href="/admin/dashboard"</script>';
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE job_id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();
if (!$job) {
    echo '<script>window.location.href="/admin/dashboard"</script>';
    exit;
} 

$err = $success = '';
$fieldErrors = [];

// Handle file upload with better error reporting
function handleThumbnailUpload() {
    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded
    }
    
    $uploadDir = __DIR__ . '/../../thumbnails/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['error' => 'Failed to create upload directory.'];
        }
    }
    
    $file = $_FILES['thumbnail'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        
        return ['error' => $errorMessages[$file['error']] ?? 'Unknown upload error.'];
    }
    
    // Check file size (max 2MB)
    if ($file['size'] > 2097152) {
        return ['error' => 'File size exceeds the 2MB limit.'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
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
    
    // Handle file upload and check for errors
    $thumbnailResult = handleThumbnailUpload();
    $thumbnail = null;
    $uploadError = '';
    
    if (is_array($thumbnailResult) && isset($thumbnailResult['error'])) {
        $uploadError = $thumbnailResult['error'];
        $fieldErrors['thumbnail'] = $thumbnailResult['error'];
    } else {
        $thumbnail = $thumbnailResult;
    }

    // Validate required fields
    if ($title === '') {
        $fieldErrors['job_title'] = 'Job title is required.';
    }
    
    if ($company === '') {
        $fieldErrors['company_name'] = 'Company name is required.';
    }
    
    if ($category_slug === '0') {
        $fieldErrors['category_slug'] = 'Please select a job category.';
    }
    
    // Validate URLs if provided
    if (!empty($apply_url) && !filter_var($apply_url, FILTER_VALIDATE_URL)) {
        $fieldErrors['apply_url'] = 'Please enter a valid URL for the application link.';
    }
    
    if (!empty($document_link) && !filter_var($document_link, FILTER_VALIDATE_URL)) {
        $fieldErrors['document_link'] = 'Please enter a valid URL for the document link.';
    }
    
    // Validate salary range
    if ($min_salary > 0 && $max_salary > 0 && $min_salary > $max_salary) {
        $fieldErrors['min_salary'] = 'Minimum salary cannot be higher than maximum salary.';
        $fieldErrors['max_salary'] = 'Maximum salary cannot be lower than minimum salary.';
    }
    
    if (empty($fieldErrors)) {
        // slug update
        $base_slug = slugify($title);
        $slug = unique_slug($pdo, 'jobs', 'job_title_slug', $base_slug, $id);
        
        // Prepare update query with or without thumbnail
        if ($thumbnail) {
            $u = $pdo->prepare("UPDATE jobs SET category_slug=?, job_title=?, job_title_slug=?, meta_title=?, meta_description=?, company_name=?, location=?, description=?, requirements=?, job_type=?, apply_url=?, last_date=?, status=?, min_salary=?, max_salary=?, document_link=?, thumbnail=?, updated_at=NOW() WHERE job_id=?");
            $u->execute([$category_slug, $title, $slug, $meta_title, $meta_desc, $company, $location, $description, $requirements, $job_type, $apply_url, $last_date, $status, $min_salary, $max_salary, $document_link, $thumbnail, $id]);
        } else {
            $u = $pdo->prepare("UPDATE jobs SET category_slug=?, job_title=?, job_title_slug=?, meta_title=?, meta_description=?, company_name=?, location=?, description=?, requirements=?, job_type=?, apply_url=?, last_date=?, status=?, min_salary=?, max_salary=?, document_link=?, updated_at=NOW() WHERE job_id=?");

            $u->execute([$category_slug, $title, $slug, $meta_title, $meta_desc, $company, $location, $description, $requirements, $job_type, $apply_url, $last_date, $status, $min_salary, $max_salary, $document_link, $id]);
        }
        
        $success = 'Job updated successfully!';
        
        // refresh job data
        $stmt->execute([$id]);
        $job = $stmt->fetch();
    } else {
        $err = 'Please fix the errors below.';
    }
}

// fetch categories for select
$cats = $pdo->query("SELECT * FROM job_categories ORDER BY category_name ASC")->fetchAll();
?>

<!-- Add EasyMDE CSS -->
<link rel="stylesheet" href="/assets/easymde.min.css">
<script src="/assets/easymde.min.js"></script>

<h1 class="text-2xl font-bold mb-4 dark:text-white">Edit Job Posting</h1>

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
                      <?= isset($fieldErrors['job_title']) ? 'border-red-500' : '' ?>"
               value="<?= e($_POST['job_title'] ?? $job['job_title']) ?>" 
               placeholder="e.g., Senior Web Developer">
        <?php if (isset($fieldErrors['job_title'])): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['job_title']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="company_name">
                Company Name <span class="text-red-500">*</span>
            </label>
            <input name="company_name" id="company_name" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= isset($fieldErrors['company_name']) ? 'border-red-500' : '' ?>"
                   value="<?= e($_POST['company_name'] ?? $job['company_name']) ?>" 
                   placeholder="Company name">
            <?php if (isset($fieldErrors['company_name'])): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['company_name']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="location">
                Location (State) <span class="text-red-500">*</span>
            </label>
            <select name="location" id="location" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">-- Select State --</option>
                <?php foreach ($indianStates as $slug => $name): ?>
                <option value="<?= htmlspecialchars($slug) ?>" <?= (($_POST['location'] ?? $job['location']) == $slug) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="category_slug">
                Category <span class="text-red-500">*</span>
            </label>
            <select name="category_slug" id="category_slug" 
                    class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                           <?= isset($fieldErrors['category_slug']) ? 'border-red-500' : '' ?>">
                <option value="0">-- Select Category --</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= e($c['category_slug']) ?>" <?= (($_POST['category_slug'] ?? $job['category_slug']) == $c['category_slug']) ? 'selected' : '' ?>>
                    <?= e($c['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($fieldErrors['category_slug'])): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['category_slug']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="min_salary">
                Minimum Salary (₹)
            </label>
            <input type="number" name="min_salary" id="min_salary" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= isset($fieldErrors['min_salary']) ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['min_salary'] ?? $job['min_salary']) ?>" min="0" placeholder="e.g., 30000">
            <?php if (isset($fieldErrors['min_salary'])): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['min_salary']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="max_salary">
                Maximum Salary (₹)
            </label>
            <input type="number" name="max_salary" id="max_salary" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= isset($fieldErrors['max_salary']) ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['max_salary'] ?? $job['max_salary']) ?>" min="0" placeholder="e.g., 50000">
            <?php if (isset($fieldErrors['max_salary'])): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['max_salary']) ?></p>
            <?php endif; ?>
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
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                  rows="6" placeholder="Describe the job responsibilities, role, etc."><?= e($_POST['description'] ?? $job['description']) ?></textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports Markdown formatting</p>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="requirements">
            Requirements & Qualifications
        </label>
        <textarea name="requirements" id="requirements" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                  rows="4" placeholder="List the required skills, experience, education, etc."><?= e($_POST['requirements'] ?? $job['requirements']) ?></textarea>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="apply_url">
                Application URL
            </label>
            <input name="apply_url" id="apply_url" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= isset($fieldErrors['apply_url']) ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['apply_url'] ?? $job['apply_url']) ?>" 
                   placeholder="https://...">
            <?php if (isset($fieldErrors['apply_url'])): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['apply_url']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="document_link">
                Document Link
            </label>
            <input name="document_link" id="document_link" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white
                          <?= isset($fieldErrors['document_link']) ? 'border-red-500' : '' ?>" 
                   value="<?= e($_POST['document_link'] ?? $job['document_link']) ?>" 
                   placeholder="https://...">
            <?php if (isset($fieldErrors['document_link'])): ?>
            <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['document_link']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="last_date">
                Application Deadline (Optional)
            </label>
            <input type="date" name="last_date" id="last_date" 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   value="<?= e($_POST['last_date'] ?? $job['last_date']) ?>">
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
                <option value="full-time" <?= (($_POST['job_type'] ?? $job['job_type']) == 'full-time') ? 'selected' : '' ?>>Full-time</option>
                <option value="part-time" <?= (($_POST['job_type'] ?? $job['job_type']) == 'part-time') ? 'selected' : '' ?>>Part-time</option>
                <option value="contract" <?= (($_POST['job_type'] ?? $job['job_type']) == 'contract') ? 'selected' : '' ?>>Contract</option>
                <option value="internship" <?= (($_POST['job_type'] ?? $job['job_type']) == 'internship') ? 'selected' : '' ?>>Internship</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="status">
                Status
            </label>
            <select name="status" id="status" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="published" <?= (($_POST['status'] ?? $job['status']) == 'published') ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= (($_POST['status'] ?? $job['status']) == 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="closed" <?= (($_POST['status'] ?? $job['status']) == 'closed') ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_title">
            Meta Title (for SEO)
        </label>
        <input name="meta_title" id="meta_title" 
               class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
               value="<?= e($_POST['meta_title'] ?? $job['meta_title']) ?>" 
               placeholder="Recommended: 50-60 characters">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Appears in search engine results</p>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 dark:text-gray-300" for="meta_description">
            Meta Description (for SEO)
        </label>
        <textarea name="meta_description" id="meta_description" 
                  class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                  rows="2" placeholder="Brief summary of the job posting"><?= e($_POST['meta_description'] ?? $job['meta_description']) ?></textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 150-160 characters</p>
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium dark:text-gray-300">Thumbnail Image</label>
        <div class="flex items-center gap-4">
            <?php if ($job['thumbnail']): ?>
                <div class="flex-shrink-0">
                    <img src="<?= e($job['thumbnail']) ?>" class="w-20 h-20 object-cover rounded" alt="Current thumbnail">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Current thumbnail</p>
                </div>
            <?php endif; ?>
            <label class="flex-1">
                <input type="file" name="thumbnail" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-md file:border-0
                  file:text-sm file:font-semibold
                  file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900 dark:file:text-blue-200
                  hover:file:bg-blue-100 dark:hover:file:bg-blue-800
                  <?= isset($fieldErrors['thumbnail']) ? 'border-red-500' : '' ?>">
            </label>
        </div>
        <?php if (isset($fieldErrors['thumbnail'])): ?>
        <p class="mt-1 text-xs text-red-500 dark:text-red-400"><?= e($fieldErrors['thumbnail']) ?></p>
        <?php else: ?>
        <p class="text-xs text-gray-500 dark:text-gray-400">Max 2MB (JPG, PNG, WEBP). Recommended: 600×400 pixels. Leave empty to keep current image.</p>
        <?php endif; ?>
    </div>
  </div>
  
  <div class="flex justify-end gap-3">
    <a href="/admin/dashboard" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
        </svg>
        Back to Dashboard
    </a>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        Update Job
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
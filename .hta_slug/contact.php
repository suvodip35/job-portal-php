<?php
require_once('_header.php');

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Get additional information
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $session_id = session_id();
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $page_url = $_SERVER['REQUEST_URI'] ?? '';
    
    $errors = [];
    
    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (strlen($name) > 255) {
        $errors[] = 'Name must be less than 255 characters.';
    }
    
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email must be less than 255 characters.';
    }
    
    if ($message === '') {
        $errors[] = 'Message is required.';
    }
    
    if (empty($errors)) {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $pdo->prepare("INSERT INTO contacts 
                (name, email, message, ip_address, user_agent, session_id, referrer, page_url, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            // Execute with parameters to ensure proper escaping
            $stmt->execute([
                $name, 
                $email, 
                $message, 
                $ip_address, 
                $user_agent, 
                $session_id, 
                $referrer, 
                $page_url
            ]);
            
            $success = 'Your message has been sent successfully! We will get back to you soon.';
            
            // Clear form fields
            $name = $email = $message = '';
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            $errors[] = 'Sorry, there was an error sending your message. Please try again later.';
        }
    }
    
    if (!empty($errors)) {
        $err = implode('<br>', $errors);
    }
}

// Function to safely escape output (if not already defined elsewhere)
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
?>

<main class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-6 dark:text-white">Contact Us</h1>
  
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

  <form method="post" class="space-y-6 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    
    <div>
      <label class="block text-sm font-medium mb-2 dark:text-gray-300">Your Name <span class="text-red-500">*</span></label>
      <input type="text" name="name" class="w-full px-4 py-2 rounded-md border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring focus:ring-indigo-500" 
             value="<?= e($name ?? '') ?>" required maxlength="255">
    </div>
    
    <div>
      <label class="block text-sm font-medium mb-2 dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
      <input type="email" name="email" class="w-full px-4 py-2 rounded-md border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring focus:ring-indigo-500" 
             value="<?= e($email ?? '') ?>" required maxlength="255">
    </div>
    
    <div>
      <label class="block text-sm font-medium mb-2 dark:text-gray-300">Message <span class="text-red-500">*</span></label>
      <textarea name="message" rows="5" class="w-full px-4 py-2 rounded-md border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring focus:ring-indigo-500" 
                required><?= e($message ?? '') ?></textarea>
    </div>
    
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md">Send</button>
  </form>
</main>

<?php require_once "_footer.php"; ?>
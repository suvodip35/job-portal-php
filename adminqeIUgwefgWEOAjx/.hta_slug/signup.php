<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    csrf_check($token);

    if ($name && $email && $pass) {
        // check if email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetch();

        if ($exists) {
            $err = 'This email is already registered. Please log in.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, 'user']);
            $success = 'Account created successfully. You can now <a href="/adminqeIUgwefgWEOAjx/login" class="text-blue-600">login</a>.';
        }
    } else {
        $err = 'Please fill in all fields.';
    }
}
?>
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-4">
  <div class="w-full max-w-md">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <div class="p-8">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Sign Up</h1>
          <p class="text-gray-600 dark:text-gray-300">Create your account to access the dashboard</p>
        </div>
        
        <?php if ($err): ?>
        <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded text-sm">
          <?= e($err) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded text-sm">
          <?= $success ?>
        </div>
        <?php endif; ?>
        
        <form method="post" class="space-y-5">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
            <input 
              id="name"
              name="name" 
              type="text" 
              required 
              class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              placeholder="Your full name"
            >
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input 
              id="email"
              name="email" 
              type="email" 
              required 
              class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              placeholder="your@email.com"
            >
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
            <input 
              id="password"
              name="password" 
              type="password" 
              required 
              class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              placeholder="••••••••"
            >
          </div>
          
          <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition">
            Sign up
          </button>
        </form>
      </div>
      
      <div class="px-8 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 text-center">
        <p class="text-gray-600 dark:text-gray-300 text-sm">
          Already have an account? 
          <a href="/adminqeIUgwefgWEOAjx/login" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
            Login here
          </a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php
// require_once __DIR__ . '/../functions.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    csrf_check($token);
    if ($email && $pass) {
        $stmt = $pdo->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            if (password_verify($pass, $user['password'])) {
                // ✅ Already using password_hash
                $loginOK = true;
            } elseif (md5($pass) === $user['password']) {
                // ⚠ Old MD5 password — upgrade it
                $newHash = password_hash($pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update->execute([$newHash, $user['user_id']]);
                $loginOK = true;
            } else {
                $loginOK = false;
            }

            if ($loginOK && $user['role'] === 'admin') {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['user_id'];
                $_SESSION['admin_name'] = $user['name'];
                echo '<script>window.location.href="/admin/dashboard"</script>';
                exit;
            } else {
                $err = 'Invalid credentials or not an admin.';
            }
        } else {
            $err = 'Invalid credentials or not an admin.';
        }
    } else {
        $err = 'Please fill in all fields.';
    }
}
// require_once __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-4">
  <div class="w-full max-w-md">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <div class="p-8">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Admin Login</h1>
          <p class="text-gray-600 dark:text-gray-300">Enter your credentials to access the dashboard</p>
        </div>
        
        <?php if ($err): ?>
        <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded text-sm">
          <?= e($err) ?>
        </div>
        <?php endif; ?>
        
        <form method="post" class="space-y-5">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          
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
          
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
              <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Remember me</label>
            </div>
            
            <a href="/forgot-password" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
              Forgot password?
            </a>
          </div>
          
          <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition">
            Sign in
          </button>
        </form>
      </div>
      
      <div class="px-8 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 text-center">
        <p class="text-gray-600 dark:text-gray-300 text-sm">
          Don't have an account? 
          <a href="/register" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
            Contact administrator
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
<?php 
    // require_once __DIR__ . '/../includes/footer.php'; 
?>
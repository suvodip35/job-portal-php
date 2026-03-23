<?php
// ================= SEO =================
$pageTitle = "Contact FromCampus - Get Support & Career Guidance Support";
$pageDescription = "Have questions about latest job notifications or need career support? Contact the FromCampus team.";
$keywords = "Contact FromCampus, Job Support India, Career Feedback";
$author = "FromCampus Team";
$ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$canonicalUrl = "https://fromcampus.com/contact";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "ContactPage",
    "name" => "Contact FromCampus",
    "url" => "https://fromcampus.com/contact",
    "description" => "Contact the FromCampus team for verified job updates.",
    "mainEntity" => [
        "@type" => "Organization",
        "name" => "FromCampus",
        "contactPoint" => [
            "@type" => "ContactPoint",
            "email" => "support@fromcampus.com",
            "contactType" => "customer service",
            "areaServed" => "IN",
            "availableLanguage" => ["en", "hi", "bn"]
        ]
    ]
];

require_once('_header.php');

// ================= DEFAULT VARIABLES =================
$err = '';
$success = '';
$name = '';
$email = '';
$message = '';

// ================= FORM PROCESS =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? '');

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Extra info
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $session_id = session_id();
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $page_url = $_SERVER['REQUEST_URI'] ?? '';

    $errors = [];

    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (strlen($name) > 255) {
        $errors[] = 'Name too long.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }

    if ($message === '') {
        $errors[] = 'Message is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts 
                (name, email, message, ip_address, user_agent, session_id, referrer, page_url, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

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

            $success = "Your message has been sent successfully!";
            $name = $email = $message = '';
        } catch (PDOException $e) {
            error_log("Contact error: " . $e->getMessage());
            $err = "Something went wrong. Try again later.";
        }
    } else {
        $err = implode('<br>', $errors);
    }
}

// Escape function
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
?>

<main class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-2 dark:text-white">Contact Us</h1>
  <p class="text-gray-600 dark:text-gray-400 mb-8">
    Have questions? We are here to help you.
  </p>

  <!-- INFO BOX -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
    <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg border text-center">
        <h3 class="font-bold text-blue-800 dark:text-blue-400">Email</h3>
        <p class="text-sm dark:text-gray-300">teamfromcampus@gmail.com</p>
    </div>
    <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg border text-center">
        <h3 class="font-bold text-blue-800 dark:text-blue-400">Response</h3>
        <p class="text-sm dark:text-gray-300">24-48 Hours</p>
    </div>
    <div class="p-6 bg-blue-50 dark:bg-gray-800 rounded-lg border text-center">
        <h3 class="font-bold text-blue-800 dark:text-blue-400">Location</h3>
        <p class="text-sm dark:text-gray-300">West Bengal, India</p>
    </div>
  </div>

  <!-- ERROR -->
  <?php if (!empty($err)): ?>
  <div class="p-4 mb-6 bg-red-100 text-red-700 rounded">
      <?= $err ?>
  </div>
  <?php endif; ?>

  <!-- SUCCESS -->
  <?php if (!empty($success)): ?>
  <div class="p-4 mb-6 bg-green-100 text-green-700 rounded">
      <?= $success ?>
  </div>
  <?php endif; ?>

  <!-- FORM -->
  <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl flex flex-col md:flex-row overflow-hidden">
    
    <div class="md:w-1/3 bg-indigo-600 p-8 text-white">
        <h2 class="text-2xl font-bold mb-4">Get in Touch</h2>
        <p class="mb-6">We'd love to hear from you.</p>
        <ul class="space-y-2 text-sm">
            <li>✨ Career Guidance</li>
            <li>📢 Job Corrections</li>
            <li>🤝 Partnerships</li>
        </ul>
    </div>

    <form method="post" class="md:w-2/3 p-8 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="grid md:grid-cols-2 gap-4">
            <input type="text" name="name" placeholder="Full Name"
                value="<?= e($name) ?>"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-900 dark:text-white" required>

            <input type="email" name="email" placeholder="Email"
                value="<?= e($email) ?>"
                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-900 dark:text-white" required>
        </div>

        <textarea name="message" rows="4"
            placeholder="Your message..."
            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-900 dark:text-white"
            required><?= e($message) ?></textarea>

        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg">
            Send Message
        </button>
    </form>

  </div>
</main>

<?php require_once "_footer.php"; ?>
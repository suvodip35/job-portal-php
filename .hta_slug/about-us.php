<?php 
  // SEO Optimized Meta Tags
    $pageTitle = "Contact FromCampus - Get Support & Career Guidance Support";
    $pageDescription = "Have questions about latest job notifications or need career support? Contact the FromCampus team for help, feedback, or collaboration inquiries.";
    $keywords = "Contact FromCampus, Job Support India, Career Feedback, FromCampus Email, Job Portal Help";
    $author = "FromCampus Team";
    $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
    $canonicalUrl = "https://fromcampus.com/contact";

    $schema = [
        "@context" => "https://schema.org",
        "@type" => "ContactPage",
        "name" => "Contact FromCampus",
        "url" => "https://fromcampus.com/contact",
        "description" => "Contact the FromCampus team for verified job updates and career-related queries.",
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
?>
<main class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-2 dark:text-white">Contact Us</h1>
  <p class="text-gray-600 dark:text-gray-400 mb-8">Have questions about a recruitment notification? We are here to help you navigate your career path.</p>
  
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
    <div class="p-4 bg-blue-50 dark:bg-gray-800 rounded-lg border border-blue-100 dark:border-gray-700 text-center">
        <h3 class="font-bold text-blue-800 dark:text-blue-400">Email Us</h3>
        <p class="text-sm dark:text-gray-300">support@fromcampus.com</p>
    </div>
    <div class="p-4 bg-blue-50 dark:bg-gray-800 rounded-lg border border-blue-100 dark:border-gray-700 text-center">
        <h3 class="font-bold text-blue-800 dark:text-blue-400">Response Time</h3>
        <p class="text-sm dark:text-gray-300">Within 24-48 Hours</p>
    </div>
    <div class="p-4 bg-blue-50 dark:bg-gray-800 rounded-lg border border-blue-100 dark:border-gray-700 text-center">
        <h3 class="font-bold text-blue-800 dark:text-blue-400">Location</h3>
        <p class="text-sm dark:text-gray-300">West Bengal, India</p>
    </div>
  </div>

  <?php // Error and Success messages (Same as your original code) ?>
  <?php if ($err): ?> ... <?php endif; ?>
  <?php if ($success): ?> ... <?php endif; ?>

  <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row border dark:border-gray-700">
    <div class="md:w-1/3 bg-indigo-600 p-8 text-white">
        <h2 class="text-2xl font-bold mb-4">Get in Touch</h2>
        <p class="text-indigo-100 mb-6">Whether you are a job seeker or a partner, we'd love to hear from you.</p>
        <ul class="space-y-4 text-sm">
            <li class="flex items-center">✨ Career Guidance</li>
            <li class="flex items-center">📢 Job Post Corrections</li>
            <li class="flex items-center">🤝 Partnerships</li>
        </ul>
    </div>

    <form method="post" class="md:w-2/3 p-8 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
              <input type="text" name="name" class="w-full px-4 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" 
                     value="<?= e($name ?? '') ?>" required maxlength="255" placeholder="John Doe">
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1 dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
              <input type="email" name="email" class="w-full px-4 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" 
                     value="<?= e($email ?? '') ?>" required maxlength="255" placeholder="example@mail.com">
            </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1 dark:text-gray-300">Subject / Inquiry Type</label>
          <select class="w-full px-4 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
              <option>General Inquiry</option>
              <option>Job Notification Correction</option>
              <option>Advertisement/Collaboration</option>
              <option>Other</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1 dark:text-gray-300">How can we help? <span class="text-red-500">*</span></label>
          <textarea name="message" rows="4" class="w-full px-4 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" 
                    required placeholder="Write your message here..."><?= e($message ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-lg transition-all shadow-lg">
            Send Message
        </button>
    </form>
  </div>
</main>
<?php require_once "_footer.php"; ?>
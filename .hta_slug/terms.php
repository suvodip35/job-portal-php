<?php 
  // SEO Optimized Meta Tags
  $pageTitle = "Terms and Conditions | FromCampus - Usage Policy & Disclaimer";
  $pageDescription = "Read the Terms and Conditions of FromCampus.com. Learn about our content usage policy, job notification accuracy disclaimer, and user responsibilities.";
  $keywords = "Terms and Conditions FromCampus, Job Portal Disclaimer, Website Usage Policy, FromCampus Rules, Career Portal Terms India";
  $author = "FromCampus Team";
  $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
  $canonicalUrl = "https://fromcampus.com/terms";

  $schema = [
      "@context" => "https://schema.org",
      "@type" => "WebPage",
      "name" => "Terms & Conditions - FromCampus",
      "url" => "https://fromcampus.com/terms",
      "description" => "Official Terms and Conditions and User Agreement for FromCampus.com visitors.",
      "publisher" => [
          "@type" => "Organization",
          "name" => "FromCampus",
          "url" => "https://fromcampus.com/",
          "logo" => [
              "@type" => "ImageObject",
              "url" => "https://fromcampus.com/assets/logo/FromCampus_Color_text.png"
          ]
      ]
  ];

  require_once('_header.php');
?>
<main class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white border-b-2 border-indigo-500 inline-block">Terms & Conditions</h1>

  <div class="space-y-6 text-gray-700 dark:text-gray-300 leading-relaxed text-base">

    <p>Welcome to <strong>FromCampus.com</strong>. These Terms and Conditions outline the rules and regulations for the use of FromCampus's Website, located at <a href="https://fromcampus.com/" class="text-indigo-600 hover:underline">https://fromcampus.com/</a>.</p>

    <p>By accessing this website, we assume you accept these terms and conditions. Do not continue to use FromCampus if you do not agree to take all of the terms and conditions stated on this page.</p>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">1. Intellectual Property Rights</h2>
    <p>Unless otherwise stated, FromCampus and/or its licensors own the intellectual property rights for all material on FromCampus. All intellectual property rights are reserved. You may access this from FromCampus for your own personal use subjected to restrictions set in these terms and conditions.</p>
    <p><strong>You must not:</strong></p>
    <ul class="list-disc pl-6 space-y-2">
      <li>Republish material from FromCampus without proper credit.</li>
      <li>Sell, rent, or sub-license material from FromCampus.</li>
      <li>Reproduce, duplicate or copy material for commercial purposes.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">2. Accuracy of Job Notifications (Disclaimer)</h2>
    <p>The information provided on FromCampus.com is for general informational purposes only. While we strive to provide the most <strong>accurate and up-to-date job alerts</strong>, we collect data from official government gazettes, news portals, and department websites.</p>
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 my-4">
        <p class="text-yellow-800 dark:text-yellow-200 font-medium"><strong>Mandatory Notice:</strong> FromCampus is NOT a government entity. We are an independent news portal. Users are requested to verify all details from the <strong>Official Notification</strong> before paying any fees or submitting applications.</p>
    </div>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">3. User Responsibilities</h2>
    <ul class="list-disc pl-6 space-y-2">
      <li>Users must provide accurate information when contacting us.</li>
      <li>Misuse of the "Saved Jobs" feature or attempting to scrape data from our portal is strictly prohibited.</li>
      <li>You agree not to use the website in any way that causes, or may cause, damage to the website or impairment of the availability or accessibility of FromCampus.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">4. No Warranties</h2>
    <p>This Website is provided "as is," with all faults, and FromCampus expresses no representations or warranties, of any kind related to this Website or the materials contained on this Website.</p>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">5. Governing Law & Jurisdiction</h2>
    <p>These Terms will be governed by and interpreted in accordance with the laws of <strong>India</strong>, and you submit to the non-exclusive jurisdiction of the state and federal courts located in <strong>West Bengal</strong> for the resolution of any disputes.</p>

    <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg mt-10 border-l-4 border-indigo-600">
        <h2 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Contact Information</h2>
        <p>If you have any questions about our Terms and Conditions, please reach out to us:</p>
        <p class="mt-2 font-medium">📧 <strong>Email:</strong> teamfromcampus@gmail.com</p>
        <p class="mt-1 font-medium">🌐 <strong>Website:</strong> <a href="/contact" class="text-indigo-600 hover:underline">Contact Form</a></p>
    </div>

    <p class="mt-6 text-sm italic">Last Updated: <?php echo date('F j, Y'); ?></p>
  </div>
</main>
<?php require_once "_footer.php"; ?>
<?php 
  // SEO Optimized Meta Tags
  $pageTitle = "Privacy Policy | FromCampus - Data Protection & User Privacy";
  $pageDescription = "Official Privacy Policy of FromCampus. Learn how we handle your data, our use of cookies, Google AdSense compliance, and how your saved jobs are stored locally.";
  $keywords = "Privacy Policy FromCampus, Job Portal Privacy, Google AdSense Cookies, Data Protection, Saved Jobs Local Storage";
  $author = "FromCampus Team";
  $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
  $canonicalUrl = "https://fromcampus.com/privacy-policy";

  $schema = [
      "@context" => "https://schema.org",
      "@type" => "WebPage",
      "name" => "Privacy Policy - FromCampus",
      "url" => "https://fromcampus.com/privacy-policy",
      "description" => "Detailed Privacy Policy of FromCampus regarding user data, Google AdSense, and browser storage usage.",
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
  <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white border-b-2 border-indigo-500 inline-block">Privacy Policy</h1>

  <div class="space-y-6 text-gray-700 dark:text-gray-300 leading-relaxed text-base">

    <p>At <strong>FromCampus.com</strong>, accessible from <a href="https://fromcampus.com/" class="text-indigo-600 hover:underline">https://fromcampus.com/</a>, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by FromCampus and how we use it.</p>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">1. Information Collection and Use</h2>
    <p>We believe in minimal data collection to ensure a safe browsing experience. The information we may collect includes:</p>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>Log Files:</strong> FromCampus follows a standard procedure of using log files. These files log visitors when they visit websites. This includes IP addresses, browser type, Internet Service Provider (ISP), date/time stamp, and referring/exit pages.</li>
        <li><strong>Voluntary Data:</strong> If you contact us directly via our <a href="/contact" class="text-indigo-600 hover:underline">Contact Form</a>, we may receive your name, email address, and the contents of the message you send.</li>
        <li><strong>Local Storage:</strong> To enhance your experience (e.g., "Saved Jobs" feature), we use browser <strong>Local Storage</strong>. This data remains on your device and is never uploaded to our servers.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">2. Google DoubleClick DART Cookie</h2>
    <p>Google is one of the third-party vendors on our site. It also uses cookies, known as DART cookies, to serve ads to our site visitors based upon their visit to our site and other sites on the internet. However, visitors may choose to decline the use of DART cookies by visiting the Google ad and content network Privacy Policy.</p>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">3. Advertising Partners Privacy Policies</h2>
    <p>Third-party ad servers or ad networks use technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on FromCampus. They automatically receive your IP address when this occurs.</p>
    <p><strong>Note:</strong> FromCampus has no access to or control over these cookies that are used by third-party advertisers.</p>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">4. GDPR & CCPA Data Protection Rights</h2>
    <p>We want to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:</p>
    <ul class="list-disc pl-6 space-y-2">
        <li><strong>The right to access:</strong> You have the right to request copies of your personal data.</li>
        <li><strong>The right to rectification:</strong> You have the right to request that we correct any information you believe is inaccurate.</li>
        <li><strong>The right to erasure:</strong> You have the right to request that we erase your personal data, under certain conditions.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">5. Children's Information</h2>
    <p>Another part of our priority is adding protection for children while using the internet. We do not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think your child provided this kind of information on our website, we strongly encourage you to contact us immediately.</p>

    <h2 class="text-2xl font-semibold mt-8 text-gray-900 dark:text-white">6. Consent</h2>
    <p>By using our website, you hereby consent to our Privacy Policy and agree to its <a href="/terms" class="text-indigo-600 hover:underline">Terms and Conditions</a>.</p>

    <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg mt-10 border-l-4 border-indigo-600">
        <h2 class="text-xl font-bold mb-2">Contact Our Privacy Team</h2>
        <p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us:</p>
        <p class="mt-2 font-medium">📧 <strong>Email:</strong> teamfromcampus@gmail.com</p>
        <p class="mt-1 font-medium">📍 <strong>Location:</strong> West Bengal, India</p>
    </div>

    <p class="mt-6 text-sm italic">Last Updated: <?php echo date('F j, Y'); ?></p>
  </div>
</main>
<?php require_once "_footer.php"; ?>
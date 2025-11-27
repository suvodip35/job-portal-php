<?php 

  $pageTitle = "Privacy Policy | FromCampus - Job Notification Portal";
  $pageDescription = "Read the official Privacy Policy of FromCampus. Learn how we collect, store, and protect user data including preferences and saved jobs using browser local storage.";
  $keywords = "Privacy Policy, User Data, Local Storage Data, Saved Jobs Policy, FromCampus Privacy";
  $author = "FromCampus";
  $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
  $canonicalUrl = "https://fromcampus.com/privacy-policy";

  $schema = [
      "@context" => "https://schema.org",
      "@type" => "WebPage",
      "name" => "Privacy Policy - FromCampus",
      "url" => "https://fromcampus.com/privacy-policy",
      "description" => "Official Privacy Policy of FromCampus explaining how user data like saved jobs and browser local storage is used and protected.",
      "keywords" => "Privacy Policy, User Data, Local Storage Data, Saved Jobs Policy, FromCampus",
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
  <h1 class="text-3xl font-bold mb-6">Privacy Policy</h1>

  <div class="space-y-4 text-gray-700 dark:text-gray-300 leading-relaxed">

    <p>Your privacy is important to us. This Privacy Policy explains how FromCampus.com collects, uses, and protects your information when you visit or interact with our website.</p>

    <h2 class="text-xl font-semibold mt-6">Information We Collect</h2>
    <p>We do not require users to register or create an account. However, we may collect the following information:</p>
    <ul class="list-disc pl-6 space-y-2">
      <li>Basic interaction data such as IP address, device type, and browsing activity (through analytics tools).</li>
      <li>Email address (only if you voluntarily submit via contact form or newsletter subscription).</li>
      <li>Saved job bookmarks — stored locally in your browser using local storage. This data is <strong>not stored or accessed by us.</strong></li>
    </ul>

    <h2 class="text-xl font-semibold mt-6">Use of Cookies & Local Storage</h2>
    <p>
      We may use cookies and browser storage to improve user experience, such as remembering bookmarked job posts or preferences.
      You may disable cookies anytime from your browser settings, though some features may not work properly.
    </p>

    <h2 class="text-xl font-semibold mt-6">How We Use Your Information</h2>
    <ul class="list-disc pl-6 space-y-2">
      <li>To improve website functionality and user experience.</li>
      <li>To provide relevant job updates, articles, and notifications.</li>
      <li>To analyze website performance and user engagement using tools such as Google Analytics.</li>
    </ul>

    <h2 class="text-xl font-semibold mt-6">Third-Party Services</h2>
    <p>
      Our website may use third-party tools such as Google Analytics, Google AdSense, and affiliate links. These third parties may collect anonymized data to serve ads or analyze traffic.
      We do not control how third parties use this data, and users should review their policies for more details.
    </p>

    <h2 class="text-xl font-semibold mt-6">Data Sharing</h2>
    <p>
      We do not sell, trade, or share your personal information with anyone, except when legally required or when integrating third-party services mentioned above.
    </p>

    <h2 class="text-xl font-semibold mt-6">Your Rights</h2>
    <ul class="list-disc pl-6 space-y-2">
      <li>You may request deletion of any voluntarily submitted information (such as email).</li>
      <li>You may turn off cookies or clear browser storage anytime.</li>
      <li>You may unsubscribe from newsletter emails (if subscribed).</li>
    </ul>

    <h2 class="text-xl font-semibold mt-6">Changes to This Policy</h2>
    <p>
      We may update this Privacy Policy from time to time. Continued use of our website means you accept the updated policy.
    </p>

    <h2 class="text-xl font-semibold mt-6">Contact Us</h2>
    <p>If you have any questions about this Privacy Policy, you can reach us at:</p>
    <p class="font-medium">📧 teamfromcampus@gmail.com</p>

    <p class="mt-6 font-semibold">Last Updated: <?= date('F j, Y') ?></p>

  </div>
</main>

<?php require_once "_footer.php"; ?>

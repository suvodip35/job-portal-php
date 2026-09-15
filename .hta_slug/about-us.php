<?php 
  // SEO Optimized Meta Tags
  $pageTitle = "About Us | FromCampus - India's Trusted Job & Career Notification Portal";
  $pageDescription = "Learn about FromCampus.com, our mission, and our editorial standards for delivering verified government job alerts, exam updates, study resources, and career guidance across India.";
  $keywords = "About FromCampus, Job Portal India, Government Job Alerts, Career Guidance, FromCampus Team, Exam Notifications";
  $author = "FromCampus Team";
  $ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
  $canonicalUrl = "https://fromcampus.com/about-us";

  $schema = [
      "@context" => "https://schema.org",
      "@type" => "AboutPage",
      "name" => "About FromCampus",
      "url" => "https://fromcampus.com/about-us",
      "description" => "Comprehensive information about FromCampus, our mission, verification methodology, and editorial team.",
      "mainEntity" => [
          "@type" => "Organization",
          "name" => "FromCampus",
          "url" => "https://fromcampus.com/",
          "logo" => [
              "@type" => "ImageObject",
              "url" => "https://fromcampus.com/assets/logo/FromCampus_Color_text.png"
          ],
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
<main class="max-w-5xl mx-auto px-4 py-10">
  <!-- Page Header -->
  <header class="mb-10 text-center">
    <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full">
      Who We Are
    </span>
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mt-3 mb-4">
      About FromCampus
    </h1>
    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
      Your reliable, independent career guidance and recruitment alert platform helping students and aspirants across India navigate government and private job opportunities.
    </p>
  </header>

  <!-- Mission and Vision -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Our Mission</h2>
      <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
        To bridge the gap between job aspirants and official employment notifications by delivering timely, thoroughly verified, and easily comprehensible career updates without clutter or misinformation.
      </p>
    </div>

    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Our Vision</h2>
      <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
        To become India's most trusted, candidate-centric educational ecosystem offering accurate job circulars, authentic study materials, exam prep books, and essential online utilities under one transparent roof.
      </p>
    </div>
  </div>

  <!-- What We Provide -->
  <section class="mb-12">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">What We Offer</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="p-5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Verified Job Alerts</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Structured summaries for UPSC, SSC, Railways, State PSCs, Police, ITI, Apprenticeships, and Banking recruitments.
        </p>
      </div>

      <div class="p-5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Exam Updates</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Instant alerts on admit cards, exam schedules, answer keys, results, and syllabus revisions.
        </p>
      </div>

      <div class="p-5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Recommended Books</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Curated collection of competitive exam preparation books with direct access to official editions and authentic publishers.
        </p>
      </div>

      <div class="p-5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Career Tools</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Free browser-based utilities including document & photo compressors tailored for job application portals.
        </p>
      </div>
    </div>
  </section>

  <!-- Editorial & Verification Standards -->
  <section class="mb-12 p-8 bg-blue-50/60 dark:bg-gray-800 rounded-2xl border border-blue-100 dark:border-gray-700">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
      Our Editorial & Verification Process
    </h2>
    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
      At FromCampus, we understand that a single incorrect date or eligibility requirement can cost an aspirant their career opportunity. That is why our editorial team follows a rigorous 3-step verification process:
    </p>
    <ol class="list-decimal pl-6 space-y-3 text-gray-700 dark:text-gray-300">
      <li>
        <strong>Primary Source Verification:</strong> Every recruitment notification is sourced and cross-referenced directly from official government gazettes, employment news publications, or official department portals (e.g., ssc.gov.in, upsc.gov.in, indianrailways.gov.in).
      </li>
      <li>
        <strong>Structured Extraction:</strong> Key details such as age limits, eligibility criteria, category-wise vacancies, selection stages, and application fees are extracted into easy-to-read comparison tables.
      </li>
      <li>
        <strong>Direct Official Links:</strong> We always provide verified direct links to the official PDF notification and official application links so candidates can confirm details independently before applying.
      </li>
    </ol>
  </section>

  <!-- Transparency and Disclaimer Notice -->
  <section class="mb-12 p-6 bg-amber-50 dark:bg-amber-900/20 rounded-xl border-l-4 border-amber-500">
    <h3 class="text-lg font-bold text-amber-900 dark:text-amber-200 mb-2">
      Independent Information Notice
    </h3>
    <p class="text-sm text-amber-800 dark:text-amber-300 leading-relaxed">
      FromCampus is an independent educational and informational news portal. We are <strong>not affiliated, associated, authorized, endorsed by, or in any way officially connected with any government body, ministry, or department</strong>. All government trademarks and logos mentioned belong to their respective authorities. For full details, please review our <a href="/disclaimer" class="underline font-semibold">Disclaimer & DMCA Policy</a>.
    </p>
  </section>

  <!-- Location and Contact Information -->
  <section class="p-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Our Team & Location</h2>
    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
      FromCampus is managed and operated by a dedicated team of educational researchers, career writers, and web developers based in <strong>West Bengal, India</strong>. We are committed to maintaining a clean, accessible, and fast web experience for every user.
    </p>

    <div class="flex flex-wrap gap-4 items-center">
      <a href="/contact" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition shadow-md">
        Contact Our Team
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
      </a>
      <a href="/privacy-policy" class="inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition">
        Privacy Policy
      </a>
    </div>
  </section>
</main>
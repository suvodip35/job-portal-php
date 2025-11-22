<?php
$metaTitle = "Free Online Tools – Productivity, Utilities & More | FromCampus";
$pageDescription = "Discover free online tools from FromCampus to boost productivity, manage tasks, compress images, generate slugs, and more. All tools are browser-based and easy to use.";
$keywords = "Online Tools, Free Productivity Tools, Image Compressor, To-Do List, Slug Generator, Utility Tools, FromCampus Tools";
$ogImage = "https://fromcampus.com/assets/assets/tools-image/tools-homepage.jpg";
$canonicalUrl = "https://fromcampus.com/tools";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "WebSite",
    "name" => "FromCampus Tools",
    "url" => $canonicalUrl,
    "description" => $pageDescription,
    "publisher" => [
        "@type" => "Organization",
        "name" => "FromCampus"
    ],
    "potentialAction" => [
        "@type" => "SearchAction",
        "target" => "https://fromcampus.com/tools?search={search_term_string}",
        "query-input" => "required name=search_term_string"
    ]
];

require_once __DIR__ . '/../../.hta_slug/_header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-500 to-teal-500 text-white py-8 px-6 text-center">
    <h1 class="text-4xl md:text-5xl font-bold mb-4">Explore Free Online Tools</h1>
    <p class="text-lg md:text-xl max-w-3xl mx-auto">
        FromCampus offers a collection of browser-based tools to boost productivity, organize tasks, 
        compress images, generate SEO-friendly slugs, and more. Everything is free and easy to use.
    </p>
</div>

<!-- Search Bar -->
<div class="max-w-2xl mx-auto mt-8 px-4">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Search tools..." 
        class="w-full p-4 border-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 shadow" 
        onkeyup="filterTools()" />
</div>

<!-- Tools Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 max-w-6xl mx-auto mt-12 px-4">
    
    <!-- Example Tool Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transform transition hover:scale-105 duration-300">
        <img src="/assets/tools-image/image-compressor.jpg" alt="Image Compressor" class="w-full h-40 object-cover">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">Image Compressor</h2>
            <p class="text-gray-700 dark:text-gray-300 mb-4 text-sm">
                Compress images without losing quality. Supports JPG, JPEG, PNG & converts to lightweight JPG.
            </p>
            <a href="/tools/image-compressor" class="inline-block px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition">
                Open Tool
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transform transition hover:scale-105 duration-300">
        <img src="/assets/tools-image/todo-list.jpg" alt="To-Do List" class="w-full h-40 object-cover">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">To-Do List</h2>
            <p class="text-gray-700 dark:text-gray-300 mb-4 text-sm">
                Add, edit, complete, and manage tasks efficiently. Drag & drop, filtering, and auto-save included.
            </p>
            <a href="/tools/todo-list" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                Open Tool
            </a>
        </div>
    </div>

    <!-- <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transform transition hover:scale-105 duration-300">
        <img src="/assets/tools-image/slug-generator.jpg" alt="Slug Generator" class="w-full h-40 object-cover">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">Slug Generator</h2>
            <p class="text-gray-700 dark:text-gray-300 mb-4 text-sm">
                Convert text into SEO-friendly slugs instantly for blogs, URLs, and CMS content.
            </p>
            <a href="/tools/slug-generator" class="inline-block px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                Open Tool
            </a>
        </div>
    </div> -->

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transform transition hover:scale-105 duration-300">
        <img src="/assets/tools-image/typing-test.jpg" alt="Typing Speed Test" class="w-full h-40 object-cover">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">Typing Speed Test</h2>
            <p class="text-gray-700 dark:text-gray-300 mb-4 text-sm">
                Check your typing speed and accuracy in real-time. Improve typing skills efficiently.
            </p>
            <a href="/tools/typing-speed" class="inline-block px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
                Open Tool
            </a>
        </div>
    </div>

    <!-- Add more tools dynamically as needed -->
</div>

<script>
function filterTools() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.grid > div');
    cards.forEach(card => {
        const title = card.querySelector('h2').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        card.style.display = title.includes(input) || desc.includes(input) ? 'block' : 'none';
    });
}
</script>

<style>
body {
    transition: background-color 0.3s, color 0.3s;
}

/* Card hover shadow & scale effect for smooth UI */
.grid > div:hover {
    box-shadow: 0 20px 30px rgba(0,0,0,0.2);
}
</style>

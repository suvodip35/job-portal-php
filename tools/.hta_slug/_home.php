<?php
$metaTitle = "Free Online Tools – Productivity, Utilities & More | FromCampus";
$pageDescription = "Discover free online tools from FromCampus to boost productivity, manage tasks, compress images, generate slugs, and more. All tools are browser-based and easy to use.";
$keywords = "Online Tools, Free Productivity Tools, Image Compressor, PDF Compressor, To-Do List, Slug Generator, Utility Tools, FromCampus Tools";
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

// Tools array
$tools = [
    [
        'name' => 'Image Compressor',
        'description' => 'Compress images without losing quality. Supports JPG, JPEG, PNG & converts to lightweight JPG.',
        'image' => '/assets/tools-image/image-compressor.jpg',
        'url' => '/tools/image-compressor',
        'button_color' => 'bg-pink-500 hover:bg-pink-600',
        'alt' => 'Image Compressor'
    ],
    [
        'name' => 'PC Builder',
        'description' => 'Build your own PC with the latest components. Choose from CPU, GPU, RAM, Storage, and more.',
        'image' => '/assets/tools-image/pc-builder.jpg',
        'url' => '/tools/pc-builder',
        'button_color' => 'bg-blue-500 hover:bg-blue-600',
        'alt' => 'PC Builder'
    ],
    // [
    //     'name' => 'PDF Compressor',
    //     'description' => 'Reduce PDF file size while maintaining quality. Perfect for documents, forms, and email attachments.',
    //     'image' => '/assets/tools-image/pdf-compressor.jpg',
    //     'url' => '/tools/pdf-compressor',
    //     'button_color' => 'bg-blue-500 hover:bg-blue-600',
    //     'alt' => 'PDF Compressor'
    // ],
    [
        'name' => 'QR Code Generator',
        'description' => 'Generate QR codes for URLs, text, and more. Perfect for online forms, job applications, and more.',
        'image' => '/assets/tools-image/qr-code.jpg',
        'url' => '/tools/qr-code-generator',
        'button_color' => 'bg-yellow-500 hover:bg-yellow-600',
        'alt' => 'QR Code Generator'
    ],
    [
        'name' => 'Letter Case Converter',
        'description' => 'Convert text between uppercase, lowercase, sentence case, title case and more. Format text instantly.',
        'image' => '/assets/tools-image/letter-case-converter.jpg',
        'url' => '/tools/lettercase-converter',
        'button_color' => 'bg-indigo-500 hover:bg-indigo-600',
        'alt' => 'Letter Case Converter'
    ],
    [
        'name' => 'To-Do List',
        'description' => 'Add, edit, complete, and manage tasks efficiently. Drag & drop, filtering, and auto-save included.',
        'image' => '/assets/tools-image/todo-list.jpg',
        'url' => '/tools/todo-list',
        'button_color' => 'bg-green-500 hover:bg-green-600',
        'alt' => 'To-Do List'
    ],
    [
        'name' => 'Typing Speed Test',
        'description' => 'Check your typing speed and accuracy in real-time. Improve typing skills efficiently.',
        'image' => '/assets/tools-image/typing-test.jpg',
        'url' => '/tools/typing-speed',
        'button_color' => 'bg-purple-500 hover:bg-purple-600',
        'alt' => 'Typing Speed Test'
    ],
];

require_once __DIR__ . '/../../.hta_slug/_header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-500 to-teal-500 text-white py-8 px-6 text-center">
    <h1 class="text-4xl md:text-5xl font-bold mb-4">Explore Free Online Tools</h1>
    <p class="text-lg md:text-xl max-w-3xl mx-auto">
        FromCampus offers a collection of browser-based tools to boost productivity, organize tasks, 
        compress images and PDFs, and more. Everything is free and easy to use.
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
    
    <?php foreach ($tools as $tool): ?>
    <div class="border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transform transition hover:scale-105 duration-300 tool-card">
        <div class="w-full h-40 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center relative">
            <img 
                src="<?php echo htmlspecialchars($tool['image']); ?>" 
                alt="<?php echo htmlspecialchars($tool['alt']); ?>" 
                class="w-full h-40 object-cover absolute inset-0"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
            <div class="hidden flex-col items-center justify-center text-gray-500 dark:text-gray-400 p-4">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($tool['name'] === 'Image Compressor'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    <?php elseif ($tool['name'] === 'PDF Compressor'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    <?php elseif ($tool['name'] === 'To-Do List'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    <?php elseif ($tool['name'] === 'Typing Speed Test'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    <?php endif; ?>
                </svg>
                <span class="text-sm font-medium text-center"><?php echo htmlspecialchars($tool['name']); ?></span>
            </div>
        </div>
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white"><?php echo htmlspecialchars($tool['name']); ?></h2>
            <p class="text-gray-700 dark:text-gray-300 mb-4 text-sm">
                <?php echo htmlspecialchars($tool['description']); ?>
            </p>
            <a href="<?php echo htmlspecialchars($tool['url']); ?>" class="inline-block px-4 py-2 <?php echo htmlspecialchars($tool['button_color']); ?> text-white rounded-lg transition">
                Open Tool
            </a>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<script>
function filterTools() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.tool-card');
    
    cards.forEach(card => {
        const title = card.querySelector('h2').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        card.style.display = title.includes(input) || desc.includes(input) ? 'block' : 'none';
    });
}

// Initialize fallback images on page load
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.tool-card img');
    images.forEach(img => {
        // Check if image loaded successfully
        if (!img.complete || img.naturalHeight === 0) {
            img.style.display = 'none';
            const fallback = img.nextElementSibling;
            if (fallback && fallback.classList.contains('hidden')) {
                fallback.classList.remove('hidden');
                fallback.style.display = 'flex';
            }
        }
    });
});
</script>

<style>
body {
    transition: background-color 0.3s, color 0.3s;
}

/* Card hover shadow & scale effect for smooth UI */
.tool-card:hover {
    box-shadow: 0 20px 30px rgba(0,0,0,0.2);
}

/* Smooth image loading */
.tool-card img {
    transition: opacity 0.3s ease;
}

.tool-card img:not([src]) {
    opacity: 0;
}
</style>
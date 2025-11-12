<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();

// Meta values for books page
$pageTitle = 'Recommended Books - ' . APP_NAME;
$pageDescription = 'Find the best books for competitive exams like UPSC, SSC, Banking, Railway, and other government job preparations';
$keywords = "UPSC Books, SSC Books, Banking Books, Competitive Exam Books, Study Materials";
$ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$canonicalUrl = "/books";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => "Recommended Books - FromCampus",
    "url" => $canonicalUrl,
    "description" => "Find the best books for competitive exams like UPSC, SSC, Banking, Railway, and other government job preparations",
    "keywords" => "UPSC Books, SSC Books, Banking Books, Competitive Exam Books, Study Materials",
    "image" => $ogImage,
    "isPartOf" => [
        "@type" => "WebSite",
        "name" => "FromCampus - JOB Notification Portal",
        "url" => "https://fromcampus.com/"
    ],
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


// Fetch books for each category
$categoryBooks = [];
foreach ($bookCategories as $category) {
    $books = $pdo->query("SELECT slug, title, description, author, publisher, book_image, amazon_link, flipkart_link, created_at FROM books WHERE book_type = '" . $category['category_slug'] . "' AND status = 'active' ORDER BY created_at DESC LIMIT 8")->fetchAll();
    $categoryBooks[$category['category_slug']] = $books;
}

// Function to check if book is new (within last 7 days)
function isNewBook($created_at) {
    return strtotime($created_at) > strtotime('-7 days');
}

// Function to create plain text excerpt from markdown
function markdownExcerpt($markdown, $Parsedown, $length = 80) {
    // Convert markdown to plain text
    $text = strip_tags($Parsedown->text($markdown));
    // Return truncated text
    return mb_substr($text, 0, $length) . (mb_strlen($text) > $length ? '...' : '');
}

// Function to get default book image
function getBookImage($book_image, $title) {
    if ($book_image && file_exists(__DIR__ . '/../../' . ltrim($book_image, '/'))) {
        return BASE_URL . $book_image;
    }
    // Return a default book image or placeholder
    return BASE_URL . '/assets/images/default-book-cover.jpg';
}

// Function to get color classes
function getColorClasses($color) {
    $colors = [
        'blue' => ['bg' => 'from-blue-600 to-blue-700', 'text' => 'text-blue-600', 'dark_text' => 'dark:text-blue-400', 'scrollbar_thumb' => 'blue-300', 'scrollbar_track' => 'blue-100'],
        'green' => ['bg' => 'from-green-600 to-green-700', 'text' => 'text-green-600', 'dark_text' => 'dark:text-green-400', 'scrollbar_thumb' => 'green-300', 'scrollbar_track' => 'green-100'],
        'yellow' => ['bg' => 'from-yellow-600 to-yellow-700', 'text' => 'text-yellow-600', 'dark_text' => 'dark:text-yellow-400', 'scrollbar_thumb' => 'yellow-300', 'scrollbar_track' => 'yellow-100'],
        'red' => ['bg' => 'from-red-600 to-red-700', 'text' => 'text-red-600', 'dark_text' => 'dark:text-red-400', 'scrollbar_thumb' => 'red-300', 'scrollbar_track' => 'red-100'],
        'purple' => ['bg' => 'from-purple-600 to-purple-700', 'text' => 'text-purple-600', 'dark_text' => 'dark:text-purple-400', 'scrollbar_thumb' => 'purple-300', 'scrollbar_track' => 'purple-100'],
        'indigo' => ['bg' => 'from-indigo-600 to-indigo-700', 'text' => 'text-indigo-600', 'dark_text' => 'dark:text-indigo-400', 'scrollbar_thumb' => 'indigo-300', 'scrollbar_track' => 'indigo-100'],
        'pink' => ['bg' => 'from-pink-600 to-pink-700', 'text' => 'text-pink-600', 'dark_text' => 'dark:text-pink-400', 'scrollbar_thumb' => 'pink-300', 'scrollbar_track' => 'pink-100'],
        'teal' => ['bg' => 'from-teal-600 to-teal-700', 'text' => 'text-teal-600', 'dark_text' => 'dark:text-teal-400', 'scrollbar_thumb' => 'teal-300', 'scrollbar_track' => 'teal-100'],
        'orange' => ['bg' => 'from-orange-600 to-orange-700', 'text' => 'text-orange-600', 'dark_text' => 'dark:text-orange-400', 'scrollbar_thumb' => 'orange-300', 'scrollbar_track' => 'orange-100'],
        'gray' => ['bg' => 'from-gray-600 to-gray-700', 'text' => 'text-gray-600', 'dark_text' => 'dark:text-gray-400', 'scrollbar_thumb' => 'gray-300', 'scrollbar_track' => 'gray-100']
    ];
    
    return $colors[$color] ?? $colors['blue'];
}
?>

<!-- Loading Placeholder (shown initially) -->
<div id="loading-placeholder" class="container mx-auto min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="max-w-8xl mx-auto p-4">
        <!-- Breadcrumb placeholder -->
        <div class="mb-6">
            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
        </div>
        
        <!-- Page title placeholder -->
        <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-1/4 mb-8 animate-pulse"></div>
        
        <!-- Four columns placeholder -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <?php for($i = 0; $i < 4; $i++): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-2/3 mb-4 animate-pulse"></div>
                <div class="space-y-4">
                    <?php for($j = 0; $j < 4; $j++): ?>
                    <div class="p-3 border rounded dark:border-gray-700">
                        <div class="flex justify-between mb-2">
                            <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 animate-pulse"></div>
                            <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-10 animate-pulse"></div>
                        </div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/2 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Actual Content (hidden initially) -->
<div id="actual-content" class="hidden container mx-auto">
    <div class="max-w-8xl mx-auto p-4">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>" 
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Home
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Books</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Recommended Books</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Find the best books for competitive exams recommended by experts and toppers</p>
        </div>

        <!-- Books Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <?php foreach ($bookCategories as $category): 
                $colorClasses = getColorClasses($category['color']);
                $books = $categoryBooks[$category['category_slug']] ?? [];
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden flex flex-col border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r <?= $colorClasses['bg'] ?> p-4 flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="<?= $category['icon'] ?>" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-white"><?= e($category['category_name']) ?></h2>
                        <p class="text-white/80 text-xs mt-1"><?= e($category['description']) ?></p>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[600px] scrollbar-thin scrollbar-thumb-<?= $colorClasses['scrollbar_thumb'] ?> scrollbar-track-<?= $colorClasses['scrollbar_track'] ?> dark:scrollbar-thumb-<?= $colorClasses['scrollbar_thumb'] ?> dark:scrollbar-track-<?= $colorClasses['scrollbar_track'] ?>">
                    <div class="p-4 space-y-4">
                        <?php if (empty($books)): ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm">No books available</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($books as $book): 
                                $shortDescription = markdownExcerpt($book['description'], $Parsedown, 60);
                                $bookImage = getBookImage($book['book_image'], $book['title']);
                            ?>
                            <a href="book-details?slug=<?= e($book['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 bg-white dark:bg-gray-800 group">
                                <div class="flex items-start gap-3 mb-3">
                                    <img src="<?= $bookImage ?>" alt="<?= e($book['title']) ?>" 
                                         class="w-12 h-16 object-cover rounded border dark:border-gray-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <h3 class="font-semibold text-sm dark:text-white line-clamp-2 flex-1 group-hover:<?= $colorClasses['text'] ?> dark:group-hover:<?= $colorClasses['dark_text'] ?>"><?= e($book['title']) ?></h3>
                                            <?php if (isNewBook($book['created_at'])) echo blinkTag("NEW", $colorClasses['bg']); ?>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">By <?= e($book['author']) ?></p>
                                        <?php if ($book['publisher']): ?>
                                        <p class="text-xs text-gray-500 dark:text-gray-500"><?= e($book['publisher']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 clamp-text"><?= e($shortDescription) ?></p>
                                <div class="flex items-center justify-between mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-500"><?= date('M Y', strtotime($book['created_at'])) ?></p>
                                    <span class="<?= $colorClasses['text'] ?> dark:<?= $colorClasses['dark_text'] ?> text-xs font-medium flex items-center">
                                        View Details
                                        <svg class="w-3 h-3 ml-1 transition-transform duration-200 group-hover:translate-x-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script type="application/ld+json">
    <?= json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) ?>
</script>

<script>
// Show actual content and hide placeholder once page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(function() {
    document.getElementById('loading-placeholder').style.display = 'none';
    document.getElementById('actual-content').classList.remove('hidden');
  }, 300);
});
</script>

<style>
.clamp-1 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
}

.clamp-text {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Custom scrollbar styles */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  border-radius: 10px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  border-radius: 10px;
}

/* Blinking badge animation */
@keyframes blink {
  0%, 50%, 100% { opacity: 1; }
  25%, 75% { opacity: 0.5; }
}

.blink-badge {
  display: inline-block;
  padding: 2px 6px;
  font-size: 10px;
  font-weight: bold;
  border-radius: 4px;
  color: #fff;
  animation: blink 1.5s infinite;
  flex-shrink: 0;
  margin-left: 8px;
  line-height: 1.2;
}
</style>
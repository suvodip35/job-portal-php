<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();
// Meta values for homepage
$pageTitle = 'Latest Updates - ' . APP_NAME;
$pageDescription = 'Stay updated with the latest exam notifications, admit cards, results, and government notices';
$keywords = "Exam Updates, Admit Card, Result, Govt Notice, Latest Notifications";
$ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$canonicalUrl = BASE_URL . "updates/";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => "Latest Updates - FromCampus",
    "url" => $canonicalUrl,
    "description" => "Stay updated with the latest exam notifications, admit cards, results, and government notices",
    "keywords" => "Exam Updates, Admit Card, Result, Govt Notice, Latest Notifications",
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

// Fetch updates for marquee
$marqueeUpdates = $pdo->query("SELECT slug, title, created_at FROM updates ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Fetch updates for different sections
$examUpdates = $pdo->query("SELECT slug, title, description, created_at FROM updates WHERE update_type = 'exam' ORDER BY created_at DESC LIMIT 8")->fetchAll();
$resultUpdates = $pdo->query("SELECT slug, title, description, created_at FROM updates WHERE update_type = 'result' ORDER BY created_at DESC LIMIT 8")->fetchAll();
$syllabusUpdates = $pdo->query("SELECT slug, title, description, created_at FROM updates WHERE update_type = 'syllabus' ORDER BY created_at DESC LIMIT 8")->fetchAll();
$ansKeyUpdates = $pdo->query("SELECT slug, title, description, created_at FROM updates WHERE update_type = 'ans_key' ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Function to check if update is new (within last 2 days)
function isNewUpdate($created_at) {
    return strtotime($created_at) > strtotime('-2 days');
}

// Function to create plain text excerpt from markdown
function markdownExcerpt($markdown, $Parsedown, $length = 80) {
    // Convert markdown to plain text
    $text = strip_tags($Parsedown->text($markdown));
    // Return truncated text
    return mb_substr($text, 0, $length) . (mb_strlen($text) > $length ? '...' : '');
}
?>

<!-- Loading Placeholder (shown initially) -->
<div id="loading-placeholder" class="container mx-auto min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Marquee Placeholder -->
    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 py-2 flex items-center gap-3 overflow-hidden">
            <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-16 animate-pulse"></div>
            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
        </div>
    </div>
    
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
<div id="actual-content" class="hidden container mx-auto ">
    <!-- Marquee Section -->
    <?php if ($marqueeUpdates): ?>
    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2 overflow-hidden">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 flex items-center gap-3">
            <span class="px-2 py-0.5 text-xs font-semibold rounded bg-yellow-400 text-black shrink-0 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                </svg>
                Breaking
            </span>
            <div class="overflow-hidden whitespace-nowrap flex-1">
                <div class="inline-block whitespace-nowrap animate-marquee">
                    <?php foreach ($marqueeUpdates as $i=>$update): ?>
                        <a class="inline-block mx-4 transition-all duration-300 ease-in-out hover:text-blue-600 dark:hover:text-blue-400 text-gray-700 dark:text-gray-300 font-medium text-sm" href="<?= BASE_URL ?>/details?slug=<?= e($update['slug']) ?>">
                            <?= e($update['title']) ?>
                            <?php if (isNewUpdate($update['created_at'])) echo blinkTag("NEW", "#ef4444"); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Updates</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Latest Updates</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Stay informed with the latest exam notifications, admit cards, results and government notices</p>
        </div>

        <!-- Four Columns Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Exam Updates Column -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden flex flex-col border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">Exam Updates</h2>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[600px] scrollbar-thin scrollbar-thumb-blue-300 scrollbar-track-blue-100 dark:scrollbar-thumb-blue-700 dark:scrollbar-track-blue-900">
                    <div class="p-4 space-y-4">
                        <?php if (empty($examUpdates)): ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm">No exam updates available</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($examUpdates as $exam): 
                                $shortDescription = markdownExcerpt($exam['description'], $Parsedown, 80);
                            ?>
                            <a href="details?slug=<?= e($exam['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 bg-white dark:bg-gray-800 group">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-blue-600 dark:group-hover:text-blue-400"><?= e($exam['title']) ?></h3>
                                    <?php if (isNewUpdate($exam['created_at'])) echo blinkTag("NEW", "#3b82f6"); ?>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 clamp-text"><?= e($shortDescription) ?></p>
                                <div class="flex items-center justify-between mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-500"><?= date('M d, Y', strtotime($exam['created_at'])) ?></p>
                                    <span class="text-blue-600 dark:text-blue-400 text-xs font-medium flex items-center">
                                        Read more
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

            <!-- Admit Card Updates Column -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden flex flex-col border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-green-600 to-green-700 p-4 flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">Result Updates</h2>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[600px] scrollbar-thin scrollbar-thumb-green-300 scrollbar-track-green-100 dark:scrollbar-thumb-green-700 dark:scrollbar-track-green-900">
                    <div class="p-4 space-y-4">
                        <?php if (empty($resultUpdates)): ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm">No Result updates available</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($resultUpdates as $result): 
                                $shortDescription = markdownExcerpt($result['description'], $Parsedown, 80);
                            ?>
                            <a href="details?slug=<?= e($result['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 bg-white dark:bg-gray-800 group">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-green-600 dark:group-hover:text-green-400"><?= e($result['title']) ?></h3>
                                    <?php if (isNewUpdate($result['created_at'])) echo blinkTag("NEW", "#10b981"); ?>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 clamp-text"><?= e($shortDescription) ?></p>
                                <div class="flex items-center justify-between mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-500"><?= date('M d, Y', strtotime($result['created_at'])) ?></p>
                                    <span class="text-green-600 dark:text-green-400 text-xs font-medium flex items-center">
                                        Read more
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

            <!-- Result Updates Column -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden flex flex-col border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 p-4 flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l1.5-1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">Syllabus Updates</h2>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[600px] scrollbar-thin scrollbar-thumb-yellow-300 scrollbar-track-yellow-100 dark:scrollbar-thumb-yellow-700 dark:scrollbar-track-yellow-900">
                    <div class="p-4 space-y-4">
                        <?php if (empty($syllabusUpdates)): ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm">No result updates available</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($syllabusUpdates as $syllabus): 
                                $shortDescription = markdownExcerpt($syllabus['description'], $Parsedown, 80);
                            ?>
                            <a href="details?slug=<?= e($syllabus['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 bg-white dark:bg-gray-800 group">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-yellow-600 dark:group-hover:text-yellow-400"><?= e($syllabus['title']) ?></h3>
                                    <?php if (isNewUpdate($syllabus['created_at'])) echo blinkTag("NEW", "#f59e0b"); ?>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 clamp-text"><?= e($shortDescription) ?></p>
                                <div class="flex items-center justify-between mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-500"><?= date('M d, Y', strtotime($syllabus['created_at'])) ?></p>
                                    <span class="text-yellow-600 dark:text-yellow-400 text-xs font-medium flex items-center">
                                        Read more
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

            <!-- Notice Updates Column -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden flex flex-col border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-lg">
                <div class="bg-gradient-to-r from-red-600 to-red-700 p-4 flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">Answer Key Updates</h2>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[600px] scrollbar-thin scrollbar-thumb-red-300 scrollbar-track-red-100 dark:scrollbar-thumb-red-700 dark:scrollbar-track-red-900">
                    <div class="p-4 space-y-4">
                        <?php if (empty($ansKeyUpdates)): ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm">No notice updates available</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($ansKeyUpdates as $ansKey): 
                                $shortDescription = markdownExcerpt($ansKey['description'], $Parsedown, 80);
                            ?>
                            <a href="details?slug=<?= e($ansKey['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 bg-white dark:bg-gray-800 group">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-sm dark:text-white clamp-1 flex-1 group-hover:text-red-600 dark:group-hover:text-red-400"><?= e($ansKey['title']) ?></h3>
                                    <?php if (isNewUpdate($ansKey['created_at'])) echo blinkTag("NEW", "#ef4444"); ?>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 clamp-text"><?= e($shortDescription) ?></p>
                                <div class="flex items-center justify-between mt-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-500"><?= date('M d, Y', strtotime($ansKey['created_at'])) ?></p>
                                    <span class="text-red-600 dark:text-red-400 text-xs font-medium flex items-center">
                                        Read more
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
        </div>
    </div>
</div>
<script type="application/ld+json">
    <?= json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) ?>
</script>
<script>
// Show actual content and hide placeholder once page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Small delay to ensure all content is rendered
  setTimeout(function() {
    document.getElementById('loading-placeholder').style.display = 'none';
    document.getElementById('actual-content').classList.remove('hidden');
    
    // Initialize auto-scrolling for columns with many items
    initAutoScroll();
  }, 300);
});

// Function to initialize auto-scrolling for columns
function initAutoScroll() {
  const columns = document.querySelectorAll('.overflow-y-auto');
  
  columns.forEach(column => {
    const content = column.querySelector('div');
    if (content.scrollHeight > column.clientHeight) {
      // কনটেন্ট ডুপ্লিকেট করা
      const clone = content.cloneNode(true);
      column.appendChild(clone);

      // ক্লাস যোগ করা যাতে অ্যানিমেশন চালু হয়
      content.classList.add('scroll-content');
    }
  });
}
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

/* Marquee animation */
@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.animate-marquee {
  animation: marquee 30s linear infinite;
  display: inline-block;
}

.animate-marquee:hover {
  animation-play-state: paused;
}

/* Column content scrolling animation */
@keyframes scrollContent {
  0% { transform: translateY(0); }
  100% { transform: translateY(-50%); } /* অর্ধেক পর্যন্ত উঠবে */
}

.scroll-content {
  display: flex;
  flex-direction: column;
  animation: scrollContent 25s linear infinite;
}

.scroll-content:hover {
  animation-play-state: paused;
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
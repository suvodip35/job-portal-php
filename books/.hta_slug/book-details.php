<?php

// Get slug from URL
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    echo "<script>window.location.href='/'</script>";
    exit;
}

// Fetch book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE slug = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "<h1>Book not found</h1>";
    exit;
}

// Fetch related books (same category)
$relatedBooks = $pdo->query("SELECT slug, title, author, book_image, created_at FROM books WHERE book_type = '{$book['book_type']}' AND slug != '{$book['slug']}' AND status = 'active' ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Fetch latest books (last 30 days)
$latestBooks = $pdo->query("SELECT slug, title, author, book_image, book_type, created_at FROM books WHERE status='active' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Function to check if book is new (within last 7 days)
function isNewBook($created_at) {
    return strtotime($created_at) > strtotime('-7 days');
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

$book_image_url = "/book-image/".$book['book_image'];
// Meta values override
$pageTitle       = ($book['meta_title'] ?? $book['title']) . ' - ' . APP_NAME;
$pageDescription = mb_substr(strip_tags($book['meta_description'] ?? $book['description']), 0, 160);
$metaTitle       = $book['meta_title'] ?? $book['title'];
$keywords        = "Book Review, Competitive Exam Books, Study Materials, " . $book['author'] . ", " . $metaTitle;
$ogImage         = $book_image_url;
$canonicalUrl    = "https://fromcampus.com/books/book-details?slug=" . $slug;

$schema = [
  "@context" => "https://schema.org",
  "@type" => "Book",
  "mainEntityOfPage" => [
    "@type" => "WebPage",
    "@id" => $canonicalUrl
  ],
  "name" => $book['title'],
  "description" => strip_tags($book['meta_description'] ?? $book['description']),
  "image" => $ogImage,
  "author" => [
    "@type" => "Person",
    "name" => $book['author']
  ],
  "publisher" => [
    "@type" => "Organization",
    "name" => $book['publisher'] ?? "FromCampus"
  ],
  "datePublished" => !empty($book['publication_year']) ? $book['publication_year'] : date('Y', strtotime($book['created_at'])),
  "isbn" => $book['isbn'] ?? '',
  "offers" => [
    "@type" => "Offer",
    "url" => $canonicalUrl,
    "priceCurrency" => "INR",
    "availability" => "https://schema.org/InStock"
  ]
];

if (!empty($book['updated_at'])) {
  $schema["dateModified"] = date('c', strtotime($book['updated_at']));
}

// Markdown parser for content
require __DIR__ . '/../../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();
require_once __DIR__ . '/../../.hta_slug/_header.php';

// Generate current URL
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$shareTitle = urlencode($book['title']);
$shareText  = urlencode("Check out this book: " . $book['title'] . " by " . $book['author']);

// Fetch books for bottom filters
$upscBooks = $pdo->query("SELECT slug, title, author, created_at FROM books WHERE book_type = 'upsc' AND status = 'active' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$sscBooks = $pdo->query("SELECT slug, title, author, created_at FROM books WHERE book_type = 'ssc' AND status = 'active' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$bankingBooks = $pdo->query("SELECT slug, title, author, created_at FROM books WHERE book_type = 'banking' AND status = 'active' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$railwayBooks = $pdo->query("SELECT slug, title, author, created_at FROM books WHERE book_type = 'railway' AND status = 'active' ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>

<!-- Loading Placeholder (shown initially) -->
<div id="loading-placeholder" class="min-h-screen bg-gray-100 dark:bg-gray-900 p-4">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Breadcrumb placeholder -->
        <div class="lg:col-span-4">
            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
        </div>
        
        <!-- Main content placeholder -->
        <main class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <!-- Share buttons placeholder -->
                    <div class="flex flex-wrap items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-16 animate-pulse"></div>
                        <div class="flex items-center gap-2">
                            <?php for($i = 0; $i < 6; $i++): ?>
                            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-700 rounded-full animate-pulse"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <!-- Book info placeholder -->
                    <div class="flex flex-col md:flex-row gap-6 mb-6">
                        <div class="w-48 h-64 bg-gray-300 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                        <div class="flex-1">
                            <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-4 animate-pulse"></div>
                            <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                            <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                            <div class="space-y-2">
                                <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                                <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-5/6 animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content placeholder -->
                    <div class="mt-6 space-y-3">
                        <?php for($i = 0; $i < 10; $i++): ?>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Latest books placeholder (mobile) -->
            <div class="mt-8 lg:hidden">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-4">
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="p-4 border rounded-lg dark:border-gray-700">
                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
        
        <!-- Sidebar placeholder -->
        <aside class="hidden lg:block space-y-6">
            <!-- Latest books placeholder -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="h-6 bg-gray-300 dark:bg-gray-700 rounded w-1/3 mb-4 animate-pulse"></div>
                <div class="space-y-3">
                    <?php for($i = 0; $i < 5; $i++): ?>
                    <div class="p-3 border rounded dark:border-gray-700">
                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-1/2 mb-2 animate-pulse"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Actual Content (hidden initially) -->
<div id="actual-content" style="display: none;">
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8 p-4">
  <!-- Breadcrumb -->
  <div class="lg:col-span-4">
    <nav class="flex px-4" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
          <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Home</a>
          <svg class="w-3 h-3 text-gray-400 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
        </li>
        <li class="inline-flex items-center">
          <a href="/books" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Books</a>
        </li>
        <li aria-current="page">
          <div class="flex items-center">
            <svg class="w-3 h-3 text-gray-400 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400"><?= e($book['title']) ?></span>
          </div>
        </li>
      </ol>
    </nav>
  </div>

  <!-- Main Content -->
  <main class="lg:col-span-3">
    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
      <div class="p-6">
        <!-- Share Buttons -->
        <div class="flex flex-wrap items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Share:</span>
          <div class="flex items-center gap-2">
            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition" title="Share on Facebook">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>

            <!-- WhatsApp -->
            <a href="https://wa.me/?text=<?= urlencode($shareText . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="p-2 bg-green-500 text-white rounded-full hover:bg-green-600 transition" title="Share on WhatsApp">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.050-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
              </svg>
            </a>

            <!-- Telegram -->
            <a href="https://t.me/share/url?url=<?= urlencode($currentUrl) ?>&text=<?= urlencode($shareText) ?>" target="_blank" rel="noopener" class="p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition" title="Share on Telegram">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.999 15.169l-.398 5.601c.568 0 .812-.244 1.106-.537l2.663-2.544 5.522 4.034c1.012.559 1.731.266 1.988-.936l3.603-16.894.001-.001c.318-1.482-.535-2.06-1.516-1.702L1.502 9.75c-1.447.561-1.426 1.362-.246 1.727l5.548 1.730 12.878-8.143c.607-.367 1.162-.164.707.234"/></svg>
            </a>

            <!-- Copy Link -->
            <button onclick="copyToClipboard(this, '<?= $currentUrl ?>')" class="p-2 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition" title="Copy link">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Book Information -->
        <div class="flex flex-col md:flex-row gap-6 mb-6">
          <!-- Book Cover -->
          <div class="flex-shrink-0">
            <img src="<?= $book_image_url ?>" 
                 alt="<?= e($book['title']) ?>" 
                 class="w-48 h-64 object-cover rounded-lg shadow-md border dark:border-gray-600">
          </div>
          
          <!-- Book Details -->
          <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2"><?= e($book['title']) ?></h1>
            
            <div class="space-y-2 mb-4">
              <div class="flex items-center text-lg text-gray-700 dark:text-gray-300">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <strong class="mr-2">Author:</strong> <?= e($book['author']) ?>
              </div>
              
              <?php if ($book['publisher']): ?>
              <div class="flex items-center text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                </svg>
                <strong class="mr-2">Publisher:</strong> <?= e($book['publisher']) ?>
              </div>
              <?php endif; ?>
              
              <?php if ($book['publication_year']): ?>
              <div class="flex items-center text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
                <strong class="mr-2">Year:</strong> <?= e($book['publication_year']) ?>
              </div>
              <?php endif; ?>
              
              <?php if ($book['isbn']): ?>
              <div class="flex items-center text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                <strong class="mr-2">ISBN:</strong> <?= e($book['isbn']) ?>
              </div>
              <?php endif; ?>
            </div>

            <!-- Purchase Links -->
            <div class="flex flex-wrap gap-3 mt-6">
              <?php if ($book['amazon_link']): ?>
              <a href="<?= e($book['amazon_link']) ?>" target="_blank" rel="noopener" 
                 class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M10.605 0h2.606v5.125h-2.606V0zm0 6.259h2.606c1.188 0 2.238.857 2.238 2.239 0 1.361-1.05 2.24-2.238 2.24h-2.606V6.26zm2.608 3.229c.587 0 1.026-.439 1.026-1.04 0-.62-.439-1.04-1.026-1.04h-.872v2.08h.872z"/>
                </svg>
                Buy on Amazon
              </a>
              <?php endif; ?>
              
              <?php if ($book['flipkart_link']): ?>
              <a href="<?= e($book['flipkart_link']) ?>" target="_blank" rel="noopener" 
                 class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M11.52 23.75c.63 0 1.35-.41 1.8-1.07l7.65-11.8c.45-.66.69-1.41.69-2.08 0-1.85-1.5-3.35-3.35-3.35-.63 0-1.35.41-1.8 1.07l-7.65 11.8c-.45.66-.69 1.41-.69 2.08 0 1.85 1.5 3.35 3.35 3.35z"/>
                </svg>
                Buy on Flipkart
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div id="markdownContent" class="book-description mt-6 prose dark:prose-invert max-w-none text-justify leading-7">
          <?= $Parsedown->text($book['description']) ?>
        </div>
      </div>
    </article>

    <!-- Related Books -->
    <?php if (!empty($relatedBooks)): ?>
    <section class="mt-8">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
        </svg>
        Related Books
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($relatedBooks as $relatedBook): ?>
        <a href="book-details?slug=<?= e($relatedBook['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 group">
          <div class="flex items-start gap-3">
            <img src="<?= $book_image_url ?>" 
                 alt="<?= e($relatedBook['title']) ?>" 
                 class="w-12 h-16 object-cover rounded border dark:border-gray-600 flex-shrink-0">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-sm dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                <?= e($relatedBook['title']) ?>
              </h3>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">By <?= e($relatedBook['author']) ?></p>
              <p class="text-xs text-gray-500 dark:text-gray-500 mt-2"><?= formatDate($relatedBook['created_at']) ?></p>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Latest Books (Mobile - Bottom) -->
    <div class="mt-8 lg:hidden">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
            </svg>
            Latest Books
        </h2>
        
        <div class="space-y-4">
            <?php if (empty($latestBooks)): ?>
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm">No recent books available</p>
                </div>
            <?php else: ?>
                <?php foreach ($latestBooks as $latestBook): ?>
                <a href="book-details?slug=<?= e($latestBook['slug']) ?>" class="block p-4 border rounded-lg hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-700/50 group">
                    <div class="flex items-start gap-3">
                        <img src="<?= $book_image_url ?>" 
                             alt="<?= e($latestBook['title']) ?>" 
                             class="w-12 h-16 object-cover rounded border dark:border-gray-600 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="font-semibold text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    <?= e($latestBook['title']) ?>
                                </h3>
                                <?php if (isNewBook($latestBook['created_at'])): ?>
                                    <span class="blink-badge" style="background-color: #3b82f6;">NEW</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">By <?= e($latestBook['author']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-500"><?= formatDate($latestBook['created_at']) ?></p>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="mt-6 text-center">
            <a href="/books" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                View All Books
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </div>
  </main>

  <!-- Sidebar (Desktop) - Latest Books Section -->
  <aside class="hidden lg:block space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 h-96 flex flex-col">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
        </svg>
        Latest Books
      </h2>

      <!-- Scroll container -->
      <div class="relative flex-1 overflow-hidden">
        <div class="scroll-content space-y-3">
          <?php if (empty($latestBooks)): ?>
            <div class="text-center py-4 text-gray-500 dark:text-gray-400">
              <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2 text-xs">No recent books available</p>
            </div>
          <?php else: ?>
            <?php foreach ($latestBooks as $latestBook): ?>
              <a href="book-details?slug=<?= e($latestBook['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700 group">
                <div class="flex items-start gap-2">
                  <img src="<?= $book_image_url ?>" 
                       alt="<?= e($latestBook['title']) ?>" 
                       class="w-10 h-14 object-cover rounded border dark:border-gray-600 flex-shrink-0">
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start mb-1">
                      <h3 class="font-medium text-sm dark:text-white line-clamp-2 flex-1 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                        <?= e($latestBook['title']) ?>
                      </h3>
                      <?php if (isNewBook($latestBook['created_at'])): ?>
                        <span class="blink-badge" style="background-color: #3b82f6;">NEW</span>
                      <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">By <?= e($latestBook['author']) ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                      <?= formatDate($latestBook['created_at']) ?>
                    </p>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Footer fixed -->
      <div class="mt-4 text-center">
        <a href="/books" class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium">
          View All Books
          <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </a>
      </div>
    </div>
  </aside>
</div>

<!-- Bottom Filter Sections -->
<div class="max-w-7xl mx-auto mt-12 p-4">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- UPSC Books -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
        </svg>
        UPSC Books      
      </h2>
      <div class="space-y-3">
        <?php foreach ($upscBooks as $upscBook): ?>
        <a href="book-details?slug=<?= e($upscBook['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($upscBook['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">By <?= e($upscBook['author']) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/books" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All UPSC Books →
      </a>
    </div>

    <!-- SSC Books -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        SSC Books
      </h2>
      <div class="space-y-3">
        <?php foreach ($sscBooks as $sscBook): ?>
        <a href="book-details?slug=<?= e($sscBook['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($sscBook['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">By <?= e($sscBook['author']) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/books" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All SSC Books →
      </a>
    </div>

    <!-- Banking Books -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
        </svg>
        Banking Books
      </h2>
      <div class="space-y-3">
        <?php foreach ($bankingBooks as $bankingBook): ?>
        <a href="book-details?slug=<?= e($bankingBook['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($bankingBook['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">By <?= e($bankingBook['author']) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/books" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All Banking Books →
      </a>
    </div>

    <!-- Railway Books -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
      <h2 class="text-xl font-bold dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
        </svg>
        Railway Books
      </h2>
      <div class="space-y-3">
        <?php foreach ($railwayBooks as $railwayBook): ?>
        <a href="book-details?slug=<?= e($railwayBook['slug']) ?>" class="block p-3 border rounded hover:shadow-sm transition dark:border-gray-700 dark:hover:bg-gray-700">
          <h3 class="font-medium text-sm dark:text-white line-clamp-2"><?= e($railwayBook['title']) ?></h3>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">By <?= e($railwayBook['author']) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <a href="/books" class="block text-center mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
        View All Railway Books →
      </a>
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
    document.getElementById('actual-content').style.display = 'block';
  }, 300);
});

function copyToClipboard(button, text) {
  navigator.clipboard.writeText(text).then(function() {
    const originalHtml = button.innerHTML;

    button.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
    button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
    button.classList.add('bg-green-600', 'hover:bg-green-700');

    setTimeout(() => {
      button.innerHTML = originalHtml;
      button.classList.remove('bg-green-600', 'hover:bg-green-700');
      button.classList.add('bg-gray-600', 'hover:bg-gray-700');
    }, 2000);
  }).catch(function(err) {
    console.error('Could not copy text: ', err);
    alert('Failed to copy link. Please try again.');
  });
}
</script>

<style>
/* Animation for placeholder */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
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

@keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.5; }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes scrollContent {
  0%   { transform: translateY(0); }
  100% { transform: translateY(-50%); }
}

.scroll-content {
  display: flex;
  flex-direction: column;
  animation: scrollContent 25s linear infinite;
}

.scroll-content:hover {
  animation-play-state: paused;
}

</style>
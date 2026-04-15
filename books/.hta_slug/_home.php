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

// Get all books with category information
$allBooks = [];
try {
    $stmt = $pdo->query("
        SELECT slug, title, description, author, publisher, book_image, amazon_link, flipkart_link, created_at, book_type 
        FROM books 
        WHERE status = 'active' 
        ORDER BY created_at DESC
    ");
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If query fails, continue with empty array
    $allBooks = [];
}

// Function to check if book is new (within last 7 days)
function isNewBook($created_at) {
    return strtotime($created_at) > strtotime('-7 days');
}

// Function to create plain text excerpt from markdown
function markdownExcerpt($markdown, $Parsedown, $length = 100) {
    $text = strip_tags($Parsedown->text($markdown));
    return mb_substr($text, 0, $length) . (mb_strlen($text) > $length ? '...' : '');
}

// Function to get default book image
function getBookImage($book_image, $title) {
    if ($book_image && file_exists(__DIR__ . '/../../' . ltrim($book_image, '/'))) {
        return BASE_URL . $book_image;
    }
    return BASE_URL . '/assets/images/default-book-cover.jpg';
}

// Function to get color classes
function getColorClasses($color) {
    $colors = [
        'blue' => ['bg' => 'from-blue-600 to-blue-700', 'text' => 'text-blue-600', 'dark_text' => 'dark:text-blue-400'],
        'green' => ['bg' => 'from-green-600 to-green-700', 'text' => 'text-green-600', 'dark_text' => 'dark:text-green-400'],
        'yellow' => ['bg' => 'from-yellow-600 to-yellow-700', 'text' => 'text-yellow-600', 'dark_text' => 'dark:text-yellow-400'],
        'red' => ['bg' => 'from-red-600 to-red-700', 'text' => 'text-red-600', 'dark_text' => 'dark:text-red-400'],
        'purple' => ['bg' => 'from-purple-600 to-purple-700', 'text' => 'text-purple-600', 'dark_text' => 'dark:text-purple-400'],
        'indigo' => ['bg' => 'from-indigo-600 to-indigo-700', 'text' => 'text-indigo-600', 'dark_text' => 'dark:text-indigo-400'],
        'pink' => ['bg' => 'from-pink-600 to-pink-700', 'text' => 'text-pink-600', 'dark_text' => 'dark:text-pink-400'],
        'teal' => ['bg' => 'from-teal-600 to-teal-700', 'text' => 'text-teal-600', 'dark_text' => 'dark:text-teal-400'],
        'orange' => ['bg' => 'from-orange-600 to-orange-700', 'text' => 'text-orange-600', 'dark_text' => 'dark:text-orange-400'],
        'gray' => ['bg' => 'from-gray-600 to-gray-700', 'text' => 'text-gray-600', 'dark_text' => 'dark:text-gray-400']
    ];
    return $colors[$color] ?? $colors['blue'];
}
?>

<!-- Loading Placeholder -->
<div id="loading-placeholder" class="container mx-auto min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="max-w-8xl mx-auto p-4">
        <div class="animate-pulse">
            <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-1/4 mb-8"></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-full mb-4"></div>
                <div class="space-y-3">
                    <?php for($i = 0; $i < 10; $i++): ?>
                    <div class="h-12 bg-gray-300 dark:bg-gray-700 rounded"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actual Content -->
<div id="actual-content" class="hidden container mx-auto min-h-screen">
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

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Recommended Books</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Find the best books for competitive exams recommended by experts and toppers</p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="searchInput" placeholder="Search books by title, author, publisher..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div>
                    <select id="categoryFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Categories</option>
                        <?php foreach ($bookCategories as $category): ?>
                        <option value="<?= e($category['category_slug']) ?>"><?= e($category['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sort -->
                <div>
                    <select id="sortFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="title">Title (A-Z)</option>
                        <option value="author">Author (A-Z)</option>
                    </select>
                </div>
            </div>
            
            <!-- Active Filters -->
            <div id="activeFilters" class="mt-4 flex flex-wrap gap-2 hidden">
                <!-- Filter tags will be inserted here -->
            </div>
        </div>

        <!-- Results Summary -->
        <div class="mb-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing <span id="resultCount">0</span> books
            </div>
        </div>

        <!-- Books Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full books-table">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Book
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Author
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Publisher
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody id="booksTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Books will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- No Results -->
            <div id="noResults" class="hidden p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No books found matching your criteria</p>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="mt-6 flex justify-center">
            <!-- Pagination will be inserted here -->
        </div>
    </div>
</div>

<script type="application/ld+json">
    <?= json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) ?>
</script>

<script>
// Books data
const booksData = <?= json_encode($allBooks) ?>;
const categories = <?= json_encode($bookCategories) ?>;

let filteredBooks = [...booksData];
let currentPage = 1;
const itemsPerPage = 25;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.getElementById('loading-placeholder').style.display = 'none';
        document.getElementById('actual-content').classList.remove('hidden');
        initializeBooksTable();
    }, 300);
});

function initializeBooksTable() {
    renderBooksTable();
    setupEventListeners();
}

function setupEventListeners() {
    // Search
    document.getElementById('searchInput').addEventListener('input', filterBooks);
    
    // Category filter
    document.getElementById('categoryFilter').addEventListener('change', filterBooks);
    
    // Sort
    document.getElementById('sortFilter').addEventListener('change', filterBooks);
}

function filterBooks() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const sortBy = document.getElementById('sortFilter').value;
    
    // Filter
    filteredBooks = booksData.filter(book => {
        const matchesSearch = !searchTerm || 
            book.title.toLowerCase().includes(searchTerm) ||
            book.author.toLowerCase().includes(searchTerm) ||
            (book.publisher && book.publisher.toLowerCase().includes(searchTerm));
        
        const matchesCategory = !categoryFilter || book.book_type === categoryFilter;
        
        return matchesSearch && matchesCategory;
    });
    
    // Sort
    switch(sortBy) {
        case 'newest':
            filteredBooks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'oldest':
            filteredBooks.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'title':
            filteredBooks.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'author':
            filteredBooks.sort((a, b) => a.author.localeCompare(b.author));
            break;
    }
    
    currentPage = 1;
    renderBooksTable();
    updateActiveFilters();
}

function renderBooksTable() {
    const tbody = document.getElementById('booksTableBody');
    const noResults = document.getElementById('noResults');
    const resultCount = document.getElementById('resultCount');
    
    resultCount.textContent = filteredBooks.length;
    
    if (filteredBooks.length === 0) {
        tbody.innerHTML = '';
        noResults.classList.remove('hidden');
        document.getElementById('pagination').innerHTML = '';
        return;
    }
    
    noResults.classList.add('hidden');
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedBooks = filteredBooks.slice(startIndex, endIndex);
    
    // Render table rows
    tbody.innerHTML = paginatedBooks.map(book => {
        const category = categories.find(cat => cat.category_slug === book.book_type);
        const colorClasses = getColorClasses(category?.color || 'blue');
        const isNew = isNewBook(book.created_at);
        
        return `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer" onclick="window.location.href='${book.slug}'">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <img src="${getBookImage(book.book_image, book.title)}" alt="${book.title}" 
                             class="w-12 h-16 object-cover rounded border dark:border-gray-600 mr-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                ${book.title}
                                ${isNew ? '<span class="ml-2 inline-block px-2 py-1 text-xs font-semibold text-white bg-gradient-to-r ' + colorClasses.bg + ' rounded">NEW</span>' : ''}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                ${markdownExcerpt(book.description, 100)}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    ${book.author || 'N/A'}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    ${book.publisher || '-'}
                </td>
                <td class="px-6 py-4">
                    <span class="category-badge inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-gradient-to-r ${colorClasses.bg} text-white whitespace-nowrap">
                        ${category?.category_name || book.book_type || 'Uncategorized'}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <button onclick="event.stopPropagation(); window.location.href='${book.slug}'" class="view-details-btn inline-flex items-center px-3 py-2 rounded text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors whitespace-nowrap">
                        View Details
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    renderPagination();
}

function renderPagination() {
    const pagination = document.getElementById('pagination');
    const totalPages = Math.ceil(filteredBooks.length / itemsPerPage);
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '<div class="flex items-center space-x-2">';
    
    // Previous button
    if (currentPage > 1) {
        paginationHTML += `
            <button onclick="changePage(${currentPage - 1})" 
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                Previous
            </button>
        `;
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const isActive = i === currentPage;
            paginationHTML += `
                <button onclick="changePage(${i})" 
                        class="px-3 py-1 text-sm ${isActive ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600'} rounded">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            paginationHTML += '<span class="px-2 text-gray-500">...</span>';
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHTML += `
            <button onclick="changePage(${currentPage + 1})" 
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                Next
            </button>
        `;
    }
    
    paginationHTML += '</div>';
    pagination.innerHTML = paginationHTML;
}

function changePage(page) {
    currentPage = page;
    renderBooksTable();
    document.getElementById('actual-content').scrollIntoView({ behavior: 'smooth' });
}

function updateActiveFilters() {
    const container = document.getElementById('activeFilters');
    const searchTerm = document.getElementById('searchInput').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    const category = categories.find(cat => cat.category_slug === categoryFilter);
    
    if (!searchTerm && !categoryFilter) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    
    let filtersHTML = '';
    
    if (searchTerm) {
        filtersHTML += `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                Search: ${searchTerm}
                <button onclick="clearSearch()" class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </span>
        `;
    }
    
    if (categoryFilter && category) {
        filtersHTML += `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                Category: ${category.category_name}
                <button onclick="clearCategory()" class="ml-2 text-green-600 hover:text-green-800 dark:text-green-300 dark:hover:text-green-100">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </span>
        `;
    }
    
    container.innerHTML = filtersHTML + `
        <button onclick="clearAllFilters()" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
            Clear All
        </button>
    `;
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    filterBooks();
}

function clearCategory() {
    document.getElementById('categoryFilter').value = '';
    filterBooks();
}

function clearAllFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    filterBooks();
}

// Helper functions (mirroring PHP functions)
function isNewBook(created_at) {
    return new Date(created_at) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
}

function getBookImage(book_image, title) {
    // This would need to be implemented properly
    return '<?= BASE_URL ?>/assets/images/default-book-cover.jpg';
}

function markdownExcerpt(markdown, length) {
    if (!markdown) return '';
    // Simple text extraction (in real implementation, you'd need proper markdown parsing)
    const text = markdown.replace(/[#*`_~]/g, '').replace(/\[([^\]]+)\]\([^)]+\)/g, '$1');
    return text.length > length ? text.substring(0, length) + '...' : text;
}

function getColorClasses(color) {
    const colors = {
        'blue': { bg: 'from-blue-600 to-blue-700', text: 'text-blue-600', dark_text: 'dark:text-blue-400' },
        'green': { bg: 'from-green-600 to-green-700', text: 'text-green-600', dark_text: 'dark:text-green-400' },
        'yellow': { bg: 'from-yellow-600 to-yellow-700', text: 'text-yellow-600', dark_text: 'dark:text-yellow-400' },
        'red': { bg: 'from-red-600 to-red-700', text: 'text-red-600', dark_text: 'dark:text-red-400' },
        'purple': { bg: 'from-purple-600 to-purple-700', text: 'text-purple-600', dark_text: 'dark:text-purple-400' },
        'indigo': { bg: 'from-indigo-600 to-indigo-700', text: 'text-indigo-600', dark_text: 'dark:text-indigo-400' },
        'pink': { bg: 'from-pink-600 to-pink-700', text: 'text-pink-600', dark_text: 'dark:text-pink-400' },
        'teal': { bg: 'from-teal-600 to-teal-700', text: 'text-teal-600', dark_text: 'dark:text-teal-400' },
        'orange': { bg: 'from-orange-600 to-orange-700', text: 'text-orange-600', dark_text: 'dark:text-orange-400' },
        'gray': { bg: 'from-gray-600 to-gray-700', text: 'text-gray-600', dark_text: 'dark:text-gray-400' }
    };
    return colors[color] || colors['blue'];
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

/* Fix table layout issues */
.books-table {
    table-layout: fixed;
    min-width: 800px; /* Ensure minimum width for all content */
}

.books-table th:nth-child(1) {
    width: 35%; /* Book column */
    min-width: 300px;
}

.books-table th:nth-child(2) {
    width: 15%; /* Author column */
    min-width: 120px;
}

.books-table th:nth-child(3) {
    width: 15%; /* Publisher column */
    min-width: 120px;
}

.books-table th:nth-child(4) {
    width: 15%; /* Category column */
    min-width: 120px;
}

.books-table th:nth-child(5) {
    width: 10%; /* Added column */
    min-width: 80px;
}

.books-table th:nth-child(6) {
    width: 10%; /* Actions column */
    min-width: 120px;
}

/* Ensure category badges don't overflow */
.category-badge {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Fix View Details button */
.view-details-btn {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.2s ease;
}

.view-details-btn:hover {
    transform: translateX(2px);
}

</style>
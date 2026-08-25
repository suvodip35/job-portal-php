<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';

require_admin();

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE :q OR description LIKE :q)";
    $params[':q'] = "%$search%";
}

if ($category) {
    $where[] = "category = :category";
    $params[':category'] = $category;
}

$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM current_affairs $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Fetch records
$stmt = $pdo->prepare("SELECT * FROM current_affairs $whereSql ORDER BY event_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$articles = $stmt->fetchAll();

$categories = ['National', 'International', 'Sports', 'Economy', 'Science & Tech', 'Appointments', 'Obituaries', 'Awards & Honours'];
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Current Affairs Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Articles: <?= $total ?></p>
        </div>
        <a href="/adminqeIUgwefgWEOAjx/add_current_affairs.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Article
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search title or content..." class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <select name="category" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded dark:bg-gray-700 hover:bg-gray-900 w-full md:w-auto">Filter</button>
                <a href="/adminqeIUgwefgWEOAjx/current_affairs.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300 w-full md:w-auto text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Articles Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Thumbnail</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Event Date</th>
                    <th class="px-4 py-3">Views</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No Current Affairs articles found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $item): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-3">
                                <?php if (!empty($item['thumbnail'])): ?>
                                    <img src="<?= e($item['thumbnail']) ?>" alt="" class="w-12 h-12 object-cover rounded shadow-sm">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-xs text-gray-400">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white max-w-xs truncate">
                                <a href="/current-affairs/<?= e($item['slug']) ?>" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400">
                                    <?= e($item['title']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <?= e($item['category'] ?? 'General') ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <?= $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : 'N/A' ?>
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold">
                                <?= number_format($item['views']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs <?= $item['status'] === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="/adminqeIUgwefgWEOAjx/edit_current_affairs.php?id=<?= $item['id'] ?>" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                <a href="/adminqeIUgwefgWEOAjx/delete_current_affairs.php?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to delete this article?')" class="text-red-600 dark:text-red-400 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center gap-2 mt-6">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>" class="px-3 py-1 rounded border <?= $i === $page ? 'bg-blue-600 text-white dark:bg-blue-700' : 'bg-white dark:bg-gray-800 dark:text-gray-300' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../.hta_slug/_footer.php'; ?>

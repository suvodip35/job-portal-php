<?php
// get-books-by-category.php
header('Content-Type: application/json');

// Database connection
require_once '.hta_config/config.php';

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get category slug from GET parameter
if (!isset($_GET['categorySlug']) || empty($_GET['categorySlug'])) {
    echo json_encode([]);
    exit;
}

$category_slug = $_GET['categorySlug'];

// Query to get books by category
$query = "SELECT * FROM books WHERE book_type = :category_slug AND status = 'active' ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute(['category_slug' => $category_slug]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($books);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
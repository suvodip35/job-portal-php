<?php
// get-save-job.php (alternative version)
header('Content-Type: application/json');

// Database connection
require_once '.hta_config/config.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['slugs']) || !is_array($input['slugs']) || empty($input['slugs'])) {
    echo json_encode([]);
    exit;
}

// Create placeholders for the query
$placeholders = implode(',', array_fill(0, count($input['slugs']), '?'));

// Query to get saved jobs
$query = "SELECT job_id, job_title, job_title_slug, company_name, location, description, posted_date, thumbnail FROM jobs WHERE job_title_slug IN ($placeholders) AND status = 'published' ORDER BY posted_date DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($input['slugs']);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($jobs);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
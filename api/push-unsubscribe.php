<?php
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../.hta_config/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['endpoint'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing endpoint']);
    exit;
}

$endpoint = $input['endpoint'];

try {
    // Deactivate subscription (don't delete to maintain logs)
    $updateStmt = $pdo->prepare("
        UPDATE push_subscriptions 
        SET is_active = 0 
        WHERE endpoint = ?
    ");
    $updateStmt->execute([$endpoint]);
    
    $rowCount = $updateStmt->rowCount();
    
    if ($rowCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Subscription deactivated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Subscription not found but operation completed'
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in push-unsubscribe.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in push-unsubscribe.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

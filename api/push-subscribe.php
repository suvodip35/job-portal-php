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
if (!isset($input['endpoint']) || !isset($input['keys']['p256dh']) || !isset($input['keys']['auth'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required subscription data']);
    exit;
}

$endpoint = $input['endpoint'];
$p256dhKey = $input['keys']['p256dh'];
$authKey = $input['keys']['auth'];
$userAgent = $input['userAgent'] ?? '';
$ipAddress = $input['ipAddress'] ?? $_SERVER['REMOTE_ADDR'];

try {
    // Check if subscription already exists
    $checkStmt = $pdo->prepare("SELECT id FROM push_subscriptions WHERE endpoint = ?");
    $checkStmt->execute([$endpoint]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // Update existing subscription
        $updateStmt = $pdo->prepare("
            UPDATE push_subscriptions 
            SET p256dh_key = ?, auth_key = ?, user_agent = ?, ip_address = ?, last_used_at = NOW(), is_active = 1 
            WHERE endpoint = ?
        ");
        $updateStmt->execute([$p256dhKey, $authKey, $userAgent, $ipAddress, $endpoint]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'subscription_id' => $existing['id']
        ]);
    } else {
        // Insert new subscription
        $insertStmt = $pdo->prepare("
            INSERT INTO push_subscriptions (endpoint, p256dh_key, auth_key, user_agent, ip_address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([$endpoint, $p256dhKey, $authKey, $userAgent, $ipAddress]);
        
        $subscriptionId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription created successfully',
            'subscription_id' => $subscriptionId
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in push-subscribe.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in push-subscribe.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

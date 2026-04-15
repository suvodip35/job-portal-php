<?php
/**
 * Save FCM Token
 * Saves Firebase Cloud Messaging token for push notifications
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once '../.hta_config/config.php';
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['token'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token is required']);
        exit;
    }
    
    $token = $input['token'];
    $userAgent = $input['user_agent'] ?? '';
    $timestamp = $input['timestamp'] ?? time();
    
    // Check if token already exists
    $stmt = $pdo->prepare("SELECT id FROM fcm_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing token
        $stmt = $pdo->prepare("
            UPDATE fcm_tokens 
            SET user_agent = ?, last_active = NOW(), is_active = 1 
            WHERE token = ?
        ");
        $stmt->execute([$userAgent, $token]);
        
        echo json_encode(['success' => true, 'message' => 'Token updated successfully']);
    } else {
        // Insert new token
        $stmt = $pdo->prepare("
            INSERT INTO fcm_tokens (token, user_agent, created_at, last_active, is_active) 
            VALUES (?, ?, NOW(), NOW(), 1)
        ");
        $stmt->execute([$token, $userAgent]);
        
        echo json_encode(['success' => true, 'message' => 'Token saved successfully']);
    }
    
} catch (Exception $e) {
    error_log("Error saving FCM token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>

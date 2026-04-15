<?php
/**
 * Fix VAPID JWT Authentication Issue
 */

echo "<h1>Fix VAPID JWT Authentication</h1>";

// Check current VAPID keys in database
try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Current VAPID Keys in Database</h2>";
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($vapid) {
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Found VAPID Keys:</h3>";
        echo "<p><strong>Public Key:</strong> " . htmlspecialchars($vapid['public_key']) . "</p>";
        echo "<p><strong>Private Key:</strong> " . htmlspecialchars(substr($vapid['private_key'], 0, 20)) . "...</p>";
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($vapid['subject']) . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>No VAPID keys found in database</p>";
    }
    
    // Update VAPID keys with your working keys
    echo "<h2>Updating VAPID Keys</h2>";
    $yourKeys = [
        'public_key' => 'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ',
        'private_key' => '2KObtM-HzMp4xmQdk03TzkaCaYtNQWoIigB62q7FsHE',
        'subject' => 'mailto:teamfromcampus@gmail.com'
    ];
    
    $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE public_key = VALUES(public_key), private_key = VALUES(private_key), subject = VALUES(subject)");
    $stmt->execute([
        $yourKeys['public_key'],
        $yourKeys['private_key'],
        $yourKeys['subject']
    ]);
    
    echo "<p style='color: green;'>VAPID keys updated in database</p>";
    
    // Verify the update
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $updatedVapid = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Updated VAPID Keys:</h3>";
    echo "<p><strong>Public Key:</strong> " . htmlspecialchars($updatedVapid['public_key']) . "</p>";
    echo "<p><strong>Subject:</strong> " . htmlspecialchars($updatedVapid['subject']) . "</p>";
    echo "</div>";
    
    // Test JWT generation
    echo "<h2>Test JWT Generation</h2>";
    
    // Create a simple test JWT (this is what the service should be doing)
    function createTestJWT($publicKey, $privateKey, $audience) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'ES256']);
        $payload = json_encode([
            'aud' => $audience,
            'exp' => time() + 3600, // 1 hour expiry
            'sub' => 'mailto:teamfromcampus@gmail.com'
        ]);
        
        // This is a simplified JWT for testing
        // In reality, this needs proper ES256 signature
        return base64_encode($header) . '.' . base64_encode($payload) . '.signature_placeholder';
    }
    
    $testJWT = createTestJWT($updatedVapid['public_key'], $updatedVapid['private_key'], 'https://fcm.googleapis.com');
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Test JWT Structure:</h3>";
    echo "<pre>" . htmlspecialchars($testJWT) . "</pre>";
    echo "</div>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li><strong>Clear Browser Cache:</strong> Press Ctrl+F5</li>";
    echo "<li><strong>Test Again:</strong> <a href='/test-complete-system.php' target='_blank'>Test Complete System</a></li>";
    echo "<li><strong>Send Test Notification:</strong> Use the test button</li>";
    echo "<li><strong>Check for JWT Errors:</strong> Monitor console and logs</li>";
    echo "</ol>";
    
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>JWT Error Solutions:</h3>";
    echo "<ul>";
    echo "<li><strong>Key Mismatch:</strong> Ensure database keys match what PushNotificationService uses</li>";
    echo "<li><strong>Invalid Format:</strong> Keys must be proper base64-encoded VAPID keys</li>";
    echo "<li><strong>Signature Error:</strong> JWT must be properly signed with ES256 algorithm</li>";
    echo "<li><strong>Audience Mismatch:</strong> JWT audience must match push service endpoint</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

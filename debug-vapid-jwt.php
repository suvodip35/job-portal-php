<?php
/**
 * Debug and Fix VAPID JWT Issues
 */

echo "<h1>Debug VAPID JWT Authentication</h1>";

// Check current VAPID keys in database
try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Current Database VAPID Keys</h2>";
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($vapid) {
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Database Keys:</h3>";
        echo "<p><strong>Public Key:</strong> " . htmlspecialchars($vapid['public_key']) . "</p>";
        echo "<p><strong>Private Key:</strong> " . htmlspecialchars(substr($vapid['private_key'], 0, 20)) . "...</p>";
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($vapid['subject']) . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>No VAPID keys found in database</p>";
    }
    
    // Test JWT generation with current keys
    echo "<h2>Test JWT Generation</h2>";
    
    function createTestJWT($publicKey, $privateKey, $audience) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'ES256']);
        $payload = json_encode([
            'aud' => $audience,
            'exp' => time() + 3600, // 1 hour expiry
            'sub' => 'mailto:teamfromcampus@gmail.com'
        ]);
        
        // Create proper JWT signature
        $dataToSign = base64url_encode($header) . '.' . base64url_encode($payload);
        $signature = hash_hmac('sha256', $dataToSign, $privateKey, true);
        $signature = base64url_encode($signature);
        
        return base64url_encode($header) . '.' . base64url_encode($payload) . '.' . $signature;
    }
    
    if ($vapid) {
        $testJWT = createTestJWT($vapid['public_key'], $vapid['private_key'], 'https://fcm.googleapis.com');
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Test JWT Created:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; word-break: break-all;'>" . htmlspecialchars($testJWT) . "</pre>";
        echo "</div>";
        
        // Verify JWT structure
        $jwtParts = explode('.', $testJWT);
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>JWT Parts Analysis:</h3>";
        echo "<p><strong>Header:</strong> " . htmlspecialchars($jwtParts[0]) . "</p>";
        echo "<p><strong>Payload:</strong> " . htmlspecialchars($jwtParts[1]) . "</p>";
        echo "<p><strong>Signature:</strong> " . htmlspecialchars($jwtParts[2]) . "</p>";
        echo "</div>";
    }
    
    // Check if PushNotificationService is using hardcoded keys
    echo "<h2>PushNotificationService Analysis</h2>";
    $pushServiceFile = __DIR__ . '/lib/PushNotificationService.php';
    $pushServiceContent = file_get_contents($pushServiceFile);
    
    if (strpos($pushServiceContent, 'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ') !== false) {
        echo "<p style='color: green;'>✅ PushNotificationService has your VAPID keys</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ PushNotificationService might be using fallback keys</p>";
    }
    
    // Check if database has the right keys
    echo "<h2>Solution Steps</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Step 1: Update Database</h3>";
    echo "<p>Run this script to ensure database has correct keys:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "-- Update vapid_keys table";
    echo "-- SET public_key = 'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ'";
    echo "-- SET private_key = '2KObtM-HzMp4xmQdk03TzkaCaYtNQWoIigB62q7FsHE'";
    echo "-- SET subject = 'mailto:teamfromcampus@gmail.com'";
    echo "</pre>";
    
    echo "<h3>Step 2: Clear Everything</h3>";
    echo "<ul>";
    echo "<li>Clear browser cache (Ctrl+F5)</li>";
    echo "<li>Restart web server</li>";
    echo "<li>Check PHP error logs</li>";
    echo "</ul>";
    
    echo "<h3>Step 3: Test Again</h3>";
    echo "<p>After updating database, test notifications again.</p>";
    
    echo "<h3>Common JWT Issues</h3>";
    echo "<ul>";
    echo "<li><strong>Key Mismatch:</strong> Database keys don't match PushNotificationService</li>";
    echo "<li><strong>Invalid Format:</strong> Keys not properly base64 encoded</li>";
    echo "<li><strong>Wrong Algorithm:</strong> Using HMAC instead of ES256 signature</li>";
    echo "<li><strong>Expired Token:</strong> JWT expiry time passed</li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

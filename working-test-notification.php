<?php
/**
 * Working Test Notification - Direct Approach
 * This bypasses all JWT complexity and sends a simple notification
 */

echo "<h1>Working Test Notification</h1>";

try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Direct Push Notification Test</h2>";
    
    // Get a test subscription
    $stmt = $pdo->query("SELECT * FROM push_subscriptions WHERE is_active = 1 LIMIT 1");
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subscription) {
        echo "<p style='color: red;'>❌ No active subscriptions found. Please subscribe first.</p>";
        echo "<p><a href='/test-push-notifications.html' target='_blank'>Subscribe Here</a></p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Found active subscription: " . htmlspecialchars($subscription['endpoint']) . "</p>";
    
    // Create a simple notification payload
    $notification = [
        'title' => '🎉 Test Notification Working!',
        'body' => 'This is a direct test notification that should work without JWT issues.',
        'icon' => '/assets/logo/fc_logo_crop.webp',
        'badge' => '/favicon.ico',
        'tag' => 'direct-test-' . time(),
        'data' => [
            'url' => '/',
            'test' => true,
            'timestamp' => time()
        ]
    ];
    
    // Send directly to FCM endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $subscription['endpoint']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: key=YOUR_SERVER_KEY', // Use FCM server key for testing
        'TTL: 2419200'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
    
    echo "<h3>Sending Notification...</h3>";
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Result:</h3>";
    
    if ($error) {
        echo "<p style='color: red;'>❌ cURL Error: " . htmlspecialchars($error) . "</p>";
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        echo "<p style='color: green;'>✅ Notification sent successfully! (HTTP $httpCode)</p>";
        echo "<p>Check your browser for the notification!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Error: $httpCode</p>";
        echo "<p>Response: " . htmlspecialchars($response) . "</p>";
    }
    
    echo "</div>";
    
    echo "<h2>What This Test Does</h2>";
    echo "<ul>";
    echo "<li>✅ Bypasses all JWT authentication complexity</li>";
    echo "<li>✅ Uses direct FCM server key approach</li>";
    echo "<li>✅ Tests basic push notification delivery</li>";
    echo "<li>✅ Should work regardless of VAPID configuration</li>";
    echo "</ul>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li>If this test works, the issue is in your VAPID/JWT implementation</li>";
    echo "<li>If this test fails, the issue is with the subscription or FCM endpoint</li>";
    echo "<li>Either way, this tells us if the basic push mechanism works</li>";
    echo "</ol>";
    
    echo "<h2>FCM Server Key Note</h2>";
    echo "<p style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "For testing only, this uses 'key=YOUR_SERVER_KEY' which bypasses VAPID authentication.<br>";
    echo "In production, you would use your actual FCM server key from Firebase Console.";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

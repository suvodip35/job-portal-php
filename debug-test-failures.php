<?php
/**
 * Debug Test Failures
 * Analyzes why backend tests are failing
 */

echo "<h1>Debug Test Failures Analysis</h1>";

try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Step 1: System Status</h2>";
    
    // Check database connection
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>Database connection: OK</p>";
    
    // Check VAPID keys
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($vapid) {
        echo "<p style='color: green;'>VAPID keys: Found</p>";
        echo "<pre>Public Key: " . htmlspecialchars($vapid['public_key']) . "</pre>";
        echo "<pre>Subject: " . htmlspecialchars($vapid['subject']) . "</pre>";
    } else {
        echo "<p style='color: red;'>VAPID keys: Not found</p>";
    }
    
    // Check subscriptions
    $stmt = $pdo->query("SELECT * FROM push_subscriptions WHERE is_active = 1");
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Active subscriptions: " . count($subscriptions) . "</p>";
    
    if (empty($subscriptions)) {
        echo "<p style='color: red;'>No active subscriptions found - This is why tests are failing!</p>";
        echo "<p>You need to subscribe first before testing notifications.</p>";
        echo "<p><a href='/test-push-notifications.html' target='_blank'>Subscribe Here</a></p>";
    } else {
        echo "<p>Subscriptions found:</p>";
        foreach ($subscriptions as $sub) {
            echo "<pre>" . htmlspecialchars($sub['endpoint']) . "</pre>";
        }
    }
    
    echo "<h2>Step 2: Test with Detailed Error Logging</h2>";
    
    if (!empty($subscriptions) && file_exists(__DIR__ . '/lib/PushNotificationServiceWorking.php')) {
        require_once __DIR__ . '/lib/PushNotificationServiceWorking.php';
        $pushService = new PushNotificationServiceWorking($pdo);
        
        echo "<h3>Testing with Error Details</h3>";
        
        // Test with detailed error reporting
        $result = $pushService->sendCustomNotification(
            'Debug Test Notification',
            'This is a debug test with detailed error logging',
            '/',
            ['debug_test' => true, 'timestamp' => time()]
        );
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>Detailed Result:</h4>";
        echo "<p><strong>Success:</strong> " . ($result['success'] ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Sent Count:</strong> " . ($result['sent_count'] ?? 0) . "</p>";
        echo "<p><strong>Failed Count:</strong> " . ($result['failed_count'] ?? 0) . "</p>";
        echo "<p><strong>Total Subscribers:</strong> " . ($result['total_subscribers'] ?? 0) . "</p>";
        
        if (!empty($result['errors'])) {
            echo "<h5>Specific Errors:</h5>";
            echo "<ul>";
            foreach ($result['errors'] as $error) {
                echo "<li style='color: red;'>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
        
        if (isset($result['error'])) {
            echo "<h5>General Error:</h5>";
            echo "<p style='color: red;'>" . htmlspecialchars($result['error']) . "</p>";
        }
        
        echo "</div>";
        
        // Check recent logs
        echo "<h3>Recent Notification Logs</h3>";
        $stmt = $pdo->query("SELECT * FROM push_notification_logs ORDER BY sent_at DESC LIMIT 5");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($logs)) {
            echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><th>Type</th><th>Status</th><th>Error</th><th>Time</th></tr>";
            foreach ($logs as $log) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($log['notification_type']) . "</td>";
                echo "<td style='color: " . ($log['status'] == 'sent' ? 'green' : 'red') . ";'>" . htmlspecialchars($log['status']) . "</td>";
                echo "<td>" . htmlspecialchars($log['error_message'] ?? 'None') . "</td>";
                echo "<td>" . $log['sent_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No recent logs found</p>";
        }
        
    } else {
        echo "<p style='color: red;'>Cannot test - no subscriptions or working service file missing</p>";
    }
    
    echo "<h2>Step 3: Common Issues & Solutions</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Why Tests Might Be Failing:</h3>";
    
    echo "<h4>1. No Active Subscriptions</h4>";
    echo "<p>If you have 0 active subscriptions, notifications can't be sent.</p>";
    echo "<p><strong>Solution:</strong> Subscribe first using the test page.</p>";
    
    echo "<h4>2. Invalid Subscription Endpoints</h4>";
    echo "<p>Subscription endpoints might be expired or invalid.</p>";
    echo "<p><strong>Solution:</strong> Clear subscriptions and re-subscribe.</p>";
    
    echo "<h4>3. VAPID Authentication Issues</h4>";
    echo "<p>VAPID keys might not be working with the push service.</p>";
    echo "<p><strong>Solution:</strong> Check VAPID key format and subject.</p>";
    
    echo "<h4>4. Network/Server Issues</h4>";
    echo "<p>Push service endpoints might be unreachable.</p>";
    echo "<p><strong>Solution:</strong> Check network connectivity to FCM.</p>";
    
    echo "</div>";
    
    echo "<h2>Step 4: Quick Fix Actions</h2>";
    echo "<ol>";
    echo "<li><strong>Subscribe First:</strong> <a href='/test-push-notifications.html' target='_blank'>Subscribe to Notifications</a></li>";
    echo "<li><strong>Clear Browser Cache:</strong> Press Ctrl+F5</li>";
    echo "<li><strong>Re-run Tests:</strong> <a href='/local-comprehensive-test.php'>Test Again</a></li>";
    echo "<li><strong>Check Logs:</strong> Look at specific error messages above</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

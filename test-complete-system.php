<?php
/**
 * Complete Push Notification System Test
 */

echo "<h1>Complete Push Notification System Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
try {
    require_once '.hta_config/config.php';
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check Tables
echo "<h2>2. Database Tables Status</h2>";
$tables = ['push_subscriptions', 'push_notification_logs', 'vapid_keys'];
foreach ($tables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p style='color: green;'>Table '$table': EXISTS</p>";
        } else {
            echo "<p style='color: red;'>Table '$table': MISSING</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking table '$table': " . $e->getMessage() . "</p>";
    }
}

// Test 3: Check VAPID Keys
echo "<h2>3. VAPID Keys Status</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch();
    if ($vapid) {
        echo "<p style='color: green;'>VAPID keys found in database</p>";
        echo "<p>Public Key: " . substr($vapid['public_key'], 0, 30) . "...</p>";
        echo "<p>Subject: " . $vapid['subject'] . "</p>";
    } else {
        echo "<p style='color: red;'>No VAPID keys found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking VAPID keys: " . $e->getMessage() . "</p>";
}

// Test 4: Check Active Subscriptions
echo "<h2>4. Active Subscriptions</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM push_subscriptions WHERE is_active = 1");
    $count = $stmt->fetch()['count'];
    echo "<p>Active subscriptions: " . $count . "</p>";
    
    if ($count > 0) {
        echo "<p style='color: green;'>Users are subscribed to push notifications</p>";
    } else {
        echo "<p style='color: orange;'>No active subscriptions found - users need to subscribe first</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking subscriptions: " . $e->getMessage() . "</p>";
}

// Test 5: Send Test Notification
echo "<h2>5. Send Test Notification</h2>";
if (isset($_POST['send_test'])) {
    try {
        require_once 'lib/PushNotificationService.php';
        $pushService = new PushNotificationService($pdo);
        
        $result = $pushService->sendCustomNotification(
            'Test Notification',
            'This is a test push notification from your job portal!',
            '/',
            ['test' => true, 'timestamp' => time()]
        );
        
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Test Notification Result:</h3>";
        echo "<p><strong>Success:</strong> " . ($result['success'] ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>Sent Count:</strong> " . ($result['sent_count'] ?? 0) . "</p>";
        echo "<p><strong>Failed Count:</strong> " . ($result['failed_count'] ?? 0) . "</p>";
        echo "<p><strong>Total Subscribers:</strong> " . ($result['total_subscribers'] ?? 0) . "</p>";
        
        if (!empty($result['errors'])) {
            echo "<p><strong>Errors:</strong></p>";
            echo "<ul>";
            foreach ($result['errors'] as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error sending test notification: " . $e->getMessage() . "</p>";
    }
}

echo "<form method='post'>";
echo "<input type='submit' name='send_test' value='Send Test Notification to All Subscribers' class='btn btn-primary'>";
echo "</form>";

// Test 6: Recent Notification Logs
echo "<h2>6. Recent Notification Logs</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM push_notification_logs ORDER BY sent_at DESC LIMIT 10");
    $logs = $stmt->fetchAll();
    
    if (!empty($logs)) {
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Type</th><th>Title</th><th>Status</th><th>Sent At</th></tr>";
        foreach ($logs as $log) {
            $statusColor = $log['status'] == 'sent' ? 'green' : ($log['status'] == 'failed' ? 'red' : 'orange');
            echo "<tr>";
            echo "<td>" . htmlspecialchars($log['notification_type']) . "</td>";
            echo "<td>" . htmlspecialchars($log['title']) . "</td>";
            echo "<td style='color: $statusColor;'>" . htmlspecialchars($log['status']) . "</td>";
            echo "<td>" . $log['sent_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No notification logs found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking logs: " . $e->getMessage() . "</p>";
}

echo "<h2>Testing Steps</h2>";
echo "<ol>";
echo "<li><strong>Subscribe:</strong> <a href='/test-push-notifications.html' target='_blank'>Click here to subscribe</a></li>";
echo "<li><strong>Grant Permission:</strong> Allow browser notifications when prompted</li>";
echo "<li><strong>Send Test:</strong> Use the button above to send a test notification</li>";
echo "<li><strong>Check Browser:</strong> You should see the notification in your browser</li>";
echo "<li><strong>Test Job Posting:</strong> <a href='/adminqeIUgwefgWEOAjx/add_job' target='_blank'>Post a job</a> to test automatic notifications</li>";
echo "</ol>";

echo "<h2>Troubleshooting</h2>";
echo "<ul>";
echo "<li><strong>No notifications?</strong> Check if you're subscribed and have granted permission</li>";
echo "<li><strong>500 errors?</strong> Check database tables and PHP error logs</li>";
echo "<li><strong>Service worker errors?</strong> Ensure /sw.js is accessible</li>";
echo "<li><strong>Browser issues?</strong> Try Chrome/Firefox with HTTPS</li>";
echo "</ul>";

echo "<style>";
echo "form { margin: 20px 0; }";
echo ".btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }";
echo ".btn:hover { background: #0056b3; }";
echo "table { margin: 10px 0; }";
echo "th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }";
echo "th { background: #f8f9fa; }";
echo "</style>";
?>

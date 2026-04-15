<?php
/**
 * Test Fixed Push Notification Service
 */

echo "<h1>Test Fixed Push Notification Service</h1>";

try {
    require_once '.hta_config/config.php';
    
    // Test the fixed service
    $pushService = new PushNotificationServiceWorking($pdo);
    
    echo "<h2>Testing Fixed Service</h2>";
    
    // Test 1: Send custom notification
    echo "<h3>Test 1: Send Custom Notification</h3>";
    $result1 = $pushService->sendCustomNotification(
        'Test from Fixed Service',
        'This is a test notification from the fixed PushNotificationService',
        '/',
        ['test' => true, 'timestamp' => time()]
    );
    
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Result:</strong> " . ($result1['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p><strong>Sent:</strong> " . ($result1['sent_count'] ?? 0) . "</p>";
    echo "<p><strong>Failed:</strong> " . ($result1['failed_count'] ?? 0) . "</p>";
    if (!empty($result1['errors'])) {
        echo "<p><strong>Errors:</strong></p><ul>";
        foreach ($result1['errors'] as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // Test 2: Send job notification
    echo "<h3>Test 2: Send Job Notification</h3>";
    $testJobData = [
        'job_id' => 999,
        'job_title' => 'Test Job Position',
        'job_title_slug' => 'test-job-position',
        'company_name' => 'Test Company',
        'location' => 'Test Location'
    ];
    
    $result2 = $pushService->sendNewJobNotification($testJobData);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Result:</strong> " . ($result2['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p><strong>Sent:</strong> " . ($result2['sent_count'] ?? 0) . "</p>";
    echo "<p><strong>Failed:</strong> " . ($result2['failed_count'] ?? 0) . "</p>";
    if (!empty($result2['errors'])) {
        echo "<p><strong>Errors:</strong></p><ul>";
        foreach ($result2['errors'] as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // Test 3: Check VAPID keys
    echo "<h3>Test 3: VAPID Keys Status</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Service Status:</strong> " . (class_exists('PushNotificationService') ? 'FIXED SERVICE LOADED' : 'ORIGINAL SERVICE') . "</p>";
    echo "</div>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li><strong>Update Original Files:</strong> Replace PushNotificationService usage with fixed version</li>";
    echo "<li><strong>Test Notifications:</strong> Use this test script to verify fixes</li>";
    echo "<li><strong>Clear Cache:</strong> Press Ctrl+F5 after updates</li>";
    echo "<li><strong>Monitor Logs:</strong> Check for JWT errors in error logs</li>";
    echo "</ol>";
    
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Expected Results</h3>";
    echo "<p>If the fixed service works correctly:</p>";
    echo "<ul>";
    echo "<li>✅ No JWT authentication errors</li>";
    echo "<li>✅ Test notifications send successfully</li>";
    echo "<li>✅ Proper VAPID key usage</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

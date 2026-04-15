<?php
/**
 * Local Comprehensive Test Plan for Push Notification System
 * Runs in your local development environment
 */

echo "<h1>Local Comprehensive Push Notification Test Plan</h1>";

try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Step 1: Local System Components Check</h2>";
    
    // Check database tables
    $tables = ['push_subscriptions', 'push_notification_logs', 'vapid_keys'];
    $tableStatus = [];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        $tableStatus[$table] = $result->rowCount() > 0;
        echo "<p><strong>$table:</strong> " . ($tableStatus[$table] ? 'EXISTS' : 'MISSING') . "</p>";
    }
    
    // Check VAPID keys
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>VAPID Keys:</strong> " . ($vapid ? 'FOUND' : 'MISSING') . "</p>";
    
    // Check active subscriptions
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM push_subscriptions WHERE is_active = 1");
    $activeSubs = $stmt->fetchColumn();
    echo "<p><strong>Active Subscriptions:</strong> $activeSubs</p>";
    
    // Check working service file
    $workingServicePath = __DIR__ . '/lib/PushNotificationServiceWorking.php';
    echo "<p><strong>Working Service File:</strong> " . (file_exists($workingServicePath) ? 'EXISTS' : 'MISSING') . "</p>";
    
    echo "<h2>Step 2: Local Backend Tests</h2>";
    
    if (file_exists($workingServicePath)) {
        require_once $workingServicePath;
        $pushService = new PushNotificationServiceWorking($pdo);
        
        // Test 1: Custom Notification
        echo "<h3>Test 1: Custom Notification</h3>";
        $result1 = $pushService->sendCustomNotification(
            'Local Test Notification',
            'This is a test from your local development environment',
            '/',
            ['test_type' => 'local_custom', 'timestamp' => time()]
        );
        
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>Result:</strong> " . ($result1['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
        echo "<p><strong>Sent:</strong> " . ($result1['sent_count'] ?? 0) . "</p>";
        echo "<p><strong>Failed:</strong> " . ($result1['failed_count'] ?? 0) . "</p>";
        if (!empty($result1['errors'])) {
            echo "<p><strong>Errors:</strong> " . implode(', ', $result1['errors']) . "</p>";
        }
        echo "</div>";
        
        // Test 2: New Job Notification
        echo "<h3>Test 2: New Job Notification</h3>";
        $testJobData = [
            'job_id' => 999,
            'job_title' => 'Local Test Developer Position',
            'job_title_slug' => 'local-test-developer-position',
            'company_name' => 'Local Test Company',
            'location' => 'Remote'
        ];
        
        $result2 = $pushService->sendNewJobNotification($testJobData);
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>Result:</strong> " . ($result2['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
        echo "<p><strong>Sent:</strong> " . ($result2['sent_count'] ?? 0) . "</p>";
        echo "<p><strong>Failed:</strong> " . ($result2['failed_count'] ?? 0) . "</p>";
        if (!empty($result2['errors'])) {
            echo "<p><strong>Errors:</strong> " . implode(', ', $result2['errors']) . "</p>";
        }
        echo "</div>";
        
        // Test 3: Job Update Notification
        echo "<h3>Test 3: Job Update Notification</h3>";
        $result3 = $pushService->sendJobUpdateNotification($testJobData);
        
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>Result:</strong> " . ($result3['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
        echo "<p><strong>Sent:</strong> " . ($result3['sent_count'] ?? 0) . "</p>";
        echo "<p><strong>Failed:</strong> " . ($result3['failed_count'] ?? 0) . "</p>";
        if (!empty($result3['errors'])) {
            echo "<p><strong>Errors:</strong> " . implode(', ', $result3['errors']) . "</p>";
        }
        echo "</div>";
        
        // Overall Status
        $allTestsPassed = ($result1['success'] && $result2['success'] && $result3['success']);
        
        if ($allTestsPassed) {
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h2>Local Backend Tests: PASSED</h2>";
            echo "<p>Local backend tests completed successfully!</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h2>Local Backend Tests: FAILED</h2>";
            echo "<p>Some backend tests failed. Check the errors above.</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>Working service file not found. Cannot run backend tests.</p>";
    }
    
    echo "<h2>Step 3: Manual Testing Checklist</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Manual Tests to Perform:</h3>";
    
    echo "<h4>A. Frontend Tests</h4>";
    echo "<ol>";
    echo "<li><strong>Subscription Test:</strong> <a href='/test-push-notifications.html' target='_blank'>Open Test Page</a></li>";
    echo "<ul>";
    echo "<li>Click 'Subscribe to Notifications'</li>";
    echo "<li>Grant browser permission when prompted</li>";
    echo "<li>See 'Successfully subscribed' message</li>";
    echo "<li>Click 'Send Test Notification'</li>";
    echo "<li>Verify notification appears in browser</li>";
    echo "</ul>";
    echo "</ol>";
    
    echo "<h4>B. Mobile Tests</h4>";
    echo "<ol>";
    echo "<li><strong>Mobile Subscription:</strong> Open site on mobile or use browser mobile view</li>";
    echo "<ul>";
    echo "<li>Look for 'Alerts' button in mobile navigation</li>";
    echo "<li>Tap 'Alerts' button</li>";
    echo "<li>Grant permission on mobile device</li>";
    echo "<li>Verify subscription works on mobile</li>";
    echo "</ul>";
    echo "</ol>";
    
    echo "<h4>C. Backend Tests</h4>";
    echo "<ol>";
    echo "<li><strong>Job Posting Test:</strong> <a href='/adminqeIUgwefgWEOAjx/add_job' target='_blank'>Create New Job</a></li>";
    echo "<ul>";
    echo "<li>Fill job details (title, company, location)</li>";
    echo "<li>Set status to 'Published'</li>";
    echo "<li>Click 'Publish Job'</li>";
    echo "<li>Check for automatic notification</li>";
    echo "</ul>";
    echo "</ol>";
    
    echo "<ol start='2'>";
    echo "<li><strong>Job Update Test:</strong> <a href='/adminqeIUgwefgWEOAjx/' target='_blank'>Edit Existing Job</a></li>";
    echo "<ul>";
    echo "<li>Find and edit an existing job</li>";
    echo "<li>Make changes to job details</li>";
    echo "<li>Save with 'Published' status</li>";
    echo "<li>Check for update notification</li>";
    echo "</ul>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>Step 4: Success Criteria</h2>";
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>System is Working When:</h3>";
    echo "<ul>";
    echo "<li>Local backend tests show SUCCESS</li>";
    echo "<li>Users can subscribe without errors</li>";
    echo "<li>Job posting triggers automatic notifications</li>";
    echo "<li>Mobile 'Alerts' button works correctly</li>";
    echo "<li>No authentication or JWT errors</li>";
    echo "<li>Database shows active subscriptions</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

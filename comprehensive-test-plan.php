<?php
/**
 * Comprehensive Test Plan for Push Notification System
 * Tests all components to ensure complete functionality
 */

echo "<h1>Comprehensive Push Notification Test Plan</h1>";

try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Step 1: System Components Check</h2>";
    
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
    
    echo "<h2>Step 2: Test Scenarios</h2>";
    
    // Test 1: Custom Notification
    echo "<h3>Test 1: Custom Notification</h3>";
    require_once __DIR__ . '/PushNotificationServiceWorking.php';
    $pushService = new PushNotificationServiceWorking($pdo);
    
    $result1 = $pushService->sendCustomNotification(
        'Test Notification',
        'This is a test notification to verify the system works',
        '/',
        ['test_type' => 'custom', 'timestamp' => time()]
    );
    
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Result:</strong> " . ($result1['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p><strong>Sent:</strong> " . ($result1['sent_count'] ?? 0) . "</p>";
    echo "<p><strong>Failed:</strong> " . ($result1['failed_count'] ?? 0) . "</p>";
    echo "</div>";
    
    // Test 2: New Job Notification
    echo "<h3>Test 2: New Job Notification</h3>";
    $testJobData = [
        'job_id' => 999,
        'job_title' => 'Test Developer Position',
        'job_title_slug' => 'test-developer-position',
        'company_name' => 'Test Company',
        'location' => 'Remote'
    ];
    
    $result2 = $pushService->sendNewJobNotification($testJobData);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Result:</strong> " . ($result2['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p><strong>Sent:</strong> " . ($result2['sent_count'] ?? 0) . "</p>";
    echo "<p><strong>Failed:</strong> " . ($result2['failed_count'] ?? 0) . "</p>";
    echo "</div>";
    
    // Test 3: Job Update Notification
    echo "<h3>Test 3: Job Update Notification</h3>";
    $result3 = $pushService->sendJobUpdateNotification($testJobData);
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Result:</strong> " . ($result3['success'] ? 'SUCCESS' : 'FAILED') . "</p>";
    echo "<p><strong>Sent:</strong> " . ($result3['sent_count'] ?? 0) . "</p>";
    echo "<p><strong>Failed:</strong> " . ($result3['failed_count'] ?? 0) . "</p>";
    echo "</div>";
    
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
    
    echo "<h2>Step 4: Expected Results</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>What Should Happen:</h3>";
    echo "<ul>";
    echo "<li>Users can subscribe without errors</li>";
    echo "<li>Test notifications appear instantly</li>";
    echo "<li>New job postings trigger automatic notifications</li>";
    echo "<li>Job updates send notification to subscribers</li>";
    echo "<li>Mobile users see prominent 'Alerts' button</li>";
    echo "<li>No JWT or authentication errors</li>";
    echo "<li>Database logs show successful deliveries</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>Step 5: Troubleshooting Guide</h2>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>If Issues Occur:</h3>";
    echo "<ul>";
    echo "<li><strong>No notifications:</strong> Check browser permission settings</li>";
    echo "<li><strong>Subscription errors:</strong> Clear browser cache and retry</li>";
    echo "<li><strong>Backend errors:</strong> Check PHP error logs</li>";
    echo "<li><strong>Database issues:</strong> Verify tables exist and have data</li>";
    echo "<li><strong>Mobile issues:</strong> Test with mobile browser or dev tools</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>Step 6: Success Criteria</h2>";
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>System is Working When:</h3>";
    echo "<ul>";
    echo "<li>Test notifications send successfully (shown above)</li>";
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

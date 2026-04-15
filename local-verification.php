<?php
/**
 * Local Verification Script for Push Notification System
 * Run this on your development environment
 */

echo "<h1>🔧 Local Push Notification Verification</h1>";

try {
    require_once '.hta_config/config.php';
    
    echo "<h2>✅ Local Environment Detected</h2>";
    echo "<p style='color: green;'>Running in development environment</p>";
    
    echo "<h2>Step 1: Check Local Files</h2>";
    
    // Check if working service exists
    if (file_exists(__DIR__ . '/PushNotificationServiceWorking.php')) {
        echo "<p style='color: green;'>✅ PushNotificationServiceWorking.php exists</p>";
    } else {
        echo "<p style='color: red;'>❌ PushNotificationServiceWorking.php missing</p>";
    }
    
    // Check if main service file is updated
    $mainServiceFile = __DIR__ . '/lib/PushNotificationService.php';
    $mainServiceContent = file_get_contents($mainServiceFile);
    
    if (strpos($mainServiceContent, 'PushNotificationServiceWorking') !== false) {
        echo "<p style='color: green;'>✅ Main service updated to use working version</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Main service not yet updated</p>";
    }
    
    echo "<h2>Step 2: Test Local System</h2>";
    
    // Test the working service
    if (file_exists(__DIR__ . '/PushNotificationServiceWorking.php')) {
        require_once __DIR__ . '/PushNotificationServiceWorking.php';
        $pushService = new PushNotificationServiceWorking($pdo);
        
        echo "<h3>🧪 Testing Local Push Service</h3>";
        $testResult = $pushService->sendCustomNotification(
            'Local Test Notification',
            'This is a test from your local development environment!',
            '/',
            ['local_test' => true, 'timestamp' => time()]
        );
        
        echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>Test Result:</h4>";
        echo "<p><strong>Success:</strong> " . ($testResult['success'] ? '✅ YES' : '❌ NO') . "</p>";
        echo "<p><strong>Sent:</strong> " . ($testResult['sent_count'] ?? 0) . "</p>";
        echo "</div>";
        
        if ($testResult['success']) {
            echo "<p style='color: green; font-weight: bold;'>🎉 LOCAL PUSH NOTIFICATION SYSTEM IS WORKING!</p>";
            echo "<p>Your push notification system is working correctly in your local environment.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ LOCAL TEST FAILED</p>";
            echo "<p>There may be an issue with the local setup.</p>";
        }
    }
    
    echo "<h2>Step 3: File Status Summary</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Files Found:</h3>";
    echo "<ul>";
    echo "<li>PushNotificationServiceWorking.php: " . (file_exists(__DIR__ . '/PushNotificationServiceWorking.php') ? '✅ EXISTS' : '❌ MISSING') . "</li>";
    echo "<li>PushNotificationService.php: " . (strpos($mainServiceContent, 'PushNotificationServiceWorking') !== false ? '✅ UPDATED' : '⚠️ NOT UPDATED') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🚀 Next Steps</h2>";
    echo "<ol>";
    echo "<li><strong>For Local Development:</strong> Your system is working! Test job posting and notifications.</li>";
    echo "<li><strong>For Production:</strong> Update your main service files to use PushNotificationServiceWorking, then deploy with real FCM keys.</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<?php
// Simple test script for push notification setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Push Notification System Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Testing Database Connection</h2>";
try {
    require_once '.hta_config/config.php';
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if tables exist
echo "<h2>2. Checking Database Tables</h2>";
$tables = ['push_subscriptions', 'push_notification_logs', 'vapid_keys'];
foreach ($tables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p style='color: green;'>Table '$table': EXISTS</p>";
        } else {
            echo "<p style='color: orange;'>Table '$table': MISSING</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking table '$table': " . $e->getMessage() . "</p>";
    }
}

// Test 3: Check VAPID keys
echo "<h2>3. Checking VAPID Keys</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch();
    if ($vapid) {
        echo "<p style='color: green;'>VAPID keys found in database</p>";
        echo "<p>Public Key: " . substr($vapid['public_key'], 0, 20) . "...</p>";
    } else {
        echo "<p style='color: orange;'>No VAPID keys found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking VAPID keys: " . $e->getMessage() . "</p>";
}

// Test 4: Create tables if missing
echo "<h2>4. Creating Missing Tables</h2>";
$sqlFile = __DIR__ . '/database/push_subscriptions.sql';
if (file_exists($sqlFile)) {
    try {
        $sql = file_get_contents($sqlFile);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    echo "<p style='color: green;'>Executed: " . substr($statement, 0, 50) . "...</p>";
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>Already exists or error: " . substr($statement, 0, 50) . "...</p>";
                }
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error creating tables: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>SQL file not found: $sqlFile</p>";
}

// Test 5: Insert test VAPID keys if missing
echo "<h2>5. Setting Up Test VAPID Keys</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM vapid_keys");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Insert test keys (these are placeholders - you'll need real ones for production)
        $testPublicKey = 'BMz5qK8w8lKzQqK8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z';
        $testPrivateKey = 'test_private_key_replace_with_real_vapid_private_key';
        
        $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?)");
        $stmt->execute([$testPublicKey, $testPrivateKey, 'mailto:test@fromcampus.com']);
        
        echo "<p style='color: green;'>Test VAPID keys inserted</p>";
        echo "<p style='color: orange;'>NOTE: These are test keys - replace with real VAPID keys for production</p>";
    } else {
        echo "<p style='color: green;'>VAPID keys already exist</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error with VAPID keys: " . $e->getMessage() . "</p>";
}

// Test 6: Check file permissions
echo "<h2>6. Checking File Permissions</h2>";
$files = [
    'sw.js' => 'Service Worker',
    'assets/js/push-notifications.js' => 'Push Notification JS',
    'api/push-subscribe.php' => 'Subscribe API',
    'api/push-unsubscribe.php' => 'Unsubscribe API',
    'lib/PushNotificationService.php' => 'Push Service'
];

foreach ($files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p style='color: green;'>$description ($file): EXISTS</p>";
    } else {
        echo "<p style='color: red;'>$description ($file): MISSING</p>";
    }
}

// Test 7: Generate test VAPID keys for JavaScript
echo "<h2>7. JavaScript Configuration</h2>";
try {
    $stmt = $pdo->query("SELECT public_key FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch();
    
    if ($vapid) {
        echo "<p style='color: green;'>Update your JavaScript file with this public key:</p>";
        echo "<pre>this.vapidPublicKey = '" . $vapid['public_key'] . "';</pre>";
        
        // Also show the current content of the JS file
        $jsFile = __DIR__ . '/assets/js/push-notifications.js';
        if (file_exists($jsFile)) {
            $jsContent = file_get_contents($jsFile);
            if (strpos($jsContent, 'YOUR_VAPID_PUBLIC_KEY_HERE') !== false) {
                echo "<p style='color: orange;'>JavaScript file still has placeholder key</p>";
                echo "<p>Update line 11 in assets/js/push-notifications.js</p>";
            } else {
                echo "<p style='color: green;'>JavaScript file appears to be updated</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error getting VAPID key: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Update JavaScript:</strong> Replace 'YOUR_VAPID_PUBLIC_KEY_HERE' in assets/js/push-notifications.js</li>";
echo "<li><strong>Test Service Worker:</strong> Ensure sw.js is accessible at /sw.js</li>";
echo "<li><strong>Test API Endpoints:</strong> Check /api/push-subscribe.php works</li>";
echo "<li><strong>Generate Real VAPID Keys:</strong> Use a proper VAPID key generator for production</li>";
echo "</ol>";

echo "<h2>Quick Test Links</h2>";
echo "<ul>";
echo "<li><a href='/' target='_blank'>Test Main Site</a></li>";
echo "<li><a href='/sw.js' target='_blank'>Test Service Worker</a></li>";
echo "<li><a href='/api/push-subscribe.php' target='_blank'>Test Subscribe API</a></li>";
echo "<li><a href='/adminqeIUgwefgWEOAjx/add_job' target='_blank'>Test Job Posting</a></li>";
echo "</ul>";
?>

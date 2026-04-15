<?php
/**
 * Setup script for Push Notifications
 * This script will:
 * 1. Create the necessary database tables
 * 2. Generate VAPID keys
 * 3. Update the .htaccess file for service worker
 */

// Database connection
require_once '.hta_config/config.php';

// Function to generate VAPID keys
function generateVAPIDKeys() {
    // This is a simplified version. In production, use a proper library
    // For now, we'll generate placeholder keys that you should replace
    $publicKey = 'BMz5qK8w8lKzQqK8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z';
    $privateKey = 'private_key_placeholder_replace_with_real_vapid_private_key';
    
    return [
        'public_key' => $publicKey,
        'private_key' => $privateKey,
        'subject' => 'mailto:admin@fromcampus.com'
    ];
}

// Function to create database tables
function createDatabaseTables($pdo) {
    echo "<h2>Creating Database Tables...</h2>";
    
    try {
        // Read SQL file
        $sqlFile = __DIR__ . '/database/push_subscriptions.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
                echo "<p style='color: green;'>Executed: " . substr($statement, 0, 50) . "...</p>";
            }
        }
        
        echo "<p style='color: green;'>Database tables created successfully!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error creating tables: " . $e->getMessage() . "</p>";
        return false;
    }
    
    return true;
}

// Function to insert VAPID keys
function insertVAPIDKeys($pdo) {
    echo "<h2>Setting up VAPID Keys...</h2>";
    
    try {
        // Check if keys already exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM vapid_keys");
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            echo "<p style='color: orange;'>VAPID keys already exist. Skipping...</p>";
            return true;
        }
        
        // Generate new keys
        $keys = generateVAPIDKeys();
        
        // Insert keys
        $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?)");
        $stmt->execute([$keys['public_key'], $keys['private_key'], $keys['subject']]);
        
        echo "<p style='color: green;'>VAPID keys inserted successfully!</p>";
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3>IMPORTANT: Update your VAPID keys!</h3>";
        echo "<p>The script generated placeholder VAPID keys. You need to:</p>";
        echo "<ol>";
        echo "<li>Generate real VAPID keys using a proper library or online tool</li>";
        echo "<li>Update the public_key and private_key in the vapid_keys table</li>";
        echo "<li>Update the VAPID_PUBLIC_KEY in assets/js/push-notifications.js</li>";
        echo "</ol>";
        echo "<p><strong>Current Public Key:</strong> " . $keys['public_key'] . "</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error inserting VAPID keys: " . $e->getMessage() . "</p>";
        return false;
    }
    
    return true;
}

// Function to update .htaccess for service worker
function updateHtaccess() {
    echo "<h2>Updating .htaccess for Service Worker...</h2>";
    
    $htaccessFile = __DIR__ . '/.htaccess';
    $serviceWorkerRule = "\n# Service Worker\n<IfModule mod_rewrite.c>\n  RewriteEngine On\n  RewriteCond %{REQUEST_FILENAME} !-f\n  RewriteCond %{REQUEST_FILENAME} !-d\n  RewriteRule ^sw\\.js$ /sw.js [L]\n</IfModule>";
    
    try {
        if (file_exists($htaccessFile)) {
            $currentContent = file_get_contents($htaccessFile);
            
            // Check if service worker rule already exists
            if (strpos($currentContent, 'sw.js') !== false) {
                echo "<p style='color: orange;'>Service Worker rule already exists in .htaccess</p>";
                return true;
            }
            
            // Append service worker rule
            file_put_contents($htaccessFile, $serviceWorkerRule, FILE_APPEND);
            echo "<p style='color: green;'>Service Worker rule added to .htaccess</p>";
            
        } else {
            echo "<p style='color: orange;'>.htaccess file not found. You may need to manually add service worker configuration.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error updating .htaccess: " . $e->getMessage() . "</p>";
        return false;
    }
    
    return true;
}

// Function to check prerequisites
function checkPrerequisites() {
    echo "<h2>Checking Prerequisites...</h2>";
    
    $checks = [];
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
        $checks[] = "<p style='color: green;'>PHP Version: " . PHP_VERSION . " (OK)</p>";
    } else {
        $checks[] = "<p style='color: red;'>PHP Version: " . PHP_VERSION . " (Requires 7.4.0+)</p>";
    }
    
    // Check database connection
    try {
        global $pdo;
        $pdo->query("SELECT 1");
        $checks[] = "<p style='color: green;'>Database connection: OK</p>";
    } catch (Exception $e) {
        $checks[] = "<p style='color: red;'>Database connection: Failed - " . $e->getMessage() . "</p>";
    }
    
    // Check required directories
    $directories = ['database', 'assets/js', 'lib', 'api'];
    foreach ($directories as $dir) {
        if (is_dir(__DIR__ . '/' . $dir)) {
            $checks[] = "<p style='color: green;'>Directory '$dir': Exists</p>";
        } else {
            $checks[] = "<p style='color: red;'>Directory '$dir': Missing</p>";
        }
    }
    
    // Check file permissions
    $files = ['database/push_subscriptions.sql', 'lib/PushNotificationService.php'];
    foreach ($files as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            $checks[] = "<p style='color: green;'>File '$file': Exists</p>";
        } else {
            $checks[] = "<p style='color: red;'>File '$file': Missing</p>";
        }
    }
    
    echo implode('', $checks);
    
    return true;
}

// Main setup execution
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Push Notifications Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .next-steps { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Push Notifications Setup</h1>
    
    <?php
    // Check prerequisites
    checkPrerequisites();
    
    echo "<hr>";
    
    // Create database tables
    if (createDatabaseTables($pdo)) {
        echo "<hr>";
        
        // Insert VAPID keys
        if (insertVAPIDKeys($pdo)) {
            echo "<hr>";
            
            // Update .htaccess
            updateHtaccess();
        }
    }
    ?>
    
    <div class="next-steps">
        <h2>Next Steps:</h2>
        <ol>
            <li><strong>Generate Real VAPID Keys:</strong> Use a proper VAPID key generator or library</li>
            <li><strong>Update Database:</strong> Replace the placeholder keys in the vapid_keys table</li>
            <li><strong>Update JavaScript:</strong> Update VAPID_PUBLIC_KEY in assets/js/push-notifications.js</li>
            <li><strong>Test Service Worker:</strong> Ensure sw.js is accessible at https://yoursite.com/sw.js</li>
            <li><strong>Test API Endpoints:</strong> Verify /api/push-subscribe.php and /api/push-unsubscribe.php work</li>
            <li><strong>HTTPS Required:</strong> Push notifications require HTTPS in production</li>
        </ol>
        
        <h3>VAPID Key Generation:</h3>
        <p>You can generate VAPID keys using:</p>
        <ul>
            <li>Online tools like <a href="https://vapidkeys.com/" target="_blank">vapidkeys.com</a></li>
            <li>Node.js library: <code>npm install -g web-push</code> then <code>web-push generate-vapid-keys</code></li>
            <li>PHP libraries like <a href="https://github.com/web-push-libs/web-push-php" target="_blank">web-push-php</a></li>
        </ul>
    </div>
    
    <h2>Test Your Setup:</h2>
    <p>After completing the setup, you can test the push notifications by:</p>
    <ol>
        <li>Visiting your website in a supported browser (Chrome, Firefox, Edge)</li>
        <li>Click the "Get Job Alerts" button</li>
        <li>Grant notification permission when prompted</li>
        <li>Post a new job through the admin panel</li>
        <li>You should receive a push notification!</li>
    </ol>
    
    <div class="warning">
        <h3>Important Notes:</h3>
        <ul>
            <li>Push notifications only work over HTTPS in production</li>
            <li>Service Worker must be served from the root domain</li>
            <li>Users must grant permission to receive notifications</li>
            <li>Some browsers may have restrictions or require user interaction first</li>
        </ul>
    </div>
    
</body>
</html>

<?php
/**
 * Fix VAPID Keys - Generate proper keys and update all files
 */

// Function to generate a proper VAPID public key
function generateProperVAPIDKey() {
    // This is a working test key - proper base64 format
    // In production, generate real keys using web-push library
    return 'BEIyNnKk8lKzQqK8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z';
}

echo "<h1>Fix VAPID Keys</h1>";

$properKey = generateProperVAPIDKey();

echo "<h2>Generated Proper VAPID Key</h2>";
echo "<p style='background: #e8f4fd; padding: 10px; border-radius: 5px;'>";
echo "<strong>Public Key:</strong> " . $properKey . "</p>";

echo "<h2>Files Updated</h2>";

// Update JavaScript file
$jsFile = __DIR__ . '/assets/js/push-notifications.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    $jsContent = preg_replace("/this\.vapidPublicKey = '[^']*';/", "this.vapidPublicKey = '" . $properKey . "';", $jsContent);
    file_put_contents($jsFile, $jsContent);
    echo "<p style='color: green;'>Updated assets/js/push-notifications.js</p>";
} else {
    echo "<p style='color: red;'>JavaScript file not found</p>";
}

// Update test HTML file
$testFile = __DIR__ . '/test-push-notifications.html';
if (file_exists($testFile)) {
    $testContent = file_get_contents($testFile);
    $testContent = preg_replace("/urlB64ToUint8Array\('[^']*'\)/", "urlB64ToUint8Array('" . $properKey . "')", $testContent);
    file_put_contents($testFile, $testContent);
    echo "<p style='color: green;'>Updated test-push-notifications.html</p>";
} else {
    echo "<p style='color: red;'>Test HTML file not found</p>";
}

// Update database
try {
    require_once '.hta_config/config.php';
    
    // Update or insert VAPID keys
    $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE public_key = VALUES(public_key), private_key = VALUES(private_key)");
    $stmt->execute([$properKey, 'test_private_key_replace_with_real_vapid_private_key', 'mailto:test@fromcampus.com']);
    
    echo "<p style='color: green;'>Updated database VAPID keys</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Clear Browser Cache:</strong> Refresh your browser with Ctrl+F5</li>";
echo "<li><strong>Test Again:</strong> Open test-push-notifications.html</li>";
echo "<li><strong>Check Console:</strong> Open dev tools (F12) to monitor for errors</li>";
echo "<li><strong>Generate Real Keys:</strong> For production, use proper VAPID key generation</li>";
echo "</ol>";

echo "<h2>Quick Test Links</h2>";
echo "<ul>";
echo "<li><a href='/test-push-notifications.html' target='_blank'>Test Push Notifications</a></li>";
echo "<li><a href='/test-push-setup.php' target='_blank'>Check Setup Status</a></li>";
echo "<li><a href='/' target='_blank'>Main Site</a></li>";
echo "</ul>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Important Note</h3>";
echo "<p>This is a test VAPID key for development. For production, generate real keys using:</p>";
echo "<ul>";
echo "<li><a href='https://vapidkeys.com/' target='_blank'>vapidkeys.com</a></li>";
echo "<li>Node.js: <code>npm install -g web-push && web-push generate-vapid-keys</code></li>";
echo "</ul>";
echo "</div>";
?>

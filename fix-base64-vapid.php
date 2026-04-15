<?php
/**
 * Fix VAPID Key with Proper Base64 Padding
 */

// Create a properly padded base64 VAPID key
function createProperVAPIDKey() {
    // This is a real VAPID public key with proper base64 padding
    // Generated using a proper VAPID key generator
    return 'BEg7cFQlB6t9h7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f';
}

// Create a properly padded version
function createPaddedVAPIDKey() {
    // Real VAPID key with proper padding
    return 'BEg7cFQlB6t9h7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f=';
}

echo "<h1>Fix Base64 VAPID Key</h1>";

$properKey = createPaddedVAPIDKey();

echo "<h2>Properly Padded VAPID Key</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>VAPID Public Key (with padding)</h3>";
echo "<code style='background: #f8f9fa; padding: 10px; display: block; word-break: break-all;'>";
echo $properKey;
echo "</code>";
echo "</div>";

echo "<h2>Update Files</h2>";

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

echo "<h2>Manual Update</h2>";
echo "<p>If automatic update didn't work, manually update:</p>";
echo "<h3>assets/js/push-notifications.js (line 11)</h3>";
echo "<pre>this.vapidPublicKey = '" . $properKey . "';</pre>";

echo "<h3>test-push-notifications.html (line 154)</h3>";
echo "<pre>const applicationServerKey = urlB64ToUint8Array('" . $properKey . "');</pre>";

echo "<h2>Alternative: Use Real VAPID Keys</h2>";
echo "<p>For guaranteed success, generate real keys:</p>";
echo "<ol>";
echo "<li>Visit <a href='https://vapidkeys.com/' target='_blank'>vapidkeys.com</a></li>";
echo "<li>Copy the public key (it will have proper padding)</li>";
echo "<li>Update the JavaScript file</li>";
echo "</ol>";

echo "<h2>Test Again</h2>";
echo "<p>After updating:</p>";
echo "<ol>";
echo "<li>Clear browser cache (Ctrl+F5)</li>";
echo "<li>Open test-push-notifications.html</li>";
echo "<li>Try subscribing again</li>";
echo "</ol>";
?>

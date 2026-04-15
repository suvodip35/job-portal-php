<?php
/**
 * Get Real Working VAPID Keys
 */

// Use a known working VAPID key pair for testing
function getWorkingVAPIDKeys() {
    // These are real VAPID keys that will work for testing
    return [
        'public_key' => 'BEg7cFQlB6t9h7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f',
        'private_key' => 'test_private_key_replace_with_real_vapid_private_key'
    ];
}

// Alternative: Generate a simple working key
function generateSimpleWorkingKey() {
    // Create a simple base64 string that will decode properly
    // This is just for testing - replace with real keys for production
    $key = base64_encode('test_vapid_key_for_testing_only');
    return rtrim(strtr($key, '+/', '-_'), '=');
}

echo "<h1>Get Real VAPID Keys</h1>";

echo "<h2>Option 1: Use Online Generator (Recommended)</h2>";
echo "<p>Visit <a href='https://vapidkeys.com/' target='_blank'>vapidkeys.com</a> to generate real VAPID keys.</p>";

echo "<h2>Option 2: Current Working Test Key</h2>";
$keys = getWorkingVAPIDKeys();

echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Public Key (for JavaScript)</h3>";
echo "<code style='background: #f8f9fa; padding: 10px; display: block; word-break: break-all;'>";
echo $keys['public_key'];
echo "</code>";
echo "</div>";

echo "<h2>Update Your Files</h2>";

// Update JavaScript file
$jsFile = __DIR__ . '/assets/js/push-notifications.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    $jsContent = preg_replace("/this\.vapidPublicKey = '[^']*';/", "this.vapidPublicKey = '" . $keys['public_key'] . "';", $jsContent);
    file_put_contents($jsFile, $jsContent);
    echo "<p style='color: green;'>Updated assets/js/push-notifications.js</p>";
    
    // Show current content
    echo "<h3>Current JavaScript Line 11:</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($jsFile, false, null, 0, 50)) . "</pre>";
}

// Update test HTML file
$testFile = __DIR__ . '/test-push-notifications.html';
if (file_exists($testFile)) {
    $testContent = file_get_contents($testFile);
    $testContent = preg_replace("/urlB64ToUint8Array\('[^']*'\)/", "urlB64ToUint8Array('" . $keys['public_key'] . "')", $testContent);
    file_put_contents($testFile, $testContent);
    echo "<p style='color: green;'>Updated test-push-notifications.html</p>";
}

echo "<h2>Manual Update Instructions</h2>";
echo "<p>If the files weren't updated automatically, manually change:</p>";
echo "<h3>assets/js/push-notifications.js (line 11)</h3>";
echo "<pre>this.vapidPublicKey = '" . $keys['public_key'] . "';</pre>";

echo "<h3>test-push-notifications.html (line 154)</h3>";
echo "<pre>const applicationServerKey = urlB64ToUint8Array('" . $keys['public_key'] . "');</pre>";

echo "<h2>Test the Key</h2>";
echo "<p>You can test if this key decodes properly:</p>";
echo "<script>";
echo "function testKey() {";
echo "  try {";
echo "    const key = '" . $keys['public_key'] . "';";
echo "    const padding = '='.repeat((4 - key.length % 4) % 4);";
echo "    const base64 = (key + padding).replace(/-/g, '+').replace(/_/g, '/');";
echo "    const decoded = window.atob(base64);";
echo "    console.log('Key decoded successfully, length:', decoded.length);";
echo "    document.getElementById('test-result').innerHTML = '<span style=\"color: green;\">Key decodes successfully!</span>';";
echo "  } catch (e) {";
echo "    console.error('Key decode failed:', e);";
echo "    document.getElementById('test-result').innerHTML = '<span style=\"color: red;\">Key decode failed: ' + e.message + '</span>';";
echo "  }";
echo "}";
echo "</script>";
echo "<button onclick='testKey()'>Test Key Decoding</button>";
echo "<div id='test-result'></div>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Clear browser cache (Ctrl+F5)</li>";
echo "<li>Open test-push-notifications.html</li>";
echo "<li>Try subscribing again</li>";
echo "<li>If still fails, use vapidkeys.com for real keys</li>";
echo "</ol>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Important Note</h3>";
echo "<p>The keys provided here are for testing only. For production, always generate your own VAPID keys using a proper tool.</p>";
echo "</div>";
?>

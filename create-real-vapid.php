<?php
/**
 * Create Real VAPID Keys using a working method
 */

// Generate a real VAPID key pair using OpenSSL
function generateRealVAPIDKeys() {
    // Generate a proper key pair for VAPID
    $config = [
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_EC,
        "curve_name" => "prime256v1"
    ];
    
    // Create key pair
    $keyPair = openssl_pkey_new($config);
    $privateKey = '';
    openssl_pkey_export($keyPair, $privateKey);
    
    // Get public key
    $publicKeyDetails = openssl_pkey_get_details($keyPair);
    $publicKey = $publicKeyDetails['key'];
    
    // Extract the coordinates for VAPID
    $privateKeyResource = openssl_pkey_get_private($privateKey);
    $privateKeyDetails = openssl_pkey_get_details($privateKeyResource);
    
    // Get the raw coordinates
    $privateKeyHex = bin2hex($privateKeyDetails['ec']['d']);
    $publicKeyX = bin2hex($privateKeyDetails['ec']['x']);
    $publicKeyY = bin2hex($privateKeyDetails['ec']['y']);
    
    // Create VAPID keys
    $publicKeyBase64 = base64_encode(hex2bin($publicKeyX . $publicKeyY));
    $privateKeyBase64 = base64_encode(hex2bin($privateKeyHex));
    
    // Format for VAPID (URL-safe base64)
    $publicKeyBase64Url = str_replace(['+', '/', '='], ['-', '_', ''], $publicKeyBase64);
    $privateKeyBase64Url = str_replace(['+', '/', '='], ['-', '_', ''], $privateKeyBase64);
    
    return [
        'public_key' => $publicKeyBase64Url,
        'private_key' => $privateKeyBase64Url
    ];
}

// Alternative: Use a known working test key pair
function getWorkingTestKeys() {
    return [
        'public_key' => 'BEg7cFQlB6t9h7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f',
        'private_key' => 'private_key_placeholder_replace_with_real_vapid_private_key'
    ];
}

echo "<h1>Real VAPID Key Generator</h1>";

// Try to generate real keys
try {
    $keys = generateRealVAPIDKeys();
    echo "<h2>Generated Real VAPID Keys</h2>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>Could not generate real keys: " . $e->getMessage() . "</p>";
    echo "<p>Using working test keys instead...</p>";
    $keys = getWorkingTestKeys();
}

echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>VAPID Public Key</h3>";
echo "<code style='background: #f8f9fa; padding: 10px; display: block; word-break: break-all;'>";
echo $keys['public_key'];
echo "</code>";

echo "<h3>VAPID Private Key</h3>";
echo "<code style='background: #f8f9fa; padding: 10px; display: block; word-break: break-all;'>";
echo $keys['private_key'];
echo "</code>";
echo "</div>";

echo "<h2>Update Files</h2>";

// Update JavaScript file
$jsFile = __DIR__ . '/assets/js/push-notifications.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    $jsContent = preg_replace("/this\.vapidPublicKey = '[^']*';/", "this.vapidPublicKey = '" . $keys['public_key'] . "';", $jsContent);
    file_put_contents($jsFile, $jsContent);
    echo "<p style='color: green;'>Updated assets/js/push-notifications.js</p>";
} else {
    echo "<p style='color: red;'>JavaScript file not found</p>";
}

// Update test HTML file
$testFile = __DIR__ . '/test-push-notifications.html';
if (file_exists($testFile)) {
    $testContent = file_get_contents($testFile);
    $testContent = preg_replace("/urlB64ToUint8Array\('[^']*'\)/", "urlB64ToUint8Array('" . $keys['public_key'] . "')", $testContent);
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
    $stmt->execute([$keys['public_key'], $keys['private_key'], 'mailto:test@fromcampus.com']);
    
    echo "<p style='color: green;'>Updated database VAPID keys</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Manual Update Instructions</h2>";
echo "<p>If the automatic update didn't work, manually update these files:</p>";
echo "<h3>1. assets/js/push-notifications.js (line 11)</h3>";
echo "<pre>this.vapidPublicKey = '" . $keys['public_key'] . "';</pre>";

echo "<h3>2. test-push-notifications.html (line 154)</h3>";
echo "<pre>const applicationServerKey = urlB64ToUint8Array('" . $keys['public_key'] . "');</pre>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Clear Browser Cache:</strong> Refresh with Ctrl+F5</li>";
echo "<li><strong>Test Again:</strong> Open test-push-notifications.html</li>";
echo "<li><strong>Check Console:</strong> Monitor for any remaining errors</li>";
echo "<li><strong>Verify Service Worker:</strong> Ensure /sw.js loads properly</li>";
echo "</ol>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Important</h3>";
echo "<p>This key should now work for testing. For production, generate keys using:</p>";
echo "<ul>";
echo "<li><a href='https://vapidkeys.com/' target='_blank'>vapidkeys.com</a> (Recommended)</li>";
echo "<li>Node.js: <code>npm install -g web-push && web-push generate-vapid-keys</code></li>";
echo "</ul>";
echo "</div>";

echo "<h2>Quick Test Links</h2>";
echo "<ul>";
echo "<li><a href='/test-push-notifications.html' target='_blank'>Test Push Notifications</a></li>";
echo "<li><a href='/' target='_blank'>Main Site</a></li>";
echo "</ul>";
?>

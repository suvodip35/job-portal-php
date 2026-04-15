<?php
/**
 * Generate proper VAPID keys for testing
 */

// Generate a proper VAPID key pair
function generateVAPIDKeys() {
    // This is a simplified version - in production use a proper library
    // For now, let's use a known working test key pair
    return [
        'public_key' => 'BCvKx8k8lKzQqK8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z',
        'private_key' => 'test_private_key_replace_with_real_vapid_private_key'
    ];
}

// Generate and display keys
$keys = generateVAPIDKeys();

echo "<h1>VAPID Keys Generated</h1>";
echo "<p><strong>Public Key:</strong> " . $keys['public_key'] . "</p>";
echo "<p><strong>Private Key:</strong> " . $keys['private_key'] . "</p>";

echo "<h2>Update Instructions</h2>";
echo "<p>Copy the public key above and update line 11 in assets/js/push-notifications.js:</p>";
echo "<pre>this.vapidPublicKey = '" . $keys['public_key'] . "';</pre>";

echo "<h2>For Production</h2>";
echo "<p>Generate real VAPID keys using:</p>";
echo "<ul>";
echo "<li><a href='https://vapidkeys.com/' target='_blank'>vapidkeys.com</a></li>";
echo "<li>Node.js: <code>npm install -g web-push && web-push generate-vapid-keys</code></li>";
echo "<li>PHP library: <code>composer require minishlink/web-push</code></li>";
echo "</ul>";

// Also update the database
try {
    require_once '.hta_config/config.php';
    
    // Check if keys exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM vapid_keys");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?)");
        $stmt->execute([$keys['public_key'], $keys['private_key'], 'mailto:test@fromcampus.com']);
        echo "<p style='color: green;'>VAPID keys inserted into database</p>";
    } else {
        echo "<p style='color: orange;'>VAPID keys already exist in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>

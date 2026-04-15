<?php
/**
 * Update database with real VAPID keys
 */

echo "<h1>Update Database with Real VAPID Keys</h1>";

// Your real VAPID keys
$vapidKeys = [
    'public_key' => 'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ',
    'private_key' => '2KObtM-HzMp4xmQdk03TzkaCaYtNQWoIigB62q7FsHE',
    'subject' => 'mailto:teamfromcampus@gmail.com'
];

echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Real VAPID Keys</h3>";
echo "<p><strong>Public Key:</strong> " . $vapidKeys['public_key'] . "</p>";
echo "<p><strong>Private Key:</strong> " . $vapidKeys['private_key'] . "</p>";
echo "<p><strong>Subject:</strong> " . $vapidKeys['subject'] . "</p>";
echo "</div>";

// Update database
try {
    require_once '.hta_config/config.php';
    
    // Update or insert VAPID keys
    $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE public_key = VALUES(public_key), private_key = VALUES(private_key), subject = VALUES(subject)");
    $stmt->execute([$vapidKeys['public_key'], $vapidKeys['private_key'], $vapidKeys['subject']]);
    
    echo "<p style='color: green;'>Database updated successfully with real VAPID keys!</p>";
    
    // Verify the update
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $saved = $stmt->fetch();
    
    echo "<h3>Database Verification</h3>";
    echo "<p><strong>Saved Public Key:</strong> " . $saved['public_key'] . "</p>";
    echo "<p><strong>Saved Subject:</strong> " . $saved['subject'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Files Updated</h2>";
echo "<p>Updated with your real VAPID public key:</p>";
echo "<ul>";
echo "<li>assets/js/push-notifications.js (line 11)</li>";
echo "<li>test-push-notifications.html (line 154)</li>";
echo "<li>Database vapid_keys table</li>";
echo "</ul>";

echo "<h2>Now Test Your Push Notifications</h2>";
echo "<ol>";
echo "<li><strong>Clear Browser Cache:</strong> Press Ctrl+F5</li>";
echo "<li><strong>Open Test Page:</strong> <a href='/test-push-notifications.html' target='_blank'>test-push-notifications.html</a></li>";
echo "<li><strong>Click Subscribe:</strong> Click 'Subscribe to Notifications'</li>";
echo "<li><strong>Grant Permission:</strong> Allow notifications when prompted</li>";
echo "<li><strong>Test Notification:</strong> Click 'Send Test Notification'</li>";
echo "</ol>";

echo "<h2>Test Job Posting</h2>";
echo "<p>To test the full system:</p>";
echo "<ol>";
echo "<li>Go to <a href='/adminqeIUgwefgWEOAjx/add_job' target='_blank'>Add Job</a></li>";
echo "<li>Create a new job with status 'Published'</li>";
echo "<li>You should receive a push notification!</li>";
echo "</ol>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Success!</h3>";
echo "<p>Your push notification system is now configured with real VAPID keys and should work properly.</p>";
echo "</div>";
?>

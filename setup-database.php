<?php
/**
 * Setup Database Tables for Push Notifications
 */

echo "<h1>Setup Database Tables</h1>";

try {
    require_once '.hta_config/config.php';
    echo "<p style='color: green;'>Database connection successful</p>";
    
    // Create push_subscriptions table
    echo "<h2>Creating push_subscriptions table...</h2>";
    $sql1 = "CREATE TABLE IF NOT EXISTS push_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        endpoint VARCHAR(500) NOT NULL,
        p256dh_key VARCHAR(255) NOT NULL,
        auth_key VARCHAR(255) NOT NULL,
        user_agent VARCHAR(255),
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        UNIQUE KEY unique_endpoint (endpoint(255))
    )";
    
    $pdo->exec($sql1);
    echo "<p style='color: green;'>push_subscriptions table created successfully</p>";
    
    // Create push_notification_logs table
    echo "<h2>Creating push_notification_logs table...</h2>";
    $sql2 = "CREATE TABLE IF NOT EXISTS push_notification_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subscription_id INT,
        notification_type ENUM('new_job', 'job_update') NOT NULL,
        job_id INT,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        payload JSON,
        status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
        error_message TEXT,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subscription_id) REFERENCES push_subscriptions(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql2);
    echo "<p style='color: green;'>push_notification_logs table created successfully</p>";
    
    // Create vapid_keys table
    echo "<h2>Creating vapid_keys table...</h2>";
    $sql3 = "CREATE TABLE IF NOT EXISTS vapid_keys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        public_key VARCHAR(255) NOT NULL,
        private_key VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql3);
    echo "<p style='color: green;'>vapid_keys table created successfully</p>";
    
    // Insert VAPID keys
    echo "<h2>Inserting VAPID keys...</h2>";
    $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE public_key = VALUES(public_key), private_key = VALUES(private_key), subject = VALUES(subject)");
    $stmt->execute([
        'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ',
        '2KObtM-HzMp4xmQdk03TzkaCaYtNQWoIigB62q7FsHE',
        'mailto:teamfromcampus@gmail.com'
    ]);
    echo "<p style='color: green;'>VAPID keys inserted successfully</p>";
    
    // Verify tables
    echo "<h2>Verifying Tables...</h2>";
    $tables = ['push_subscriptions', 'push_notification_logs', 'vapid_keys'];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p style='color: green;'>Table '$table': EXISTS</p>";
        } else {
            echo "<p style='color: red;'>Table '$table': MISSING</p>";
        }
    }
    
    // Verify VAPID keys
    $stmt = $pdo->query("SELECT * FROM vapid_keys LIMIT 1");
    $vapid = $stmt->fetch();
    if ($vapid) {
        echo "<p style='color: green;'>VAPID keys found in database</p>";
        echo "<p>Public Key: " . substr($vapid['public_key'], 0, 30) . "...</p>";
        echo "<p>Subject: " . $vapid['subject'] . "</p>";
    } else {
        echo "<p style='color: red;'>No VAPID keys found</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Database Setup Complete!</h3>";
    echo "<p>All tables have been created and VAPID keys have been inserted.</p>";
    echo "</div>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li><strong>Test Push Notifications:</strong> <a href='/test-push-notifications.html' target='_blank'>Test Now</a></li>";
    echo "<li><strong>Clear Browser Cache:</strong> Press Ctrl+F5</li>";
    echo "<li><strong>Try Subscribing:</strong> Click 'Subscribe to Notifications'</li>";
    echo "<li><strong>Test Job Posting:</strong> <a href='/adminqeIUgwefgWEOAjx/add_job' target='_blank'>Post a Job</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Check your database connection and permissions.</p>";
}
?>

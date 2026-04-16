<?php
/**
 * FCM Database Setup Script
 * Creates the necessary tables for Firebase Cloud Messaging
 */

require_once '.hta_config/config.php';

echo "Setting up FCM database tables...\n\n";

try {
    // Create fcm_tokens table
    $sql = "CREATE TABLE IF NOT EXISTS fcm_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token VARCHAR(255) NOT NULL UNIQUE,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1,
        INDEX idx_active (is_active),
        INDEX idx_last_active (last_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Created fcm_tokens table\n";

    // Create fcm_notification_logs table for tracking
    $sql2 = "CREATE TABLE IF NOT EXISTS fcm_notification_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token_id INT,
        title VARCHAR(255),
        body TEXT,
        data JSON,
        status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
        response TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (token_id) REFERENCES fcm_tokens(id) ON DELETE SET NULL,
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql2);
    echo "✓ Created fcm_notification_logs table\n";

    echo "\n✅ FCM database setup completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Verify Service Account credentials in .hta_config/config.php:\n";
    echo "   - FIREBASE_PROJECT_ID\n";
    echo "   - FIREBASE_PRIVATE_KEY\n";
    echo "   - FIREBASE_CLIENT_EMAIL\n";
    echo "   (These are already configured for OAuth2 authentication)\n";
    echo "2. Get your VAPID Key from Firebase Console:\n";
    echo "   - Go to Project Settings > Cloud Messaging > Web Push certificates\n";
    echo "   - Copy the 'Key pair'\n";
    echo "3. Update firebase-config.js with your VAPID key\n";
    echo "4. Test by posting a new job - notifications will be sent automatically!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

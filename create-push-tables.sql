-- Push Notification Database Tables
-- Run this SQL in your database to create all necessary tables

-- 1. Push Subscriptions Table
-- Stores user subscription information for push notifications
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `endpoint` VARCHAR(500) NOT NULL,
    `p256dh_key` VARCHAR(255) NOT NULL,
    `auth_key` VARCHAR(255) NOT NULL,
    `user_agent` VARCHAR(255),
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_active` BOOLEAN DEFAULT TRUE,
    UNIQUE KEY `unique_endpoint` (`endpoint`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Push Notification Logs Table  
-- Logs all notification delivery attempts
CREATE TABLE IF NOT EXISTS `push_notification_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subscription_id` INT,
    `notification_type` ENUM('new_job', 'job_update', 'custom') NOT NULL,
    `job_id` INT,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `payload` JSON,
    `status` ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    `error_message` TEXT,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscription_id`) REFERENCES `push_subscriptions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. VAPID Keys Table
-- Stores VAPID authentication keys for push notifications
CREATE TABLE IF NOT EXISTS `vapid_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `public_key` VARCHAR(255) NOT NULL,
    `private_key` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Insert VAPID Keys (Your real keys)
INSERT INTO `vapid_keys` (`public_key`, `private_key`, `subject`) 
VALUES (
    'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ',
    '2KObtM-HzMp4xmQdk03TzkaCaYtNQWoIigB62q7FsHE',
    'mailto:teamfromcampus@gmail.com'
) ON DUPLICATE KEY UPDATE 
    `public_key` = VALUES(`public_key`), 
    `private_key` = VALUES(`private_key`), 
    `subject` = VALUES(`subject`);

-- 5. Create Indexes for Better Performance
CREATE INDEX IF NOT EXISTS `idx_subscription_status` ON `push_subscriptions` (`is_active`);
CREATE INDEX IF NOT EXISTS `idx_notification_type` ON `push_notification_logs` (`notification_type`);
CREATE INDEX IF NOT EXISTS `idx_notification_status` ON `push_notification_logs` (`status`);
CREATE INDEX IF NOT EXISTS `idx_sent_at` ON `push_notification_logs` (`sent_at`);

-- 6. Verification Queries (Optional - Run to check if tables were created)
-- SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('push_subscriptions', 'push_notification_logs', 'vapid_keys');
-- SELECT * FROM vapid_keys LIMIT 1;

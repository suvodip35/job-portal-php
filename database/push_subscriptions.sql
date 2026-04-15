-- Push notification subscriptions table
CREATE TABLE IF NOT EXISTS push_subscriptions (
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
);

-- Push notification logs table
CREATE TABLE IF NOT EXISTS push_notification_logs (
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
);

-- VAPID keys table for server authentication
CREATE TABLE IF NOT EXISTS vapid_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    public_key VARCHAR(255) NOT NULL,
    private_key VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default VAPID keys (you should generate your own)
INSERT IGNORE INTO vapid_keys (public_key, private_key, subject) VALUES 
('YOUR_PUBLIC_KEY_HERE', 'YOUR_PRIVATE_KEY_HERE', 'mailto:admin@fromcampus.com');

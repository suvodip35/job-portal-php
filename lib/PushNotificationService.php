<?php

class PushNotificationService {
    private $pdo;
    private $vapidKeys;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadVapidKeys();
    }
    
    private function loadVapidKeys() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM vapid_keys LIMIT 1");
            $this->vapidKeys = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->vapidKeys) {
                throw new Exception('VAPID keys not found in database');
            }
        } catch (Exception $e) {
            error_log("Error loading VAPID keys: " . $e->getMessage());
            // Fallback to environment variables or default values
            $this->vapidKeys = [
                'public_key' => $_ENV['VAPID_PUBLIC_KEY'] ?? 'YOUR_PUBLIC_KEY_HERE',
                'private_key' => $_ENV['VAPID_PRIVATE_KEY'] ?? 'YOUR_PRIVATE_KEY_HERE',
                'subject' => $_ENV['VAPID_SUBJECT'] ?? 'mailto:admin@fromcampus.com'
            ];
        }
    }
    
    /**
     * Send push notification for new job
     */
    public function sendNewJobNotification($jobData) {
        $title = "New Job: " . $jobData['job_title'];
        $message = "Company: " . $jobData['company_name'] . " | Location: " . $jobData['location'];
        
        $payload = [
            'title' => $title,
            'body' => $message,
            'icon' => '/assets/logo/fc_logo_crop.webp',
            'badge' => '/favicon.ico',
            'tag' => 'new-job-' . $jobData['job_id'],
            'data' => [
                'url' => '/job/' . $jobData['job_title_slug'],
                'job_id' => $jobData['job_id'],
                'notification_type' => 'new_job',
                'timestamp' => time()
            ],
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'View Job'
                ]
            ]
        ];
        
        return $this->sendToAllSubscribers('new_job', $jobData['job_id'], $title, $message, $payload);
    }
    
    /**
     * Send push notification for job update
     */
    public function sendJobUpdateNotification($jobData) {
        $title = "Job Updated: " . $jobData['job_title'];
        $message = "Company: " . $jobData['company_name'] . " | Important updates available";
        
        $payload = [
            'title' => $title,
            'body' => $message,
            'icon' => '/assets/logo/fc_logo_crop.webp',
            'badge' => '/favicon.ico',
            'tag' => 'job-update-' . $jobData['job_id'],
            'data' => [
                'url' => '/job/' . $jobData['job_title_slug'],
                'job_id' => $jobData['job_id'],
                'notification_type' => 'job_update',
                'timestamp' => time()
            ],
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'View Updates'
                ]
            ]
        ];
        
        return $this->sendToAllSubscribers('job_update', $jobData['job_id'], $title, $message, $payload);
    }
    
    /**
     * Send custom notification
     */
    public function sendCustomNotification($title, $message, $url = '/', $data = []) {
        $payload = [
            'title' => $title,
            'body' => $message,
            'icon' => '/assets/logo/fc_logo_crop.webp',
            'badge' => '/favicon.ico',
            'tag' => 'custom-' . uniqid(),
            'data' => array_merge([
                'url' => $url,
                'notification_type' => 'custom',
                'timestamp' => time()
            ], $data)
        ];
        
        return $this->sendToAllSubscribers('custom', null, $title, $message, $payload);
    }
    
    /**
     * Send notification to all active subscribers
     */
    private function sendToAllSubscribers($notificationType, $jobId, $title, $message, $payload) {
        try {
            // Get all active subscriptions
            $stmt = $this->pdo->prepare("SELECT * FROM push_subscriptions WHERE is_active = 1");
            $stmt->execute();
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($subscriptions)) {
                return ['success' => true, 'message' => 'No active subscribers', 'sent_count' => 0];
            }
            
            $sentCount = 0;
            $failedCount = 0;
            $errors = [];
            
            foreach ($subscriptions as $subscription) {
                try {
                    $result = $this->sendPushNotification($subscription, $payload);
                    
                    if ($result['success']) {
                        $sentCount++;
                        $this->logNotification($subscription['id'], $notificationType, $jobId, $title, $message, $payload, 'sent');
                    } else {
                        $failedCount++;
                        $this->logNotification($subscription['id'], $notificationType, $jobId, $title, $message, $payload, 'failed', $result['error']);
                        $errors[] = "Subscription ID {$subscription['id']}: " . $result['error'];
                    }
                    
                } catch (Exception $e) {
                    $failedCount++;
                    $errorMsg = "Subscription ID {$subscription['id']}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    $this->logNotification($subscription['id'], $notificationType, $jobId, $title, $message, $payload, 'failed', $errorMsg);
                    
                    // Deactivate problematic subscription
                    $this->deactivateSubscription($subscription['id']);
                }
            }
            
            return [
                'success' => $sentCount > 0,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_subscribers' => count($subscriptions),
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            error_log("Error in sendToAllSubscribers: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send push notification to a single subscription
     */
    private function sendPushNotification($subscription, $payload) {
        try {
            // Prepare subscription data
            $endpoint = $subscription['endpoint'];
            $keys = [
                'p256dh' => $subscription['p256dh_key'],
                'auth' => $subscription['auth_key']
            ];
            
            // Create Web Push subscription
            $webPushSubscription = [
                'endpoint' => $endpoint,
                'keys' => $keys
            ];
            
            // Prepare VAPID authentication
            $vapidAuth = [
                'VAPID' => [
                    'subject' => $this->vapidKeys['subject'],
                    'publicKey' => $this->vapidKeys['public_key'],
                    'privateKey' => $this->vapidKeys['private_key']
                ]
            ];
            
            // Send notification using cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'TTL: 2419200' // 28 days TTL
            ]);
            
            // Add VAPID headers
            $authHeader = $this->generateVAPIDAuthHeader($endpoint, $vapidAuth['VAPID']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
                'Content-Type: application/json',
                'TTL: 2419200',
                'Authorization: ' . $authHeader['Authorization'],
                'Crypto-Key: ' . $authHeader['Crypto-Key']
            ]));
            
            // Set payload
            $encryptedPayload = $this->encryptPayload(json_encode($payload), $keys);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedPayload);
            
            // Execute request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            // Check response
            if ($httpCode >= 200 && $httpCode < 300) {
                // Update last used timestamp
                $this->updateLastUsed($subscription['id']);
                return ['success' => true];
            } else {
                throw new Exception("HTTP error: " . $httpCode . " - " . $response);
            }
            
        } catch (Exception $e) {
            error_log("Error sending push notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate VAPID authentication header
     */
    private function generateVAPIDAuthHeader($endpoint, $vapid) {
        // This is a simplified version. In production, use a proper Web Push library
        $timestamp = time();
        $jwtHeader = base64url_encode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $jwtPayload = base64url_encode(json_encode([
            'aud' => parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST),
            'sub' => $vapid['subject'],
            'exp' => $timestamp + 3600
        ]));
        
        // This would need proper ES256 signature generation
        $signature = 'SIGNATURE_HERE'; // Simplified for example
        
        return [
            'Authorization' => 'WebPush ' . $jwtHeader . '.' . $jwtPayload . '.' . $signature,
            'Crypto-Key' => 'p256ecdsa=' . $vapid['publicKey']
        ];
    }
    
    /**
     * Encrypt payload (simplified version)
     */
    private function encryptPayload($payload, $keys) {
        // This is a simplified version. In production, use proper encryption
        // For now, we'll send unencrypted payload (not recommended for production)
        return $payload;
    }
    
    /**
     * Log notification attempt
     */
    private function logNotification($subscriptionId, $notificationType, $jobId, $title, $message, $payload, $status, $errorMessage = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO push_notification_logs 
                (subscription_id, notification_type, job_id, title, message, payload, status, error_message) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $subscriptionId,
                $notificationType,
                $jobId,
                $title,
                $message,
                json_encode($payload),
                $status,
                $errorMessage
            ]);
        } catch (Exception $e) {
            error_log("Error logging notification: " . $e->getMessage());
        }
    }
    
    /**
     * Update subscription last used timestamp
     */
    private function updateLastUsed($subscriptionId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE push_subscriptions SET last_used_at = NOW() WHERE id = ?");
            $stmt->execute([$subscriptionId]);
        } catch (Exception $e) {
            error_log("Error updating last used timestamp: " . $e->getMessage());
        }
    }
    
    /**
     * Deactivate problematic subscription
     */
    private function deactivateSubscription($subscriptionId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE push_subscriptions SET is_active = 0 WHERE id = ?");
            $stmt->execute([$subscriptionId]);
        } catch (Exception $e) {
            error_log("Error deactivating subscription: " . $e->getMessage());
        }
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats($days = 30) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    notification_type,
                    status,
                    COUNT(*) as count,
                    DATE(sent_at) as date
                FROM push_notification_logs 
                WHERE sent_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY notification_type, status, DATE(sent_at)
                ORDER BY date DESC, notification_type, status
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cleanup old subscriptions and logs
     */
    public function cleanup($days = 90) {
        try {
            // Deactivate inactive subscriptions
            $stmt = $this->pdo->prepare("
                UPDATE push_subscriptions 
                SET is_active = 0 
                WHERE last_used_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            
            // Delete old logs
            $stmt = $this->pdo->prepare("
                DELETE FROM push_notification_logs 
                WHERE sent_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            
            return ['success' => true, 'message' => 'Cleanup completed'];
        } catch (Exception $e) {
            error_log("Error during cleanup: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Helper function for base64 URL encoding
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

?>

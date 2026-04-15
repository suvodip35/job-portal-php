<?php

/**
 * Firebase Cloud Messaging Service
 * Handles sending push notifications via FCM
 */

class FCMNotificationService {
    private $pdo;
    private $serverKey;
    
    public function __construct($pdo, $serverKey) {
        $this->pdo = $pdo;
        $this->serverKey = $serverKey;
    }
    
    /**
     * Send notification to all active FCM tokens
     */
    public function sendToAll($title, $body, $data = []) {
        try {
            // Get all active tokens
            $stmt = $this->pdo->prepare("SELECT id, token FROM fcm_tokens WHERE is_active = 1");
            $stmt->execute();
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($tokens)) {
                return [
                    'success' => false,
                    'message' => 'No active FCM tokens found',
                    'sent_count' => 0,
                    'failed_count' => 0
                ];
            }
            
            return $this->sendNotification($tokens, $title, $body, $data);
            
        } catch (Exception $e) {
            error_log("Error sending to all FCM tokens: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'sent_count' => 0,
                'failed_count' => 0
            ];
        }
    }
    
    /**
     * Send notification to specific tokens
     */
    private function sendNotification($tokens, $title, $body, $data = []) {
        $sentCount = 0;
        $failedCount = 0;
        $errors = [];
        
        // Prepare notification payload
        $notification = [
            'title' => $title,
            'body' => $body,
            'icon' => '/assets/logo/fc_logo_crop.webp',
            'badge' => '/favicon.ico',
            'sound' => 'default',
            'click_action' => '/',
            'tag' => 'job-notification'
        ];
        
        // Android specific settings
        $android = [
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/assets/logo/fc_logo_crop.webp',
                'color' => '#28a745',
                'sound' => 'default',
                'click_action' => '/'
            ]
        ];
        
        // Web specific settings
        $webpush = [
            'headers' => [
                'Urgency' => 'high'
            ],
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/assets/logo/fc_logo_crop.webp',
                'badge' => '/favicon.ico',
                'tag' => 'job-notification',
                'requireInteraction' => false,
                'renotify' => true
            ]
        ];
        
        // Split tokens into chunks (FCM supports max 1000 tokens per request)
        $tokenChunks = array_chunk($tokens, 1000);
        
        foreach ($tokenChunks as $chunk) {
            $tokenList = array_column($chunk, 'token');
            
            // Prepare FCM payload
            $payload = [
                'registration_ids' => $tokenList,
                'notification' => $notification,
                'data' => array_merge($data, [
                    'click_action' => '/',
                    'notification_type' => $data['notification_type'] ?? 'general'
                ]),
                'android' => $android,
                'webpush' => $webpush,
                'priority' => 'high'
            ];
            
            // Send to FCM
            $result = $this->sendToFCM($payload);
            
            if ($result['success']) {
                $sentCount += $result['sent_count'];
                $failedCount += $result['failed_count'];
                
                // Log results
                $this->logResults($chunk, $title, $body, $data, $result);
                
                if (!empty($result['errors'])) {
                    $errors = array_merge($errors, $result['errors']);
                }
            } else {
                $failedCount += count($chunk);
                $errors[] = $result['error'];
            }
        }
        
        return [
            'success' => $sentCount > 0,
            'message' => "Sent to $sentCount devices, failed on $failedCount devices",
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }
    
    /**
     * Send payload to FCM servers
     */
    private function sendToFCM($payload) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL error: ' . $error,
                'sent_count' => 0,
                'failed_count' => count($payload['registration_ids'])
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $sentCount = $result['success'] ?? 0;
            $failedCount = $result['failure'] ?? 0;
            $errors = [];
            
            // Process individual results
            if (isset($result['results'])) {
                foreach ($result['results'] as $index => $itemResult) {
                    if (isset($itemResult['error'])) {
                        $tokenId = $payload['registration_ids'][$index];
                        $errors[] = "Token $tokenId: " . $itemResult['error'];
                        
                        // Deactivate invalid tokens
                        if (in_array($itemResult['error'], ['NotRegistered', 'InvalidRegistration'])) {
                            $this->deactivateToken($tokenId);
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'response' => $result
            ];
        } else {
            return [
                'success' => false,
                'error' => "HTTP error: $httpCode - $response",
                'sent_count' => 0,
                'failed_count' => count($payload['registration_ids'])
            ];
        }
    }
    
    /**
     * Deactivate invalid token
     */
    private function deactivateToken($token) {
        try {
            $stmt = $this->pdo->prepare("UPDATE fcm_tokens SET is_active = 0 WHERE token = ?");
            $stmt->execute([$token]);
        } catch (Exception $e) {
            error_log("Error deactivating token: " . $e->getMessage());
        }
    }
    
    /**
     * Log notification results
     */
    private function logResults($tokens, $title, $body, $data, $result) {
        try {
            foreach ($tokens as $token) {
                $status = ($result['sent_count'] > 0) ? 'sent' : 'failed';
                $response = json_encode($result['response'] ?? []);
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO fcm_notification_logs (token_id, title, body, data, status, response) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $token['id'],
                    $title,
                    $body,
                    json_encode($data),
                    $status,
                    $response
                ]);
            }
        } catch (Exception $e) {
            error_log("Error logging FCM results: " . $e->getMessage());
        }
    }
    
    /**
     * Get active token count
     */
    public function getActiveTokenCount() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM fcm_tokens WHERE is_active = 1");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error getting token count: " . $e->getMessage());
            return 0;
        }
    }
}
?>

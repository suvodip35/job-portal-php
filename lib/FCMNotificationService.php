<?php

/**
 * Firebase Cloud Messaging Service (FCM v1 API with OAuth2)
 * Handles sending push notifications via FCM using modern OAuth2 authentication
 */

class FCMNotificationService {
    private $pdo;
    private $projectId;
    private $clientEmail;
    private $privateKey;
    private $accessToken = null;
    private $tokenExpiry = 0;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->projectId = FIREBASE_PROJECT_ID;
        $this->clientEmail = FIREBASE_CLIENT_EMAIL;
        $this->privateKey = FIREBASE_PRIVATE_KEY;
    }
    
    /**
     * Format private key for OpenSSL
     */
    private function formatPrivateKey($key) {
        // If already has headers, return as-is
        if (strpos($key, '-----BEGIN PRIVATE KEY-----') !== false) {
            return $key;
        }
        
        // Clean up the key - remove all whitespace and newlines
        $key = preg_replace('/\s+/', '', $key);
        
        // Remove any existing headers/footers if present in the middle
        $key = str_replace('-----BEGINPRIVATEKEY-----', '', $key);
        $key = str_replace('-----ENDPRIVATEKEY-----', '', $key);
        
        // Format with proper line breaks (64 chars per line as per PEM spec)
        $formatted = "-----BEGIN PRIVATE KEY-----\n";
        $formatted .= chunk_split($key, 64, "\n");
        $formatted .= "-----END PRIVATE KEY-----\n";
        
        return $formatted;
    }
    
    /**
     * Get OAuth2 access token using service account
     */
    private function getAccessToken() {
        // Return cached token if still valid
        if ($this->accessToken && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }
        
        try {
            // Verify constants are defined
            if (!defined('FIREBASE_CLIENT_EMAIL') || !defined('FIREBASE_PRIVATE_KEY')) {
                error_log("FCM OAuth2 error: Firebase constants not defined");
                return null;
            }
            
            // Create JWT header
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            
            // Create JWT claim set (JWT assertion)
            $now = time();
            $claimSet = json_encode([
                'iss' => FIREBASE_CLIENT_EMAIL,
                'sub' => FIREBASE_CLIENT_EMAIL,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600
            ]);
            
            // Base64URL encode header and claims
            $headerEncoded = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
            $claimSetEncoded = rtrim(strtr(base64_encode($claimSet), '+/', '-_'), '=');
            
            // Create signature input
            $signatureInput = $headerEncoded . '.' . $claimSetEncoded;
            
            // Format and load private key
            $privateKey = $this->formatPrivateKey(FIREBASE_PRIVATE_KEY);
            
            // Sign the JWT
            $signature = '';
            $signSuccess = openssl_sign(
                $signatureInput, 
                $signature, 
                $privateKey, 
                'sha256'
            );
            
            if (!$signSuccess) {
                $errors = [];
                while ($error = openssl_error_string()) {
                    $errors[] = $error;
                }
                $errorStr = implode(', ', $errors);
                error_log("FCM OAuth2 signing failed: $errorStr");
                error_log("Key format check - has headers: " . (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false ? 'yes' : 'no'));
                error_log("Key length: " . strlen($privateKey));
                return null;
            }
            
            // Base64URL encode signature
            $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            
            // Create JWT
            $jwt = $signatureInput . '.' . $signatureEncoded;
            
            // Exchange JWT for access token
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                error_log("FCM OAuth2 cURL error: $curlError");
                return null;
            }
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if (isset($data['access_token'])) {
                    $this->accessToken = $data['access_token'];
                    $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600) - 60;
                    return $this->accessToken;
                }
                error_log("FCM OAuth2: No access_token in response");
                return null;
            } else {
                $errorData = json_decode($response, true);
                $errorMsg = $errorData['error_description'] ?? $errorData['error'] ?? $response;
                error_log("FCM OAuth2 token request failed: HTTP $httpCode - $errorMsg");
                return null;
            }
        } catch (Exception $e) {
            error_log("FCM OAuth2 error: " . $e->getMessage());
            return null;
        }
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
     * Send notification to specific tokens using FCM v1 API
     */
    private function sendNotification($tokens, $title, $body, $data = []) {
        $sentCount = 0;
        $failedCount = 0;
        $errors = [];
        
        // Get OAuth2 access token
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to obtain OAuth2 access token',
                'sent_count' => 0,
                'failed_count' => count($tokens)
            ];
        }
        
        // FCM v1 API endpoint
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        
        // Send to each token individually (FCM v1 API doesn't support batch registration_ids)
        foreach ($tokens as $tokenData) {
            $token = $tokenData['token'];
            $tokenId = $tokenData['id'];
            
            // Prepare FCM v1 payload
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => array_merge($data, [
                        'click_action' => $data['url'] ?? '/',
                        'notification_type' => $data['notification_type'] ?? 'general'
                    ]),
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'notification' => [
                            'icon' => '/assets/logo/fc_logo_crop.webp',
                            'badge' => '/favicon.ico',
                            'tag' => 'job-notification',
                            'requireInteraction' => false,
                            'renotify' => true
                        ],
                        'fcm_options' => [
                            'link' => $data['url'] ?? '/'
                        ]
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => '/assets/logo/fc_logo_crop.webp',
                            'color' => '#28a745',
                            'sound' => 'default',
                            'click_action' => 'OPEN_JOB'
                        ]
                    ]
                ]
            ];
            
            // Send to FCM v1 API
            $result = $this->sendToFCM($url, $accessToken, $message);
            
            if ($result['success']) {
                $sentCount++;
                $this->logResult($tokenId, $title, $body, $data, 'sent', $result['response']);
            } else {
                $failedCount++;
                $errors[] = "Token $tokenId: " . $result['error'];
                $this->logResult($tokenId, $title, $body, $data, 'failed', $result['error']);
                
                // Deactivate invalid tokens
                if (in_array($result['error_code'], ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'])) {
                    $this->deactivateToken($token);
                }
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
     * Send message to FCM v1 API
     */
    private function sendToFCM($url, $accessToken, $message) {
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL error: ' . $error,
                'error_code' => 'CURL_ERROR'
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200) {
            return [
                'success' => true,
                'response' => $result,
                'name' => $result['name'] ?? null
            ];
        } else {
            $errorCode = $result['error']['code'] ?? 'UNKNOWN_ERROR';
            $errorMessage = $result['error']['message'] ?? $result['error']['status'] ?? 'Unknown error';
            
            return [
                'success' => false,
                'error' => "HTTP $httpCode: $errorMessage",
                'error_code' => $errorCode,
                'response' => $result
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
     * Log single notification result
     */
    private function logResult($tokenId, $title, $body, $data, $status, $response) {
        try {
            $responseJson = is_array($response) ? json_encode($response) : $response;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO fcm_notification_logs (token_id, title, body, data, status, response) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tokenId,
                $title,
                $body,
                json_encode($data),
                $status,
                $responseJson
            ]);
        } catch (Exception $e) {
            error_log("Error logging FCM result: " . $e->getMessage());
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

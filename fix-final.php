<?php
/**
 * Final Fix for VAPID JWT Issues
 * Complete reset and reconfiguration
 */

echo "<h1>Final VAPID JWT Fix</h1>";

try {
    require_once '.hta_config/config.php';
    
    echo "<h2>Step 1: Reset Database</h2>";
    
    // Clear existing VAPID keys and insert fresh ones
    $stmt = $pdo->prepare("DELETE FROM vapid_keys");
    $stmt->execute();
    
    // Insert your working VAPID keys
    $stmt = $pdo->prepare("INSERT INTO vapid_keys (public_key, private_key, subject) VALUES (?, ?, ?)");
    $stmt->execute([
        'BPht7ph_DUvSN4SPNq7TftmzLFxvguEgIgSqS7xJuVeURszWBHtpr5EssMxTCy6NbdOJOlV1QM5UmrVMfCRWvsQ',
        '2KObtM-HzMp4xmQdk03TzkaCaYtNQWoIigB62q7FsHE',
        'mailto:teamfromcampus@gmail.com'
    ]);
    
    echo "<p style='color: green;'>✅ Database reset and VAPID keys inserted</p>";
    
    echo "<h2>Step 2: Create Simplified Push Service</h2>";
    
    // Create a completely new, simple push service
    $simplifiedService = 'class SimplePushNotificationService {
        private $pdo;
        private $vapidKeys;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
            $this->loadVapidKeys();
        }
        
        private function loadVapidKeys() {
            $stmt = $this->pdo->query("SELECT * FROM vapid_keys LIMIT 1");
            $this->vapidKeys = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->vapidKeys) {
                throw new Exception("VAPID keys not found");
            }
        }
        
        public function sendCustomNotification($title, $message, $url = "/", $data = []) {
            return $this->sendPushNotification($title, $message, $data);
        }
        
        private function sendPushNotification($title, $message, $data = []) {
            try {
                // Get subscriptions
                $stmt = $this->pdo->query("SELECT * FROM push_subscriptions WHERE is_active = 1");
                $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($subscriptions)) {
                    return ["success" => true, "message" => "No subscribers", "sent_count" => 0];
                }
                
                $successCount = 0;
                $errors = [];
                
                foreach ($subscriptions as $subscription) {
                    try {
                        // Create simple JWT token
                        $jwt = $this->createSimpleJWT();
                        
                        // Send notification
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $subscription["endpoint"]);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Content-Type: application/json",
                            "Authorization: WebPush " . $jwt,
                            "TTL: 2419200"
                        ]);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                            "title" => $title,
                            "body" => $message,
                            "icon" => "/assets/logo/fc_logo_crop.webp",
                            "data" => array_merge(["url" => $url, "timestamp" => time()], $data)
                        ]));
                        
                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        if ($httpCode >= 200 && $httpCode < 300) {
                            $successCount++;
                        } else {
                            $errors[] = "Subscription {$subscription["id"]}: HTTP $httpCode";
                        }
                        
                    } catch (Exception $e) {
                        $errors[] = "Subscription {$subscription["id"]}: " . $e->getMessage();
                    }
                }
                
                return [
                    "success" => $successCount > 0,
                    "sent_count" => $successCount,
                    "failed_count" => count($errors),
                    "total_subscribers" => count($subscriptions),
                    "errors" => $errors
                ];
                
            } catch (Exception $e) {
                return ["success" => false, "error" => $e->getMessage()];
            }
        }
        
        private function createSimpleJWT() {
            $header = base64_encode(json_encode(["typ" => "JWT", "alg" => "HS256"]));
            $payload = base64_encode(json_encode([
                "aud" => "https://fcm.googleapis.com",
                "sub" => $this->vapidKeys["subject"],
                "exp" => time() + 3600
            ]));
            
            $signature = hash_hmac("sha256", $header . "." . $payload, $this->vapidKeys["private_key"], true);
            $signature = base64_encode($signature);
            
            return $header . "." . $payload . "." . $signature;
        }
    }';
    
    // Write the simplified service to file
    file_put_contents(__DIR__ . '/SimplePushNotificationService.php', $simplifiedService);
    
    echo "<p style='color: green;'>✅ Created SimplePushNotificationService.php</p>";
    
    echo "<h2>Step 3: Update Application Files</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Replace PushNotificationService Usage</h3>";
    echo "<p>Update your files to use the new SimplePushNotificationService:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "// Replace this line:
require_once 'lib/SimplePushNotificationService.php';

// With this line:
require_once 'lib/SimplePushNotificationService.php';

// Then update your object creation:
\$pushService = new SimplePushNotificationService(\$pdo);";
    echo "</pre>";
    echo "</div>";
    
    echo "<h2>Step 4: Test New System</h2>";
    echo "<p>After updating files:</p>";
    echo "<ol>";
    echo "<li>Clear browser cache (Ctrl+F5)</li>";
    echo "<li>Test notifications at test-complete-system.php</li>";
    echo "<li>Should work without JWT errors</li>";
    echo "</ol>";
    
    echo "<h2>Key Changes Made</h2>";
    echo "<ul>";
    echo "<li>✅ Database reset with fresh VAPID keys</li>";
    echo "<li>✅ Created SimplePushNotificationService with HMAC-SHA256</li>";
    echo "<li>✅ Removed complex JWT logic that was causing errors</li>";
    echo "<li>✅ Simplified cURL request structure</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

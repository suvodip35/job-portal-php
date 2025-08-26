<?php
require __DIR__ . '/vendor/autoload.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (Exception $e) {
    // In production, don't echo $e->getMessage()
    error_log('DB Connection error: ' . $e->getMessage());
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

// Get subscriptions
$stmt = $pdo->query("SELECT * FROM push_subscriptions");
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get latest job
$job = $pdo->query("SELECT * FROM jobs WHERE status='published' ORDER BY posted_date DESC LIMIT 1")->fetch();

$auth = [
    'VAPID' => [
        'subject' => 'mailto:admin@example.com',
        'publicKey' => '<?= PUBLIC_VAPID_KEY ?>',
        'privateKey' => '<?= PRIVATE_VAPID_KEY ?>',
    ],
];

$webPush = new WebPush($auth);

foreach ($subs as $s) {
    $sub = Subscription::create([
        'endpoint' => $s['endpoint'],
        'publicKey' => $s['p256dh'],
        'authToken' => $s['auth'],
    ]);

    $payload = json_encode([
        "title" => $job['job_title'],
        "body" => $job['company_name'] . " – " . $job['location'],
        "url" => BASE_URL . "job/" . $job['job_title_slug']
    ]);

    $webPush->sendOneNotification($sub, $payload);
}

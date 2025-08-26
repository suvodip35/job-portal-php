<?php
// error handler function
function setupErrorLogger($logFile = __DIR__ . '/error_files.txt') {
    // Set timezone for accurate timestamps
    date_default_timezone_set('Asia/Kolkata');

    // Custom error handler
    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logFile) {
        $date = date('Y-m-d H:i:s');
        $message = "[$date] ERROR: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
        return true; // prevent default PHP error handler
    });

    // Fatal error handler
    register_shutdown_function(function () use ($logFile) {
        $error = error_get_last();
        if ($error !== null) {
            $date = date('Y-m-d H:i:s');
            $message = "[$date] FATAL: {$error['message']} in {$error['file']} on line {$error['line']}" . PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);
        }
    });

    // Exception handler
    set_exception_handler(function ($exception) use ($logFile) {
        $date = date('Y-m-d H:i:s');
        $message = "[$date] EXCEPTION: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}" . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
    });
}

// Call the function at the very top of header
setupErrorLogger();
?>

<?php
require __DIR__ . '/vendor/autoload.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'suvo_jnp');
define('DB_USER', 'suvo_jnp');
define('DB_PASS', 'Simple2pass');
define('DB_CHARSET', 'utf8');

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (Exception $e) {
    error_log('DB Connection error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database connection failed.']);
    exit;
}

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if(!$data) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid JSON']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO push_subscriptions (endpoint, p256dh, auth) VALUES (:endpoint, :p256dh, :auth)");
    $stmt->execute([
        ':endpoint' => $data['endpoint'],
        ':p256dh' => $data['keys']['p256dh'],
        ':auth' => $data['keys']['auth']
    ]);
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}

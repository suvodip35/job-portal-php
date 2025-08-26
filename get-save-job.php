<?php
require_once __DIR__ . '/.hta_config/config.php';

// Include your logger function
function setupErrorLogger($logFile = __DIR__ . '/error_files.txt') {
    date_default_timezone_set('Asia/Kolkata');

    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logFile) {
        $date = date('Y-m-d H:i:s');
        $message = "[$date] ERROR: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
        return true;
    });

    register_shutdown_function(function () use ($logFile) {
        $error = error_get_last();
        if ($error !== null) {
            $date = date('Y-m-d H:i:s');
            $message = "[$date] FATAL: {$error['message']} in {$error['file']} on line {$error['line']}" . PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);
        }
    });

    set_exception_handler(function ($exception) use ($logFile) {
        $date = date('Y-m-d H:i:s');
        $message = "[$date] EXCEPTION: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}" . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
    });
}

// enable logger
setupErrorLogger();

// always set header before any output
header("Content-Type: application/json");

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $slugs = $input['slugs'] ?? [];

    if (empty($slugs)) {
        echo json_encode([]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($slugs), '?'));

    $sql = "SELECT * FROM jobs WHERE job_title_slug IN ($placeholders) AND status='published'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($slugs);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($jobs);

} catch (Exception $e) {
    // Log exception (already handled by setupErrorLogger), but also return safe JSON
    http_response_code(500);
    echo json_encode([
        "error" => "Server error. Please try again later."
    ]);
}

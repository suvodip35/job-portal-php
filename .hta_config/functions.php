<?php
// functions.php - helper utilities
require_once __DIR__ . '/config.php';

/**
 * Escape output for HTML
 */
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generate slug from string
 */
function slugify(string $text): string {
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, '-');
    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * Unique slug generator for jobs or posts
 */
function unique_slug(PDO $pdo, string $table, string $column, string $base_slug, int $id = 0): string {
    $slug = $base_slug;
    $i = 1;
    while (true) {
        if ($id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = ? AND job_id != ?");
            $stmt->execute([$slug, $id]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?");
            $stmt->execute([$slug]);
        }
        $count = (int)$stmt->fetchColumn();
        if ($count === 0) break;
        $i++;
        $slug = $base_slug . '-' . $i;
    }
    return $slug;
}

/**
 * CSRF token generation & validation
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_check(string $token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        // echo "Session Token = " . $_SESSION['csrf_token'] . " / Token = " . $token;
        http_response_code(400);
        die('CSRF validation failed.');
    }
}

/**
 * Require admin login
 */
function require_admin() {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo '<script>window.location.href="/admin/login"</script>';
        // header('Location: login.php');
        exit;
    }
}

/**
 * Pagination helper
 */
function paginate(int $total, int $perPage, int $current, string $baseUrl): string {
    $pages = ceil($total / $perPage);
    $html = '<nav class="mt-4">';
    for ($i=1;$i<=$pages;$i++) {
        $active = $i === $current ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200';
        $html .= '<a href="'.e($baseUrl).'&page='.$i.'" class="px-3 py-1 rounded mx-1 '.$active.'">'.$i.'</a>';
    }
    $html .= '</nav>';
    return $html;
}

$indianStates = [
    "andhra-pradesh" => "Andhra Pradesh",
    "arunachal-pradesh" => "Arunachal Pradesh",
    "assam" => "Assam",
    "bihar" => "Bihar",
    "chhattisgarh" => "Chhattisgarh",
    "goa" => "Goa",
    "gujarat" => "Gujarat",
    "haryana" => "Haryana",
    "himachal-pradesh" => "Himachal Pradesh",
    "jharkhand" => "Jharkhand",
    "karnataka" => "Karnataka",
    "kerala" => "Kerala",
    "madhya-pradesh" => "Madhya Pradesh",
    "maharashtra" => "Maharashtra",
    "manipur" => "Manipur",
    "meghalaya" => "Meghalaya",
    "mizoram" => "Mizoram",
    "nagaland" => "Nagaland",
    "odisha" => "Odisha",
    "punjab" => "Punjab",
    "rajasthan" => "Rajasthan",
    "sikkim" => "Sikkim",
    "tamil-nadu" => "Tamil Nadu",
    "telangana" => "Telangana",
    "tripura" => "Tripura",
    "uttar-pradesh" => "Uttar Pradesh",
    "uttarakhand" => "Uttarakhand",
    "west-bengal" => "West Bengal"
];



function blinkTag($text = "NEW", $bg = "#ef4444") {
    return '
    <style>
        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0; }
        }
    </style>
    <span style="
        display:inline-block;
        padding:2px 6px;
        font-size:10px;
        font-weight:bold;
        border-radius:4px;
        background:'.$bg.';
        color:#fff;
        animation:blink 1s infinite;
    ">'.$text.'</span>';
}

function compressImage($source, $destination, $quality = 80, $maxWidth = 600, $maxHeight = 400) {
    $info = getimagesize($source);
    if ($info === false) {
        return false;
    }

    list($width, $height) = $info;
    $mime = $info['mime'];

    // Aspect ratio maintain করে resize করা
    $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // PNG & WEBP transparency handle
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    imagecopyresampled($newImage, $image, 0, 0, 0, 0,
        $newWidth, $newHeight, $width, $height);

    // Save compressed image
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($newImage, $destination, $quality);
            break;
        case 'image/png':
            imagepng($newImage, $destination, 6); // compression level 0-9
            break;
        case 'image/webp':
            imagewebp($newImage, $destination, $quality);
            break;
    }

    imagedestroy($image);
    imagedestroy($newImage);

    return true;
}

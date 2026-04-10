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
        echo '<script>window.location.href="/adminqeIUgwefgWEOAjx/login"</script>';
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
    "anywhere-india" => "Anywhere India",
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

// Define book categories matching job categories
$bookCategories = [
    [
        'category_id' => 1,
        'category_name' => 'Bank Jobs',
        'category_slug' => 'bank-jobs',
        'description' => 'Books for banking exams like IBPS, SBI, RBI',
        'color' => 'yellow',
        'icon' => 'M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z'
    ],
    [
        'category_id' => 2,
        'category_name' => 'Railway Jobs',
        'category_slug' => 'railway-jobs',
        'description' => 'Books for railway recruitment exams',
        'color' => 'red',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z'
    ],
    [
        'category_id' => 3,
        'category_name' => 'ITI Jobs',
        'category_slug' => 'iti-jobs',
        'description' => 'Books for ITI diploma holder exams',
        'color' => 'blue',
        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    ],
    [
        'category_id' => 4,
        'category_name' => 'Police Jobs',
        'category_slug' => 'police-jobs',
        'description' => 'Books for police recruitment exams',
        'color' => 'indigo',
        'icon' => 'M12 14l9-5-9-5-9 5 9 5z'
    ],
    [
        'category_id' => 5,
        'category_name' => 'Army Jobs',
        'category_slug' => 'army-jobs',
        'description' => 'Books for Indian Army recruitment',
        'color' => 'green',
        'icon' => 'M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7z'
    ],
    [
        'category_id' => 6,
        'category_name' => 'Teaching Jobs',
        'category_slug' => 'teaching-jobs',
        'description' => 'Books for teaching eligibility tests',
        'color' => 'purple',
        'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'
    ],
    [
        'category_id' => 7,
        'category_name' => 'Defence Jobs',
        'category_slug' => 'defence-jobs',
        'description' => 'Books for defence sector exams',
        'color' => 'gray',
        'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'
    ],
    [
        'category_id' => 8,
        'category_name' => 'Engineering Jobs',
        'category_slug' => 'engineering-jobs',
        'description' => 'Books for engineering competitive exams',
        'color' => 'orange',
        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'
    ],
    [
        'category_id' => 9,
        'category_name' => 'Medical Jobs',
        'category_slug' => 'medical-jobs',
        'description' => 'Books for medical and healthcare exams',
        'color' => 'pink',
        'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'
    ],
    [
        'category_id' => 10,
        'category_name' => 'Government Jobs',
        'category_slug' => 'government-jobs',
        'description' => 'Books for various government exams',
        'color' => 'teal',
        'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
    ]
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

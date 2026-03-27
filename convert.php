<?php

$dir = __DIR__ . '/thumbnails';
$files = scandir($dir);

foreach ($files as $file) {
    if (preg_match('/\.(jpg|jpeg|png)$/i', $file)) {
        $source = $dir . '/' . $file;
        $dest = $dir . '/' . preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);

        if (file_exists($dest)) {
            echo "Skip: $file already converted\n";
            continue;
        }

        $imageInfo = getimagesize($source);

        if ($imageInfo['mime'] === 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($imageInfo['mime'] === 'image/png') {
            $image = imagecreatefrompng($source);
        } else {
            continue;
        }

        imagewebp($image, $dest, 80);
        imagedestroy($image);

        echo "Converted: $file → " . basename($dest) . "\n";
    }
}

echo "DONE ✅";

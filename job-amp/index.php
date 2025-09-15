<?php
require_once('../.hta_config/var.php');

// Get the path without query string
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/')); 

// For /updates/ → segments = ['updates']
// For /updates/sample-page → segments = ['updates','sample-page']
$slug = $segments[1] ?? ''; 
// echo "Slug -:" . $slug;
if ($slug === '' || $slug === '_home') {
    require_once('.hta_slug/_home.php');
} elseif (file_exists(".hta_slug/{$slug}.php")) {
    include ".hta_slug/{$slug}.php";
} else {
    require_once('.hta_slug/_404.php');
}

// require_once('../.hta_slug/_footer.php');

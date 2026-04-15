<?php
require_once('../.hta_config/var.php');

// Get the path without query string
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/')); 

// Handle /job/{slug} pattern for AMP
if ($segments[0] === 'amp' && $segments[1] === 'job' && !empty($segments[2])) {
    $_GET['slug'] = $segments[2];
    require_once('.hta_slug/job.php');
}
// Handle existing query string format for backward compatibility
elseif ($segments[0] === 'amp' && $segments[1] === 'job' && !empty($_GET['slug'])) {
    require_once('.hta_slug/job.php');
}
// For /updates/ → segments = ['updates']
// For /updates/sample-page → segments = ['updates','sample-page']
else {
    $slug = $segments[1] ?? ''; 
    // echo "Slug -:" . $slug;
    if ($slug === '' || $slug === '_home') {
        require_once('.hta_slug/_home.php');
    } elseif (file_exists(".hta_slug/{$slug}.php")) {
        include ".hta_slug/{$slug}.php";
    } else {
        require_once('.hta_slug/_404.php');
    }
}

// require_once('../.hta_slug/_footer.php');

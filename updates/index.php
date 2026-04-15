<?php
require_once('../.hta_config/var.php');

// Get the path without query string
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/')); 

// Handle /updates/{slug} pattern (clean URLs)
if (isset($segments[0]) && $segments[0] === 'updates' && isset($segments[1]) && !empty($segments[1]) && $segments[1] !== 'details') {
    $_GET['slug'] = $segments[1];
    require_once('.hta_slug/details.php');
}
// Handle existing query string format for backward compatibility
elseif (isset($segments[0]) && $segments[0] === 'updates' && isset($segments[1]) && $segments[1] === 'details' && !empty($_GET['slug'])) {
    require_once('.hta_slug/details.php');
}
// For /updates/ -> segments = ['updates']
// For /updates/sample-page -> segments = ['updates','sample-page']
elseif (!isset($segments[1]) || $segments[1] === '' || $segments[1] === '_home') {
    require_once('.hta_slug/_home.php');
} elseif (isset($segments[1]) && file_exists(".hta_slug/{$segments[1]}.php")) {
    include ".hta_slug/{$segments[1]}.php";
} else {
    require_once('.hta_slug/_404.php');
}

require_once('../.hta_slug/_footer.php');

<?php
// session_start();
require_once('.hta_config/var.php');

$url = explode('/', $_SERVER['REQUEST_URI']);
$page_to_load = null;

// Handle /job/{slug} pattern
if ($url[1] === 'job' && !empty($url[2])) {
    $_GET['slug'] = $url[2];
    $page_to_load = '.hta_slug/job.php';
}
// Handle existing query string format for backward compatibility
elseif ($url[1] === 'job' && !empty($_GET['slug'])) {
    $page_to_load = '.hta_slug/job.php';
}
else {
    // Extract slug for other pages
    if (strpos($url[1], "?") !== false) {
        $url2 = explode('?', $url[1]);
        $slug=$url2[0];
    } else {
        $slug=$url[1];
    }

    // require_once('.hta_slug/_nav.php');
    if($slug=="") {
        $page_to_load = '.hta_slug/_home.php';
    }
    elseif(file_exists(".hta_slug/".$slug.".php")) {
        $page_to_load = ".hta_slug/".$slug.".php";
    }
    else {
        $page_to_load = '.hta_slug/_404.php';
    }
}

// Load the determined page
if ($page_to_load) {
    require_once($page_to_load);
}

require_once('.hta_slug/_footer.php');
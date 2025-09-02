<?php
session_start();
require_once('.hta_config/var.php');

$url = explode('/', $_SERVER['REQUEST_URI']);
if (strpos($url[1], "?") !== false) {
    $url2 = explode('?', $url[1]);
    $slug=$url2[0];
}   else  $slug=$url[1];


// require_once('.hta_slug/_nav.php');
if($slug=="") require_once('.hta_slug/_home.php');
elseif(file_exists(".hta_slug/".$slug.".php")) include ".hta_slug/".$slug.".php";
else require_once('.hta_slug/_404.php');

require_once('.hta_slug/_footer.php');
<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require_once __DIR__ . '/../../.hta_config/functions.php';
// require_once __DIR__ . '/../functions.php';
session_unset();
session_destroy();
echo "<script>window.location.href='/admin/login'</script>";
// header('Location: login.php');
exit;

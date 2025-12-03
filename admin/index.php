<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// توجيه إلى لوحة التحكم
header("Location: dashboard.php");
exit();
?>
<?php
require_once 'config.php';

// تسجيل الخروج
session_destroy();

// توجيه إلى الصفحة الرئيسية
header("Location: index.php");
exit();
?>
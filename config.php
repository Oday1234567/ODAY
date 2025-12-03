<?php
// إعدادات الموقع
session_start();

define('SITE_NAME', 'الرابطة الطلابية');
define('SITE_URL', 'http://localhost/ra2ba');
define('SITE_DESCRIPTION', 'رابطة طلاب الكلية - حيث تبدأ القيادة');
define('ADMIN_EMAIL', 'admin@ra2ba.edu');

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'ra2ba_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// إعدادات أخرى
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// تصحيح الأخطاء (تعطيل في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');
?>

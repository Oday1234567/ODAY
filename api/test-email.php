<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

try {
    // إرسال بريد اختباري
    $test_subject = 'اختبار إرسال البريد - ' . SITE_NAME;
    $test_message = '
        <h3>اختبار إرسال البريد الإلكتروني</h3>
        <p>هذا بريد اختباري من نظام ' . SITE_NAME . '</p>
        <p>إذا استلمت هذا البريد، فهذا يعني أن إعدادات البريد الإلكتروني تعمل بشكل صحيح.</p>
        <p>التاريخ: ' . date('Y-m-d H:i:s') . '</p>
    ';
    
    $email_sent = sendEmail($_SESSION['user_email'], $test_subject, $test_message);
    
    if ($email_sent) {
        echo json_encode(['success' => true, 'message' => 'تم إرسال بريد الاختبار بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في إرسال البريد']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
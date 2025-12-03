<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

// جلب الإحصائيات الحية
$stats = [];

// عدد الأعضاء
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
$stats['members'] = $stmt->fetchColumn();

// الفعاليات القادمة
$stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND status = 'active'");
$stats['events'] = $stmt->fetchColumn();

// المقالات المنشورة
$stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
$stats['articles'] = $stmt->fetchColumn();

// الرسائل الجديدة
$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
$stats['messages'] = $stmt->fetchColumn();

// طلبات الانضمام المعلقة
$stmt = $pdo->query("SELECT COUNT(*) FROM join_requests WHERE status = 'pending'");
$stats['requests'] = $stmt->fetchColumn();

// زوار الشهر
$stmt = $pdo->query("SELECT SUM(unique_visitors) FROM visitor_stats WHERE YEAR(visit_date) = YEAR(CURDATE()) AND MONTH(visit_date) = MONTH(CURDATE())");
$stats['visitors'] = $stmt->fetchColumn() ?: 0;

echo json_encode($stats);
?>
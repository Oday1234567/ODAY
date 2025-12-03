<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

// جلب البيانات بناء على المعاملات
$period = isset($_GET['period']) ? $_GET['period'] : '7days';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT * FROM visitor_stats 
    WHERE visit_date BETWEEN ? AND ? 
    ORDER BY visit_date ASC
");
$stmt->execute([$start_date, $end_date]);
$visitor_stats = $stmt->fetchAll();

// تصدير كملف CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="analytics-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// كتابة رأس CSV
fputcsv($output, ['التاريخ', 'الزوار الفريدون', 'مشاهدات الصفحة', 'متوسط المشاهدات/زائر']);

// كتابة البيانات
foreach ($visitor_stats as $stat) {
    $avg_views = $stat['unique_visitors'] > 0 ? 
        round($stat['page_views'] / $stat['unique_visitors'], 2) : 0;
    
    fputcsv($output, [
        $stat['visit_date'],
        $stat['unique_visitors'],
        $stat['page_views'],
        $avg_views
    ]);
}

fclose($output);
exit();
?>
<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إحصائيات الزوار";
include 'includes/header.php';

// تحديد الفترة الزمنية
$period = isset($_GET['period']) ? $_GET['period'] : '7days';
$start_date = '';
$end_date = date('Y-m-d');

switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        break;
    case '7days':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        break;
    case '30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        break;
    case '90days':
        $start_date = date('Y-m-d', strtotime('-90 days'));
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        break;
    case 'custom':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-7 days'));
}

// جلب إحصائيات الزوار
$stmt = $pdo->prepare("
    SELECT * FROM visitor_stats 
    WHERE visit_date BETWEEN ? AND ? 
    ORDER BY visit_date ASC
");
$stmt->execute([$start_date, $end_date]);
$visitor_stats = $stmt->fetchAll();

// إحصائيات أساسية
$total_visitors = 0;
$total_page_views = 0;
$unique_visitors = 0;

foreach ($visitor_stats as $stat) {
    $total_visitors += $stat['unique_visitors'];
    $total_page_views += $stat['page_views'];
    $unique_visitors += $stat['unique_visitors'];
}

$average_views = count($visitor_stats) > 0 ? round($total_page_views / count($visitor_stats), 2) : 0;

// تحضير البيانات للرسم البياني
$chart_labels = [];
$chart_visitors = [];
$chart_views = [];

foreach ($visitor_stats as $stat) {
    $chart_labels[] = date('m-d', strtotime($stat['visit_date']));
    $chart_visitors[] = $stat['unique_visitors'];
    $chart_views[] = $stat['page_views'];
}

// أكثر الصفحات زيارة (محاكاة - تحتاج إلى جدول منفصل في التطبيق الحقيقي)
$popular_pages = [
    ['page' => 'الرئيسية', 'visits' => 1245],
    ['page' => 'فعالية اليوم الهندسي', 'visits' => 867],
    ['page' => 'مقال: نصائح للفصل الدراسي', 'visits' => 543],
    ['page' => 'عن الرابطة', 'visits' => 321],
    ['page' => 'اتصل بنا', 'visits' => 287]
];
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">إحصائيات الزوار</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <form method="GET" class="d-flex gap-2">
                        <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>اليوم</option>
                            <option value="7days" <?php echo $period === '7days' ? 'selected' : ''; ?>>آخر 7 أيام</option>
                            <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>آخر 30 يوم</option>
                            <option value="90days" <?php echo $period === '90days' ? 'selected' : ''; ?>>آخر 90 يوم</option>
                            <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>آخر سنة</option>
                            <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>مخصص</option>
                        </select>
                        
                        <?php if($period === 'custom'): ?>
                        <input type="date" name="start_date" class="form-control form-control-sm" 
                               value="<?php echo $start_date; ?>" style="width: 150px;">
                        <input type="date" name="end_date" class="form-control form-control-sm" 
                               value="<?php echo $end_date; ?>" style="width: 150px;">
                        <button type="submit" class="btn btn-primary btn-sm">تطبيق</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- الإحصائيات الأساسية -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">إجمالي الزوار</h6>
                                    <h4 class="mb-0"><?php echo number_format($total_visitors); ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-people fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">مشاهدات الصفحة</h6>
                                    <h4 class="mb-0"><?php echo number_format($total_page_views); ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-eye fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-info border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">متوسط المشاهدات/يوم</h6>
                                    <h4 class="mb-0"><?php echo $average_views; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-graph-up fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">زوار فريدون</h6>
                                    <h4 class="mb-0"><?php echo number_format($unique_visitors); ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-check fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- الرسم البياني الرئيسي -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">حركة الزوار - <?php echo $start_date . ' إلى ' . $end_date; ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="visitorsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- أكثر الصفحات زيارة -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">أكثر الصفحات زيارة</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach($popular_pages as $page): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" style="max-width: 200px;"><?php echo $page['page']; ?></span>
                                    <span class="badge bg-primary rounded-pill"><?php echo $page['visits']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول الإحصائيات التفصيلي -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">التفاصيل اليومية</h5>
                            <a href="export-analytics.php?period=<?php echo $period; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-download"></i> تصدير البيانات
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>التاريخ</th>
                                            <th>الزوار الفريدون</th>
                                            <th>مشاهدات الصفحة</th>
                                            <th>متوسط المشاهدات/زائر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($visitor_stats)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                لا توجد بيانات للإطار الزمني المحدد
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach($visitor_stats as $stat): ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d', strtotime($stat['visit_date'])); ?></td>
                                            <td><?php echo $stat['unique_visitors']; ?></td>
                                            <td><?php echo $stat['page_views']; ?></td>
                                            <td>
                                                <?php echo $stat['unique_visitors'] > 0 ? 
                                                    round($stat['page_views'] / $stat['unique_visitors'], 2) : 0; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// الرسم البياني للزوار
const ctx = document.getElementById('visitorsChart').getContext('2d');
const visitorsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [
            {
                label: 'الزوار الفريدون',
                data: <?php echo json_encode($chart_visitors); ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'مشاهدات الصفحة',
                data: <?php echo json_encode($chart_views); ?>,
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "لوحة التحكم";
include 'includes/header.php';

// جلب الإحصائيات
$total_members = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$upcoming_events = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND status = 'active'")->fetchColumn();
$published_articles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
$new_messages = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM join_requests WHERE status = 'pending'")->fetchColumn();

// إحصائيات الزوار
$total_visitors = $pdo->query("SELECT SUM(unique_visitors) FROM visitor_stats WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
$month_visitors = $pdo->query("SELECT SUM(unique_visitors) FROM visitor_stats WHERE YEAR(visit_date) = YEAR(CURDATE()) AND MONTH(visit_date) = MONTH(CURDATE())")->fetchColumn();

// جلب آخر النشاطات
$recent_activities = $pdo->query("
    (SELECT 'event' as type, title, created_at FROM events ORDER BY created_at DESC LIMIT 2)
    UNION 
    (SELECT 'article' as type, title, created_at FROM articles ORDER BY created_at DESC LIMIT 2)
    UNION
    (SELECT 'message' as type, CONCAT('رسالة من ', name) as title, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 2)
    ORDER BY created_at DESC LIMIT 6
")->fetchAll();

// بيانات الرسم البياني للزوار (آخر 7 أيام)
$visitor_data = [];
$visitor_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT unique_visitors FROM visitor_stats WHERE visit_date = ?");
    $stmt->execute([$date]);
    $visitors = $stmt->fetchColumn();
    $visitor_data[] = $visitors ?: 0;
    $visitor_labels[] = date('m-d', strtotime($date));
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">النظرة العامة</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="btn btn-sm btn-outline-secondary"><?php echo date('Y-m-d'); ?></span>
                    </div>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">الأعضاء</h6>
                                    <h4 class="mb-0"><?php echo $total_members; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-people fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">الفعاليات</h6>
                                    <h4 class="mb-0"><?php echo $upcoming_events; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-calendar-event fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-start border-info border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">المقالات</h6>
                                    <h4 class="mb-0"><?php echo $published_articles; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-file-text fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-start border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">الرسائل</h6>
                                    <h4 class="mb-0"><?php echo $new_messages; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-envelope fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-start border-danger border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">طلبات الانضمام</h6>
                                    <h4 class="mb-0"><?php echo $pending_requests; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-plus fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-start border-secondary border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">زوار الموقع</h6>
                                    <h4 class="mb-0"><?php echo $total_visitors ?: 0; ?></h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-eye fs-2 text-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- رسم بياني للزوار -->
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">إحصائيات الزوار - آخر 7 أيام</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="visitorsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- آخر النشاطات -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">آخر النشاطات</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($recent_activities)): ?>
                                <p class="text-muted text-center">لا توجد نشاطات حديثة</p>
                            <?php else: ?>
                                <?php foreach($recent_activities as $activity): ?>
                                <div class="d-flex border-bottom pb-2 mb-2">
                                    <div class="flex-shrink-0">
                                        <?php if($activity['type'] == 'event'): ?>
                                            <i class="bi bi-calendar-event text-primary"></i>
                                        <?php elseif($activity['type'] == 'article'): ?>
                                            <i class="bi bi-file-text text-success"></i>
                                        <?php else: ?>
                                            <i class="bi bi-envelope text-warning"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?php echo $activity['title']; ?></h6>
                                        <small class="text-muted"><?php echo date('Y-m-d H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الفعاليات القادمة -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">الفعاليات القادمة</h5>
                            <a href="events.php" class="btn btn-sm btn-primary">عرض الكل</a>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $pdo->query("
                                SELECT * FROM events 
                                WHERE event_date >= CURDATE() AND status = 'active' 
                                ORDER BY event_date ASC 
                                LIMIT 5
                            ");
                            $upcoming_events = $stmt->fetchAll();
                            ?>
                            
                            <?php if(empty($upcoming_events)): ?>
                                <p class="text-muted text-center">لا توجد فعاليات قادمة</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>اسم الفعالية</th>
                                                <th>التاريخ</th>
                                                <th>المكان</th>
                                                <th>الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($upcoming_events as $event): ?>
                                            <tr>
                                                <td>
                                                    <a href="event-edit.php?id=<?php echo $event['id']; ?>" class="text-decoration-none">
                                                        <?php echo $event['title']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($event['event_date'])); ?></td>
                                                <td><?php echo $event['location']; ?></td>
                                                <td>
                                                    <span class="badge bg-success">نشطة</span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// رسم بياني للزوار
const ctx = document.getElementById('visitorsChart').getContext('2d');
const visitorsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($visitor_labels); ?>,
        datasets: [{
            label: 'عدد الزوار',
            data: <?php echo json_encode($visitor_data); ?>,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
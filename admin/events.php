<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة الفعاليات";
include 'includes/header.php';

// جلب الفعاليات
$stmt = $pdo->query("
    SELECT e.*, u.name as creator_name 
    FROM events e 
    LEFT JOIN users u ON e.created_by = u.id 
    ORDER BY e.created_at DESC
");
$events = $stmt->fetchAll();

// حذف فعالية
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    
    // حذف الصورة أولاً إذا وجدت
    $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if ($event && $event['image']) {
        $image_path = "../uploads/events/" . $event['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$event_id]);
    header("Location: events.php?success=تم حذف الفعالية بنجاح");
    exit();
}

// تغيير حالة الفعالية
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $event_id = intval($_GET['toggle']);
    $stmt = $pdo->prepare("SELECT status FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    $new_status = $event['status'] === 'active' ? 'draft' : 'active';
    $pdo->prepare("UPDATE events SET status = ? WHERE id = ?")->execute([$new_status, $event_id]);
    
    header("Location: events.php?success=تم تغيير حالة الفعالية");
    exit();
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">إدارة الفعاليات</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="event-add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> إضافة فعالية
                    </a>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>صورة</th>
                                    <th>اسم الفعالية</th>
                                    <th>التاريخ</th>
                                    <th>المكان</th>
                                    <th>المنظم</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($events)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-calendar-event display-4 d-block mb-2"></i>
                                        لا توجد فعاليات
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($events as $index => $event): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if($event['image']): ?>
                                        <img src="../uploads/events/<?php echo $event['image']; ?>" 
                                             alt="<?php echo $event['title']; ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover;" 
                                             class="rounded">
                                        <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-calendar-event text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo $event['title']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo substr($event['description'], 0, 50); ?>...</small>
                                    </td>
                                    <td>
                                        <?php echo date('Y-m-d', strtotime($event['event_date'])); ?>
                                        <br>
                                        <small class="text-muted"><?php echo $event['event_time']; ?></small>
                                    </td>
                                    <td><?php echo $event['location']; ?></td>
                                    <td><?php echo $event['creator_name']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $event['status'] === 'active' ? 'نشطة' : 'مسودة'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="event-edit.php?id=<?php echo $event['id']; ?>" 
                                               class="btn btn-outline-primary" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="events.php?toggle=<?php echo $event['id']; ?>" 
                                               class="btn btn-outline-<?php echo $event['status'] === 'active' ? 'warning' : 'success'; ?>" 
                                               title="<?php echo $event['status'] === 'active' ? 'تعطيل' : 'تفعيل'; ?>">
                                                <i class="bi bi-<?php echo $event['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                            </a>
                                            <a href="events.php?delete=<?php echo $event['id']; ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذه الفعالية؟')"
                                               title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
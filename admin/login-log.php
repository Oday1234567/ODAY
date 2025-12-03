<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "سجل الدخول";
include 'includes/header.php';

// جلب سجل الدخول
$stmt = $pdo->query("
    SELECT ll.*, u.name as user_name 
    FROM login_logs ll 
    LEFT JOIN users u ON ll.user_id = u.id 
    ORDER BY ll.login_time DESC 
    LIMIT 100
");
$login_logs = $stmt->fetchAll();
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">سجل الدخول</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-primary fs-6">
                        <?php echo count($login_logs); ?> محاولة
                    </span>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>عنوان IP</th>
                                    <th>وكيل المستخدم</th>
                                    <th>وقت الدخول</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($login_logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                                        لا توجد سجلات دخول
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($login_logs as $index => $log): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo $log['user_name']; ?></strong>
                                        <?php if($log['user_id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-primary">أنت</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo $log['ip_address']; ?></code>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="<?php echo $log['user_agent']; ?>">
                                            <?php echo substr($log['user_agent'], 0, 50); ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('Y-m-d H:i:s', strtotime($log['login_time'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">ناجح</span>
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
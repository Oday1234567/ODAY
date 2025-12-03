<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$pageTitle = "الرسائل";
include 'includes/header.php';
include 'includes/navbar.php';

// جلب الرسائل
$stmt = $pdo->query("
    SELECT * FROM contact_messages 
    ORDER BY created_at DESC
");
$messages = $stmt->fetchAll();

// تحديث حالة الرسالة كمقروءة
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?")->execute([$message_id]);
    header("Location: messages.php");
    exit();
}

// حذف الرسالة
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$message_id]);
    header("Location: messages.php");
    exit();
}
?>

<main class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-5 fw-bold text-primary">الرسائل الواردة</h1>
                <div class="badge bg-primary fs-6">
                    <?php echo count($messages); ?> رسالة
                </div>
            </div>

            <?php if(empty($messages)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-envelope display-4 d-block mb-3"></i>
                    <h4>لا توجد رسائل</h4>
                    <p class="text-muted">لم يتم استلام أي رسائل حتى الآن</p>
                </div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المرسل</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الموضوع</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($messages as $message): ?>
                                    <tr class="<?php echo $message['status'] === 'new' ? 'table-warning' : ''; ?>">
                                        <td>
                                            <strong><?php echo $message['name']; ?></strong>
                                        </td>
                                        <td><?php echo $message['email']; ?></td>
                                        <td>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $message['id']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo $message['subject']; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $message['status'] === 'new' ? 'warning' : 'success'; ?>">
                                                <?php echo $message['status'] === 'new' ? 'جديدة' : 'تم الرد'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if($message['status'] === 'new'): ?>
                                                <a href="messages.php?mark_read=<?php echo $message['id']; ?>" 
                                                   class="btn btn-outline-success" title="وضع علامة مقروء">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="messages.php?delete=<?php echo $message['id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')"
                                                   title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal لعرض الرسالة -->
                                    <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?php echo $message['subject']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>المرسل:</strong> <?php echo $message['name']; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>البريد الإلكتروني:</strong> 
                                                            <a href="mailto:<?php echo $message['email']; ?>">
                                                                <?php echo $message['email']; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <strong>التاريخ:</strong> 
                                                            <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="message-content">
                                                        <strong>الرسالة:</strong>
                                                        <p class="mt-2"><?php echo nl2br($message['message']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="mailto:<?php echo $message['email']; ?>?subject=رد على: <?php echo $message['subject']; ?>" 
                                                       class="btn btn-primary">
                                                        <i class="bi bi-reply"></i> الرد على الرسالة
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
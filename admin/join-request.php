<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "طلبات الانضمام";
include 'includes/header.php';

// جلب طلبات الانضمام
$stmt = $pdo->query("
    SELECT * FROM join_requests 
    ORDER BY created_at DESC
");
$requests = $stmt->fetchAll();

// معالجة الطلبات
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $request_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $pdo->prepare("UPDATE join_requests SET status = 'approved' WHERE id = ?")->execute([$request_id]);
        
        // إرسال بريد إلكتروني للموافقة
        $stmt = $pdo->prepare("SELECT * FROM join_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();
        
        $subject = "موافقة على طلب الانضمام - " . SITE_NAME;
        $message = "
            <h3>مرحباً {$request['name']}!</h3>
            <p>نحن سعداء بإعلامك بأن طلب انضمامك إلى " . SITE_NAME . " قد تمت الموافقة عليه.</p>
            <p>يمكنك الآن تسجيل الدخول والبدء في المشاركة في أنشطة الرابطة.</p>
            <p>مع أطيب التمنيات،<br>فريق " . SITE_NAME . "</p>
        ";
        
        sendEmail($request['email'], $subject, $message);
        
        header("Location: join-requests.php?success=تمت الموافقة على الطلب وإرسال بريد الترحيب");
        exit();
        
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE join_requests SET status = 'rejected' WHERE id = ?")->execute([$request_id]);
        header("Location: join-requests.php?success=تم رفض الطلب");
        exit();
        
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM join_requests WHERE id = ?")->execute([$request_id]);
        header("Location: join-requests.php?success=تم حذف الطلب");
        exit();
    }
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">طلبات الانضمام</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-primary fs-6">
                        <?php echo count($requests); ?> طلب
                    </span>
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
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الكليّة</th>
                                    <th>التخصص</th>
                                    <th>المجالات</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($requests)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-person-plus display-4 d-block mb-2"></i>
                                        لا توجد طلبات انضمام
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($requests as $index => $request): ?>
                                <tr class="<?php echo $request['status'] === 'pending' ? 'table-warning' : ($request['status'] === 'approved' ? 'table-success' : 'table-danger'); ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo $request['name']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $request['phone']; ?></small>
                                    </td>
                                    <td><?php echo $request['email']; ?></td>
                                    <td><?php echo $request['college']; ?></td>
                                    <td><?php echo $request['major']; ?></td>
                                    <td>
                                        <small class="text-muted"><?php echo $request['interests']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                            <?php echo $request['status'] === 'pending' ? 'قيد المراجعة' : ($request['status'] === 'approved' ? 'مقبول' : 'مرفوض'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('Y-m-d', strtotime($request['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" data-bs-target="#requestModal<?php echo $request['id']; ?>"
                                                    title="عرض التفاصيل">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <?php if($request['status'] === 'pending'): ?>
                                            <a href="join-requests.php?action=approve&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-outline-success" title="موافقة">
                                                <i class="bi bi-check"></i>
                                            </a>
                                            <a href="join-requests.php?action=reject&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-outline-danger" title="رفض">
                                                <i class="bi bi-x"></i>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <a href="join-requests.php?action=delete&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')"
                                               title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal لعرض التفاصيل -->
                                <div class="modal fade" id="requestModal<?php echo $request['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل طلب الانضمام</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong>الاسم الكامل:</strong> <?php echo $request['name']; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>البريد الإلكتروني:</strong> 
                                                        <a href="mailto:<?php echo $request['email']; ?>"><?php echo $request['email']; ?></a>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong>رقم الجوال:</strong> <?php echo $request['phone'] ?: 'غير متوفر'; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>الكليّة:</strong> <?php echo $request['college']; ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong>التخصص:</strong> <?php echo $request['major']; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>السنة الدراسية:</strong> <?php echo $request['academic_year'] ?: 'غير محدد'; ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <strong>المجالات المهتم بها:</strong>
                                                        <p><?php echo $request['interests'] ?: 'لم يتم تحديد مجالات'; ?></p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <strong>الخبرات السابقة:</strong>
                                                        <p><?php echo $request['experience'] ?: 'لا توجد خبرات سابقة'; ?></p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <strong>المهارات:</strong>
                                                        <p><?php echo $request['skills'] ?: 'لم يتم تحديد مهارات'; ?></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <strong>الدوافع للانضمام:</strong>
                                                        <p><?php echo $request['motivation']; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <?php if($request['status'] === 'pending'): ?>
                                                <a href="join-requests.php?action=approve&id=<?php echo $request['id']; ?>" 
                                                   class="btn btn-success">
                                                    <i class="bi bi-check"></i> موافقة
                                                </a>
                                                <a href="join-requests.php?action=reject&id=<?php echo $request['id']; ?>" 
                                                   class="btn btn-danger">
                                                    <i class="bi bi-x"></i> رفض
                                                </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
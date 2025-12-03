<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة الأعضاء";
include 'includes/header.php';

// جلب الأعضاء
$stmt = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC
");
$members = $stmt->fetchAll();

// حذف عضو
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $member_id = intval($_GET['delete']);
    
    // منع حذف المستخدم الحالي
    if ($member_id != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$member_id]);
        header("Location: members.php?success=تم حذف العضو بنجاح");
        exit();
    } else {
        header("Location: members.php?error=لا يمكن حذف حسابك الشخصي");
        exit();
    }
}

// تغيير حالة العضو
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $member_id = intval($_GET['toggle']);
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    
    $new_status = $member['status'] === 'active' ? 'inactive' : 'active';
    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new_status, $member_id]);
    
    header("Location: members.php?success=تم تغيير حالة العضو");
    exit();
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">إدارة الأعضاء</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="member-add.php" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> إضافة عضو
                    </a>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الصورة</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الصفة</th>
                                    <th>اللجنة</th>
                                    <th>الحالة</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($members)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-people display-4 d-block mb-2"></i>
                                        لا توجد أعضاء
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($members as $index => $member): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-muted"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo $member['name']; ?></strong>
                                        <?php if($member['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-primary">أنت</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $member['email']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $member['role'] === 'admin' ? 'danger' : ($member['role'] === 'editor' ? 'warning' : 'info'); ?>">
                                            <?php echo $member['role'] === 'admin' ? 'مسؤول' : ($member['role'] === 'editor' ? 'محرر' : 'مشرف'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($member['committee']): ?>
                                        <span class="badge bg-secondary"><?php echo $member['committee']; ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $member['status'] === 'active' ? 'نشط' : 'غير نشط'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('Y-m-d', strtotime($member['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="member-edit.php?id=<?php echo $member['id']; ?>" 
                                               class="btn btn-outline-primary" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="members.php?toggle=<?php echo $member['id']; ?>" 
                                               class="btn btn-outline-<?php echo $member['status'] === 'active' ? 'warning' : 'success'; ?>" 
                                               title="<?php echo $member['status'] === 'active' ? 'تعطيل' : 'تفعيل'; ?>">
                                                <i class="bi bi-<?php echo $member['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                            </a>
                                            <?php if($member['id'] != $_SESSION['user_id']): ?>
                                            <a href="members.php?delete=<?php echo $member['id']; ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا العضو؟')"
                                               title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
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
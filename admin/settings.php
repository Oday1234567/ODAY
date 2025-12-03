<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "الإعدادات العامة";
include 'includes/header.php';

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = sanitize($_POST['site_name']);
    $site_description = sanitize($_POST['site_description']);
    $admin_email = sanitize($_POST['admin_email']);
    $contact_phone = sanitize($_POST['contact_phone']);
    $contact_address = sanitize($_POST['contact_address']);
    
    try {
        // في التطبيق الحقيقي، ستكون هناك جدول للإعدادات
        // هنا نستخدم ملف config مؤقتاً
        $config_content = "<?php
// إعدادات الموقع
define('SITE_NAME', '$site_name');
define('SITE_DESCRIPTION', '$site_description');
define('ADMIN_EMAIL', '$admin_email');
// ... باقي الإعدادات
?>";
        
        // في بيئة الإنتاج، سيتم حفظ الإعدادات في قاعدة البيانات
        $_SESSION['success'] = "تم تحديث الإعدادات بنجاح";
        header("Location: settings.php");
        exit();
        
    } catch(Exception $e) {
        $error_message = "حدث خطأ أثناء حفظ الإعدادات: " . $e->getMessage();
    }
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">الإعدادات العامة</h1>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">اسم الموقع</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?php echo SITE_NAME; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">وصف الموقع</label>
                                    <textarea class="form-control" id="site_description" name="site_description" 
                                              rows="3"><?php echo SITE_DESCRIPTION; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">البريد الإلكتروني للإدارة</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="<?php echo ADMIN_EMAIL; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">رقم الهاتف</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="+966 123 456 789">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_address" class="form-label">العنوان</label>
                                    <textarea class="form-control" id="contact_address" name="contact_address" 
                                              rows="2">جامعة الملك سعود - الرياض</textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> حفظ الإعدادات
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات النظام</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>إصدار PHP:</strong> <?php echo phpversion(); ?>
                            </div>
                            <div class="mb-2">
                                <strong>قاعدة البيانات:</strong> MySQL
                            </div>
                            <div class="mb-2">
                                <strong>المسار:</strong> <?php echo __DIR__; ?>
                            </div>
                            <div class="mb-2">
                                <strong>الخادم:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">الإجراءات</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-warning">
                                    <i class="bi bi-arrow-clockwise"></i> إعادة تحميل التخزين المؤقت
                                </button>
                                <button class="btn btn-outline-info">
                                    <i class="bi bi-database"></i> نسخ احتياطي للبيانات
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i> مسح التخزين المؤقت
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
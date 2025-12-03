<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إعدادات البريد الإلكتروني";
include 'includes/header.php';

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtp_host = sanitize($_POST['smtp_host']);
    $smtp_port = sanitize($_POST['smtp_port']);
    $smtp_username = sanitize($_POST['smtp_username']);
    $smtp_password = $_POST['smtp_password'];
    $smtp_encryption = sanitize($_POST['smtp_encryption']);
    
    try {
        // حفظ الإعدادات في قاعدة البيانات
        $settings = [
            'smtp_host' => $smtp_host,
            'smtp_port' => $smtp_port,
            'smtp_username' => $smtp_username,
            'smtp_encryption' => $smtp_encryption
        ];
        
        // إذا تم تقديم كلمة مرور جديدة، قم بتحديثها
        if (!empty($smtp_password)) {
            $settings['smtp_password'] = password_hash($smtp_password, PASSWORD_DEFAULT);
        }
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }
        
        $_SESSION['success'] = "تم تحديث إعدادات البريد الإلكتروني بنجاح";
        header("Location: email-settings.php");
        exit();
        
    } catch(Exception $e) {
        $error_message = "حدث خطأ أثناء حفظ الإعدادات: " . $e->getMessage();
    }
}

// جلب الإعدادات الحالية
$current_settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'");
$settings_data = $stmt->fetchAll();

foreach ($settings_data as $setting) {
    $current_settings[$setting['setting_key']] = $setting['setting_value'];
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">إعدادات البريد الإلكتروني</h1>
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
                        <div class="card-header">
                            <h5 class="card-title mb-0">إعدادات SMTP</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_host" class="form-label">خادم SMTP</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                               value="<?php echo $current_settings['smtp_host'] ?? 'smtp.gmail.com'; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_port" class="form-label">المنفذ</label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                               value="<?php echo $current_settings['smtp_port'] ?? '587'; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="smtp_username" class="form-label">اسم المستخدم</label>
                                    <input type="email" class="form-control" id="smtp_username" name="smtp_username" 
                                           value="<?php echo $current_settings['smtp_username'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="smtp_password" class="form-label">كلمة المرور</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                           placeholder="اتركه فارغاً للحفاظ على كلمة المرور الحالية">
                                    <div class="form-text">استخدم كلمة مرور التطبيق إذا كنت تستخدم Gmail</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="smtp_encryption" class="form-label">التشفير</label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?php echo ($current_settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($current_settings['smtp_encryption'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="">بدون تشفير</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> حفظ الإعدادات
                                </button>
                                
                                <button type="button" class="btn btn-outline-info" onclick="testEmail()">
                                    <i class="bi bi-envelope-check"></i> اختبار الإرسال
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- معلومات وإحصائيات -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">إحصائيات البريد</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <i class="bi bi-envelope"></i> 
                                <strong>إجمالي الردود المرسلة:</strong> 
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) FROM message_replies");
                                echo $stmt->fetchColumn();
                                ?>
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-calendar"></i> 
                                <strong>هذا الشهر:</strong> 
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) FROM message_replies WHERE MONTH(replied_at) = MONTH(CURDATE())");
                                echo $stmt->fetchColumn();
                                ?>
                            </div>
                            <div>
                                <i class="bi bi-check-circle"></i> 
                                <strong>آخر رد:</strong> 
                                <?php
                                $stmt = $pdo->query("SELECT MAX(replied_at) FROM message_replies");
                                $last_reply = $stmt->fetchColumn();
                                echo $last_reply ? date('Y-m-d H:i', strtotime($last_reply)) : 'لا توجد ردود';
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- نصائح -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">نصائح للإعداد</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Gmail:</strong> استخدم منفذ 587 مع TLS
                            </div>
                            <div class="mb-2">
                                <strong>Outlook:</strong> استخدم منفذ 587 مع STARTTLS
                            </div>
                            <div class="mb-2">
                                <strong>Yahoo:</strong> استخدم منفذ 465 مع SSL
                            </div>
                            <div>
                                <strong>ملاحظة:</strong> قد تحتاج إلى تفعيل خيار "تطبيقات أقل أماناً" في Gmail
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// اختبار إرسال البريد
function testEmail() {
    if (confirm('هل تريد إرسال بريد اختباري إلى عنوانك الإلكتروني؟')) {
        // إرسال طلب AJAX لاختبار البريد
        fetch('api/test-email.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم إرسال بريد الاختبار بنجاح!');
                } else {
                    alert('فشل في إرسال بريد الاختبار: ' + data.message);
                }
            })
            .catch(error => {
                alert('حدث خطأ أثناء اختبار البريد: ' + error);
            });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
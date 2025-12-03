<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

// إذا كان المستخدم مسجل الدخول بالفعل، توجيهه إلى لوحة التحكم
if (isLoggedIn()) {
    header("Location: admin/dashboard.php");
    exit();
}

$pageTitle = "تسجيل الدخول";
$no_tracking = true; // عدم تسجيل الزيارات لصفحة التسجيل
include 'includes/header.php';

// معالجة نموذج تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // البحث عن المستخدم
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // تسجيل الدخول ناجح
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        
        // تسجيل محاولة الدخول
        $pdo->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, login_time) VALUES (?, ?, ?, NOW())")
            ->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        
        header("Location: admin/dashboard.php");
        exit();
    } else {
        $error_message = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
    }
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-people display-4 text-primary mb-3"></i>
                        <h2 class="card-title text-primary">تسجيل الدخول</h2>
                        <p class="text-muted">لوحة تحكم الرابطة الطلابية</p>
                    </div>

                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($email) ? $email : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">تذكرني</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="index.php" class="text-muted text-decoration-none">
                            <i class="bi bi-arrow-right"></i> العودة إلى الموقع الرئيسي
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
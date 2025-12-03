<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$pageTitle = "اتصل بنا";
include 'includes/header.php';
include 'includes/navbar.php';

// معالجة نموذج الاتصال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // التحقق من البيانات
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "الاسم مطلوب";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صحيح";
    }
    
    if (empty($subject)) {
        $errors[] = "الموضوع مطلوب";
    }
    
    if (empty($message)) {
        $errors[] = "الرسالة مطلوبة";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $subject, $message]);
            
            // إرسال إشعار بالبريد الإلكتروني (اختياري)
            $email_message = "
                <h3>رسالة جديدة من نموذج الاتصال</h3>
                <p><strong>الاسم:</strong> {$name}</p>
                <p><strong>البريد الإلكتروني:</strong> {$email}</p>
                <p><strong>الموضوع:</strong> {$subject}</p>
                <p><strong>الرسالة:</strong></p>
                <p>{$message}</p>
            ";
            
            sendEmail(ADMIN_EMAIL, "رسالة جديدة من {$name}", $email_message);
            
            $success_message = "شكراً لك! تم إرسال رسالتك بنجاح وسنقوم بالرد عليك قريباً.";
            
            // مسح البيانات
            $name = $email = $subject = $message = '';
            
        } catch(PDOException $e) {
            $error_message = "حدث خطأ أثناء إرسال الرسالة: " . $e->getMessage();
        }
    }
}
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary mb-3">اتصل بنا</h1>
                <p class="lead text-muted">نحن هنا لمساعدتك! لا تتردد في التواصل معنا لأي استفسار</p>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <?php if(isset($success_message)): ?>
                                <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if(isset($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if(isset($errors) && !empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">الاسم الكامل *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($name) ? $name : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">البريد الإلكتروني *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($email) ? $email : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">موضوع الرسالة *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?php echo isset($subject) ? $subject : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">الرسالة *</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" 
                                              required><?php echo isset($message) ? $message : ''; ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-send"></i> إرسال الرسالة
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- معلومات الاتصال -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body text-center">
                            <i class="bi bi-envelope display-4 text-primary mb-3"></i>
                            <h5>البريد الإلكتروني</h5>
                            <p class="text-muted"><?php echo ADMIN_EMAIL; ?></p>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body text-center">
                            <i class="bi bi-telephone display-4 text-success mb-3"></i>
                            <h5>الهاتف</h5>
                            <p class="text-muted">+966 123 456 789</p>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <i class="bi bi-clock display-4 text-warning mb-3"></i>
                            <h5>أوقات العمل</h5>
                            <p class="text-muted">الأحد - الخميس<br>8:00 ص - 4:00 م</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
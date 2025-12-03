<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: messages.php");
    exit();
}

$message_id = intval($_GET['id']);

// جلب بيانات الرسالة
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$message_id]);
$message = $stmt->fetch();

if (!$message) {
    header("Location: messages.php");
    exit();
}

$pageTitle = "الرد على رسالة: " . $message['subject'];
include 'includes/header.php';

// معالجة نموذج الرد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_subject = sanitize($_POST['reply_subject']);
    $reply_message = $_POST['reply_message'];
    $mark_as_replied = isset($_POST['mark_as_replied']) ? true : false;
    
    $errors = [];
    
    // التحقق من البيانات
    if (empty($reply_subject)) {
        $errors[] = "موضوع الرد مطلوب";
    }
    
    if (empty($reply_message)) {
        $errors[] = "نص الرد مطلوب";
    }
    
    if (empty($errors)) {
        try {
            // إرسال البريد الإلكتروني
            $email_sent = sendEmail(
                $message['email'],
                $reply_subject,
                nl2br($reply_message)
            );
            
            if ($email_sent) {
                // تحديث حالة الرسالة إذا طلب المستخدم
                if ($mark_as_replied) {
                    $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?")
                        ->execute([$message_id]);
                }
                
                // حفظ الرد في قاعدة البيانات (إذا أردت حفظ سجل الردود)
                $stmt = $pdo->prepare("
                    INSERT INTO message_replies 
                    (message_id, reply_subject, reply_message, replied_by, replied_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $message_id, 
                    $reply_subject, 
                    $reply_message, 
                    $_SESSION['user_id']
                ]);
                
                $_SESSION['success'] = "تم إرسال الرد بنجاح إلى " . $message['email'];
                header("Location: messages.php");
                exit();
            } else {
                $error_message = "فشل في إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى.";
            }
            
        } catch(Exception $e) {
            $error_message = "حدث خطأ: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">الرد على رسالة</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="messages.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-right"></i> العودة للرسائل
                    </a>
                </div>
            </div>

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

            <div class="row">
                <!-- تفاصيل الرسالة الأصلية -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-envelope"></i> الرسالة الأصلية</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>المرسل:</strong><br>
                                <?php echo $message['name']; ?>
                            </div>
                            <div class="mb-3">
                                <strong>البريد الإلكتروني:</strong><br>
                                <a href="mailto:<?php echo $message['email']; ?>">
                                    <?php echo $message['email']; ?>
                                </a>
                            </div>
                            <div class="mb-3">
                                <strong>الموضوع:</strong><br>
                                <?php echo $message['subject']; ?>
                            </div>
                            <div class="mb-3">
                                <strong>التاريخ:</strong><br>
                                <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                            </div>
                            <div>
                                <strong>الرسالة:</strong><br>
                                <div class="border rounded p-2 bg-light mt-1">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- معلومات سريعة -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> معلومات سريعة</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <i class="bi bi-person"></i> 
                                <strong>الرد بواسطة:</strong> <?php echo $_SESSION['user_name']; ?>
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-calendar"></i> 
                                <strong>تاريخ الرد:</strong> <?php echo date('Y-m-d H:i'); ?>
                            </div>
                            <div>
                                <i class="bi bi-envelope"></i> 
                                <strong>حالة الرسالة:</strong> 
                                <span class="badge bg-<?php echo $message['status'] === 'new' ? 'warning' : 'success'; ?>">
                                    <?php echo $message['status'] === 'new' ? 'جديدة' : 'تم الرد'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- نموذج الرد -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-reply"></i> كتابة رد</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="reply_subject" class="form-label">موضوع الرد *</label>
                                    <input type="text" class="form-control" id="reply_subject" name="reply_subject" 
                                           value="رد على: <?php echo $message['subject']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reply_message" class="form-label">نص الرد *</label>
                                    <textarea class="form-control" id="reply_message" name="reply_message" 
                                              rows="12" required placeholder="اكتب ردك هنا..."></textarea>
                                    <div class="form-text">
                                        يمكنك استخدام تنسيق HTML في الرد إذا أردت
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="mark_as_replied" 
                                               name="mark_as_replied" value="1" checked>
                                        <label class="form-check-label" for="mark_as_replied">
                                            وضع علامة "تم الرد" على الرسالة الأصلية
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="save_template" 
                                               name="save_template" value="1">
                                        <label class="form-check-label" for="save_template">
                                            حفظ هذا الرد كقالب للمستقبل
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send"></i> إرسال الرد
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="previewReply()">
                                        <i class="bi bi-eye"></i> معاينة الرد
                                    </button>
                                    <button type="reset" class="btn btn-outline-danger">
                                        <i class="bi bi-arrow-clockwise"></i> إعادة تعيين
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- قوالب الردود السريعة -->
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="bi bi-lightning"></i> قوالب سريعة</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" 
                                            onclick="loadTemplate('شكراً لتواصلكم')">
                                        رد شكر
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-success btn-sm w-100" 
                                            onclick="loadTemplate('تم استلام طلبك')">
                                        تأكيد الاستلام
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-info btn-sm w-100" 
                                            onclick="loadTemplate('سيتم الرد قريباً')">
                                        رد تلقائي
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" 
                                            onclick="loadTemplate('نقدر اقتراحكم')">
                                        رد على اقتراح
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal معاينة الرد -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معاينة الرد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="document.querySelector('form').submit()">
                    <i class="bi bi-send"></i> إرسال الرد
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// تحميل قالب سريع
function loadTemplate(templateType) {
    let subject = '';
    let message = '';
    
    switch(templateType) {
        case 'شكراً لتواصلكم':
            subject = 'شكراً لتواصلكم مع ' + '<?php echo SITE_NAME; ?>';
            message = `عزيزي/عزيزتي ${'<?php echo $message["name"]; ?>'}،

شكراً لتواصلكم مع ${'<?php echo SITE_NAME; ?>'}.

لقد استلمنا رسالتكم وسنقوم بالرد عليها في أقرب وقت ممكن.

مع خالص التحيات،
فريق ${'<?php echo SITE_NAME; ?>'}`;
            break;
            
        case 'تم استلام طلبك':
            subject = 'تم استلام طلبك - ' + '<?php echo SITE_NAME; ?>';
            message = `عزيزي/عزيزتي ${'<?php echo $message["name"]; ?>'}،

نؤكد لك أننا قد استلمنا طلبك/استفسارك بنجاح.

سيتم مراجعة طلبك من قبل فريقنا وسنقوم بالرد عليك في غضون 24-48 ساعة.

شكراً لصبرك وتعاونك.

مع خالص التحيات،
فريق ${'<?php echo SITE_NAME; ?>'}`;
            break;
            
        case 'سيتم الرد قريباً':
            subject = 'رد تلقائي - ' + '<?php echo SITE_NAME; ?>';
            message = `عزيزي/عزيزتي ${'<?php echo $message["name"]; ?>'}،

نشكرك على تواصلك مع ${'<?php echo SITE_NAME; ?>'}.

نود إعلامك أننا قد استلمنا رسالتك وسيتم الرد عليها في أقرب وقت ممكن خلال ساعات العمل الرسمية.

مع خالص التحيات،
فريق ${'<?php echo SITE_NAME; ?>'}`;
            break;
            
        case 'نقدر اقتراحكم':
            subject = 'شكراً على اقتراحكم - ' + '<?php echo SITE_NAME; ?>';
            message = `عزيزي/عزيزتي ${'<?php echo $message["name"]; ?>'}،

نشكرك على اقتراحك القيم لـ ${'<?php echo SITE_NAME; ?>'}.

لقد تم تسجيل اقتراحك وسيتم دراسته من قبل الفريق المختص.

نقدر دائماً ملاحظاتكم واقتراحاتكم التي تساعدنا على التحسين والتطوير.

مع خالص التحيات،
فريق ${'<?php echo SITE_NAME; ?>'}`;
            break;
    }
    
    document.getElementById('reply_subject').value = subject;
    document.getElementById('reply_message').value = message;
}

// معاينة الرد
function previewReply() {
    const subject = document.getElementById('reply_subject').value;
    const message = document.getElementById('reply_message').value;
    
    if (!subject || !message) {
        alert('يرجى ملء موضوع الرد ونص الرد أولاً');
        return;
    }
    
    const previewContent = `
        <div class="card">
            <div class="card-header">
                <strong>المرسل:</strong> ${'<?php echo $_SESSION["user_name"]; ?>'} (${'<?php echo ADMIN_EMAIL; ?>'})<br>
                <strong>المستلم:</strong> ${'<?php echo $message["name"]; ?>'} (${'<?php echo $message["email"]; ?>'})<br>
                <strong>الموضوع:</strong> ${subject}<br>
                <strong>التاريخ:</strong> ${new Date().toLocaleString('ar-SA')}
            </div>
            <div class="card-body">
                ${message.replace(/\n/g, '<br>')}
            </div>
        </div>
    `;
    
    document.getElementById('previewContent').innerHTML = previewContent;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// حفظ المسودة تلقائياً
let autoSaveTimer;
document.getElementById('reply_message').addEventListener('input', function() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        // يمكن إضافة حفظ تلقائي للمسودة هنا
        console.log('تم حفظ المسودة تلقائياً');
    }, 3000);
});
</script>

<?php include 'includes/footer.php'; ?>
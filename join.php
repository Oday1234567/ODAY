<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$pageTitle = "انضم إلينا";
include 'includes/header.php';
include 'includes/navbar.php';

// معالجة نموذج الانضمام
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $college = sanitize($_POST['college']);
    $major = sanitize($_POST['major']);
    $year = sanitize($_POST['year']);
    $interests = isset($_POST['interests']) ? implode(', ', $_POST['interests']) : '';
    $experience = sanitize($_POST['experience']);
    $skills = sanitize($_POST['skills']);
    $motivation = sanitize($_POST['motivation']);
    
    // التحقق من البيانات
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "الاسم الكامل مطلوب";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صحيح";
    }
    
    if (empty($college)) {
        $errors[] = "الكليّة مطلوبة";
    }
    
    if (empty($major)) {
        $errors[] = "التخصص مطلوب";
    }
    
    // التحقق من عدم التقديم مسبقاً
    $stmt = $pdo->prepare("SELECT id FROM join_requests WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "لقد سبق وتقدمت بطلب انضمام بهذا البريد الإلكتروني";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO join_requests 
                (name, email, phone, college, major, academic_year, interests, experience, skills, motivation, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $college, $major, $year, $interests, $experience, $skills, $motivation]);
            
            $success_message = "شكراً لك! تم استلام طلب انضمامك بنجاح وسيتم مراجعته من قبل الفريق.";
            
            // إرسال إشعار بالبريد الإلكتروني
            $email_message = "
                <h3>طلب انضمام جديد</h3>
                <p><strong>الاسم:</strong> {$name}</p>
                <p><strong>البريد الإلكتروني:</strong> {$email}</p>
                <p><strong>الكليّة:</strong> {$college}</p>
                <p><strong>التخصص:</strong> {$major}</p>
                <p><strong>السنة الدراسية:</strong> {$year}</p>
            ";
            
            sendEmail(ADMIN_EMAIL, "طلب انضمام جديد من {$name}", $email_message);
            
            // مسح البيانات
            $name = $email = $phone = $college = $major = $year = $experience = $skills = $motivation = '';
            
        } catch(PDOException $e) {
            $error_message = "حدث خطأ أثناء إرسال الطلب: " . $e->getMessage();
        }
    }
}
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary mb-3">انضم إلى رابطتنا</h1>
                <p class="lead text-muted">كن جزءاً من فريقنا المتميز وساهم في بناء مجتمع طلابي فعال</p>
            </div>

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
                            <h6>يوجد أخطاء في النموذج:</h6>
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- المعلومات الشخصية -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3 border-bottom pb-2">
                                    <i class="bi bi-person"></i> المعلومات الشخصية
                                </h5>
                            </div>
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
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">رقم الجوال</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($phone) ? $phone : ''; ?>">
                            </div>
                        </div>

                        <!-- المعلومات الأكاديمية -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3 border-bottom pb-2">
                                    <i class="bi bi-book"></i> المعلومات الأكاديمية
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="college" class="form-label">الكليّة *</label>
                                <input type="text" class="form-control" id="college" name="college" 
                                       value="<?php echo isset($college) ? $college : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="major" class="form-label">التخصص *</label>
                                <input type="text" class="form-control" id="major" name="major" 
                                       value="<?php echo isset($major) ? $major : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="year" class="form-label">السنة الدراسية</label>
                                <select class="form-select" id="year" name="year">
                                    <option value="">اختر السنة الدراسية</option>
                                    <option value="الأولى" <?php echo (isset($year) && $year == 'الأولى') ? 'selected' : ''; ?>>الأولى</option>
                                    <option value="الثانية" <?php echo (isset($year) && $year == 'الثانية') ? 'selected' : ''; ?>>الثانية</option>
                                    <option value="الثالثة" <?php echo (isset($year) && $year == 'الثالثة') ? 'selected' : ''; ?>>الثالثة</option>
                                    <option value="الرابعة" <?php echo (isset($year) && $year == 'الرابعة') ? 'selected' : ''; ?>>الرابعة</option>
                                    <option value="خامسة+" <?php echo (isset($year) && $year == 'خامسة+') ? 'selected' : ''; ?>>خامسة فأكثر</option>
                                </select>
                            </div>
                        </div>

                        <!-- المجالات المهتم بها -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3 border-bottom pb-2">
                                    <i class="bi bi-heart"></i> المجالات المهتم بها
                                </h5>
                                <div class="row">
                                    <?php
                                    $interest_areas = [
                                        'الفعاليات والأنشطة',
                                        'التصميم والإبداع',
                                        'التقنية والبرمجة',
                                        'الكتابة والمحتوى',
                                        'التسويق والإعلام',
                                        'التنظيم والإدارة',
                                        'التدريب والتعليم',
                                        'خدمة المجتمع'
                                    ];
                                    ?>
                                    <?php foreach($interest_areas as $area): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="interests[]" 
                                                   value="<?php echo $area; ?>" id="interest_<?php echo $area; ?>"
                                                   <?php echo (isset($_POST['interests']) && in_array($area, $_POST['interests'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="interest_<?php echo $area; ?>">
                                                <?php echo $area; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- الخبرات والمهارات -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3 border-bottom pb-2">
                                    <i class="bi bi-award"></i> الخبرات والمهارات
                                </h5>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="experience" class="form-label">الخبرات السابقة (إن وجدت)</label>
                                <textarea class="form-control" id="experience" name="experience" rows="3"><?php echo isset($experience) ? $experience : ''; ?></textarea>
                                <div class="form-text">اذكر أي خبرات سابقة في العمل التطوعي أو الطلابي</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="skills" class="form-label">المهارات التي تمتلكها</label>
                                <textarea class="form-control" id="skills" name="skills" rows="3"><?php echo isset($skills) ? $skills : ''; ?></textarea>
                                <div class="form-text">اذكر المهارات التي تمتلكها (لغات، برامج، etc.)</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="motivation" class="form-label">لماذا تريد الانضمام إلى الرابطة؟ *</label>
                                <textarea class="form-control" id="motivation" name="motivation" rows="4" required><?php echo isset($motivation) ? $motivation : ''; ?></textarea>
                                <div class="form-text">أخبرنا بدوافعك للانضمام وما الذي تأمل في تحقيقه</div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send"></i> تقديم طلب الانضمام
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-people display-4 text-primary mb-3"></i>
                        <h5>مجتمع نشط</h5>
                        <p class="text-muted">انضم إلى مجتمع طلابي نشط ومتفاعل</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-award display-4 text-success mb-3"></i>
                        <h5>تطوير المهارات</h5>
                        <p class="text-muted">طور مهاراتك القيادية والمهنية</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-briefcase display-4 text-info mb-3"></i>
                        <h5>فرص مميزة</h5>
                        <p class="text-muted">احصل على فرص تدريبية ووظيفية</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
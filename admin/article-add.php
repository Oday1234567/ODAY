<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إضافة مقال جديد";
include 'includes/header.php';

// معالجة النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = $_POST['content'];
    $category = sanitize($_POST['category']);
    $status = sanitize($_POST['status']);
    
    $errors = [];
    
    // التحقق من البيانات
    if (empty($title)) {
        $errors[] = "عنوان المقال مطلوب";
    }
    
    if (empty($content)) {
        $errors[] = "محتوى المقال مطلوب";
    }
    
    if (empty($category)) {
        $errors[] = "التصنيف مطلوب";
    }
    
    if (empty($errors)) {
        try {
            // معالجة رفع الصورة
            $featured_image = '';
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $featured_image = uploadImage($_FILES['featured_image'], 'articles');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO articles 
                (title, content, category, featured_image, status, author_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $title, $content, $category, $featured_image, $status, $_SESSION['user_id']
            ]);
            
            header("Location: articles.php?success=تم إضافة المقال بنجاح");
            exit();
            
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
                <h1 class="h2">إضافة مقال جديد</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="articles.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-right"></i> العودة للقائمة
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

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">عنوان المقال *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo isset($title) ? $title : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="content" class="form-label">محتوى المقال *</label>
                                    <textarea class="form-control" id="content" name="content" 
                                              rows="12" required><?php echo isset($content) ? $content : ''; ?></textarea>
                                    <div class="form-text">يمكنك استخدام HTML للتنسيق</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label">التصنيف *</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">اختر التصنيف</option>
                                        <option value="أخبار" <?php echo (isset($category) && $category == 'أخبار') ? 'selected' : ''; ?>>أخبار</option>
                                        <option value="تطوير" <?php echo (isset($category) && $category == 'تطوير') ? 'selected' : ''; ?>>تطوير</option>
                                        <option value="تعليم" <?php echo (isset($category) && $category == 'تعليم') ? 'selected' : ''; ?>>تعليم</option>
                                        <option value="تقنية" <?php echo (isset($category) && $category == 'تقنية') ? 'selected' : ''; ?>>تقنية</option>
                                        <option value="فعاليات" <?php echo (isset($category) && $category == 'فعاليات') ? 'selected' : ''; ?>>فعاليات</option>
                                        <option value="عام" <?php echo (isset($category) && $category == 'عام') ? 'selected' : ''; ?>>عام</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">الصورة المميزة</label>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <div class="form-text">الصور المسموح بها: JPG, PNG, GIF - الحد الأقصى: 5MB</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">حالة المقال</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="published" <?php echo (isset($status) && $status == 'published') ? 'selected' : ''; ?>>منشور</option>
                                        <option value="draft" <?php echo (isset($status) && $status == 'draft') ? 'selected' : ''; ?>>مسودة</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                        <label class="form-check-label" for="featured">
                                            مقال مميز
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle"></i> نشر المقال
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// معاينة المحتوى
document.getElementById('content').addEventListener('input', function() {
    const preview = document.getElementById('content-preview');
    if (!preview) {
        const previewDiv = document.createElement('div');
        previewDiv.id = 'content-preview';
        previewDiv.className = 'card mt-3';
        previewDiv.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">معاينة المحتوى</h6>
            </div>
            <div class="card-body"></div>
        `;
        this.parentNode.appendChild(previewDiv);
    }
    
    document.querySelector('#content-preview .card-body').innerHTML = this.value;
});
</script>

<?php include 'includes/footer.php'; ?>
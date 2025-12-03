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
    header("Location: articles.php");
    exit();
}

$article_id = intval($_GET['id']);

// جلب بيانات المقال
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: articles.php");
    exit();
}

$pageTitle = "تعديل مقال: " . $article['title'];
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
            $featured_image = $article['featured_image'];
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                // حذف الصورة القديمة إذا وجدت
                if ($featured_image) {
                    $old_image_path = "../uploads/articles/" . $featured_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $featured_image = uploadImage($_FILES['featured_image'], 'articles');
            }
            
            $stmt = $pdo->prepare("
                UPDATE articles SET 
                title = ?, content = ?, category = ?, featured_image = ?, status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $content, $category, $featured_image, $status, $article_id
            ]);
            
            header("Location: articles.php?success=تم تحديث المقال بنجاح");
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
                <h1 class="h2">تعديل مقال: <?php echo $article['title']; ?></h1>
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
                                           value="<?php echo $article['title']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="content" class="form-label">محتوى المقال *</label>
                                    <textarea class="form-control" id="content" name="content" 
                                              rows="12" required><?php echo $article['content']; ?></textarea>
                                    <div class="form-text">يمكنك استخدام HTML للتنسيق</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label">التصنيف *</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">اختر التصنيف</option>
                                        <option value="أخبار" <?php echo $article['category'] == 'أخبار' ? 'selected' : ''; ?>>أخبار</option>
                                        <option value="تطوير" <?php echo $article['category'] == 'تطوير' ? 'selected' : ''; ?>>تطوير</option>
                                        <option value="تعليم" <?php echo $article['category'] == 'تعليم' ? 'selected' : ''; ?>>تعليم</option>
                                        <option value="تقنية" <?php echo $article['category'] == 'تقنية' ? 'selected' : ''; ?>>تقنية</option>
                                        <option value="فعاليات" <?php echo $article['category'] == 'فعاليات' ? 'selected' : ''; ?>>فعاليات</option>
                                        <option value="عام" <?php echo $article['category'] == 'عام' ? 'selected' : ''; ?>>عام</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">الصورة المميزة</label>
                                    <?php if($article['featured_image']): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/articles/<?php echo $article['featured_image']; ?>" 
                                             alt="الصورة الحالية" 
                                             style="max-width: 200px; height: auto;" 
                                             class="img-thumbnail">
                                        <br>
                                        <small class="text-muted">الصورة الحالية</small>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <div class="form-text">اتركه فارغاً للحفاظ على الصورة الحالية</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">حالة المقال</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>منشور</option>
                                        <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>مسودة</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-text">
                                        <strong>المعلومات:</strong><br>
                                        - الكاتب: <?php echo $_SESSION['user_name']; ?><br>
                                        - المشاهدات: <?php echo $article['views']; ?><br>
                                        - تاريخ الإنشاء: <?php echo date('Y-m-d', strtotime($article['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle"></i> تحديث المقال
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
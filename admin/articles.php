<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة المقالات";
include 'includes/header.php';

// جلب المقالات
$stmt = $pdo->query("
    SELECT a.*, u.name as author_name 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
$articles = $stmt->fetchAll();

// حذف مقال
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $article_id = intval($_GET['delete']);
    
    // حذف الصورة أولاً إذا وجدت
    $stmt = $pdo->prepare("SELECT featured_image FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if ($article && $article['featured_image']) {
        $image_path = "../uploads/articles/" . $article['featured_image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$article_id]);
    header("Location: articles.php?success=تم حذف المقال بنجاح");
    exit();
}

// تغيير حالة المقال
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $article_id = intval($_GET['toggle']);
    $stmt = $pdo->prepare("SELECT status FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    $new_status = $article['status'] === 'published' ? 'draft' : 'published';
    $pdo->prepare("UPDATE articles SET status = ? WHERE id = ?")->execute([$new_status, $article_id]);
    
    header("Location: articles.php?success=تم تغيير حالة المقال");
    exit();
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">إدارة المقالات</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="article-add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> إضافة مقال
                    </a>
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
                                    <th>صورة</th>
                                    <th>عنوان المقال</th>
                                    <th>التصنيف</th>
                                    <th>الكاتب</th>
                                    <th>المشاهدات</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($articles)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-file-text display-4 d-block mb-2"></i>
                                        لا توجد مقالات
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($articles as $index => $article): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if($article['featured_image']): ?>
                                        <img src="../uploads/articles/<?php echo $article['featured_image']; ?>" 
                                             alt="<?php echo $article['title']; ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover;" 
                                             class="rounded">
                                        <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-file-text text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo $article['title']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo substr(strip_tags($article['content']), 0, 50); ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $article['category']; ?></span>
                                    </td>
                                    <td><?php echo $article['author_name']; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $article['views']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $article['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                            <?php echo $article['status'] === 'published' ? 'منشور' : 'مسودة'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('Y-m-d', strtotime($article['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="article-edit.php?id=<?php echo $article['id']; ?>" 
                                               class="btn btn-outline-primary" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="articles.php?toggle=<?php echo $article['id']; ?>" 
                                               class="btn btn-outline-<?php echo $article['status'] === 'published' ? 'warning' : 'success'; ?>" 
                                               title="<?php echo $article['status'] === 'published' ? 'إلغاء النشر' : 'نشر'; ?>">
                                                <i class="bi bi-<?php echo $article['status'] === 'published' ? 'eye-slash' : 'eye'; ?>"></i>
                                            </a>
                                            <a href="articles.php?delete=<?php echo $article['id']; ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا المقال؟')"
                                               title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </a>
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
<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header("Location: blog.php");
    exit();
}

$article_id = intval($_GET['id']);

// جلب بيانات المقالة
$stmt = $pdo->prepare("
    SELECT a.*, u.name as author_name 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    WHERE a.id = ? AND a.status = 'published'
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: blog.php");
    exit();
}

// زيادة عدد المشاهدات
$pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article_id]);

$pageTitle = $article['title'];
include 'includes/header.php';
include 'includes/navbar.php';

// جلب المقالات ذات الصلة
$stmt = $pdo->prepare("
    SELECT id, title, created_at 
    FROM articles 
    WHERE category = ? AND id != ? AND status = 'published' 
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->execute([$article['category'], $article_id]);
$related_articles = $stmt->fetchAll();
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <article class="card shadow-sm border-0">
                <?php if($article['featured_image']): ?>
                <img src="uploads/articles/<?php echo $article['featured_image']; ?>" 
                     class="card-img-top" alt="<?php echo $article['title']; ?>"
                     style="max-height: 400px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body p-4">
                    <div class="mb-4">
                        <span class="badge bg-primary mb-2"><?php echo $article['category']; ?></span>
                        <h1 class="display-6 fw-bold text-primary mb-3"><?php echo $article['title']; ?></h1>
                        
                        <div class="d-flex flex-wrap gap-4 text-muted mb-3">
                            <div>
                                <i class="bi bi-person"></i> 
                                كاتب المقال: <?php echo $article['author_name']; ?>
                            </div>
                            <div>
                                <i class="bi bi-calendar"></i> 
                                <?php echo date('Y-m-d', strtotime($article['created_at'])); ?>
                            </div>
                            <div>
                                <i class="bi bi-eye"></i> 
                                <?php echo $article['views'] + 1; ?> مشاهدة
                            </div>
                        </div>
                    </div>

                    <div class="article-content">
                        <?php echo nl2br($article['content']); ?>
                    </div>
                </div>
            </article>

            <!-- مشاركة المقال -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h6 class="card-title text-primary mb-3">مشاركة المقال</h6>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-twitter"></i> تويتر
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-facebook"></i> فيسبوك
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-linkedin"></i> لينكدإن
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-link-45deg"></i> نسخ الرابط
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- الشريط الجانبي -->
        <div class="col-lg-4">
            <!-- المقالات ذات الصلة -->
            <?php if(!empty($related_articles)): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-link"></i> مقالات ذات صلة</h6>
                </div>
                <div class="card-body">
                    <?php foreach($related_articles as $related): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-1">
                            <a href="article.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                <?php echo $related['title']; ?>
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?php echo date('Y-m-d', strtotime($related['created_at'])); ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- معلومات الكاتب -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-person"></i> عن الكاتب</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle display-4 text-muted"></i>
                    </div>
                    <h6><?php echo $article['author_name']; ?></h6>
                    <p class="text-muted small">كاتب في الرابطة الطلابية</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
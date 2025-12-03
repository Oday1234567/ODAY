<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$pageTitle = "المدونة";
include 'includes/header.php';
include 'includes/navbar.php';

// التصنيفات
$categories = ['جميع المقالات', 'أخبار', 'تطوير', 'تعليم', 'تقنية'];

// جلب المقالات مع التصفية
$category = isset($_GET['category']) ? $_GET['category'] : 'جميع المقالات';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

if ($category !== 'جميع المقالات') {
    $sql = "SELECT COUNT(*) FROM articles WHERE status = 'published' AND category = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category]);
    $total_articles = $stmt->fetchColumn();

    $sql = "SELECT a.*, u.name as author_name 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.status = 'published' AND a.category = ? 
            ORDER BY a.created_at DESC 
            LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category, $limit, $offset]);
} else {
    $total_articles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();

    $sql = "SELECT a.*, u.name as author_name 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit, $offset]);
}

$articles = $stmt->fetchAll();
$total_pages = ceil($total_articles / $limit);
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold text-primary mb-4">المدونة</h1>
            
            <!-- تصفية التصنيفات -->
            <div class="mb-4">
                <div class="btn-group flex-wrap" role="group">
                    <?php foreach($categories as $cat): ?>
                    <a href="blog.php?category=<?php echo urlencode($cat); ?>" 
                       class="btn btn-outline-primary <?php echo $category === $cat ? 'active' : ''; ?>">
                        <?php echo $cat; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- قائمة المقالات -->
            <?php if(empty($articles)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-file-text display-4 d-block mb-3"></i>
                    <h4>لا توجد مقالات</h4>
                    <p class="text-muted">سيتم نشر مقالات جديدة قريباً</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($articles as $article): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <?php if($article['featured_image']): ?>
                            <img src="uploads/articles/<?php echo $article['featured_image']; ?>" 
                                 class="card-img-top" alt="<?php echo $article['title']; ?>"
                                 style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="bi bi-file-text display-4 text-muted"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-primary"><?php echo $article['category']; ?></span>
                                </div>
                                
                                <h5 class="card-title"><?php echo $article['title']; ?></h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo substr(strip_tags($article['content']), 0, 120); ?>...
                                </p>
                                
                                <div class="article-meta d-flex justify-content-between align-items-center mt-auto">
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> <?php echo $article['author_name']; ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo date('Y-m-d', strtotime($article['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    قراءة المزيد
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- الترقيم -->
                <?php if($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="blog.php?category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>">
                                السابق
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="blog.php?category=<?php echo urlencode($category); ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="blog.php?category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>">
                                التالي
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- الشريط الجانبي -->
        <div class="col-lg-4">
            <!-- أحدث المقالات -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> أحدث المقالات</h6>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->query("
                        SELECT id, title, created_at 
                        FROM articles 
                        WHERE status = 'published' 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $recent_articles = $stmt->fetchAll();
                    ?>
                    
                    <?php foreach($recent_articles as $recent): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-1">
                            <a href="article.php?id=<?php echo $recent['id']; ?>" class="text-decoration-none">
                                <?php echo $recent['title']; ?>
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?php echo date('Y-m-d', strtotime($recent['created_at'])); ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- التصنيفات -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-tags"></i> التصنيفات</h6>
                </div>
                <div class="card-body">
                    <?php foreach($categories as $cat): ?>
                    <a href="blog.php?category=<?php echo urlencode($cat); ?>" 
                       class="d-block mb-2 text-decoration-none <?php echo $category === $cat ? 'text-primary fw-bold' : 'text-muted'; ?>">
                        <i class="bi bi-arrow-left"></i> <?php echo $cat; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
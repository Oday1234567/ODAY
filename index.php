<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$pageTitle = "الرئيسية";

// جلب الإحصائيات
$stats = getStatistics($pdo);

// جلب الفعاليات القادمة
$stmt = $pdo->query("
    SELECT * FROM events 
    WHERE event_date >= CURDATE() AND status = 'active' 
    ORDER BY event_date ASC 
    LIMIT 3
");
$upcoming_events = $stmt->fetchAll();

// جلب أحدث المقالات
$stmt = $pdo->query("
    SELECT a.*, u.name as author_name 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    WHERE a.status = 'published' 
    ORDER BY a.created_at DESC 
    LIMIT 3
");
$recent_articles = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="container my-4">
    <!-- قسم البطل -->
    <section class="hero-section bg-primary text-white rounded-3 p-5 mb-5 text-center">
        <h1 class="display-4 fw-bold mb-3">مرحباً بكم في الرابطة الطلابية</h1>
        <p class="lead mb-4">منصة طلابية رائدة تهدف إلى تطوير المهارات وبناء القيادات الشبابية</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="events.php" class="btn btn-light btn-lg px-4">
                <i class="bi bi-calendar-event"></i> استكشف الفعاليات
            </a>
            <a href="about.php" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-info-circle"></i> تعرف علينا
            </a>
        </div>
    </section>

    <!-- قسم الإحصائيات -->
    <section class="stats-section mb-5">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4">
                        <i class="bi bi-people display-4 text-primary mb-3"></i>
                        <h3 class="text-primary"><?php echo $stats['total_members']; ?></h3>
                        <p class="text-muted mb-0">عضو نشط</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4">
                        <i class="bi bi-calendar-check display-4 text-success mb-3"></i>
                        <h3 class="text-success"><?php echo $stats['upcoming_events']; ?></h3>
                        <p class="text-muted mb-0">فعالية قادمة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4">
                        <i class="bi bi-file-text display-4 text-info mb-3"></i>
                        <h3 class="text-info"><?php echo $stats['published_articles']; ?></h3>
                        <p class="text-muted mb-0">مقال منشور</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-4">
                        <i class="bi bi-graph-up display-4 text-warning mb-3"></i>
                        <h3 class="text-warning">15+</h3>
                        <p class="text-muted mb-0">مشروع منجز</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- الفعاليات القادمة -->
    <section class="events-section mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">الفعاليات القادمة</h2>
            <a href="events.php" class="btn btn-outline-primary">عرض الكل</a>
        </div>
        
        <div class="row g-4">
            <?php if(empty($upcoming_events)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> لا توجد فعاليات قادمة حالياً
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($upcoming_events as $event): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm event-card">
                        <?php if($event['image']): ?>
                        <img src="uploads/events/<?php echo $event['image']; ?>" class="card-img-top" alt="<?php echo $event['title']; ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-calendar-event display-4 text-muted"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $event['title']; ?></h5>
                            <p class="card-text text-muted"><?php echo substr($event['description'], 0, 100); ?>...</p>
                            <div class="event-meta">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> <?php echo date('Y-m-d', strtotime($event['event_date'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?php echo $event['event_time']; ?>
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> <?php echo $event['location']; ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">التفاصيل والتسجيل</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- أحدث المقالات -->
    <section class="articles-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">أحدث المقالات</h2>
            <a href="blog.php" class="btn btn-outline-primary">عرض الكل</a>
        </div>
        
        <div class="row g-4">
            <?php if(empty($recent_articles)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> لا توجد مقالات حالياً
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($recent_articles as $article): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $article['title']; ?></h5>
                            <p class="card-text text-muted"><?php echo substr(strip_tags($article['content']), 0, 120); ?>...</p>
                            <div class="article-meta d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?php echo $article['author_name']; ?>
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?php echo date('Y-m-d', strtotime($article['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">قراءة المزيد</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
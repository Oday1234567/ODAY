<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$pageTitle = "الفعاليات";
include 'includes/header.php';
include 'includes/navbar.php';

// جلب الفعاليات
$type = isset($_GET['type']) ? $_GET['type'] : 'upcoming';

if ($type === 'past') {
    $stmt = $pdo->query("
        SELECT * FROM events 
        WHERE event_date < CURDATE() AND status = 'active' 
        ORDER BY event_date DESC
    ");
    $pageTitle = "الفعاليات السابقة";
} else {
    $stmt = $pdo->query("
        SELECT * FROM events 
        WHERE event_date >= CURDATE() AND status = 'active' 
        ORDER BY event_date ASC
    ");
    $pageTitle = "الفعاليات القادمة";
}

$events = $stmt->fetchAll();
?>

<main class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-5 fw-bold text-primary">الفعاليات</h1>
                <div class="btn-group">
                    <a href="events.php?type=upcoming" class="btn btn-outline-primary <?php echo $type === 'upcoming' ? 'active' : ''; ?>">
                        القادمة
                    </a>
                    <a href="events.php?type=past" class="btn btn-outline-primary <?php echo $type === 'past' ? 'active' : ''; ?>">
                        السابقة
                    </a>
                </div>
            </div>

            <?php if(empty($events)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                    <h4>لا توجد فعاليات <?php echo $type === 'upcoming' ? 'قادمة' : 'سابقة'; ?></h4>
                    <p class="text-muted">سيتم الإعلان عن الفعاليات القادمة قريباً</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($events as $event): ?>
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="row g-0 h-100">
                                <div class="col-md-4">
                                    <?php if($event['image']): ?>
                                    <img src="uploads/events/<?php echo $event['image']; ?>" class="img-fluid h-100 w-100" alt="<?php echo $event['title']; ?>" style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light h-100 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-calendar-event display-4 text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body d-flex flex-column h-100">
                                        <h5 class="card-title"><?php echo $event['title']; ?></h5>
                                        <p class="card-text text-muted flex-grow-1"><?php echo substr($event['description'], 0, 150); ?>...</p>
                                        
                                        <div class="event-details mb-3">
                                            <div class="d-flex flex-wrap gap-3">
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
                                        
                                        <div class="mt-auto">
                                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">
                                                <?php echo $type === 'upcoming' ? 'التفاصيل والتسجيل' : 'عرض التفاصيل'; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header("Location: events.php");
    exit();
}

$event_id = intval($_GET['id']);

// جلب بيانات الفعالية
$stmt = $pdo->prepare("
    SELECT e.*, u.name as organizer_name 
    FROM events e 
    LEFT JOIN users u ON e.created_by = u.id 
    WHERE e.id = ? AND e.status = 'active'
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: events.php");
    exit();
}

$pageTitle = $event['title'];
include 'includes/header.php';
include 'includes/navbar.php';

// معالجة نموذج التسجيل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $college = sanitize($_POST['college']);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO event_registrations (event_id, name, email, phone, college, registered_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$event_id, $name, $email, $phone, $college]);
        
        $success_message = "تم تسجيلك في الفعالية بنجاح!";
    } catch(PDOException $e) {
        $error_message = "حدث خطأ أثناء التسجيل: " . $e->getMessage();
    }
}

// التحقق مما إذا كان قد تم التسجيل مسبقاً
$is_registered = false;
if (isset($_POST['email'])) {
    $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ?");
    $stmt->execute([$event_id, $_POST['email']]);
    $is_registered = $stmt->fetch() !== false;
}
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- صورة الفعالية -->
            <?php if($event['image']): ?>
            <img src="uploads/events/<?php echo $event['image']; ?>" class="img-fluid rounded-3 mb-4 w-100" alt="<?php echo $event['title']; ?>" style="max-height: 400px; object-fit: cover;">
            <?php endif; ?>

            <!-- تفاصيل الفعالية -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h1 class="card-title display-6 fw-bold text-primary mb-3"><?php echo $event['title']; ?></h1>
                    
                    <div class="event-meta mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar text-primary me-2"></i>
                                    <span><?php echo date('Y-m-d', strtotime($event['event_date'])); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock text-primary me-2"></i>
                                    <span><?php echo $event['event_time']; ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-geo-alt text-primary me-2"></i>
                                    <span><?php echo $event['location']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="event-description">
                        <h4 class="text-primary mb-3">عن الفعالية</h4>
                        <p class="lead"><?php echo nl2br($event['description']); ?></p>
                    </div>

                    <?php if($event['capacity']): ?>
                    <div class="event-capacity mt-4">
                        <div class="alert alert-info">
                            <i class="bi bi-people"></i> 
                            سعة الفعالية: <?php echo $event['capacity']; ?> مقعد
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- نموذج التسجيل -->
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square"></i> التسجيل في الفعالية
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php elseif(isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif($is_registered): ?>
                        <div class="alert alert-warning">لقد سبق وتسجيلك في هذه الفعالية</div>
                    <?php elseif(strtotime($event['event_date']) < time()): ?>
                        <div class="alert alert-info">انتهت فترة التسجيل في هذه الفعالية</div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">الاسم الكامل *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">رقم الجوال</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="college" class="form-label">الكليّة/التخصص</label>
                                <input type="text" class="form-control" id="college" name="college">
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> تأكيد التسجيل
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- معلومات المنظم -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="bi bi-person"></i> منظم الفعالية
                    </h6>
                    <p class="card-text"><?php echo $event['organizer_name']; ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
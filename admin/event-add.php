<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إضافة فعالية جديدة";
include 'includes/header.php';

// معالجة النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = $_POST['description'];
    $event_date = sanitize($_POST['event_date']);
    $event_time = sanitize($_POST['event_time']);
    $location = sanitize($_POST['location']);
    $capacity = sanitize($_POST['capacity']);
    $status = sanitize($_POST['status']);
    
    $errors = [];
    
    // التحقق من البيانات
    if (empty($title)) {
        $errors[] = "اسم الفعالية مطلوب";
    }
    
    if (empty($event_date)) {
        $errors[] = "تاريخ الفعالية مطلوب";
    }
    
    if (empty($location)) {
        $errors[] = "مكان الفعالية مطلوب";
    }
    
    if (empty($errors)) {
        try {
            // معالجة رفع الصورة
            $image_name = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = uploadImage($_FILES['image'], 'events');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO events 
                (title, description, event_date, event_time, location, capacity, image, status, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $title, $description, $event_date, $event_time, 
                $location, $capacity, $image_name, $status, $_SESSION['user_id']
            ]);
            
            header("Location: events.php?success=تم إضافة الفعالية بنجاح");
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
                <h1 class="h2">إضافة فعالية جديدة</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="events.php" class="btn btn-secondary">
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
                                    <label for="title" class="form-label">اسم الفعالية *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo isset($title) ? $title : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">وصف الفعالية *</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="8" required><?php echo isset($description) ? $description : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">تاريخ الفعالية *</label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" 
                                           value="<?php echo isset($event_date) ? $event_date : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="event_time" class="form-label">وقت الفعالية *</label>
                                    <input type="time" class="form-control" id="event_time" name="event_time" 
                                           value="<?php echo isset($event_time) ? $event_time : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="location" class="form-label">مكان الفعالية *</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo isset($location) ? $location : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">سعة المقاعد</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" 
                                           value="<?php echo isset($capacity) ? $capacity : ''; ?>" min="1">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">صورة الغلاف</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="form-text">الصور المسموح بها: JPG, PNG, GIF - الحد الأقصى: 5MB</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">حالة الفعالية</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : ''; ?>>نشطة</option>
                                        <option value="draft" <?php echo (isset($status) && $status == 'draft') ? 'selected' : ''; ?>>مسودة</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle"></i> نشر الفعالية
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['file'])) die('لم يتم اختيار ملف.');
    $f = $_FILES['file'];
    if ($f['error'] !== UPLOAD_ERR_OK) die('خطأ أثناء الرفع.');
    $allowed = ['image/jpeg','image/png','application/pdf'];
    if (!in_array($f['type'], $allowed)) die('نوع الملف غير مسموح.');
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $newname = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $newname)) die('فشل حفظ الملف.');
    $stmt = $pdo->prepare("INSERT INTO uploads (filename, original_name, mime, size, uploaded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$newname, $f['name'], $f['type'], $f['size'], $_SESSION['user_id']]);
    header('Location: /admin/uploads.php?success=1');
    exit;
}
?>
<!-- نموذج رفع بسيط -->
<form method="post" enctype="multipart/form-data">
  <input type="file" name="file">
  <button>رفع</button>
</form>

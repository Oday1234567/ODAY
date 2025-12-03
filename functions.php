<?php
require_once 'db.php';

// دالة للحماية من الهجمات
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// التحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// التحقق من صلاحيات المدير
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// تحميل الصور
function uploadImage($file, $folder = 'general') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('فشل في رفع الملف');
    }
    
    // التحقق من نوع الملف
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('نوع الملف غير مسموح به');
    }
    
    // التحقق من حجم الملف
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('حجم الملف كبير جداً');
    }
    
    // إنشاء المجلد إذا لم يكن موجوداً
    $upload_dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // إنشاء اسم فريد للملف
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    } else {
        throw new Exception('فشل في حفظ الملف');
    }
}

// جلب الإحصائيات
function getStatistics($pdo) {
    $stats = [];
    
    // عدد الأعضاء
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
    $stats['total_members'] = $stmt->fetchColumn();
    
    // الفعاليات القادمة
    $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND status = 'active'");
    $stats['upcoming_events'] = $stmt->fetchColumn();
    
    // المقالات المنشورة
    $stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
    $stats['published_articles'] = $stmt->fetchColumn();
    
    // الرسائل الجديدة
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    $stats['new_messages'] = $stmt->fetchColumn();
    
    return $stats;
}

// تسجيل زائر جديد
function logVisitor($pdo) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $page = $_SERVER['REQUEST_URI'];
    $date = date('Y-m-d');
    
    // التحقق مما إذا تمت زيارة هذا اليوم
    $stmt = $pdo->prepare("SELECT id FROM visitor_stats WHERE visit_date = ?");
    $stmt->execute([$date]);
    
    if ($stmt->fetch()) {
        // تحديث الإحصائيات الموجودة
        $stmt = $pdo->prepare("
            UPDATE visitor_stats 
            SET page_views = page_views + 1 
            WHERE visit_date = ?
        ");
        $stmt->execute([$date]);
    } else {
        // إنشاء سجل جديد
        $stmt = $pdo->prepare("
            INSERT INTO visitor_stats (visit_date, page_views, unique_visitors) 
            VALUES (?, 1, 1)
        ");
        $stmt->execute([$date]);
    }
}

// إرسال البريد الإلكتروني
function sendEmail($to, $subject, $message) {
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>
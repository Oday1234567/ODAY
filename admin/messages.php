<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة الرسائل";
include 'includes/header.php';

// جلب الرسائل مع التصفية
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// بناء الاستعلام
$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// تحديث حالة الرسالة
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?")->execute([$message_id]);
    header("Location: messages.php?success=تم وضع علامة مقروء على الرسالة");
    exit();
}

if (isset($_GET['mark_unread']) && is_numeric($_GET['mark_unread'])) {
    $message_id = intval($_GET['mark_unread']);
    $pdo->prepare("UPDATE contact_messages SET status = 'new' WHERE id = ?")->execute([$message_id]);
    header("Location: messages.php?success=تم وضع علامة غير مقروء على الرسالة");
    exit();
}

// حذف الرسالة
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$message_id]);
    header("Location: messages.php?success=تم حذف الرسالة بنجاح");
    exit();
}

// حذف متعدد
if (isset($_POST['delete_selected']) && isset($_POST['selected_messages'])) {
    $selected_ids = $_POST['selected_messages'];
    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
    $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)")->execute($selected_ids);
    header("Location: messages.php?success=تم حذف الرسائل المحددة بنجاح");
    exit();
}

// وضع علامة مقروء متعدد
if (isset($_POST['mark_read_selected']) && isset($_POST['selected_messages'])) {
    $selected_ids = $_POST['selected_messages'];
    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
    $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id IN ($placeholders)")->execute($selected_ids);
    header("Location: messages.php?success=تم وضع علامة مقروء على الرسائل المحددة");
    exit();
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">إدارة الرسائل</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-primary fs-6">
                        <?php echo count($messages); ?> رسالة
                    </span>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
            <?php endif; ?>

            <!-- أدوات التصفية والبحث -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="filter" class="form-label">تصفية حسب الحالة</label>
                            <select name="filter" id="filter" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>جميع الرسائل</option>
                                <option value="new" <?php echo $filter === 'new' ? 'selected' : ''; ?>>الرسائل الجديدة</option>
                                <option value="replied" <?php echo $filter === 'replied' ? 'selected' : ''; ?>>الرسائل المقروءة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">بحث في الرسائل</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo $search; ?>" placeholder="ابحث في الاسم، البريد، الموضوع...">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <a href="messages.php" class="btn btn-outline-secondary">إعادة تعيين</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">إجمالي الرسائل</h6>
                                    <h4 class="mb-0">
                                        <?php 
                                        $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
                                        echo $stmt->fetchColumn(); 
                                        ?>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-envelope fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">رسائل جديدة</h6>
                                    <h4 class="mb-0">
                                        <?php 
                                        $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
                                        echo $stmt->fetchColumn(); 
                                        ?>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-envelope-exclamation fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">تم الرد</h6>
                                    <h4 class="mb-0">
                                        <?php 
                                        $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'");
                                        echo $stmt->fetchColumn(); 
                                        ?>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-envelope-check fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-start border-info border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted fw-semibold">هذا الشهر</h6>
                                    <h4 class="mb-0">
                                        <?php 
                                        $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
                                        echo $stmt->fetchColumn(); 
                                        ?>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="bi bi-calendar-month fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قائمة الرسائل -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">قائمة الرسائل</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">
                            <i class="bi bi-check-all"></i> تحديد الكل
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="markReadSelected()">
                            <i class="bi bi-envelope-check"></i> وضع مقروء
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSelected()">
                            <i class="bi bi-trash"></i> حذف المحدد
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($messages)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-envelope display-4 d-block mb-3"></i>
                            <h4>لا توجد رسائل</h4>
                            <p class="text-muted">لم يتم العثور على رسائل تطابق معايير البحث</p>
                        </div>
                    <?php else: ?>
                    <form method="POST" id="messagesForm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                        </th>
                                        <th>المرسل</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الموضوع</th>
                                        <th>الرسالة</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($messages as $message): ?>
                                    <tr class="<?php echo $message['status'] === 'new' ? 'table-warning' : ''; ?>">
                                        <td>
                                            <input type="checkbox" name="selected_messages[]" 
                                                   value="<?php echo $message['id']; ?>" 
                                                   class="message-checkbox">
                                        </td>
                                        <td>
                                            <strong><?php echo $message['name']; ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo $message['email']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo $message['email']; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <strong><?php echo $message['subject']; ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                  title="<?php echo htmlspecialchars($message['message']); ?>">
                                                <?php echo substr($message['message'], 0, 50); ?>...
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $message['status'] === 'new' ? 'warning' : 'success'; ?>">
                                                <?php echo $message['status'] === 'new' ? 'جديدة' : 'تم الرد'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#messageModal<?php echo $message['id']; ?>"
                                                        title="عرض الرسالة">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <?php if($message['status'] === 'new'): ?>
                                                <a href="messages.php?mark_read=<?php echo $message['id']; ?>" 
                                                   class="btn btn-outline-success" title="وضع علامة مقروء">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <?php else: ?>
                                                <a href="messages.php?mark_unread=<?php echo $message['id']; ?>" 
                                                   class="btn btn-outline-warning" title="وضع علامة غير مقروء">
                                                    <i class="bi bi-envelope"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="messages.php?delete=<?php echo $message['id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')"
                                                   title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal لعرض الرسالة -->
                                    <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تفاصيل الرسالة</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>المرسل:</strong> <?php echo $message['name']; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>البريد الإلكتروني:</strong> 
                                                            <a href="mailto:<?php echo $message['email']; ?>">
                                                                <?php echo $message['email']; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>الموضوع:</strong> <?php echo $message['subject']; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>التاريخ:</strong> 
                                                            <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <strong>الرسالة:</strong>
                                                            <div class="card mt-2">
                                                                <div class="card-body">
                                                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="mailto:<?php echo $message['email']; ?>?subject=رد على: <?php echo urlencode($message['subject']); ?>" 
                                                       class="btn btn-primary">
                                                        <i class="bi bi-reply"></i> الرد على الرسالة
                                                    </a>
                                                    
                                                    <?php if($message['status'] === 'new'): ?>
                                                    <a href="messages.php?mark_read=<?php echo $message['id']; ?>" 
                                                       class="btn btn-success">
                                                        <i class="bi bi-check"></i> وضع علامة مقروء
                                                    </a>
                                                    <?php else: ?>
                                                    <a href="messages.php?mark_unread=<?php echo $message['id']; ?>" 
                                                       class="btn btn-warning">
                                                        <i class="bi bi-envelope"></i> وضع علامة غير مقروء
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="messages.php?delete=<?php echo $message['id']; ?>" 
                                                       class="btn btn-danger" 
                                                       onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
                                                        <i class="bi bi-trash"></i> حذف
                                                    </a>
                                                    
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- الترقيم (إذا كانت هناك الكثير من الرسائل) -->
            <?php if(count($messages) > 20): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#">السابق</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">التالي</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
// تحديد وإلغاء تحديد الكل
function toggleSelectAll(source) {
    const checkboxes = document.getElementsByClassName('message-checkbox');
    for (let checkbox of checkboxes) {
        checkbox.checked = source.checked;
    }
}

function selectAll() {
    const checkboxes = document.getElementsByClassName('message-checkbox');
    const selectAll = document.getElementById('selectAll');
    let allChecked = true;
    
    for (let checkbox of checkboxes) {
        if (!checkbox.checked) {
            allChecked = false;
            break;
        }
    }
    
    for (let checkbox of checkboxes) {
        checkbox.checked = !allChecked;
    }
    selectAll.checked = !allChecked;
}

// حذف الرسائل المحددة
function deleteSelected() {
    const selected = getSelectedMessages();
    if (selected.length === 0) {
        alert('يرجى تحديد رسائل للحذف');
        return;
    }
    
    if (confirm(`هل أنت متأكد من حذف ${selected.length} رسالة؟`)) {
        document.getElementById('messagesForm').action = 'messages.php';
        document.getElementById('messagesForm').innerHTML += '<input type="hidden" name="delete_selected" value="1">';
        document.getElementById('messagesForm').submit();
    }
}

// وضع علامة مقروء على الرسائل المحددة
function markReadSelected() {
    const selected = getSelectedMessages();
    if (selected.length === 0) {
        alert('يرجى تحديد رسائل لوضع علامة مقروء');
        return;
    }
    
    if (confirm(`هل تريد وضع علامة مقروء على ${selected.length} رسالة؟`)) {
        document.getElementById('messagesForm').action = 'messages.php';
        document.getElementById('messagesForm').innerHTML += '<input type="hidden" name="mark_read_selected" value="1">';
        document.getElementById('messagesForm').submit();
    }
}

// الحصول على الرسائل المحددة
function getSelectedMessages() {
    const checkboxes = document.getElementsByClassName('message-checkbox');
    const selected = [];
    
    for (let checkbox of checkboxes) {
        if (checkbox.checked) {
            selected.push(checkbox.value);
        }
    }
    
    return selected;
}

// البحث الفوري (يمكن تفعيله إذا أردت)
document.getElementById('search').addEventListener('input', function() {
    // يمكن إضافة بحث فوري هنا باستخدام AJAX
});
</script>

<?php include 'includes/footer.php'; ?>
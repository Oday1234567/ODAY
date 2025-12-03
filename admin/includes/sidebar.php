<!-- الشريط الجانبي -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><?php echo SITE_NAME; ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="nav flex-column">
            <a class="nav-link text-white mb-2 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
               href="dashboard.php">
                <i class="bi bi-speedometer2"></i> النظرة العامة
            </a>
            
            <div class="dropdown mb-2">
                <a class="nav-link text-white dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-file-text"></i> المحتوى
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="articles.php">المقالات</a></li>
                    <li><a class="dropdown-item" href="article-add.php">إضافة مقال</a></li>
                </ul>
            </div>
            
            <div class="dropdown mb-2">
                <a class="nav-link text-white dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar-event"></i> الفعاليات
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="events.php">جميع الفعاليات</a></li>
                    <li><a class="dropdown-item" href="event-add.php">إضافة فعالية</a></li>
                </ul>
            </div>
            
            <div class="dropdown mb-2">
                <a class="nav-link text-white dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-people"></i> الأعضاء
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="members.php">قائمة الأعضاء</a></li>
                    <li><a class="dropdown-item" href="join-requests.php">طلبات الانضمام</a></li>
                </ul>
            </div>
            
            <a class="nav-link text-white mb-2 <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" 
               href="messages.php">
                <i class="bi bi-envelope"></i> الرسائل
                <?php
                $stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
                $new_messages = $stmt->fetchColumn();
                if ($new_messages > 0): ?>
                <span class="badge bg-danger float-left"><?php echo $new_messages; ?></span>
                <?php endif; ?>
            </a>
            
            <a class="nav-link text-white mb-2 <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>" 
               href="analytics.php">
                <i class="bi bi-graph-up"></i> الإحصائيات
            </a>
            
            <?php if(isAdmin()): ?>
            <div class="dropdown mb-2">
                <a class="nav-link text-white dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i> الإعدادات
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="settings.php">الإعدادات العامة</a></li>
                    <li><a class="dropdown-item" href="users.php">إدارة المستخدمين</a></li>
                    <li><a class="dropdown-item" href="login-log.php">سجل الدخول</a></li>
                </ul>
            </div>
            <?php endif; ?>
        </nav>
    </div>
</div>
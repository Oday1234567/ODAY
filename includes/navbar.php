<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="bi bi-people-fill me-2"></i>
            <strong><?php echo SITE_NAME; ?></strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house"></i> الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">عن الرابطة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="events.php">الفعاليات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">المدونة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">اتصل بنا</a>
                </li>
            </ul>
            
            <div class="navbar-nav">
                <?php if(isLoggedIn()): ?>
                    <a class="nav-link" href="admin/dashboard.php">
                        <i class="bi bi-speedometer2"></i> لوحة التحكم
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-left"></i> تسجيل الخروج
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">
                        <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
                    </a>
                    <a class="btn btn-outline-light btn-sm me-2" href="join.php">
                        انضم إلينا
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
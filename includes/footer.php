    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted"><?php echo SITE_DESCRIPTION; ?></p>
                </div>
                <div class="col-md-4">
                    <h5>روابط سريعة</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted">الرئيسية</a></li>
                        <li><a href="events.php" class="text-muted">الفعاليات</a></li>
                        <li><a href="blog.php" class="text-muted">المدونة</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>التواصل</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope"></i> <?php echo ADMIN_EMAIL; ?></li>
                        <li><i class="bi bi-telephone"></i> +966 123 456 789</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>
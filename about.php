<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$pageTitle = "عن الرابطة";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">عن الرابطة الطلابية</h1>
                <p class="lead text-muted">نحو بناء جيل قيادي مبدع</p>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h3 class="card-title text-primary mb-3">رؤيتنا</h3>
                    <p class="card-text">أن نكون الرابطة الطلابية الرائدة في بناء الشخصية القيادية وتنمية المهارات لدى الطلاب، والإسهام في إعداد جيل قادر على قيادة المستقبل.</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h3 class="card-title text-primary mb-3">رسالتنا</h3>
                    <p class="card-text">تمكين الطلاب من خلال برامج وأنشطة نوعية تعزز قدراتهم القيادية والمهنية، وتنمي روح العمل الجماعي، وتسهم في بناء شخصيات متكاملة قادرة على الإبداع والابتكار.</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h3 class="card-title text-primary mb-3">أهدافنا</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> تنمية المهارات القيادية لدى الطلاب</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> تعزيز العمل الجماعي وروح الفريق</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> تنظيم فعاليات وبرامج تدريبية نوعية</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> الإسهام في خدمة المجتمع الطلابي</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> بناء شراكات مع المؤسسات المحلية</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="card-title text-primary mb-3">الهيكل التنظيمي</h3>
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="bi bi-people display-6 text-primary mb-2"></i>
                                <h6>لجنة الفعاليات</h6>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="bi bi-megaphone display-6 text-success mb-2"></i>
                                <h6>لجنة العلاقات العامة</h6>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="bi bi-laptop display-6 text-info mb-2"></i>
                                <h6>لجنة التطوير</h6>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="bi bi-pen display-6 text-warning mb-2"></i>
                                <h6>لجنة المحتوى</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
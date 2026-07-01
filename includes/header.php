<?php
require_once __DIR__ . '/auth.php';

$base_path = app_base_path();
$title = isset($page_title) ? $page_title . ' - College Portal' : 'Student Admission Management System';
$body_class = $body_class ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Admission Management System - College Admission Portal">
    <title><?php echo e($title); ?></title>
    
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for professional dashboard icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo e($base_path); ?>assets/css/style.css">
</head>
<body class="<?php echo e($body_class); ?>">

<?php if (isset($is_public_page) && $is_public_page === true): ?>
<!-- Glassmorphic Top Navbar -->
<nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo app_base_path(); ?>index.php">
            <i class="fa-solid fa-graduation-cap me-2 text-info fs-3"></i>
            <span>SCT PORTAL</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#homepageNavbar" aria-controls="homepageNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars text-dark fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="homepageNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo app_base_path(); ?>index.php"><i class="fa-solid fa-house me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo app_base_path(); ?>portal.php"><i class="fa-solid fa-door-open me-1"></i>Portal Selection</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo app_base_path(); ?>courses.php"><i class="fa-solid fa-book-open me-1"></i>Academic Courses</a>
                </li>
                <?php if (isset($_SESSION['role'])): ?>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo app_base_path() . role_dashboard_path($_SESSION['role']); ?>" class="btn btn-premium-sky btn-sm py-2 px-4 fw-bold">
                            <i class="fa-solid fa-gauge me-1"></i>My Dashboard
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="<?php echo app_base_path(); ?>logout.php" class="btn btn-outline-danger btn-sm py-2 px-3 fw-bold">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo app_base_path(); ?>login.php?role=student" class="btn btn-premium-sky btn-sm py-2 px-4 fw-bold">Student Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="<?php echo app_base_path(); ?>student_register.php" class="btn btn-premium-secondary btn-sm py-2 px-4 fw-bold">Create Account</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>


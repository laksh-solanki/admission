<?php
require_once 'includes/auth.php';

// If a user is already logged in, redirect them directly to their dashboard
redirect_if_logged_in();

$is_public_page = true;
$body_class = "homepage-body";
$page_title = "Admissions Portal";
include 'includes/header.php';
?>

<!-- Hero Banner Section -->
<div class="hero-section-premium text-center">
    <div class="container">
        <div class="hero-badge">
            <i class="fa-solid fa-bullhorn text-warning"></i> Admissions Open for Academic Year 2026-27
        </div>
        <h1 class="hero-title-premium">Admissions Portal</h1>
        <p class="hero-subtitle-premium">Select your portal below to register, verify student documents, or manage system parameters.</p>
    </div>
</div>

<!-- Portal Selection Section -->
<div class="container portal-grid mb-5" id="portals">
    <div class="row g-4 justify-content-center">
        <!-- Student Card -->
        <div class="col-md-4">
            <div class="premium-card card-student">
                <div>
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h3 class="text-center">Student Portal</h3>
                    <p class="text-center text-muted">Register a new profile, submit academic marks, select course preferences, upload mandatory documents, and track approval status.</p>
                </div>
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <a href="login.php?role=student" class="btn btn-premium-primary w-100 mb-2 fw-bold text-center">Student Login</a>
                    <a href="student_register.php" class="btn btn-premium-secondary w-100 text-center">Create New Account</a>
                </div>
            </div>
        </div>

        <!-- Staff Card -->
        <div class="col-md-4">
            <div class="premium-card card-staff">
                <div>
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h3 class="text-center">Admission Staff</h3>
                    <p class="text-center text-muted">Access the verification workflow. Review student profiles, verify transcripts, submit comments, approve or reject applications, and download reports.</p>
                </div>
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <a href="login.php?role=staff" class="btn btn-premium-sky w-100 fw-bold text-center">Admission Staff Login</a>
                </div>
            </div>
        </div>

        <!-- Admin Card -->
        <div class="col-md-4">
            <div class="premium-card card-admin">
                <div>   
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <h3 class="text-center">Admin Console</h3>
                    <p class="text-center text-muted">Monitor system statistics, manage course offerings, create/update staff accounts, edit student details, and perform database maintenance tasks.</p>
                </div>
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <a href="login.php?role=admin" class="btn btn-premium-amber w-100 fw-bold text-center">Administrator Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

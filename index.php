<?php
// ====================================================================
// Home Page / Portal Landing Page (index.php)
// This is the front page of the Student Admission Management System.
// It redirects logged-in users to their respective dashboards and
// offers portal access cards for Students, Staff, and Admins.
// ====================================================================

require_once 'includes/auth.php';

// If a user is already logged in, redirect them directly to their dashboard
redirect_if_logged_in();

$page_title = "Welcome to State College Portal";
include 'includes/header.php';
?>

<!-- Hero Banner Section -->
<div class="hero-section text-center">
    <div class="container">
        <h1 class="hero-title mb-3"><i class="fa-solid fa-graduation-cap me-3"></i>State College of Technology</h1>
        <p class="hero-subtitle mb-4">Admissions open for Academic Year 2026-27. Submit your application online easily.</p>
        <span class="badge bg-warning text-dark px-3 py-2 fs-6">Minimum Eligibility: 35% in 12th Standard</span>
    </div>
</div>

<!-- Main Container -->
<div class="container mb-5">
    <!-- Portal Selection Section -->
    <div class="row justify-content-center">
        <!-- Student Card -->
        <div class="col-md-6 col-lg-5">
            <div class="role-card text-center">
                <i class="fa-solid fa-user-graduate"></i>
                <h3 class="fw-bold mt-2">Student Admission Portal</h3>
                <p class="text-muted">Register a new account, fill out your academic and personal details, upload certificates, and track your admission status.</p>
                <div class="mt-4 pt-3 border-top">
                    <a href="student_login.php" class="btn btn-custom-primary w-100 py-2.5 mb-2 fw-bold">Student Login</a>
                    <a href="student_register.php" class="btn btn-outline-secondary w-100 py-2.5">Create New Account</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Informative Admission Process Section -->
    <div class="card-custom mt-5">
        <div class="card-header-custom bg-light">
            <i class="fa-solid fa-circle-info me-2 text-primary"></i>Application Steps for Students
        </div>
        <div class="card-body-custom">
            <div class="row text-center g-4">
                <div class="col-md-3">
                    <div class="p-3">
                        <h4 class="text-primary fw-bold">Step 1</h4>
                        <h6 class="fw-bold">Sign Up</h6>
                        <p class="text-muted small">Register with your email and mobile number.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <h4 class="text-primary fw-bold">Step 2</h4>
                        <h6 class="fw-bold">Fill Details</h6>
                        <p class="text-muted small">Enter your marks, category, and course preference.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <h4 class="text-primary fw-bold">Step 3</h4>
                        <h6 class="fw-bold">Uploads</h6>
                        <p class="text-muted small">Upload photo, marksheets, LC, and Aadhaar card.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <h4 class="text-primary fw-bold">Step 4</h4>
                        <h6 class="fw-bold">Track & Print</h6>
                        <p class="text-muted small">Monitor staff reviews and download your final receipt.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

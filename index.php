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

$body_class = "homepage-body";
$page_title = "Welcome to State College Portal";
include 'includes/header.php';
?>

<!-- Glassmorphic Top Navbar -->
<nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="fa-solid fa-graduation-cap me-2 text-info fs-3"></i>
            <span>SCT PORTAL</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#homepageNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="homepageNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="#portals"><i class="fa-solid fa-door-open me-1"></i>Portals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#steps"><i class="fa-solid fa-list-check me-1"></i>Admission Steps</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="courses.php"><i class="fa-solid fa-book-open me-1"></i>Academic Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#features"><i class="fa-solid fa-star me-1"></i>Portal Features</a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a href="login.php?role=student" class="btn btn-premium-sky btn-sm py-2 px-4 fw-bold">Student Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Banner Section -->
<div class="hero-section-premium text-center">
    <div class="container">
        <div class="hero-badge">
            <i class="fa-solid fa-bullhorn text-warning"></i> Admissions Open for Academic Year 2026-27
        </div>
        <h1 class="hero-title-premium"><i class="fa-solid fa-circle-nodes me-2 text-info"></i>State College of Technology</h1>
        <p class="hero-subtitle-premium">Experience a fully digital, streamlined admission process. Apply for courses, upload your credentials, and track your application status in real time.</p>
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

<!-- Admission Steps Section -->
<div class="container my-5" id="steps">
    <div class="timeline-section">
        <h2 class="section-title">Application Journey</h2>
        <p class="section-subtitle">Follow these 4 simple steps to complete your admission process at State College of Technology.</p>
        
        <div class="timeline-stepper px-4">
            <div class="step-card">
                <div class="step-number-bubble">1</div>
                <h5>Quick Registration</h5>
                <p>Create your credentials using an active email address and mobile number.</p>
            </div>
            <div class="step-card">
                <div class="step-number-bubble">2</div>
                <h5>Academic Details</h5>
                <p>Provide 10th and 12th details and choose your preferred engineering course stream.</p>
            </div>
            <div class="step-card">
                <div class="step-number-bubble">3</div>
                <h5>Document Upload</h5>
                <p>Securely upload photo, signature, board marksheet, leaving certificate, and Aadhaar card.</p>
            </div>
            <div class="step-card">
                <div class="step-number-bubble">4</div>
                <h5>Track & Receipt</h5>
                <p>Monitor real-time verification and download your official PDF admission letter.</p>
            </div>
        </div>
    </div>
</div>

<!-- Feature Highlights Section -->
<div class="container features-section" id="features">
    <h2 class="section-title">Why Use Our Digital Portal?</h2>
    <p class="section-subtitle">Engineered to offer a reliable, modern, and transparent experience for applicants and staff alike.</p>
    
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="feature-box">
                <i class="fa-solid fa-bolt feature-icon"></i>
                <h5>Real-Time Updates</h5>
                <p>Receive immediate status updates as staff members review your academic documents and uploaded certificates.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <i class="fa-solid fa-shield-halved feature-icon"></i>
                <h5>Secure Uploads</h5>
                <p>All private documents and marksheets are encrypted and stored in secure, designated storage directories.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <i class="fa-solid fa-file-pdf feature-icon"></i>
                <h5>Instant PDF Receipts</h5>
                <p>Once approved, generate your digital admission fee receipt and seat allotment letter instantly.</p>
            </div>
        </div>
    </div>
</div>

<!-- Campus Facilities Section -->
<div class="campus-facilities-section" id="facilities">
    <div class="container">
        <h2 class="section-title">Campus & Infrastructure</h2>
        <p class="section-subtitle">State-of-the-art facilities designed to foster academic success and research innovation.</p>
        <div class="row g-4 mt-2">
            <div class="col-md-3">
                <div class="facility-card text-center">
                    <div class="facility-icon-wrapper bg-light text-primary mx-auto">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <h4>Central Library</h4>
                    <p class="small text-muted mb-0">Over 50,000 reference books, international journals, and a high-speed digital research archive.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="facility-card text-center">
                    <div class="facility-icon-wrapper bg-light text-success mx-auto">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <h4>Advanced IT Labs</h4>
                    <p class="small text-muted mb-0">Intel Core i9 systems, high-speed fiber internet, and dedicated servers for software experimentation.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="facility-card text-center">
                    <div class="facility-icon-wrapper bg-light text-warning mx-auto">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h4>Smart Classrooms</h4>
                    <p class="small text-muted mb-0">Acoustically treated lecture halls equipped with projection systems and interactive displays.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="facility-card text-center">
                    <div class="facility-icon-wrapper bg-light text-danger mx-auto">
                        <i class="fa-solid fa-volleyball"></i>
                    </div>
                    <h4>Sports & Hostels</h4>
                    <p class="small text-muted mb-0">Dedicated gyms, courts for indoor/outdoor games, and hygienic, comfortable hostel rooms.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Placement Highlights Section -->
<div class="placement-highlights-section" id="placements">
    <div class="container">
        <h2 class="section-title">Training & Placements</h2>
        <p class="section-subtitle">Bridging the gap between academic education and successful career paths.</p>
        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <div class="placement-card text-center">
                    <div class="placement-icon-wrapper bg-light text-primary mx-auto">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <h4>94% Placement Rate</h4>
                    <p class="small text-muted mb-0">Consistently high placement rate across IT, Science, Commerce, and Humanities branches.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="placement-card text-center">
                    <div class="placement-icon-wrapper bg-light text-success mx-auto">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <h4>120+ Corporate Recruiters</h4>
                    <p class="small text-muted mb-0">Partnerships with top multinational firms, offering internships and packages up to ₹15 LPA.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="placement-card text-center">
                    <div class="placement-icon-wrapper bg-light text-warning mx-auto">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <h4>Placement Training Cell</h4>
                    <p class="small text-muted mb-0">Dedicated guidance cell conducting mock interviews, resume workshops, and soft skill seminars.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Testimonials Section -->
<div class="student-testimonials-section" id="testimonials">
    <div class="container">
        <h2 class="section-title">What Our Alumni Say</h2>
        <p class="section-subtitle">Real feedback from students who transformed their careers at State College of Technology.</p>
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="testimonial-avatar">A</div>
                        <div class="testimonial-author-info">
                            <h6 class="mb-0">Amit Sharma</h6>
                            <span>B.Sc. Computer Science, Batch of 2024</span>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="mb-0">"The academic rigor and practical focus at SCT shaped my understanding of software engineering. Faculty guided me in building a deep web application, which helped me land a developer role at a leading technology firm during campus placements."</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="testimonial-avatar">R</div>
                        <div class="testimonial-author-info">
                            <h6 class="mb-0">Riya Patel</h6>
                            <span>Bachelor of Computer Applications (BCA), Batch of 2025</span>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <p class="mb-0">"Studying at SCT has been an enriching experience. The hands-on labs and project-based assessments taught me web and mobile development frameworks. Faculty support was exceptional, assisting me at every stage of my academic project."</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer class="premium-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5 col-md-12">
                <h5 class="d-flex align-items-center">
                    <i class="fa-solid fa-graduation-cap me-2 text-info fs-4"></i>
                    <span>State College of Technology</span>
                </h5>
                <p class="mt-3 text-muted">A premier institution offering state-of-the-art technical education. Empowering students since 2002 to build the systems and innovations of tomorrow.</p>
                <div class="mt-4 d-flex gap-3">
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="text-dark">Quick Links</h5>
                <ul>
                    <li><a href="#portals">Portal Selection</a></li>
                    <li><a href="#steps">Admission Steps</a></li>
                    <li><a href="courses.php">Academic Courses</a></li>
                    <li><a href="#features">Key Features</a></li>
                    <li><a href="student_register.php">Create Account</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <h5 class="text-dark">Help & Support</h5>
                <p class="small text-muted mb-2"><i class="fa-solid fa-envelope me-2 text-info"></i>admissions@statecollege.edu</p>
                <p class="small text-muted mb-2"><i class="fa-solid fa-phone me-2 text-info"></i>+1 (555) 019-2834</p>
                <p class="small text-muted"><i class="fa-solid fa-location-dot me-2 text-info"></i>100 Tech University Circle, Suite 400</p>
            </div>
        </div>
        
        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> State College of Technology. All Rights Reserved. Designed for Excellence.</p>
        </div>
    </div>
</footer>

<?php include 'includes/footer.php'; ?>


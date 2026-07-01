<?php
require_once 'includes/auth.php';

$is_public_page = true;
$body_class = "homepage-body";
$page_title = "State College of Technology - Academic Excellence";
include 'includes/header.php';
?>

<!-- Hero Banner Section -->
<div class="hero-section-home py-5 position-relative overflow-hidden text-white mb-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); min-height: 520px; display: flex; align-items: center;">
    <!-- Decorative Ambient light circles -->
    <div class="position-absolute" style="width: 350px; height: 350px; background: rgba(59, 130, 246, 0.15); border-radius: 50%; top: -100px; right: -50px; filter: blur(60px);"></div>
    <div class="position-absolute" style="width: 250px; height: 250px; background: rgba(13, 148, 136, 0.12); border-radius: 50%; bottom: -50px; left: -50px; filter: blur(50px);"></div>
    
    <div class="container position-relative z-1 my-3">
        <div class="row align-items-center g-5">
            <!-- Left Text Content -->
            <div class="col-lg-7 text-center text-lg-start">
                <div class="d-inline-flex align-items-center gap-2 mb-3 px-3 py-1.5 rounded-pill text-warning fw-bold border border-warning border-opacity-25" style="background: rgba(245, 158, 11, 0.1); font-size: 0.85rem;">
                    <i class="fa-solid fa-bullhorn text-warning"></i> Admissions Open for Academic Year 2026-27
                </div>
                <h1 class="display-4 fw-extrabold mb-3 text-white" style="line-height: 1.15; font-family: 'Poppins', sans-serif;">
                    Unlock Your Academic Potential & <span class="text-info">Innovate for Tomorrow</span>
                </h1>
                <p class="lead mb-4 text-white-50" style="font-size: 1.1rem; max-width: 620px; margin: 0 auto 1.5rem;">
                    Welcome to the State College of Technology. Experience a fully digital, streamlined admission process. Apply for courses, upload your credentials, and track your application status in real time.
                </p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start flex-wrap mt-2">
                    <a href="portal.php" class="btn btn-info btn-lg px-4 py-3 fw-bold text-white shadow-sm d-flex align-items-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Admission Portal Access
                    </a>
                    <a href="courses.php" class="btn btn-outline-light btn-lg px-4 py-3 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-book-open"></i> Explore Courses
                    </a>
                </div>
            </div>
            <!-- Right graphic box -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="bg-white bg-opacity-10 border border-white border-opacity-10 rounded-4 p-4 shadow-lg backdrop-blur" style="backdrop-filter: blur(10px);">
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-white border-opacity-10 pb-3">
                        <div class="d-flex align-items-center gap-2 text-start">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-white">SCT Admission Desk</h6>
                                <small class="text-white-50 text-xs">Verification System Status</small>
                            </div>
                        </div>
                        <span class="badge bg-success px-2.5 py-1">Online</span>
                    </div>
                    
                    <div class="text-start text-white-50 small">
                        <div class="d-flex gap-3 mb-3 align-items-start">
                            <span class="badge bg-info text-white mt-1">1</span>
                            <div>
                                <strong class="text-white d-block">Quick Register</strong>
                                <span>Create accounts with name, email and password.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3 align-items-start">
                            <span class="badge bg-info text-white mt-1">2</span>
                            <div>
                                <strong class="text-white d-block">Academic Profile</strong>
                                <span>Enter 10th/12th scores and choose preferences.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3 align-items-start">
                            <span class="badge bg-info text-white mt-1">3</span>
                            <div>
                                <strong class="text-white d-block">Secure Upload</strong>
                                <span>Add marksheet transcripts, photos, and ID.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-info text-white mt-1">4</span>
                            <div>
                                <strong class="text-white d-block">Verification Log</strong>
                                <span>Review staff approval and download admission letter.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Counter Grid Section -->
<div class="container my-5">
    <div class="row g-4 text-center">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100 rounded-3">
                <div class="fs-1 fw-extrabold text-primary mb-1">94%</div>
                <div class="fw-bold text-dark small text-uppercase tracking-wider">Placement Rate</div>
                <p class="text-muted small mt-2 mb-0">Consistently placed technical and commerce graduates in top firms.</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100 rounded-3">
                <div class="fs-1 fw-extrabold text-success mb-1">120+</div>
                <div class="fw-bold text-dark small text-uppercase tracking-wider">Top Recruiters</div>
                <p class="text-muted small mt-2 mb-0">Direct recruitment ties with MNCs and tech startups.</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100 rounded-3">
                <div class="fs-1 fw-extrabold text-warning mb-1">50K+</div>
                <div class="fw-bold text-dark small text-uppercase tracking-wider">Library Archive</div>
                <p class="text-muted small mt-2 mb-0">Vast collection of books, academic journals, and digital logs.</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100 rounded-3">
                <div class="fs-1 fw-extrabold text-danger mb-1">₹15 LPA</div>
                <div class="fw-bold text-dark small text-uppercase tracking-wider">Highest Package</div>
                <p class="text-muted small mt-2 mb-0">Impressive packages achieved in national hiring portals.</p>
            </div>
        </div>
    </div>
</div>

<!-- Academic Offerings Preview -->
<div class="container my-5 py-3">
    <div class="text-center mb-5">
        <span class="badge bg-light text-primary border px-3 py-2 mb-2 fw-bold text-uppercase">Programs</span>
        <h2 class="fw-bold text-dark">Explore Our Academic Fields</h2>
        <p class="text-muted mx-auto" style="max-width: 600px;">
            We offer modern, comprehensive curriculum options designed by industry specialists to prepare you for global technology landscapes.
        </p>
    </div>
    
    <div class="row g-4 justify-content-center">
        <!-- CS Program Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 bg-white rounded-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fa-solid fa-code"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Computer Science & IT</h5>
                    <p class="text-muted small mb-3">Programming, Algorithms, Data Systems, Cybersecurity networks, Cloud Computing, and App Development frameworks.</p>
                </div>
                <div class="border-top pt-3 mt-auto">
                    <a href="courses.php" class="text-decoration-none small fw-bold text-primary d-inline-flex align-items-center gap-1">
                        Syllabus details <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Commerce Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 bg-white rounded-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-inline-flex align-items-center justify-content-center bg-light text-warning rounded-circle mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Commerce & Accounting</h5>
                    <p class="text-muted small mb-3">Financial reporting, corporate governance, micro/macroeconomics, taxation systems, auditing, and corporate laws.</p>
                </div>
                <div class="border-top pt-3 mt-auto">
                    <a href="courses.php" class="text-decoration-none small fw-bold text-warning d-inline-flex align-items-center gap-1">
                        Syllabus details <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Literature Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 bg-white rounded-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-inline-flex align-items-center justify-content-center bg-light text-danger rounded-circle mb-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Humanities & Languages</h5>
                    <p class="text-muted small mb-3">Linguistics, creative writing, drama critiques, classical and modern English poetry studies, and critical thinking development.</p>
                </div>
                <div class="border-top pt-3 mt-auto">
                    <a href="courses.php" class="text-decoration-none small fw-bold text-danger d-inline-flex align-items-center gap-1">
                        Syllabus details <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-5">
        <a href="courses.php" class="btn btn-primary fw-bold px-4 py-2">
            <i class="fa-solid fa-magnifying-glass me-1"></i> View All Available Programs
        </a>
    </div>
</div>

<!-- Admission Steps Journey Section -->
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

<!-- Campus Infrastructure -->
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

<!-- Bottom Admissions Call To Action -->
<div class="bg-light border-top py-5 text-center">
    <div class="container py-3">
        <h3 class="fw-bold text-dark mb-2">Build Your Technical Career Path Today</h3>
        <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
            Registration is quick and online. Log in, specify your board marks, upload your PDF and picture credentials, and track your admission status instantly.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="portal.php" class="btn btn-primary btn-lg px-4 py-2.5 fw-bold shadow-sm">
                <i class="fa-solid fa-user-plus me-1"></i> Register & Apply Online
            </a>
            <a href="login.php?role=student" class="btn btn-outline-secondary btn-lg px-4 py-2.5 fw-bold">
                Student Portal Login
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

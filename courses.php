<?php
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Fetch all courses from database
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY department, course_name");
    $courses_from_db = $stmt->fetchAll();
} catch (PDOException $e) {
    // If query fails, fall back to empty array
    $courses_from_db = [];
}

// Map for rich curriculum details
$course_details_map = [
    'B.Sc. Computer Science' => [
        'icon' => 'fa-laptop-code text-primary',
        'duration' => '3 Years (6 Semesters)',
        'eligibility' => 'Higher Secondary (10+2) with Mathematics as a core subject, minimum 50% marks.',
        'curriculum' => [
            'Programming in C++ & Java',
            'Data Structures & Algorithms',
            'Database Management Systems (DBMS)',
            'Operating Systems & Networking',
            'Software Engineering Principles'
        ],
        'how_students_study' => 'Students engage in intensive hands-on programming labs, design databases, write systems code, and collaborate in peer-programming sessions. Every semester culminates in a practical software project applying modern frameworks.',
        'how_faculty_teach' => 'Faculty members employ case-study-driven teaching. They guide students through complex system concepts in labs, conduct interactive coding reviews, invite industry guests, and support undergraduate research in AI and systems engineering.'
    ],
    'Bachelor of Computer Applications (BCA)' => [
        'icon' => 'fa-mobile-screen-button text-success',
        'duration' => '3 Years (6 Semesters)',
        'eligibility' => 'Higher Secondary (10+2) in any stream with English, minimum 45% marks.',
        'curriculum' => [
            'Web Technologies (HTML, CSS, JS)',
            'Object-Oriented Programming',
            'Mobile App Development',
            'Cloud Computing Foundations',
            'E-Commerce & Digital Marketing'
        ],
        'how_students_study' => 'Students design web portals, build mobile applications, host applications on cloud platforms, and practice software testing. They work on real-world projects in collaboration with startup labs.',
        'how_faculty_teach' => 'Faculty members prioritize project-based learning. They utilize code-along tutorials, continuous practical evaluations, and guide students in building production-ready apps for local community businesses.'
    ],
    'B.Com. (General)' => [
        'icon' => 'fa-chart-pie text-warning',
        'duration' => '3 Years (6 Semesters)',
        'eligibility' => 'Higher Secondary (10+2) in Commerce or Science stream, minimum 50% marks.',
        'curriculum' => [
            'Financial & Management Accounting',
            'Business Law & Corporate Governance',
            'Micro & Macro Economics',
            'Direct & Indirect Taxation',
            'Auditing & Financial Modeling'
        ],
        'how_students_study' => 'Students analyze real corporate balance sheets, simulate stock trading, study tax regulations, and solve business finance case studies. They practice accounting software like Tally and ERP systems.',
        'how_faculty_teach' => 'Faculty use real-world financial reports, business news analyses, and workshops by Chartered Accountants. They mentor students in analytical modeling, financial decision-making, and tax planning.'
    ],
    'B.A. English Literature' => [
        'icon' => 'fa-book-open-reader text-danger',
        'duration' => '3 Years (6 Semesters)',
        'eligibility' => 'Higher Secondary (10+2) in any stream with minimum 50% marks in English.',
        'curriculum' => [
            'History of English Literature',
            'Classical & Modern Poetry',
            'Drama & Creative Writing',
            'Linguistics & Phonetics',
            'Literary Criticism & Theory'
        ],
        'how_students_study' => 'Students read diverse texts, participate in seminar discussions, write critical essays, perform script readings, and publish creative pieces in the college literary magazine.',
        'how_faculty_teach' => 'Faculty foster critical thinking and discussion. They use interactive seminar circles, run creative writing workshops, direct drama performances, and support students in comparative literature research.'
    ],
    'B.Sc. Information Technology (B.Sc. IT)' => [
        'icon' => 'fa-shield-halved text-info',
        'duration' => '3 Years (6 Semesters)',
        'eligibility' => 'Higher Secondary (10+2) with Mathematics/IT, minimum 45% marks.',
        'curriculum' => [
            'System Administration & Linux',
            'Cybersecurity & Network Security',
            'Web Application Architecture',
            'Big Data & Analytics',
            'IT Project Management'
        ],
        'how_students_study' => 'Students configure secure server networks, simulate security threat attacks and defense, learn cloud migration, and analyze massive logs using modern analytical software.',
        'how_faculty_teach' => 'Faculty teach through hands-on virtual sandbox environments. They conduct capture-the-flag (CTF) cybersecurity challenges, guide students in network design certifications, and lead workshops on systems engineering.'
    ]
];

$default_details = [
    'icon' => 'fa-graduation-cap text-secondary',
    'duration' => '3 Years (6 Semesters)',
    'eligibility' => 'Higher Secondary (10+2) from a recognized board with minimum 45% marks.',
    'curriculum' => [
        'Core Foundational Coursework',
        'Advanced Specialized Subjects',
        'Elective Professional Modules',
        'Seminars & Research Projects',
        'Practical Laboratory Work'
    ],
    'how_students_study' => 'Students engage in lectures, group research, laboratory experiments, and interactive seminars, building a strong foundation in both theory and practical applications.',
    'how_faculty_teach' => 'Faculty members use a blend of lectures, interactive labs, and individual mentoring. They help students develop critical thinking and guide their projects to academic and industrial success.'
];

$is_public_page = true;
$body_class = "courses-page-body";
$page_title = "Academic Programs & Courses";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="courses-hero-section text-center">
    <div class="container">
        <span class="badge bg-light text-primary border px-3 py-2 mb-3 fw-bold text-uppercase tracking-wider">State College of Technology</span>
        <h1 class="hero-title fw-extrabold text-dark mb-3">Academic Programs & Course Details</h1>
        <p class="hero-subtitle text-muted mx-auto" style="max-width: 700px;">
            Discover our world-class educational pathways. Explore detailed curricula, learning patterns, and how our expert faculty members guide students towards Excellence.
        </p>
    </div>
</div>

<!-- Courses Details Container -->
<div class="container my-5">
    <?php if (empty($courses_from_db)): ?>
        <div class="alert alert-info text-center shadow-sm py-4">
            <i class="fa-solid fa-circle-info fs-3 mb-2 text-info"></i>
            <h5>No Active Courses Found</h5>
            <p class="mb-0 text-muted">Courses database is currently empty. Please check back later or contact admin.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($courses_from_db as $course): 
                $course_name = $course['course_name'];
                $details = $course_details_map[$course_name] ?? $default_details;
            ?>
                <div class="col-12" id="course-<?php echo e($course['course_id']); ?>">
                    <div class="course-detail-card card border-0 shadow-sm overflow-hidden mb-4">
                        <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="course-card-icon rounded-circle d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px; font-size: 24px;">
                                    <i class="fa-solid <?php echo e($details['icon']); ?>"></i>
                                </div>
                                <div>
                                    <h3 class="mb-1 fw-bold text-dark"><?php echo e($course_name); ?></h3>
                                    <span class="badge bg-primary text-white py-1.5 px-3 rounded-pill"><?php echo e($course['department']); ?></span>
                                    <span class="badge bg-secondary text-white py-1.5 px-3 rounded-pill ms-1"><?php echo e($course['semester']); ?></span>
                                </div>
                            </div>
                            <div class="text-lg-end text-start">
                                <div class="small text-muted mb-1"><i class="fa-solid fa-chair text-primary me-1"></i>Total Allotted Seats</div>
                                <span class="fs-4 fw-bold text-dark"><?php echo e($course['total_seats']); ?> Seats</span>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 bg-light bg-opacity-50 border-top">
                            <div class="row g-4">
                                <!-- Column 1: Core Details -->
                                <div class="col-md-4">
                                    <div class="p-3 bg-white rounded shadow-sm h-100">
                                        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-list-check me-2"></i>Program Info</h5>
                                        <div class="mb-3">
                                            <span class="small text-muted d-block">Duration:</span>
                                            <strong class="text-dark"><?php echo e($details['duration'] ?? '3 Years (6 Semesters)'); ?></strong>
                                        </div>
                                        <div class="mb-3">
                                            <span class="small text-muted d-block">Eligibility:</span>
                                            <strong class="text-dark d-block small" style="line-height: 1.5;"><?php echo e($details['eligibility']); ?></strong>
                                        </div>
                                        <div>
                                            <span class="small text-muted d-block mb-2">Curriculum Highlights:</span>
                                            <ul class="list-unstyled mb-0">
                                                <?php foreach ($details['curriculum'] as $item): ?>
                                                    <li class="small text-muted mb-1.5 d-flex align-items-start">
                                                        <i class="fa-solid fa-circle-check text-success me-2 mt-1" style="font-size: 11px;"></i>
                                                        <span><?php echo e($item); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Column 2: How Students Study -->
                                <div class="col-md-4">
                                    <div class="p-3 bg-white rounded shadow-sm h-100">
                                        <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-user-graduate me-2"></i>How Students Study</h5>
                                        <p class="text-muted small" style="line-height: 1.7; text-align: justify;">
                                            <?php echo e($details['how_students_study']); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Column 3: How Faculty Teach -->
                                <div class="col-md-4">
                                    <div class="p-3 bg-white rounded shadow-sm h-100">
                                        <h5 class="fw-bold text-warning mb-3"><i class="fa-solid fa-user-tie me-2"></i>How Faculty Teach</h5>
                                        <p class="text-muted small" style="line-height: 1.7; text-align: justify;">
                                            <?php echo e($details['how_faculty_teach']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Action Section -->
    <div class="text-center mt-5 p-5 bg-white rounded shadow-sm">
        <h3 class="fw-bold text-dark mb-2">Ready to embark on your educational journey?</h3>
        <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">Create your student account in minutes, fill out your academic marks, upload your documents, and track your admission status online.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="student_register.php" class="btn btn-primary btn-lg fw-bold px-4 py-2.5 shadow-sm"><i class="fa-solid fa-user-plus me-2"></i>Register for Admission</a>
            <a href="login.php?role=student" class="btn btn-outline-secondary btn-lg fw-bold px-4 py-2.5"><i class="fa-solid fa-right-to-bracket me-2"></i>Student Login Portal</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


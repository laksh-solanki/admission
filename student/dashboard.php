<?php
// ====================================================================
// Student Dashboard Page (student/dashboard.php)
// This page acts as the main landing page for students. It displays
// their current application progress, alerts them to pending actions,
// and shows the final decision (Approved/Rejected) with remarks.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify that the user is logged in as a student
check_access('student');

$user_id = $_SESSION['user_id'];
$student = null;
$documents = null;
$has_form = false;
$has_docs = false;

try {
    // 1. Fetch student details linked to the user account
    $stmt = $pdo->prepare("SELECT s.*, c.course_name FROM students s LEFT JOIN courses c ON s.course_id = c.course_id WHERE s.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch();

    if ($student) {
        $has_form = true;
        
        // 2. Fetch uploaded documents for the student
        $doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE student_id = :student_id");
        $doc_stmt->execute(['student_id' => $student['student_id']]);
        $documents = $doc_stmt->fetch();
        
        if ($documents && !empty($documents['photo']) && !empty($documents['marksheet10']) && !empty($documents['marksheet12']) && !empty($documents['leaving_certificate']) && !empty($documents['aadhaar'])) {
            $has_docs = true;
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Handle Final Submit request
if (isset($_POST['action']) && $_POST['action'] === 'final_submit' && $has_form && $has_docs && $student['is_submitted'] == 0) {
    try {
        $pdo->beginTransaction();

        // Mark student application as submitted
        $update_stmt = $pdo->prepare("UPDATE students SET is_submitted = 1, status = 'Pending' WHERE student_id = :student_id");
        $update_stmt->execute(['student_id' => $student['student_id']]);

        // Insert into status history log
        $history_stmt = $pdo->prepare("INSERT INTO status_history (student_id, status, remarks) VALUES (:student_id, 'Pending', 'Application submitted by student.')");
        $history_stmt->execute(['student_id' => $student['student_id']]);

        $pdo->commit();
        
        // Refresh student details
        header("Location: dashboard.php?msg=submitted");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Failed to submit application: " . $e->getMessage();
    }
}

$page_title = "Student Dashboard";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-custom-primary">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="navbar-brand ms-3">Student Admission Portal</span>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted small"><i class="fa-solid fa-circle-user me-1"></i><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Alert Messages -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'submitted'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Your application has been finalized and submitted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card-custom bg-white p-4">
                        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                        <p class="text-muted">Manage your college admission process step-by-step from this dashboard.</p>
                    </div>
                </div>
            </div>

            <!-- Wizard / Application Flow Status Alert -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <?php if (!$has_form): ?>
                        <!-- Step 1: Form Fill Pending -->
                        <div class="alert alert-warning p-4 border-start border-warning border-4 shadow-sm" role="alert">
                            <h4 class="alert-heading fw-bold"><i class="fa-solid fa-circle-exclamation me-2 text-warning"></i>Step 1: Fill Admission Form</h4>
                            <p>You have not filled the admission form yet. Please enter your personal details, academic percentages, and course preferences to start the admission process.</p>
                            <hr>
                            <a href="apply.php" class="btn btn-custom-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Fill Form Now</a>
                        </div>
                    <?php elseif ($has_form && !$has_docs): ?>
                        <!-- Step 2: Document Upload Pending -->
                        <div class="alert alert-info p-4 border-start border-info border-4 shadow-sm" role="alert">
                            <h4 class="alert-heading fw-bold"><i class="fa-solid fa-cloud-arrow-up me-2 text-info"></i>Step 2: Upload Documents</h4>
                            <p>Your details have been saved, but your required certificates are missing. You must upload your passport photo, 10th & 12th marksheets, leaving certificate, and Aadhaar card to proceed.</p>
                            <hr>
                            <a href="upload.php" class="btn btn-custom-secondary"><i class="fa-solid fa-file-arrow-up me-2"></i>Upload Documents</a>
                            <a href="apply.php" class="btn btn-outline-secondary ms-2"><i class="fa-solid fa-pen"></i> Edit Form Details</a>
                        </div>
                    <?php elseif ($has_form && $has_docs && $student['is_submitted'] == 0): ?>
                        <!-- Step 3: Final Submit Pending -->
                        <div class="alert alert-primary p-4 border-start border-primary border-4 shadow-sm" role="alert">
                            <h4 class="alert-heading fw-bold"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Step 3: Finalize and Submit Application</h4>
                            <p>Your admission form and documents are successfully compiled. Please review the details below. Once submitted, you cannot edit details unless rejected by staff.</p>
                            <hr>
                            <form action="dashboard.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="final_submit">
                                <button type="submit" class="btn btn-success fw-bold" onclick="return confirm('Are you sure you want to finalize and submit? You will not be able to edit this form.');">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Final Submit Application
                                </button>
                            </form>
                            <a href="apply.php" class="btn btn-outline-secondary ms-2"><i class="fa-solid fa-pen"></i> Edit Form</a>
                            <a href="upload.php" class="btn btn-outline-secondary ms-2"><i class="fa-solid fa-file-image"></i> Manage Uploads</a>
                        </div>
                    <?php else: ?>
                        <!-- Application Submitted and Locked -->
                        <?php if ($student['status'] === 'Pending'): ?>
                            <div class="alert alert-warning p-4 border-start border-warning border-4 shadow-sm" role="alert">
                                <h4 class="alert-heading fw-bold"><i class="fa-solid fa-spinner fa-spin me-2 text-warning"></i>Application Status: PENDING VERIFICATION</h4>
                                <p>Your application (Admission ID: <strong><?php echo htmlspecialchars($student['admission_no']); ?></strong>) was submitted on <strong><?php echo date('d-M-Y H:i', strtotime($student['created_at'])); ?></strong>. It is currently under review by our admission staff. We will notify you here once verified.</p>
                            </div>
                        <?php elseif ($student['status'] === 'Approved'): ?>
                            <div class="alert alert-success p-4 border-start border-success border-4 shadow-sm" role="alert">
                                <h4 class="alert-heading fw-bold"><i class="fa-solid fa-circle-check me-2 text-success"></i>Application Status: APPROVED</h4>
                                <p>Congratulations! Your admission application for <strong><?php echo htmlspecialchars($student['course_name']); ?></strong> has been verified and approved. You can now download your official admission receipt below.</p>
                                <hr>
                                <a href="receipt.php" target="_blank" class="btn btn-success"><i class="fa-solid fa-file-pdf me-2"></i>Download Admission Receipt</a>
                            </div>
                        <?php elseif ($student['status'] === 'Rejected'): ?>
                            <div class="alert alert-danger p-4 border-start border-danger border-4 shadow-sm" role="alert">
                                <h4 class="alert-heading fw-bold"><i class="fa-solid fa-circle-xmark me-2 text-danger"></i>Application Status: REJECTED</h4>
                                <p>Your application was rejected by the admission staff due to verification issues.</p>
                                <?php
                                    // Fetch the latest remarks from status history
                                    $hist_stmt = $pdo->prepare("SELECT remarks FROM status_history WHERE student_id = :student_id ORDER BY history_id DESC LIMIT 1");
                                    $hist_stmt->execute(['student_id' => $student['student_id']]);
                                    $history = $hist_stmt->fetch();
                                ?>
                                <p class="fw-bold">Staff Remarks: <span class="text-dark bg-white px-2 py-1 rounded border border-danger-subtle"><?php echo htmlspecialchars($history ? $history['remarks'] : 'No remarks provided.'); ?></span></p>
                                <hr>
                                <p class="small text-muted">You can edit your details and re-upload documents to correct the error and re-submit.</p>
                                <a href="apply.php" class="btn btn-danger"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Form & Re-Submit</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Details Preview (If Form is Filled) -->
            <?php if ($has_form): ?>
                <div class="row">
                    <!-- Personal & Academic Details Card -->
                    <div class="col-lg-8">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <i class="fa-solid fa-user me-2"></i>Application Details Preview
                                <?php if ($student['is_submitted'] == 0): ?>
                                    <span class="badge bg-secondary float-end">Draft (Unsubmitted)</span>
                                <?php else: ?>
                                    <span class="badge bg-success float-end">Submitted</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body-custom">
                                <div class="row mb-4">
                                    <h5 class="fw-bold text-primary border-bottom pb-2">Personal Information</h5>
                                    <div class="col-md-6 mb-2">
                                        <strong>Admission No:</strong> <?php echo htmlspecialchars($student['admission_no']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Applied Course:</strong> <?php echo htmlspecialchars($student['course_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>First Name:</strong> <?php echo htmlspecialchars($student['first_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Last Name:</strong> <?php echo htmlspecialchars($student['last_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>DOB:</strong> <?php echo date('d-M-Y', strtotime($student['dob'])); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Category:</strong> <?php echo htmlspecialchars($student['category']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Mobile:</strong> <?php echo htmlspecialchars($student['mobile']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Email Address:</strong> <?php echo htmlspecialchars($student['email']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Pincode:</strong> <?php echo htmlspecialchars($student['pincode']); ?>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong>Address:</strong> <?php echo htmlspecialchars($student['address']) . ", " . htmlspecialchars($student['city']) . ", " . htmlspecialchars($student['state']); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <h5 class="fw-bold text-primary border-bottom pb-2">Academic Information</h5>
                                    <div class="col-md-6 mb-2">
                                        <strong>10th Percentage:</strong> <?php echo htmlspecialchars($student['tenth_percentage']); ?>%
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>12th Percentage:</strong> <?php echo htmlspecialchars($student['twelfth_percentage']); ?>%
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Previous School:</strong> <?php echo htmlspecialchars($student['school_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Passing Year:</strong> <?php echo htmlspecialchars($student['passing_year']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Uploaded Documents Card -->
                    <div class="col-lg-4">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <i class="fa-solid fa-file-lines me-2"></i>Required Documents
                            </div>
                            <div class="card-body-custom">
                                <ul class="list-group list-group-flush">
                                    <!-- Photo Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-image me-2 text-primary"></i>Student Photo</span>
                                        <?php if ($documents && !empty($documents['photo'])): ?>
                                            <span class="badge bg-success-subtle text-success badge-custom"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge-custom"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- 10th Marksheet Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-file-pdf me-2 text-danger"></i>10th Marksheet</span>
                                        <?php if ($documents && !empty($documents['marksheet10'])): ?>
                                            <span class="badge bg-success-subtle text-success badge-custom"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge-custom"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- 12th Marksheet Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-file-pdf me-2 text-danger"></i>12th Marksheet</span>
                                        <?php if ($documents && !empty($documents['marksheet12'])): ?>
                                            <span class="badge bg-success-subtle text-success badge-custom"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge-custom"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- LC Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-file-word me-2 text-info"></i>Leaving Certificate</span>
                                        <?php if ($documents && !empty($documents['leaving_certificate'])): ?>
                                            <span class="badge bg-success-subtle text-success badge-custom"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge-custom"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- Aadhaar Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-address-card me-2 text-success"></i>Aadhaar Card</span>
                                        <?php if ($documents && !empty($documents['aadhaar'])): ?>
                                            <span class="badge bg-success-subtle text-success badge-custom"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge-custom"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>
                                </ul>

                                <?php if ($student['is_submitted'] == 0): ?>
                                    <div class="mt-4">
                                        <a href="upload.php" class="btn btn-outline-primary btn-sm w-100"><i class="fa-solid fa-upload me-1"></i>Go to Uploads Manager</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

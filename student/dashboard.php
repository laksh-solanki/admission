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
        <?php render_topbar('Student Admission Portal', '<span class="text-muted small"><i class="fa-solid fa-circle-user me-1"></i>' . e($_SESSION['email']) . '</span>'); ?>

        <div class="container-fluid">
            <!-- Alert Messages -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'submitted'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Your application has been finalized and submitted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'paid'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>Your processing fee payment of â‚¹500.00 has been recorded successfully! You can now finalize and submit your application.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-white p-4">
                        <h2>Welcome, <?php echo e($_SESSION['name']); ?>!</h2>
                        <p class="text-muted">Manage your college admission process step-by-step from this dashboard.</p>
                    </div>
                </div>
            </div>

            <!-- Wizard / Application Flow Status Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <?php if (!$has_form): ?>
                        <!-- Step 1: Form Fill Pending -->
                        <div class="status-card-premium status-card-step">
                            <div class="status-stepper-premium">
                                <div class="status-stepper-step-premium active">
                                    <div class="status-stepper-dot-premium">1</div>
                                    <div class="status-stepper-label-premium">Fill Details</div>
                                </div>
                                <div class="status-stepper-step-premium">
                                    <div class="status-stepper-dot-premium">2</div>
                                    <div class="status-stepper-label-premium">Upload Docs</div>
                                </div>
                                <div class="status-stepper-step-premium">
                                    <div class="status-stepper-dot-premium">3</div>
                                    <div class="status-stepper-label-premium">Payment</div>
                                </div>
                                <div class="status-stepper-step-premium">
                                    <div class="status-stepper-dot-premium">4</div>
                                    <div class="status-stepper-label-premium">Submit</div>
                                </div>
                            </div>
                            
                            <div class="status-header-premium">
                                <div class="status-icon-wrapper-premium">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </div>
                                <div>
                                    <div class="status-title-premium">Step 1: Fill Admission Form</div>
                                    <div class="status-subtitle-premium">Action Required</div>
                                </div>
                            </div>
                            <div class="status-body-premium">
                                <p>You have not filled the admission form yet. Please enter your personal details, academic percentages, and course preferences to start the admission process.</p>
                            </div>
                            <div class="status-action-row-premium">
                                <a href="apply.php" class="btn btn-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Fill Form Now</a>
                            </div>
                        </div>
                    <?php elseif ($has_form && !$has_docs): ?>
                        <!-- Step 2: Document Upload Pending -->
                        <div class="status-card-premium status-card-step">
                            <div class="status-stepper-premium">
                                <div class="status-stepper-step-premium completed">
                                    <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                    <div class="status-stepper-label-premium">Fill Details</div>
                                </div>
                                <div class="status-stepper-step-premium active">
                                    <div class="status-stepper-dot-premium">2</div>
                                    <div class="status-stepper-label-premium">Upload Docs</div>
                                </div>
                                <div class="status-stepper-step-premium">
                                    <div class="status-stepper-dot-premium">3</div>
                                    <div class="status-stepper-label-premium">Payment</div>
                                </div>
                                <div class="status-stepper-step-premium">
                                    <div class="status-stepper-dot-premium">4</div>
                                    <div class="status-stepper-label-premium">Submit</div>
                                </div>
                            </div>
                            
                            <div class="status-header-premium">
                                <div class="status-icon-wrapper-premium">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <div class="status-title-premium">Step 2: Upload Documents</div>
                                    <div class="status-subtitle-premium">Action Required</div>
                                </div>
                            </div>
                            <div class="status-body-premium">
                                <p>Your details have been saved, but your required certificates are missing. You must upload your passport photo, 10th & 12th marksheets, leaving certificate, and Aadhaar card to proceed.</p>
                            </div>
                            <div class="status-action-row-premium">
                                <a href="upload.php" class="btn btn-primary"><i class="fa-solid fa-file-arrow-up me-2"></i>Upload Documents</a>
                                <a href="apply.php" class="btn btn-secondary"><i class="fa-solid fa-pen me-2"></i>Edit Form Details</a>
                            </div>
                        </div>
                    <?php elseif ($has_form && $has_docs && $student['payment_status'] === 'Unpaid'): ?>
                        <!-- Step 3: Payment Pending -->
                        <div class="status-card-premium status-card-step">
                            <div class="status-stepper-premium">
                                <div class="status-stepper-step-premium completed">
                                    <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                    <div class="status-stepper-label-premium">Fill Details</div>
                                </div>
                                <div class="status-stepper-step-premium completed">
                                    <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                    <div class="status-stepper-label-premium">Upload Docs</div>
                                </div>
                                <div class="status-stepper-step-premium active">
                                    <div class="status-stepper-dot-premium">3</div>
                                    <div class="status-stepper-label-premium">Payment</div>
                                </div>
                                <div class="status-stepper-step-premium">
                                    <div class="status-stepper-dot-premium">4</div>
                                    <div class="status-stepper-label-premium">Submit</div>
                                </div>
                            </div>
                            
                            <div class="status-header-premium">
                                <div class="status-icon-wrapper-premium">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <div>
                                    <div class="status-title-premium">Step 3: Pay Processing Fee</div>
                                    <div class="status-subtitle-premium">Action Required</div>
                                </div>
                            </div>
                            <div class="status-body-premium">
                                <p>Your details and documents are successfully compiled. You must now complete the online fee payment of â‚¹500.00 to submit your application.</p>
                            </div>
                            <div class="status-action-row-premium">
                                <a href="payment.php" class="btn btn-primary"><i class="fa-solid fa-credit-card me-2"></i>Pay Fees Now</a>
                                <a href="apply.php" class="btn btn-secondary"><i class="fa-solid fa-pen me-2"></i>Edit Form</a>
                                <a href="upload.php" class="btn btn-secondary"><i class="fa-solid fa-file-image me-2"></i>Manage Uploads</a>
                            </div>
                        </div>
                    <?php elseif ($has_form && $has_docs && $student['payment_status'] === 'Paid' && $student['is_submitted'] == 0): ?>
                        <!-- Step 4: Final Submit Pending -->
                        <div class="status-card-premium status-card-step">
                            <div class="status-stepper-premium">
                                <div class="status-stepper-step-premium completed">
                                    <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                    <div class="status-stepper-label-premium">Fill Details</div>
                                </div>
                                <div class="status-stepper-step-premium completed">
                                    <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                    <div class="status-stepper-label-premium">Upload Docs</div>
                                </div>
                                <div class="status-stepper-step-premium completed">
                                    <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                    <div class="status-stepper-label-premium">Payment</div>
                                </div>
                                <div class="status-stepper-step-premium active">
                                    <div class="status-stepper-dot-premium">4</div>
                                    <div class="status-stepper-label-premium">Submit</div>
                                </div>
                            </div>
                            
                            <div class="status-header-premium">
                                <div class="status-icon-wrapper-premium">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <div>
                                    <div class="status-title-premium">Step 4: Finalize and Submit Application</div>
                                    <div class="status-subtitle-premium">Action Required</div>
                                </div>
                            </div>
                            <div class="status-body-premium">
                                <p>Your fee payment (Transaction ID: <strong><?php echo e($student['transaction_id']); ?></strong>) was recorded successfully. Please review the details below. Once submitted, you cannot edit details unless rejected by staff.</p>
                            </div>
                            <div class="status-action-row-premium">
                                <form action="dashboard.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="final_submit">
                                    <button type="submit" class="btn btn-primary fw-bold" onclick="return confirm('Are you sure you want to finalize and submit? You will not be able to edit this form.');">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Final Submit Application
                                    </button>
                                </form>
                                <a href="apply.php" class="btn btn-secondary"><i class="fa-solid fa-pen me-2"></i>Edit Form</a>
                                <a href="upload.php" class="btn btn-secondary"><i class="fa-solid fa-file-image me-2"></i>Manage Uploads</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Application Submitted and Locked -->
                        <?php if ($student['status'] === 'Pending'): ?>
                            <div class="status-card-premium status-card-pending">
                                <div class="status-stepper-premium">
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Fill Details</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Upload Docs</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Payment</div>
                                    </div>
                                    <div class="status-stepper-step-premium active">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-spinner fa-spin"></i></div>
                                        <div class="status-stepper-label-premium">Verification</div>
                                    </div>
                                </div>
                                
                                <div class="status-header-premium">
                                    <div class="status-icon-wrapper-premium">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <div>
                                        <div class="status-title-premium">Application Status: PENDING VERIFICATION</div>
                                        <div class="status-subtitle-premium">Under Review</div>
                                    </div>
                                </div>
                                <div class="status-body-premium">
                                    <p>Your application (Admission ID: <strong><?php echo e($student['admission_no']); ?></strong>) was submitted on <strong><?php echo date('d-M-Y H:i', strtotime($student['created_at'])); ?></strong>. It is currently under review by our admission staff. We will notify you here once verified.</p>
                                </div>
                            </div>
                        <?php elseif ($student['status'] === 'Approved'): ?>
                            <div class="status-card-premium status-card-approved">
                                <div class="status-stepper-premium">
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Fill Details</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Upload Docs</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Payment</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Verification</div>
                                    </div>
                                </div>
                                
                                <div class="status-header-premium">
                                    <div class="status-icon-wrapper-premium">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <div>
                                        <div class="status-title-premium">Application Status: APPROVED</div>
                                        <div class="status-subtitle-premium">Process Complete</div>
                                    </div>
                                </div>
                                <div class="status-body-premium">
                                    <p>Congratulations! Your admission application for <strong><?php echo e($student['course_name']); ?></strong> has been verified and approved. You can now download your official admission receipt below.</p>
                                </div>
                                <div class="status-action-row-premium">
                                    <a href="receipt.php" target="_blank" class="btn btn-primary"><i class="fa-solid fa-file-pdf me-2"></i>Download Admission Receipt</a>
                                </div>
                            </div>
                        <?php elseif ($student['status'] === 'Rejected'): ?>
                            <div class="status-card-premium status-card-rejected">
                                <div class="status-stepper-premium">
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Fill Details</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Upload Docs</div>
                                    </div>
                                    <div class="status-stepper-step-premium completed">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-check"></i></div>
                                        <div class="status-stepper-label-premium">Payment</div>
                                    </div>
                                    <div class="status-stepper-step-premium active">
                                        <div class="status-stepper-dot-premium"><i class="fa-solid fa-xmark"></i></div>
                                        <div class="status-stepper-label-premium">Verification</div>
                                    </div>
                                </div>
                                
                                <div class="status-header-premium">
                                    <div class="status-icon-wrapper-premium">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                    <div>
                                        <div class="status-title-premium">Application Status: REJECTED</div>
                                        <div class="status-subtitle-premium">Action Required</div>
                                    </div>
                                </div>
                                <div class="status-body-premium">
                                    <p>Your application was rejected by the admission staff due to verification issues.</p>
                                    <?php
                                        // Fetch the latest remarks from status history
                                        $hist_stmt = $pdo->prepare("SELECT remarks FROM status_history WHERE student_id = :student_id ORDER BY history_id DESC LIMIT 1");
                                        $hist_stmt->execute(['student_id' => $student['student_id']]);
                                        $history = $hist_stmt->fetch();
                                    ?>
                                    <div class="status-remarks-box">
                                        <strong>Staff Remarks:</strong> <?php echo e($history ? $history['remarks'] : 'No remarks provided.'); ?>
                                    </div>
                                    <p class="small text-muted">You can edit your details and re-upload documents to correct the error and re-submit.</p>
                                </div>
                                <div class="status-action-row-premium">
                                    <a href="apply.php" class="btn btn-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Form & Re-Submit</a>
                                </div>
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
                        <div class="card">
                            <div class="card-header">
                                <i class="fa-solid fa-user me-2"></i>Application Details Preview
                                <?php if ($student['is_submitted'] == 0): ?>
                                    <span class="badge bg-secondary float-end">Draft (Unsubmitted)</span>
                                <?php else: ?>
                                    <span class="badge bg-success float-end">Submitted</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <h5 class="fw-bold text-primary border-bottom pb-2">Personal Information</h5>
                                    <div class="col-md-6 mb-2">
                                        <strong>Admission No:</strong> <?php echo e($student['admission_no']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Applied Course:</strong> <?php echo e($student['course_name']); ?>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong>Student Full Name:</strong> <?php echo e($student['full_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Father's Name:</strong> <?php echo e($student['father_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Mother's Name:</strong> <?php echo e($student['mother_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Gender:</strong> <?php echo e($student['gender']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>DOB:</strong> <?php echo date('d-M-Y', strtotime($student['dob'])); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Category:</strong> <?php echo e($student['category']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Mobile:</strong> <?php echo e($student['mobile']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Email Address:</strong> <?php echo e($student['email']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Pincode:</strong> <?php echo e($student['pincode']); ?>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong>Address:</strong> <?php echo e($student['address']) . ", " . e($student['city']) . ", " . e($student['state']); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <h5 class="fw-bold text-primary border-bottom pb-2">Academic Information</h5>
                                    <div class="col-md-6 mb-2">
                                        <strong>10th Percentage:</strong> <?php echo e($student['tenth_percentage']); ?>%
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>12th Percentage:</strong> <?php echo e($student['twelfth_percentage']); ?>%
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Previous School:</strong> <?php echo e($student['school_name']); ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Passing Year:</strong> <?php echo e($student['passing_year']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Uploaded Documents Card -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa-solid fa-file-lines me-2"></i>Required Documents
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <!-- Photo Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-image me-2 text-primary"></i>Student Photo</span>
                                        <?php if ($documents && !empty($documents['photo'])): ?>
                                            <span class="badge bg-success-subtle text-success badge"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- 10th Marksheet Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-file-pdf me-2 text-danger"></i>10th Marksheet</span>
                                        <?php if ($documents && !empty($documents['marksheet10'])): ?>
                                            <span class="badge bg-success-subtle text-success badge"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- 12th Marksheet Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-file-pdf me-2 text-danger"></i>12th Marksheet</span>
                                        <?php if ($documents && !empty($documents['marksheet12'])): ?>
                                            <span class="badge bg-success-subtle text-success badge"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- LC Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-file-word me-2 text-info"></i>Leaving Certificate</span>
                                        <?php if ($documents && !empty($documents['leaving_certificate'])): ?>
                                            <span class="badge bg-success-subtle text-success badge"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- Aadhaar Status -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <span><i class="fa-regular fa-address-card me-2 text-success"></i>Aadhaar Card</span>
                                        <?php if ($documents && !empty($documents['aadhaar'])): ?>
                                            <span class="badge bg-success-subtle text-success badge"><i class="fa-solid fa-check me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger badge"><i class="fa-solid fa-xmark me-1"></i>Missing</span>
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

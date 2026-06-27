<?php
// ====================================================================
// Document Verification Page (staff/verify.php)
// This page displays a student's personal, academic, and document files.
// It allows staff to approve or reject (requires remarks) applications
// and logs the history.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify that the user is logged in as staff
check_access('staff');

$error_msg = "";
$success_msg = "";

// Ensure that a student ID is provided in the query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$student_id = intval($_GET['id']);
$student = null;
$documents = null;

try {
    // 1. Fetch student details and course details
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name, c.department, c.semester 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        WHERE s.student_id = :student_id AND s.is_submitted = 1
    ");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        // Redirect back if student not found or not submitted
        header("Location: dashboard.php");
        exit;
    }
    
    // 2. Fetch student documents
    $doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE student_id = :student_id");
    $doc_stmt->execute(['student_id' => $student_id]);
    $documents = $doc_stmt->fetch();
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 3. Process Approval or Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    
    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();
            
            // Set student status to 'Approved'
            $update_stmt = $pdo->prepare("UPDATE students SET status = 'Approved' WHERE student_id = :student_id");
            $update_stmt->execute(['student_id' => $student_id]);
            
            // Insert status history entry
            $hist_stmt = $pdo->prepare("INSERT INTO status_history (student_id, status, remarks) VALUES (:student_id, 'Approved', :remarks)");
            $hist_stmt->execute([
                'student_id' => $student_id,
                'remarks' => !empty($remarks) ? $remarks : "Verified and approved by staff member: " . $_SESSION['name']
            ]);
            
            $pdo->commit();
            $success_msg = "Application has been approved successfully.";
            
            // Refresh student details
            header("Location: dashboard.php?msg=approved");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Transaction failed: " . $e->getMessage();
        }
    } elseif ($action === 'reject') {
        // Remarks are compulsory for rejection
        if (empty($remarks)) {
            $error_msg = "Remarks are mandatory when rejecting an application.";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Set student status to 'Rejected' and reset is_submitted to 0 so the student can edit
                $update_stmt = $pdo->prepare("UPDATE students SET status = 'Rejected', is_submitted = 0 WHERE student_id = :student_id");
                $update_stmt->execute(['student_id' => $student_id]);
                
                // Insert status history entry
                $hist_stmt = $pdo->prepare("INSERT INTO status_history (student_id, status, remarks) VALUES (:student_id, 'Rejected', :remarks)");
                $hist_stmt->execute([
                    'student_id' => $student_id,
                    'remarks' => $remarks
                ]);
                
                $pdo->commit();
                $success_msg = "Application has been rejected and student notified.";
                
                header("Location: dashboard.php?msg=rejected");
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_msg = "Transaction failed: " . $e->getMessage();
            }
        }
    }
}

$page_title = "Verify Student Application";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="navbar-brand ms-3">Verification Desk</span>
                <div class="ms-auto">
                    <a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Applicants</a>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Notifications -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Left Column: Student Details -->
                <div class="col-lg-6">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-address-card me-2"></i>Applicant Details Preview
                        </div>
                        <div class="card-body">
                            <!-- Admission Meta -->
                            <div class="row mb-4 p-3 bg-light rounded border border-light-subtle">
                                <div class="col-md-6">
                                    <small class="text-muted block">Admission ID</small>
                                    <div class="fw-bold fs-5 text-primary"><?php echo htmlspecialchars($student['admission_no']); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted block">Current Decision</small>
                                    <div>
                                        <?php if ($student['status'] === 'Pending'): ?>
                                            <span class="badge badge-pending">Pending Verification</span>
                                        <?php elseif ($student['status'] === 'Approved'): ?>
                                            <span class="badge badge-approved">Approved</span>
                                        <?php elseif ($student['status'] === 'Rejected'): ?>
                                            <span class="badge badge-rejected">Rejected</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Info Grid -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Personal Details</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-md-12"><strong>Student Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></div>
                                <div class="col-md-6"><strong>Father's Name:</strong> <?php echo htmlspecialchars($student['father_name']); ?></div>
                                <div class="col-md-6"><strong>Mother's Name:</strong> <?php echo htmlspecialchars($student['mother_name']); ?></div>
                                <div class="col-md-6"><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></div>
                                <div class="col-md-6"><strong>DOB:</strong> <?php echo date('d-M-Y', strtotime($student['dob'])); ?></div>
                                <div class="col-md-6"><strong>Category:</strong> <?php echo htmlspecialchars($student['category']); ?></div>
                                <div class="col-md-6"><strong>Mobile:</strong> <?php echo htmlspecialchars($student['mobile']); ?></div>
                                <div class="col-md-6"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></div>
                                <div class="col-md-12"><strong>Address:</strong> <?php echo htmlspecialchars($student['address']) . ", " . htmlspecialchars($student['city']) . ", " . htmlspecialchars($student['state']) . " - " . htmlspecialchars($student['pincode']); ?></div>
                            </div>

                            <!-- Academic Info Grid -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Academic Scorecards</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-md-6"><strong>10th Percentage:</strong> <?php echo htmlspecialchars($student['tenth_percentage']); ?>%</div>
                                <div class="col-md-6"><strong>12th Percentage:</strong> <span class="fw-bold <?php echo ($student['twelfth_percentage'] >= 35) ? 'text-success' : 'text-danger'; ?>"><?php echo htmlspecialchars($student['twelfth_percentage']); ?>%</span></div>
                                <div class="col-md-6"><strong>Previous School:</strong> <?php echo htmlspecialchars($student['school_name']); ?></div>
                                <div class="col-md-6"><strong>Passing Year:</strong> <?php echo htmlspecialchars($student['passing_year']); ?></div>
                            </div>

                            <!-- Course Selection Info Grid -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Course Preferred</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-md-12"><strong>Program:</strong> <?php echo htmlspecialchars($student['course_name']); ?></div>
                                <div class="col-md-6"><strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?></div>
                                <div class="col-md-6"><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester']); ?></div>
                            </div>

                            <!-- Processing Fee Payment Details -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Processing Fee Payment</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <strong>Status:</strong> 
                                    <?php if ($student['payment_status'] === 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success font-weight-bold px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger font-weight-bold px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Unpaid</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>UPI Transaction Ref:</strong> 
                                    <span class="font-monospace text-dark fw-bold"><?php echo htmlspecialchars($student['transaction_id'] ? $student['transaction_id'] : 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Documents and Actions -->
                <div class="col-lg-6">
                    <!-- Documents Checklist Card -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-folder-open me-2"></i>Document Verification Checklist
                        </div>
                        <div class="card-body">
                            <?php if (!$documents): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>No documents uploaded yet by this applicant.
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush mb-4">
                                    <!-- Photo -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <div>
                                            <i class="fa-regular fa-image text-primary me-2"></i><strong>Candidate Photo</strong>
                                        </div>
                                        <?php if (!empty($documents['photo'])): ?>
                                            <a href="../uploads/photo/<?php echo htmlspecialchars($documents['photo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i>View Document
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Uploaded</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- 10th Marksheet -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <div>
                                            <i class="fa-regular fa-file-pdf text-danger me-2"></i><strong>10th Marksheet</strong>
                                        </div>
                                        <?php if (!empty($documents['marksheet10'])): ?>
                                            <a href="../uploads/marksheet10/<?php echo htmlspecialchars($documents['marksheet10']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i>View Document
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Uploaded</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- 12th Marksheet -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <div>
                                            <i class="fa-regular fa-file-pdf text-danger me-2"></i><strong>12th Marksheet</strong>
                                        </div>
                                        <?php if (!empty($documents['marksheet12'])): ?>
                                            <a href="../uploads/marksheet12/<?php echo htmlspecialchars($documents['marksheet12']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i>View Document
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Uploaded</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- Leaving Certificate -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <div>
                                            <i class="fa-regular fa-file-word text-info me-2"></i><strong>Leaving Certificate</strong>
                                        </div>
                                        <?php if (!empty($documents['leaving_certificate'])): ?>
                                            <a href="../uploads/leaving_certificate/<?php echo htmlspecialchars($documents['leaving_certificate']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i>View Document
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Uploaded</span>
                                        <?php endif; ?>
                                    </li>

                                    <!-- Aadhaar Card -->
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                        <div>
                                            <i class="fa-regular fa-address-card text-success me-2"></i><strong>Aadhaar Card</strong>
                                        </div>
                                        <?php if (!empty($documents['aadhaar'])): ?>
                                            <a href="../uploads/aadhaar/<?php echo htmlspecialchars($documents['aadhaar']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i>View Document
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Uploaded</span>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            <?php endif; ?>

                            <!-- Action Box -->
                            <?php if ($student['status'] === 'Pending'): ?>
                                <div class="bg-light p-4 rounded border">
                                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-gavel me-2"></i>Verification Action</h5>
                                    
                                    <form action="verify.php?id=<?php echo $student_id; ?>" method="POST" id="verifyForm">
                                        <!-- Remarks input -->
                                        <div class="mb-3">
                                            <label for="remarks" class="form-label form-label">Review Remarks / Reason for Rejection</label>
                                            <textarea class="form-control form-control" id="remarks" name="remarks" rows="3" placeholder="Enter feedback here... Required for rejections."></textarea>
                                        </div>

                                        <div class="row g-2">
                                            <!-- Reject button -->
                                            <div class="col-md-6">
                                                <button type="submit" name="action" value="reject" class="btn btn-danger w-100 py-2.5 fw-bold" onclick="return confirmReject();">
                                                    <i class="fa-solid fa-circle-xmark me-1"></i>Reject Application
                                                </button>
                                            </div>
                                            <!-- Approve button -->
                                            <div class="col-md-6">
                                                <button type="submit" name="action" value="approve" class="btn btn-success w-100 py-2.5 fw-bold" onclick="return confirm('Are you sure you want to approve this application?');">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Approve Admission
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary text-center" role="alert">
                                    <i class="fa-solid fa-lock me-2"></i>This application has already been processed (Decision: <strong><?php echo $student['status']; ?></strong>).
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prevent rejection without remarks
function confirmReject() {
    const remarks = document.getElementById('remarks').value.trim();
    if (remarks === "") {
        alert("You must provide remarks stating the reason for rejection.");
        return false;
    }
    return confirm("Are you sure you want to reject this application?");
}
</script>

<?php include '../includes/footer.php'; ?>


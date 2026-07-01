<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin access
check_access('admin');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php");
    exit;
}

$student_id = intval($_GET['id']);
$student = null;
$documents = null;
$history = [];

try {
    // 1. Fetch student and course details
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name, c.department, c.semester 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        WHERE s.student_id = :student_id
    ");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: manage_students.php");
        exit;
    }
    
    // 2. Fetch documents
    $doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE student_id = :student_id");
    $doc_stmt->execute(['student_id' => $student_id]);
    $documents = $doc_stmt->fetch();
    
    // 3. Fetch status history timeline
    $hist_stmt = $pdo->prepare("SELECT * FROM status_history WHERE student_id = :student_id ORDER BY history_id DESC");
    $hist_stmt->execute(['student_id' => $student_id]);
    $history = $hist_stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "View Student Details";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <?php render_topbar('Student Profile Viewer', '<a href="manage_students.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Database</a>'); ?>

        <div class="container-fluid">
            <div class="row">
                <!-- Profile details -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-user-shield me-2 text-primary"></i>Student Admission Profile Details
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 p-3 bg-light rounded border">
                                <div class="col-6">
                                    <small class="text-muted block">Admission Number</small>
                                    <div class="fw-bold fs-5 text-primary"><?php echo e($student['admission_no']); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted block">Current Decision</small>
                                    <div>
                                        <?php if ($student['status'] === 'Pending'): ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php elseif ($student['status'] === 'Approved'): ?>
                                            <span class="badge badge-approved">Approved</span>
                                        <?php elseif ($student['status'] === 'Rejected'): ?>
                                            <span class="badge badge-rejected">Rejected</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Personal Information</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-md-12"><strong>Student Full Name:</strong> <?php echo e($student['full_name']); ?></div>
                                <div class="col-md-6"><strong>Father's Name:</strong> <?php echo e($student['father_name']); ?></div>
                                <div class="col-md-6"><strong>Mother's Name:</strong> <?php echo e($student['mother_name']); ?></div>
                                <div class="col-md-6"><strong>Gender:</strong> <?php echo e($student['gender']); ?></div>
                                <div class="col-md-6"><strong>Date of Birth:</strong> <?php echo date('d-M-Y', strtotime($student['dob'])); ?></div>
                                <div class="col-md-6"><strong>Category:</strong> <?php echo e($student['category']); ?></div>
                                <div class="col-md-6"><strong>Mobile No:</strong> <?php echo e($student['mobile']); ?></div>
                                <div class="col-md-6"><strong>Email Address:</strong> <?php echo e($student['email']); ?></div>
                                <div class="col-md-6"><strong>Pincode:</strong> <?php echo e($student['pincode']); ?></div>
                                <div class="col-md-12"><strong>Full Address:</strong> <?php echo e($student['address']) . ", " . e($student['city']) . ", " . e($student['state']); ?></div>
                            </div>

                            <!-- Academic Profile -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Academic Qualifications</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-md-6"><strong>10th Percentage:</strong> <?php echo e($student['tenth_percentage']); ?>%</div>
                                <div class="col-md-6"><strong>12th Percentage:</strong> <?php echo e($student['twelfth_percentage']); ?>%</div>
                                <div class="col-md-6"><strong>Previous School Name:</strong> <?php echo e($student['school_name']); ?></div>
                                <div class="col-md-6"><strong>Passing Year:</strong> <?php echo e($student['passing_year']); ?></div>
                            </div>

                            <!-- Preferred Program -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Preferred Course Detail</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-md-12"><strong>Program:</strong> <?php echo e($student['course_name']); ?></div>
                                <div class="col-md-6"><strong>Department:</strong> <?php echo e($student['department']); ?></div>
                                <div class="col-md-6"><strong>Semester:</strong> <?php echo e($student['semester']); ?></div>
                            </div>

                            <!-- Processing Fee Details -->
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Processing Fee Payment Detail</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <strong>Payment Status:</strong> 
                                    <?php if ($student['payment_status'] === 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success font-weight-bold px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger font-weight-bold px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Unpaid</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>UPI UTR/Ref ID:</strong> 
                                    <span class="font-monospace text-dark fw-bold"><?php echo e($student['transaction_id'] ? $student['transaction_id'] : 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents and Status History -->
                <div class="col-lg-5">
                    <!-- Documents Preview -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fa-solid fa-folder-closed me-2 text-primary"></i>Uploaded Certificates File
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush px-3 py-2">
                                <?php
                                $file_names = [
                                    'photo' => ['Photo Image', 'fa-image text-primary'],
                                    'marksheet10' => ['10th Marksheet', 'fa-file-pdf text-danger'],
                                    'marksheet12' => ['12th Marksheet', 'fa-file-pdf text-danger'],
                                    'leaving_certificate' => ['Leaving Certificate', 'fa-file-lines text-info'],
                                    'aadhaar' => ['Aadhaar Card', 'fa-address-card text-success']
                                ];
                                ?>
                                <?php foreach ($file_names as $col => $lbl): ?>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom last-border-0">
                                        <span><i class="fa-regular <?php echo $lbl[1]; ?> me-2"></i><strong><?php echo $lbl[0]; ?></strong></span>
                                        <?php if ($documents && !empty($documents[$col])): ?>
                                            <a href="../uploads/<?php echo $col; ?>/<?php echo e($documents[$col]); ?>" target="_blank" class="btn btn-xs btn-outline-secondary">
                                                <i class="fa-solid fa-up-right-from-square"></i> Open
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">Missing</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status Log timeline -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-timeline me-2 text-primary"></i>Application Review Timeline
                        </div>
                        <div class="card-body">
                            <?php if (empty($history)): ?>
                                <p class="text-muted text-center mb-0">No application reviews logged yet.</p>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($history as $log): ?>
                                        <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                                            <div class="position-absolute bg-primary rounded-circle" style="width: 10px; height: 10px; left: -6px; top: 6px;"></div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge <?php echo ($log['status'] === 'Approved') ? 'badge-approved' : (($log['status'] === 'Rejected') ? 'badge-rejected' : 'badge-pending'); ?>">
                                                    <?php echo $log['status']; ?>
                                                </span>
                                                <small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($log['updated_at'])); ?></small>
                                            </div>
                                            <p class="text-muted small mb-0 bg-light p-2 rounded mt-1 border"><?php echo e($log['remarks']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


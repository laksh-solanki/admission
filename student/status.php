<?php
// ====================================================================
// Student Application Status Tracking Page (student/status.php)
// This page fetches and displays the full timeline history of reviews,
// approvals, and rejections from the `status_history` table.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify student role
check_access('student');

$user_id = $_SESSION['user_id'];
$student = null;
$history = [];

try {
    // 1. Fetch student details
    $stmt = $pdo->prepare("SELECT student_id, admission_no, status FROM students WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch();
    
    if ($student) {
        // 2. Fetch history records ordered by date descending
        $hist_stmt = $pdo->prepare("SELECT * FROM status_history WHERE student_id = :student_id ORDER BY history_id DESC");
        $hist_stmt->execute(['student_id' => $student['student_id']]);
        $history = $hist_stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "Track Application Status";
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
                <span class="navbar-brand ms-3">Application Status Tracker</span>
            </div>
        </nav>

        <div class="container-fluid">
            <?php if (!$student): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>You have not initiated an admission form yet. Please fill the admission details first.
                </div>
            <?php else: ?>
                <div class="row">
                    <!-- Current Status Card -->
                    <div class="col-md-4 mb-4">
                        <div class="card bg-white p-4">
                            <h5 class="fw-bold color-primary mb-3">Application Status</h5>
                            <div class="mb-3">
                                <strong>Admission Number:</strong>
                                <div class="fs-5 text-dark fw-bold"><?php echo htmlspecialchars($student['admission_no']); ?></div>
                            </div>
                            <div>
                                <strong>Current Decision:</strong>
                                <div class="mt-1">
                                    <?php if ($student['status'] === 'Pending'): ?>
                                        <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-spinner fa-spin me-1"></i>Pending</span>
                                    <?php elseif ($student['status'] === 'Approved'): ?>
                                        <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-circle-check me-1"></i>Approved</span>
                                    <?php elseif ($student['status'] === 'Rejected'): ?>
                                        <span class="badge bg-danger px-3 py-2 fs-6"><i class="fa-solid fa-circle-xmark me-1"></i>Rejected</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($student['status'] === 'Approved'): ?>
                                <hr>
                                <a href="receipt.php" target="_blank" class="btn btn-success w-100"><i class="fa-solid fa-file-pdf me-2"></i>Download Receipt</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Timeline Card -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa-solid fa-clock-rotate-left me-2"></i>Status History & Remarks Log
                            </div>
                            <div class="card-body">
                                <?php if (empty($history)): ?>
                                    <p class="text-muted text-center py-4">No reviews recorded yet. Please submit your application.</p>
                                <?php else: ?>
                                    <div class="timeline">
                                        <?php foreach ($history as $log): ?>
                                            <div class="border-start border-3 border-primary ps-3 pb-4 position-relative">
                                                <!-- Tiny dot on the border -->
                                                <div class="position-absolute bg-primary rounded-circle" style="width: 12px; height: 12px; left: -8px; top: 5px;"></div>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div>
                                                        <?php if ($log['status'] === 'Pending'): ?>
                                                            <span class="badge badge-pending">Pending</span>
                                                        <?php elseif ($log['status'] === 'Approved'): ?>
                                                            <span class="badge badge-approved">Approved</span>
                                                        <?php elseif ($log['status'] === 'Rejected'): ?>
                                                            <span class="badge badge-rejected">Rejected</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i><?php echo date('d-M-Y h:i A', strtotime($log['updated_at'])); ?></span>
                                                </div>
                                                <div class="bg-light p-3 rounded mt-2 border">
                                                    <strong>Remarks:</strong>
                                                    <p class="text-muted mb-0 small mt-1"><?php echo htmlspecialchars($log['remarks']); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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


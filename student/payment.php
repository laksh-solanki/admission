<?php
// ====================================================================
// Fees Payment Gateway (student/payment.php)
// This file handles student processing fee payments. It shows payment
// instructions, references a UPI address, collects transaction UTR,
// and saves details to the `students` table to unlock final submission.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify student access
check_access('student');

$user_id = $_SESSION['user_id'];
$error_msg = "";
$success_msg = "";

try {
    // 1. Fetch student details and documents upload status
    $stmt = $pdo->prepare("SELECT s.*, d.photo, d.marksheet10, d.marksheet12, d.leaving_certificate, d.aadhaar 
                           FROM students s 
                           LEFT JOIN documents d ON s.student_id = d.student_id 
                           WHERE s.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        // Details form not filled yet
        header("Location: apply.php");
        exit;
    }
    
    // Check if documents are uploaded
    $has_docs = ($student['photo'] && $student['marksheet10'] && $student['marksheet12'] && $student['leaving_certificate'] && $student['aadhaar']);
    if (!$has_docs) {
        header("Location: upload.php");
        exit;
    }
    
    // If already submitted, lock access
    if ($student['is_submitted'] == 1) {
        header("Location: dashboard.php");
        exit;
    }
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 2. Process payment transaction submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = trim($_POST['transaction_id']);
    
    if (empty($transaction_id)) {
        $error_msg = "Please enter your Transaction Reference ID / UTR number.";
    } elseif (strlen($transaction_id) < 8) {
        $error_msg = "Transaction Reference ID must be at least 8 characters long.";
    } else {
        try {
            // Update payment details in students table
            $update_stmt = $pdo->prepare("UPDATE students SET payment_status = 'Paid', transaction_id = :transaction_id WHERE student_id = :student_id");
            $update_stmt->execute([
                'transaction_id' => $transaction_id,
                'student_id' => $student['student_id']
            ]);
            
            header("Location: dashboard.php?msg=paid");
            exit;
        } catch (PDOException $e) {
            $error_msg = "Payment recording failed: " . $e->getMessage();
        }
    }
}

$page_title = "Pay Processing Fee";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <?php render_topbar('Student Admission Portal', '<span class="text-muted small"><i class="fa-solid fa-circle-user me-1"></i>' . e($_SESSION['email']) . '</span>'); ?>

        <div class="container-fluid">
            <!-- Stepper Container -->
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
            </div>

            <div class="row">
                <!-- Payment Instructions -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-credit-card me-2"></i>Processing Fee Details
                        </div>
                        <div class="card-body">
                            <?php if ($student['payment_status'] === 'Paid'): ?>
                                <div class="alert alert-success">
                                    <i class="fa-solid fa-circle-check me-2"></i>You have already completed the payment! Transaction ID: <strong><?php echo e($student['transaction_id']); ?></strong>
                                </div>
                                <a href="dashboard.php" class="btn btn-primary mt-3">Return to Dashboard</a>
                            <?php else: ?>
                                <h5 class="fw-bold mb-3 text-primary">Admission Processing Fee: â‚¹500.00</h5>
                                <p class="text-muted">Please pay the non-refundable processing fee of â‚¹500.00 using any UPI application (such as Google Pay, PhonePe, Paytm, BHIM, etc.) to the UPI ID listed below.</p>
                                
                                <div class="p-4 my-4 bg-light rounded border border-dashed border-primary text-center">
                                    <h6 class="text-uppercase fw-bold text-muted mb-2">Scan or Pay via UPI</h6>
                                    <div class="fs-4 fw-bold text-dark my-2"><i class="fa-solid fa-qrcode me-2 text-primary"></i>sctadmissions@upi</div>
                                    <p class="small text-muted mb-0">State College of Technology - Admissions Account</p>
                                </div>

                                <div class="mt-4">
                                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-2 text-info"></i>How to complete the payment:</h6>
                                    <ol class="small text-muted ps-3">
                                        <li class="mb-2">Open your preferred UPI mobile application.</li>
                                        <li class="mb-2">Send precisely <strong>â‚¹500.00</strong> to the UPI ID: <strong class="text-dark">sctadmissions@upi</strong>.</li>
                                        <li class="mb-2">Note down the 12-digit UPI Transaction Reference Number / UTR number from your payment confirmation screen.</li>
                                        <li class="mb-2">Enter that Reference ID in the payment submission form on the right and submit.</li>
                                    </ol>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Reference Form -->
                <?php if ($student['payment_status'] !== 'Paid'): ?>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa-solid fa-file-invoice-dollar me-2"></i>Submit Transaction Reference
                            </div>
                            <div class="card-body">
                                <?php if (!empty($error_msg)): ?>
                                    <div class="alert alert-danger">
                                        <i class="fa-solid fa-circle-xmark me-2"></i><?php echo e($error_msg); ?>
                                    </div>
                                <?php endif; ?>

                                <form action="payment.php" method="POST">
                                    <div class="mb-3">
                                        <label for="transaction_id" class="form-label">UPI Transaction Reference ID / UTR</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="e.g. 317583920194" required>
                                        <div class="form-text small text-muted">A 12-digit number (or transaction ID) shown on your payment receipt.</div>
                                    </div>

                                    <div class="alert alert-warning-subtle border border-warning rounded p-3 my-3">
                                        <p class="small text-warning-emphasis mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>Please ensure you submit the correct UTR reference. The transaction will be manually verified by college staff before admission confirmation.</p>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                                        <i class="fa-solid fa-check me-2"></i>Submit Payment details
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


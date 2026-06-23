<?php
// ====================================================================
// Document Upload System (student/upload.php)
// This file handles uploading required certificates. It strictly
// validates file size (<2MB) and extensions (JPEG/PNG/PDF), sanitizes
// and renames files to avoid collisions, and handles database mapping.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify student access
check_access('student');

$user_id = $_SESSION['user_id'];
$error_msg = "";
$success_msg = "";

// 1. Fetch student record and details
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        // Must fill details form first
        header("Location: apply.php");
        exit;
    }
    
    // Check if application is already finalized
    if ($student['is_submitted'] == 1) {
        header("Location: dashboard.php");
        exit;
    }
    
    $student_id = $student['student_id'];
    $admission_no = $student['admission_no'];
    
    // Fetch current document paths
    $doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE student_id = :student_id");
    $doc_stmt->execute(['student_id' => $student_id]);
    $documents = $doc_stmt->fetch();
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 2. Process uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['photo', 'marksheet10', 'marksheet12', 'leaving_certificate', 'aadhaar'];
    $uploaded_paths = [];
    $upload_errors = [];
    
    // Define base upload path in root directory
    $upload_base_dir = "../uploads/";
    
    // Create folders if they do not exist
    foreach ($fields as $field) {
        $target_dir = $upload_base_dir . $field . "/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
            // Write a dummy index.html to prevent folder directory listing
            file_put_to_file($target_dir . "index.html", "Access Denied");
        }
    }
    
    // Helper function to write protect index.html
    function file_put_to_file($file, $data) {
        file_put_contents($file, $data);
    }

    foreach ($fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES[$field]['name'];
            $file_size = $_FILES[$field]['size'];
            $file_tmp  = $_FILES[$field]['tmp_name'];
            
            // Validate File Extensions
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($field === 'photo') {
                $allowed = ['jpg', 'jpeg', 'png'];
            } else {
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            }
            
            if (!in_array($ext, $allowed)) {
                $upload_errors[] = "Invalid extension for " . ucfirst($field) . ". Allowed: " . implode(', ', $allowed);
                continue;
            }
            
            // Validate File Size (Max 2MB = 2097152 bytes)
            if ($file_size > 2097152) {
                $upload_errors[] = ucfirst($field) . " file size exceeds the 2MB limit.";
                continue;
            }
            
            // Securely rename file to prevent collision (e.g. photo_ADM2026001_16238382.png)
            $new_name = $field . "_" . $admission_no . "_" . time() . "." . $ext;
            $dest_path = $upload_base_dir . $field . "/" . $new_name;
            
            // Move uploaded file to destination folder
            if (move_uploaded_file($file_tmp, $dest_path)) {
                // Delete previous file if it exists to free server space
                if ($documents && !empty($documents[$field])) {
                    $old_file_path = $upload_base_dir . $field . "/" . basename($documents[$field]);
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                
                // Store path to update database
                $uploaded_paths[$field] = $new_name;
            } else {
                $upload_errors[] = "Failed to upload " . ucfirst($field) . ". Please try again.";
            }
        }
    }
    
    // Update Database if there are uploaded files and no errors
    if (empty($upload_errors) && !empty($uploaded_paths)) {
        try {
            if ($documents) {
                // Update existing record
                $sets = [];
                $params = ['student_id' => $student_id];
                
                foreach ($uploaded_paths as $key => $val) {
                    $sets[] = "$key = :$key";
                    $params[$key] = $val;
                }
                
                $update_sql = "UPDATE documents SET " . implode(', ', $sets) . " WHERE student_id = :student_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute($params);
            } else {
                // Insert new record
                $cols = ['student_id'];
                $vals = [':student_id'];
                $params = ['student_id' => $student_id];
                
                foreach ($fields as $field) {
                    $cols[] = $field;
                    $vals[] = ":" . $field;
                    $params[$field] = isset($uploaded_paths[$field]) ? $uploaded_paths[$field] : null;
                }
                
                $insert_sql = "INSERT INTO documents (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute($params);
            }
            
            $success_msg = "Documents uploaded successfully!";
            
            // Refresh documents details
            $doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE student_id = :student_id");
            $doc_stmt->execute(['student_id' => $student_id]);
            $documents = $doc_stmt->fetch();
            
        } catch (PDOException $e) {
            $error_msg = "Database Update Failed: " . $e->getMessage();
        }
    } elseif (!empty($upload_errors)) {
        $error_msg = implode('<br>', $upload_errors);
    }
}

$page_title = "Upload Required Documents";
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
                <span class="navbar-brand ms-3">Document Upload Center</span>
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
                <!-- Upload Form -->
                <div class="col-lg-8">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Upload Documents (Max 2MB per file)
                        </div>
                        <div class="card-body-custom">
                            <form action="upload.php" method="POST" enctype="multipart/form-data">
                                
                                <!-- Student Photo -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <label class="form-label form-label-custom">1. Student Passport Size Photograph <span class="text-danger">*</span></label>
                                    <input type="file" name="photo" class="form-control form-control-custom" <?php echo ($documents && !empty($documents['photo'])) ? '' : 'required'; ?>>
                                    <div class="form-text small text-muted">Formats: JPG, JPEG, PNG only.</div>
                                    <?php if ($documents && !empty($documents['photo'])): ?>
                                        <div class="mt-2 text-success small">
                                            <i class="fa-solid fa-check-circle me-1"></i>Current File: 
                                            <a href="../uploads/photo/<?php echo htmlspecialchars($documents['photo']); ?>" target="_blank" class="text-success fw-bold text-decoration-underline"><?php echo htmlspecialchars($documents['photo']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- 10th Marksheet -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <label class="form-label form-label-custom">2. 10th Standard Marksheet <span class="text-danger">*</span></label>
                                    <input type="file" name="marksheet10" class="form-control form-control-custom" <?php echo ($documents && !empty($documents['marksheet10'])) ? '' : 'required'; ?>>
                                    <div class="form-text small text-muted">Formats: JPG, JPEG, PNG, PDF.</div>
                                    <?php if ($documents && !empty($documents['marksheet10'])): ?>
                                        <div class="mt-2 text-success small">
                                            <i class="fa-solid fa-check-circle me-1"></i>Current File: 
                                            <a href="../uploads/marksheet10/<?php echo htmlspecialchars($documents['marksheet10']); ?>" target="_blank" class="text-success fw-bold text-decoration-underline"><?php echo htmlspecialchars($documents['marksheet10']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- 12th Marksheet -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <label class="form-label form-label-custom">3. 12th Standard Marksheet <span class="text-danger">*</span></label>
                                    <input type="file" name="marksheet12" class="form-control form-control-custom" <?php echo ($documents && !empty($documents['marksheet12'])) ? '' : 'required'; ?>>
                                    <div class="form-text small text-muted">Formats: JPG, JPEG, PNG, PDF.</div>
                                    <?php if ($documents && !empty($documents['marksheet12'])): ?>
                                        <div class="mt-2 text-success small">
                                            <i class="fa-solid fa-check-circle me-1"></i>Current File: 
                                            <a href="../uploads/marksheet12/<?php echo htmlspecialchars($documents['marksheet12']); ?>" target="_blank" class="text-success fw-bold text-decoration-underline"><?php echo htmlspecialchars($documents['marksheet12']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- School Leaving Certificate -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <label class="form-label form-label-custom">4. School Leaving Certificate <span class="text-danger">*</span></label>
                                    <input type="file" name="leaving_certificate" class="form-control form-control-custom" <?php echo ($documents && !empty($documents['leaving_certificate'])) ? '' : 'required'; ?>>
                                    <div class="form-text small text-muted">Formats: JPG, JPEG, PNG, PDF.</div>
                                    <?php if ($documents && !empty($documents['leaving_certificate'])): ?>
                                        <div class="mt-2 text-success small">
                                            <i class="fa-solid fa-check-circle me-1"></i>Current File: 
                                            <a href="../uploads/leaving_certificate/<?php echo htmlspecialchars($documents['leaving_certificate']); ?>" target="_blank" class="text-success fw-bold text-decoration-underline"><?php echo htmlspecialchars($documents['leaving_certificate']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Aadhaar Card -->
                                <div class="mb-4">
                                    <label class="form-label form-label-custom">5. Aadhaar Card <span class="text-danger">*</span></label>
                                    <input type="file" name="aadhaar" class="form-control form-control-custom" <?php echo ($documents && !empty($documents['aadhaar'])) ? '' : 'required'; ?>>
                                    <div class="form-text small text-muted">Formats: JPG, JPEG, PNG, PDF.</div>
                                    <?php if ($documents && !empty($documents['aadhaar'])): ?>
                                        <div class="mt-2 text-success small">
                                            <i class="fa-solid fa-check-circle me-1"></i>Current File: 
                                            <a href="../uploads/aadhaar/<?php echo htmlspecialchars($documents['aadhaar']); ?>" target="_blank" class="text-success fw-bold text-decoration-underline"><?php echo htmlspecialchars($documents['aadhaar']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                                    <a href="dashboard.php" class="btn btn-outline-secondary py-2"><i class="fa-solid fa-arrow-left me-1"></i>Back to Dashboard</a>
                                    <button type="submit" class="btn btn-custom-secondary px-5 py-2 fw-bold">
                                        <i class="fa-solid fa-arrow-up-from-bracket me-1"></i>Upload Selected
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <!-- Info Help Sidebar -->
                <div class="col-lg-4">
                    <div class="card-custom bg-white">
                        <div class="card-header-custom bg-light">
                            <i class="fa-solid fa-circle-info text-info me-1"></i>Uploading Guideline
                        </div>
                        <div class="card-body-custom small">
                            <ul class="list-unstyled ps-0 mb-0">
                                <li class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i><strong>Format compatibility:</strong> The passport photo should only be an image file (JPG, JPEG, PNG). The marksheets, Leaving Certificate, and Aadhaar card can be uploaded as PDFs or image files.</li>
                                <li class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i><strong>Size Constraint:</strong> Each file must be under 2MB. Files larger than this will fail validation.</li>
                                <li class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i><strong>Legibility:</strong> Ensure that text and details on marksheets are clearly readable. Staff might reject illegible scans.</li>
                                <li class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i><strong>Re-uploads:</strong> If you re-upload a document, it will automatically overwrite your previous upload.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

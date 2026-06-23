<?php
// ====================================================================
// Student Administration Page (admin/manage_students.php)
// This page provides view, search, edit, and delete functionality
// for student records. It utilizes ON DELETE CASCADE constraints
// to clean up associated data in MySQL.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin access
check_access('admin');

$error_msg = "";
$success_msg = "";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

// --------------------------------------------------------------------
// PROCESS ACTIONS (DELETE & EDIT UPDATE)
// --------------------------------------------------------------------

// 1. Delete Student (Unlinks document files, then deletes user row)
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $pdo->beginTransaction();
        
        // Fetch student user_id and document paths
        $stmt = $pdo->prepare("
            SELECT s.user_id, s.student_id, d.photo, d.marksheet10, d.marksheet12, d.leaving_certificate, d.aadhaar 
            FROM students s 
            LEFT JOIN documents d ON s.student_id = d.student_id 
            WHERE s.student_id = :id
        ");
        $stmt->execute(['id' => $delete_id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $user_id_to_del = $data['user_id'];
            
            // Delete actual files from directories
            $file_fields = ['photo', 'marksheet10', 'marksheet12', 'leaving_certificate', 'aadhaar'];
            foreach ($file_fields as $field) {
                if (!empty($data[$field])) {
                    $file_path = "../uploads/" . $field . "/" . basename($data[$field]);
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            }
            
            // Delete user row (cascades to delete students, documents, status_history)
            $del_user = $pdo->prepare("DELETE FROM users WHERE user_id = :uid");
            $del_user->execute(['uid' => $user_id_to_del]);
            
            $pdo->commit();
            $success_msg = "Student and all linked account records/files deleted successfully.";
        } else {
            $pdo->rollBack();
            $error_msg = "Student record not found.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Failed to delete student: " . $e->getMessage();
    }
}

// 2. Edit Student Details (Update details in students table)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_student') {
    $student_id = intval($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $category = trim($_POST['category']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    
    $tenth_percentage = floatval($_POST['tenth_percentage']);
    $twelfth_percentage = floatval($_POST['twelfth_percentage']);
    $school_name = trim($_POST['school_name']);
    $passing_year = intval($_POST['passing_year']);
    
    $course_id = intval($_POST['course_id']);
    $status = $_POST['status'];

    if (empty($first_name) || empty($last_name) || empty($gender) || empty($dob) || empty($category) || empty($mobile) || empty($address) || empty($city) || empty($state) || empty($pincode) || empty($school_name) || empty($passing_year) || empty($course_id)) {
        $error_msg = "All fields are required.";
    } else {
        try {
            // Check for duplicate mobile
            $chk = $pdo->prepare("SELECT student_id FROM students WHERE mobile = :mobile AND student_id != :id");
            $chk->execute(['mobile' => $mobile, 'id' => $student_id]);
            
            if ($chk->rowCount() > 0) {
                $error_msg = "Mobile number is already registered by another student.";
            } else {
                $pdo->beginTransaction();

                // Get current status to check if it has changed
                $status_stmt = $pdo->prepare("SELECT status FROM students WHERE student_id = :id");
                $status_stmt->execute(['id' => $student_id]);
                $old_status = $status_stmt->fetchColumn();

                $update_sql = "
                    UPDATE students SET 
                        first_name = :first_name, last_name = :last_name, gender = :gender, dob = :dob,
                        category = :category, mobile = :mobile, address = :address, city = :city,
                        state = :state, pincode = :pincode, tenth_percentage = :tenth_percentage,
                        twelfth_percentage = :twelfth_percentage, school_name = :school_name,
                        passing_year = :passing_year, course_id = :course_id, status = :status
                    WHERE student_id = :student_id
                ";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'gender' => $gender,
                    'dob' => $dob,
                    'category' => $category,
                    'mobile' => $mobile,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode,
                    'tenth_percentage' => $tenth_percentage,
                    'twelfth_percentage' => $twelfth_percentage,
                    'school_name' => $school_name,
                    'passing_year' => $passing_year,
                    'course_id' => $course_id,
                    'status' => $status,
                    'student_id' => $student_id
                ]);

                // Log status changes in history
                if ($old_status !== $status) {
                    $hist_stmt = $pdo->prepare("INSERT INTO status_history (student_id, status, remarks) VALUES (:student_id, :status, :remarks)");
                    $hist_stmt->execute([
                        'student_id' => $student_id,
                        'status' => $status,
                        'remarks' => "Status updated to " . $status . " by administrator."
                    ]);
                }

                $pdo->commit();
                $success_msg = "Student details updated successfully.";
                $edit_id = 0; // Close editing screen
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Update Failed: " . $e->getMessage();
        }
    }
}

// --------------------------------------------------------------------
// FETCH RECORDS
// --------------------------------------------------------------------

// Fetch courses list for form dropdowns
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC")->fetchAll();

// Fetch student detail for editing if edit_id is set
$edit_student = null;
if ($edit_id > 0) {
    $edit_stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :id");
    $edit_stmt->execute(['id' => $edit_id]);
    $edit_student = $edit_stmt->fetch();
}

// Fetch all students based on search term
try {
    $list_sql = "
        SELECT s.*, c.course_name 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id
    ";
    $list_params = [];
    
    if (!empty($search)) {
        $list_sql .= " WHERE s.admission_no LIKE :search 
                      OR CONCAT(s.first_name, ' ', s.last_name) LIKE :search 
                      OR s.mobile LIKE :search";
        $list_params['search'] = "%$search%";
    }
    
    $list_sql .= " ORDER BY s.student_id DESC";
    $list_stmt = $pdo->prepare($list_sql);
    $list_stmt->execute($list_params);
    $students = $list_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "Manage Student Accounts";
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
                <span class="navbar-brand ms-3">Student Accounts Desk</span>
                <div class="ms-auto">
                    <a href="add_student.php" class="btn btn-sm btn-custom-primary"><i class="fa-solid fa-user-plus me-1"></i>Add Student</a>
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

            <!-- The inline edit form has been relocated to edit_student.php -->

            <!-- Search Filter Card -->
            <div class="card-custom mb-4">
                <div class="card-body-custom">
                    <form action="manage_students.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="search" class="form-label form-label-custom">Filter applicants database</label>
                            <input type="text" class="form-control form-control-custom" id="search" name="search" 
                                placeholder="Search by Admission ID, Name, or Mobile Number..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-custom-primary w-100 py-2"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Student Database Records Table -->
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fa-solid fa-users me-2"></i>Students Database Records
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Admission ID</th>
                                    <th>Student Name</th>
                                    <th>Course Preference</th>
                                    <th>Mobile No</th>
                                    <th>12th Std (%)</th>
                                    <th>Status</th>
                                    <th>Submission</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No student records registered in database.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($s['admission_no']); ?></td>
                                            <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($s['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($s['mobile']); ?></td>
                                            <td><?php echo htmlspecialchars($s['twelfth_percentage']); ?>%</td>
                                            <td>
                                                <?php if ($s['status'] === 'Pending'): ?>
                                                    <span class="badge badge-pending">Pending</span>
                                                <?php elseif ($s['status'] === 'Approved'): ?>
                                                    <span class="badge badge-approved">Approved</span>
                                                <?php elseif ($s['status'] === 'Rejected'): ?>
                                                    <span class="badge badge-rejected">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($s['is_submitted'] == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Submitted</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <!-- View button -->
                                                <a href="view_students.php?id=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="View Profile">
                                                    <i class="fa-solid fa-eye"></i> View
                                                </a>
                                                <!-- Edit button -->
                                                <a href="edit_student.php?id=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit Profile">
                                                    <i class="fa-solid fa-user-pen"></i> Edit
                                                </a>
                                                <!-- Delete button -->
                                                <a href="manage_students.php?delete_id=<?php echo $s['student_id']; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('DANGER: Deleting this student will wipe out their credentials, documents, and logs. Proceed?');">
                                                    <i class="fa-solid fa-user-minus"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

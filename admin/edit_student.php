<?php
// ====================================================================
// Edit Student Page (admin/edit_student.php)
// This page allows administrators to edit a student's personal, academic,
// and course details and saves changes back to the database.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin access
check_access('admin');

$error_msg = "";
$success_msg = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php");
    exit;
}

$student_id = intval($_GET['id']);
$student = null;

try {
    // Fetch courses list
    $courses = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC")->fetchAll();

    // Fetch student record
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :id");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: manage_students.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Process update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_student') {
    $full_name = trim($_POST['full_name']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
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

    if (empty($full_name) || empty($father_name) || empty($mother_name) || empty($gender) || empty($dob) || empty($category) || empty($mobile) || empty($address) || empty($city) || empty($state) || empty($pincode) || empty($school_name) || empty($passing_year) || empty($course_id)) {
        $error_msg = "All fields are compulsory.";
    } else {
        try {
            // Check for duplicate mobile
            $chk = $pdo->prepare("SELECT student_id FROM students WHERE mobile = :mobile AND student_id != :id");
            $chk->execute(['mobile' => $mobile, 'id' => $student_id]);
            
            if ($chk->rowCount() > 0) {
                $error_msg = "Mobile number is already registered by another student.";
            } else {
                $pdo->beginTransaction();

                // Get current status
                $status_stmt = $pdo->prepare("SELECT status FROM students WHERE student_id = :id");
                $status_stmt->execute(['id' => $student_id]);
                $old_status = $status_stmt->fetchColumn();

                $update_sql = "
                    UPDATE students SET 
                        full_name = :full_name, father_name = :father_name, mother_name = :mother_name, gender = :gender, dob = :dob,
                        category = :category, mobile = :mobile, address = :address, city = :city,
                        state = :state, pincode = :pincode, tenth_percentage = :tenth_percentage,
                        twelfth_percentage = :twelfth_percentage, school_name = :school_name,
                        passing_year = :passing_year, course_id = :course_id, status = :status
                    WHERE student_id = :student_id
                ";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    'full_name' => $full_name,
                    'father_name' => $father_name,
                    'mother_name' => $mother_name,
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

                // Log status changes
                if ($old_status !== $status) {
                    $hist_stmt = $pdo->prepare("INSERT INTO status_history (student_id, status, remarks) VALUES (:student_id, :status, :remarks)");
                    $hist_stmt->execute([
                        'student_id' => $student_id,
                        'status' => $status,
                        'remarks' => "Status updated to " . $status . " by administrator."
                    ]);
                }

                $pdo->commit();
                
                // Redirect back
                header("Location: manage_students.php?msg=updated");
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Update Failed: " . $e->getMessage();
        }
    }
}

$page_title = "Edit Student Profile";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <?php render_topbar('Edit Applicant Info', '<a href="manage_students.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Database</a>'); ?>

        <div class="container-fluid">
            <!-- Notifications -->
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo e($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-user-pen me-2"></i>Modify Student Profile: <?php echo e($student['admission_no']); ?>
                </div>
                <div class="card-body">
                    <form action="edit_student.php?id=<?php echo $student_id; ?>" method="POST">
                        <input type="hidden" name="action" value="update_student">
                        
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Personal Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Student Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo e($student['full_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Father's Name</label>
                                <input type="text" class="form-control" name="father_name" value="<?php echo e($student['father_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mother's Name</label>
                                <input type="text" class="form-control" name="mother_name" value="<?php echo e($student['mother_name']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select form-control" name="gender" required>
                                    <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($student['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">DOB</label>
                                <input type="date" class="form-control" name="dob" value="<?php echo e($student['dob']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="category" value="<?php echo e($student['category']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" class="form-control" name="mobile" value="<?php echo e($student['mobile']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" value="<?php echo e($student['address']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" value="<?php echo e($student['city']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" value="<?php echo e($student['state']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" class="form-control" name="pincode" value="<?php echo e($student['pincode']); ?>" required>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Academic Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">10th Std (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" name="tenth_percentage" value="<?php echo e($student['tenth_percentage']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">12th Std (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" name="twelfth_percentage" value="<?php echo e($student['twelfth_percentage']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School Name</label>
                                <input type="text" class="form-control" name="school_name" value="<?php echo e($student['school_name']); ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Passing Year</label>
                                <input type="number" class="form-control" name="passing_year" value="<?php echo e($student['passing_year']); ?>" required>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Status & Program Preference</h6>
                        <div class="row g-3 mb-5">
                            <div class="col-md-6">
                                <label class="form-label">Select Degree</label>
                                <select class="form-select form-control" name="course_id" required>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['course_id']; ?>" <?php echo ($student['course_id'] == $c['course_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($c['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Admission Status</label>
                                <select class="form-select form-control" name="status" required>
                                    <option value="Pending" <?php echo ($student['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo ($student['status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo ($student['status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="manage_students.php" class="btn btn-outline-secondary py-2"><i class="fa-solid fa-arrow-left me-1"></i>Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Update Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


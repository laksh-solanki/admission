<?php
// ====================================================================
// Student Admission Form Page (student/apply.php)
// This page handles filling and editing student details. It performs
// server-side eligibility checks (12th % >= 35), duplicate mobile checks,
// and auto-generates the Admission Number.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify student role
check_access('student');

$user_id = $_SESSION['user_id'];
$error_msg = "";
$success_msg = "";

// Fetch existing details if any
$student = null;
$has_record = false;

try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch();
    
    if ($student) {
        $has_record = true;
        
        // If the application is already finalized, prevent edits
        if ($student['is_submitted'] == 1) {
            header("Location: dashboard.php");
            exit;
        }
    }
    
    // Fetch courses list for the dropdown select input
    $course_stmt = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC");
    $courses = $course_stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_msg = "Database Error: " . $e->getMessage();
}

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input values
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
    
    // Validation
    if (empty($full_name) || empty($father_name) || empty($mother_name) || empty($gender) || empty($dob) || empty($category) || empty($mobile) || empty($address) || empty($city) || empty($state) || empty($pincode) || empty($school_name) || empty($passing_year) || empty($course_id)) {
        $error_msg = "All fields are compulsory.";
    } elseif ($twelfth_percentage < 35.0) {
        // Minimum Eligibility Rule
        $error_msg = "Eligibility Alert: You must have at least 35% in 12th standard to apply.";
    } elseif (strlen($mobile) < 10 || strlen($mobile) > 12 || !is_numeric($mobile)) {
        $error_msg = "Please enter a valid mobile number.";
    } else {
        try {
            // Check for duplicate mobile number (excluding current student record if updating)
            $dup_check_sql = "SELECT student_id FROM students WHERE mobile = :mobile";
            if ($has_record) {
                $dup_check_sql .= " AND student_id != :student_id";
            }
            $dup_stmt = $pdo->prepare($dup_check_sql);
            $dup_params = ['mobile' => $mobile];
            if ($has_record) {
                $dup_params['student_id'] = $student['student_id'];
            }
            $dup_stmt->execute($dup_params);
            
            if ($dup_stmt->rowCount() > 0) {
                $error_msg = "Mobile number is already registered by another applicant.";
            } else {
                if ($has_record) {
                    // Update Existing Record
                    $update_sql = "UPDATE students SET 
                        full_name = :full_name, father_name = :father_name, mother_name = :mother_name, gender = :gender, dob = :dob,
                        category = :category, mobile = :mobile, address = :address, city = :city,
                        state = :state, pincode = :pincode, tenth_percentage = :tenth_percentage,
                        twelfth_percentage = :twelfth_percentage, school_name = :school_name,
                        passing_year = :passing_year, course_id = :course_id 
                        WHERE student_id = :student_id";
                    
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
                        'student_id' => $student['student_id']
                    ]);
                    
                    $success_msg = "Admission details updated successfully.";
                } else {
                    // Create New Record - Auto Generate Admission Number (format: ADM[YEAR][001])
                    $year = date('Y');
                    
                    // Fetch count of students for current year to determine next sequential number
                    $count_stmt = $pdo->prepare("SELECT COUNT(*) as current_count FROM students WHERE admission_no LIKE :prefix");
                    $count_stmt->execute(['prefix' => "ADM" . $year . "%"]);
                    $row = $count_stmt->fetch();
                    $seq_num = $row['current_count'] + 1;
                    
                    // Format: ADM2026001
                    $admission_no = sprintf("ADM%s%03d", $year, $seq_num);
                    
                    // Insert into students table
                    $insert_sql = "INSERT INTO students (
                        user_id, admission_no, full_name, father_name, mother_name, gender, dob, category, mobile, email,
                        address, city, state, pincode, tenth_percentage, twelfth_percentage, school_name,
                        passing_year, course_id, status, is_submitted
                    ) VALUES (
                        :user_id, :admission_no, :full_name, :father_name, :mother_name, :gender, :dob, :category, :mobile, :email,
                        :address, :city, :state, :pincode, :tenth_percentage, :twelfth_percentage, :school_name,
                        :passing_year, :course_id, 'Pending', 0
                    )";
                    
                    $insert_stmt = $pdo->prepare($insert_sql);
                    $insert_stmt->execute([
                        'user_id' => $user_id,
                        'admission_no' => $admission_no,
                        'full_name' => $full_name,
                        'father_name' => $father_name,
                        'mother_name' => $mother_name,
                        'gender' => $gender,
                        'dob' => $dob,
                        'category' => $category,
                        'mobile' => $mobile,
                        'email' => $_SESSION['email'], // Use registered email
                        'address' => $address,
                        'city' => $city,
                        'state' => $state,
                        'pincode' => $pincode,
                        'tenth_percentage' => $tenth_percentage,
                        'twelfth_percentage' => $twelfth_percentage,
                        'school_name' => $school_name,
                        'passing_year' => $passing_year,
                        'course_id' => $course_id
                    ]);
                    
                    $success_msg = "Details submitted successfully. Please proceed to upload certificates.";
                }
                
                // Refresh data
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

$page_title = $has_record ? "Edit Admission Form" : "Fill Admission Form";
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
                <span class="navbar-brand ms-3"><?php echo $page_title; ?></span>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Stepper Container -->
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
            </div>

            <!-- Notification Alert -->
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="card-custom">
                <div class="card-header-custom bg-white">
                    <i class="fa-solid fa-file-invoice me-2 text-primary"></i>Admission Registration Form
                </div>
                <div class="card-body-custom">
                    <form action="apply.php" method="POST" id="admissionForm">
                        
                        <!-- 1. Personal Information -->
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-4"><i class="fa-solid fa-user me-2"></i>Personal Information</h5>
                        <div class="row g-3 mb-4">
                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label for="full_name" class="form-label form-label-custom">Student Full Name</label>
                                <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['full_name']) : ''; ?>" required>
                            </div>
                            <!-- Father's Name -->
                            <div class="col-md-3">
                                <label for="father_name" class="form-label form-label-custom">Father's Name</label>
                                <input type="text" class="form-control form-control-custom" id="father_name" name="father_name" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['father_name']) : ''; ?>" required>
                            </div>
                            <!-- Mother's Name -->
                            <div class="col-md-3">
                                <label for="mother_name" class="form-label form-label-custom">Mother's Name</label>
                                <input type="text" class="form-control form-control-custom" id="mother_name" name="mother_name" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['mother_name']) : ''; ?>" required>
                            </div>
                            <!-- Gender -->
                            <div class="col-md-4">
                                <label for="gender" class="form-label form-label-custom">Gender</label>
                                <select class="form-select form-control-custom" id="gender" name="gender" required>
                                    <option value="">Choose...</option>
                                    <option value="Male" <?php echo ($has_record && $student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($has_record && $student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($has_record && $student['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <!-- DOB -->
                            <div class="col-md-4">
                                <label for="dob" class="form-label form-label-custom">Date of Birth</label>
                                <input type="date" class="form-control form-control-custom" id="dob" name="dob" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['dob']) : ''; ?>" required>
                            </div>
                            <!-- Category -->
                            <div class="col-md-4">
                                <label for="category" class="form-label form-label-custom">Category</label>
                                <select class="form-select form-control-custom" id="category" name="category" required>
                                    <option value="">Choose...</option>
                                    <option value="General" <?php echo ($has_record && $student['category'] === 'General') ? 'selected' : ''; ?>>General</option>
                                    <option value="OBC" <?php echo ($has_record && $student['category'] === 'OBC') ? 'selected' : ''; ?>>OBC</option>
                                    <option value="SC" <?php echo ($has_record && $student['category'] === 'SC') ? 'selected' : ''; ?>>SC</option>
                                    <option value="ST" <?php echo ($has_record && $student['category'] === 'ST') ? 'selected' : ''; ?>>ST</option>
                                    <option value="Other" <?php echo ($has_record && $student['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <!-- Mobile -->
                            <div class="col-md-4">
                                <label for="mobile" class="form-label form-label-custom">Mobile Number</label>
                                <input type="tel" class="form-control form-control-custom" id="mobile" name="mobile" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['mobile']) : ''; ?>" required>
                            </div>
                            <!-- Email (Auto-filled read-only) -->
                            <div class="col-md-6">
                                <label for="email" class="form-label form-label-custom">Email (Registered)</label>
                                <input type="email" class="form-control form-control-custom bg-light" id="email" name="email" 
                                    value="<?php echo $_SESSION['email']; ?>" readonly>
                            </div>
                            <!-- Address -->
                            <div class="col-md-6">
                                <label for="address" class="form-label form-label-custom">Correspondence Address</label>
                                <input type="text" class="form-control form-control-custom" id="address" name="address" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['address']) : ''; ?>" required>
                            </div>
                            <!-- City -->
                            <div class="col-md-4">
                                <label for="city" class="form-label form-label-custom">City</label>
                                <input type="text" class="form-control form-control-custom" id="city" name="city" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['city']) : ''; ?>" required>
                            </div>
                            <!-- State -->
                            <div class="col-md-4">
                                <label for="state" class="form-label form-label-custom">State</label>
                                <input type="text" class="form-control form-control-custom" id="state" name="state" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['state']) : ''; ?>" required>
                            </div>
                            <!-- Pincode -->
                            <div class="col-md-4">
                                <label for="pincode" class="form-label form-label-custom">Pincode</label>
                                <input type="text" class="form-control form-control-custom" id="pincode" name="pincode" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['pincode']) : ''; ?>" required>
                            </div>
                        </div>

                        <!-- 2. Academic Information -->
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-4"><i class="fa-solid fa-user-graduate me-2"></i>Academic Details</h5>
                        <div class="row g-3 mb-4">
                            <!-- 10th Percentage -->
                            <div class="col-md-3">
                                <label for="tenth_percentage" class="form-label form-label-custom">10th Std Percentage (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-custom" id="tenth_percentage" name="tenth_percentage" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['tenth_percentage']) : ''; ?>" required>
                            </div>
                            <!-- 12th Percentage -->
                            <div class="col-md-3">
                                <label for="twelfth_percentage" class="form-label form-label-custom">12th Std Percentage (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-custom" id="twelfth_percentage" name="twelfth_percentage" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['twelfth_percentage']) : ''; ?>" required>
                                <small class="text-muted">Eligibility: Minimum 35% required</small>
                            </div>
                            <!-- School Name -->
                            <div class="col-md-4">
                                <label for="school_name" class="form-label form-label-custom">Previous School / Board Name</label>
                                <input type="text" class="form-control form-control-custom" id="school_name" name="school_name" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['school_name']) : ''; ?>" required>
                            </div>
                            <!-- Passing Year -->
                            <div class="col-md-2">
                                <label for="passing_year" class="form-label form-label-custom">Passing Year</label>
                                <input type="number" min="2000" max="2026" class="form-control form-control-custom" id="passing_year" name="passing_year" 
                                    value="<?php echo $has_record ? htmlspecialchars($student['passing_year']) : date('Y'); ?>" required>
                            </div>
                        </div>

                        <!-- 3. Course Preference -->
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-4"><i class="fa-solid fa-book-bookmark me-2"></i>Course Selection</h5>
                        <div class="row g-3 mb-4">
                            <!-- Course Name Selection -->
                            <div class="col-md-12">
                                <label for="course_id" class="form-label form-label-custom">Select Preferred Program</label>
                                <select class="form-select form-control-custom" id="course_id" name="course_id" required>
                                    <option value="">Select a Course...</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['course_id']; ?>" 
                                            <?php echo ($has_record && $student['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_name']) . " (" . htmlspecialchars($course['department']) . " - " . htmlspecialchars($course['semester']) . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                            <a href="dashboard.php" class="btn btn-outline-secondary py-2"><i class="fa-solid fa-arrow-left me-1"></i>Cancel & Back</a>
                            <button type="submit" class="btn btn-custom-primary px-5 py-2 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Save Details
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to dynamically check 12th minimum percentage and auto-save form details on page refresh -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('admissionForm');
    if (!form) return;

    const userId = '<?php echo $_SESSION['user_id']; ?>';
    const storageKey = `admission_form_draft_${userId}`;

    // Load saved details from localStorage
    function loadSavedData() {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                for (const key in data) {
                    const field = form.elements[key];
                    if (field && key !== 'email') { // Keep registered email read-only
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = (field.value === data[key]);
                        } else {
                            field.value = data[key];
                        }
                    }
                }
            } catch (e) {
                console.error('Error loading saved form data:', e);
            }
        }
    }

    // Save details to localStorage
    function saveData() {
        const data = {};
        const formData = new FormData(form);
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        localStorage.setItem(storageKey, JSON.stringify(data));
    }

    // Restore form values on load
    loadSavedData();

    // Listen for inputs and changes to auto-save
    form.addEventListener('input', saveData);
    form.addEventListener('change', saveData);

    // Dynamic 12th eligibility validation and clearing of local storage on submit
    form.addEventListener('submit', function(event) {
        const percentageInput = document.getElementById('twelfth_percentage');
        const percentage = parseFloat(percentageInput.value);
        
        if (percentage < 35.0) {
            alert("Eligibility Block: You must have scored a minimum of 35% in your 12th standard examinations to apply.");
            event.preventDefault(); // Stop form submission
            return;
        }

        // Clear local storage since it is successfully submitted
        localStorage.removeItem(storageKey);
    });
});
</script>

<?php include '../includes/footer.php'; ?>

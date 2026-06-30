<?php
// ====================================================================
// Student Registration Page (student_register.php)
// This file handles student account registration, hashes passwords using
// BCrypt, verifies email uniqueness, and initializes their session.
// ====================================================================

require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Redirect if already logged in
redirect_if_logged_in();

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Input Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        try {
            // Check if email already exists in users table
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $error_msg = "This email is already registered. Please login.";
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into users table
                $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'student')");
                $insert_stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password
                ]);

                // Fetch new user ID
                $new_user_id = $pdo->lastInsertId();

                login_user($new_user_id, 'student', $name, $email);
                app_redirect('student/dashboard.php');
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

$page_title = "Student Registration";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header text-center bg-white border-0 pt-4">
                    <h3 class="fw-bold text-primary mb-1 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-graduation-cap me-2 text-info fs-3"></i>
                        <span>SCT PORTAL</span>
                    </h3>
                    <p class="text-muted small mb-0 fw-semibold text-uppercase tracking-wider" style="font-size: 11px; color: var(--slate-500) !important;">State College of Technology</p>
                    <p class="text-muted small mt-2"><i class="fa-solid fa-user-plus me-1 text-primary"></i>Student Registration</p>
                </div>
                <div class="card-body pt-0">
                    
                    <?php render_alert($error_msg, 'danger', false, true); ?>

                    <form action="student_register.php" method="POST">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name ?? ''); ?>" required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email ?? ''); ?>" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password (Min. 6 chars)</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Register Now</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <span class="text-muted small">Already have an account?</span>
                        <a href="login.php?role=student" class="text-decoration-none small fw-bold ms-1">Login here</a>
                    </div>
                    <div class="text-center mt-2">
                        <a href="index.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


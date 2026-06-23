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

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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

                // Initialize session variables to auto-login
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['role'] = 'student';
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;

                // Redirect to student dashboard
                header("Location: student/dashboard.php");
                exit;
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
            <div class="card-custom">
                <div class="card-header-custom text-center bg-white border-0 pt-4">
                    <h3 class="fw-bold color-primary"><i class="fa-solid fa-user-plus me-2"></i>Student Registration</h3>
                    <p class="text-muted small">Create an account to start your admission process</p>
                </div>
                <div class="card-body-custom pt-0">
                    
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="student_register.php" method="POST">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label form-label-custom">Full Name</label>
                            <input type="text" class="form-control form-control-custom" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label form-label-custom">Email Address</label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label form-label-custom">Password (Min. 6 chars)</label>
                            <input type="password" class="form-control form-control-custom" id="password" name="password" required>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label form-label-custom">Confirm Password</label>
                            <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-custom-primary w-100 py-2.5 mb-3">Register Now</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <span class="text-muted small">Already have an account?</span>
                        <a href="student_login.php" class="text-decoration-none small fw-bold ms-1">Login here</a>
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

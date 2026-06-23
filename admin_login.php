<?php
// ====================================================================
// Administrator Login Page (admin_login.php)
// This page authenticates administrators by verifying credentials
// against users in the `users` table where role = 'admin'.
// ====================================================================

require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Redirect if already logged in
redirect_if_logged_in();

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        try {
            // Fetch admin record from users table
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin'");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Admin login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];

                header("Location: admin/dashboard.php");
                exit;
            } else {
                $error_msg = "Invalid admin email or password.";
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

$page_title = "Admin Login";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card-custom border-top border-danger border-4">
                <div class="card-header-custom text-center bg-white border-0 pt-4">
                    <h3 class="fw-bold text-dark"><i class="fa-solid fa-screwdriver-wrench me-2 text-danger"></i>Admin Login</h3>
                    <p class="text-muted small">Systems administration portal</p>
                </div>
                <div class="card-body-custom pt-0">
                    
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="admin_login.php" method="POST">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label form-label-custom">Admin Email</label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label form-label-custom">Password</label>
                            <input type="password" class="form-control form-control-custom" id="password" name="password" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-dark w-100 py-2.5 mb-3">Login as Admin</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
// ====================================================================
// Unified Login Portal (login.php)
// This page authenticates students, staff, and administrators
// using a tabbed navigation system, routing users to their correct database
// tables and dashboard screens.
// ====================================================================

require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Redirect if already logged in
redirect_if_logged_in();

$error_msg = '';

$role = normalize_role($_GET['role'] ?? 'student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = normalize_role($_POST['role'] ?? 'student');

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        try {
            if ($role === 'staff') {
                // Fetch staff record from admission_staff table
                $stmt = $pdo->prepare("SELECT * FROM admission_staff WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();
                $id_col = 'staff_id';
            } else {
                // Fetch student or admin from users table
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = :role");
                $stmt->execute(['email' => $email, 'role' => $role]);
                $user = $stmt->fetch();
                $id_col = 'user_id';
            }

            if ($user && password_verify($password, $user['password'])) {
                login_user($user[$id_col], $role, $user['name'], $user['email']);
                app_redirect(role_dashboard_path($role));
            } else {
                $error_msg = "Invalid " . strtolower(role_label($role)) . " email or password.";
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

$page_title = ucfirst($role) . " Login";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header text-center bg-white border-0 pt-4 pb-0">
                    <h3 class="fw-bold text-primary mb-1 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-graduation-cap me-2 text-info fs-3"></i>
                        <span>SCT PORTAL</span>
                    </h3>
                    <p class="text-muted small mb-0 fw-semibold text-uppercase tracking-wider" style="font-size: 11px; color: var(--slate-500) !important;">State College of Technology</p>
                    <p class="text-muted small mt-2">Please sign in to access your dashboard</p>
                </div>
                
                <!-- Role Tabs Navigation -->
                <div class="px-4 pt-3">
                    <ul class="nav nav-tabs nav-fill border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fw-semibold rounded-2 <?php echo $role === 'student' ? 'active text-primary border-bottom border-primary border-2' : 'text-muted'; ?>" href="login.php?role=student">
                                <i class="fa-solid fa-user me-1"></i>Student
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold rounded-2 <?php echo $role === 'staff' ? 'active text-info border-bottom border-info border-2' : 'text-muted'; ?>" href="login.php?role=staff">
                                <i class="fa-solid fa-user-tie me-1"></i>Admission Staff
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold rounded-2 <?php echo $role === 'admin' ? 'active text-danger border-bottom border-danger border-2' : 'text-muted'; ?>" href="login.php?role=admin">
                                <i class="fa-solid fa-screwdriver-wrench me-1"></i>Admin
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-3">
                    <?php render_alert($error_msg, 'danger', false, true); ?>

                    <form action="login.php?role=<?php echo urlencode($role); ?>" method="POST">
                        <input type="hidden" name="role" value="<?php echo e($role); ?>">
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <?php echo $role === 'student' ? 'Email Address' : ($role === 'staff' ? 'Staff Email' : 'Admin Email'); ?>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email ?? ''); ?>" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <!-- Submit Button -->
                        <?php
                        $btn_class = $role === 'student' ? 'btn-primary' : ($role === 'staff' ? 'btn-info text-white' : 'btn-dark');
                        $btn_text = $role === 'student' ? 'Login as Student' : ($role === 'staff' ? 'Login as Staff' : 'Login as Admin');
                        ?>
                        <button type="submit" class="btn <?php echo $btn_class; ?> w-100 py-2 mb-3 fw-bold">
                            <?php echo $btn_text; ?>
                        </button>
                    </form>
                    
                    <?php if ($role === 'student'): ?>
                        <div class="text-center mt-3">
                            <span class="text-muted small">New student?</span>
                            <a href="student_register.php" class="text-decoration-none small fw-bold ms-1">Create an account</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-2">
                        <a href="index.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

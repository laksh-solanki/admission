<?php
// ====================================================================
// Unified User Profile Page (profile.php)
// This page is shared by all logged-in roles (Admin, Staff, Student).
// It allows users to view their account info and change their passwords.
// ====================================================================

require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

check_access(APP_ROLES);

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$error_msg = "";
$success_msg = "";

$user_email = $_SESSION['email'];
$user_name = $_SESSION['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($current_password)) {
        $error_msg = "Name and current password are required to apply updates.";
    } else {
        try {
            $source = account_source_for_role($role);
            $stmt = $pdo->prepare("SELECT password FROM {$source['table']} WHERE {$source['id']} = :id");
            $stmt->execute(['id' => $user_id]);
            $db_hashed_pass = $stmt->fetchColumn();

            if (!$db_hashed_pass || !password_verify($current_password, $db_hashed_pass)) {
                $error_msg = "Verification Failed: Current password is incorrect.";
            } else {
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $error_msg = "New password must be at least 6 characters long.";
                    } elseif ($new_password !== $confirm_password) {
                        $error_msg = "New passwords do not match.";
                    }
                }

                if (empty($error_msg)) {
                    $fields = "name = :name";
                    $params = ['name' => $name, 'id' => $user_id];

                    if (!empty($new_password)) {
                        $fields .= ", password = :password";
                        $params['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    }

                    $up_stmt = $pdo->prepare("UPDATE {$source['table']} SET {$fields} WHERE {$source['id']} = :id");
                    $up_stmt->execute($params);

                    $_SESSION['name'] = $name;
                    $user_name = $name;
                    $success_msg = !empty($new_password)
                        ? "Profile information and password updated successfully."
                        : "Profile details updated successfully.";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

$page_title = "My Profile";
include 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <?php render_topbar('User Profile Desk'); ?>

        <div class="container-fluid">
            <!-- Notifications -->
            <?php render_alert($success_msg); ?>
            <?php render_alert($error_msg, 'danger'); ?>

            <div class="row">
                <!-- Profile Editor Column -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-user-gear me-2"></i>Account Settings
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <!-- Email (Disabled Username) -->
                                <div class="mb-3">
                                    <label class="form-label">Email Address (Registered Login)</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo e($user_email); ?>" readonly disabled>
                                    <div class="form-text small text-muted">Email address acts as login username and cannot be altered.</div>
                                </div>

                                <!-- Display Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Display Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo e($user_name); ?>" required>
                                </div>

                                <!-- Current Password verification -->
                                <div class="mb-4 pt-3 border-top">
                                    <label for="current_password" class="form-label text-danger">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Confirm current password to save changes" required>
                                </div>

                                <!-- New Password Fields -->
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Change Account Password (Optional)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Min. 6 characters">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Info summary column -->
                <div class="col-lg-5">
                    <div class="card bg-white">
                        <div class="card-header bg-light">
                            <i class="fa-solid fa-shield-halved me-1 text-primary"></i>Security Center
                        </div>
                        <div class="card-body text-center">
                            <i class="fa-solid fa-circle-user text-primary mb-3" style="font-size: 72px;"></i>
                            <h4 class="fw-bold mb-1"><?php echo e($user_name); ?></h4>
                            <span class="badge bg-secondary mb-3"><?php echo strtoupper($role); ?> ACCOUNT</span>
                            
                            <hr>
                            
                            <div class="text-start small text-muted">
                                <p class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i>Passwords must be at least 6 characters long.</p>
                                <p class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i>Always use strong characters including numbers and symbols.</p>
                                <p class="mb-0"><i class="fa-solid fa-circle-dot text-primary me-2"></i>Remember to log out if you are on a shared computer.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


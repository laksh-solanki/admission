<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin access
check_access('admin');

$error_msg = "";
$success_msg = "";

// --------------------------------------------------------------------
// PROCESS ACTIONS (ADD, EDIT, DELETE)
// --------------------------------------------------------------------

// 1. Delete Staff
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM admission_staff WHERE staff_id = :id");
        $stmt->execute(['id' => $delete_id]);
        $success_msg = "Staff account deleted successfully.";
    } catch (PDOException $e) {
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

// 2. Add or Edit Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email)) {
        $error_msg = "Name and Email fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        if ($action === 'add') {
            if (empty($password) || strlen($password) < 6) {
                $error_msg = "Password is required and must be at least 6 characters long.";
            } else {
                try {
                    // Check if email already registered in staff table
                    $chk = $pdo->prepare("SELECT staff_id FROM admission_staff WHERE email = :email");
                    $chk->execute(['email' => $email]);
                    
                    if ($chk->rowCount() > 0) {
                        $error_msg = "This email is already registered to a staff account.";
                    } else {
                        // Secure password hashing
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO admission_staff (name, email, password) VALUES (:name, :email, :password)");
                        $stmt->execute([
                            'name' => $name,
                            'email' => $email,
                            'password' => $hashed_password
                        ]);
                        $success_msg = "New staff account created successfully.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database Error: " . $e->getMessage();
                }
            }
        } elseif ($action === 'edit') {
            $staff_id = intval($_POST['staff_id']);
            try {
                // Check if email is already taken by another staff
                $chk = $pdo->prepare("SELECT staff_id FROM admission_staff WHERE email = :email AND staff_id != :id");
                $chk->execute(['email' => $email, 'id' => $staff_id]);
                
                if ($chk->rowCount() > 0) {
                    $error_msg = "This email is already registered to another staff account.";
                } else {
                    if (!empty($password)) {
                        // If password is changed, update with encryption
                        if (strlen($password) < 6) {
                            $error_msg = "Password must be at least 6 characters long.";
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE admission_staff SET name = :name, email = :email, password = :pass WHERE staff_id = :id");
                            $stmt->execute([
                                'name' => $name,
                                'email' => $email,
                                'pass' => $hashed_password,
                                'id' => $staff_id
                            ]);
                            $success_msg = "Staff details and password updated successfully.";
                        }
                    } else {
                        // Password not changed, update other fields
                        $stmt = $pdo->prepare("UPDATE admission_staff SET name = :name, email = :email WHERE staff_id = :id");
                        $stmt->execute([
                            'name' => $name,
                            'email' => $email,
                            'id' => $staff_id
                        ]);
                        $success_msg = "Staff details updated successfully.";
                    }
                }
            } catch (PDOException $e) {
                $error_msg = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all staff accounts
try {
    $stmt = $pdo->query("SELECT * FROM admission_staff ORDER BY staff_id DESC");
    $staff_members = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "Manage Staff Accounts";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <?php render_topbar('Staff Management Portal', '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="fa-solid fa-plus me-1"></i>Create Staff Account</button>'); ?>

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

            <!-- Staff Accounts Table -->
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-user-tie me-2"></i>Admission Staff Accounts
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table align-middle">
                            <thead>
                                <tr>
                                    <th># ID</th>
                                    <th>Staff Name</th>
                                    <th>Email Address</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($staff_members)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No staff accounts registered yet. Click "Create Staff Account" to set one up.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($staff_members as $s): ?>
                                        <tr>
                                            <td><?php echo $s['staff_id']; ?></td>
                                            <td class="fw-bold text-primary"><?php echo e($s['name']); ?></td>
                                            <td><?php echo e($s['email']); ?></td>
                                            <td class="text-center">
                                                <!-- Edit button triggers Populate JS -->
                                                <button class="btn btn-sm btn-outline-secondary me-2" onclick="editStaff(<?php echo e(json_encode($s)); ?>)">
                                                    <i class="fa-solid fa-user-pen"></i> Edit
                                                </button>
                                                <!-- Delete button -->
                                                <a href="manage_staff.php?delete_id=<?php echo $s['staff_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this staff account?');">
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

<!-- ====================================================================
     MODALS SECTION
     ==================================================================== -->

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addStaffModalLabel"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Create Staff Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="manage_staff.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Staff Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Secure Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Account Password (Min. 6 chars)</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editStaffModalLabel"><i class="fa-solid fa-user-gear me-1 text-primary"></i>Edit Staff Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="manage_staff.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_staff_id" name="staff_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Staff Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Secure Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="Change password...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Populate values in edit form modal dynamically -->
<script>
function editStaff(staff) {
    document.getElementById('edit_staff_id').value = staff.staff_id;
    document.getElementById('edit_name').value = staff.name;
    document.getElementById('edit_email').value = staff.email;
    document.getElementById('edit_password').value = ""; // Always reset password input
    
    // Show edit modal
    const editModal = new bootstrap.Modal(document.getElementById('editStaffModal'));
    editModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>

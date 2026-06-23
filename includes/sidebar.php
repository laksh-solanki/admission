<?php
// ====================================================================
// Sidebar Layout Component
// This component renders a dynamic, responsive navigation sidebar
// tailored to the logged-in user's role (admin, staff, or student).
// ====================================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Compute the base path to correctly link pages regardless of depth
$base_path = "";
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if (in_array($current_dir, ['admin', 'staff', 'student'])) {
    $base_path = "../";
}

// Fetch the current script name to mark active menu items
$current_page = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
?>
<nav id="sidebar">
    <div class="sidebar-header">
        <h4><i class="fa-solid fa-graduation-cap me-2"></i>College Portal</h4>
        <small class="text-white-50">Welcome, <?php echo htmlspecialchars($user_name); ?></small>
    </div>

    <ul class="list-unstyled components">
        <?php if ($role === 'admin'): ?>
            <!-- Admin Navigation Items -->
            <li class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            </li>
            <li class="<?php echo ($current_page === 'manage_courses.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>admin/manage_courses.php"><i class="fa-solid fa-book"></i> Course Management</a>
            </li>
            <li class="<?php echo ($current_page === 'manage_staff.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>admin/manage_staff.php"><i class="fa-solid fa-user-tie"></i> Manage Staff</a>
            </li>
            <li class="<?php echo ($current_page === 'manage_students.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>admin/manage_students.php"><i class="fa-solid fa-users"></i> Manage Students</a>
            </li>
            <li class="<?php echo ($current_page === 'backup_restore.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>admin/backup_restore.php"><i class="fa-solid fa-database"></i> Backup & Restore</a>
            </li>

        <?php elseif ($role === 'staff'): ?>
            <!-- Staff Navigation Items -->
            <li class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>staff/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            </li>
            <li class="<?php echo ($current_page === 'verify.php' || $current_page === 'verify_details.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>staff/verify.php"><i class="fa-solid fa-id-card-clip"></i> Verify Applications</a>
            </li>
            <li class="<?php echo ($current_page === 'reports.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>staff/reports.php"><i class="fa-solid fa-chart-line"></i> Generate Reports</a>
            </li>

        <?php elseif ($role === 'student'): ?>
            <!-- Student Navigation Items -->
            <li class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>student/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            </li>
            <li class="<?php echo ($current_page === 'apply.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>student/apply.php"><i class="fa-solid fa-file-signature"></i> Fill Admission Form</a>
            </li>
            <li class="<?php echo ($current_page === 'upload.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>student/upload.php"><i class="fa-solid fa-file-arrow-up"></i> Upload Documents</a>
            </li>
            <li class="<?php echo ($current_page === 'status.php') ? 'active' : ''; ?>">
                <a href="<?php echo $base_path; ?>student/status.php"><i class="fa-solid fa-clock-rotate-left"></i> Track Application</a>
            </li>
        <?php endif; ?>

        <!-- Common Items -->
        <li class="<?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">
            <a href="<?php echo $base_path; ?>profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>logout.php" class="text-warning"><i class="fa-solid fa-right-from-bracket text-warning"></i> Logout</a>
        </li>
    </ul>
</nav>
